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
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/3/3c/Google_Favicon_2025.svg/250px-Google_Favicon_2025.svg.png" alt="Google" width="20" height="20" style="width:20px;height:20px;margin-right:8px;vertical-align:middle;" /> <span>Google</span>
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
