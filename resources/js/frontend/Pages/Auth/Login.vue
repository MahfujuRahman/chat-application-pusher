<template>
  <Head>
    <title> Login</title>
  </Head>
  <Layout>
    <div class="professional-login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="brand-section">
            <div class="brand-icon">
              <i class="fas fa-shield-alt"></i>
            </div>
            <h2 class="brand-title">ChatZone</h2>
            <p class="brand-subtitle">Welcome back! Please sign in to your account</p>
          </div>
        </div>

        <form @submit.prevent="LoginSubmitHandler" class="login-form">
          <div class="form-group">
            <label for="email" class="form-label">
              <i class="fas fa-envelope"></i>
              Email Address
            </label>
            <input id="email" class="form-control" type="email" placeholder="Enter your email" name="email" v-model="email" required />
          </div>

          <div class="form-group">
            <label for="password" class="form-label">
              <i class="fas fa-lock"></i>
              Password
            </label>
            <div class="password-input-wrapper">
              <input
                id="password"
                class="form-control"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Enter your password"
                name="password"
                v-model="password"
                required
              />
              <button
                type="button"
                class="password-toggle"
                @click="showPassword = !showPassword"
                :title="showPassword ? 'Hide password' : 'Show password'"
              >
                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>

          <div class="form-options">
            <div class="remember-me">
              <input type="checkbox" id="rememberMe" v-model="rememberMe" class="checkbox-input" />
              <label for="rememberMe" class="checkbox-label"> Remember me </label>
            </div>
            <Link href="/forgot-password" class="forgot-password-link"> Forgot password? </Link>
          </div>

          <button class="login-button" type="submit" :disabled="loading || !email || !password">
            <span v-if="!loading" class="button-content">
              <i class="fas fa-sign-in-alt"></i>
              Sign In
            </span>
            <span v-if="loading" class="button-content loading">
              <div class="spinner"></div>
              Signing in...
            </span>
          </button>
        </form>

        <div class="login-footer">
          <p class="footer-text">
            Don't have an account?
            <Link href="/register" class="signup-link">Register</Link>
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
      loading: false,
      showPassword: false,
      passwordError: false,
      email: "",
      password: "",
      rememberMe: false,
    };
  },

  async mounted() {
    this.loadRememberedCredentials();
    // If an admin token exists, validate it and redirect to conversation
    const token = localStorage.getItem('admin_token');
    if (token) {
      // set temporary auth header for validation
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
      try {
        const resp = await axios.get('/check_user');
        if (resp.data?.status === 'success') {
          const prev_url = window.sessionStorage.getItem('prevurl');
          let redirectTo;
          if (prev_url && !prev_url.includes('/login')) {
            // normalize prev_url to an absolute URL when possible
            if (prev_url.startsWith('http')) {
              redirectTo = prev_url;
            } else if (prev_url.startsWith('/')) {
              // already a root path
              redirectTo = window.location.origin + prev_url;
            } else if (prev_url.startsWith('#')) {
              // hash-only routes should be resolved under /super-admin
              redirectTo = window.location.origin + '/super-admin' + prev_url;
            } else {
              redirectTo = window.location.origin + '/' + prev_url;
            }
          } else {
            redirectTo = window.location.origin + '/super-admin#/message/conversation';
          }
          window.location.href = redirectTo;
          return;
        } else {
          // invalid token — remove and continue to login
          localStorage.removeItem('admin_token');
        }
      } catch (err) {
        console.warn('Token validation failed, clearing token', err);
        localStorage.removeItem('admin_token');
      } finally {
        // clear temporary header
        delete axios.defaults.headers.common['Authorization'];
      }
    }

    // If app store indicates authenticated and auth_info is loaded, redirect
    if (this.is_auth && this.auth_info) {
      const prev_url = window.sessionStorage.getItem('prevurl');
      let redirectTo;
      if (prev_url && !prev_url.includes('/login')) {
        if (prev_url.startsWith('http')) {
          redirectTo = prev_url;
        } else if (prev_url.startsWith('/')) {
          redirectTo = window.location.origin + prev_url;
        } else if (prev_url.startsWith('#')) {
          redirectTo = window.location.origin + '/super-admin' + prev_url;
        } else {
          redirectTo = window.location.origin + '/' + prev_url;
        }
      } else {
        redirectTo = window.location.origin + '/super-admin#/message/conversation';
      }
      window.location.href = redirectTo;
    }
  },

  methods: {
    LoginSubmitHandler: async function () {
      try {
        this.loading = true;

        // Handle remember me functionality
        if (this.rememberMe) {
          this.saveCredentials();
        } else {
          this.clearSavedCredentials();
        }

        let formData = new FormData();
        formData.append("email", this.email);
        formData.append("password", this.password);
        formData.append("remember", this.rememberMe);

        let response = await axios.post("/login", formData);
        if (response.data?.status === "success") {
          let data = response.data?.data;
          if (data.access_token) {
            window.s_alert("Login Successfully");
            localStorage.setItem("admin_token", data.access_token);
            if (data.user) {
              window.location.href = "super-admin#/message/conversation";
            } else {
              window.location.href = "login";
            }
          }
        }
      } catch (error) {
        console.error("Login error", error);
        window.s_alert("Login failed. Please check your credentials.", "error");
      } finally {
        this.loading = false;
      }
    },

    saveCredentials() {
      const credentials = {
        email: this.email,
        password: this.password,
        rememberMe: this.rememberMe,
      };
      localStorage.setItem("rememberedCredentials", JSON.stringify(credentials));
    },

    clearSavedCredentials() {
      localStorage.removeItem("rememberedCredentials");
    },

    loadRememberedCredentials() {
      const savedCredentials = localStorage.getItem("rememberedCredentials");
      if (savedCredentials) {
        try {
          const credentials = JSON.parse(savedCredentials);
          this.email = credentials.email || "";
          this.password = credentials.password || "";
          this.rememberMe = credentials.rememberMe || false;
        } catch (error) {
          console.error("Error loading remembered credentials:", error);
          this.clearSavedCredentials();
        }
      }
    },

    setPassword(email) {
      this.email = email;
    },
  },
};
</script>
