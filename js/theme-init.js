// imposta il tema salvato all'avvio
(function () {
    let savedTheme = '';

    try {
        savedTheme = localStorage.getItem('cinespecs-theme');
    } catch (error) {
        savedTheme = '';
    }

    if (savedTheme === 'dark') {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
}());
