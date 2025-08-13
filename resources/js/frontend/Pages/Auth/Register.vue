<template>
  <Head>
    <title>Register</title>
  </Head>
  <Layout>
    <div class="professional-login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="brand-section">
            <div class="brand-icon">
              <i class="fas fa-user-plus"></i>
            </div>
            <h2 class="brand-title">Project Management</h2>
            <p class="brand-subtitle">Create your account</p>
          </div>
        </div>
        <form @submit.prevent="RegisterSubmitHandler" class="login-form">
          <div class="form-group">
            <label for="name" class="form-label">
              <i class="fas fa-user"></i>
              Name
            </label>
            <input
              class="form-control"
              type="text"
              placeholder="Enter your name"
              name="name"
              v-model="name"
              required
            />
            <p class="alert-danger" id="name"></p>
          </div>
          <div class="form-group">
            <label for="phone" class="form-label">
              <i class="fas fa-phone"></i>
              Phone
            </label>
            <input
              class="form-control"
              type="text"
              placeholder="Enter your phone"
              name="phone_number"
              v-model="phone_number"
              required
            />
            <p class="alert-danger" id="phone"></p>
          </div>
          <div class="form-group">
            <label for="email" class="form-label">
              <i class="fas fa-envelope"></i>
              Email
            </label>
            <input
              class="form-control"
              type="email"
              placeholder="Enter your email"
              name="email"
              v-model="email"
              required
            />
            <p class="alert-danger" id="email"></p>
          </div>
          <div class="form-group">
            <label for="password" class="form-label">
              <i class="fas fa-lock"></i>
              Password
            </label>
            <input
              class="form-control"
              type="password"
              placeholder="Enter your password"
              name="password"
              v-model="password"
              required
            />
            <p class="alert-danger" id="password"></p>
          </div>
          <button class="login-button" type="submit">
            <span class="button-content">
              <i class="fas fa-user-plus"></i>
              Register
            </span>
          </button>
        </form>
        <div class="login-footer">
          <p class="footer-text">
            Already have an account?
            <Link href="/login" class="signup-link">Login</Link>
          </p>
        </div>
      </div>
    </div>
  </Layout>
</template>

<script>
import Layout from "./Layout/Layout.vue";
import { Link } from "@inertiajs/vue3";
export default {
  components: { Layout, Link },
  data() {
    return {
      name: "",
      phone_number: "",
      email: "",
      password: "",
      showPassword: false,
      loading: false,
    };
  },
  methods: {
    RegisterSubmitHandler: async function () {
      try {
        this.loading = true;
        let formData = new FormData(event.target);
        let response = await axios.post("/register", formData);
        console.log(response);

        if (response.data?.status === "success") {
          let data = response.data?.data;
          if (data.access_token) {
            window.s_alert("Register Successfully");
            localStorage.setItem("admin_token", data.access_token);
            localStorage.setItem("admin_role", data.user?.role_id);
            if (data.user?.role_id == 1) {
              window.location.href = "super-admin#/dashboard";
            } else if (data.user?.role_id == 2) {
              window.location.href = "employee#/dashboard";
            } else {
              window.location.href = "/";
            }
          }
        }
      } catch (error) {
        console.error("Login error", error);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

