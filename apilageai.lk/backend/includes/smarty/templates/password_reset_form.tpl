{include file="components/head.tpl"}

<div id="pageLoader" class="page-loader" aria-hidden="true">
  <div class="page-loader-card">
    <div class="spinner"></div>
    <p>Loading...</p>
  </div>
</div>

<div class="auth-page">
  <div class="auth-shell">
    <header class="auth-header">
      <div class="auth-brand">
        <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="ApilageAI" class="auth-logo" width="44" height="44" style="width:44px;height:44px;" />
        <span>ApilageAI</span>
      </div>
      <div class="auth-kicker">Set a new password.</div>
      <h1>Reset your password</h1>
      <p>Create a new password to keep your account secure.</p>
    </header>

    {if isset($result.e) && $result.e}
      <div class="auth-alert error" role="alert" style="display:block;">
        {$result.m}
      </div>
      {if isset($result.expired) && $result.expired}
        <p class="auth-footnote">If your link expired, <a class="auth-link" href="{$base_url}/auth/reset-request">request a new reset</a>.</p>
      {/if}
    {/if}

    {if (empty($reset_complete)) && (!isset($result.e) || !$result.e)}
    <form id="resetPasswordForm" class="auth-form" method="POST" action="{$base_url}/auth/reset-password?token={$smarty.get.token}">
      <div class="auth-field">
        <label>New Password</label>
        <div class="auth-password">
          <input type="password" id="password" name="password" autocomplete="new-password" required placeholder="Enter new password" />
          <button type="button" id="toggleResetPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
        </div>
      </div>

      <div class="auth-field">
        <label>Confirm New Password</label>
        <div class="auth-password">
          <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password" required placeholder="Confirm new password" />
          <button type="button" id="toggleResetConfirmPassword" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
        </div>
      </div>

      <button type="submit" class="auth-submit">Save new password</button>

      <div class="auth-foot">
        <span>Back to</span>
        <a href="{$base_url}/auth/login" class="auth-link">Login</a>
      </div>
    </form>
    {/if}
  </div>

  <div id="resetSuccessOverlay" class="auth-loading" style="display:none;">
    <div class="auth-loading-card">
      <div class="auth-success-icon">
        <i class="fa fa-check"></i>
      </div>
      <h3>Password updated</h3>
      <p>Redirecting you back to login...</p>
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
    if (loader) {
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
    }

    const resetComplete = {if isset($reset_complete) && $reset_complete}true{else}false{/if};
    if (resetComplete) {
      const overlay = document.getElementById('resetSuccessOverlay');
      if (overlay) overlay.style.display = 'flex';
      setTimeout(() => {
        window.location.href = '{$base_url}/auth/login';
      }, 1800);
    }

    function bindToggle(btnId, inputId) {
      const btn = document.getElementById(btnId);
      const input = document.getElementById(inputId);
      if (!btn || !input) return;
      btn.addEventListener('click', () => {
        const type = input.type === 'password' ? 'text' : 'password';
        input.type = type;
        btn.innerHTML = type === 'password'
          ? '<i class="fa-regular fa-eye"></i>'
          : '<i class="fa-regular fa-eye-slash"></i>';
      });
    }

    bindToggle('toggleResetPassword', 'password');
    bindToggle('toggleResetConfirmPassword', 'confirm_password');
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
  background: var(--auth-bg);
}

.auth-shell {
  width: min(540px, 92vw);
  background: transparent;
  border: none;
  border-radius: 0;
  box-shadow: none;
  padding: 36px 40px 32px;
  position: relative;
  animation: authPop 0.6s ease;
}

.auth-header {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 22px;
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

.auth-kicker {
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--auth-muted);
}

.auth-header h1 {
  margin: 0;
  font-family: "Outfit", "Plus Jakarta Sans", sans-serif;
  font-size: 28px;
  color: var(--auth-primary);
}

.auth-header p {
  margin: 0;
  color: var(--auth-muted);
}

.auth-form {
  display: flex;
  flex-direction: column;
  gap: 14px;
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

.auth-foot {
  display: flex;
  gap: 6px;
  justify-content: center;
  font-size: 13px;
  color: var(--auth-muted);
  margin-top: 10px;
}

.auth-link {
  color: var(--auth-accent);
  text-decoration: none;
  font-weight: 600;
}

.auth-link:hover {
  text-decoration: underline;
}

.auth-footnote {
  font-size: 13px;
  color: var(--auth-muted);
  margin-bottom: 12px;
}

.auth-alert {
  display: none;
  padding: 12px 14px;
  border-radius: 12px;
  font-size: 13px;
  background: #fef2f2;
  color: #b91c1c;
  border: 1px solid rgba(185, 28, 28, 0.2);
  margin-bottom: 14px;
}

.auth-alert.error {
  display: block;
}

.auth-loading {
  position: fixed;
  inset: 0;
  background: var(--auth-overlay);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.auth-loading-card {
  background: var(--auth-card);
  border-radius: 20px;
  padding: 26px 30px;
  text-align: center;
  box-shadow: 0 20px 50px rgba(15, 23, 42, 0.2);
  width: min(420px, 90vw);
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

[data-theme="dark"] .auth-alert {
  background: rgba(220, 38, 38, 0.15);
  color: #fecaca;
  border-color: rgba(248, 113, 113, 0.4);
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

@keyframes authPop {
  from { opacity: 0; transform: translateY(16px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

@media (max-width: 600px) {
  .auth-shell {
    padding: 28px 22px 26px;
  }

  .auth-header h1 {
    font-size: 24px;
  }
}
</style>
