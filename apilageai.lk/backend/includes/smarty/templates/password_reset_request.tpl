{include file="components/head.tpl"}

<div id="pageLoader" class="page-loader" aria-hidden="true">
  <div class="page-loader-card">
    <div class="spinner"></div>
    <p>Loading...</p>
  </div>
</div>

<div class="auth-page">
  <div class="auth-shell auth-shell-narrow">
    <header class="auth-header">
      <div class="auth-brand">
        <img src="{$smarty.const.APP_URL}/assets/images/icon.png" alt="ApilageAI" class="auth-logo" width="44" height="44" style="width:44px;height:44px;" />
        <span>ApilageAI</span>
      </div>
      <div class="auth-kicker">Reset access.</div>
      <h1>Forgot your password?</h1>
      <p>Enter your email and we will send you a secure reset link.</p>
    </header>

    {if isset($error)}
      <div class="auth-alert error" role="alert" style="display:block;">
        {$error}
      </div>
    {/if}

    {if isset($success)}
      <div class="auth-alert success" role="alert" style="display:block;">
        {$success}
      </div>
    {/if}

    <noscript>
      <div class="auth-alert error" role="alert" style="display:block;">
        JavaScript is required to send the reset email.
      </div>
    </noscript>

    <div id="resetRequestAlert" class="auth-alert" role="alert"></div>

    <form id="passwordResetRequestForm" class="auth-form" method="POST" action="{$base_url}/auth/reset-request">
      <div class="auth-field">
        <label>Email</label>
        <input type="email" id="email" name="email" autocomplete="email" autocapitalize="none" required placeholder="name@company.com" />
      </div>

      <button type="submit" class="auth-submit">Send reset link</button>
    </form>

    <div class="auth-foot">
      <span>Remembered your password?</span>
      <a href="{$base_url}/auth/login" class="auth-link">Back to login</a>
    </div>
  </div>

  <div id="resetLoadingOverlay" class="auth-loading" style="display:none;">
    <div class="auth-loading-card">
      <div class="spinner"></div>
      <p>Sending email...</p>
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

    const form = document.getElementById('passwordResetRequestForm');
    const alertEl = document.getElementById('resetRequestAlert');
    const overlay = document.getElementById('resetLoadingOverlay');
    const baseUrl = (window.APP_BASE_URL || window.location.origin || '').replace(/\/$/, '');

    function showAlert(message, type) {
      if (!alertEl) return;
      if (!message) {
        alertEl.textContent = '';
        alertEl.style.display = 'none';
        alertEl.className = 'auth-alert';
        return;
      }
      alertEl.textContent = message;
      alertEl.style.display = 'block';
      alertEl.className = 'auth-alert ' + (type || 'error');
    }

    function showLoading(show) {
      if (!overlay) return;
      overlay.style.display = show ? 'flex' : 'none';
    }

    async function parseResponse(res) {
      const text = await res.text();
      try {
        return { ok: res.ok, data: JSON.parse(text) };
      } catch (err) {
        const match = text.match(/\{[\s\S]*\}/);
        if (match) {
          try {
            return { ok: res.ok, data: JSON.parse(match[0]) };
          } catch (_) {}
        }
        return { ok: false, data: null, raw: text || '', status: res.status };
      }
    }

    if (!form) return;

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      showAlert('', '');
      showLoading(true);
      try {
        const data = new FormData(form);
        const res = await fetch(baseUrl + '/api/auth.php?act=reset-request', {
          method: 'POST',
          body: data
        });
        const parsed = await parseResponse(res);
        showLoading(false);
        const result = parsed.data || {};
        if (!parsed.ok && !parsed.data) {
          const msg = (window.AUTH_DEBUG && parsed.raw)
            ? parsed.raw
            : 'Failed to send reset link. Please try again.';
          showAlert(msg, 'error');
          return;
        }
        if (result.e) {
          const msg = (window.AUTH_DEBUG && result._debug_output)
            ? (result.m || 'Failed to send reset link.') + '\n' + result._debug_output
            : (result.m || 'Failed to send reset link.');
          showAlert(msg, 'error');
          return;
        }
        showAlert(result.m || 'If the email exists, reset instructions have been sent.', 'success');
        form.reset();
      } catch (err) {
        showLoading(false);
        showAlert('Failed to send reset link. Please try again.', 'error');
      }
    });
  })();
</script>

