(function () {
    var themeButton = document.getElementById('theme-toggle');
    var brandLogo = document.getElementById('brand-logo');

    if (!themeButton) {
        return;
    }

    function getCurrentTheme() {
        return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function saveTheme(theme) {
        try {
            localStorage.setItem('cinespecs-theme', theme);
        } catch (error) {
            return;
        }
    }

    function updateButton(theme) {
        var isDark = theme === 'dark';

        themeButton.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        themeButton.textContent = isDark ? 'Tema chiaro' : 'Tema scuro';
    }

    function updateLogo(theme) {
        if (!brandLogo) {
            return;
        }

        brandLogo.src = theme === 'dark' ? brandLogo.dataset.darkLogo : brandLogo.dataset.lightLogo;
    }

    function applyTheme(theme) {
        if (theme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }

        updateButton(theme);
        updateLogo(theme);
        saveTheme(theme);
    }

    updateButton(getCurrentTheme());
    updateLogo(getCurrentTheme());

    themeButton.addEventListener('click', function () {
        var nextTheme = getCurrentTheme() === 'dark' ? 'light' : 'dark';
        applyTheme(nextTheme);
    });
}());
