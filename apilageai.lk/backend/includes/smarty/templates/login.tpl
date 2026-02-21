{include file="components/head.tpl"}

{assign var=auth_state value="login"}
{if $smarty.get.mode == "register" || $smarty.get.mode == "signup"}
  {assign var=auth_state value="signup"}
{elseif $smarty.get.mode == "forgot" || $smarty.get.mode == "reset"}
  {assign var=auth_state value="forgot"}
{/if}

<div id="pageLoader" class="page-loader" aria-hidden="true">
  <div class="page-loader-card">
    <div class="spinner"></div>
    <p>Loading...</p>
  </div>
</div>

<div class="auth-page">
  <div class="auth-shell" data-auth-state="{$auth_state}">
    <header class="auth-header">
      <div class="auth-brand">
        <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="ApilageAI" class="auth-logo" width="44" height="44" style="width:44px;height:44px;" />
        <span>ApilageAI</span>
      </div>

      <div class="auth-headline auth-headline-login">
        <div class="auth-kicker">Your AI workspace.</div>
        <h1>Log in to your <span>ApilageAI</span> account</h1>
        <p>Use a trusted provider or continue with email.</p>
      </div>

      <div class="auth-headline auth-headline-signup">
        <div class="auth-kicker">Start your workspace.</div>
        <h1>Create your <span>ApilageAI</span> account</h1>
        <p>It takes less than a minute. No credit card required.</p>
      </div>

      <div class="auth-headline auth-headline-forgot">
        <div class="auth-kicker">Reset access.</div>
        <h1>Recover your <span>ApilageAI</span> account</h1>
        <p>We will send you a secure link to reset your password.</p>
      </div>
    </header>

    {if isset($smarty.get.error)}
      <div class="auth-alert error" role="alert" style="display:block;">
        {if $smarty.get.error == 'google_auth_failed'}
          Google sign-in failed. Please try again.
        {elseif $smarty.get.error == 'google_already_linked'}
          This Google account is already linked to another user.
        {elseif $smarty.get.error == 'facebook_auth_failed'}
          Facebook sign-in failed. Please try again.
        {elseif $smarty.get.error == 'facebook_already_linked'}
          This Facebook account is already linked to another user.
        {elseif $smarty.get.error == 'facebook_email_required'}
          Facebook did not provide an email address. Please use another login method.
        {elseif $smarty.get.error == 'disposable_email_not_allowed'}
          Disposable email addresses are not allowed for social sign-in.
        {elseif $smarty.get.error == 'magic_expired'}
          Your login link expired. Please request a new one.
        {else}
          Authentication failed. Please try again.
        {/if}
      </div>
    {/if}

    <div class="auth-providers">
      <a class="auth-provider" id="loginGoogle" href="{$smarty.const.APP_URL}/auth/google" aria-label="Continue with Google">
        <i class="fa-brands fa-google"></i>
        <span>Google</span>
      </a>
      <a class="auth-provider" id="loginFacebook" href="{$smarty.const.APP_URL}/auth/facebook" aria-label="Continue with Facebook">
        <i class="fa-brands fa-facebook"></i>
        <span>Facebook</span>
      </a>
      <a class="auth-provider" id="loginGlobbook" href="https://globbook.com/api/oauth?app_id=56532326578385" aria-label="Continue with Globbook">
        <i class="fa-solid fa-earth-asia"></i>
        <span>Globbook</span>
      </a>
    </div>

    <a class="auth-ghost" id="loginGuest" href="{$smarty.const.APP_URL}/auth/guest">Continue without account</a>

    <div class="auth-divider"><span>or continue with email</span></div>

    <div class="auth-panel" data-auth-panel="login" {if $auth_state != 'login'}style="display:none;"{/if}>
      <form id="loginForm" class="auth-form">
        <div class="auth-field">
          <label>Email</label>
          <input type="email" name="e" autocomplete="email" autocapitalize="none" required placeholder="name@company.com" />
        </div>

        <div class="auth-field">
          <label>Password</label>
          <div class="auth-password">
            <input type="password" id="loginPassword" name="p" autocomplete="current-password" required placeholder="Enter your password" />
            <button type="button" id="toggleLoginPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
          </div>
        </div>

        <div class="auth-captcha">
          <div id="loginCaptcha" class="cf-turnstile" data-sitekey="{$smarty.const.RECAPTCHA_SITE_KEY}"></div>
        </div>

        <div id="loginAlert" class="auth-alert" role="alert"></div>
        <div id="resendWrap" class="auth-resend" style="display:none;">
          <button type="button" id="resendBtn" class="auth-link-button" data-resend-label="Resend verification email" data-resend-cooldown-prefix="Resend in">
            Resend verification email
          </button>
        </div>

        <button type="submit" class="auth-submit">Log in</button>

        <div class="auth-foot">
          <span>Forgot password?</span>
          <a href="#" class="auth-link" data-auth-tab="forgot">Reset it</a>
        </div>
        <div class="auth-foot">
          <span>New here?</span>
          <a href="#" class="auth-link" data-auth-tab="signup">Create an account</a>
        </div>
      </form>
    </div>

    <div class="auth-panel" data-auth-panel="signup" {if $auth_state != 'signup'}style="display:none;"{/if}>
      <form id="signupForm" class="auth-form">
        <div class="auth-grid">
          <div class="auth-field">
            <label>First Name</label>
            <input type="text" name="f" autocomplete="given-name" required placeholder="First name" />
          </div>
          <div class="auth-field">
            <label>Last Name</label>
            <input type="text" name="l" autocomplete="family-name" required placeholder="Last name" />
          </div>
        </div>

        <div class="auth-field">
          <label>Email</label>
          <input type="email" name="e" autocomplete="email" autocapitalize="none" required placeholder="name@company.com" />
        </div>

        <div class="auth-field">
          <label>Phone</label>
          <input type="tel" id="signupPhone" name="t" autocomplete="tel" inputmode="numeric" required placeholder="Phone number" />
        </div>

        <div class="auth-field">
          <label>Password</label>
          <div class="auth-password">
            <input type="password" id="signupPassword" name="p" autocomplete="new-password" required placeholder="Create a password" />
            <button type="button" id="toggleSignupPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
          </div>
          <small>Use at least 5 characters.</small>
        </div>

        <div class="auth-field">
          <label>Confirm Password</label>
          <div class="auth-password">
            <input type="password" id="signupConfirmPassword" name="p-c" autocomplete="new-password" required placeholder="Confirm your password" />
            <button type="button" id="toggleSignupConfirmPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
          </div>
        </div>

        <div class="auth-captcha">
          <div id="signupCaptcha" class="cf-turnstile" data-sitekey="{$smarty.const.RECAPTCHA_SITE_KEY}"></div>
        </div>

        <div id="signupAlert" class="auth-alert" role="alert"></div>
        <button type="submit" class="auth-submit">Create account</button>

        <div class="auth-foot">
          <span>Already have an account?</span>
          <a href="#" class="auth-link" data-auth-tab="login">Log in</a>
        </div>
        <div class="auth-legal">
          By continuing, you agree to our
          <a href="{$smarty.const.APP_URL}/termsofservice/">Terms of Service</a>.
        </div>
      </form>
    </div>

    <div class="auth-panel" data-auth-panel="forgot" {if $auth_state != 'forgot'}style="display:none;"{/if}>
      <form id="forgotForm" class="auth-form">
        <div class="auth-field">
          <label>Email</label>
          <input type="email" name="email" autocomplete="email" autocapitalize="none" required placeholder="name@company.com" />
        </div>

        <div class="auth-captcha">
          <div id="forgotCaptcha" class="cf-turnstile" data-sitekey="{$smarty.const.RECAPTCHA_SITE_KEY}"></div>
        </div>

        <div id="forgotAlert" class="auth-alert" role="alert"></div>
        <button type="submit" class="auth-submit">Send reset link</button>

        <div class="auth-foot">
          <span>Remembered your password?</span>
          <a href="#" class="auth-link" data-auth-tab="login">Back to login</a>
        </div>
      </form>
    </div>

    <div class="auth-footer">© 2026 ApilageAI. All rights reserved.</div>
  </div>

  <div id="loadingOverlay" class="auth-loading" style="display:none;">
    <div class="auth-loading-card">
      <div class="spinner"></div>
      <p>Working on it...</p>
    </div>
  </div>

  <div id="signupSuccessOverlay" class="auth-success-overlay" style="display:none;">
    <div class="auth-success-card">
      <div class="auth-success-icon">
        <i class="fa-solid fa-circle-check"></i>
      </div>
      <h3>Account created</h3>
      <p>Your account is created. Check your inbox to continue.</p>
      <div class="auth-success-actions">
        <button type="button" id="successResendBtn" class="auth-submit auth-submit-outline" data-resend-label="Resend email" data-resend-cooldown-prefix="Resend in">Resend email</button>
        <button type="button" class="auth-submit auth-submit-ghost" onclick="window.location.href='{$smarty.const.APP_URL}/auth/login'">Back to login</button>
      </div>
    </div>
  </div>

  <div class="auth-help" data-help-menu>
    <button type="button" class="auth-help-toggle" aria-expanded="false" aria-controls="authHelpMenu">?</button>
    <div id="authHelpMenu" class="auth-help-menu" role="menu">
      <div class="auth-help-title">Theme</div>
      <button type="button" class="auth-help-item" data-theme-choice="system">Default device theme</button>
      <button type="button" class="auth-help-item" data-theme-choice="light">Light mode</button>
      <button type="button" class="auth-help-item" data-theme-choice="dark">Dark mode</button>
      <div class="auth-help-divider"></div>
      <a class="auth-help-link" href="{$smarty.const.APP_URL}/termsofservice/">See terms and conditions</a>
      <a class="auth-help-link" href="{$smarty.const.APP_URL}/privacypolicy/">See privacy policy</a>
    </div>
  </div>
</div>

<script>
  window.AUTH_APP_BASE = '{$smarty.const.APP_URL}';
  window.AUTH_CAPTCHA_SITE_KEY = '{$smarty.const.RECAPTCHA_SITE_KEY}';
  window.AUTH_DEBUG = {if $smarty.const.APP_DEBUG}true{else}false{/if};
</script>
<script src="{$smarty.const.APP_URL}/assets/scripts/libs/dialog-js/main.min.js?V=01.03.04.2025"></script>
<script src="{$smarty.const.APP_URL}/assets/scripts/auth-traditional.js?V={get_hash_token()}"></script>
<script>
  (function () {
    const themeStorageKey = 'theme';
    const root = document.documentElement;
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    const helpMenu = document.querySelector('[data-help-menu]');
    const helpToggle = helpMenu ? helpMenu.querySelector('.auth-help-toggle') : null;
    const helpPanel = helpMenu ? helpMenu.querySelector('.auth-help-menu') : null;
    const themeButtons = helpMenu ? helpMenu.querySelectorAll('[data-theme-choice]') : [];

    function applyTheme(choice) {
      if (!choice || choice === 'system') {
        root.classList.remove('dark');
        root.removeAttribute('data-theme');
        localStorage.removeItem(themeStorageKey);
        return;
      }
      const theme = choice === 'dark' ? 'dark' : 'light';
      if (theme === 'dark') {
        root.classList.add('dark');
      } else {
        root.classList.remove('dark');
      }
      root.setAttribute('data-theme', theme);
      localStorage.setItem(themeStorageKey, theme);
    }

    const savedTheme = localStorage.getItem(themeStorageKey);
    if (savedTheme) {
      applyTheme(savedTheme);
    } else if (prefersDarkScheme.matches) {
      root.classList.add('dark');
      root.setAttribute('data-theme', 'dark');
    } else {
      root.classList.remove('dark');
      root.setAttribute('data-theme', 'light');
    }

    if (helpToggle && helpPanel) {
      helpToggle.addEventListener('click', () => {
        const isOpen = helpMenu.classList.toggle('auth-help-open');
        helpToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      });
      document.addEventListener('click', (event) => {
        if (!helpMenu.contains(event.target)) {
          helpMenu.classList.remove('auth-help-open');
          helpToggle.setAttribute('aria-expanded', 'false');
        }
      });
    }

    if (themeButtons.length) {
      themeButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
          applyTheme(btn.dataset.themeChoice);
          if (helpMenu) helpMenu.classList.remove('auth-help-open');
          if (helpToggle) helpToggle.setAttribute('aria-expanded', 'false');
        });
      });
    }

    const loader = document.getElementById('pageLoader');
    if (!loader) return;
    const hide = () => {
      loader.classList.add('page-loader--hide');
      setTimeout(() => {
        loader.remove();
      }, 400);
    };
    if (document.readyState === 'complete') {
      hide();
    } else {
      window.addEventListener('load', hide, { once: true });
    }
  })();
</script>

<style>
:root {
  --auth-bg: #ffffff;
  --auth-ink: #111827;
  --auth-muted: #6b7280;
  --auth-border: #e5e7eb;
  --auth-card: rgba(255, 255, 255, 0.95);
  --auth-primary: #0f172a;
  --auth-primary-hover: #111827;
  --auth-accent: #1d4ed8;
  --auth-accent-soft: #dbeafe;
  --auth-input-bg: #f9fafb;
  --auth-overlay: rgba(15, 23, 42, 0.35);
}

[data-theme="dark"] {
  --auth-bg: #1e1e1e;
  --auth-ink: #f8fafc;
  --auth-muted: #94a3b8;
  --auth-border: rgba(148, 163, 184, 0.25);
  --auth-card: rgba(15, 23, 42, 0.6);
  --auth-primary: #f8fafc;
  --auth-primary-hover: #ffffff;
  --auth-accent: #60a5fa;
  --auth-accent-soft: rgba(96, 165, 250, 0.2);
  --auth-input-bg: rgba(15, 23, 42, 0.6);
  --auth-overlay: rgba(2, 6, 23, 0.72);
}

@media (prefers-color-scheme: dark) {
  :root:not([data-theme]) {
    --auth-bg: #1e1e1e;
    --auth-ink: #f8fafc;
    --auth-muted: #94a3b8;
    --auth-border: rgba(148, 163, 184, 0.25);
    --auth-card: rgba(15, 23, 42, 0.6);
    --auth-primary: #f8fafc;
    --auth-primary-hover: #ffffff;
    --auth-accent: #60a5fa;
    --auth-accent-soft: rgba(96, 165, 250, 0.2);
    --auth-input-bg: rgba(15, 23, 42, 0.6);
    --auth-overlay: rgba(2, 6, 23, 0.72);
  }
}

* {
  box-sizing: border-box;
}

body {
  margin: 0;
  font-family: "Outfit", "Plus Jakarta Sans", "Patrick Hand", sans-serif;
  color: var(--auth-ink);
  background: var(--auth-bg);
}

.auth-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 52px 20px;
  position: relative;
  background: var(--auth-bg);
}

.auth-page::before {
  content: none;
}

.auth-page::after {
  content: none;
}

.auth-shell {
  width: min(620px, 94vw);
  background: transparent;
  border: none;
  border-radius: 0;
  box-shadow: none;
  padding: 36px 40px 32px;
  position: relative;
  z-index: 1;
  animation: authPop 0.6s ease;
}

.auth-header {
  display: flex;
  flex-direction: column;
  gap: 14px;
  margin-bottom: 26px;
}

.auth-brand {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-weight: 600;
  color: var(--auth-primary);
}

.auth-logo {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  background: transparent;
  padding: 0;
  object-fit: contain;
}

.auth-headline {
  display: none;
}

.auth-shell[data-auth-state="login"] .auth-headline-login,
.auth-shell[data-auth-state="signup"] .auth-headline-signup,
.auth-shell[data-auth-state="forgot"] .auth-headline-forgot {
  display: block;
}

.auth-kicker {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--auth-muted);
}

.auth-headline h1 {
  margin: 8px 0 6px;
  font-family: "Outfit", "Plus Jakarta Sans", sans-serif;
  font-size: 30px;
  line-height: 1.2;
  color: var(--auth-primary);
}

.auth-headline h1 span {
  color: var(--auth-accent);
}

.auth-headline p {
  margin: 0;
  color: var(--auth-muted);
}

.auth-providers {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 12px;
  margin-bottom: 16px;
}

.auth-provider {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 14px 10px;
  border-radius: 16px;
  border: 1px solid var(--auth-border);
  text-decoration: none;
  color: var(--auth-primary);
  background: var(--auth-card);
  font-weight: 600;
  transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
}

.auth-provider i {
  font-size: 20px;
}

.auth-provider:hover {
  transform: translateY(-2px);
  border-color: rgba(29, 78, 216, 0.35);
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
}

.auth-ghost {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  border-radius: 999px;
  padding: 12px 16px;
  border: 1px dashed rgba(15, 23, 42, 0.25);
  color: var(--auth-primary);
  text-decoration: none;
  font-weight: 600;
  margin-bottom: 18px;
  transition: border-color 0.2s ease, color 0.2s ease;
}

.auth-ghost:hover {
  border-color: var(--auth-accent);
  color: var(--auth-accent);
}

.auth-divider {
  position: relative;
  text-align: center;
  margin: 18px 0 20px;
  color: var(--auth-muted);
  font-size: 13px;
}

.auth-divider::before,
.auth-divider::after {
  content: "";
  position: absolute;
  top: 50%;
  width: 38%;
  height: 1px;
  background: rgba(15, 23, 42, 0.12);
}

.auth-divider::before {
  left: 0;
}

.auth-divider::after {
  right: 0;
}

.auth-shell[data-auth-state="forgot"] .auth-providers,
.auth-shell[data-auth-state="forgot"] .auth-divider,
.auth-shell[data-auth-state="forgot"] .auth-ghost {
  display: none;
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.auth-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.auth-field label {
  display: block;
  font-size: 12px;
  color: var(--auth-muted);
  margin-bottom: 6px;
}

.auth-field input {
  width: 100%;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid var(--auth-border);
  background: var(--auth-input-bg);
  font-size: 14px;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.auth-field input:focus {
  outline: none;
  border-color: rgba(29, 78, 216, 0.5);
  box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.12);
  background: var(--auth-card);
}

.auth-password {
  display: flex;
  align-items: center;
  gap: 10px;
}

.auth-password button {
  border: none;
  background: transparent;
  color: var(--auth-muted);
  cursor: pointer;
  padding: 6px;
  border-radius: 8px;
}

.auth-password button:hover {
  color: var(--auth-primary);
  background: rgba(15, 23, 42, 0.06);
}

.auth-form small {
  color: var(--auth-muted);
  font-size: 12px;
}

.auth-submit {
  border: none;
  border-radius: 14px;
  padding: 13px 18px;
  font-weight: 600;
  color: #ffffff;
  background: linear-gradient(135deg, #0f172a, #1f2937);
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

[data-theme="dark"] .auth-submit {
  background: #444444;
}

[data-theme="dark"] .auth-submit:hover {
  box-shadow: 0 12px 22px rgba(0, 0, 0, 0.45);
}

.auth-submit:hover {
  transform: translateY(-1px);
  box-shadow: 0 12px 22px rgba(15, 23, 42, 0.18);
}

.auth-submit-outline {
  background: transparent;
  color: var(--auth-primary);
  border: 1px solid var(--auth-border);
}

.auth-submit-ghost {
  background: var(--auth-input-bg);
  color: var(--auth-primary);
}

.auth-foot {
  display: flex;
  gap: 6px;
  justify-content: center;
  font-size: 13px;
  color: var(--auth-muted);
}

.auth-link {
  color: var(--auth-accent);
  text-decoration: none;
  font-weight: 600;
}

.auth-link:hover {
  text-decoration: underline;
}

.auth-legal {
  text-align: center;
  font-size: 12px;
  color: var(--auth-muted);
}

.auth-legal a {
  color: var(--auth-accent);
  text-decoration: none;
  font-weight: 600;
}

.auth-alert {
  display: none;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 13px;
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid rgba(185, 28, 28, 0.2);
}

.auth-alert.success {
  background: #ecfdf5;
  color: #047857;
  border-color: rgba(4, 120, 87, 0.25);
}

.auth-alert.error {
  display: block;
}

.auth-resend {
  display: flex;
  justify-content: center;
}

.auth-link-button {
  border: none;
  background: transparent;
  color: var(--auth-accent);
  font-weight: 600;
  cursor: pointer;
}

.auth-captcha {
  display: flex;
  justify-content: center;
}

.auth-footer {
  text-align: center;
  margin-top: 26px;
  font-size: 12px;
  color: var(--auth-muted);
}

.auth-loading,
.auth-success-overlay {
  position: fixed;
  inset: 0;
  background: var(--auth-overlay);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.page-loader {
  position: fixed;
  inset: 0;
  background: var(--auth-bg);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.page-loader--hide {
  opacity: 0;
  visibility: hidden;
}

.page-loader-card {
  text-align: center;
  color: var(--auth-muted);
  font-size: 14px;
}

.auth-loading-card,
.auth-success-card {
  background: var(--auth-card);
  border-radius: 20px;
  padding: 26px 30px;
  text-align: center;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2);
  width: min(420px, 90vw);
}

.spinner {
  width: 36px;
  height: 36px;
  border: 3px solid rgba(15, 23, 42, 0.1);
  border-top-color: var(--auth-accent);
  border-radius: 50%;
  margin: 0 auto 12px;
  animation: spin 1s linear infinite;
}

.auth-success-icon {
  width: 72px;
  height: 72px;
  margin: 0 auto 16px;
  border-radius: 50%;
  background: var(--auth-accent-soft);
  color: var(--auth-accent);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 34px;
}

.auth-success-actions {
  margin-top: 18px;
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
  justify-content: center;
}

[data-theme="dark"] .auth-provider {
  border-color: rgba(148, 163, 184, 0.35);
}

[data-theme="dark"] .auth-provider:hover {
  box-shadow: 0 12px 24px rgba(2, 6, 23, 0.5);
}

[data-theme="dark"] .auth-alert {
  background: rgba(220, 38, 38, 0.15);
  color: #fecaca;
  border-color: rgba(248, 113, 113, 0.4);
}

[data-theme="dark"] .auth-alert.success {
  background: rgba(16, 185, 129, 0.15);
  color: #a7f3d0;
  border-color: rgba(52, 211, 153, 0.3);
}

.auth-help {
  position: fixed;
  right: 24px;
  bottom: 24px;
  z-index: 10001;
}

.auth-help-toggle {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  border: 1px solid var(--auth-border);
  background: var(--auth-card);
  color: var(--auth-primary);
  font-weight: 700;
  font-size: 18px;
  cursor: pointer;
  box-shadow: 0 12px 24px rgba(15, 23, 42, 0.15);
}

.auth-help-menu {
  position: absolute;
  right: 0;
  bottom: 56px;
  min-width: 220px;
  background: var(--auth-card);
  border: 1px solid var(--auth-border);
  border-radius: 14px;
  padding: 12px;
  box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18);
  opacity: 0;
  transform: translateY(10px);
  pointer-events: none;
  transition: opacity 0.2s ease, transform 0.2s ease;
}

.auth-help-menu::after {
  content: "";
  position: absolute;
  right: 16px;
  bottom: -6px;
  width: 12px;
  height: 12px;
  background: var(--auth-card);
  border-right: 1px solid var(--auth-border);
  border-bottom: 1px solid var(--auth-border);
  transform: rotate(45deg);
}

.auth-help-open .auth-help-menu {
  opacity: 1;
  transform: translateY(0);
  pointer-events: auto;
}

.auth-help-title {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--auth-muted);
  margin-bottom: 8px;
}

.auth-help-item,
.auth-help-link {
  display: block;
  width: 100%;
  padding: 8px 10px;
  border-radius: 10px;
  border: none;
  background: transparent;
  color: var(--auth-primary);
  text-align: left;
  cursor: pointer;
  font-size: 13px;
  text-decoration: none;
}

.auth-help-item:hover,
.auth-help-link:hover {
  background: rgba(15, 23, 42, 0.08);
}

[data-theme="dark"] .auth-help-item:hover,
[data-theme="dark"] .auth-help-link:hover {
  background: rgba(148, 163, 184, 0.16);
}

.auth-help-divider {
  height: 1px;
  background: var(--auth-border);
  margin: 8px 0;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

@keyframes authPop {
  from { opacity: 0; transform: translateY(16px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 640px) {
  .auth-shell {
    padding: 28px 22px 26px;
  }

  .auth-providers {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .auth-grid {
    grid-template-columns: 1fr;
  }

  .auth-headline h1 {
    font-size: 26px;
  }
}
</style>
