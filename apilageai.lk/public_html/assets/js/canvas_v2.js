/**
 * ApilageAI Canvas v2 – Fabric.js collaborative whiteboard
 * Handles: drawing tools, shapes, stickies, text, PDF from Drive, AI write, real-time collab
 */
(function () {
    'use strict';

    // ── State ────────────────────────────────────────────────────────────────────
    let fabricCanvas = null;
    let socket = null;
    let conversationId = null;
    let currentTool = 'select';
    let drawColor = '#1a1a2e';
    let drawSize = 3;
    let isDrawing = false;
    let drawPath = null;
    let drawPoints = [];
    let driveConnected = false;
    let openPdfFileId = null;
    let openPdfText = '';
    let pdfDoc = null;
    let undoStack = [];   // local undo – array of element ids added this session
    const APP_URL = window.APP_URL || window.APP_BASE_URL || '';

    // ── Init ─────────────────────────────────────────────────────────────────────
    function init(sock, convId) {
        socket = sock;
        conversationId = null;

        const wrapper = document.getElementById('cv2-wrapper');
        if (!wrapper) return;

        const canvasEl = document.getElementById('cv2-canvas');
        if (!canvasEl) return;

        // size canvas to wrapper
        function resize() {
            const w = wrapper.clientWidth || 900;
            const h = wrapper.clientHeight || 600;
            if (fabricCanvas) {
                fabricCanvas.setWidth(w);
                fabricCanvas.setHeight(h);
                fabricCanvas.renderAll();
            }
        }

        fabricCanvas = new fabric.Canvas('cv2-canvas', {
            isDrawingMode: false,
            selection: true,
            backgroundColor: '#ffffff',
        });

        resize();
        window.addEventListener('resize', resize);

        bindToolbar();
        bindSocketEvents();
        bindCanvasEvents();
        checkDriveStatus();

        // load existing state
        setConversation(convId);
    }

    // ── Tool helpers ─────────────────────────────────────────────────────────────
    function setTool(tool) {
        currentTool = tool;
        document.querySelectorAll('.cv2-tool-btn').forEach(b => b.classList.remove('active', 'is-active'));
        const btn = document.querySelector(`[data-tool="${tool}"]`);
        if (btn) btn.classList.add('active', 'is-active');

        if (!fabricCanvas) return;
        if (tool === 'pen' || tool === 'highlight') {
            fabricCanvas.isDrawingMode = true;
            fabricCanvas.freeDrawingBrush.color = tool === 'highlight'
                ? hexWithAlpha(drawColor, 0.35) : drawColor;
            fabricCanvas.freeDrawingBrush.width = tool === 'highlight' ? drawSize * 4 : drawSize;
        } else {
            fabricCanvas.isDrawingMode = false;
            fabricCanvas.selection = (tool === 'select');
            fabricCanvas.discardActiveObject();
            fabricCanvas.renderAll();
        }
    }

    function setConversation(id) {
        const nextId = Number(id || 0) || null;
        if (nextId === conversationId) return;
        conversationId = nextId;

        openPdfFileId = null;
        openPdfText = '';
        pdfDoc = null;
        undoStack = [];
        const badge = document.getElementById('cv2-pdf-badge');
        if (badge) { badge.textContent = ''; badge.style.display = 'none'; }

        if (fabricCanvas) {
            fabricCanvas.clear();
            fabricCanvas.backgroundColor = '#ffffff';
            fabricCanvas.renderAll();
        }

        if (!socket || !conversationId) return;
        socket.emit('canvas_get', { conversation_id: conversationId });
    }

    function hexWithAlpha(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r},${g},${b},${alpha})`;
    }

    function genId() {
        return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
    }

    function toFrac(px, dim) { return px / dim; }
    function fromFracX(f) { return f * fabricCanvas.getWidth(); }
    function fromFracY(f) { return f * fabricCanvas.getHeight(); }

    // ── Toolbar binding ──────────────────────────────────────────────────────────
    function bindToolbar() {
        document.querySelectorAll('.cv2-tool-btn[data-tool]').forEach(btn => {
            btn.addEventListener('click', () => setTool(btn.dataset.tool));
        });

        const colorPicker = document.getElementById('cv2-color');
        if (colorPicker) colorPicker.addEventListener('input', e => {
            drawColor = e.target.value;
            if (fabricCanvas && fabricCanvas.isDrawingMode) {
                fabricCanvas.freeDrawingBrush.color = drawColor;
            }
        });

        const sizePicker = document.getElementById('cv2-size');
        if (sizePicker) sizePicker.addEventListener('input', e => {
            drawSize = parseInt(e.target.value);
            if (fabricCanvas && fabricCanvas.isDrawingMode) {
                fabricCanvas.freeDrawingBrush.width = drawSize;
            }
        });

        // Shape buttons
        document.getElementById('cv2-btn-rect')?.addEventListener('click', () => addShape('rect'));
        document.getElementById('cv2-btn-ellipse')?.addEventListener('click', () => addShape('ellipse'));
        document.getElementById('cv2-btn-arrow')?.addEventListener('click', () => addArrow());
        document.getElementById('cv2-btn-sticky')?.addEventListener('click', () => addSticky());
        document.getElementById('cv2-btn-text')?.addEventListener('click', () => addTextBox());

        // Erase selected
        document.getElementById('cv2-btn-erase')?.addEventListener('click', eraseSelected);

        // Undo
        document.getElementById('cv2-btn-undo')?.addEventListener('click', () => {
            if (undoStack.length === 0) return;
            const id = undoStack.pop();
            socket.emit('canvas_undo', { conversation_id: conversationId, count: 1 });
        });

        // Clear all
        document.getElementById('cv2-btn-clear')?.addEventListener('click', () => {
            if (!confirm('Clear the entire canvas?')) return;
            socket.emit('canvas_clear', { conversation_id: conversationId });
        });

        // Drive buttons
        document.getElementById('cv2-drive-connect')?.addEventListener('click', connectDrive);
        document.getElementById('cv2-drive-save')?.addEventListener('click', saveToGDrive);
        document.getElementById('cv2-drive-list')?.addEventListener('click', listGDriveFiles);
        document.getElementById('cv2-drive-list-pdfs')?.addEventListener('click', listGDrivePDFs);

        // AI panel
        document.getElementById('cv2-ai-send')?.addEventListener('click', sendAIRequest);
        document.getElementById('cv2-ai-suggest')?.addEventListener('click', () => {
            const inp = document.getElementById('cv2-ai-prompt');
            if (inp) inp.value = 'Suggest how to improve this canvas and add helpful elements';
            sendAIRequest();
        });
        document.getElementById('cv2-ai-prompt')?.addEventListener('keydown', e => {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendAIRequest(); }
        });
    }

    // ── Canvas mouse events for shape tools ──────────────────────────────────────
    function bindCanvasEvents() {
        if (!fabricCanvas) return;

        // After free-draw path completed, emit to server
        fabricCanvas.on('path:created', e => {
            const path = e.path;
            const pts = path.path.map(cmd => ({ x: cmd[1] || 0, y: cmd[2] || 0 })).filter(p => p.x || p.y);
            const w = fabricCanvas.getWidth();
            const h = fabricCanvas.getHeight();
            const strokeData = {
                id: genId(),
                tool: currentTool === 'highlight' ? 'highlight' : 'pen',
                color: path.stroke,
                size: path.strokeWidth,
                alpha: currentTool === 'highlight' ? 0.35 : 1,
                points: pts.map(p => ({ x: toFrac(p.x, w), y: toFrac(p.y, h) })),
            };
            // Tag the fabric object with our id for undo
            path.apId = strokeData.id;
            undoStack.push(strokeData.id);
            socket.emit('canvas_stroke', { conversation_id: conversationId, stroke: strokeData });
        });

        // Cursor broadcast (throttled)
        let cursorTimer = 0;
        fabricCanvas.on('mouse:move', e => {
            const now = Date.now();
            if (now - cursorTimer < 50) return;
            cursorTimer = now;
            const p = e.absolutePointer || e.pointer;
            if (!p) return;
            socket.emit('canvas_cursor', {
                conversation_id: conversationId,
                x: toFrac(p.x, fabricCanvas.getWidth()),
                y: toFrac(p.y, fabricCanvas.getHeight()),
            });
        });

        // For rect/ellipse: drag to create
        let shapeOrigin = null;
        let activeShapeObj = null;

        fabricCanvas.on('mouse:down', e => {
            if (!['rect', 'ellipse'].includes(currentTool)) return;
            const p = e.absolutePointer || e.pointer;
            shapeOrigin = { x: p.x, y: p.y };
            const commonOpts = {
                left: p.x, top: p.y, width: 1, height: 1,
                fill: 'rgba(227,242,253,0.7)', stroke: drawColor, strokeWidth: 2,
                selectable: false,
            };
            activeShapeObj = currentTool === 'rect'
                ? new fabric.Rect(commonOpts)
                : new fabric.Ellipse({ ...commonOpts, rx: 1, ry: 1 });
            fabricCanvas.add(activeShapeObj);
        });

        fabricCanvas.on('mouse:move', e => {
            if (!shapeOrigin || !activeShapeObj) return;
            const p = e.absolutePointer || e.pointer;
            const w = Math.abs(p.x - shapeOrigin.x);
            const h = Math.abs(p.y - shapeOrigin.y);
            const left = Math.min(p.x, shapeOrigin.x);
            const top = Math.min(p.y, shapeOrigin.y);
            if (currentTool === 'rect') {
                activeShapeObj.set({ left, top, width: w, height: h });
            } else {
                activeShapeObj.set({ left, top, rx: w / 2, ry: h / 2 });
            }
            fabricCanvas.renderAll();
        });

        fabricCanvas.on('mouse:up', e => {
            if (!shapeOrigin || !activeShapeObj) return;
            const p = e.absolutePointer || e.pointer;
            const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
            const w = Math.abs(p.x - shapeOrigin.x) / cw;
            const h = Math.abs(p.y - shapeOrigin.y) / ch;

            if (w < 0.01 && h < 0.01) {
                fabricCanvas.remove(activeShapeObj);
                shapeOrigin = null; activeShapeObj = null;
                return;
            }

            const elId = genId();
            activeShapeObj.apId = elId;
            activeShapeObj.selectable = true;
            undoStack.push(elId);

            const el = {
                id: elId, type: 'shape',
                shape: currentTool,
                x: Math.min(shapeOrigin.x, p.x) / cw,
                y: Math.min(shapeOrigin.y, p.y) / ch,
                w, h,
                fill: 'rgba(227,242,253,0.7)', stroke: drawColor, stroke_width: 2,
            };
            socket.emit('canvas_shape', { conversation_id: conversationId, element: el });

            shapeOrigin = null; activeShapeObj = null;
        });

        // Object moved/scaled – emit canvas_move
        fabricCanvas.on('object:modified', e => {
            const obj = e.target;
            if (!obj || !obj.apId) return;
            const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
            const br = obj.getBoundingRect(true);
            socket.emit('canvas_move', {
                conversation_id: conversationId,
                element_id: obj.apId,
                x: br.left / cw, y: br.top / ch,
                w: br.width / cw, h: br.height / ch,
                angle: obj.angle || 0,
            });
        });
    }

    // ── Shape helpers ────────────────────────────────────────────────────────────
    function addShape(shape) {
        const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
        const elId = genId();
        const el = {
            id: elId, type: 'shape', shape,
            x: 0.3, y: 0.3, w: 0.2, h: 0.15,
            fill: shape === 'rect' ? '#e3f2fd' : '#fce4ec',
            stroke: shape === 'rect' ? '#1565c0' : '#c62828',
            stroke_width: 2,
        };
        renderElement(el);
        undoStack.push(elId);
        socket.emit('canvas_shape', { conversation_id: conversationId, element: el });
    }

    function addArrow() {
        const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
        const elId = genId();
        const line = new fabric.Line([cw * 0.3, ch * 0.5, cw * 0.6, ch * 0.5], {
            stroke: drawColor, strokeWidth: 3, selectable: true,
        });
        line.apId = elId;
        fabricCanvas.add(line);
        undoStack.push(elId);
        // Emit as a shape with shape='arrow'
        socket.emit('canvas_shape', {
            conversation_id: conversationId,
            element: { id: elId, type: 'shape', shape: 'arrow', x: 0.3, y: 0.5, w: 0.3, h: 0.01, stroke: drawColor, stroke_width: 3 },
        });
    }

    function addSticky() {
        const colors = ['#ffe082', '#a5d6a7', '#90caf9', '#ef9a9a', '#ce93d8'];
        const bg = colors[Math.floor(Math.random() * colors.length)];
        const elId = genId();
        const text = prompt('Sticky note text:') || 'Note';
        const el = { id: elId, type: 'sticky', x: 0.1 + Math.random() * 0.5, y: 0.1 + Math.random() * 0.4, w: 0.2, h: 0.15, text, bg, color: '#1a1a2e', font_size: 13 };
        renderElement(el);
        undoStack.push(elId);
        socket.emit('canvas_shape', { conversation_id: conversationId, element: el });
    }

    function addTextBox() {
        const elId = genId();
        const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
        const tb = new fabric.IText('Type here…', {
            left: cw * 0.3, top: ch * 0.3,
            fontSize: 18, fill: drawColor, fontFamily: 'Inter, sans-serif',
            editable: true, selectable: true,
        });
        tb.apId = elId;
        fabricCanvas.add(tb);
        fabricCanvas.setActiveObject(tb);
        tb.enterEditing();
        undoStack.push(elId);

        tb.on('editing:exited', () => {
            const txt = tb.text || '';
            socket.emit('canvas_shape', {
                conversation_id: conversationId,
                element: { id: elId, type: 'text', x: toFrac(tb.left, cw), y: toFrac(tb.top, ch), w: 0.3, h: 0.1, text: txt, color: drawColor, font_size: 18 },
            });
        });
    }

    function eraseSelected() {
        const objs = fabricCanvas.getActiveObjects();
        if (!objs.length) return;
        const ids = objs.map(o => o.apId).filter(Boolean);
        fabricCanvas.discardActiveObject();
        objs.forEach(o => fabricCanvas.remove(o));
        fabricCanvas.renderAll();
        if (ids.length) socket.emit('canvas_erase_element', { conversation_id: conversationId, element_ids: ids });
    }

    // ── Render element from server ───────────────────────────────────────────────
    function renderElement(el) {
        if (!fabricCanvas) return;
        const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
        const x = fromFracX(el.x), y = fromFracY(el.y);
        const w = fromFracX(el.w || 0.2), h = fromFracY(el.h || 0.15);
        let obj = null;

        if (el.type === 'sticky') {
            const g = [];
            g.push(new fabric.Rect({ width: w, height: h, fill: el.bg || '#ffe082', rx: 6, ry: 6, stroke: 'rgba(0,0,0,0.1)', strokeWidth: 1 }));
            g.push(new fabric.Textbox(el.text || '', { width: w - 12, left: 6, top: 6, fontSize: el.font_size || 13, fill: el.color || '#1a1a2e', fontFamily: 'Inter, sans-serif', editable: false }));
            obj = new fabric.Group(g, { left: x, top: y, selectable: true });

        } else if (el.type === 'text') {
            obj = new fabric.IText(el.text || '', {
                left: x, top: y,
                fontSize: el.font_size || 16,
                fill: el.color || '#1a1a2e',
                fontWeight: el.bold ? 'bold' : 'normal',
                fontFamily: 'Inter, sans-serif',
                editable: true, selectable: true,
            });

        } else if (el.type === 'shape') {
            const opts = { left: x, top: y, fill: el.fill || '#e3f2fd', stroke: el.stroke || '#1565c0', strokeWidth: el.stroke_width || 2, selectable: true };
            if (el.shape === 'ellipse') {
                obj = new fabric.Ellipse({ ...opts, rx: w / 2, ry: h / 2 });
            } else if (el.shape === 'arrow') {
                obj = new fabric.Line([x, y, x + w, y], { stroke: el.stroke || drawColor, strokeWidth: el.stroke_width || 3, selectable: true });
            } else {
                obj = new fabric.Rect({ ...opts, width: w, height: h });
            }

        } else if (el.type === 'image' || el.type === 'pdf_page') {
            if (el.src) {
                fabric.Image.fromURL(el.src, img => {
                    img.set({ left: x, top: y, scaleX: w / (img.width || 1), scaleY: h / (img.height || 1), selectable: true });
                    img.apId = el.id;
                    fabricCanvas.add(img);
                    fabricCanvas.renderAll();
                });
                return;
            }
        }

        if (obj) {
            obj.apId = el.id;
            fabricCanvas.add(obj);
            fabricCanvas.renderAll();
        }
    }

    // ── Render legacy stroke ─────────────────────────────────────────────────────
    function renderStroke(s) {
        if (!fabricCanvas || !s.points || s.points.length < 2) return;
        const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
        const pts = s.points.map(p => ({ x: fromFracX(p.x), y: fromFracY(p.y) }));
        const pathStr = pts.map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x} ${p.y}`).join(' ');
        const alpha = s.tool === 'highlight' ? 0.35 : (Number.isFinite(s.alpha) ? s.alpha : 1);
        const path = new fabric.Path(pathStr, {
            stroke: s.color || '#000',
            strokeWidth: s.size || 2,
            fill: '',
            opacity: alpha,
            selectable: true,
            evented: true,
        });
        path.apId = s.id;
        fabricCanvas.add(path);
    }

    // ── Socket events ────────────────────────────────────────────────────────────
    function bindSocketEvents() {
        if (!socket) return;

        socket.on('canvas_state', data => {
            if (data.conversation_id !== conversationId) return;
            fabricCanvas.clear();
            fabricCanvas.backgroundColor = '#ffffff';
            const d = data.data || {};

            (d.strokes || []).forEach(renderStroke);
            (d.texts || []).forEach(t => {
                const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
                const tb = new fabric.IText(t.text || '', {
                    left: fromFracX(t.x), top: fromFracY(t.y),
                    fontSize: t.size || 16, fill: t.color || '#000',
                    fontFamily: 'Inter, sans-serif', selectable: true, editable: true,
                });
                tb.apId = t.id;
                fabricCanvas.add(tb);
            });
            (d.elements || []).forEach(renderElement);

            // Restore open PDF reference
            if (d.open_pdf?.drive_file_id) {
                openPdfFileId = d.open_pdf.drive_file_id;
                showPdfBadge(d.open_pdf.file_name);
            }

            fabricCanvas.renderAll();
        });

        socket.on('canvas_stroke', data => {
            if (data.conversation_id !== conversationId) return;
            renderStroke(data.stroke);
            fabricCanvas.renderAll();
        });

        socket.on('canvas_shape', data => {
            if (data.conversation_id !== conversationId) return;
            renderElement(data.element);
        });

        socket.on('canvas_text', data => {
            if (data.conversation_id !== conversationId) return;
            renderElement({ ...data.entry, type: 'text', font_size: data.entry?.size });
        });

        socket.on('canvas_move', data => {
            if (data.conversation_id !== conversationId) return;
            const obj = fabricCanvas.getObjects().find(o => o.apId === data.element_id);
            if (!obj) return;
            const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
            if (Number.isFinite(data.x)) obj.set('left', fromFracX(data.x));
            if (Number.isFinite(data.y)) obj.set('top', fromFracY(data.y));
            if (Number.isFinite(data.angle)) obj.set('angle', data.angle);
            obj.setCoords();
            fabricCanvas.renderAll();
        });

        socket.on('canvas_erase_element', data => {
            if (data.conversation_id !== conversationId) return;
            const ids = new Set(data.element_ids || []);
            fabricCanvas.getObjects().filter(o => ids.has(o.apId)).forEach(o => fabricCanvas.remove(o));
            fabricCanvas.renderAll();
        });

        socket.on('canvas_load_pdf', data => {
            if (data.conversation_id !== conversationId) return;
            openPdfFileId = data.drive_file_id;
            showPdfBadge(data.file_name);
            // Only the user who opened it will render it; others see the badge
        });

        socket.on('canvas_ai_write', data => {
            if (data.conversation_id !== conversationId) return;
            (data.elements || []).forEach(renderElement);
            fabricCanvas.renderAll();
            showToast('🤖 AI: ' + (data.message || 'Done!'));
        });

        socket.on('canvas_doc', data => {
            if (data.conversation_id !== conversationId) return;
            // legacy doc_html - ignore in v2 canvas
        });

        // Collaborator cursors
        socket.on('canvas_cursor', data => {
            if (data.conversation_id !== conversationId || data.user_id === window.CURRENT_USER_ID) return;
            updateCollabCursor(data);
        });
    }

    // ── Collab cursors ───────────────────────────────────────────────────────────
    const cursorEls = {};
    function updateCollabCursor(data) {
        const overlay = document.getElementById('cv2-cursors');
        if (!overlay || !fabricCanvas) return;
        let el = cursorEls[data.user_id];
        if (!el) {
            el = document.createElement('div');
            el.className = 'cv2-cursor';
            el.innerHTML = `<svg width="14" height="14" viewBox="0 0 14 14"><path d="M0 0L0 12L3.5 9L6 13L8 12L5.5 8L10 8Z" fill="${randomColor(data.user_id)}"/></svg><span>${data.name || 'User'}</span>`;
            overlay.appendChild(el);
            cursorEls[data.user_id] = el;
        }
        el.style.left = (data.x * fabricCanvas.getWidth()) + 'px';
        el.style.top = (data.y * fabricCanvas.getHeight()) + 'px';
        el.style.opacity = '1';
        clearTimeout(el._hide);
        el._hide = setTimeout(() => { el.style.opacity = '0'; }, 3000);
    }

    function randomColor(seed) {
        const colors = ['#e53935', '#8e24aa', '#1e88e5', '#00897b', '#f4511e', '#6d4c41'];
        return colors[Math.abs(Number(seed) || 0) % colors.length];
    }

    // ── Google Drive ─────────────────────────────────────────────────────────────
    async function checkDriveStatus() {
        try {
            const res = await fetch(`${APP_URL}/api/drive_canvas.php`, {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'status' }),
            });
            const d = await res.json();
            driveConnected = d.connected;
            updateDriveUI();
        } catch (e) { }
    }

    function updateDriveUI() {
        const btn = document.getElementById('cv2-drive-connect');
        const badge = document.getElementById('cv2-drive-badge');
        if (btn) btn.textContent = driveConnected ? '✅ Drive Connected' : '🔗 Connect Drive';
        if (badge) badge.style.display = driveConnected ? 'inline-flex' : 'none';
        ['cv2-drive-save', 'cv2-drive-list', 'cv2-drive-list-pdfs'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.disabled = !driveConnected;
        });
    }

    function connectDrive() {
        window.location.href = `${APP_URL}/api/drive_canvas.php?connect`;
    }

    async function saveToGDrive() {
        showToast('Saving to Drive…');
        const json = JSON.stringify(fabricCanvas.toJSON(['apId']));
        try {
            const listRes = await drivePost({ action: 'list' });
            const existing = (listRes.files || []).find(f => f.name === `canvas_${conversationId}.json`);
            let res;
            if (existing) {
                res = await drivePost({ action: 'save', fileId: existing.id, content: json });
            } else {
                res = await drivePost({ action: 'create', name: `canvas_${conversationId}.json`, content: json });
            }
            showToast(res.success ? '✅ Saved to Drive!' : '❌ Save failed');
        } catch (e) { showToast('❌ Drive error'); }
    }

    async function listGDriveFiles() {
        try {
            const res = await drivePost({ action: 'list' });
            showDriveFilePicker(res.files || [], false);
        } catch (e) { showToast('❌ Could not load files'); }
    }

    async function listGDrivePDFs() {
        try {
            const res = await drivePost({ action: 'list_pdfs' });
            showDriveFilePicker(res.files || [], true);
        } catch (e) { showToast('❌ Could not load PDFs'); }
    }

    function showDriveFilePicker(files, isPdf) {
        let modal = document.getElementById('cv2-drive-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'cv2-drive-modal';
            modal.className = 'cv2-modal';
            document.body.appendChild(modal);
        }
        modal.innerHTML = `
      <div class="cv2-modal-inner">
        <h4>${isPdf ? '📄 Open PDF from Drive' : '🗂 Load Canvas File'}</h4>
        <div class="cv2-file-list">${files.length ? files.map(f =>
            `<div class="cv2-file-item" data-id="${f.id}" data-name="${f.name}" data-pdf="${isPdf}">
            <span class="cv2-file-icon">${isPdf ? '📄' : '🗂'}</span>
            <span>${f.name}</span>
            <small>${f.modifiedTime ? new Date(f.modifiedTime).toLocaleDateString() : ''}</small>
          </div>`
        ).join('') : '<p>No files found.</p>'}</div>
        <button class="cv2-btn cv2-btn-ghost" id="cv2-drive-modal-close">Close</button>
      </div>`;
        modal.style.display = 'flex';
        modal.querySelector('#cv2-drive-modal-close').onclick = () => modal.style.display = 'none';
        modal.querySelectorAll('.cv2-file-item').forEach(item => {
            item.onclick = async () => {
                modal.style.display = 'none';
                const id = item.dataset.id, name = item.dataset.name;
                if (item.dataset.pdf === 'true') {
                    await openPdfFromDrive(id, name);
                } else {
                    await loadCanvasFromDrive(id);
                }
            };
        });
    }

    async function loadCanvasFromDrive(fileId) {
        showToast('Loading from Drive…');
        try {
            const res = await drivePost({ action: 'get', fileId });
            if (res.success && res.content) {
                const data = typeof res.content === 'string' ? JSON.parse(res.content) : res.content;
                fabricCanvas.loadFromJSON(data, () => fabricCanvas.renderAll());
                showToast('✅ Canvas loaded!');
            }
        } catch (e) { showToast('❌ Load failed'); }
    }

    async function openPdfFromDrive(fileId, fileName) {
        showToast('Loading PDF from Drive…');
        try {
            const res = await drivePost({ action: 'get_pdf_proxy', fileId });
            if (!res.success || !res.data) { showToast('❌ PDF load failed'); return; }

            const pdfBytes = Uint8Array.from(atob(res.data), c => c.charCodeAt(0));
            const loadingTask = pdfjsLib.getDocument({ data: pdfBytes });
            pdfDoc = await loadingTask.promise;

            openPdfFileId = fileId;
            openPdfText = '';
            showPdfBadge(fileName);

            // Broadcast to collaborators
            socket.emit('canvas_load_pdf', {
                conversation_id: conversationId,
                drive_file_id: fileId,
                file_name: fileName,
                page_count: pdfDoc.numPages,
            });

            // Render first 3 pages as image objects on canvas
            const pagesToRender = Math.min(pdfDoc.numPages, 3);
            for (let p = 1; p <= pagesToRender; p++) {
                await renderPDFPage(p, p);
            }

            // Extract text for AI context
            for (let p = 1; p <= Math.min(pdfDoc.numPages, 5); p++) {
                const page = await pdfDoc.getPage(p);
                const tc = await page.getTextContent();
                openPdfText += tc.items.map(i => i.str).join(' ') + '\n';
            }
            openPdfText = openPdfText.slice(0, 8000);
            showToast(`✅ PDF loaded (${pdfDoc.numPages} pages)`);
        } catch (e) { console.error(e); showToast('❌ PDF error: ' + e.message); }
    }

    async function renderPDFPage(pageNum, slotIndex) {
        const page = await pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: 1.2 });
        const offCanvas = document.createElement('canvas');
        offCanvas.width = viewport.width;
        offCanvas.height = viewport.height;
        await page.render({ canvasContext: offCanvas.getContext('2d'), viewport }).promise;

        const dataUrl = offCanvas.toDataURL('image/jpeg', 0.8);
        const cw = fabricCanvas.getWidth(), ch = fabricCanvas.getHeight();
        const scaledW = Math.min(cw * 0.45, viewport.width);
        const scaledH = (scaledW / viewport.width) * viewport.height;
        const x = (slotIndex - 1) * (scaledW + 20);
        const y = 20;

        fabric.Image.fromURL(dataUrl, img => {
            img.set({ left: x, top: y, scaleX: scaledW / img.width, scaleY: scaledH / img.height, selectable: true });
            const elId = genId();
            img.apId = elId;
            fabricCanvas.add(img);
            fabricCanvas.renderAll();

            // Broadcast page element so collaborators know about it
            socket.emit('canvas_shape', {
                conversation_id: conversationId,
                element: {
                    id: elId, type: 'pdf_page',
                    x: x / cw, y: y / ch,
                    w: scaledW / cw, h: scaledH / ch,
                    src: dataUrl, page: pageNum,
                    drive_file_id: openPdfFileId,
                },
            });
        });
    }

    function showPdfBadge(name) {
        const badge = document.getElementById('cv2-pdf-badge');
        if (badge) { badge.textContent = `📄 ${name || 'PDF open'}`; badge.style.display = 'inline-flex'; }
    }

    async function drivePost(body) {
        const res = await fetch(`${APP_URL}/api/drive_canvas.php`, {
            method: 'POST', credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body),
        });
        return res.json();
    }

    // ── AI Panel ─────────────────────────────────────────────────────────────────
    async function sendAIRequest() {
        const promptEl = document.getElementById('cv2-ai-prompt');
        const prompt = promptEl?.value?.trim();
        if (!prompt) return;

        const sendBtn = document.getElementById('cv2-ai-send');
        if (sendBtn) sendBtn.disabled = true;
        showToast('🤖 Thinking…');

        try {
            // Capture canvas screenshot
            const canvasImage = fabricCanvas.toDataURL({ format: 'jpeg', quality: 0.6, multiplier: 0.5 });

            const res = await fetch(`${APP_URL}/api/canvas_ai.php`, {
                method: 'POST', credentials: 'include',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    conversation_id: conversationId,
                    prompt,
                    canvas_doc_text: openPdfText,
                    canvas_image: canvasImage,
                }),
            });
            const data = await res.json();
            if (!data.success) { showToast('❌ ' + (data.error || 'AI error')); return; }

            // Render elements locally
            (data.elements || []).forEach(renderElement);
            fabricCanvas.renderAll();

            // Broadcast to collaborators via socket
            socket.emit('canvas_ai_write', {
                conversation_id: conversationId,
                elements: data.elements || [],
                message: data.message || '',
            });

            showToast('🤖 ' + (data.message || 'Done!'));
            if (promptEl) promptEl.value = '';
        } catch (e) {
            showToast('❌ AI request failed');
        } finally {
            if (sendBtn) sendBtn.disabled = false;
        }
    }

    // ── Toast ────────────────────────────────────────────────────────────────────
    function showToast(msg) {
        let t = document.getElementById('cv2-toast');
        if (!t) {
            t = document.createElement('div');
            t.id = 'cv2-toast';
            t.className = 'cv2-toast';
            document.body.appendChild(t);
        }
        t.textContent = msg;
        t.classList.add('visible');
        clearTimeout(t._timer);
        t._timer = setTimeout(() => t.classList.remove('visible'), 3500);
    }

    // ── Auto-boot (fallback) ────────────────────────────────────────────────────
    function getConversationId() {
        if (window.currentConversationId) return Number(window.currentConversationId) || 0;
        if (typeof window.getConversationIdFromURL === 'function') {
            return Number(window.getConversationIdFromURL()) || 0;
        }
        const match = window.location.pathname.match(/\/app\/chat\/(\d+)/);
        return match ? Number(match[1]) || 0 : 0;
    }

    function isCanvasSidebarVisible(sidebar) {
        if (!sidebar) return false;
        if (sidebar.getAttribute('aria-hidden') === 'true') return false;
        if (sidebar.style.display === 'none') return false;
        return sidebar.classList.contains('active');
    }

    function tryAutoInit() {
        if (window._CanvasV2Inited) return;
        if (!window.socket) return;
        const sidebar = document.getElementById('rightsidebar2');
        if (!isCanvasSidebarVisible(sidebar)) return;
        window._CanvasV2Inited = true;
        init(window.socket, getConversationId());
    }

    function setupAutoBoot() {
        const sidebar = document.getElementById('rightsidebar2');
        if (!sidebar) return;

        const observer = new MutationObserver(() => {
            if (!window._CanvasV2Inited) {
                tryAutoInit();
                return;
            }
            if (isCanvasSidebarVisible(sidebar)) {
                setConversation(getConversationId());
            }
        });
        observer.observe(sidebar, { attributes: true, attributeFilter: ['aria-hidden', 'class', 'style'] });

        const interval = setInterval(() => {
            if (window._CanvasV2Inited) { clearInterval(interval); return; }
            tryAutoInit();
        }, 300);

        document.addEventListener('canvas_opened', () => {
            if (window._CanvasV2Inited) setConversation(getConversationId());
            else tryAutoInit();
        });
    }

    // ── Public API ───────────────────────────────────────────────────────────────
    window.CanvasV2 = { init, setConversation };
    setupAutoBoot();

})();
