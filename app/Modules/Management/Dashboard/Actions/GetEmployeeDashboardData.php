<?php

namespace App\Modules\Management\Dashboard\Actions;

use App\Modules\Management\ProjectManagement\Project\Models\Model as Project;
use App\Modules\Management\TodoManagement\AddTodo\Models\Model as Todo;
use App\Modules\Management\TodoManagement\Credential\Models\Model as Credential;
use App\Modules\Management\AttendanceManagement\Attendance\Models\Model as Attendance;
use App\Modules\Management\TasksManagement\Tasks\Models\Model as Task;
use Illuminate\Support\Facades\DB;

class GetEmployeeDashboardData
{
    public static function execute()
    {
        try {
            $user = auth()->user();
            $userId = $user->id;

            // Optimize queries using single queries with aggregations
            $projectCount = Project::whereHas('project_users', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->count();

            $todoCount = Todo::where('user_id', $userId)->count();
            $credentialCount = Credential::where('user_id', $userId)->count();
            $total_late_days = Attendance::where('user_id', $userId)
                ->where('is_late',  true)
                ->count();
            $total_late_minutes = Attendance::where('user_id', $userId)
                ->where('is_late',  true)
                ->sum('late_minutes');

            // Get attendance stats in a single query
            $attendanceStats = Attendance::where('user_id', $userId)
                ->selectRaw('
                    COUNT(*) as total_attendance,
                    SUM(CASE WHEN attendance_status = "Present" THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN attendance_status = "Absent" THEN 1 ELSE 0 END) as absent_count
                ')
                ->first();

            // Get task stats in a single query including rating
            $taskStats = Task::where('assigned_to', $userId)
                ->selectRaw('
                    COUNT(*) as total_tasks,
                    SUM(CASE WHEN task_status = "Completed" THEN 1 ELSE 0 END) as completed_tasks,
                    SUM(CASE WHEN task_status = "Not Completed" THEN 1 ELSE 0 END) as not_completed_tasks,
                    SUM(CASE WHEN task_status = "Pending" THEN 1 ELSE 0 END) as pending_tasks,
                    SUM(CASE WHEN task_status = "In Progress" THEN 1 ELSE 0 END) as in_progress_tasks,
                    COALESCE(AVG(CASE WHEN task_status IN ("Completed", "Not Completed") AND rating IS NOT NULL THEN rating END), 0) as average_rating,
                    COUNT(CASE WHEN task_status IN ("Completed", "Not Completed") AND rating IS NOT NULL THEN 1 END) as rated_tasks_count
                ')
                ->first();

            // Calculate rating efficiently
            $rating = self::calculateUserRating($taskStats, $attendanceStats);
            $ratingBreakdown = self::calculateRatingBreakdown($taskStats, $attendanceStats);

            // Prepare final data array
            $data = [
                'total_projects' => $projectCount,
                'total_todos' => $todoCount,
                'total_credentials' => $credentialCount,
                'total_present_attendance' => $attendanceStats->present_count ?? 0,
                'total_absent_attendance' => $attendanceStats->absent_count ?? 0,
                'total_tasks' => $taskStats->total_tasks ?? 0,
                'total_complete_tasks' => $taskStats->completed_tasks ?? 0,
                'total_not_completed_tasks' => $taskStats->not_completed_tasks ?? 0,
                'total_pending_tasks' => $taskStats->pending_tasks ?? 0,
                'total_in_progress_tasks' => $taskStats->in_progress_tasks ?? 0,
                'user_rating' => $rating,
                'rating_breakdown' => $ratingBreakdown,
            ];

            return entityResponse($data);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(), [], 500, 'server_error');
        }
    }

    /**
     * Calculate user rating based on average task ratings (max 10)
     */
    private static function calculateUserRating($taskStats, $attendanceStats): float
    {
        $averageRating = $taskStats->average_rating ?? 0;
        $ratedTasksCount = $taskStats->rated_tasks_count ?? 0;

        if ($ratedTasksCount === 0) {
            return 0;
        }

        // Convert average rating to scale of 10
        // Assuming task ratings are on a scale of 1-5, convert to 1-10
        $userRating = ($averageRating / 5) * 10;

        // Return final rating (max 10)
        return round($userRating, 1);
    }
    /**
     * Calculate detailed rating breakdown based on task ratings
     */
    private static function calculateRatingBreakdown($taskStats, $attendanceStats): array
    {
        $averageRating = $taskStats->average_rating ?? 0;
        $ratedTasksCount = $taskStats->rated_tasks_count ?? 0;
        $completedTasks = $taskStats->completed_tasks ?? 0;
        $notCompletedTasks = $taskStats->not_completed_tasks ?? 0;

        // Convert average rating to scale of 10
        $userRating = $ratedTasksCount > 0 ? round(($averageRating / 5) * 10, 1) : 0;

        return [
            'user_rating' => $userRating,
            'average_task_rating' => round($averageRating, 1),
            'rated_tasks_count' => $ratedTasksCount,
            'completed_tasks' => $completedTasks,
            'not_completed_tasks' => $notCompletedTasks,
            'total_evaluated_tasks' => $completedTasks + $notCompletedTasks,
            'rating_scale' => 'Task ratings (1-5) converted to user rating (1-10)',
            'max_possible_score' => 10,
            'total_late_days' => $total_late_days ?? 0,
            'total_late_minutes' => $total_late_minutes ?? 0,
        ];
    }
}
