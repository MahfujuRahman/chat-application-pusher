<?php

namespace App\Modules\Management\UserManagement\User\Actions;



class GetSingleData
{
    static $model = \App\Modules\Management\UserManagement\User\Models\Model::class;

    public static function execute($slug)
    {
        try {
            $with = ['role', 'tasks', 'project', 'attendance'];
           
            $fields = request()->input('fields') ?? ['*'];
            if (!$data = self::$model::query()->with($with)->select($fields)->where('slug', $slug)->first()) {
                return messageResponse('Data not found...',$data, 404, 'error');
            }
            
            // Calculate user statistics
            $statistics = [
                // Project Statistics
                'total_projects' => $data->project ? $data->project->count() : 0,
                'active_projects' => $data->project ? $data->project->where('status', 'active')->count() : 0,
                'completed_projects' => $data->project ? $data->project->where('status', 'completed')->count() : 0,
                'pending_projects' => $data->project ? $data->project->where('status', 'pending')->count() : 0,
                
                // Task Statistics
                'total_tasks' => $data->tasks ? $data->tasks->count() : 0,
                'completed_tasks' => $data->tasks ? $data->tasks->where('task_status', 'Completed')->count() : 0,
                'pending_tasks' => $data->tasks ? $data->tasks->where('task_status', 'Pending')->count() : 0,
                'in_progress_tasks' => $data->tasks ? $data->tasks->where('task_status', 'In Progress')->count() : 0,
                'overdue_tasks' => $data->tasks ? $data->tasks->where('task_status', 'Overdue')->count() : 0,
                'cancelled_tasks' => $data->tasks ? $data->tasks->where('task_status', 'Cancelled')->count() : 0,
                
                // Task Priority Statistics
                'high_priority_tasks' => $data->tasks ? $data->tasks->where('priority', 'High')->count() : 0,
                'medium_priority_tasks' => $data->tasks ? $data->tasks->where('priority', 'Medium')->count() : 0,
                'low_priority_tasks' => $data->tasks ? $data->tasks->where('priority', 'Low')->count() : 0,
                
                // Attendance Statistics
                'total_attendance_records' => $data->attendance ? $data->attendance->count() : 0,
                'present_days' => $data->attendance ? $data->attendance->where('attendance_status', 'Present')->count() : 0,
                'absent_days' => $data->attendance ? $data->attendance->where('attendance_status', 'Absent')->count() : 0,
                'late_days' => $data->attendance ? $data->attendance->where('attendance_status', 'Late')->count() : 0,
                'half_day' => $data->attendance ? $data->attendance->where('attendance_status', 'Half Day')->count() : 0,
                
                // Performance Calculations
                'task_completion_rate' => $data->tasks && $data->tasks->count() > 0 
                    ? round(($data->tasks->where('task_status', 'Completed')->count() / $data->tasks->count()) * 100, 2) 
                    : 0,
                'attendance_rate' => $data->attendance && $data->attendance->count() > 0 
                    ? round(($data->attendance->where('attendance_status', 'Present')->count() / $data->attendance->count()) * 100, 2) 
                    : 0,
                'project_completion_rate' => $data->project && $data->project->count() > 0 
                    ? round(($data->project->where('status', 'completed')->count() / $data->project->count()) * 100, 2) 
                    : 0,
                
                // Recent Activity (last 30 days)
                'recent_tasks' => $data->tasks ? $data->tasks->where('created_at', '>=', now()->subDays(30))->count() : 0,
                'recent_attendance' => $data->attendance ? $data->attendance->where('created_at', '>=', now()->subDays(30))->count() : 0,
                
                // Additional Metrics
                'average_task_completion_days' => $data->tasks && $data->tasks->where('task_status', 'Completed')->count() > 0
                    ? $data->tasks->where('task_status', 'Completed')
                        ->filter(function($task) {
                            return $task->created_at && $task->updated_at;
                        })
                        ->avg(function($task) {
                            return $task->created_at->diffInDays($task->updated_at);
                        })
                    : 0,
            ];
            
            // Calculate User Rating (out of 10) based on task completion
            // Each task contributes max 5 points, rating calculated based on completion percentage
            $rating = 0;
            if ($data->tasks && $data->tasks->count() > 0) {
                $totalTasks = $data->tasks->count();
                $completedTasks = $data->tasks->where('task_status', 'Completed')->count();
                
                // Base rating from task completion (0-7 points)
                $completionRate = ($completedTasks / $totalTasks);
                $baseRating = $completionRate * 7; // 70% weight for completion
                
                // Bonus points for performance factors (0-3 points)
                $bonusPoints = 0;
                
                // Bonus for attendance (0-1.5 points)
                if ($data->attendance && $data->attendance->count() > 0) {
                    $attendanceRate = $data->attendance->where('attendance_status', 'Present')->count() / $data->attendance->count();
                    $bonusPoints += $attendanceRate * 1.5;
                }
                
                // Bonus for task efficiency (0-1.5 points)
                $inProgressTasks = $data->tasks->where('task_status', 'In Progress')->count();
                $overdueTasks = $data->tasks->where('task_status', 'Overdue')->count();
                
                if ($totalTasks > 0) {
                    $efficiencyRate = 1 - (($overdueTasks * 0.5 + $inProgressTasks * 0.2) / $totalTasks);
                    $bonusPoints += max(0, $efficiencyRate) * 1.5;
                }
                
                // Calculate final rating (max 10)
                $rating = min(10, round($baseRating + $bonusPoints, 1));
            }
            
            // Add rating to statistics
            $statistics['user_rating'] = $rating;
            $statistics['rating_breakdown'] = [
                'completion_score' => $data->tasks && $data->tasks->count() > 0 
                    ? round((($data->tasks->where('task_status', 'Completed')->count() / $data->tasks->count()) * 7), 1)
                    : 0,
                'attendance_bonus' => $data->attendance && $data->attendance->count() > 0 
                    ? round((($data->attendance->where('attendance_status', 'Present')->count() / $data->attendance->count()) * 1.5), 1)
                    : 0,
                'efficiency_bonus' => $data->tasks && $data->tasks->count() > 0
                    ? round((1 - (($data->tasks->where('task_status', 'Overdue')->count() * 0.5 + $data->tasks->where('task_status', 'In Progress')->count() * 0.2) / $data->tasks->count())) * 1.5, 1)
                    : 0,
                'max_possible_score' => 10
            ];
            
            // Add statistics to the data object
            $data->statistics = $statistics;
            
            // Add rating directly to user data for easy access
            $data->rating = $rating;
            
            return entityResponse($data);
        } catch (\Exception $e) {
            return messageResponse($e->getMessage(),[], 500, 'server_error');
        }
    }
}