<?php
// Gestione autenticazione: register, login, logout
require __DIR__ . '/config.php';

session_start();

header('Content-Type: application/json; charset=utf-8');


// Legge e pulisce i campi POST
function get_post_value($key)
{
    $value = filter_input(INPUT_POST, $key, FILTER_DEFAULT);

    if ($value === null) {
        return null;
    }

    return trim($value);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(false, 'Metodo non consentito. Usa una richiesta POST.', 405);
}

$action = get_post_value('action');

if ($action === null || $action === '') {
    send_json(false, 'Azione mancante.', 400);
}

try {
    if ($action === 'register') {
        $username = get_post_value('username');
        $email = get_post_value('email');
        $password = get_post_value('password');

        if ($username === null || $username === '') {
            send_json(false, 'Username obbligatorio.', 400);
        }

        if ($email === null || $email === '') {
            send_json(false, 'Email obbligatoria.', 400);
        }

        if ($password === null || $password === '') {
            send_json(false, 'Password obbligatoria.', 400);
        }

        if (strlen($password) < 8) {
            send_json(false, 'La password deve avere almeno 8 caratteri.', 400);
        }

        if (strlen($username) > 50) {
            send_json(false, 'Username troppo lungo.', 400);
        }

        if (strlen($email) > 100 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            send_json(false, 'Email non valida.', 400);
        }

        // Controlla se username o email esistono gia
        if (db_query($pdo,
            'SELECT id_utente FROM utenti WHERE username = ? OR email = ? LIMIT 1',
            [$username, $email]
        )->fetch()) {
            send_json(false, 'Username o email gia in uso.', 409);
        }

        // Hash della password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        db_query($pdo,
            'INSERT INTO utenti (username, email, password_hash, ruolo) VALUES (?, ?, ?, ?)',
            [$username, $email, $passwordHash, 'contributor']
        );

        send_json(true, 'Registrazione completata.', 201);
    }

    if ($action === 'login') {
        $username = get_post_value('username');
        $password = get_post_value('password');

        if ($username === null || $username === '') {
            send_json(false, 'Username obbligatorio.', 400);
        }

        if ($password === null || $password === '') {
            send_json(false, 'Password obbligatoria.', 400);
        }

        $user = db_query($pdo,
            'SELECT id_utente, username, password_hash, ruolo FROM utenti WHERE username = ? LIMIT 1',
            [$username]
        )->fetch();

        // Verifica la password
        if (!$user || !password_verify($password, $user['password_hash'])) {
            send_json(false, 'Credenziali non valide.', 401);
        }

        // Rigenera l'ID di sessione
        session_regenerate_id(true);

        $_SESSION['id_utente'] = (int) $user['id_utente'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['ruolo'] = $user['ruolo'];

        send_json(true, 'Login effettuato.', 200);
    }

    if ($action === 'logout') {
        $_SESSION = [];

        // Cancella anche il cookie di sessione
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 3600,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        send_json(true, 'Logout effettuato.', 200);
    }

    send_json(false, 'Azione non valida.', 400);
} catch (PDOException $e) {
    error_log($e->getMessage());
    send_json(false, 'Errore del server.', 500);
}
