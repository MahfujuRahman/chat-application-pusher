<template>
  <div class="container-fluid px-4 py-3 my-3" :data-theme="currentTheme">
    <!-- Page Header -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="page-header">
          <h1 class="page-title">User Profile</h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="#" class="text-decoration-none">Users</a></li>
              <li class="breadcrumb-item active" aria-current="page">Profile</li>
            </ol>
          </nav>
        </div>
      </div>
    </div>

    <!-- Main Profile Section -->
    <div class="row g-4">
      <!-- Profile Card -->
      <div class="col-xl-4 col-lg-5 col-md-6">
        <div class="card profile-card shadow-lg border-0">
          <!-- Profile Header -->
          <div class="profile-header position-relative">
            <div class="profile-bg"></div>
            <div class="profile-image-container text-center">
              <div class="profile-avatar">
                <img
                  class="avatar-img"
                  :src="`${item.image ?? 'avatar.png'}`"
                  alt="User Profile"
                />
                <!-- Rating Badge -->
                <div class="rating-badge">
                  <div class="rating-circle" :data-rating="item.rating || 0">
                    <span class="rating-value">{{ item.rating || 0 }}</span>
                    <span class="rating-scale">/10</span>
                  </div>
                </div>
              </div>
              <div class="profile-name mt-3">
                <h3 class="mb-1 fw-bold">{{ item.name || 'N/A' }}</h3>
                <p class="mb-0">Team Member</p>
              </div>
            </div>
          </div>
          
          <!-- Profile Information -->
          <div class="card-body pt-4">
            <div class="profile-details">
              <div class="detail-row">
                <div class="detail-icon">
                  <i class="zmdi zmdi-email text-primary"></i>
                </div>
                <div class="detail-content">
                  <label class="detail-label">Email Address</label>
                  <span class="detail-value">{{ item.email || 'N/A' }}</span>
                </div>
              </div>
              
              <div class="detail-row">
                <div class="detail-icon">
                  <i class="zmdi zmdi-phone text-success"></i>
                </div>
                <div class="detail-content">
                  <label class="detail-label">Phone Number</label>
                  <span class="detail-value">{{ item.phone_number || 'N/A' }}</span>
                </div>
              </div>
              
              <div class="detail-row">
                <div class="detail-icon">
                  <i class="zmdi zmdi-pin-drop text-danger"></i>
                </div>
                <div class="detail-content">
                  <label class="detail-label">Address</label>
                  <span class="detail-value">{{ item.address || "N/A" }}</span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Social Media Section -->
          <div class="card-footer bg-light border-0">
            <div class="social-section">
              <div class="social-header mb-3">
              
                <h6 class="social-title d-inline mb-0">Professional Networks</h6>
              </div>
              <div class="social-content">
                <template v-if="item.social_media && item.social_media.length > 0">
                  <div class="social-links">
                    <a
                      v-for="(social, index) in item.social_media"
                      :key="index"
                      :href="social.link"
                      target="_blank"
                      rel="noopener noreferrer"
                      :title="`Connect on ${social.name}`"
                      class="social-link"
                      :data-platform="social.name.toLowerCase()"
                    >
                      <i :class="getSocialMediaIcon(social.name)"></i>
                      <span class="social-tooltip">{{ social.name }}</span>
                    </a>
                  </div>
                </template>
                <template v-else>
                  <div class="no-social-state">
                    <i class="zmdi zmdi-link zmdi-hc-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0 small">No professional networks connected</p>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Stats and Content Section -->
      <div class="col-xl-8 col-lg-7 col-md-6">
        <!-- Statistics Cards -->
        <div class="row mb-4 stats-row">
          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 stats-col">
            <div class="stats-card card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <div class="stats-content">
                  <div class="stats-icon bg-primary-subtle">
                    <i class="zmdi zmdi-case text-primary"></i>
                  </div>
                  <div class="stats-info">
                    <h3 class="stats-number text-primary">{{ item.project?.length || 0 }}</h3>
                    <p class="stats-label mb-0">Total Projects</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 stats-col">
            <div class="stats-card card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <div class="stats-content">
                  <div class="stats-icon bg-warning-subtle">
                    <i class="zmdi zmdi-assignment text-warning"></i>
                  </div>
                  <div class="stats-info">
                    <h3 class="stats-number text-warning">{{ item.tasks?.length || 0 }}</h3>
                    <p class="stats-label mb-0">Total Tasks</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 stats-col">
            <div class="stats-card card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <div class="stats-content">
                  <div class="stats-icon bg-success-subtle">
                    <i class="zmdi zmdi-check-circle text-success"></i>
                  </div>
                  <div class="stats-info">
                    <h3 class="stats-number text-success">
                      {{ item.tasks?.filter(task => task.task_status === "Completed").length || 0 }}
                    </h3>
                    <p class="stats-label mb-0">Completed</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 stats-col">
            <div class="stats-card card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <div class="stats-content">
                  <div class="stats-icon bg-danger-subtle">
                    <i class="zmdi zmdi-time text-danger"></i>
                  </div>
                  <div class="stats-info">
                    <h3 class="stats-number text-danger">
                      {{ item.tasks?.filter(task => task.task_status !== "Completed").length || 0 }}
                    </h3>
                    <p class="stats-label mb-0">Pending</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 stats-col">
            <div class="stats-card card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <div class="stats-content">
                  <div class="stats-icon bg-info-subtle">
                    <i class="zmdi zmdi-calendar-check text-info"></i>
                  </div>
                  <div class="stats-info">
                    <h3 class="stats-number text-info">
                      {{ item.attendance?.filter(att => att.attendance_status === "Present").length || 0 }}
                    </h3>
                    <p class="stats-label mb-0">Present Days</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 stats-col">
            <div class="stats-card card border-0 shadow-sm h-100">
              <div class="card-body p-4">
                <div class="stats-content">
                  <div class="stats-icon bg-secondary-subtle">
                    <i class="zmdi zmdi-calendar-close text-secondary"></i>
                  </div>
                  <div class="stats-info">
                    <h3 class="stats-number text-secondary">
                      {{ item.attendance?.filter(att => att.attendance_status === "Absent").length || 0 }}
                    </h3>
                    <p class="stats-label mb-0">Absent Days</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Projects Table -->
        <div class="card border-0 shadow-sm">
          <div class="card-header bg-white border-bottom">
            <h5 class="card-title mb-0 fw-semibold">
              <i class="zmdi zmdi-folder text-primary me-2"></i>
              Project Overview
            </h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="border-0 fw-semibold">#</th>
                    <th class="border-0 fw-semibold">Project Name</th>
                    <th class="border-0 fw-semibold">Status</th>
                    <th class="border-0 fw-semibold">Start Date</th>
                    <th class="border-0 fw-semibold">End Date</th>
                  </tr>
                </thead>
                <tbody>
                  <template v-if="Array.isArray(item.project) && item.project?.length">
                    <tr
                      v-for="(project, index) in item.project"
                      :key="project.id || index"
                      class="border-bottom"
                    >
                      <td class="fw-medium">{{ index + 1 }}</td>
                      <td>
                        <div class="project-name">
                          <span class="fw-semibold">{{ project.name || 'N/A' }}</span>
                        </div>
                      </td>
                      <td>
                        <span :class="getStatusBadgeClass(project.project_status)" class="badge">
                          {{ project.project_status || 'Unknown' }}
                        </span>
                      </td>
                      <td>{{ formatDate(project.start_date) }}</td>
                      <td>{{ formatDate(project.end_date) }}</td>
                    </tr>
                  </template>
                  <tr v-else>
                    <td colspan="5" class="text-center py-5">
                      <div class="empty-state">
                        <i class="zmdi zmdi-folder zmdi-hc-3x mb-3"></i>
                        <p class="mb-0">No projects available</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import { mapActions, mapState, mapWritableState } from "pinia";
import { store } from "../store";
import setup from "../setup";

export default {
  data: () => ({
    setup,
    currentTheme: 'dark',
  }),
  created: async function () {
    let id = (this.param_id = this.$route.params.id);
    await this.get_data(id);
    this.detectTheme();
  },
  mounted() {
    // Listen for theme changes
    if (window.matchMedia) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      mediaQuery.addListener(this.handleThemeChange);
    }
  },
  beforeUnmount() {
    // Clean up listener
    if (window.matchMedia) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      mediaQuery.removeListener(this.handleThemeChange);
    }
  },
  methods: {
    ...mapActions(store, {
      details: "details",
    }),
    get_data: async function (slug) {
      this.item = {};
      await this.details(slug);
    },
    getSocialMediaIcon(socialMediaName) {
      const iconMap = {
        Facebook: "zmdi zmdi-facebook bg-facebook text-white",
        Instagram: "zmdi zmdi-instagram bg-instagram text-white",
        Twitter: "zmdi zmdi-twitter bg-twitter text-white",
        LinkedIn: "zmdi zmdi-linkedin bg-linkedin text-white",
        YouTube: "zmdi zmdi-youtube bg-youtube text-white",
        TikTok: "zmdi zmdi-link bg-tiktok text-white",
        Snapchat: "zmdi zmdi-link bg-snapchat text-dark",
        Pinterest: "zmdi zmdi-link bg-pinterest text-white",
        WhatsApp: "zmdi zmdi-link bg-whatsapp text-white",
        Telegram: "zmdi zmdi-link bg-telegram text-white",
        Discord: "zmdi zmdi-link bg-discord text-white",
        Reddit: "zmdi zmdi-link bg-reddit text-white",
        Tumblr: "zmdi zmdi-link bg-tumblr text-white",
        Twitch: "zmdi zmdi-link bg-twitch text-white",
        GitHub: "zmdi zmdi-github bg-github text-white",
        Behance: "zmdi zmdi-link bg-behance text-white",
        Dribbble: "zmdi zmdi-link bg-dribbble text-white",
        Skype: "zmdi zmdi-link bg-skype text-white",
        Viber: "zmdi zmdi-link bg-viber text-white",
        WeChat: "zmdi zmdi-link bg-wechat text-white",
        Other: "zmdi zmdi-link bg-secondary text-white",
      };

      return iconMap[socialMediaName] || "zmdi zmdi-link bg-secondary text-white";
    },
    
    getProjectStatusClass(status) {
      const statusClasses = {
        'Completed': 'table-success',
        'In Progress': 'table-warning',
        'Pending': 'table-info',
        'On Hold': 'table-secondary',
        'Cancelled': 'table-danger',
        'Planning': 'table-light'
      };
      return statusClasses[status] || '';
    },
    
    getStatusBadgeClass(status) {
      const badgeClasses = {
        'Completed': 'badge-success',
        'In Progress': 'badge-warning',
        'Pending': 'badge-info',
        'On Hold': 'badge-secondary',
        'Cancelled': 'badge-danger',
        'Planning': 'badge-light text-dark'
      };
      return badgeClasses[status] || 'badge-secondary';
    },
    
    formatDate(dateString) {
      if (!dateString) return 'N/A';
      try {
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'short',
          day: 'numeric'
        });
      } catch (error) {
        return dateString;
      }
    },
    
    detectTheme() {
      // Check for manual theme setting first
      const savedTheme = localStorage.getItem('theme');
      if (savedTheme) {
        this.currentTheme = savedTheme;
        return;
      }
      
      // Check system preference
      if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        this.currentTheme = 'dark';
      } else {
        this.currentTheme = 'light';
      }
    },
    
    handleThemeChange(e) {
      // Only auto-switch if no manual theme is set
      if (!localStorage.getItem('theme')) {
        this.currentTheme = e.matches ? 'dark' : 'light';
      }
    },
    
    setTheme(theme) {
      this.currentTheme = theme;
      localStorage.setItem('theme', theme);
    },
  },
  computed: {
    ...mapWritableState(store, {
      item: "item",
    }),

    statBoxes() {
      return [
        {
          label: "Total Project",
          value: this.item.total_project ?? 0,
          icon: "zmdi zmdi-case",
          bg: "bg-primary text-white",
        },
        {
          label: "Total Task",
          value: this.item.total_task ?? 0,
          icon: "zmdi zmdi-assignment",
          bg: "bg-warning text-dark",
        },
        {
          label: "Total Completed Task",
          value: this.item.completed_task ?? 0,
          icon: "zmdi zmdi-check-circle",
          bg: "bg-light text-dark",
        },
        {
          label: "Total Not Completed Task",
          value: this.item.incomplete_task ?? 0,
          icon: "zmdi zmdi-time",
          bg: "bg-light text-dark",
        },
        {
          label: "Total Attendance",
          value: this.item.total_attendance ?? 0,
          icon: "zmdi zmdi-calendar-check",
          bg: "bg-success text-white",
        },
        {
          label: "Total Absent",
          value: this.item.total_absent ?? 0,
          icon: "zmdi zmdi-calendar-close",
          bg: "bg-danger text-white",
        },
        // two empty placeholders
        { label: "", value: "", icon: "", bg: "bg-light" },
        { label: "", value: "", icon: "", bg: "bg-light" },
      ];
    },
  },
};
</script>

<style scoped>
/* CSS Variables for Theme Support */
.container-fluid {
  --bg-primary: #f8f9fa;
  --bg-secondary: #ffffff;
  --text-primary: #2c3e50;
  --text-secondary: #6c757d;
  --text-muted: #6c757d;
  --border-color: #e9ecef;
  --shadow-color: rgba(0, 0, 0, 0.1);
  --shadow-hover: rgba(0, 0, 0, 0.15);
  --card-bg: #ffffff;
  --table-hover: #f8f9fa;
  --detail-row-bg: #f8f9fa;
  --detail-row-hover: #e9ecef;
}

/* Dark Theme Variables */
.container-fluid[data-theme="dark"] {
  --bg-primary: #2d2d2d;
  --bg-secondary: #3a3a3a;
  --text-primary: #ffffff;
  --text-secondary: #b0b0b0;
  --text-muted: #888888;
  --border-color: #555555;
  --shadow-color: rgba(0, 0, 0, 0.3);
  --shadow-hover: rgba(0, 0, 0, 0.4);
  --card-bg: #3a3a3a;
  --table-hover: #404040;
  --detail-row-bg: #404040;
  --detail-row-hover: #4a4a4a;
}

/* Page Layout */
.container-fluid {
  background-color: var(--bg-primary) !important;
  color: var(--text-primary) !important;
  min-height: 100vh;
  transition: background-color 0.3s ease, color 0.3s ease;
}

/* Page Header */
.page-header {
  background: var(--card-bg);
  color: var(--text-primary);
  padding: 1.5rem 2rem;
  border-radius: 12px;
  box-shadow: 0 2px 10px var(--shadow-color);
  border-left: 4px solid #0d6efd;
  transition: background-color 0.3s ease, color 0.3s ease;
}

.page-title {
  font-size: 1.75rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0;
}

.breadcrumb {
  margin: 0;
  background: none;
  padding: 0;
}

.breadcrumb-item a {
  color: var(--text-secondary);
  transition: color 0.3s ease;
}

.breadcrumb-item a:hover {
  color: #0d6efd;
}

.breadcrumb-item.active {
  color: var(--text-primary);
}

/* Profile Card */
.profile-card {
  background: var(--card-bg);
  color: var(--text-primary);
  border-radius: 20px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
  border: 1px solid var(--border-color);
}

.profile-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 20px 40px var(--shadow-hover) !important;
}

.profile-header {
  padding: 0;
  margin: 0;
}

.profile-bg {
  height: 120px;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  position: relative;
}

.profile-bg::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 60px;
  background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.1));
}

.profile-image-container {
  position: relative;
  margin-top: -60px;
  z-index: 10;
}

.profile-avatar {
  position: relative;
  display: inline-block;
}

.avatar-img {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 5px solid var(--card-bg);
  object-fit: cover;
  object-position: center;
  box-shadow: 0 10px 30px var(--shadow-color);
  transition: transform 0.3s ease;
}

.profile-avatar:hover .avatar-img {
  transform: scale(1.05);
}

.rating-badge {
  position: absolute;
  bottom: 5px;
  right: 5px;
}

.rating-circle {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  box-shadow: 0 4px 15px var(--shadow-color);
  border: 3px solid var(--card-bg);
  transition: all 0.3s ease;
}

.rating-circle:hover {
  transform: scale(1.1);
  box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

.rating-value {
  font-size: 14px;
  font-weight: 700;
  line-height: 1;
}

.rating-scale {
  font-size: 8px;
  opacity: 0.8;
  line-height: 1;
}

.profile-name h3 {
  font-size: 1.5rem;
  color: var(--text-primary);
}

.profile-name p {
  color: var(--text-secondary);
}

/* Rating Colors */
.rating-circle[data-rating="0"],
.rating-circle[data-rating="1"],
.rating-circle[data-rating="2"] {
  background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
}

.rating-circle[data-rating="3"],
.rating-circle[data-rating="4"] {
  background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
}

.rating-circle[data-rating="5"],
.rating-circle[data-rating="6"] {
  background: linear-gradient(135deg, #f1c40f 0%, #f39c12 100%);
  color: #2c3e50;
}

.rating-circle[data-rating="7"],
.rating-circle[data-rating="8"] {
  background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
}

.rating-circle[data-rating="9"],
.rating-circle[data-rating="10"] {
  background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
}

/* Profile Details */
.profile-details {
  padding: 1rem 0;
}

.detail-row {
  display: flex;
  align-items: flex-start;
  margin-bottom: 1.5rem;
  padding: 0.75rem;
  background: var(--detail-row-bg);
  border-radius: 10px;
  transition: all 0.3s ease;
}

.detail-row:hover {
  background: var(--detail-row-hover);
  transform: translateX(5px);
}

.detail-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--card-bg);
  border-radius: 10px;
  margin-right: 1rem;
  box-shadow: 0 2px 10px var(--shadow-color);
}

.detail-content {
  flex: 1;
}

.detail-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  color: var(--text-secondary);
  margin-bottom: 0.25rem;
  letter-spacing: 0.5px;
}

.detail-value {
  font-size: 0.95rem;
  color: var(--text-primary);
  font-weight: 500;
  word-break: break-word;
}

/* Social Section */
.social-section {
  padding: 1.5rem;
  background: var(--detail-row-bg);

}

.social-header {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1rem;
}

.social-title {
  color: var(--text-primary);
  font-weight: 600;
  font-size: 0.9rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.social-content {
  text-align: center;
}

.social-links {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.social-link {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  text-decoration: none;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
  overflow: hidden;
  border: 3px solid transparent;
  margin: 0.25rem;
  padding: 0;
}

.social-link:hover {
  transform: translateY(-6px) scale(1.1);
  box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
  border-color: rgba(255, 255, 255, 0.3);
}

.social-link:active {
  transform: translateY(-3px) scale(1.05);
}

.social-link i {
  font-size: 1.2rem;
  line-height: 1;
  transition: all 0.3s ease;
  filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.social-link:hover i {
  transform: scale(1.1);
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.3));
}

/* Social Tooltip */
.social-tooltip {
  position: absolute;
  bottom: -35px;
  left: 50%;
  transform: translateX(-50%);
  background: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 0.25rem 0.5rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 500;
  white-space: nowrap;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  z-index: 10;
  pointer-events: none;
}

.social-tooltip::before {
  content: '';
  position: absolute;
  top: -4px;
  left: 50%;
  transform: translateX(-50%);
  border-left: 4px solid transparent;
  border-right: 4px solid transparent;
  border-bottom: 4px solid rgba(0, 0, 0, 0.8);
}

.social-link:hover .social-tooltip {
  opacity: 1;
  visibility: visible;
  bottom: -40px;
}

/* No Social State */
.no-social-state {
  padding: 2rem 1rem;
  text-align: center;
}

.no-social-state i {
  opacity: 0.4;
  margin-bottom: 0.5rem;
}

.no-social-state p {
  margin: 0;
  font-style: italic;
}

/* Stats Cards */
.stats-row {
  margin-left: -0.75rem;
  margin-right: -0.75rem;
}

.stats-col {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
  margin-bottom: 1.5rem;
}

.stats-card {
  background: var(--card-bg);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  border-radius: 16px;
  transition: all 0.3s ease;
}

.stats-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 35px var(--shadow-hover) !important;
}

/* Force horizontal gaps for stats cards */
.row.g-4 > [class*="col-"] {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.row.g-4 > [class*="col-"]:first-child {
  padding-left: 0;
}

.row.g-4 > [class*="col-"]:last-child {
  padding-right: 0;
}

.stats-content {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.stats-icon {
  width: 60px;
  height: 60px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.stats-icon i {
  font-size: 1.5rem;
}

.stats-info {
  flex: 1;
  min-width: 0;
}

.stats-number {
  font-size: 1.75rem;
  font-weight: 700;
  margin: 0;
  line-height: 1.2;
}

.stats-label {
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--text-secondary);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Table Styles */
.table {
  font-size: 0.9rem;
  color: var(--text-primary);
}

.table thead th {
  background: var(--detail-row-bg);
  border: none;
  color: var(--text-primary);
  font-weight: 600;
  text-transform: uppercase;
  font-size: 0.75rem;
  letter-spacing: 0.5px;
  padding: 1rem;
}

.table tbody td {
  padding: 1rem;
  vertical-align: middle;
  border-color: var(--border-color);
  color: var(--text-primary);
}

.table tbody tr:hover {
  background-color: var(--table-hover);
}

.project-name {
  display: flex;
  align-items: center;
}

/* General Card Styling */
.card {
  background: var(--card-bg);
  color: var(--text-primary);
  border: 1px solid var(--border-color);
  transition: background-color 0.3s ease, color 0.3s ease;
}

.card-header {
  background: var(--card-bg) !important;
  color: var(--text-primary);
  border-bottom: 1px solid var(--border-color) !important;
}

.card-title {
  color: var(--text-primary);
}

.card-footer {
  background: var(--detail-row-bg) !important;
  border-top: 1px solid var(--border-color) !important;
}

/* Status Badges */
.badge {
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.5rem 0.75rem;
  border-radius: 20px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.badge-success {
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
}

.badge-warning {
  background-color: #fff3cd;
  color: #856404;
  border: 1px solid #ffeaa7;
}

.badge-info {
  background-color: #d1ecf1;
  color: #0c5460;
  border: 1px solid #bee5eb;
}

.badge-secondary {
  background-color: #e2e3e5;
  color: #383d41;
  border: 1px solid #d6d8db;
}

.badge-danger {
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
}

/* Empty State */
.empty-state {
  padding: 2rem;
  color: var(--text-muted);
}

.empty-state i {
  opacity: 0.5;
  color: var(--text-muted);
}

.empty-state p {
  color: var(--text-muted);
}

/* Dark Theme Badge Overrides */
.container-fluid[data-theme="dark"] .badge-success {
  background-color: rgba(40, 167, 69, 0.2);
  color: #28a745;
  border: 1px solid rgba(40, 167, 69, 0.3);
}

.container-fluid[data-theme="dark"] .badge-warning {
  background-color: rgba(255, 193, 7, 0.2);
  color: #ffc107;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.container-fluid[data-theme="dark"] .badge-info {
  background-color: rgba(23, 162, 184, 0.2);
  color: #17a2b8;
  border: 1px solid rgba(23, 162, 184, 0.3);
}

.container-fluid[data-theme="dark"] .badge-secondary {
  background-color: rgba(108, 117, 125, 0.2);
  color: #6c757d;
  border: 1px solid rgba(108, 117, 125, 0.3);
}

.container-fluid[data-theme="dark"] .badge-danger {
  background-color: rgba(220, 53, 69, 0.2);
  color: #dc3545;
  border: 1px solid rgba(220, 53, 69, 0.3);
}

/* Override Bootstrap Text Classes for Dark Theme */
.container-fluid[data-theme="dark"] .text-dark,
.container-fluid[data-theme="dark"] .text-muted,
.container-fluid[data-theme="dark"] .fw-medium,
.container-fluid[data-theme="dark"] .fw-semibold,
.container-fluid[data-theme="dark"] h1, .container-fluid[data-theme="dark"] h2, .container-fluid[data-theme="dark"] h3, 
.container-fluid[data-theme="dark"] h4, .container-fluid[data-theme="dark"] h5, .container-fluid[data-theme="dark"] h6,
.container-fluid[data-theme="dark"] p, .container-fluid[data-theme="dark"] span {
  color: var(--text-primary) !important;
}

.container-fluid[data-theme="dark"] .text-muted {
  color: var(--text-muted) !important;
}

/* Responsive Design */
@media (max-width: 1200px) {
  .stats-content {
    flex-direction: column;
    text-align: center;
    gap: 0.75rem;
  }
  
  .stats-icon {
    width: 50px;
    height: 50px;
  }
  
  .stats-number {
    font-size: 1.5rem;
  }
}

@media (max-width: 768px) {
  .page-header {
    padding: 1rem;
  }
  
  .page-title {
    font-size: 1.5rem;
  }
  
  .avatar-img {
    width: 100px;
    height: 100px;
  }
  
  .rating-circle {
    width: 35px;
    height: 35px;
  }
  
  .rating-value {
    font-size: 12px;
  }
  
  .rating-scale {
    font-size: 7px;
  }
  
  .detail-row {
    padding: 0.5rem;
  }
  
  .detail-icon {
    width: 35px;
    height: 35px;
  }
  
  .stats-number {
    font-size: 1.25rem;
  }
  
  .table {
    font-size: 0.8rem;
  }
  
  .table thead th,
  .table tbody td {
    padding: 0.75rem 0.5rem;
  }
}

@media (max-width: 576px) {
  .container-fluid {
    padding: 1rem;
  }
  
  .social-link {
    width: 44px;
    height: 44px;
    margin: 0.2rem;
    padding: 0;
  }
  
  .social-link i {
    font-size: 1.1rem;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
  }
  
  .social-links {
    gap: 0.3rem;
    padding: 0.3rem;
  }
  
  .social-section {
    padding: 1rem;
  }
  
  .social-tooltip {
    font-size: 0.7rem;
    padding: 0.2rem 0.4rem;
  }
  
  .social-link:hover {
    transform: translateY(-4px) scale(1.05);
  }
  
  .social-link:hover i {
    transform: scale(1.05);
  }
}

/* Social Media Background Colors - Modern Vibrant Theme */
.bg-facebook { 
  background: radial-gradient(circle at 30% 30%, #4267B2, #365899) !important;
  box-shadow: 0 6px 20px rgba(66, 103, 178, 0.4) !important;
}
.bg-instagram { 
  background: radial-gradient(circle at 30% 30%, #833ab4, #fd1d1d, #fcb045) !important;
  box-shadow: 0 6px 20px rgba(131, 58, 180, 0.4) !important;
}
.bg-twitter { 
  background: radial-gradient(circle at 30% 30%, #1DA1F2, #0d8bd9) !important;
  box-shadow: 0 6px 20px rgba(29, 161, 242, 0.4) !important;
}
.bg-linkedin { 
  background: radial-gradient(circle at 30% 30%, #0077B5, #005885) !important;
  box-shadow: 0 6px 20px rgba(0, 119, 181, 0.4) !important;
}
.bg-youtube { 
  background: radial-gradient(circle at 30% 30%, #FF0000, #cc0000) !important;
  box-shadow: 0 6px 20px rgba(255, 0, 0, 0.4) !important;
}
.bg-tiktok { 
  background: radial-gradient(circle at 30% 30%, #000000, #ff0050) !important;
  box-shadow: 0 6px 20px rgba(255, 0, 80, 0.4) !important;
}
.bg-snapchat { 
  background: radial-gradient(circle at 30% 30%, #FFFC00, #fff700) !important;
  box-shadow: 0 6px 20px rgba(255, 252, 0, 0.4) !important;
  color: #000 !important; 
}
.bg-pinterest { 
  background: radial-gradient(circle at 30% 30%, #E60023, #bd081c) !important;
  box-shadow: 0 6px 20px rgba(230, 0, 35, 0.4) !important;
}
.bg-whatsapp { 
  background: radial-gradient(circle at 30% 30%, #25D366, #128c7e) !important;
  box-shadow: 0 6px 20px rgba(37, 211, 102, 0.4) !important;
}
.bg-telegram { 
  background: radial-gradient(circle at 30% 30%, #0088CC, #005f8a) !important;
  box-shadow: 0 6px 20px rgba(0, 136, 204, 0.4) !important;
}
.bg-discord { 
  background: radial-gradient(circle at 30% 30%, #7289DA, #5b6eae) !important;
  box-shadow: 0 6px 20px rgba(114, 137, 218, 0.4) !important;
}
.bg-reddit { 
  background: radial-gradient(circle at 30% 30%, #FF4500, #cc3700) !important;
  box-shadow: 0 6px 20px rgba(255, 69, 0, 0.4) !important;
}
.bg-tumblr { 
  background: radial-gradient(circle at 30% 30%, #00CF35, #00a62a) !important;
  box-shadow: 0 6px 20px rgba(0, 207, 53, 0.4) !important;
}
.bg-twitch { 
  background: radial-gradient(circle at 30% 30%, #9146FF, #7329cc) !important;
  box-shadow: 0 6px 20px rgba(145, 70, 255, 0.4) !important;
}
.bg-github { 
  background: radial-gradient(circle at 30% 30%, #333333, #1a1a1a) !important;
  box-shadow: 0 6px 20px rgba(51, 51, 51, 0.4) !important;
}
.bg-behance { 
  background: radial-gradient(circle at 30% 30%, #1769FF, #0d4fd1) !important;
  box-shadow: 0 6px 20px rgba(23, 105, 255, 0.4) !important;
}
.bg-dribbble { 
  background: radial-gradient(circle at 30% 30%, #EA4C89, #c73670) !important;
  box-shadow: 0 6px 20px rgba(234, 76, 137, 0.4) !important;
}
.bg-skype { 
  background: radial-gradient(circle at 30% 30%, #00AFF0, #0085c7) !important;
  box-shadow: 0 6px 20px rgba(0, 175, 240, 0.4) !important;
}
.bg-viber { 
  background: radial-gradient(circle at 30% 30%, #665CAC, #4f4788) !important;
  box-shadow: 0 6px 20px rgba(102, 92, 172, 0.4) !important;
}
.bg-wechat { 
  background: radial-gradient(circle at 30% 30%, #7BB32E, #5f8f24) !important;
  box-shadow: 0 6px 20px rgba(123, 179, 46, 0.4) !important;
}

/* Enhanced Hover Effects for Social Icons */
.social-link:hover.bg-facebook {
  box-shadow: 0 12px 35px rgba(66, 103, 178, 0.6) !important;
}
.social-link:hover.bg-instagram {
  box-shadow: 0 12px 35px rgba(131, 58, 180, 0.6) !important;
}
.social-link:hover.bg-twitter {
  box-shadow: 0 12px 35px rgba(29, 161, 242, 0.6) !important;
}
.social-link:hover.bg-linkedin {
  box-shadow: 0 12px 35px rgba(0, 119, 181, 0.6) !important;
}
.social-link:hover.bg-youtube {
  box-shadow: 0 12px 35px rgba(255, 0, 0, 0.6) !important;
}
.social-link:hover.bg-tiktok {
  box-shadow: 0 12px 35px rgba(255, 0, 80, 0.6) !important;
}
.social-link:hover.bg-twitch {
  box-shadow: 0 12px 35px rgba(145, 70, 255, 0.6) !important;
}
.social-link:hover.bg-github {
  box-shadow: 0 12px 35px rgba(51, 51, 51, 0.6) !important;
}

/* Dark Theme Social Media Adjustments */
.container-fluid[data-theme="dark"] .social-link {
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.6);
  border-color: rgba(255, 255, 255, 0.1);
}

.container-fluid[data-theme="dark"] .social-link:hover {
  box-shadow: 0 12px 35px rgba(0, 0, 0, 0.8);
  border-color: rgba(255, 255, 255, 0.2);
}

.container-fluid[data-theme="dark"] .social-tooltip {
  background: rgba(255, 255, 255, 0.95);
  color: #2c3e50;
  backdrop-filter: blur(10px);
}

.container-fluid[data-theme="dark"] .social-tooltip::before {
  border-bottom-color: rgba(255, 255, 255, 0.95);
}

/* Additional Modern Effects */
.social-link {
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.social-link::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.1);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.social-link:hover::before {
  opacity: 1;
}

/* Improved Social Links Container */
.social-links {
  display: flex;
  justify-content: center;
  flex-wrap: wrap;
  gap: 0.5rem;
  padding: 0.5rem;
}

/* Bootstrap Color Variants */
.bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1) !important; }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1) !important; }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1) !important; }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1) !important; }
.bg-info-subtle { background-color: rgba(13, 202, 240, 0.1) !important; }
.bg-secondary-subtle { background-color: rgba(108, 117, 125, 0.1) !important; }
</style>
