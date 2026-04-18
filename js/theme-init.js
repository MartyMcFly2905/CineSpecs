(function () {
    var savedTheme = '';

    try {
        savedTheme = localStorage.getItem('cinespecs-theme');
    } catch (error) {
        savedTheme = '';
    }

    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}());
