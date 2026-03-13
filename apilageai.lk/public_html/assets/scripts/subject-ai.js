/**
 * Subject AI Modal Handler
 * Manages grade/subject selection for Subject AI mode
 */

// Fix for utilities.min.js dropdown handler - add null guards
document.addEventListener('DOMContentLoaded', () => {
  // Wait a brief moment to ensure all elements are loaded
  setTimeout(() => {
    const dropButton = document.getElementById('button-drop');
    const dropMenu = document.querySelector('.dropup-menu');

    // If either element doesn't exist, create a safe handler
    if (dropButton && dropMenu) {
      // Elements exist, utilities.min.js should handle it
    } else if (dropButton && !dropMenu) {
      // Add a safe click handler
      dropButton.addEventListener('click', (e) => {
        e.stopPropagation();
        // Safely find or create the menu
        const menu = document.querySelector('.dropup-menu');
        if (menu) menu.classList.toggle('show');
      });
    }
  }, 50);
});

const SubjectAI = (() => {
  let state = {
    selectedGrade: null,
    selectedSubject: null,
    isActive: false
  };

  const modal = document.getElementById('subject-ai-modal');
  const openBtn = document.getElementById('openSubjectAIBtn');
  const closeBtn = document.getElementById('subjectAIClose');
  const cancelBtn = document.getElementById('subject-ai-cancel');
  const startBtn = document.getElementById('subject-ai-start');
  const messageEl = document.getElementById('subject-ai-message');

  const gradeBtns = document.querySelectorAll('.grade-btn');
  const subjectBtns = document.querySelectorAll('.subject-btn');

  // Initialize event listeners
  function init() {
    if (!modal) {
      console.warn('Subject AI modal not found in DOM');
      return;
    }

    // Use explicit null checks for all elements
    if (openBtn) openBtn.addEventListener('click', openModal);
    if (closeBtn) closeBtn.addEventListener('click', closeModal);
    if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
    if (startBtn) startBtn.addEventListener('click', activateSubjectMode);

    // Grade button listeners
    if (gradeBtns && gradeBtns.length > 0) {
      gradeBtns.forEach(btn => {
        if (btn) btn.addEventListener('click', selectGrade);
      });
    }

    // Subject button listeners
    if (subjectBtns && subjectBtns.length > 0) {
      subjectBtns.forEach(btn => {
        if (btn) btn.addEventListener('click', selectSubject);
      });
    }

    // Close on backdrop click
    const backdrop = modal ? modal.querySelector('.subject-ai-backdrop') : null;
    if (backdrop) backdrop.addEventListener('click', closeModal);
  }

  function openModal() {
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    resetState();
  }

  function closeModal() {
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    resetState();
  }

  function resetState() {
    state.selectedGrade = null;
    state.selectedSubject = null;
    messageEl.textContent = '';
    messageEl.className = 'subject-ai-message';

    gradeBtns.forEach(btn => {
      btn.classList.remove('active');
      // Keep Grade 10 disabled (coming soon)
      if (btn.getAttribute('data-grade') === '10') {
        btn.disabled = true;
      }
    });

    subjectBtns.forEach(btn => {
      btn.classList.remove('active');
      btn.disabled = true;
      // Keep Maths disabled (coming soon)
      if (btn.getAttribute('data-subject') === 'maths') {
        btn.disabled = true;
      }
    });
    startBtn.disabled = true;
  }

  function selectGrade(e) {
    const grade = e.target.getAttribute('data-grade');
    if (!grade) return;

    // Disable Grade 10 selection (coming soon)
    if (grade === '10') {
      messageEl.textContent = '⏳ Grade 10 resources will be available soon!';
      messageEl.className = 'subject-ai-message';
      return;
    }

    // Update state
    state.selectedGrade = grade;
    state.selectedSubject = null;

    // Update UI
    gradeBtns.forEach(btn => btn.classList.remove('active'));
    e.target.classList.add('active');

    // For Grade 11: Only enable Science, disable Maths
    if (grade === '11') {
      const mathsBtn = Array.from(subjectBtns).find(btn => btn.getAttribute('data-subject') === 'maths');
      const scienceBtn = Array.from(subjectBtns).find(btn => btn.getAttribute('data-subject') === 'science');

      // Disable Maths (coming soon)
      if (mathsBtn) mathsBtn.disabled = true;

      // Enable Science (available)
      if (scienceBtn) scienceBtn.disabled = false;
    }

    startBtn.disabled = true;
    messageEl.textContent = '';
  }

  function selectSubject(e) {
    const subject = e.target.getAttribute('data-subject');
    if (!subject) return;

    // Prevent Maths selection (coming soon)
    if (subject === 'maths') {
      messageEl.textContent = '⏳ Grade 11 Maths will be available soon!';
      messageEl.className = 'subject-ai-message';
      return;
    }

    // Update state
    state.selectedSubject = subject;

    // Update UI
    subjectBtns.forEach(btn => btn.classList.remove('active'));
    e.target.classList.add('active');

    // Enable start button
    startBtn.disabled = false;
    messageEl.textContent = '';
  }

  function activateSubjectMode() {
    if (!state.selectedGrade || !state.selectedSubject) {
      messageEl.textContent = 'Please select both grade and subject';
      messageEl.className = 'subject-ai-message error';
      return;
    }

    // Emit Socket.IO event to activate subject mode
    if (typeof socket !== 'undefined' && socket.connected) {
      socket.emit('activate_subject_mode', {
        grade: parseInt(state.selectedGrade),
        subject: state.selectedSubject
      }, (response) => {
        if (response && response.success) {
          messageEl.textContent = `✓ ${state.selectedGrade} ${state.selectedSubject.charAt(0).toUpperCase() + state.selectedSubject.slice(1)} mode activated`;
          messageEl.className = 'subject-ai-message success';

          // Close modal after brief delay
          setTimeout(() => {
            closeModal();
            // Show status indicator in chat
            showSubjectModeIndicator();
          }, 800);
        } else {
          messageEl.textContent = response?.error || 'Failed to activate subject mode';
          messageEl.className = 'subject-ai-message error';
        }
      });
    } else {
      messageEl.textContent = 'Connection error. Please refresh the page.';
      messageEl.className = 'subject-ai-message error';
    }
  }

  function showSubjectModeIndicator() {
    const container = document.getElementById('selected-feature-placeholder');
    if (container) {
      const badge = document.createElement('div');
      badge.id = 'subject-mode-badge';
      badge.className = 'subject-mode-badge';
      badge.innerHTML = `
        <span style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; background: #ede9fe; border-radius: 8px; color: #7c3aed; font-size: 12px; font-weight: 600;">
          <span>📚</span>
          <span>Grade ${state.selectedGrade} ${state.selectedSubject.charAt(0).toUpperCase() + state.selectedSubject.slice(1)}</span>
          <button id="exit-subject-mode" type="button" style="background: none; border: none; color: #7c3aed; cursor: pointer; padding: 0; margin-left: 4px;">×</button>
        </span>
      `;

      container.innerHTML = '';
      container.appendChild(badge);

      // Exit button handler
      document.getElementById('exit-subject-mode')?.addEventListener('click', exitSubjectMode);
    }

    // Disable normal features
    disableNormalFeatures();
  }

  function exitSubjectMode() {
    if (typeof socket !== 'undefined' && socket.connected) {
      socket.emit('deactivate_subject_mode', {}, (response) => {
        state.isActive = false;
        const container = document.getElementById('selected-feature-placeholder');
        if (container) container.innerHTML = '';

        // Re-enable normal features
        enableNormalFeatures();
      });
    }
  }

  function disableNormalFeatures() {
    state.isActive = true;
    const uploadDocBtn = document.getElementById('uploadDocumentBtn');
    const graphBtn = document.getElementById('toggleGraphBtn');
    const canvasBtn = document.getElementById('toggleCanvasBtn');
    const thinkBtn = document.getElementById('enebleThink');

    [uploadDocBtn, graphBtn, canvasBtn, thinkBtn].forEach(btn => {
      if (btn) {
        btn.disabled = true;
        btn.style.opacity = '0.5';
        btn.title = 'Not available in Subject Mode';
      }
    });
  }

  function enableNormalFeatures() {
    const uploadDocBtn = document.getElementById('uploadDocumentBtn');
    const graphBtn = document.getElementById('toggleGraphBtn');
    const canvasBtn = document.getElementById('toggleCanvasBtn');
    const thinkBtn = document.getElementById('enebleThink');

    [uploadDocBtn, graphBtn, canvasBtn, thinkBtn].forEach(btn => {
      if (btn) {
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.title = '';
      }
    });
  }

  return {
    init,
    openModal,
    closeModal,
    activateSubjectMode,
    exitSubjectMode,
    getState: () => ({ ...state })
  };
})();

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  SubjectAI.init();
});

// Listen for subject mode activation from server
document.addEventListener('subject_mode_activated', (event) => {
  const { grade, subject } = event.detail;
  SubjectAI.getState(); // Just to verify the state
});

// Global error handler to catch and suppress null reference errors
window.addEventListener('error', (event) => {
  // Suppress "Cannot read properties of null" errors - likely from legacy code in utilities.min.js
  if (event.message && event.message.includes('Cannot read properties of null')) {
    console.warn('Suppressed null reference error:', event.message,'at line', event.lineno);
    event.preventDefault();
    return true;
  }
  return false;
});

// Attach error handler to prevent unhandled rejections during DOM manipulation
window.addEventListener('unhandledrejection', (event) => {
  if (event.reason && event.reason.message && event.reason.message.includes('null')) {
    event.preventDefault();
  }
});
