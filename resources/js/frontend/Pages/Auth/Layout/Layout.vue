<template>
    <div id="authentication">
        <div class="container">
            <div class="background">
                <div class="shape"></div>
                <div class="shape"></div>
            </div>
            <div>
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
export default {

}
</script>
<style>
#authentication {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    position: relative;
    overflow: hidden;
}

#authentication::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>');
    opacity: 0.5;
}

#authentication .container {
    position: relative;
    z-index: 1;
}

#authentication .background {
    display: block;
    width: 100%;
    height: 100%;
    position: absolute;
    top: 0;
    left: 0;
    pointer-events: none;
}

#authentication .background .shape {
    height: 300px;
    width: 300px;
    position: absolute;
    border-radius: 50%;
    filter: blur(100px);
    opacity: 0.3;
}

#authentication .shape:first-child {
    background: linear-gradient(45deg, #ff6b6b, #4ecdc4);
    left: -150px;
    top: -150px;
    animation: float1 6s ease-in-out infinite;
}

#authentication .shape:last-child {
    background: linear-gradient(45deg, #a8e6cf, #ffd93d);
    right: -150px;
    bottom: -150px;
    animation: float2 8s ease-in-out infinite;
}

@keyframes float1 {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-20px) rotate(180deg); }
}

@keyframes float2 {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(20px) rotate(-180deg); }
}

/* Professional Login Container - Merged from Login.vue */
.professional-login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* Main Login Card - Merged from Login.vue */
.login-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  width: 100%;
  /* max-width: 450px; */
  overflow: hidden;
  position: relative;
}

/* Login Header - Merged from Login.vue */
.login-header {
  padding: 40px 40px 20px;
  text-align: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  position: relative;
}

.login-header::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="20" cy="20" r="1" fill="white" opacity="0.1"/><circle cx="80" cy="40" r="1" fill="white" opacity="0.1"/><circle cx="40" cy="80" r="1" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
  opacity: 0.3;
}

.brand-section {
  position: relative;
  z-index: 1;
}

.brand-icon {
  width: 70px;
  height: 70px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.brand-icon i {
  font-size: 32px;
  color: white;
}

.brand-title {
  font-size: 28px;
  font-weight: 700;
  margin: 0 0 8px;
  letter-spacing: -0.5px;
}

.brand-subtitle {
  font-size: 16px;
  opacity: 0.9;
  margin: 0;
  font-weight: 300;
}

/* Login Form - Merged from Login.vue */
.login-form {
  padding: 40px;
}

.form-group {
  margin-bottom: 25px;
}

.form-label {
  display: flex;
  align-items: center;
  font-size: 14px;
  font-weight: 600;
  color: #374151;
  margin-bottom: 8px;
  letter-spacing: 0.5px;
}

.form-label i {
  margin-right: 8px;
  color: #6366f1;
  width: 16px;
}

.form-control {
  width: 100%;
  padding: 16px 20px;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 16px;
  background: white;
  transition: all 0.3s ease;
  color: #000000 !important;
  font-weight: 600;
}

.form-control:focus {
  outline: none;
  border-color: #6366f1;
  background: white;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
  transform: translateY(-1px);
  color: #000000 !important;
}

.form-control::placeholder {
  color: #9ca3af;
  font-weight: 400;
}

/* Password Input - Merged from Login.vue */
.password-input-wrapper {
  position: relative;
}

.password-toggle {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 8px;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.password-toggle:hover {
  color: #6366f1;
  background: rgba(99, 102, 241, 0.1);
}

/* Form Options - Merged from Login.vue */
.form-options {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.remember-me {
  display: flex;
  align-items: center;
}

.checkbox-input {
  width: 18px;
  height: 18px;
  margin-right: 8px;
  accent-color: #6366f1;
  cursor: pointer;
}

.checkbox-label {
  font-size: 14px;
  color: #6b7280;
  cursor: pointer;
  user-select: none;
}

.forgot-password-link {
  font-size: 14px;
  color: #6366f1;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.2s ease;
}

.forgot-password-link:hover {
  color: #4f46e5;
}

/* Login Button - Merged from Login.vue */
.login-button {
  width: 100%;
  background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
  color: white;
  border: none;
  padding: 16px 24px;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
  position: relative;
  overflow: hidden;
}

.login-button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(99, 102, 241, 0.5);
}

.login-button:active {
  transform: translateY(0);
}

.login-button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
  box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
}

.button-content {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

.button-content.loading {
  opacity: 0.8;
}

/* Spinner - Merged from Login.vue */
.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Login Footer - Merged from Login.vue */
.login-footer {
  padding: 20px 40px 40px;
  text-align: center;
  border-top: 1px solid #e5e7eb;
  background: #f8fafc;
}

.footer-text {
  margin: 0;
  font-size: 14px;
  color: #6b7280;
}

.signup-link {
  color: #6366f1;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.2s ease;
}

.signup-link:hover {
  color: #4f46e5;
}

/* Animation for card entrance - Merged from Login.vue */
.login-card {
  animation: slideInUp 0.6s ease-out;
}

@keyframes slideInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Focus animations - Merged from Login.vue */
.form-control:focus + .form-label,
.form-control:not(:placeholder-shown) + .form-label {
  color: #6366f1;
}

/* Input validation states - Merged from Login.vue */
.form-control:invalid:not(:focus):not(:placeholder-shown) {
  border-color: #ef4444;
  background: #fef2f2;
}

.form-control:valid:not(:focus):not(:placeholder-shown) {
  border-color: #10b981;
}

/* Mobile Responsiveness - Merged from Login.vue */
@media (max-width: 768px) {
  .professional-login-container {
    padding: 15px;
  }

  .login-card {
    max-width: 100%;
  }

  .login-header {
    padding: 30px 30px 15px;
  }

  .brand-title {
    font-size: 24px;
  }

  .brand-subtitle {
    font-size: 14px;
  }

  .login-form {
    padding: 30px;
  }

  .login-footer {
    padding: 15px 30px 30px;
  }

  .form-options {
    flex-direction: column;
    gap: 15px;
    align-items: flex-start;
  }

  #authentication .shape:first-child {
    height: 200px;
    width: 200px;
    left: -100px;
    top: -100px;
  }
  
  #authentication .shape:last-child {
    height: 200px;
    width: 200px;
    right: -100px;
    bottom: -100px;
  }
}

@media (max-width: 480px) {
  .login-header {
    padding: 25px 20px 10px;
  }

  .brand-icon {
    width: 60px;
    height: 60px;
  }

  .brand-icon i {
    font-size: 28px;
  }

  .brand-title {
    font-size: 22px;
  }

  .login-form {
    padding: 25px 20px;
  }

  .login-footer {
    padding: 15px 20px 25px;
  }

  .form-control {
    padding: 14px 16px;
    font-size: 15px;
  }

  #authentication .shape:first-child {
    height: 150px;
    width: 150px;
    left: -75px;
    top: -75px;
  }
  
  #authentication .shape:last-child {
    height: 150px;
    width: 150px;
    right: -75px;
    bottom: -75px;
  }
}

/* Legacy styles preserved for backward compatibility */
#authentication #userList {
    overflow-y: auto;
    background-color: rgba(255, 255, 255, 0.13);
    backdrop-filter: blur(10px);
}

#authentication .spinner-border {
    width: 1.5rem;
    height: 1.5rem;
}

#authentication .password-icon {
    position: relative;
}

#authentication .password-icon i {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
}

#authentication .alert-warning {
    color: red;
    padding: 5px 10px;
}

#authentication #toasterMessage {
    position: absolute;
    top: 20px;
    right: 0px;
    display: none;
}

#authentication #toasterMessage p {
    color: rgb(255, 255, 255);
    background-color: #f09819;
    padding: 5px 10px;
    font-size: 20px;
}
</style>
