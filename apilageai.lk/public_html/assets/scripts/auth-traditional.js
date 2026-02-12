(() => {
  const appBase = (window.AUTH_APP_BASE || window.location.origin || '').replace(/\/$/, '');
  const captchaSiteKey = (window.AUTH_CAPTCHA_SITE_KEY || '').trim();

  const tabs = document.querySelectorAll('[data-auth-tab]');
  const panels = document.querySelectorAll('[data-auth-panel]');

  const loginForm = document.getElementById('loginForm');
  const signupForm = document.getElementById('signupForm');
  const forgotForm = document.getElementById('forgotForm');
  const loadingOverlay = document.getElementById('loadingOverlay');
  const signupSuccessOverlay = document.getElementById('signupSuccessOverlay');

  const loginAlert = document.getElementById('loginAlert');
  const signupAlert = document.getElementById('signupAlert');
  const forgotAlert = document.getElementById('forgotAlert');

  const resendBtn = document.getElementById('resendBtn');
  const successResendBtn = document.getElementById('successResendBtn');

  const loginGoogle = document.getElementById('loginGoogle');
  const loginGlobbook = document.getElementById('loginGlobbook');
  const loginFacebook = document.getElementById('loginFacebook');

  const captchaIds = { login: null, signup: null, forgot: null };
  const captchaTokens = { login: '', signup: '', forgot: '' };
  const captchaWaiters = { login: [], signup: [], forgot: [] };
  const captchaExecuting = { login: false, signup: false, forgot: false };
  const captchaPromises = { login: null, signup: null, forgot: null };

  const RESEND_COOLDOWN_SECONDS = 50;
  let resendCooldown = 0;
  let resendTimer = null;

  function showPanel(name) {
    tabs.forEach((tab) => {
      tab.classList.toggle('active', tab.dataset.authTab === name);
    });
    panels.forEach((panel) => {
      panel.style.display = panel.dataset.authPanel === name ? 'block' : 'none';
    });
    clearAlerts();
  }

  function clearAlerts() {
    [loginAlert, signupAlert, forgotAlert].forEach((el) => {
      if (el) {
        el.textContent = '';
        el.style.display = 'none';
        el.className = 'auth-alert';
      }
    });
  }

  function showAlert(el, message, type = 'error') {
    if (!el) return;
    el.textContent = message;
    el.style.display = 'block';
    el.className = `auth-alert ${type}`;
  }

  function showLoading(show) {
    if (loadingOverlay) loadingOverlay.style.display = show ? 'flex' : 'none';
  }

  function showSignupSuccess() {
    startResendCooldown();
    if (signupSuccessOverlay) signupSuccessOverlay.style.display = 'flex';
  }

  function updateResendButtons() {
    const btns = [resendBtn, successResendBtn].filter(Boolean);
    btns.forEach((btn) => {
      const label = btn.dataset.resendLabel || 'Resend verification email';
      const cooldownPrefix = btn.dataset.resendCooldownPrefix || 'Resend in';
      if (resendCooldown > 0) {
        btn.disabled = true;
        btn.textContent = `${cooldownPrefix} ${resendCooldown}s`;
        return;
      }
      btn.disabled = false;
      btn.textContent = label;
    });
  }

  function startResendCooldown() {
    resendCooldown = RESEND_COOLDOWN_SECONDS;
    updateResendButtons();
    if (resendTimer) clearInterval(resendTimer);
    resendTimer = setInterval(() => {
      resendCooldown -= 1;
      updateResendButtons();
      if (resendCooldown <= 0) {
        clearInterval(resendTimer);
        resendTimer = null;
      }
    }, 1000);
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

  async function getCaptchaToken(name, widgetId) {
    if (captchaTokens[name]) return captchaTokens[name];
    if (!window.turnstile) return '';
    if (widgetId === null) initCaptchaWidgets();
    const id = captchaIds[name];
    if (id === null) return '';
    if (captchaExecuting[name] && captchaPromises[name]) {
      return captchaPromises[name];
    }

    captchaExecuting[name] = true;
    const promise = new Promise((resolve) => {
      const timer = setTimeout(() => {
        captchaExecuting[name] = false;
        captchaPromises[name] = null;
        resolve('');
      }, 10000);
      captchaWaiters[name].push((token) => {
        clearTimeout(timer);
        resolve(token || '');
      });
      try {
        turnstile.execute(id);
      } catch (_) {
        clearTimeout(timer);
        captchaExecuting[name] = false;
        captchaPromises[name] = null;
        resolve('');
      }
    });
    captchaPromises[name] = promise;
    return promise;
  }

  function resetCaptcha(name, widgetId) {
    if (!window.turnstile || widgetId === null) return;
    captchaTokens[name] = '';
    captchaExecuting[name] = false;
    captchaPromises[name] = null;
    try { turnstile.reset(widgetId); } catch (_) {}
  }

  function togglePassword(btnId, inputId) {
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

  togglePassword('toggleLoginPassword', 'loginPassword');
  togglePassword('toggleSignupPassword', 'signupPassword');
  togglePassword('toggleSignupConfirmPassword', 'signupConfirmPassword');

  tabs.forEach((tab) => {
    tab.addEventListener('click', (e) => {
      e.preventDefault();
      showPanel(tab.dataset.authTab);
    });
  });

  if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearAlerts();
      const captcha = await getCaptchaToken('login', captchaIds.login);
      if (!captcha) {
        showAlert(loginAlert, 'Please complete the captcha.', 'error');
        return;
      }

      showLoading(true);
      try {
        const data = new FormData(loginForm);
        data.append('cf-turnstile-response', captcha);
        data.append('g-recaptcha-response', captcha);
        const res = await fetch(`${appBase}/api/auth.php?act=login`, { method: 'POST', body: data });
        const parsed = await parseResponse(res);
        const result = parsed.data || {};
        showLoading(false);
        if (!parsed.ok && !parsed.data) {
          resetCaptcha('login', captchaIds.login);
          const msg = (window.AUTH_DEBUG && parsed.raw)
            ? parsed.raw
            : 'Login failed. Please try again.';
          showAlert(loginAlert, msg, 'error');
          return;
        }
        if (result.e) {
          resetCaptcha('login', captchaIds.login);
          const msg = (window.AUTH_DEBUG && result._debug_output)
            ? `${result.m || 'Login failed.'}\n${result._debug_output}`
            : (result.m || 'Login failed.');
          showAlert(loginAlert, msg, 'error');
          if (result.resend && result.email && resendBtn) {
            resendBtn.onclick = () => resendVerification(result.email, loginAlert);
          }
          return;
        }
        showAlert(loginAlert, 'Login successful. Redirecting...', 'success');
        setTimeout(() => { window.location.href = `${appBase}/app`; }, 800);
      } catch (err) {
        showLoading(false);
        resetCaptcha('login', captchaIds.login);
        showAlert(loginAlert, 'Login failed. Please try again.', 'error');
      }
    });
  }

  if (signupForm) {
    signupForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearAlerts();
      const captcha = await getCaptchaToken('signup', captchaIds.signup);
      if (!captcha) {
        showAlert(signupAlert, 'Please complete the captcha.', 'error');
        return;
      }

      const password = document.getElementById('signupPassword')?.value || '';
      const confirmPassword = document.getElementById('signupConfirmPassword')?.value || '';
      const phone = document.getElementById('signupPhone')?.value || '';

      const validPassword = /^.{5,}$/.test(password);
      if (!validPassword) {
        showAlert(signupAlert, 'Password must be at least 5 characters.', 'error');
        return;
      }
      if (password !== confirmPassword) {
        showAlert(signupAlert, 'Passwords do not match.', 'error');
        return;
      }
      if (!/^\+?[0-9]{10,15}$/.test(phone.replace(/\s/g, ''))) {
        showAlert(signupAlert, 'Please enter a valid phone number (10-15 digits).', 'error');
        return;
      }

      showLoading(true);
      try {
        const data = new FormData(signupForm);
        data.append('cf-turnstile-response', captcha);
        data.append('g-recaptcha-response', captcha);
        const res = await fetch(`${appBase}/api/auth.php?act=register`, { method: 'POST', body: data });
        const parsed = await parseResponse(res);
        const result = parsed.data || {};
        showLoading(false);
        if (!parsed.ok && !parsed.data) {
          resetCaptcha('signup', captchaIds.signup);
          const msg = (window.AUTH_DEBUG && parsed.raw)
            ? parsed.raw
            : 'Registration failed. Please try again.';
          showAlert(signupAlert, msg, 'error');
          return;
        }
        if (result.e) {
          resetCaptcha('signup', captchaIds.signup);
          const msg = (window.AUTH_DEBUG && result._debug_output)
            ? `${result.m || 'Registration failed.'}\n${result._debug_output}`
            : (result.m || 'Registration failed.');
          showAlert(signupAlert, msg, 'error');
          return;
        }
        resetCaptcha('signup', captchaIds.signup);
        showSignupSuccess();
      } catch (err) {
        showLoading(false);
        resetCaptcha('signup', captchaIds.signup);
        showAlert(signupAlert, 'Registration failed. Please try again.', 'error');
      }
    });
  }

  if (forgotForm) {
    forgotForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearAlerts();
      const captcha = await getCaptchaToken('forgot', captchaIds.forgot);
      if (!captcha) {
        showAlert(forgotAlert, 'Please complete the captcha.', 'error');
        return;
      }

      showLoading(true);
      try {
        const data = new FormData(forgotForm);
        data.append('cf-turnstile-response', captcha);
        data.append('g-recaptcha-response', captcha);
        const res = await fetch(`${appBase}/api/auth.php?act=reset-request`, { method: 'POST', body: data });
        const parsed = await parseResponse(res);
        const result = parsed.data || {};
        showLoading(false);
        if (!parsed.ok && !parsed.data) {
          resetCaptcha('forgot', captchaIds.forgot);
          const msg = (window.AUTH_DEBUG && parsed.raw)
            ? parsed.raw
            : 'Failed to send reset link. Please try again.';
          showAlert(forgotAlert, msg, 'error');
          return;
        }
        if (result.e) {
          resetCaptcha('forgot', captchaIds.forgot);
          const msg = (window.AUTH_DEBUG && result._debug_output)
            ? `${result.m || 'Failed to send reset link.'}\n${result._debug_output}`
            : (result.m || 'Failed to send reset link.');
          showAlert(forgotAlert, msg, 'error');
          return;
        }
        showAlert(forgotAlert, result.m || 'Reset instructions sent. Check your email.', 'success');
        resetCaptcha('forgot', captchaIds.forgot);
      } catch (err) {
        showLoading(false);
        resetCaptcha('forgot', captchaIds.forgot);
        showAlert(forgotAlert, 'Failed to send reset link. Please try again.', 'error');
      }
    });
  }

  async function resendVerification(email, alertTarget) {
    if (!email) return;
    showLoading(true);
    try {
      const formData = new FormData();
      formData.append('email', email);
      const res = await fetch(`${appBase}/auth/resend-verification`, { method: 'POST', body: formData });
      const result = await res.json();
      showLoading(false);
      if (result.e) {
        showAlert(alertTarget, result.m || 'Failed to resend verification email.', 'error');
        return;
      }
      showAlert(alertTarget, 'Resend successfully. Check your spam folder, your email may be there.', 'success');
      startResendCooldown();
    } catch (err) {
      showLoading(false);
      showAlert(alertTarget, 'Failed to resend verification email.', 'error');
    }
  }

  function resendVerificationFromLogin() {
    const email = loginForm?.querySelector('input[name="e"]')?.value?.trim();
    if (!email) {
      showAlert(loginAlert, 'Please enter your email first.', 'error');
      return;
    }
    resendVerification(email, loginAlert);
  }

  function resendVerificationFromSuccess() {
    const email = signupForm?.querySelector('input[name="e"]')?.value?.trim();
    if (!email) {
      showAlert(signupAlert, 'Please enter your email first.', 'error');
      return;
    }
    resendVerification(email, signupAlert);
  }

  function resolveSiteKey(...els) {
    if (captchaSiteKey) return captchaSiteKey;
    for (const el of els) {
      const key = el?.dataset?.sitekey?.trim();
      if (key) return key;
    }
    return '';
  }

  function resolveToken(name, token) {
    captchaTokens[name] = token || '';
    captchaExecuting[name] = false;
    captchaPromises[name] = null;
    const waiters = captchaWaiters[name];
    if (waiters.length) {
      while (waiters.length) {
        const cb = waiters.shift();
        cb(token || '');
      }
    }
  }

  function renderWidget(name, el, siteKey) {
    if (!el || !siteKey) return null;
    if (el.querySelector('iframe') || el.childElementCount) return null;
    return turnstile.render(el, {
      sitekey: siteKey,
      size: 'normal',
      execution: 'execute',
      appearance: 'interaction-only',
      callback: (token) => resolveToken(name, token),
      'error-callback': (code) => {
        resolveToken(name, '');
        if (window.AUTH_DEBUG && code) {
          console.warn('Turnstile error:', code);
        }
        return true;
      },
      'expired-callback': () => resolveToken(name, ''),
      'timeout-callback': () => resolveToken(name, '')
    });
  }

  function ensureRecaptchaScript() {
    if (document.querySelector('script[src*="challenges.cloudflare.com/turnstile"]')) return;
    const script = document.createElement('script');
    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
    document.head.appendChild(script);
  }

  function initCaptchaWidgets() {
    if (!window.turnstile) return;
    if (captchaIds.login !== null) return;
    const loginEl = document.getElementById('loginCaptcha');
    const signupEl = document.getElementById('signupCaptcha');
    const forgotEl = document.getElementById('forgotCaptcha');
    const siteKey = resolveSiteKey(loginEl, signupEl, forgotEl);
    if (!siteKey) return;
    turnstile.ready(() => {
      if (loginEl) captchaIds.login = renderWidget('login', loginEl, siteKey);
      if (signupEl) captchaIds.signup = renderWidget('signup', signupEl, siteKey);
      if (forgotEl) captchaIds.forgot = renderWidget('forgot', forgotEl, siteKey);
    });
  }

  function waitForCaptcha(attempt = 0) {
    if (window.turnstile && typeof turnstile.render === 'function') {
      initCaptchaWidgets();
      return;
    }
    if (attempt === 0) ensureRecaptchaScript();
    if (attempt > 50) {
      showAlert(loginAlert, 'Captcha failed to load. Please disable blockers and refresh.', 'error');
      return;
    }
    setTimeout(() => waitForCaptcha(attempt + 1), 200);
  }

  const googleUrl = `${appBase}/auth/google`;
  const globbookUrl = 'https://globbook.com/api/oauth?app_id=56532326578385';
  const facebookUrl = `${appBase}/auth/facebook`;

  function bindSocial(btn, url, alertTarget) {
    if (!btn) return;
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      if (url) {
        window.location.href = url;
      } else if (alertTarget) {
        showAlert(alertTarget, 'Facebook login is coming soon.', 'error');
      }
    });
  }

  bindSocial(loginGoogle, googleUrl, loginAlert);
  bindSocial(loginGlobbook, globbookUrl, loginAlert);
  bindSocial(loginFacebook, facebookUrl, loginAlert);

  if (resendBtn) resendBtn.addEventListener('click', resendVerificationFromLogin);
  if (successResendBtn) successResendBtn.addEventListener('click', resendVerificationFromSuccess);

  showPanel('login');
  waitForCaptcha();
})();
