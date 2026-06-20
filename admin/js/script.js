// ==============================================
// UNIFIED JAVASCRIPT - AdminOrder Dashboard
// Все скрипты в одном файле для всех страниц
// ==============================================

// ==============================================
// COMMON UTILITIES & SHARED FUNCTIONS
// ============================================== 

// Constants
const SIDEBAR_STATE_KEY = 'adminorder_sidebar_collapsed';
const THEME_KEY = 'adminorder_theme';

// Theme management (used across all pages)
function loadTheme() {
    const savedTheme = localStorage.getItem(THEME_KEY) || 'dark';
    applyTheme(savedTheme);
}

function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        const icon = theme === 'dark' ? '🌙' : '☀️';
        const label = theme === 'dark' ? 'Темная тема' : 'Светлая тема';
        const title = theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
        
        // Check if it's sidebar theme toggle with separate icon and label
        const themeIcon = themeToggle.querySelector('.theme-icon');
        const themeLabel = themeToggle.querySelector('.theme-label');
        
        if (themeIcon && themeLabel) {
            themeIcon.textContent = icon;
            themeLabel.textContent = label;
        } else {
            // For simple theme toggle (login/register pages)
            themeToggle.textContent = icon;
        }
        
        themeToggle.title = title;
    }
    localStorage.setItem(THEME_KEY, theme);
}

function toggleTheme() {
    const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    applyTheme(newTheme);
}

// Load theme on every page
loadTheme();

// Add theme toggle listener if button exists
const themeToggle = document.getElementById('themeToggle');
if (themeToggle) {
    themeToggle.addEventListener('click', toggleTheme);
}


// ==============================================
// SIDEBAR & NAVIGATION (Dashboard & Profile pages)
// ==============================================

// Initialize sidebar functionality if sidebar exists
const sidebar = document.getElementById('sidebar');
if (sidebar) {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const userProfile = document.getElementById('userProfile');
    const userDropdown = document.getElementById('userDropdown');

    // Load sidebar state from localStorage
    function loadSidebarState() {
        const isCollapsed = localStorage.getItem(SIDEBAR_STATE_KEY) === 'true';
        if (isCollapsed && window.innerWidth > 768) {
            sidebar.classList.add('collapsed');
            if (sidebarToggle) {
                sidebarToggle.innerHTML = '▶';
            }
        }
    }

    // Toggle sidebar
    function toggleSidebar() {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        if (sidebarToggle) {
            sidebarToggle.innerHTML = isCollapsed ? '▶' : '◀';
        }
        localStorage.setItem(SIDEBAR_STATE_KEY, isCollapsed);
        closeUserDropdown();
    }

    // Toggle mobile menu
    function toggleMobileMenu() {
        sidebar.classList.toggle('mobile-open');
        if (sidebarOverlay) {
            sidebarOverlay.classList.toggle('active');
        }
    }

    // Close mobile menu
    function closeMobileMenu() {
        sidebar.classList.remove('mobile-open');
        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('active');
        }
    }

    // Toggle user dropdown
    function toggleUserDropdown(e) {
        if (e) e.stopPropagation();
        if (userProfile) {
            userProfile.classList.toggle('active');
        }
    }

    // Close user dropdown
    function closeUserDropdown() {
        if (userProfile) {
            userProfile.classList.remove('active');
        }
    }

    // Event listeners
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeMobileMenu);
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleMobileMenu);
    }

    if (userProfile) {
        userProfile.addEventListener('click', toggleUserDropdown);
    }

    // Close on outside click
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768 && 
            sidebar.classList.contains('mobile-open') &&
            !sidebar.contains(e.target) && 
            mobileMenuBtn && !mobileMenuBtn.contains(e.target)) {
            closeMobileMenu();
        }
        
        if (userProfile && !userProfile.contains(e.target)) {
            closeUserDropdown();
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            if (window.innerWidth > 768) {
                toggleSidebar();
            } else {
                toggleMobileMenu();
            }
        }
        if (e.key === 'Escape') {
            if (window.innerWidth <= 768) {
                closeMobileMenu();
            }
            closeUserDropdown();
        }
    });

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            closeMobileMenu();
            loadSidebarState();
        }
    });

    // Initialize
    loadSidebarState();
}


// ==============================================
// DASHBOARD PAGE (index.html)
// ==============================================

if (document.querySelector('.stats-grid')) {
    // Smooth scroll animation observer
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.fade-in').forEach(el => observer.observe(el));

    // Interactive stat cards
    document.querySelectorAll('.stat-card').forEach(card => {
        card.addEventListener('click', () => {
            console.log('Stat card clicked:', card.querySelector('.stat-label').textContent);
        });
    });

    // Chart filter buttons
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const parent = e.target.closest('.chart-filters');
            parent.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            e.target.classList.add('active');
        });
    });
}


// ==============================================
// PROFILE PAGE (profile.html)
// ==============================================

if (document.getElementById('profileForm')) {
    const profileForm = document.getElementById('profileForm');
    const securityForm = document.getElementById('securityForm');
    const avatarInput = document.getElementById('avatarInput');
    const avatarUploadBtn = document.getElementById('avatarUploadBtn');
    const avatarPreview = document.getElementById('avatarPreview');
    const cancelBtn = document.getElementById('cancelBtn');
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    
    const darkThemeToggle = document.getElementById('darkTheme');
    const twoFactorToggle = document.getElementById('twoFactor');
    const emailNotificationsToggle = document.getElementById('emailNotifications');
    
    const newPasswordInput = document.getElementById('newPassword');
    const strengthFill = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text');

    // Avatar upload
    function handleAvatarUpload(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        if (file.size > 5 * 1024 * 1024) {
            showError('Размер файла не должен превышать 5MB');
            return;
        }
        
        if (!file.type.startsWith('image/')) {
            showError('Пожалуйста, выберите изображение');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            avatarPreview.style.backgroundImage = `url(${e.target.result})`;
            avatarPreview.style.backgroundSize = 'cover';
            avatarPreview.style.backgroundPosition = 'center';
            avatarPreview.textContent = '';
            showSuccess('Аватар обновлен');
        };
        reader.readAsDataURL(file);
    }

    // Password strength
    function checkPasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        strengthFill.className = 'strength-fill';
        strengthText.className = 'strength-text';
        
        if (strength <= 2) {
            strengthFill.classList.add('weak');
            strengthText.classList.add('weak');
            strengthText.textContent = password.length > 0 ? 'Слабый' : '';
        } else if (strength <= 4) {
            strengthFill.classList.add('medium');
            strengthText.classList.add('medium');
            strengthText.textContent = 'Средний';
        } else {
            strengthFill.classList.add('strong');
            strengthText.classList.add('strong');
            strengthText.textContent = 'Сильный';
        }
    }

    // Toggle password visibility
    function setupPasswordToggles() {
        document.querySelectorAll('.toggle-password').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                btn.textContent = type === 'password' ? '👁️' : '🙈';
            });
        });
    }

    // Profile form submission
    async function handleProfileSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(profileForm);
        const data = Object.fromEntries(formData);
        
        console.log('Profile data:', data);
        
        showLoading(profileForm);
        await new Promise(resolve => setTimeout(resolve, 1000));
        hideLoading(profileForm);
        
        showSuccess('Профиль успешно обновлен');
    }

    // Security form submission
    async function handleSecuritySubmit(e) {
        e.preventDefault();
        
        const currentPassword = document.getElementById('currentPassword').value;
        const newPassword = document.getElementById('newPassword').value;
        const confirmPassword = document.getElementById('confirmPassword').value;
        
        if (!currentPassword || !newPassword || !confirmPassword) {
            showError('Заполните все поля');
            return;
        }
        
        if (newPassword.length < 8) {
            showError('Пароль должен быть не менее 8 символов');
            return;
        }
        
        if (newPassword !== confirmPassword) {
            showError('Пароли не совпадают');
            return;
        }
        
        console.log('Password change requested');
        
        showLoading(securityForm);
        await new Promise(resolve => setTimeout(resolve, 1000));
        hideLoading(securityForm);
        
        showSuccess('Пароль успешно изменен');
        securityForm.reset();
        strengthFill.className = 'strength-fill';
        strengthText.textContent = '';
    }

    // Cancel button
    function handleCancel() {
        if (confirm('Отменить изменения?')) {
            profileForm.reset();
        }
    }

    // Delete account
    function handleDeleteAccount() {
        const confirmation = prompt('Чтобы удалить аккаунт, введите "DELETE":');
        if (confirmation === 'DELETE') {
            console.log('Account deletion requested');
            alert('Ваш аккаунт будет удален (в разработке)');
        }
    }

    // Show/hide loading
    function showLoading(form) {
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.style.opacity = '0.6';
        btn.textContent = 'Сохранение...';
    }

    function hideLoading(form) {
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = false;
        btn.style.opacity = '1';
        btn.textContent = form === profileForm ? 'Сохранить изменения' : 'Изменить пароль';
    }

    // Show messages
    function showSuccess(message) {
        removeMessages();
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        const profileContent = document.querySelector('.profile-content');
        profileContent.parentElement.insertBefore(successDiv, profileContent);
        setTimeout(() => successDiv.remove(), 3000);
    }

    function showError(message) {
        removeMessages();
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        const profileContent = document.querySelector('.profile-content');
        profileContent.parentElement.insertBefore(errorDiv, profileContent);
        setTimeout(() => errorDiv.remove(), 3000);
    }

    function removeMessages() {
        document.querySelectorAll('.success-message, .error-message').forEach(msg => msg.remove());
    }

    // Initialize
    setupPasswordToggles();

    // Event listeners - Forms
    profileForm.addEventListener('submit', handleProfileSubmit);
    securityForm.addEventListener('submit', handleSecuritySubmit);
    cancelBtn.addEventListener('click', handleCancel);
    deleteAccountBtn.addEventListener('click', handleDeleteAccount);

    // Avatar
    avatarUploadBtn.addEventListener('click', () => avatarInput.click());
    avatarInput.addEventListener('change', handleAvatarUpload);

    // Password strength
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', (e) => checkPasswordStrength(e.target.value));
    }

    // Dark theme toggle in settings
    if (darkThemeToggle) {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        darkThemeToggle.checked = currentTheme === 'dark';
        
        darkThemeToggle.addEventListener('change', (e) => {
            applyTheme(e.target.checked ? 'dark' : 'light');
        });
    }

    // Other toggles
    if (twoFactorToggle) {
        twoFactorToggle.addEventListener('change', (e) => {
            console.log('2FA:', e.target.checked);
            showSuccess(e.target.checked ? '2FA включена' : '2FA отключена');
        });
    }

    if (emailNotificationsToggle) {
        emailNotificationsToggle.addEventListener('change', (e) => {
            console.log('Email notifications:', e.target.checked);
            showSuccess(e.target.checked ? 'Email уведомления включены' : 'Email уведомления отключены');
        });
    }

    // Language & Timezone
    const languageSelect = document.getElementById('language');
    const timezoneSelect = document.getElementById('timezone');
    
    if (languageSelect) {
        languageSelect.addEventListener('change', (e) => {
            console.log('Language:', e.target.value);
            showSuccess('Язык изменен');
        });
    }

    if (timezoneSelect) {
        timezoneSelect.addEventListener('change', (e) => {
            console.log('Timezone:', e.target.value);
            showSuccess('Часовой пояс изменен');
        });
    }
}


// ==============================================
// LOGIN PAGE (login.html)
// ==============================================

if (document.getElementById('loginForm')) {
    const loginForm = document.getElementById('loginForm');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('email');

    // Password visibility toggle
    function togglePasswordVisibility() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        togglePassword.textContent = type === 'password' ? '👁️' : '🙈';
    }

    // Form validation
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function showError(input, message) {
        const wrapper = input.closest('.input-wrapper');
        wrapper.classList.add('error');
        
        const existingError = wrapper.parentElement.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        wrapper.parentElement.appendChild(errorDiv);
    }

    function clearError(input) {
        const wrapper = input.closest('.input-wrapper');
        wrapper.classList.remove('error');
        
        const errorMessage = wrapper.parentElement.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }

    function showSuccess(message) {
        const existingSuccess = loginForm.querySelector('.success-message');
        if (existingSuccess) {
            existingSuccess.remove();
        }
        
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        loginForm.insertBefore(successDiv, loginForm.firstChild);
        
        setTimeout(() => {
            successDiv.remove();
        }, 3000);
    }

    // Form submission
    async function handleLogin(e) {
        e.preventDefault();
        
        clearError(emailInput);
        clearError(passwordInput);
        
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const remember = document.getElementById('remember').checked;
        
        let isValid = true;
        
        if (!email) {
            showError(emailInput, 'Email обязателен');
            isValid = false;
        } else if (!validateEmail(email)) {
            showError(emailInput, 'Введите корректный email');
            isValid = false;
        }
        
        if (!password) {
            showError(passwordInput, 'Пароль обязателен');
            isValid = false;
        } else if (password.length < 6) {
            showError(passwordInput, 'Пароль должен быть не менее 6 символов');
            isValid = false;
        }
        
        if (!isValid) return;
        
        const submitBtn = loginForm.querySelector('.btn-primary');
        submitBtn.classList.add('loading');
        submitBtn.querySelector('span').textContent = 'Вход...';
        
        try {
            await new Promise(resolve => setTimeout(resolve, 1500));
            
            console.log('Login successful:', { email, remember });
            
            showSuccess('Вход выполнен успешно! Перенаправление...');
            
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1000);
            
        } catch (error) {
            submitBtn.classList.remove('loading');
            submitBtn.querySelector('span').textContent = 'Войти';
            showError(emailInput, 'Неверный email или пароль');
            console.error('Login error:', error);
        }
    }

    // Social login handlers
    function handleSocialLogin(provider) {
        console.log(`${provider} login clicked`);
        alert(`Вход через ${provider} (в разработке)`);
    }

    // Event listeners
    togglePassword.addEventListener('click', togglePasswordVisibility);
    loginForm.addEventListener('submit', handleLogin);

    emailInput.addEventListener('input', () => clearError(emailInput));
    passwordInput.addEventListener('input', () => clearError(passwordInput));

    document.querySelectorAll('.btn-social').forEach(btn => {
        btn.addEventListener('click', () => {
            const provider = btn.textContent.trim();
            handleSocialLogin(provider);
        });
    });

    document.querySelector('.forgot-password').addEventListener('click', (e) => {
        e.preventDefault();
        alert('Функция восстановления пароля (в разработке)');
    });
}


// ==============================================
// REGISTER PAGE (register.html)
// ==============================================

if (document.getElementById('registerForm')) {
    const registerForm = document.getElementById('registerForm');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const fullnameInput = document.getElementById('fullname');
    const emailInput = document.getElementById('email');
    const termsCheckbox = document.getElementById('terms');
    const strengthFill = document.querySelector('.strength-fill');
    const strengthText = document.querySelector('.strength-text');

    // Password visibility toggle
    function togglePasswordVisibility(input, button) {
        const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
        input.setAttribute('type', type);
        button.textContent = type === 'password' ? '👁️' : '🙈';
    }

    // Password strength checker
    function checkPasswordStrength(password) {
        let strength = 0;
        let strengthLevel = '';
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/\d/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        strengthFill.className = 'strength-fill';
        strengthText.className = 'strength-text';
        
        if (strength <= 2) {
            strengthFill.classList.add('weak');
            strengthText.classList.add('weak');
            strengthLevel = 'Слабый';
        } else if (strength <= 4) {
            strengthFill.classList.add('medium');
            strengthText.classList.add('medium');
            strengthLevel = 'Средний';
        } else {
            strengthFill.classList.add('strong');
            strengthText.classList.add('strong');
            strengthLevel = 'Сильный';
        }
        
        strengthText.textContent = password.length > 0 ? strengthLevel : '';
        
        return strength;
    }

    // Form validation
    function validateEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    }

    function validateFullname(name) {
        return name.trim().length >= 2 && /\s/.test(name.trim());
    }

    function showError(input, message) {
        const wrapper = input.closest('.input-wrapper');
        if (wrapper) {
            wrapper.classList.add('error');
        }
        
        const formGroup = input.closest('.form-group');
        const existingError = formGroup.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        
        if (wrapper) {
            formGroup.insertBefore(errorDiv, wrapper.nextSibling);
        } else {
            formGroup.appendChild(errorDiv);
        }
    }

    function clearError(input) {
        const wrapper = input.closest('.input-wrapper');
        if (wrapper) {
            wrapper.classList.remove('error');
        }
        
        const formGroup = input.closest('.form-group');
        const errorMessage = formGroup.querySelector('.error-message');
        if (errorMessage) {
            errorMessage.remove();
        }
    }

    function showSuccess(message) {
        const existingSuccess = registerForm.querySelector('.success-message');
        if (existingSuccess) {
            existingSuccess.remove();
        }
        
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        registerForm.insertBefore(successDiv, registerForm.firstChild);
        
        setTimeout(() => {
            successDiv.remove();
        }, 3000);
    }

    // Form submission
    async function handleRegister(e) {
        e.preventDefault();
        
        clearError(fullnameInput);
        clearError(emailInput);
        clearError(passwordInput);
        clearError(confirmPasswordInput);
        clearError(termsCheckbox);
        
        const fullname = fullnameInput.value.trim();
        const email = emailInput.value.trim();
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        const termsAccepted = termsCheckbox.checked;
        
        let isValid = true;
        
        if (!fullname) {
            showError(fullnameInput, 'Имя обязательно');
            isValid = false;
        } else if (!validateFullname(fullname)) {
            showError(fullnameInput, 'Введите полное имя (имя и фамилию)');
            isValid = false;
        }
        
        if (!email) {
            showError(emailInput, 'Email обязателен');
            isValid = false;
        } else if (!validateEmail(email)) {
            showError(emailInput, 'Введите корректный email');
            isValid = false;
        }
        
        if (!password) {
            showError(passwordInput, 'Пароль обязателен');
            isValid = false;
        } else if (password.length < 8) {
            showError(passwordInput, 'Пароль должен быть не менее 8 символов');
            isValid = false;
        }
        
        if (!confirmPassword) {
            showError(confirmPasswordInput, 'Подтвердите пароль');
            isValid = false;
        } else if (password !== confirmPassword) {
            showError(confirmPasswordInput, 'Пароли не совпадают');
            isValid = false;
        }
        
        if (!termsAccepted) {
            showError(termsCheckbox, 'Необходимо принять условия использования');
            isValid = false;
        }
        
        if (!isValid) return;
        
        const submitBtn = registerForm.querySelector('.btn-primary');
        submitBtn.classList.add('loading');
        submitBtn.querySelector('span').textContent = 'Регистрация...';
        
        try {
            await new Promise(resolve => setTimeout(resolve, 2000));
            
            console.log('Registration successful:', { fullname, email });
            
            showSuccess('Регистрация успешна! Перенаправление...');
            
            setTimeout(() => {
                window.location.href = 'login.html';
            }, 1500);
            
        } catch (error) {
            submitBtn.classList.remove('loading');
            submitBtn.querySelector('span').textContent = 'Зарегистрироваться';
            showError(emailInput, 'Этот email уже используется');
            console.error('Registration error:', error);
        }
    }

    // Social register handlers
    function handleSocialRegister(provider) {
        console.log(`${provider} register clicked`);
        alert(`Регистрация через ${provider} (в разработке)`);
    }

    // Event listeners
    togglePassword.addEventListener('click', () => togglePasswordVisibility(passwordInput, togglePassword));
    toggleConfirmPassword.addEventListener('click', () => togglePasswordVisibility(confirmPasswordInput, toggleConfirmPassword));
    registerForm.addEventListener('submit', handleRegister);

    fullnameInput.addEventListener('input', () => clearError(fullnameInput));
    emailInput.addEventListener('input', () => clearError(emailInput));
    passwordInput.addEventListener('input', () => {
        clearError(passwordInput);
        checkPasswordStrength(passwordInput.value);
    });
    confirmPasswordInput.addEventListener('input', () => clearError(confirmPasswordInput));
    termsCheckbox.addEventListener('change', () => clearError(termsCheckbox));

    document.querySelectorAll('.btn-social').forEach(btn => {
        btn.addEventListener('click', () => {
            const provider = btn.textContent.trim();
            handleSocialRegister(provider);
        });
    });

    document.querySelectorAll('.link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const text = link.textContent;
            alert(`Открытие: ${text} (в разработке)`);
        });
    });
}

// Оновлення лічильника нових замовлень
const updateNewOrdersCount = async () => {
    const newOrdersBadge = document.getElementById('newOrders');
    if (newOrdersBadge) {
        try {
            const response = await fetch('/admin/api/orders/count-new.php');
            const data = await response.json();

            if (data.success) {
                if (data.count > 0) {
                    newOrdersBadge.textContent = data.count;
                    newOrdersBadge.classList.add('show');
                } else {
                    newOrdersBadge.textContent = '';
                    newOrdersBadge.classList.remove('show');
                }
            } else {
                console.error('Failed to fetch new orders count:', data.message);
            }
        } catch (error) {
            console.error('Error fetching new orders count:', error);
        }
    }
};

// Оновлення статистики дашборду
const updateDashboardStats = async () => {
    try {
        const response = await fetch('/admin/api/dashboard-stats');
        const data = await response.json();

        if (data.success) {
            // Оновлення System Stats
            if (document.getElementById('stat-value-users')) {
                document.getElementById('stat-value-users').textContent = data.systemStats.users_value;
                document.getElementById('stat-trend-users').textContent = data.systemStats.users_trend;
            }
            if (document.getElementById('stat-value-blocked')) {
                document.getElementById('stat-value-blocked').textContent = data.systemStats.blocked_value;
                document.getElementById('stat-trend-blocked').textContent = data.systemStats.blocked_trend;
            }
            if (document.getElementById('stat-value-integrations')) {
                document.getElementById('stat-value-integrations').textContent = data.systemStats.integrations_value;
                document.getElementById('stat-trend-integrations').textContent = data.systemStats.integrations_trend;
            }

            // Оновлення Order Summary Stats
            if (document.getElementById('stat-value-orders')) {
                document.getElementById('stat-value-orders').textContent = data.orderSummaryStats.orders_value;
                document.getElementById('stat-trend-orders').textContent = data.orderSummaryStats.orders_trend;
            }
            if (document.getElementById('stat-value-revenue')) {
                document.getElementById('stat-value-revenue').textContent = data.orderSummaryStats.revenue_value;
                document.getElementById('stat-trend-revenue').textContent = data.orderSummaryStats.revenue_trend;
            }

            // Оновлення Order Status Stats
            if (document.getElementById('stat-value-status-new')) {
                document.getElementById('stat-value-status-new').textContent = data.orderStatusStats.status_new_value;
                document.getElementById('stat-trend-status-new').textContent = data.orderStatusStats.status_new_trend;
            }
            if (document.getElementById('stat-value-status-processing')) {
                document.getElementById('stat-value-status-processing').textContent = data.orderStatusStats.status_processing_value;
                document.getElementById('stat-trend-status-processing').textContent = data.orderStatusStats.status_processing_trend;
            }
            if (document.getElementById('stat-value-status-done')) {
                document.getElementById('stat-value-status-done').textContent = data.orderStatusStats.status_done_value;
                document.getElementById('stat-trend-status-done').textContent = data.orderStatusStats.status_done_trend;
            }
            if (document.getElementById('stat-value-status-cancelled')) {
                document.getElementById('stat-value-status-cancelled').textContent = data.orderStatusStats.status_cancelled_value;
                document.getElementById('stat-trend-status-cancelled').textContent = data.orderStatusStats.status_cancelled_trend;
            }
            if (document.getElementById('stat-value-status-spam')) {
                document.getElementById('stat-value-status-spam').textContent = data.orderStatusStats.status_spam_value;
                document.getElementById('stat-trend-status-spam').textContent = data.orderStatusStats.status_spam_trend;
            }

        } else {
            console.error('Failed to fetch dashboard stats:', data.message);
        }
    } catch (error) {
        console.error('Error fetching dashboard stats:', error);
    }
};

// Оновлювати кожні 30 секунд
setInterval(updateNewOrdersCount, 30000);
setInterval(updateDashboardStats, 30000);

// Викликати одразу після завантаження сторінки
document.addEventListener('DOMContentLoaded', updateNewOrdersCount);
document.addEventListener('DOMContentLoaded', updateDashboardStats);

