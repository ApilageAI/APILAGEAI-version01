{include file="components/head.tpl"}

<div class="auth-layout">
    <div class="background-accent top-right"></div>
    <div class="background-accent bottom-left"></div>
    <div class="bg-pattern"></div>

    <div class="auth-wrapper">
        <!-- Left Image Section -->
        <div class="auth-image">
            <img src="{$smarty.const.APP_URL}/assets/images/signup.jpg" alt="Signup illustration" />
        </div>

        <!-- Right Form Section -->
        <div class="auth-container">
            <div class="header animate-slide-down">
                <h1>Create your account</h1>
                <p>Sign up to get started</p>
            </div>

            <div id="loadingOverlay" style="display: none;">
                <div class="overlay-background">
                    <div class="spinner"></div>
                </div>
            </div>

            <!-- Social Login Section moved above auth-card -->
            <div class="social-login">
                <div class="social-buttons">
                  <a href="{$smarty.const.APP_URL}/auth/google" class="social-button animate-slide-up" style="animation-delay: 0.55s">
                        <i class="fa-brands fa-google"></i> <span>Google</span>
                    </a>
                    <a href="https://globbook.com/api/oauth?app_id=56532326578385" class="social-button animate-slide-up" style="animation-delay: 0.5s">
                      <i class="fa-solid fa-earth-asia"></i><span>Globbook</span>
                    </a>
                </div>
            </div>
                            <div class="auth-divider animate-fade-in" style="animation-delay: 0.45s">
                    or continue with
                </div>
            <button id="showEmailLoginBtn" class="auth-submit-button" style="margin-top:20px;">
                Continue with Email <i class="fa-solid fa-envelope"></i>
            </button>
            <div class="auth-card animate-fade-in">
                <div id="emailLoginSection" style="display:none;">
                    <form id="signupForm" class="form">
                        <!-- Profile Upload -->
                        <div class="profile-upload">
                            <div class="profile-image-container">
                                <img id="profilePreview" 
                                     {if $profile && $profile->picture}
                                     src="{$profile->picture}"
                                     {else}
                                     src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23cccccc'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8z' /%3E%3C/svg%3E"
                                     {/if}
                                     alt="Profile Picture" class="profile-image" />
                                <label for="profilePicture" class="profile-upload-icon">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="profilePicture" name="i" accept="image/*" class="hidden">
                            </div>
                        </div>

                        <!-- Inputs -->
                        <div class="form-group">
                            <input type="text" id="firstname" placeholder="First Name" class="auth-input animate-slide-up" style="animation-delay: 0.1s" {if $profile}value="{$profile->firstname}"{/if} name="f" required/>
                        </div>

                        <div class="form-group">
                            <input type="text" id="lastname" placeholder="Last Name" class="auth-input animate-slide-up" style="animation-delay: 0.15s" {if $profile}value="{$profile->lastname}"{/if} name="l" required/>
                        </div>

                        <div class="form-group">
                            <input type="email" id="email" placeholder="Email Address" class="auth-input animate-slide-up" style="animation-delay: 0.2s" {if $profile}value="{$profile->email}"{/if} name="e" required/>
                        </div>

                        <div class="form-group">
                            <input type="tel" id="phone" placeholder="Phone Number" class="auth-input animate-slide-up" style="animation-delay: 0.25s" name="t" required/>
                        </div>

                        <div class="form-group">
                            <div class="password-input-container">
                                <input type="password" id="password" placeholder="Password" class="auth-input animate-slide-up" style="animation-delay: 0.3s" name="p" required/>
                                <button type="button" class="toggle-password" id="togglePassword">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                            <div id="passwordRules" style="margin-top: 10px;">
                                <div id="ruleLength" class="rule-item">At least 8 characters</div>
                                <div id="ruleUpper" class="rule-item">At least one uppercase letter (A-Z)</div>
                                <div id="ruleLower" class="rule-item">At least one lowercase letter (a-z)</div>
                                <div id="ruleNumber" class="rule-item">At least one number (0-9)</div>
                                <div id="ruleSpecial" class="rule-item">At least one special character (@,#,$,...)</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="password-input-container">
                                <input type="password" id="confirmPassword" placeholder="Confirm Password" class="auth-input animate-slide-up" style="animation-delay: 0.35s" name="p-c" required/>
                                <button type="button" class="toggle-password" id="toggleConfirmPassword">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <center>
                            <div id="registerCaptcha" class="cf-turnstile" data-sitekey="{$smarty.const.RECAPTCHA_SITE_KEY}"></div>
                        </center>

                        <button type="submit" class="auth-submit-button animate-slide-up" style="animation-delay: 0.4s" disabled>
                            Create Account <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
                <!-- Login Redirect -->
                <div class="signup-link animate-fade-in" style="animation-delay: 0.6s">
                    Already have an account? <a href="{$smarty.const.APP_URL}/auth/login" id="loginLink">Sign in</a>
                </div>

                <center>
                    <span style="font-family: Arial, sans-serif; font-size: 14px; color: #555;">
                        By continuing, you agree to our
                        <a href="{$smarty.const.APP_URL}/termsofservice/" style="color: #007bff; text-decoration: none;">Terms & Conditions</a>.
                    </span>
                </center>
            </div>
        </div>
    </div>

    <!-- Registration Success Overlay -->
    <div id="registrationSuccessModal" class="success-overlay" style="display: none;">
        <div class="success-card animate-scale-in">
            <div class="success-icon">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <h2>Account Created</h2>
            <p class="success-message">Your account is created. Check your inbox to continue to the app</p>
            <p class="success-email" id="registeredEmail" style="display:none;"></p>
            <button type="button" onclick="resendFromSuccessModal()" class="btn-resend" id="resendEmailBtn" data-resend-label="RESEND EMAIL" data-resend-cooldown-prefix="RESEND IN">
                RESEND EMAIL
            </button>
        </div>
    </div>
    </div>

<script>
    window.AUTH_APP_BASE = '{$smarty.const.APP_URL}';
    window.AUTH_CAPTCHA_SITE_KEY = '{$smarty.const.RECAPTCHA_SITE_KEY}';
    window.AUTH_DEBUG = {if $smarty.const.APP_DEBUG}true{else}false{/if};
</script>

<style>
/* Success Overlay Styles */
.success-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    animation: fadeIn 0.3s ease;
    background:
        radial-gradient(circle at 20% 20%, rgba(34, 197, 94, 0.35), transparent 55%),
        radial-gradient(circle at 80% 10%, rgba(16, 185, 129, 0.25), transparent 50%),
        linear-gradient(135deg, #0f9b4f 0%, #047857 100%);
}

.success-card {
    position: relative;
    background: rgba(255, 255, 255, 0.97);
    border-radius: 24px;
    width: min(560px, 92vw);
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 30px 70px rgba(6, 95, 70, 0.4);
    display: flex;
    flex-direction: column;
    padding: 42px 36px 38px;
    text-align: center;
}

.animate-scale-in {
    animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes scaleIn {
    from {
        transform: scale(0.8);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.success-icon {
    width: 112px;
    height: 112px;
    margin: 0 auto 18px;
    border-radius: 999px;
    background: #22c55e;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 64px;
    box-shadow: 0 16px 40px rgba(34, 197, 94, 0.35);
}

.success-overlay h2 {
    margin: 0;
    font-size: 30px;
    font-weight: 800;
    line-height: 1.3;
    color: #064e3b;
}

.success-message {
    font-size: 18px;
    color: #14532d;
    text-align: center;
    margin: 12px 0 16px;
    font-weight: 600;
}

.success-email {
    font-weight: 700;
    color: #166534;
    font-size: 16px;
    word-break: break-all;
    padding: 10px 12px;
    background: #ecfdf5;
    border-radius: 10px;
    border: 1px solid #bbf7d0;
    margin: 0 0 24px;
}

.btn-resend {
    background: #16a34a;
    color: #ffffff;
    border: none;
    padding: 14px 34px;
    border-radius: 999px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 15px;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    box-shadow: 0 12px 26px rgba(22, 163, 74, 0.35);
}

.btn-resend:hover {
    background: #15803d;
    transform: translateY(-2px);
    box-shadow: 0 16px 30px rgba(21, 128, 61, 0.35);
}

.btn-resend:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}

/* Responsive */
@media (max-width: 768px) {
    .success-card {
        width: 95%;
        max-height: 95vh;
    }

    .success-overlay h2 {
        font-size: 24px;
    }

    .success-icon {
        width: 86px;
        height: 86px;
        font-size: 48px;
    }
}
</style>

<script src="{$smarty.const.APP_URL}/assets/scripts/libs/dialog-js/main.min.js?V=01.03.04.2025"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/auth-register.min.js?V={get_hash_token()}"></script>
<script>
document.getElementById("showEmailLoginBtn").addEventListener("click", function() {
    var section = document.getElementById("emailLoginSection");
    if (section.style.display === "none") {
        section.style.display = "block";
        this.style.display = "none";
    }
});
</script>
</body>
</html>
