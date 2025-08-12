<template>
  <!--Start topbar header-->

  <header class="topbar-nav">
    <nav class="navbar navbar-expand fixed-top">
      <div class="toggle-menu">
        <i
          @click.prevent="toggle_menu"
          class="zmdi zmdi-menu"
          title="toggle menu"
        ></i>
      </div>

      <a
        title="Go to website"
        href="/"
        target="_blank"
        class="d-flex align-items-center justify-content-center mx-2"
        style="background: rgba(255, 255, 255, 0.12); width: 30px; height: 30px"
      >
        <i class="zmdi zmdi-globe mt-1"></i>
      </a>

      <a
        title="Reload Page"
        href="javascript:void(0)"
        class="d-flex align-items-center justify-content-center mx-2"
        style="background: rgba(255, 255, 255, 0.12); width: 30px; height: 30px"
        @click="reload_page"
      >
        <i class="zmdi zmdi-replay mt-1"></i>
      </a>

      <div class="search-bar flex-grow-1"></div>

      <ul class="navbar-nav align-items-center right-nav-link ml-auto">
        <li class="nav-item">
          <router-link
            :to="{ name: 'ConversationMessage' }"
            class="btn nav-link position-relative"
            title="Go to Messages"
            @click="resetUnreadMessageCount"
          >
            <i class="zmdi zmdi-comment-outline align-middle"></i>
            <span v-if="unreadMessageCount > 0" class="bg-danger text-white badge-up">{{ unreadMessageCount }}</span>
          </router-link>
          <!-- <div class="dropdown-menu dropdown-menu-right" :class="{ show: show_message }">
            <ul class="list-group list-group-flush">
              <li class="list-group-item d-flex justify-content-between align-items-center">
                New Messages
                <a href="javascript:void();" class="extra-small-font">Clear All</a>
              </li>
              <li class="list-group-item">
                <a href="javaScript:void();">
                  <div class="media">
                    <div class="avatar">
                      <img class="align-self-start mr-3" src="avatar.png" alt="user avatar" />
                    </div>
                    <div class="media-body">
                      <h6 class="mt-0 msg-title">Jhon Deo</h6>
                      <p class="msg-info">Lorem ipsum dolor sit amet...</p>
                      <small>Today, 4:10 PM</small>
                    </div>
                  </div>
                </a>
              </li>

              <li class="list-group-item text-center">
                <a href="javaScript:void();">See All Messages</a>
              </li>
            </ul>
          </div> -->
        </li>

        <li
          class="nav-item dropdown dropdown-lg"
          @click="toggle_notification('show_notification')"
        >
          <a
            role="button"
            class="btn nav-link dropdown-toggle dropdown-toggle-nocaret position-relative"
          >
            <i class="zmdi zmdi-notifications-active align-middle"></i>
            <span class="bg-info text-white badge-up">{{
              notifications.length
            }}</span>
          </a>
          <div
            class="dropdown-menu dropdown-menu-right"
            :class="{ show: show_notification }"
          >
            <ul class="list-group list-group-flush">
              <li
                class="list-group-item d-flex justify-content-between align-items-center"
              >
                <span class="text-info font-weight-bold"> New Projects </span>
                <a
                  href="javascript:void();"
                  @click="RedirectToDestination('projects')"
                  class="extra-small-font"
                  >See All</a
                >
              </li>
              <template
                v-for="notification in notifications"
                :key="notification.id"
              >
                <li
                  class="list-group-item"
                  v-if="
                    notification.type == 'project' && notification.is_seen == 0
                  "
                >
                  <a
                    @click.prevent="
                      handleLinkClick(notification, `/${notification.link}`)
                    "
                    class="cursor-pointer"
                  >
                    <div class="media">
                      <div class="media-body">
                        <h6 class="mt-0 msg-title">
                          {{ notification.title }}
                        </h6>
                      </div>
                    </div>
                  </a>
                </li>
              </template>

              <li
                class="list-group-item d-flex justify-content-between align-items-center"
              >
                <span class="text-info font-weight-bold"> New Tasks </span>
                <a
                  href="javascript:void();"
                  @click="RedirectToDestination('tasks')"
                  class="extra-small-font"
                  >See All</a
                >
              </li>
              <template
                v-for="notification in notifications"
                :key="notification.id"
              >
                <li
                  class="list-group-item"
                  v-if="
                    notification.type == 'task' && notification.is_seen == 0
                  "
                >
                  <a
                    @click.prevent="
                      handleLinkClick(notification, `/${notification.link}`)
                    "
                    class="cursor-pointer"
                  >
                    <div class="media">
                      <div class="media-body">
                        <h6 class="mt-0 msg-title">
                          {{ notification.title }}
                        </h6>
                      </div>
                    </div>
                  </a>
                </li>
              </template>
            </ul>
          </div>
        </li>

        <li
          class="nav-item dropdown"
          @click="toggle_notification('show_profile')"
        >
          <a
            class="btn nav-link dropdown-toggle dropdown-toggle-nocaret position-relative"
          >
            <span class="user-profile">
              <img
                :src="auth_info.image ? auth_info.image : 'avatar.png'"
                @error="$event.target.src = 'avatar.png'"
                class="img-circle"
                alt="user avatar"
              />
            </span>
          </a>
          <ul
            class="dropdown-menu dropdown-menu-right"
            :class="{ show: show_profile }"
          >
            <li class="dropdown-item user-details">
              <a href="javaScript:void();">
                <div class="media">
                  <div class="avatar">
                    <img
                      class="align-self-start mr-3"
                      :src="`${auth_info.image ?? 'avatar.png'}`"
                      alt="user avatar"
                      @error="$event.target.src = 'avatar.png'"
                    />
                  </div>
                  <div class="media-body">
                    <h6 class="mt-2 user-title">
                      {{ auth_info.name }}
                    </h6>
                    <p class="user-subtitle">
                      {{ auth_info.email }}
                    </p>
                  </div>
                </div>
              </a>
            </li>
            <li class="dropdown-divider"></li>

            <li class="dropdown-divider"></li>

            <li class="dropdown-divider"></li>
            <li >
              <router-link class="dropdown-item" :to="{ name: 'AdminProfileSettings' }">
                <i class="zmdi zmdi-accounts mr-3"></i>Profile
              </router-link>
            </li>
            <li >
              <router-link class="dropdown-item" :to="{ name: 'AdminSiteSettings' }">
                <i class="zmdi zmdi-settings mr-3"></i>Settings
              </router-link>
            </li>
            <li class="dropdown-divider"></li>
            <li class="dropdown-item" @click="logout()" role="button">
              <i class="zmdi zmdi-power mr-3"></i>Logout
            </li>
          </ul>
        </li>
      </ul>
    </nav>
  </header>
  <!--End topbar header-->
</template>

<script>
//auth_store
import { auth_store } from "../../../../../GlobalStore/auth_store";
import { mapState } from "pinia";
export default {
  props: ["headerKey"],
  data: () => ({
    show_notification: 0,
    show_message: 0,
    show_profile: 0,
    notifications: [],
    unreadMessageCount: 0,
  }),

  created: async function () {
    await this.get_all_noitifications();
    await this.getUnreadMessageCount();
    this.setupMessageListener();
  },

  methods: {
    toggle_menu: function () {
      document.getElementById("wrapper").classList.toggle("toggled");
    },
    logout: async function () {
      let con = await window.s_confirm("Are you sure want to logout?");
      if (con) {
        localStorage.removeItem("admin_token");
        window.location.href = "/";
      }
    },

    reload_page() {
      // Emit an event to parent to update headerKey
      this.$emit("update:headerKey", Date.now());
      console.log("Header reload triggered (via prop)", this.headerKey);
    },

    watch: {
      headerKey(newVal, oldVal) {
        this.get_all_noitifications();
        this.getUnreadMessageCount();
        console.log("headerKey changed, notifications and messages reloaded");
      },
      // This will log when the component is re-created due to key change
      $props: {
        handler() {
          console.log(
            "Header component re-created, headerKey:",
            this.headerKey
          );
        },
        deep: true,
        immediate: true,
      },
    },

    toggle_notification: function (type) {
      if (type == "show_notification") {
        this.show_notification = this.show_notification ? 0 : 1;
        this.show_message = 0;
        this.show_profile = 0;
      } else if (type == "show_message") {
        this.show_message = this.show_message ? 0 : 1;
        this.show_notification = 0;
        this.show_profile = 0;
      } else if (type == "show_profile") {
        this.show_profile = this.show_profile ? 0 : 1;
        this.show_notification = 0;
        this.show_message = 0;
      }
    },

    get_all_noitifications: async function () {
      let response = await axios.get("notifications?not_seen=1&get_all=1");
      if (response.status == 200) {
        this.notifications = response.data.data;
      }
    },

    async getUnreadMessageCount() {
      try {
        const response = await axios.get("/messages/get-all-conversations");
        if (response.status === 200) {
          const conversations = response.data.data;
          this.unreadMessageCount = conversations.reduce((total, conversation) => {
            return total + (conversation.unread_count || 0);
          }, 0);
        }
      } catch (error) {
        console.error("Failed to get unread message count:", error);
      }
    },

    setupMessageListener() {
      const userId = this.auth_info?.id;
      if (userId && window.Echo) {
        window.Echo.private(`chat.${userId}`)
          .listen("MessageSent", (e) => {
            // Only increment count for messages from other users
            if (e.message.sender_id !== userId) {
              this.unreadMessageCount++;
            }
          })
          .error((error) => {
            console.error("❌ Header message listener error:", error);
          });
      }

      // Listen for global events when messages are marked as read
      window.addEventListener('messagesMarkedAsRead', (event) => {
        this.getUnreadMessageCount(); // Refresh the count from server
      });

      // Listen for conversation opened event to refresh count
      window.addEventListener('conversationOpened', (event) => {
        this.getUnreadMessageCount(); // Refresh the count from server
      });
    },

    resetUnreadMessageCount() {
      // Don't reset immediately, let the conversation page handle marking as read
      // The count will be updated via the event listeners above
    },

    async handleLinkClick(data, link) {
      try {
        await axios.post(`/notifications/seen`, { data });
        this.get_all_noitifications();
        if (data.type == "project") {
          this.$router.push({
            name: `DetailsProject`,
            params: { id: data.slug },
          });
        } else if (data.type == "task") {
          this.$router.push({
            name: `DetailsTasks`,
            params: { id: data.slug },
          });
        }
      } catch (e) {
        // Optionally handle error
        // this.$router.push(link);
      }
    },
    RedirectToDestination(type) {
      if (type === "tasks") {
        this.$router.push({ name: "AllTasks" });
      } else if (type === "projects") {
        this.$router.push({ name: "AllProject" });
      }
    },
  },

  mounted() {
    const navItems = document.querySelectorAll(".nav-item");

    navItems.forEach((element) => {
      element.addEventListener("click", () => {
        navItems.forEach((item) => {
          if (element !== item) {
            const dropdown = item.querySelector(".dropdown-menu");
            if (dropdown && dropdown.classList.contains("show")) {
              dropdown.classList.remove("show");
            }
          }
        });
        const dropdown = element.querySelector(".dropdown-menu");
        if (dropdown) {
          dropdown.classList.toggle("show");
        }
      });
    });
  },
  beforeUnmount() {
    // Clean up event listeners
    window.removeEventListener('messagesMarkedAsRead', this.getUnreadMessageCount);
    window.removeEventListener('conversationOpened', this.getUnreadMessageCount);
  },
  computed: {
    ...mapState(auth_store, {
      auth_info: "auth_info",
    }),
  },
};
</script>
<style>
.dropdown-menu.dropdown-menu-right.show {
  max-height: 400px;
  overflow-y: auto;
}
</style>
