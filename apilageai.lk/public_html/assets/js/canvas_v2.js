/**
 * ApilageAI Whiteboard – Whiteboard.Team integration
 * Handles: board boot per chat, AI suggestions, server-side saving
 */
(function () {
    'use strict';

    let wt = null;
    let conversationId = null;
    let boardCode = null;
    let isReady = false;
    let initInFlight = false;
    let sdkLoadPromise = null;
    let initSeq = 0;
    let readyTimeoutId = null;
    let retryTimer = null;
    let retryCount = 0;
    let isConnecting = false;
    const MAX_RETRIES = 4;

    const APP_URL = window.APP_URL || window.APP_BASE_URL || '';
    const CLIENT_ID = window.WHITEBOARD_TEAM_CLIENT_ID || '';

    const BASE_W = 1600;
    const BASE_H = 900;
    const ORIGIN_X = 120;
    const ORIGIN_Y = 120;

    function getConversationId() {
        if (window.currentConversationId) return Number(window.currentConversationId) || 0;
        if (typeof window.getConversationIdFromURL === 'function') {
            return Number(window.getConversationIdFromURL()) || 0;
        }
        const match = window.location.pathname.match(/\/app\/chat\/(\d+)/);
        return match ? Number(match[1]) || 0 : 0;
    }

    function getUserName() {
        const first = String(window.userData?.first_name || '').trim();
        const last = String(window.userData?.last_name || '').trim();
        const full = `${first} ${last}`.trim();
        return full || String(window.userData?.name || 'User');
    }

    function showStatus(message, duration = 2200) {
        const el = document.getElementById('wt-status');
        if (!el) return;
        el.textContent = message;
        el.classList.add('visible');
        clearTimeout(el._timer);
        if (duration && duration > 0) {
            el._timer = setTimeout(() => el.classList.remove('visible'), duration);
        }
    }

    function ensureSdkLoaded() {
        if (window.api && window.api.WhiteboardTeam) return Promise.resolve(true);
        if (sdkLoadPromise) return sdkLoadPromise;

        sdkLoadPromise = new Promise((resolve, reject) => {
            const existing = document.querySelector('script[data-wt-sdk]') ||
                document.querySelector('script[src*="whiteboard.team/dist/api.js"]');
            if (existing) {
                existing.addEventListener('load', () => resolve(true), { once: true });
                existing.addEventListener('error', () => reject(new Error('Whiteboard SDK failed to load')), { once: true });
            } else {
                const script = document.createElement('script');
                script.src = 'https://www.whiteboard.team/dist/api.js';
                script.async = true;
                script.dataset.wtSdk = '1';
                script.onload = () => resolve(true);
                script.onerror = () => reject(new Error('Whiteboard SDK failed to load'));
                document.head.appendChild(script);
            }

            // Safety timeout
            setTimeout(() => {
                if (window.api && window.api.WhiteboardTeam) resolve(true);
                else reject(new Error('Whiteboard SDK not available'));
            }, 8000);
        });

        return sdkLoadPromise;
    }

    async function apiPost(payload) {
        const res = await fetch(`${APP_URL}/api/whiteboard.php`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        let data = null;
        try {
            data = await res.json();
        } catch (_) { }
        if (!res.ok) {
            const msg = data?.error || data?.m || `Request failed (${res.status})`;
            throw new Error(msg);
        }
        return data || {};
    }

    async function loadBoardCode() {
        const res = await apiPost({ action: 'get', conversation_id: conversationId });
        const ok = res && (res.success === true || res.e === false);
        if (!ok) {
            throw new Error(res?.error || res?.m || 'Failed to load board');
        }
        return String(res.board_code || res.boardCode || '');
    }

    function scheduleRetry(reason) {
        if (retryTimer || initInFlight) return;
        if (retryCount >= MAX_RETRIES) {
            showStatus('Whiteboard connection failed. Please try again.', 0);
            return;
        }
        retryCount += 1;
        const delay = Math.min(8000, 1200 * retryCount);
        showStatus('Reconnecting whiteboard…', 0);
        retryTimer = setTimeout(() => {
            retryTimer = null;
            try { wt?.destroy?.(); } catch (_) { }
            wt = null;
            isReady = false;
            isConnecting = false;
            initWhiteboard(conversationId);
        }, delay);
    }

    async function initWhiteboard(convId) {
        if (initInFlight) return;
        initInFlight = true;
        try {
            const prevConversationId = conversationId;
            conversationId = Number(convId || 0) || null;
            if (!conversationId) {
                showStatus('Open a chat to use the whiteboard', 0);
                return;
            }
            if (!CLIENT_ID) {
                showStatus('Missing Whiteboard Team client id', 0);
                return;
            }
            if (prevConversationId !== conversationId) {
                retryCount = 0;
            }
            showStatus('Loading whiteboard…', 0);
            try {
                await ensureSdkLoaded();
            } catch (err) {
                console.error('Whiteboard SDK error', err);
                showStatus('Whiteboard SDK not loaded', 0);
                return;
            }
            if (!window.api || !window.api.WhiteboardTeam) {
                showStatus('Whiteboard SDK not loaded', 0);
                return;
            }

            const container = document.getElementById('wt-container');
            if (!container) return;

            try {
                boardCode = await loadBoardCode();
            } catch (err) {
                console.error('Whiteboard load error', err);
                showStatus(err?.message || 'Failed to load board', 0);
                return;
            }
            if (!boardCode) {
                showStatus('Failed to resolve board', 0);
                return;
            }

            if (wt && typeof wt.destroy === 'function') {
                try { wt.destroy(); } catch (_) { }
            }
            container.innerHTML = '';

            wt = new window.api.WhiteboardTeam('#wt-container', {
                clientId: CLIENT_ID,
                boardCode,
                participant: {
                    role: 'editor',
                    name: getUserName(),
                    permissions: ['view_chat', 'view_templates'],
                },
            });

            isReady = false;
            isConnecting = true;
            const currentInit = ++initSeq;
            clearTimeout(readyTimeoutId);
            readyTimeoutId = setTimeout(() => {
                if (initSeq !== currentInit || isReady) return;
                scheduleRetry('timeout');
            }, 20000);

            wt.addListener('ready', () => {
                if (initSeq !== currentInit) return;
                isReady = true;
                isConnecting = false;
                retryCount = 0;
                clearTimeout(readyTimeoutId);
                showStatus('Whiteboard ready', 1200);
            });
            wt.addListener('error', (error) => {
                if (initSeq !== currentInit) return;
                console.error('Whiteboard error', error);
                showStatus('Whiteboard connection failed. Retrying…', 0);
                isConnecting = false;
                scheduleRetry('error');
            });

            // Promise-based readiness (as per Whiteboard Team docs) as a fallback.
            if (typeof wt.waitUntilReady === 'function') {
                wt.waitUntilReady()
                    .then(() => {
                        if (initSeq !== currentInit || isReady) return;
                        isReady = true;
                        isConnecting = false;
                        retryCount = 0;
                        clearTimeout(readyTimeoutId);
                        showStatus('Whiteboard ready', 1200);
                    })
                    .catch((err) => {
                        if (initSeq !== currentInit) return;
                        console.error('Whiteboard waitUntilReady error', err);
                        showStatus('Whiteboard connection failed. Retrying…', 0);
                        isConnecting = false;
                        scheduleRetry('wait');
                    });
            }
        } finally {
            initInFlight = false;
        }
    }

    function setConversation(id) {
        const nextId = Number(id || 0) || null;
        if (nextId === conversationId && wt && (isReady || isConnecting || initInFlight)) return;
        initWhiteboard(nextId);
    }

    function isSidebarVisible(sidebar) {
        if (!sidebar) return false;
        if (sidebar.getAttribute('aria-hidden') === 'true') return false;
        if (sidebar.style.display === 'none') return false;
        return sidebar.classList.contains('active');
    }

    function normalizeImageString(image) {
        if (!image) return '';
        if (typeof image !== 'string') return '';
        if (image.startsWith('data:image')) return image;
        if (/^[A-Za-z0-9+/=]+$/.test(image)) return `data:image/png;base64,${image}`;
        return image;
    }

    async function getBoardImage() {
        if (!wt || !isReady || typeof wt.getImage !== 'function') return '';
        try {
            const img = await wt.getImage('white');
            return normalizeImageString(img);
        } catch (_) {
            return '';
        }
    }

    async function getBoardShapes() {
        if (!wt || !isReady || !wt.board || typeof wt.board.get !== 'function') return [];
        try {
            const shapes = await wt.board.get();
            return Array.isArray(shapes) ? shapes : [];
        } catch (_) {
            return [];
        }
    }

    async function saveWhiteboard() {
        if (!conversationId) return;
        if (!wt || !isReady) {
            showStatus('Whiteboard not ready');
            return;
        }
        showStatus('Saving…', 1200);
        try {
            const [shapes, image] = await Promise.all([getBoardShapes(), getBoardImage()]);
            const res = await apiPost({
                action: 'save',
                conversation_id: conversationId,
                shapes,
                image,
            });
            if (res && res.success) showStatus('Saved to server');
            else if (res && res.e === false) showStatus('Saved to server');
            else showStatus(res?.error || res?.m || 'Save failed');
        } catch (e) {
            console.error(e);
            showStatus('Save failed');
        }
    }

    function applyElement(el) {
        if (!wt || !isReady || !el) return;
        const x = ORIGIN_X + (Number(el.x) || 0.1) * BASE_W;
        const y = ORIGIN_Y + (Number(el.y) || 0.1) * BASE_H;
        const w = Math.max(80, (Number(el.w) || 0.2) * BASE_W);
        const h = Math.max(60, (Number(el.h) || 0.15) * BASE_H);

        if (el.type === 'sticky') {
            const text = String(el.text || '').trim() || 'Note';
            const color = String(el.bg || '#ffe082');
            const size = Math.max(10, Math.min(28, Number(el.font_size || 14)));
            wt.drawStickyNote(x, y, w, h, text, color, size);
            return;
        }

        if (el.type === 'text') {
            const text = String(el.text || '').trim();
            if (!text) return;
            const color = String(el.color || '#1a1a2e');
            const size = Math.max(10, Math.min(72, Number(el.font_size || 16)));
            wt.drawText(x, y, text, color, 'Arial', size);
            return;
        }

        if (el.type === 'shape') {
            const stroke = String(el.stroke || '#1565c0');
            const fill = String(el.fill || '#e3f2fd');
            const width = Math.max(1, Math.min(12, Number(el.stroke_width || 2)));
            if (String(el.shape) === 'ellipse') {
                const r = Math.max(20, Math.min(w, h) / 2);
                wt.drawCircle(x + r, y + r, r, stroke, width, fill);
            } else {
                wt.drawRectangle(x, y, w, h, stroke, width, fill);
            }
        }
    }

    function applyElements(elements) {
        if (!Array.isArray(elements)) return;
        elements.forEach(applyElement);
        if (wt && typeof wt.fitToScreen === 'function') {
            try { wt.fitToScreen(); } catch (_) { }
        }
    }

    async function sendAIRequest() {
        const promptEl = document.getElementById('wt-ai-prompt');
        const prompt = promptEl?.value?.trim();
        if (!prompt) return;
        if (!conversationId) return;
        if (!wt || !isReady) {
            showStatus('Whiteboard not ready');
            return;
        }

        const sendBtn = document.getElementById('wt-ai-send');
        if (sendBtn) sendBtn.disabled = true;
        showStatus('AI thinking…', 1500);

        try {
            const [shapes, image] = await Promise.all([getBoardShapes(), getBoardImage()]);
            const res = await fetch(`${APP_URL}/api/canvas_ai.php`, {
                method: 'POST',
                credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    conversation_id: conversationId,
                    prompt,
                    board_shapes: shapes,
                    board_image: image,
                }),
            });
            let data = null;
            let text = '';
            try {
                text = await res.text();
                data = text ? JSON.parse(text) : null;
            } catch (_) {
                data = null;
            }
            if (!res.ok || !data) {
                const msg = data?.error || data?.m || (text && text.trim() ? 'AI error (non-JSON response)' : 'AI error');
                showStatus(msg);
                return;
            }
            if (!data.success) {
                showStatus(data.error || 'AI error');
                return;
            }
            applyElements(data.elements || []);
            showStatus(`AI: ${data.message || 'Done'}`);
            if (promptEl) promptEl.value = '';
        } catch (e) {
            console.error(e);
            showStatus('AI request failed');
        } finally {
            if (sendBtn) sendBtn.disabled = false;
        }
    }

    function bindUI() {
        document.addEventListener('click', (e) => {
            if (e.target.closest('#wt-ai-toggle')) {
                const panel = document.getElementById('wt-ai-panel');
                if (panel) panel.style.display = panel.style.display === 'none' ? 'flex' : 'none';
            }
            if (e.target.closest('#wt-ai-send')) {
                sendAIRequest();
            }
            if (e.target.closest('#wt-ai-suggest')) {
                const inp = document.getElementById('wt-ai-prompt');
                if (inp) inp.value = 'Suggest improvements and add helpful elements';
                sendAIRequest();
            }
            if (e.target.closest('#wt-save-btn')) {
                saveWhiteboard();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                const promptEl = document.getElementById('wt-ai-prompt');
                if (promptEl && document.activeElement === promptEl) {
                    e.preventDefault();
                    sendAIRequest();
                }
            }
        });
    }

    function setupAutoBoot() {
        const sidebar = document.getElementById('rightsidebar2');
        if (!sidebar) return;

        let lastPrefetchId = null;

        const syncConversation = () => {
            const cid = getConversationId();
            if (!cid) {
                if (isSidebarVisible(sidebar)) showStatus('Open a chat to use the whiteboard', 0);
                return;
            }
            if (cid !== conversationId || !wt) setConversation(cid);
        };

        const observer = new MutationObserver(() => {
            if (!isSidebarVisible(sidebar)) {
                const activeEl = document.activeElement;
                if (activeEl && sidebar.contains(activeEl)) {
                    try { activeEl.blur(); } catch (_) { }
                }
                return;
            }
            if (!isSidebarVisible(sidebar)) return;
            if (!conversationId) {
                syncConversation();
            }
        });
        observer.observe(sidebar, { attributes: true, attributeFilter: ['aria-hidden', 'class', 'style'] });

        document.addEventListener('canvas_opened', () => {
            syncConversation();
        });

        setInterval(() => {
            if (!isSidebarVisible(sidebar)) return;
            syncConversation();
        }, 1200);

        // Pre-warm in the background so opening the sidebar feels instant.
        setInterval(() => {
            if (isSidebarVisible(sidebar)) return;
            const cid = getConversationId();
            if (!cid) return;
            if (cid === lastPrefetchId && wt && (isReady || isConnecting)) return;
            lastPrefetchId = cid;
            setConversation(cid);
        }, 6000);
    }

    bindUI();
    setupAutoBoot();

    window.CanvasV2 = { init: initWhiteboard, setConversation };
})();
