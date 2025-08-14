<template>
  <div id="wrapper" v-if="is_auth">
    <TopHeader :key="headerKey" :headerKey="headerKey" @update:headerKey="headerKey = $event"></TopHeader>
    <div class="clearfix"></div>
    <div class="content-wrapper">
      <router-view></router-view>
    </div>
    <!--End content-wrapper-->

    <!--start color switcher-->
    <Footer></Footer>
    <!--end color switcher-->
  </div>
</template>

<script>
import TopHeader from "../Layouts/Partials/Header/Index.vue";
import Footer from "../Layouts/Partials/Footer/Index.vue";
//auth_store
import { auth_store } from "../../../GlobalStore/auth_store";
import { mapActions, mapState } from "pinia";
export default {
  components: { TopHeader, Footer },
  data: () => ({
    rightToggle: false,
    headerKey: 0,
  }),
  created: async function () {
    await this.check_is_auth();
  },
  methods: {
    ...mapActions(auth_store, {
      check_is_auth: "check_is_auth",
    }),
    changeTheme(id) {
      const totalThemes = Array.from({ length: 15 }, (_, i) => i + 1);
      const newThemeNo = "bg-theme" + id;
      const body = document.getElementById("body");

      totalThemes.forEach((item) => {
        const currentThemeClass = "bg-theme" + item;
        if (body.classList.contains(currentThemeClass)) {
          body.classList.remove(currentThemeClass);
        }
      });

      body.classList.add(newThemeNo);
    },
  },

  computed: {
    ...mapState(auth_store, {
      auth_info: "auth_info",
      is_auth: "is_auth",
    }),
  },
};
</script>

<style scoped>
.content-wrapper {
  margin-left: 0px !important;
  padding-left: 0px;
  padding-right: 0px;
}
</style>
