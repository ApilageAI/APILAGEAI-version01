document.addEventListener('DOMContentLoaded', () => {
    const lightbox = document.getElementById('onboarding-lightbox');
    if (!lightbox) {
        return;
    }
    const isGuest =
        !!window.IS_GUEST ||
        document.body.classList.contains('guest-mode') ||
        !!(window.userData && window.userData.is_guest);
    if (isGuest) {
        lightbox.classList.remove('visible');
        return;
    }
    const steps = document.querySelectorAll('.onboard-step');
    const nextBtn = document.getElementById('next-btn');
    const backBtn = document.getElementById('back-btn');
    const progressFill = document.getElementById('onboard-progress-fill');
    const progressLabel = document.getElementById('onboard-progress-label');
    const planButtons = document.querySelectorAll('[data-onboard-plan]');
    const errorMessage = document.getElementById('error-message');
    const form = document.getElementById('onboarding-form');
    const interestsInput = document.getElementById('interests-hidden-input');
    const preferenceInput = document.getElementById('preference-hidden-input');
    const schoolInput = document.getElementById('school-input');
    const notStudentCheckbox = document.getElementById('not-student-checkbox');
    const schoolList = document.getElementById('school-list');
    const baseUrl = (window.APP_BASE_URL||window.location.origin||"").replace(/\/$/,"");

    let currentStep = 0;
    const selectedInterests = new Set();
    let selectedPreference = null;

    const normalizeSchoolName = (value) => value.trim().toLowerCase();
    const setSchoolList = (names) => {
        const uniqueNames = Array.from(new Set(names.filter(Boolean)));
        const notListedOption = 'MY SCHOOL IS NOT LISTED';
        if (!uniqueNames.includes(notListedOption)) {
            uniqueNames.push(notListedOption);
        }
        window.SCHOOL_LIST = uniqueNames;
        window.SCHOOL_SET = new Set(uniqueNames.map(normalizeSchoolName));
        if (schoolList) {
            schoolList.innerHTML = '';
            uniqueNames.forEach((name) => {
                const option = document.createElement('option');
                option.value = name;
                schoolList.appendChild(option);
            });
        }
    };

    const loadSchoolList = async () => {
        if (window.SCHOOL_LIST && Array.isArray(window.SCHOOL_LIST) && window.SCHOOL_LIST.length > 0) {
            return;
        }
        try {
            const res = await fetch(`${baseUrl}/allschools.json`, { cache: 'force-cache' });
            const data = await res.json();
            if (!Array.isArray(data)) return;
            const names = data.map((entry) => {
                if (typeof entry === 'string') return entry.trim();
                if (!entry || typeof entry !== 'object') return '';
                return (entry.name || entry.school || entry['School Name'] || '').toString().trim();
            }).filter(Boolean);
            if (names.length) {
                setSchoolList(names);
            }
        } catch (err) {
            console.error('Failed to load school list:', err);
        }
    };

    loadSchoolList();

    if (schoolInput && notStudentCheckbox) {
        schoolInput.addEventListener('input', () => {
            if (schoolInput.value.trim() !== '') {
                notStudentCheckbox.checked = false;
            }
        });
        notStudentCheckbox.addEventListener('change', () => {
            if (notStudentCheckbox.checked) {
                schoolInput.value = '';
            }
        });
    }

    const isSchoolValid = (value) => {
        if (!value) return false;
        if (!window.SCHOOL_SET || window.SCHOOL_SET.size === 0) return null;
        return window.SCHOOL_SET.has(normalizeSchoolName(value));
    };

    const updateProgress = (stepIndex) => {
        const total = steps.length || 1;
        const percent = Math.round(((stepIndex + 1) / total) * 100);
        if (progressFill) {
            progressFill.style.width = `${percent}%`;
        }
        if (progressLabel) {
            progressLabel.textContent = `Step ${stepIndex + 1} of ${total}`;
        }
    };

    const showStep = (stepIndex) => {
        steps.forEach((step, index) => step.classList.toggle('active', index === stepIndex));
        updateProgress(stepIndex);
        backBtn.disabled = stepIndex === 0;
        backBtn.classList.toggle('is-disabled', stepIndex === 0);
        nextBtn.classList.toggle('hidden', stepIndex === steps.length - 1);
        errorMessage.textContent = '';
    };

    const validateStep = (stepIndex) => {
        errorMessage.textContent = '';
        if (stepIndex === 0) {
            const schoolValue = schoolInput ? schoolInput.value.trim() : '';
            if (!schoolValue && !(notStudentCheckbox && notStudentCheckbox.checked)) {
                errorMessage.textContent = 'Please provide an answer to continue.';
                return false;
            }
            if (schoolValue && notStudentCheckbox && notStudentCheckbox.checked) {
                errorMessage.textContent = 'Select either School or Not a Student, not both.';
                return false;
            }
            if (schoolValue) {
                const valid = isSchoolValid(schoolValue);
                if (valid === null) {
                    errorMessage.textContent = 'School list is loading. Please wait a moment.';
                    return false;
                }
                if (!valid) {
                    errorMessage.textContent = 'Select your school from the list.';
                    return false;
                }
            }
        }
        if (stepIndex === 1 && selectedInterests.size < 3) {
            errorMessage.textContent = 'Please select at least 3 interests.';
            return false;
        }
        if (stepIndex === 2 && !selectedPreference) {
            errorMessage.textContent = 'Please choose a personality.';
            return false;
        }
        return true;
    };

    nextBtn.addEventListener('click', () => {
        if (validateStep(currentStep) && currentStep < steps.length - 1) {
            currentStep++;
            showStep(currentStep);
        }
    });

    backBtn.addEventListener('click', () => {
        if (currentStep > 0) {
            currentStep--;
            showStep(currentStep);
        }
    });

    const saveOnboarding = async (redirectUrl) => {
        if (!validateStep(currentStep)) return;
        const data = {
            school: form.school.value,
            not_student: form.not_student.checked ? 1 : 0,
            interests: interestsInput.value,
            preference: preferenceInput.value
        };
        try {
            const res = await fetch(`${baseUrl}/save_onboarding.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const response = await res.json();
            if (response.success) {
                if (redirectUrl) {
                    window.location.href = redirectUrl;
                } else {
                    lightbox.classList.remove('visible');
                }
                return;
            }
            alert(response.message || 'Failed to save onboarding data.');
        } catch (err) {
            console.error(err);
            alert('Error saving onboarding data.');
        }
    };

    // Interests selection
    document.querySelectorAll('.onboard-focus-card').forEach(card => {
        card.addEventListener('click', () => {
            const interest = card.dataset.interest;
            if (selectedInterests.has(interest)) {
                selectedInterests.delete(interest);
                card.classList.remove('selected');
            } else {
                selectedInterests.add(interest);
                card.classList.add('selected');
            }
            interestsInput.value = Array.from(selectedInterests).join(',');
            const remaining = 3 - selectedInterests.size;
            document.getElementById('focus-area-subtitle').textContent = remaining > 0 ? `Select ${remaining} more ${remaining === 1 ? 'area' : 'areas'}.` : "Great! You're ready to continue.";
        });
    });

    // Preference selection
    document.querySelectorAll('.onboard-preference-card').forEach(card => {
        card.addEventListener('click', () => {
            selectedPreference = card.dataset.preference;
            preferenceInput.value = selectedPreference;
            document.querySelectorAll('.onboard-preference-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
        });
    });

    planButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const payUrl = button.dataset.payUrl || '';
            saveOnboarding(payUrl);
        });
    });

    // AUTO POPUP: show lightbox only if onboard_complete = 0
    fetch(`${baseUrl}/check_onboarding.php`)
        .then(res => res.json())
        .then(data => {
            if (data && data.completed === false) {
                lightbox.classList.add('visible');
            } else {
                lightbox.classList.remove('visible');
            }
        })
        .catch(err => {
            console.error('Check onboarding error:', err);
            lightbox.classList.remove('visible');
        });

    showStep(currentStep);
});
