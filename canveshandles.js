/**
 * Canvas Document Editor Handler
 * Features: A4 page size, word-like behavior, highlighter colors, ruler, 
 * bullet points, text colors, LaTeX formatting, Marked.js integration
 * Multi-page support like Microsoft Word
 */

class CanvasDocumentEditor {
    constructor() {
        this.currentHighlightColor = '#ffff00';
        this.currentTextColor = '#000000';
        this.pageSize = 'A4';
        this.showRuler = true;
        this.undoStack = [];
        this.redoStack = [];
        this.maxUndoSteps = 50;
        this.pages = [];
        this.currentPageIndex = 0;
        
        // Initialize marked.js for markdown formatting
        if (typeof marked !== 'undefined') {
            marked.setOptions({
                breaks: true,
                gfm: true,
                headerIds: true,
                mangle: false
            });
        }
        
        this.init();
    }
    
    init() {
        this.initializeElements();
        this.createInitialPage();
        this.attachEventListeners();
        this.applyPageSize(this.pageSize);
        this.initializeRuler();
        this.loadCanvasState();
    }
    
    initializeElements() {
        this.canvasDoc = document.getElementById('canvas-doc');
        this.canvasStage = document.getElementById('canvas-stage');
        this.sidebar = document.getElementById('rightsidebar2');
        this.mainSidebar = document.getElementById('sidebar');
        this.isFullscreen = false;
        
        // Toolbar buttons
        this.boldBtn = document.getElementById('canvas-bold-btn');
        this.italicBtn = document.getElementById('canvas-italic-btn');
        this.underlineBtn = document.getElementById('canvas-underline-btn');
        this.highlightBtn = document.getElementById('canvas-highlight-btn');
        this.undoBtn = document.getElementById('canvas-undo-btn');
        this.redoBtn = document.getElementById('canvas-redo-btn');
        this.fontSizeSelect = document.getElementById('canvas-font-size');
        this.fullscreenBtn = document.getElementById('canvas-fullscreen-btn');
        this.closeBtn = document.getElementById('canvas-close-btn');
        this.dockBtn = document.getElementById('canvas-dock-btn');
        
        // New controls
        this.pageSizeSelect = document.getElementById('canvas-page-size');
        this.highlightColorPicker = document.getElementById('canvas-highlight-color');
        this.textColorPicker = document.getElementById('canvas-text-color');
        this.rulerToggle = document.getElementById('canvas-ruler-toggle');
        this.bulletBtn = document.getElementById('canvas-bullet-btn');
        this.numberedBtn = document.getElementById('canvas-numbered-btn');
        this.latexBtn = document.getElementById('canvas-latex-btn');
        this.formatMarkdownBtn = document.getElementById('canvas-format-markdown');
    }
    
    attachEventListeners() {
        // Text formatting
        if (this.boldBtn) {
            this.boldBtn.addEventListener('click', () => this.execCommand('bold'));
        }
        if (this.italicBtn) {
            this.italicBtn.addEventListener('click', () => this.execCommand('italic'));
        }
        if (this.underlineBtn) {
            this.underlineBtn.addEventListener('click', () => this.execCommand('underline'));
        }
        
        // Highlighter
        if (this.highlightBtn) {
            this.highlightBtn.addEventListener('click', () => this.applyHighlight());
        }
        if (this.highlightColorPicker) {
            this.highlightColorPicker.addEventListener('change', (e) => {
                this.currentHighlightColor = e.target.value;
            });
        }
        
        // Text color
        if (this.textColorPicker) {
            this.textColorPicker.addEventListener('change', (e) => {
                this.currentTextColor = e.target.value;
                this.execCommand('foreColor', this.currentTextColor);
            });
        }
        
        // Lists
        if (this.bulletBtn) {
            this.bulletBtn.addEventListener('click', () => this.execCommand('insertUnorderedList'));
        }
        if (this.numberedBtn) {
            this.numberedBtn.addEventListener('click', () => this.execCommand('insertOrderedList'));
        }
        
        // Page size
        if (this.pageSizeSelect) {
            this.pageSizeSelect.addEventListener('change', (e) => {
                this.applyPageSize(e.target.value);
            });
        }
        
        // Ruler toggle
        if (this.rulerToggle) {
            this.rulerToggle.addEventListener('click', () => this.toggleRuler());
        }
        
        // Font size
        if (this.fontSizeSelect) {
            this.fontSizeSelect.addEventListener('change', (e) => {
                this.execCommand('fontSize', '7'); // Set to largest then wrap
                const selection = window.getSelection();
                if (selection.rangeCount > 0) {
                    const range = selection.getRangeAt(0);
                    const span = document.createElement('span');
                    span.style.fontSize = e.target.value + 'px';
                    try {
                        range.surroundContents(span);
                    } catch (err) {
                        console.log('Font size application error:', err);
                    }
                }
            });
        }
        
        // Undo/Redo
        if (this.undoBtn) {
            this.undoBtn.addEventListener('click', () => this.undo());
        }
        if (this.redoBtn) {
            this.redoBtn.addEventListener('click', () => this.redo());
        }
        
        // LaTeX
        if (this.latexBtn) {
            this.latexBtn.addEventListener('click', () => this.insertLatex());
        }
        
        // Markdown formatting
        if (this.formatMarkdownBtn) {
            this.formatMarkdownBtn.addEventListener('click', () => this.autoFormatMarkdown());
        }
        
        // Fullscreen toggle
        if (this.fullscreenBtn) {
            this.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());
        }
        
        // Close button
        if (this.closeBtn) {
            this.closeBtn.addEventListener('click', () => this.closeCanvas());
        }
        
        // Dock/Minimize button
        if (this.dockBtn) {
            this.dockBtn.addEventListener('click', () => this.minimizeCanvas());
        }
        
        // Content editable tracking for undo/redo
        if (this.canvasDoc) {
            this.canvasDoc.addEventListener('input', () => {
                this.saveState();
                this.updateStatusBar();
            });
            this.canvasDoc.addEventListener('paste', (e) => this.handlePaste(e));
            
            // Auto-save every 3 seconds
            this.canvasDoc.addEventListener('input', this.debounce(() => {
                this.saveCanvasState();
            }, 3000));
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboardShortcuts(e));
    }
    
    // Create initial page
    createInitialPage() {
        if (!this.canvasDoc) return;
        
        // Clear any existing content
        this.canvasDoc.innerHTML = '';
        this.pages = [];
        
        // Create first page
        const firstPage = this.addNewPage();
        
        // Create status bar
        this.createStatusBar();

        // Initialize status info
        this.updateStatusBar();

        // Place caret at start of first page
        if (firstPage) {
            setTimeout(() => {
                firstPage.focus();
                const range = document.createRange();
                const sel = window.getSelection();
                range.setStart(firstPage, 0);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            }, 0);
        }
    }
    
    // Create status bar like Microsoft Word
    createStatusBar() {
        let statusBar = document.getElementById('canvas-status-bar');
        if (!statusBar) {
            statusBar = document.createElement('div');
            statusBar.id = 'canvas-status-bar';
            statusBar.className = 'canvas-status-bar';
            this.canvasStage.appendChild(statusBar);
        }
        
        statusBar.innerHTML = `
            <div class="status-left">
                <span id="canvas-page-info">Page 1 of 1</span>
                <span class="status-separator">|</span>
                <span id="canvas-word-count">0 words</span>
            </div>
            <div class="status-right">
                <span id="canvas-zoom">132%</span>
            </div>
        `;
        
        this.statusBar = statusBar;
    }
    
    // Update status bar with current stats
    updateStatusBar() {
        if (!this.statusBar) return;
        
        const pageInfo = this.statusBar.querySelector('#canvas-page-info');
        const wordCount = this.statusBar.querySelector('#canvas-word-count');
        
        const currentPageNumber = this.getCurrentPageNumber();
        const totalPages = this.pages.length || 1;
        
        if (pageInfo) {
            pageInfo.textContent = `Page ${currentPageNumber} of ${totalPages}`;
        }
        
        if (wordCount) {
            const text = (this.canvasDoc?.innerText || '').trim();
            const words = text ? text.split(/\s+/).filter(Boolean).length : 0;
            wordCount.textContent = `${words} word${words !== 1 ? 's' : ''}`;
        }
    }

    // Determine current page number from selection or focus
    getCurrentPageNumber() {
        if (!this.pages || this.pages.length === 0) return 1;
        const pageEl = this.getActivePageElement();
        const idx = this.pages.indexOf(pageEl);
        return idx >= 0 ? idx + 1 : 1;
    }

    getActivePageElement() {
        // Try selection first
        const sel = window.getSelection();
        if (sel && sel.rangeCount > 0) {
            const node = sel.getRangeAt(0).startContainer;
            const page = node.closest ? node.closest('.canvas-page') : this.findAncestorWithClass(node, 'canvas-page');
            if (page) return page;
        }
        // Fallback to focused element
        if (document.activeElement) {
            const page = document.activeElement.closest ? document.activeElement.closest('.canvas-page') : this.findAncestorWithClass(document.activeElement, 'canvas-page');
            if (page) return page;
        }
        // Default to first page
        return this.pages[0];
    }

    findAncestorWithClass(node, className) {
        let current = node;
        while (current) {
            if (current.classList && current.classList.contains(className)) return current;
            current = current.parentNode;
        }
        return null;
    }
    
    // Add a new page
    addNewPage() {
        const page = document.createElement('div');
        page.className = 'canvas-page';
        page.contentEditable = 'true';
        page.setAttribute('spellcheck', 'true');
        page.dataset.pageNumber = this.pages.length + 1;
        
        // Add page number
        const pageNumber = document.createElement('div');
        pageNumber.className = 'canvas-page-number';
        pageNumber.textContent = `Page ${this.pages.length + 1}`;
        pageNumber.contentEditable = 'false';
        page.appendChild(pageNumber);
        
        // Add event listeners to the page
        page.addEventListener('input', (e) => this.handlePageInput(e, page));
        page.addEventListener('keydown', (e) => this.handlePageKeydown(e, page));
        page.addEventListener('paste', (e) => this.handlePaste(e));
        
        this.canvasDoc.appendChild(page);
        this.pages.push(page);

        this.updatePageNumbers();
        
        // Hide empty hint if this is not the first page
        const emptyHint = document.getElementById('canvas-empty-hint');
        if (emptyHint && this.pages.length > 0) {
            emptyHint.style.display = 'none';
        }

        this.updateStatusBar();
        return page;
    }

    // Refresh visible page numbers text/dataset
    updatePageNumbers() {
        this.pages.forEach((page, idx) => {
            page.dataset.pageNumber = idx + 1;
            const numEl = page.querySelector('.canvas-page-number');
            if (numEl) numEl.textContent = `Page ${idx + 1}`;
        });
    }
    
    // Handle input in a page (check for overflow)
    handlePageInput(e, page) {
        const pageIndex = this.pages.indexOf(page);
        
        // Check if content exceeds page height
        if (this.isPageOverflowing(page)) {
            this.handlePageOverflow(page, pageIndex);
        }
        
        this.saveState();
        this.updateStatusBar();
    }
    
    // Handle keyboard events in a page
    handlePageKeydown(e, page) {
        const pageIndex = this.pages.indexOf(page);
        
        // If Enter is pressed at the end of the page or page is full
        if (e.key === 'Enter') {
            setTimeout(() => {
                if (this.isPageOverflowing(page) || this.isCursorAtPageEnd(page)) {
                    // Check if there's a next page
                    if (pageIndex === this.pages.length - 1) {
                        // Create new page and move cursor there
                        const newPage = this.addNewPage();
                        setTimeout(() => {
                            newPage.focus();
                            // Move cursor to start of new page
                            const range = document.createRange();
                            const sel = window.getSelection();
                            range.setStart(newPage, 0);
                            range.collapse(true);
                            sel.removeAllRanges();
                            sel.addRange(range);
                        }, 10);
                    }
                }
            }, 10);
        }
    }
    
    // Check if page content is overflowing
    isPageOverflowing(page) {
        // Get the actual content height (excluding padding)
        const computedStyle = window.getComputedStyle(page);
        const paddingTop = parseFloat(computedStyle.paddingTop);
        const paddingBottom = parseFloat(computedStyle.paddingBottom);
        const pageHeight = page.offsetHeight;
        const contentHeight = page.scrollHeight;
        
        // Allow a small buffer (5px) for rounding errors
        return contentHeight > pageHeight + 5;
    }
    
    // Check if cursor is at the end of the page
    isCursorAtPageEnd(page) {
        const selection = window.getSelection();
        if (!selection.rangeCount) return false;
        
        const range = selection.getRangeAt(0);
        const cursorPosition = range.startOffset;
        const textLength = page.textContent.length;
        
        // Check if cursor is in the last 10% of content
        return cursorPosition / textLength > 0.9;
    }
    
    // Handle page overflow by moving content to next page
    handlePageOverflow(page, pageIndex) {
        // Get all child nodes
        const children = Array.from(page.childNodes);
        let overflowContent = [];
        let totalHeight = 0;
        const maxHeight = page.offsetHeight - 100; // Leave some margin
        
        // Find where content starts overflowing
        for (let i = 0; i < children.length; i++) {
            const child = children[i];
            
            // Skip page number
            if (child.classList && child.classList.contains('canvas-page-number')) {
                continue;
            }
            
            const childHeight = child.offsetHeight || 20;
            totalHeight += childHeight;
            
            if (totalHeight > maxHeight) {
                // Move this and all subsequent children to next page
                overflowContent = children.slice(i);
                break;
            }
        }
        
        if (overflowContent.length > 0) {
            // Remove overflow content from current page
            overflowContent.forEach(node => {
                if (!node.classList || !node.classList.contains('canvas-page-number')) {
                    page.removeChild(node);
                }
            });
            
            // Get or create next page
            let nextPage;
            if (pageIndex + 1 < this.pages.length) {
                nextPage = this.pages[pageIndex + 1];
            } else {
                nextPage = this.addNewPage();
            }
            
            // Insert overflow content at the beginning of next page
            const pageNumber = nextPage.querySelector('.canvas-page-number');
            overflowContent.forEach(node => {
                if (!node.classList || !node.classList.contains('canvas-page-number')) {
                    nextPage.insertBefore(node, pageNumber);
                }
            });
        }

        this.updatePageNumbers();
        this.updateStatusBar();
    }
    
    execCommand(command, value = null) {
        document.execCommand(command, false, value);
        this.canvasDoc.focus();
    }
    
    applyHighlight() {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;
        
        const range = selection.getRangeAt(0);
        const span = document.createElement('span');
        span.style.backgroundColor = this.currentHighlightColor;
        span.className = 'highlight';
        
        try {
            range.surroundContents(span);
        } catch (err) {
            // If can't surround (complex selection), use backColor command
            this.execCommand('backColor', this.currentHighlightColor);
        }
    }
    
    applyPageSize(size) {
        this.pageSize = size;
        
        // A4 dimensions: 210mm × 297mm at 96 DPI
        const pageSizes = {
            'A4': { width: '210mm', height: '297mm', padding: '25mm' },
            'Letter': { width: '8.5in', height: '11in', padding: '1in' },
            'Legal': { width: '8.5in', height: '14in', padding: '1in' },
            'A3': { width: '297mm', height: '420mm', padding: '25mm' }
        };
        
        const dimensions = pageSizes[size] || pageSizes['A4'];
        
        // Apply to all pages
        this.pages.forEach(page => {
            page.style.width = dimensions.width;
            page.style.height = dimensions.height;
            page.style.padding = dimensions.padding;
        });
        
        // Also update canvas-doc container width
        if (this.canvasDoc) {
            this.canvasDoc.style.width = dimensions.width;
        }

        // Update ruler width to match page width
        if (this.hRuler) {
            this.hRuler.style.width = dimensions.width;
        }

        this.updateStatusBar();
        this.updatePageNumbers();
    }
    
    initializeRuler() {
        if (!this.canvasStage) return;
        
        // Create horizontal ruler
        const hRuler = document.createElement('div');
        hRuler.id = 'canvas-h-ruler';
        hRuler.className = 'canvas-ruler canvas-ruler-horizontal';
        
        // Add ruler markings
        for (let i = 0; i <= 21; i++) {
            const mark = document.createElement('div');
            mark.className = 'ruler-mark';
            mark.style.left = (i * 10) + 'mm';
            
            if (i % 5 === 0) {
                mark.classList.add('ruler-mark-major');
                const label = document.createElement('span');
                label.textContent = i;
                label.className = 'ruler-label';
                mark.appendChild(label);
            }
            
            hRuler.appendChild(mark);
        }
        
        this.canvasStage.insertBefore(hRuler, this.canvasDoc);
        this.hRuler = hRuler;
    }
    
    toggleRuler() {
        this.showRuler = !this.showRuler;
        if (this.hRuler) {
            this.hRuler.style.display = this.showRuler ? 'block' : 'none';
        }
        if (this.rulerToggle) {
            this.rulerToggle.setAttribute('aria-pressed', this.showRuler);
            this.rulerToggle.classList.toggle('active', this.showRuler);
        }
    }
    
    insertLatex() {
        const latex = prompt('Enter LaTeX formula (e.g., x^2 + y^2 = r^2):');
        if (!latex) return;
        
        // Create a span with LaTeX content
        const latexSpan = document.createElement('span');
        latexSpan.className = 'latex-formula';
        latexSpan.setAttribute('data-latex', latex);
        latexSpan.textContent = `$$${latex}$$`;
        
        // Insert at cursor
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            range.deleteContents();
            range.insertNode(latexSpan);
            
            // Move cursor after the inserted element
            range.setStartAfter(latexSpan);
            range.setEndAfter(latexSpan);
            selection.removeAllRanges();
            selection.addRange(range);
        }
        
        // Render LaTeX if KaTeX or MathJax is available
        this.renderLatex();
    }
    
    renderLatex() {
        // If KaTeX is available
        if (typeof katex !== 'undefined') {
            const formulas = this.canvasDoc.querySelectorAll('.latex-formula');
            formulas.forEach(formula => {
                const latex = formula.getAttribute('data-latex');
                try {
                    katex.render(latex, formula, {
                        throwOnError: false,
                        displayMode: true
                    });
                } catch (err) {
                    console.error('KaTeX render error:', err);
                }
            });
        }
        // If MathJax is available
        else if (typeof MathJax !== 'undefined' && MathJax.typesetPromise) {
            if (!this.canvasDoc || !this.canvasDoc.isConnected) {
                return;
            }
            MathJax.typesetPromise([this.canvasDoc]).catch((err) => {
                console.error('MathJax render error:', err);
            });
        }
    }
    
    autoFormatMarkdown() {
        if (typeof marked === 'undefined') {
            alert('Marked.js library not loaded');
            return;
        }
        
        const content = this.canvasDoc.innerText;
        try {
            const html = marked.parse(content);
            this.canvasDoc.innerHTML = html;
            
            // Re-render any LaTeX after markdown conversion
            this.renderLatex();
            
            this.saveState();
            this.updateStatusBar();
        } catch (err) {
            console.error('Markdown formatting error:', err);
            alert('Error formatting markdown: ' + err.message);
        }
    }
    
    handlePaste(e) {
        e.preventDefault();
        
        // Get plain text from clipboard
        const text = (e.clipboardData || window.clipboardData).getData('text/plain');
        
        // Insert as plain text
        const selection = window.getSelection();
        if (selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            range.deleteContents();
            const textNode = document.createTextNode(text);
            range.insertNode(textNode);
            
            // Move cursor to end
            range.setStartAfter(textNode);
            range.setEndAfter(textNode);
            selection.removeAllRanges();
            selection.addRange(range);
        }

        this.saveState();
        this.updateStatusBar();
    }
    
    saveState() {
        const state = this.canvasDoc.innerHTML;
        
        // Don't save duplicate states
        if (this.undoStack.length > 0 && this.undoStack[this.undoStack.length - 1] === state) {
            return;
        }
        
        this.undoStack.push(state);
        
        // Limit undo stack size
        if (this.undoStack.length > this.maxUndoSteps) {
            this.undoStack.shift();
        }
        
        // Clear redo stack when new action is performed
        this.redoStack = [];
    }
    
    undo() {
        if (this.undoStack.length === 0) return;
        
        const currentState = this.canvasDoc.innerHTML;
        const previousState = this.undoStack.pop();
        
        this.redoStack.push(currentState);
        this.canvasDoc.innerHTML = previousState;
        
        this.renderLatex();
    }
    
    redo() {
        if (this.redoStack.length === 0) return;
        
        const currentState = this.canvasDoc.innerHTML;
        const nextState = this.redoStack.pop();
        
        this.undoStack.push(currentState);
        this.canvasDoc.innerHTML = nextState;
        
        this.renderLatex();
    }
    
    handleKeyboardShortcuts(e) {
        // Ctrl/Cmd + B = Bold
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            this.execCommand('bold');
        }
        // Ctrl/Cmd + I = Italic
        else if ((e.ctrlKey || e.metaKey) && e.key === 'i') {
            e.preventDefault();
            this.execCommand('italic');
        }
        // Ctrl/Cmd + U = Underline
        else if ((e.ctrlKey || e.metaKey) && e.key === 'u') {
            e.preventDefault();
            this.execCommand('underline');
        }
        // Ctrl/Cmd + Z = Undo
        else if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
            e.preventDefault();
            this.undo();
        }
        // Ctrl/Cmd + Shift + Z or Ctrl/Cmd + Y = Redo
        else if ((e.ctrlKey || e.metaKey) && (e.shiftKey && e.key === 'z' || e.key === 'y')) {
            e.preventDefault();
            this.redo();
        }
        // Ctrl/Cmd + H = Highlight
        else if ((e.ctrlKey || e.metaKey) && e.key === 'h') {
            e.preventDefault();
            this.applyHighlight();
        }
    }
    
    saveCanvasState() {
        const content = this.canvasDoc.innerHTML;
        
        // Save to localStorage
        localStorage.setItem('canvas_doc_content', content);
        localStorage.setItem('canvas_page_size', this.pageSize);
        localStorage.setItem('canvas_show_ruler', this.showRuler);
        
        // Emit to socket if available for real-time collaboration
        if (typeof socket !== 'undefined' && socket.connected) {
            socket.emit('canvas_update', {
                content: content,
                timestamp: Date.now()
            });
        }
    }
    
    loadCanvasState() {
        const savedContent = localStorage.getItem('canvas_doc_content');
        const savedPageSize = localStorage.getItem('canvas_page_size');
        const savedShowRuler = localStorage.getItem('canvas_show_ruler');
        
        if (savedContent) {
            this.canvasDoc.innerHTML = savedContent;
            // Rebuild pages array from loaded content
            this.pages = Array.from(this.canvasDoc.querySelectorAll('.canvas-page'));
            
            // Reattach event listeners to loaded pages
            this.pages.forEach(page => {
                page.addEventListener('input', (e) => this.handlePageInput(e, page));
                page.addEventListener('keydown', (e) => this.handlePageKeydown(e, page));
                page.addEventListener('paste', (e) => this.handlePaste(e));
            });
            
            this.renderLatex();
            this.updateStatusBar();
            this.updatePageNumbers();
        }
        
        // If no pages were present (corrupt/empty), create one
        if (!this.pages || this.pages.length === 0) {
            this.createInitialPage();
        }
        
        if (savedPageSize) {
            this.pageSize = savedPageSize;
            if (this.pageSizeSelect) {
                this.pageSizeSelect.value = savedPageSize;
            }
            this.applyPageSize(savedPageSize);
        }
        
        if (savedShowRuler !== null) {
            this.showRuler = savedShowRuler === 'true';
            if (!this.showRuler && this.hRuler) {
                this.hRuler.style.display = 'none';
            }
        }
    }
    
    // Utility: Debounce function
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Export document as HTML
    exportAsHTML() {
        const content = this.canvasDoc.innerHTML;
        const blob = new Blob([content], { type: 'text/html' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'document.html';
        a.click();
        URL.revokeObjectURL(url);
    }
    
    // Export document as Plain Text
    exportAsText() {
        const content = this.canvasDoc.innerText;
        const blob = new Blob([content], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'document.txt';
        a.click();
        URL.revokeObjectURL(url);
    }
    
    // Toggle fullscreen mode
    toggleFullscreen() {
        this.isFullscreen = !this.isFullscreen;
        
        if (this.isFullscreen) {
            // Enter fullscreen
            this.sidebar.classList.add('canvas-fullscreen');
            if (this.mainSidebar) {
                this.mainSidebar.style.display = 'none';
            }
            if (this.fullscreenBtn) {
                const icon = this.fullscreenBtn.querySelector('i');
                if (icon) {
                    icon.className = 'fa-solid fa-compress';
                }
                this.fullscreenBtn.setAttribute('aria-label', 'Exit fullscreen');
            }
        } else {
            // Exit fullscreen
            this.sidebar.classList.remove('canvas-fullscreen');
            if (this.mainSidebar) {
                this.mainSidebar.style.display = '';
            }
            if (this.fullscreenBtn) {
                const icon = this.fullscreenBtn.querySelector('i');
                if (icon) {
                    icon.className = 'fa-solid fa-expand';
                }
                this.fullscreenBtn.setAttribute('aria-label', 'Open canvas fullscreen');
            }
        }
    }
    
    // Close canvas
    closeCanvas() {
        if (this.sidebar) {
            this.sidebar.setAttribute('aria-hidden', 'true');
        }
        // Exit fullscreen if active
        if (this.isFullscreen) {
            this.toggleFullscreen();
        }
    }
    
    // Minimize/Dock canvas
    minimizeCanvas() {
        if (this.sidebar) {
            this.sidebar.setAttribute('aria-hidden', 'true');
        }
        // Exit fullscreen if active
        if (this.isFullscreen) {
            this.toggleFullscreen();
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.canvasEditor = new CanvasDocumentEditor();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = CanvasDocumentEditor;
}
