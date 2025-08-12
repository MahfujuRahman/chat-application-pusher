<template>
  <Layout>
    <div class="professional-login-container">
      <div class="login-card">
        <div class="login-header">
          <div class="brand-section">
            <div class="brand-icon">
              <i class="fas fa-key"></i>
            </div>
            <h2 class="brand-title">Forgot Password</h2>
            <p class="brand-subtitle">Enter your email to receive a verification code</p>
          </div>
        </div>
        
        <div v-if="step_one">
          <form @submit.prevent="ForgotPasswordSubmitHandler" class="login-form">
            <div class="form-group">
              <label for="email" class="form-label">
                <i class="fas fa-envelope"></i>
                Email Address
              </label>
              <input
                id="email"
                class="form-control"
                type="email"
                placeholder="Enter your email address"
                name="email"
                v-model="email"
                required
              />
            </div>

            <button
              class="login-button"
              type="submit"
              :disabled="loading || !email"
            >
              <span v-if="!loading" class="button-content">
                <i class="fas fa-paper-plane"></i>
                Send Verification Code
              </span>
              <span v-if="loading" class="button-content loading">
                <div class="spinner"></div>
                Sending...
              </span>
            </button>
          </form>
          
          <div class="login-footer">
            <p class="footer-text">
              Remember your password? 
              <Link href="/login" class="signup-link">Back to Login</Link>
            </p>
          </div>
        </div>

        <template v-if="step_two">
          <verify-code :email="email"></verify-code>
        </template>
      </div>
    </div>
  </Layout>
</template>
<script>
import Layout from "../Layout/Layout.vue";
import VerifyCode from "./VerifyCode.vue";

export default {
  components: { Layout, VerifyCode },
  data() {
    return {
      loading: false,
      step_one: true,
      step_two: false,
      step_three: false,
      email: "",
    };
  },

  methods: {
    ForgotPasswordSubmitHandler: async function () {
      try {
        this.loading = true;
        let formData = new FormData(event.target);
        let response = await axios.post("/send-otp", formData);
        if (response.data?.status === "success") {
          window.s_alert(response.data?.message);
          this.step_one = false;
          this.step_two = true;
          this.email = response.data?.data?.email;
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
