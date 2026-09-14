// invio dei form login e registrazione

(function () {
    var loginForm = document.getElementById('login-form');
    var loginMessage = document.getElementById('login-message');

    // invio login
    if (loginForm) {
        loginForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            loginMessage.textContent = 'Accesso in corso...';

            try {
                var response = await fetch('auth.php', {
                    method: 'POST',
                    body: new FormData(loginForm)
                });

                var result = await response.json();
                loginMessage.textContent = result.message;

                if (result.success) {
                    window.location.href = 'dashboard.php';
                }
            } catch (error) {
                loginMessage.textContent = 'Errore di rete. Riprova.';
            }
        });
    }

    var registerForm = document.getElementById('register-form');
    var registerMessage = document.getElementById('register-message');

    // invio registrazione
    if (registerForm) {
        registerForm.addEventListener('submit', async function (event) {
            event.preventDefault();
            registerMessage.textContent = 'Registrazione in corso...';

            try {
                var response = await fetch('auth.php', {
                    method: 'POST',
                    body: new FormData(registerForm)
                });

                var result = await response.json();
                registerMessage.textContent = result.message;

                if (result.success) {
                    window.location.href = 'login.php';
                }
            } catch (error) {
                registerMessage.textContent = 'Errore di rete. Riprova.';
            }
        });
    }
}());
