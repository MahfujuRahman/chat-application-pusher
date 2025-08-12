<template>
  <div class="container-fluid">
    <!--Start Dashboard Content-->

    <div class="card mt-3">
      <div class="card-content">
        <div class="row row-group m-0">
          <div class="col-12 col-lg-6 col-xl-3 border-light">
            <div class="card-body">
              <h5 class="text-white mb-0">
                {{ data.total_users }}
              </h5>
              <div class="progress my-3" style="height: 3px"></div>
              <p class="mb-0 text-white small-font">Total Users</p>
            </div>
          </div>
          <div class="col-12 col-lg-6 col-xl-3 border-light">
            <div class="card-body">
              <h5 class="text-white mb-0">
                {{ data.total_projects }}
              </h5>
              <div class="progress my-3" style="height: 3px"></div>
              <p class="mb-0 text-white small-font">Total Projects</p>
            </div>
          </div>
          <div class="col-12 col-lg-6 col-xl-3 border-light">
            <div class="card-body">
              <h5 class="text-white mb-0">
                {{ data.total_todos }}
              </h5>
              <div class="progress my-3" style="height: 3px"></div>
              <p class="mb-0 text-white small-font">Total Todos</p>
            </div>
          </div>
          <div class="col-12 col-lg-6 col-xl-3 border-light">
            <div class="card-body">
              <h5 class="text-white mb-0">
                {{ data.total_todos }}
              </h5>
              <div class="progress my-3" style="height: 3px"></div>
              <p class="mb-0 text-white small-font">Total Todos</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card mt-3">
      <div class="card-content">
        <div class="row row-group m-0">
          <div class="col-12 col-lg-6 col-xl-3 border-light">
            <div class="card-body">
              <h5 class="text-white mb-0">
                {{ data.total_credentials }}
              </h5>
              <div class="progress my-3" style="height: 3px"></div>
              <p class="mb-0 text-white small-font">Total Credentials</p>
            </div>
          </div>
          <div class="col-12 col-lg-6 col-xl-3 border-light">
            <div class="card-body">
              <h5 class="text-white mb-0">
                {{ data.total_attendance }}
              </h5>
              <div class="progress my-3" style="height: 3px"></div>
              <p class="mb-0 text-white small-font">Total Attendance</p>
            </div>
          </div>
          <div class="col-12 col-lg-6 col-xl-3 border-light">
            <div class="card-body">
              <h5 class="text-white mb-0">
                {{ data.total_meetings }}
              </h5>
              <div class="progress my-3" style="height: 3px"></div>
              <p class="mb-0 text-white small-font">Total Meetings</p>
            </div>
          </div>
          <div class="col-12 col-lg-6 col-xl-3 border-light">
            <div class="card-body">
              <h5 class="text-white mb-0">
                {{ data.total_meeting_agendas }}
              </h5>
              <div class="progress my-3" style="height: 3px"></div>
              <p class="mb-0 text-white small-font">Total Meeting Agendas</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--End Row-->

    <!--Caleder section start-->
    <div class="calendar-container mb-5">
      <!-- Header -->
      <div class="calendar-header">
        <h2><span>📅</span> Calendar</h2>
        <div class="calendar-controls">
          <span class="month-label">{{ currentMonthYear }}</span>
          <input type="date" v-model="selectedDate" @change="onDateChange" />
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="calendar-grid">
        <div
          class="weekday-header"
          v-for="day in weekdayNames"
          :key="day"
          :class="[
            'weekday-header',
            day === 'Fri' || day === 'Sat' ? 'bd-offday' : '',
          ]"
        >
          {{ day }}
        </div>
        <div
          v-for="n in startDayOfMonth"
          :key="'empty-' + n"
          class="calendar-cell empty-cell"
        ></div>
        <div
          v-for="(date, index) in daysInMonth"
          :key="index"
          :class="calendarCellClass(date)"
          class="position-relative"
        >
          <router-link
            :to="`/meeting/all?date=${new Date(
              date.getTime() + 24 * 60 * 60 * 1000
            )
              .toISOString()
              .substr(0, 10)}`"
          >
            <div class="badge-container" v-if="countTodayMeetings(date) > 0">
              <span
                class="badge py-1"
                style="position: absolute; top: 5px; left: 5px"
                >M ({{ countTodayMeetings(date) }})
              </span>
            </div>
          </router-link>
          <router-link
            :to="`/tasks/date-wise-tasks/${new Date(
              date.getTime() + 24 * 60 * 60 * 1000
            )
              .toISOString()
              .substr(0, 10)}`"
          >
            <div class="badge-container" v-if="countTodayTasks(date) > 0">
              <span
                class="badge py-1"
                style="position: absolute; top: 5px; right: 5px"
                >T ({{ countTodayTasks(date) }})
              </span>
            </div>
          </router-link>
          <div class="date-number">{{ date.getDate() }}</div>
          <div class="day-name">{{ formatDay(date) }}</div>
        </div>
      </div>
    </div>
    <!--Caleder section end-->
    <!--start overlay-->
    <div class="overlay"></div>
    <!--end overlay-->
  </div>
  <!-- End container-fluid-->
</template>

<script>
export default {
  data: () => ({
    data: {},
    selectedDate: new Date().toISOString().substr(0, 10),
    task_list_dates: [],
    meeting_dates: [],
  }),
  created: async function () {
    await this.get_all_dashboard_data();
    await this.get_all_tasks();
    await this.get_all_meetings();
  },
  methods: {
    get_all_dashboard_data: async function () {
      let response = await axios.get("get-all-dashboard-data");
      if (response.status == 200) {
        this.data = response.data.data;
      }
    },
    get_all_tasks: async function () {
      let response = await axios.get("task?get_all=1");
      if (response.status == 200) {
        // Only keep the start_date values in the array
        this.task_list_dates = response.data.data
          .filter((task) => task.start_date)
          .map((task) => task.start_date);
      }
    },
    get_all_meetings: async function () {
      let response = await axios.get("meeting?get_all=1");
      if (response.status == 200) {
        // Only keep the start_date values in the array
        this.meeting_dates = response.data.data
          .filter((meeting) => meeting.date)
          .map((meeting) => meeting.date);
      }
    },
    formatDay(date) {
      return date.toLocaleString("default", { weekday: "long" });
    },
    isHoliday(date) {
      // 5 = Friday, 6 = Saturday
      return date.getDay() === 5 || date.getDay() === 6;
    },
    isToday(date) {
      const today = new Date();
      return (
        date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear()
      );
    },
    calendarCellClass(date) {
      // If today and holiday, use both classes
      if (this.isToday(date) && this.isHoliday(date)) {
        return ["calendar-cell", "today-success", "today-holiday-border"];
      } else if (this.isToday(date)) {
        return ["calendar-cell", "today-success"];
      } else if (this.isHoliday(date)) {
        return ["calendar-cell", "friday"];
      } else {
        return ["calendar-cell"];
      }
    },
    onDateChange() {
      // The computed will auto-update
    },
    countTodayTasks(date) {
      const currentDate =
        date.getFullYear() +
        "-" +
        String(date.getMonth() + 1).padStart(2, "0") +
        "-" +
        String(date.getDate()).padStart(2, "0");

      return this.task_list_dates.filter((taskDate) => {
        const taskDay = taskDate.split(" ")[0]; // Get only YYYY-MM-DD
        return taskDay === currentDate;
      }).length;
    },
    countTodayMeetings(date) {
      const currentDate =
        date.getFullYear() +
        "-" +
        String(date.getMonth() + 1).padStart(2, "0") +
        "-" +
        String(date.getDate()).padStart(2, "0");

      return this.meeting_dates.filter((meetingDate) => {
        const meetingDay = meetingDate.split(" ")[0]; // Get only YYYY-MM-DD
        return meetingDay === currentDate;
      }).length;
    },
  },
  computed: {
    currentMonthYear() {
      const date = new Date(this.selectedDate);
      return date.toLocaleString("default", {
        month: "short",
        year: "numeric",
      });
    },
    daysInMonth() {
      const date = new Date(this.selectedDate);
      const year = date.getFullYear();
      const month = date.getMonth();
      const days = [];
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);

      for (let d = 1; d <= lastDay.getDate(); d++) {
        days.push(new Date(year, month, d));
      }

      return days;
    },
    weekdayNames() {
      return ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    },
    startDayOfMonth() {
      const date = new Date(this.selectedDate);
      const firstDay = new Date(date.getFullYear(), date.getMonth(), 1);
      return firstDay.getDay(); // 0 (Sun) to 6 (Sat)
    },
  },
};
</script>

<style scoped>
.calendar-container {
  color: #ffffff;
  padding: 20px;
  border-radius: 10px;
  margin: auto;
  font-family: Arial, sans-serif;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.5);
}

.calendar-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.calendar-header h2 {
  font-size: 20px;
  display: flex;
  align-items: center;
  gap: 5px;
}

.calendar-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.calendar-controls input {
  background-color: #1f2e47;
  color: #ffffff;
  border: 1px solid #33415c;
  padding: 5px 10px;
  border-radius: 5px;
}

.month-label {
  font-weight: bold;
  color: #ccc;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 10px;
}

@media (max-width: 900px) {
  .calendar-grid {
    grid-template-columns: repeat(4, 1fr);
  }
}

@media (max-width: 600px) {
  .calendar-container {
    padding: 8px;
    font-size: 13px;
  }
  .calendar-header h2 {
    font-size: 15px;
  }
  .calendar-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 6px;
  }
  .calendar-cell {
    padding: 18px 4px;
    font-size: 12px;
  }
  .date-number {
    font-size: 14px;
  }
  .day-name {
    font-size: 10px;
  }
}

.calendar-cell {
  background-color: #1f2e47;
  border: 1px solid #2a3a59;
  padding: 40px 10px;
  border-radius: 8px;
  text-align: center;
  transition: background-color 0.3s;
}

.calendar-cell:hover {
  background-color: #2a3a59;
}

.friday {
  background-color: #3b2a4a;
  border: 1px solid #b4004e;
}

.today-success {
  background-color: #198754 !important;
  border: 1px solid #157347 !important;
  color: #fff !important;
}

/* Red border if today is a holiday */
.today-holiday-border {
  border: 2px solid #ff2d2d !important;
}

.date-number {
  font-size: 18px;
  font-weight: bold;
}

.day-name {
  font-size: 12px;
  color: #ccc;
}
.badge {
  background-color: #dc3545;
  color: white;
  font-size: 10px;
  padding: 2px 6px;
  border-radius: 12px;
  font-weight: bold;
}

.weekday-header {
  font-weight: bold;
  text-align: center;
  color: #fff;
  background: #027708;
  padding: 8px 0px;
  border-radius: 5px;
}

.empty-cell {
  background: transparent;
  border: none;
}

.bd-offday {
  background-color: #4d0000 !important;
}
</style>
