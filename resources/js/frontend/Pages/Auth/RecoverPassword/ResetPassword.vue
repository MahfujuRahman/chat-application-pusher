<template>
  <div class="professional-login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="brand-section">
          <div class="brand-icon">
            <i class="fas fa-lock"></i>
          </div>
          <h2 class="brand-title">Reset Password</h2>
          <p class="brand-subtitle">Create a new secure password for your account</p>
        </div>
      </div>
      
      <form @submit.prevent="ResetPasswordSubmitHandler" class="login-form">
        <div class="form-group">
          <label for="password" class="form-label">
            <i class="fas fa-key"></i>
            New Password
          </label>
          <div class="password-input-wrapper">
            <input
              id="password"
              class="form-control"
              :type="showPassword ? 'text' : 'password'"
              placeholder="Enter new password"
              name="password"
              v-model="password"
              required
              minlength="8"
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

        <div class="form-group">
          <label for="confirmPassword" class="form-label">
            <i class="fas fa-check-circle"></i>
            Confirm New Password
          </label>
          <div class="password-input-wrapper">
            <input
              id="confirmPassword"
              class="form-control"
              :type="showConfirmPassword ? 'text' : 'password'"
              placeholder="Confirm new password"
              name="confirmed"
              v-model="confirmPassword"
              required
              minlength="8"
            />
            <button
              type="button"
              class="password-toggle"
              @click="showConfirmPassword = !showConfirmPassword"
              :title="showConfirmPassword ? 'Hide password' : 'Show password'"
            >
              <i :class="showConfirmPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
            </button>
          </div>
          <div v-if="password && confirmPassword && password !== confirmPassword" class="password-mismatch">
            <i class="fas fa-exclamation-triangle"></i>
            Passwords do not match
          </div>
        </div>

        <button
          class="login-button"
          type="submit"
          :disabled="loading || !password || !confirmPassword || password !== confirmPassword"
        >
          <span v-if="!loading" class="button-content">
            <i class="fas fa-save"></i>
            Reset Password
          </span>
          <span v-if="loading" class="button-content loading">
            <div class="spinner"></div>
            Resetting...
          </span>
        </button>
      </form>
      
      <div class="login-footer">
        <p class="footer-text">
          Remember your password? 
          <a href="/login" class="signup-link">Back to Login</a>
        </p>
      </div>
    </div>
  </div>
</template>
<script>
import Layout from "../Layout/Layout.vue";
export default {
  components: { Layout },
  props: ["email"],
  data: () => ({
    showPassword: false,
    showConfirmPassword: false,
    loading: false,
    password: "",
    confirmPassword: "",
  }),
  methods: {
    ResetPasswordSubmitHandler: async function () {
      try {
        this.loading = true;
        let formData = new FormData();
        formData.append("password", this.password);
        formData.append("confirmed", this.confirmPassword);
        formData.append("email", this.email);
        
        let response = await axios.post("/reset-password", formData);
        if (response.data?.status === "success") {
          window.s_alert(response.data?.message);
          setTimeout(() => {
            window.location.href = "/login";
          }, 1500);
        }
      } catch (error) {
        console.error("Reset password error", error);
        window.s_alert("Password reset failed. Please try again.", "error");
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
/* Password Mismatch Warning */
.password-mismatch {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #ef4444;
  font-size: 14px;
  margin-top: 8px;
  font-weight: 500;
}

.password-mismatch i {
  font-size: 16px;
}

/* Password Strength Indicator */
.form-control:valid {
  border-color: #10b981;
}

.form-control:invalid:not(:placeholder-shown) {
  border-color: #ef4444;
}

/* Enhanced focus states for password fields */
.password-input-wrapper:focus-within .form-control {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

/* Animation for password validation */
.password-mismatch {
  animation: slideInDown 0.3s ease-out;
}

@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
