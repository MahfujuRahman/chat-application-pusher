<template>
  <!--Start topbar header-->

  <header class="topbar-nav">
    <nav class="navbar navbar-expand fixed-top">
      <span class="ml-2 font-weight-bold" style="font-size:1.5rem;letter-spacing:2px;
        background: linear-gradient(90deg, rgb(186 196 255) 0%, rgb(72 206 223) 50%, rgb(227 190 135) 100%) text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 1px 1px 4px rgba(63,81,181,0.15);">
        ChatZone
      </span>

      <div class="search-bar flex-grow-1"></div>

      <ul class="navbar-nav align-items-center right-nav-link ml-auto">
        <li class="nav-item">
          <router-link :to="{ name: 'ConversationMessage' }" class="btn nav-link position-relative"
            title="Go to Messages" @click="resetUnreadMessageCount">
            <i class="zmdi zmdi-comment-outline align-middle"></i>
            <span v-if="unreadMessageCount > 0" class="bg-danger text-white badge-up">{{ unreadMessageCount }}</span>
          </router-link>
        </li>


        <li class="nav-item dropdown" @click="toggle_notification('show_profile')">
          <a class="btn nav-link dropdown-toggle dropdown-toggle-nocaret position-relative">
            <span class="user-profile">
              <img :src="auth_info.image ? auth_info.image : 'avatar.png'" @error="$event.target.src = 'avatar.png'"
                class="img-circle" alt="user avatar" />
            </span>
          </a>
          <ul class="dropdown-menu dropdown-menu-right" :class="{ show: show_profile }">
            <li class="dropdown-item user-details">
              <a href="javaScript:void();">
                <div class="media">
                  <div class="avatar">
                    <img class="align-self-start mr-3" :src="`${auth_info.image ?? 'avatar.png'}`" alt="user avatar"
                      @error="$event.target.src = 'avatar.png'" />
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
            <li>
              <router-link class="dropdown-item" :to="{ name: 'AdminProfileSettings' }">
                <i class="zmdi zmdi-accounts mr-3"></i>Profile
              </router-link>
            </li>
            <li>
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

.toggle-menu {
  margin-left: 0px !important;
  padding-left: 0px;
  padding-right: 0px;
}
</style>
