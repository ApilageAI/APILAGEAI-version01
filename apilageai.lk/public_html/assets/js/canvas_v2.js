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
        el._timer = setTimeout(() => el.classList.remove('visible'), duration);
    }

    async function apiPost(payload) {
        const res = await fetch(`${APP_URL}/api/whiteboard.php`, {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        return res.json();
    }

    async function loadBoardCode() {
        const res = await apiPost({ action: 'get', conversation_id: conversationId });
        if (!res || !res.success) {
            throw new Error(res?.error || 'Failed to load board');
        }
        return String(res.board_code || '');
    }

    async function initWhiteboard(convId) {
        if (initInFlight) return;
        initInFlight = true;
        try {
            conversationId = Number(convId || 0) || null;
            if (!conversationId) {
                showStatus('Open a chat to use the whiteboard');
                return;
            }
            if (!CLIENT_ID) {
                showStatus('Missing Whiteboard Team client id');
                return;
            }
            if (!window.api || !window.api.WhiteboardTeam) {
                showStatus('Whiteboard SDK not loaded');
                return;
            }

            const container = document.getElementById('wt-container');
            if (!container) return;

            boardCode = await loadBoardCode();
            if (!boardCode) {
                showStatus('Failed to resolve board');
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
            wt.addListener('ready', () => {
                isReady = true;
                showStatus('Whiteboard ready');
            });
            wt.addListener('error', (error) => {
                console.error('Whiteboard error', error);
                showStatus('Whiteboard failed to load');
            });
        } finally {
            initInFlight = false;
        }
    }

    function setConversation(id) {
        const nextId = Number(id || 0) || null;
        if (nextId === conversationId) return;
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
            else showStatus(res?.error || 'Save failed');
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
            const data = await res.json();
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

        const syncConversation = () => {
            const cid = getConversationId();
            if (!cid) return;
            if (cid !== conversationId) setConversation(cid);
        };

        const observer = new MutationObserver(() => {
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
    }

    bindUI();
    setupAutoBoot();

    window.CanvasV2 = { init: initWhiteboard, setConversation };
})();
