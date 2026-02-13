document.addEventListener('DOMContentLoaded', function () {
    const isGuest = !!window.IS_GUEST;
    if (isGuest) return;

    const API_URL = (window.APP_BASE_URL || window.location.origin || '').replace(/\/$/, '') + '/profileedit.php';

    const indicator = document.getElementById('streakIndicator');
    const dayCountEl = document.getElementById('streakDayCount');

    const overlay = document.getElementById('streak-checkin-overlay');
    const hoursInput = document.getElementById('streakHours');
    const minutesInput = document.getElementById('streakMinutes');
    const summaryInput = document.getElementById('streakSummary');
    const messageEl = document.getElementById('streakCheckinMessage');
    const submitBtn = document.getElementById('streakSubmitBtn');
    const endBtn = document.getElementById('streakEndBtn');
    const popover = document.getElementById('streakInfoPopover');
    const popoverClose = document.getElementById('streakPopoverClose');
    const popoverGo = document.getElementById('streakPopoverGo');
    if (indicator) {
        indicator.title = 'Start your learning streak';
    }

    const setMessage = (message, color) => {
        if (!messageEl) return;
        messageEl.textContent = message || '';
        messageEl.style.color = color || '#b91c1c';
    };

    const setOverlayVisible = (visible) => {
        if (!overlay) return;
        if (visible) {
            overlay.classList.add('is-visible');
            overlay.setAttribute('aria-hidden', 'false');
        } else {
            overlay.classList.remove('is-visible');
            overlay.setAttribute('aria-hidden', 'true');
        }
    };

    const updateIndicator = (streakData) => {
        if (!indicator || !dayCountEl) return;
        const hasActive = !!(streakData && (streakData.active || streakData.active_streak));
        const rawCount = hasActive && streakData.active_streak ? streakData.active_streak.day_count : 0;
        const count = Number.isFinite(Number(rawCount)) ? Number(rawCount) : 0;
        indicator.style.display = 'inline-flex';
        indicator.classList.toggle('is-idle', !hasActive);
        dayCountEl.textContent = hasActive ? count : '';
        dayCountEl.style.display = hasActive ? 'inline-block' : 'none';
        const name = hasActive && streakData.active_streak && streakData.active_streak.name
            ? streakData.active_streak.name
            : 'Learning Streak';
        indicator.title = hasActive ? `${name}: Day ${count}` : 'Start your learning streak';
    };

    const setPopoverVisible = (visible) => {
        if (!popover || !indicator) return;
        if (visible) {
            popover.classList.add('is-visible');
            popover.setAttribute('aria-hidden', 'false');
            indicator.setAttribute('aria-expanded', 'true');
        } else {
            popover.classList.remove('is-visible');
            popover.setAttribute('aria-hidden', 'true');
            indicator.setAttribute('aria-expanded', 'false');
        }
    };

    const openPreferenceStreakTab = () => {
        const overlay = document.getElementById('preferenceboxOverlay');
        const tabLinks = document.querySelectorAll('.preferencebox-tab-link');
        const tabContents = document.querySelectorAll('.preferencebox-tab-content');
        if (!overlay) return;
        overlay.style.display = 'flex';

        let targetLink = null;
        tabLinks.forEach((link) => {
            if (link.getAttribute('data-tab') === 'public-profile') {
                targetLink = link;
            }
        });
        if (targetLink) {
            tabLinks.forEach((link) => link.classList.remove('active'));
            targetLink.classList.add('active');
            tabContents.forEach((content) => {
                if (content.id === 'public-profile') {
                    content.classList.add('active');
                } else {
                    content.classList.remove('active');
                }
            });
        }
    };

    const showCheckinIfNeeded = (streakData) => {
        if (!overlay) return;
        if (streakData && streakData.active && streakData.needs_check_in) {
            if (hoursInput) hoursInput.value = '';
            if (minutesInput) minutesInput.value = '';
            if (summaryInput) summaryInput.value = '';
            setMessage('');
            setOverlayVisible(true);
        } else {
            setOverlayVisible(false);
        }
    };

    const fetchStatus = async (options = {}) => {
        const showModal = options.showModal !== false;
        try {
            const res = await fetch(`${API_URL}?action=streak_status`);
            const data = await res.json();
            if (!data || !data.success) return;
            const streakData = data.learning_streak || data;
            updateIndicator(streakData);
            if (showModal) {
                showCheckinIfNeeded(streakData);
            }
        } catch (err) {
            // silent
        }
    };

    const submitCheckin = async () => {
        const hoursRaw = hoursInput ? parseInt(hoursInput.value, 10) : 0;
        const minutesRaw = minutesInput ? parseInt(minutesInput.value, 10) : 0;
        const hours = Number.isFinite(hoursRaw) ? Math.max(0, Math.min(23, hoursRaw)) : 0;
        const minutes = Number.isFinite(minutesRaw) ? Math.max(0, Math.min(59, minutesRaw)) : 0;
        const summary = summaryInput ? summaryInput.value.trim() : '';

        if (hours === 0 && minutes === 0) {
            setMessage('Please enter at least 1 minute of study time.');
            return;
        }
        if (!summary) {
            setMessage('Please add a short note about what you studied.');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'log_streak');
        formData.append('hours', String(hours));
        formData.append('minutes', String(minutes));
        formData.append('summary', summary);

        if (submitBtn) submitBtn.disabled = true;
        try {
            const res = await fetch(API_URL, { method: 'POST', body: formData });
            const result = await res.json();
            if (result && result.success) {
                const streakData = result.learning_streak || result;
                updateIndicator(streakData);
                setOverlayVisible(false);
                setMessage('');
                if (typeof window.refreshStreakStatus === 'function') {
                    window.refreshStreakStatus();
                }
            } else {
                setMessage(result.message || 'Failed to submit streak check-in.');
            }
        } catch (err) {
            setMessage('Failed to submit streak check-in.');
        } finally {
            if (submitBtn) submitBtn.disabled = false;
        }
    };

    const endStreak = async () => {
        const formData = new FormData();
        formData.append('action', 'end_streak');
        if (endBtn) endBtn.disabled = true;
        try {
            const res = await fetch(API_URL, { method: 'POST', body: formData });
            const result = await res.json();
            if (result && result.success) {
                const streakData = result.learning_streak || result;
                updateIndicator(streakData);
                setOverlayVisible(false);
                setMessage('');
                if (typeof window.refreshStreakStatus === 'function') {
                    window.refreshStreakStatus();
                }
            } else {
                setMessage(result.message || 'Failed to end streak.');
            }
        } catch (err) {
            setMessage('Failed to end streak.');
        } finally {
            if (endBtn) endBtn.disabled = false;
        }
    };

    if (submitBtn) submitBtn.addEventListener('click', submitCheckin);
    if (endBtn) endBtn.addEventListener('click', endStreak);

    if (indicator) {
        indicator.addEventListener('click', (event) => {
            if (!popover) return;
            event.stopPropagation();
            const isVisible = popover.classList.contains('is-visible');
            setPopoverVisible(!isVisible);
        });
    }
    if (popoverClose) {
        popoverClose.addEventListener('click', (event) => {
            event.stopPropagation();
            setPopoverVisible(false);
        });
    }
    if (popoverGo) {
        popoverGo.addEventListener('click', (event) => {
            event.stopPropagation();
            setPopoverVisible(false);
            openPreferenceStreakTab();
        });
    }
    document.addEventListener('click', (event) => {
        if (!popover || !indicator) return;
        if (popover.classList.contains('is-visible')) {
            const target = event.target;
            if (!popover.contains(target) && !indicator.contains(target)) {
                setPopoverVisible(false);
            }
        }
    });

    window.refreshStreakStatus = () => fetchStatus({ showModal: false });
    fetchStatus({ showModal: true });
});
