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
      return { ok: false, data: null, raw: '', status: res.status };
    }
  }

  function getCaptchaToken(widgetId) {
    if (!window.grecaptcha || widgetId === null) return '';
    return grecaptcha.getResponse(widgetId);
  }

  function resetCaptcha(widgetId) {
    if (window.grecaptcha && widgetId !== null) {
      try { grecaptcha.reset(widgetId); } catch (_) {}
    }
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
      const captcha = getCaptchaToken(captchaIds.login);
      if (!captcha) {
        showAlert(loginAlert, 'Please complete the captcha.', 'error');
        return;
      }

      showLoading(true);
      try {
        const data = new FormData(loginForm);
        data.append('g-recaptcha-response', captcha);
        const res = await fetch(`${appBase}/api/auth.php?act=login`, { method: 'POST', body: data });
        const parsed = await parseResponse(res);
        const result = parsed.data || {};
        showLoading(false);
        if (!parsed.ok && !parsed.data) {
          resetCaptcha(captchaIds.login);
          showAlert(loginAlert, 'Login failed. Please try again.', 'error');
          return;
        }
        if (result.e) {
          resetCaptcha(captchaIds.login);
          showAlert(loginAlert, result.m || 'Login failed.', 'error');
          if (result.resend && result.email && resendBtn) {
            resendBtn.onclick = () => resendVerification(result.email, loginAlert);
          }
          return;
        }
        showAlert(loginAlert, 'Login successful. Redirecting...', 'success');
        setTimeout(() => { window.location.href = `${appBase}/app`; }, 800);
      } catch (err) {
        showLoading(false);
        resetCaptcha(captchaIds.login);
        showAlert(loginAlert, 'Login failed. Please try again.', 'error');
      }
    });
  }

  if (signupForm) {
    signupForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearAlerts();
      const captcha = getCaptchaToken(captchaIds.signup);
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
        data.append('g-recaptcha-response', captcha);
        const res = await fetch(`${appBase}/api/auth.php?act=register`, { method: 'POST', body: data });
        const parsed = await parseResponse(res);
        const result = parsed.data || {};
        showLoading(false);
        if (!parsed.ok && !parsed.data) {
          resetCaptcha(captchaIds.signup);
          showAlert(signupAlert, 'Registration failed. Please try again.', 'error');
          return;
        }
        if (result.e) {
          resetCaptcha(captchaIds.signup);
          showAlert(signupAlert, result.m || 'Registration failed.', 'error');
          return;
        }
        resetCaptcha(captchaIds.signup);
        showSignupSuccess();
      } catch (err) {
        showLoading(false);
        resetCaptcha(captchaIds.signup);
        showAlert(signupAlert, 'Registration failed. Please try again.', 'error');
      }
    });
  }

  if (forgotForm) {
    forgotForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      clearAlerts();
      const captcha = getCaptchaToken(captchaIds.forgot);
      if (!captcha) {
        showAlert(forgotAlert, 'Please complete the captcha.', 'error');
        return;
      }

      showLoading(true);
      try {
        const data = new FormData(forgotForm);
        const res = await fetch(`${appBase}/api/auth.php?act=reset-request`, { method: 'POST', body: data });
        const parsed = await parseResponse(res);
        const result = parsed.data || {};
        showLoading(false);
        if (!parsed.ok && !parsed.data) {
          resetCaptcha(captchaIds.forgot);
          showAlert(forgotAlert, 'Failed to send reset link. Please try again.', 'error');
          return;
        }
        if (result.e) {
          resetCaptcha(captchaIds.forgot);
          showAlert(forgotAlert, result.m || 'Failed to send reset link.', 'error');
          return;
        }
        showAlert(forgotAlert, result.m || 'Reset instructions sent. Check your email.', 'success');
        resetCaptcha(captchaIds.forgot);
      } catch (err) {
        showLoading(false);
        resetCaptcha(captchaIds.forgot);
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

  function initCaptchaWidgets() {
    if (!window.grecaptcha) return;
    if (captchaIds.login !== null) return;
    if (!captchaSiteKey) return;
    const loginEl = document.getElementById('loginCaptcha');
    const signupEl = document.getElementById('signupCaptcha');
    const forgotEl = document.getElementById('forgotCaptcha');
    if (loginEl) captchaIds.login = grecaptcha.render(loginEl, { sitekey: captchaSiteKey });
    if (signupEl) captchaIds.signup = grecaptcha.render(signupEl, { sitekey: captchaSiteKey });
    if (forgotEl) captchaIds.forgot = grecaptcha.render(forgotEl, { sitekey: captchaSiteKey });
  }

  function waitForCaptcha() {
    if (window.grecaptcha && typeof grecaptcha.render === 'function') {
      initCaptchaWidgets();
      return;
    }
    setTimeout(waitForCaptcha, 200);
  }

  const googleUrl = `${appBase}/auth/google`;
  const globbookUrl = 'https://globbook.com/api/oauth?app_id=56532326578385';

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
  bindSocial(loginFacebook, '', loginAlert);

  if (resendBtn) resendBtn.addEventListener('click', resendVerificationFromLogin);
  if (successResendBtn) successResendBtn.addEventListener('click', resendVerificationFromSuccess);

  showPanel('login');
  waitForCaptcha();
})();
