(() => {
    const appBase = (window.AUTH_APP_BASE || window.location.origin || '').replace(/\/$/, '');
    const nodeBase = (window.AUTH_NODE_BASE || appBase || '').replace(/\/$/, '');

    const chatMessages = document.getElementById('authChatMessages');
    const chatActions = document.getElementById('authChatActions');
    const input = document.getElementById('authInput');
    const sendBtn = document.getElementById('authSend');
    const restartBtn = document.getElementById('authRestart');
    const showOptionsBtn = document.getElementById('authShowOptions');
    const captchaRow = document.getElementById('authCaptchaRow');
    const continueBtn = document.getElementById('authContinue');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const chatInputWrap = document.getElementById('authChatInput');

    const state = {
        flow: null,
        stepIndex: 0,
        data: {},
        awaitingInput: false,
        readyToContinue: false,
    };

    const flows = {
        login: [
            { key: 'email', prompt: 'Tell me your email address.' },
            { key: 'password', prompt: 'Tell me your password.', type: 'password' },
        ],
        register: [
            { key: 'first_name', prompt: 'What is your first name?' },
            { key: 'last_name', prompt: 'What is your last name?' },
            { key: 'email', prompt: 'What is your email address?' },
            { key: 'phone', prompt: 'What is your phone number?' },
            { key: 'password', prompt: 'Create a password (8+ chars with uppercase, lowercase, number, special char).', type: 'password' },
        ],
        magic: [
            { key: 'email', prompt: 'Tell me the email address to send your login link.' },
        ],
    };

    function addMessage(text, type = 'bot') {
        const el = document.createElement('div');
        el.className = `auth-message ${type}`;
        el.textContent = text;
        chatMessages.appendChild(el);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addActionButtons(buttons) {
        chatActions.innerHTML = '';
        buttons.forEach((btn) => {
            const el = document.createElement('button');
            el.className = 'auth-action-btn';
            el.innerHTML = btn.html || btn.label;
            el.addEventListener('click', btn.onClick);
            chatActions.appendChild(el);
        });
    }

    function showMenu() {
        state.flow = null;
        state.stepIndex = 0;
        state.data = {};
        state.awaitingInput = false;
        state.readyToContinue = false;
        hideCaptcha();
        addMessage('Choose a login option. You can also ask me about login or access.', 'bot');
        addActionButtons([
            { html: '<i class="fa fa-envelope"></i> Email', onClick: () => startFlow('login') },
            { html: '<i class="fab fa-google"></i> Google', onClick: () => handleOauth('google') },
            { html: '<i class="fa fa-earth-asia"></i> Globbook', onClick: () => handleOauth('globbook') },
            { html: '<i class="fa fa-user-plus"></i> I\'m new', onClick: () => startFlow('register') },
            { html: '<i class="fa fa-key"></i> Damn I forgot password', onClick: () => startFlow('magic') },
        ]);
    }

    function startFlow(flowName) {
        state.flow = flowName;
        state.stepIndex = 0;
        state.data = {};
        state.awaitingInput = true;
        state.readyToContinue = false;
        hideCaptcha();
        chatActions.innerHTML = '';
        const step = flows[flowName][0];
        addMessage(step.prompt, 'bot');
    }

    function handleOauth(provider) {
        const url = provider === 'google'
            ? `${appBase}/auth/google`
            : 'https://globbook.com/api/oauth?app_id=56532326578385';
        addMessage(provider === 'google'
            ? 'Great! Click continue to sign in with Google.'
            : 'Great! Click continue to sign in with Globbook.', 'bot');
        addActionButtons([
            { html: '<i class="fa fa-arrow-right"></i> Continue', onClick: () => window.location.href = url },
        ]);
    }

    function hideCaptcha() {
        captchaRow.style.display = 'none';
        continueBtn.disabled = true;
        if (chatInputWrap) chatInputWrap.style.display = 'flex';
        if (window.grecaptcha) {
            try { grecaptcha.reset(); } catch (_) {}
        }
    }

    function showCaptcha() {
        captchaRow.style.display = 'flex';
        continueBtn.disabled = true;
        if (chatInputWrap) chatInputWrap.style.display = 'none';
    }

    function onUserInput(text) {
        if (!text) return;

        const intent = detectIntent(text);
        let startedByIntent = false;
        if (intent && !state.flow) {
            const extracted = extractCredentials(text);
            if (intent === 'login') startFlow('login');
            if (intent === 'register') startFlow('register');
            if (intent === 'magic') startFlow('magic');
            startedByIntent = true;

            if (intent === 'magic' && extracted.email) {
                state.data.email = extracted.email;
                state.awaitingInput = false;
                state.readyToContinue = true;
                addMessage(text, 'user');
                addMessage('Got it. Please complete the captcha, then press Continue to send your login link.', 'bot');
                showCaptcha();
                return;
            }

            if (intent === 'login' && extracted.email && extracted.password) {
                state.data.email = extracted.email;
                state.data.password = extracted.password;
                state.awaitingInput = false;
                state.readyToContinue = true;
                addMessage(text, 'user');
                addMessage('Got it. Please complete the captcha, then press Continue to log you in.', 'bot');
                showCaptcha();
                return;
            }
        }

        if (!state.flow) {
            const extracted = extractCredentials(text);
            if (extracted.email && extracted.password) {
                startFlow('login');
                state.data.email = extracted.email;
                state.data.password = extracted.password;
                addMessage(text, 'user');
                state.awaitingInput = false;
                state.readyToContinue = true;
                addMessage('Got it. Please complete the captcha, then press Continue to log you in.', 'bot');
                showCaptcha();
                return;
            }
            if (extracted.email && !extracted.password) {
                startFlow('login');
                state.data.email = extracted.email;
                addMessage(text, 'user');
                addMessage('Thanks. Now please share your password.', 'bot');
                return;
            }
        }
        if (startedByIntent) {
            // Intent handled; wait for next input
            return;
        }

        if (state.flow && state.awaitingInput) {
            const flowSteps = flows[state.flow];
            const applied = applyInputToFlow(text, flowSteps);
            if (applied.used) {
                addMessage(applied.display, 'user');
                if (!state.awaitingInput) {
                    state.readyToContinue = true;
                    addMessage('Thanks! Please complete the captcha, then press Continue.', 'bot');
                    showCaptcha();
                }
                return;
            }

            const step = flowSteps[state.stepIndex];
            if (step.key === 'password' && state.flow === 'register') {
                if (!isPasswordValid(text)) {
                    addMessage(text, 'user');
                    addMessage("That password doesn't match the requirements. Please try again with 8+ chars, uppercase, lowercase, number, and special character.", 'bot');
                    return;
                }
            }

            state.data[step.key] = text;
            addMessage(step.type === 'password' ? '••••••••' : text, 'user');
            state.stepIndex += 1;
            if (state.stepIndex >= flowSteps.length) {
                state.awaitingInput = false;
                state.readyToContinue = true;
                addMessage('Thanks! Please complete the captcha, then press Continue.', 'bot');
                showCaptcha();
            } else {
                addMessage(flowSteps[state.stepIndex].prompt, 'bot');
            }
            return;
        }

        addMessage(text, 'user');
        sendToGemini(text);
    }

    async function sendToGemini(message) {
        addMessage('Typing…', 'meta');
        try {
            const res = await fetch(`${nodeBase}/api/auth/chat`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message })
            });
            const data = await res.json();
            removeMeta();
            addMessage(data.reply || 'I can only help with login and account access questions.', 'bot');
        } catch (err) {
            removeMeta();
            addMessage('Sorry, I can only help with login right now. Try one of the options.', 'bot');
        }
    }

    function removeMeta() {
        const metas = chatMessages.querySelectorAll('.auth-message.meta');
        metas.forEach(m => m.remove());
    }

    async function handleContinue() {
        if (!state.readyToContinue) return;
        const captchaToken = window.grecaptcha ? grecaptcha.getResponse() : '';
        if (!captchaToken) {
            addMessage('Please complete the captcha first.', 'bot');
            return;
        }

        if (state.flow === 'login') {
            await submitLogin(captchaToken);
        } else if (state.flow === 'register') {
            await submitRegister(captchaToken);
        } else if (state.flow === 'magic') {
            await submitMagic(captchaToken);
        }
    }

    async function submitLogin(captchaToken) {
        showLoading(true);
        try {
            const formData = new FormData();
            formData.append('e', state.data.email || '');
            formData.append('p', state.data.password || '');
            formData.append('g-recaptcha-response', captchaToken);
            const res = await fetch(`${appBase}/api/auth.php?act=login`, { method: 'POST', body: formData });
            const data = await res.json();
            showLoading(false);
            if (data.e) {
                if (window.grecaptcha) grecaptcha.reset();
                if (data.resend && data.email) {
                    addMessage(data.m || 'Please verify your email address.', 'bot');
                    addActionButtons([
                        { html: '<i class="fa fa-paper-plane"></i> Resend verification email', onClick: () => resendVerificationEmail(data.email) },
                        { html: '<i class="fa fa-rotate-left"></i> Back to options', onClick: showMenu },
                    ]);
                    return;
                }
                addMessage(normalizeLoginError(data.m || 'Login failed.'), 'bot');
                addActionButtons([{ html: '<i class="fa fa-arrow-rotate-right"></i> Try again', onClick: () => startFlow('login') }]);
                return;
            }
            addMessage('Login successful. Redirecting…', 'bot');
            setTimeout(() => { window.location.href = `${appBase}/app`; }, 800);
        } catch (err) {
            showLoading(false);
            if (window.grecaptcha) grecaptcha.reset();
            addMessage('Login failed. Please try again.', 'bot');
        }
    }

    async function submitRegister(captchaToken) {
        showLoading(true);
        try {
            if (!isPasswordValid(state.data.password || '')) {
                showLoading(false);
                addMessage("That password doesn't match the requirements. Please try again.", 'bot');
                hideCaptcha();
                startFlow('register');
                return;
            }
            const formData = new FormData();
            formData.append('f', state.data.first_name || '');
            formData.append('l', state.data.last_name || '');
            formData.append('e', state.data.email || '');
            formData.append('t', state.data.phone || '');
            formData.append('p', state.data.password || '');
            formData.append('g-recaptcha-response', captchaToken);
            const res = await fetch(`${appBase}/api/auth.php?act=register`, { method: 'POST', body: formData });
            const data = await res.json();
            showLoading(false);
            if (data.e) {
                if (window.grecaptcha) grecaptcha.reset();
                addMessage(data.m || 'Registration failed.', 'bot');
                addActionButtons([{ html: '<i class="fa fa-arrow-rotate-right"></i> Try again', onClick: () => startFlow('register') }]);
                return;
            }
            addMessage('Account created! Please check your email to verify, then sign in.', 'bot');
            addActionButtons([{ html: '<i class="fa fa-rotate-left"></i> Back to options', onClick: showMenu }]);
        } catch (err) {
            showLoading(false);
            if (window.grecaptcha) grecaptcha.reset();
            addMessage('Registration failed. Please try again.', 'bot');
        }
    }

    async function submitMagic(captchaToken) {
        showLoading(true);
        try {
            const formData = new FormData();
            formData.append('email', state.data.email || '');
            formData.append('captcha', captchaToken);
            const res = await fetch(`${appBase}/api/auth.php?act=magic`, { method: 'POST', body: formData });
            const data = await res.json();
            showLoading(false);
            if (data.e) {
                if (window.grecaptcha) grecaptcha.reset();
                addMessage(data.m || 'Unable to send login link.', 'bot');
                addActionButtons([{ html: '<i class="fa fa-arrow-rotate-right"></i> Try again', onClick: () => startFlow('magic') }]);
                return;
            }
            addMessage('Login link sent! Check your email. The link expires in 5 minutes.', 'bot');
            addActionButtons([{ html: '<i class="fa fa-rotate-left"></i> Back to options', onClick: showMenu }]);
        } catch (err) {
            showLoading(false);
            if (window.grecaptcha) grecaptcha.reset();
            addMessage('Unable to send login link. Please try again.', 'bot');
        }
    }

    function showLoading(show) {
        loadingOverlay.style.display = show ? 'flex' : 'none';
    }

    async function resendVerificationEmail(email) {
        showLoading(true);
        try {
            const formData = new FormData();
            formData.append('email', email);
            const res = await fetch(`${appBase}/auth/resend-verification`, { method: 'POST', body: formData });
            const data = await res.json();
            showLoading(false);
            if (data.e) {
                addMessage(data.m || 'Failed to resend verification email.', 'bot');
            } else {
                addMessage('Verification email sent. Check your inbox.', 'bot');
            }
        } catch (err) {
            showLoading(false);
            addMessage('Failed to resend verification email.', 'bot');
        }
    }

    function attachEvents() {
        sendBtn.addEventListener('click', () => {
            const text = input.value.trim();
            input.value = '';
            onUserInput(text);
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                const text = input.value.trim();
                input.value = '';
                onUserInput(text);
            }
        });
        restartBtn.addEventListener('click', () => {
            chatMessages.innerHTML = '';
            showMenu();
        });
        showOptionsBtn.addEventListener('click', () => {
            addMessage('Here are your options again.', 'bot');
            addActionButtons([
                { html: '<i class="fa fa-envelope"></i> Email', onClick: () => startFlow('login') },
                { html: '<i class="fab fa-google"></i> Google', onClick: () => handleOauth('google') },
                { html: '<i class="fa fa-earth-asia"></i> Globbook', onClick: () => handleOauth('globbook') },
                { html: '<i class="fa fa-user-plus"></i> I\'m new', onClick: () => startFlow('register') },
                { html: '<i class="fa fa-key"></i> Damn I forgot password', onClick: () => startFlow('magic') },
            ]);
        });
        continueBtn.addEventListener('click', handleContinue);
    }

    window.onAuthCaptchaSuccess = () => {
        continueBtn.disabled = false;
    };
    window.onAuthCaptchaExpired = () => {
        continueBtn.disabled = true;
    };

    function initFromQuery() {
        const params = new URLSearchParams(window.location.search);
        const mode = params.get('mode');
        const error = params.get('error');
        if (error === 'magic_expired') {
            addMessage('That login link has expired or was already used.', 'bot');
        }
        if (mode === 'register') {
            startFlow('register');
            return;
        }
        showMenu();
    }

    function extractEmail(text) {
        const match = text.match(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i);
        return match ? match[0] : '';
    }

    function extractPassword(text) {
        const match = text.match(/(?:password|pass)\s*[:=]?\s*([^\s]+)/i);
        return match ? match[1] : '';
    }

    function detectIntent(text) {
        const lower = text.toLowerCase();
        if (/(reset|forgot).*(password|pass)|magic|login link/.test(lower)) return 'magic';
        if (/(sign\s*up|register|create\s+account|i'?m\s+new)/.test(lower)) return 'register';
        if (/(login|sign\s*in|log\s*me\s*in)/.test(lower)) return 'login';
        return null;
    }

    function extractCredentials(text) {
        return {
            email: extractEmail(text),
            password: extractPassword(text),
        };
    }

    function applyInputToFlow(text, flowSteps) {
        const extracted = extractCredentials(text);
        let used = false;

        if (extracted.email && !state.data.email) {
            state.data.email = extracted.email;
            used = true;
        }
        if (extracted.password && !state.data.password) {
            if (state.flow === 'register' && !isPasswordValid(extracted.password)) {
                addMessage("That password doesn't match the requirements. Please try again with 8+ chars, uppercase, lowercase, number, and special character.", 'bot');
                return { used: true, display: text };
            }
            state.data.password = extracted.password;
            used = true;
        }

        if (used) {
            while (state.stepIndex < flowSteps.length && state.data[flowSteps[state.stepIndex].key]) {
                state.stepIndex += 1;
            }
            if (state.stepIndex >= flowSteps.length) {
                state.awaitingInput = false;
            } else {
                addMessage(flowSteps[state.stepIndex].prompt, 'bot');
            }
            return { used: true, display: text };
        }

        return { used: false, display: text };
    }

    function isPasswordValid(password) {
        return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*(),.?":{}|<>]).{8,}$/.test(password);
    }

    function normalizeLoginError(message) {
        if (!message) return 'Login failed.';
        const lower = message.toLowerCase();
        if (lower.includes('invalid credentials')) return 'Sorry, your email or password is incorrect.';
        if (lower.includes('invalid password')) return message;
        if (lower.includes('verify your email')) return message;
        if (lower.includes('captcha')) return message;
        return message;
    }

    attachEvents();
    initFromQuery();
})();
