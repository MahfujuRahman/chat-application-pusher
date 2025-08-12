<template>
  <div class="professional-login-container">
    <div class="login-card">
      <div class="login-header">
        <div class="brand-section">
          <div class="brand-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h2 class="brand-title">Verify Code</h2>
          <p class="brand-subtitle">Enter the 6-digit code sent to your email</p>
        </div>
      </div>
      
      <div v-if="step_one">
        <form @submit.prevent="VerifyCodeSubmitHandler" class="login-form">
          <div class="form-group">
            <label class="form-label verification-email-label">
              <i class="fas fa-envelope"></i>
              Verification code sent to: <span class="email-highlight">{{ email }}</span>
            </label>
            
            <div class="verification-code-container">
              <input 
                v-for="(value, index) in codeFields" 
                :key="index" 
                ref="codeInput"
                class="verification-input"
                type="text" 
                v-model="codeFields[index]" 
                @input="moveFocus(index)"
                @keydown="handleKeydown($event, index)" 
                @paste="handlePaste($event)" 
                maxlength="1"
                autocomplete="off"
              />
            </div>
          </div>

          <button
            class="login-button"
            type="submit"
            :disabled="loading || !isCodeComplete"
          >
            <span v-if="!loading" class="button-content">
              <i class="fas fa-check-circle"></i>
              Verify Code
            </span>
            <span v-if="loading" class="button-content loading">
              <div class="spinner"></div>
              Verifying...
            </span>
          </button>
        </form>
        
        <div class="login-footer">
          <p class="footer-text">
            Didn't receive the code? 
            <a href="#" class="signup-link" @click.prevent="resendCode">Resend Code</a>
          </p>
        </div>
      </div>

      <template v-if="step_two">
        <reset-password :email="email"></reset-password>
      </template>
    </div>
  </div>
</template>

<script>
import ResetPassword from './ResetPassword.vue';


export default {
    components: { ResetPassword },

    props: ['email'],

    data() {
        return {
            codeFields: Array(6).fill(""),
            step_one: true,
            step_two: false,
            loading: false
        };
    },

    computed: {
        isCodeComplete() {
            return this.codeFields.every(field => field !== "");
        }
    },
    methods: {
        moveFocus(index) {
            if (this.codeFields[index] && index < 5) {
                this.$refs.codeInput[index + 1].focus();
            }
        },
        handleKeydown(event, index) {
            if (event.key === "Backspace" && this.codeFields[index] === "" && index > 0) {
                this.$refs.codeInput[index - 1].focus();
            }
        },
        handlePaste(event) {
            const pastedData = event.clipboardData.getData("Text");
            if (pastedData.length === 6 && /^\d+$/.test(pastedData)) {
                this.codeFields = pastedData.split("");
                this.$nextTick(() => {
                    this.$refs.codeInput[5].focus();
                });
            }
            event.preventDefault();
        },
        VerifyCodeSubmitHandler: async function () {
            try {
                this.loading = true
                let formData = {
                    "code": this.codeFields,
                    "email": this.email,
                };
                let response = await axios.post('/verify-otp', formData);
                if (response.data?.status === 'success') {
                    window.s_alert(response.data?.message);
                    this.step_one = false;
                    this.step_two = true;
                }
            } catch (error) {
                console.error("Verification error", error);
                window.s_alert("Verification failed. Please try again.", "error");
            } finally {
                this.loading = false
            }
        },

        resendCode() {
            window.s_alert("Resend functionality will be implemented soon.", "info");
        }
    }
};
</script>

<style scoped>
/* Verification Code Specific Styles */
.verification-email-label {
    justify-content: center;
    text-align: center;
    margin-bottom: 20px;
    flex-direction: column;
    gap: 8px;
}

.email-highlight {
    color: #6366f1;
    font-weight: 700;
    font-size: 16px;
}

.verification-code-container {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin: 30px 0;
}

.verification-input {
    width: 50px;
    height: 60px;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    color: #000000 !important;
    background: white;
    transition: all 0.3s ease;
}

.verification-input:focus {
    outline: none;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    transform: translateY(-2px);
    background: white;
}

.verification-input:valid {
    border-color: #10b981;
    background: #f0fdf4;
}

/* Mobile Responsiveness for Verification */
@media (max-width: 480px) {
    .verification-code-container {
        gap: 8px;
    }
    
    .verification-input {
        width: 40px;
        height: 50px;
        font-size: 20px;
    }
}

/* Animation for verification inputs */
.verification-input {
    animation: fadeInUp 0.4s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

