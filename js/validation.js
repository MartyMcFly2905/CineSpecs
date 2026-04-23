document.addEventListener('DOMContentLoaded', function () {
    setupLoginValidation();
    setupRegisterValidation();
});

function setupLoginValidation() {
    var form = document.getElementById('login-form');

    if (!form) {
        return;
    }

    prepareForm(form);

    form.addEventListener('submit', function (event) {
        clearFormErrors(form);

        var username = form.querySelector('[name="username"]');
        var password = form.querySelector('[name="password"]');
        var isValid = true;

        if (!username || username.value.trim() === '') {
            showFieldError(username, 'Inserisci lo username.');
            isValid = false;
        }

        if (!password || password.value.trim() === '') {
            showFieldError(password, 'Inserisci la password.');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });
}

function setupRegisterValidation() {
    var form = document.getElementById('register-form');

    if (!form) {
        return;
    }

    prepareForm(form);

    form.addEventListener('submit', function (event) {
        clearFormErrors(form);

        var email = form.querySelector('[name="email"]');
        var username = form.querySelector('[name="username"]');
        var password = form.querySelector('[name="password"]');
        var passwordConfirm = form.querySelector('[name="password_confirm"]');
        var isValid = true;

        if (!email || email.value.trim() === '') {
            showFieldError(email, 'Inserisci l\'email.');
            isValid = false;
        } else if (!isValidEmail(email.value.trim())) {
            showFieldError(email, 'Inserisci un\'email valida.');
            isValid = false;
        }

        if (!username || username.value.trim() === '') {
            showFieldError(username, 'Inserisci lo username.');
            isValid = false;
        }

        if (!password || password.value.trim() === '') {
            showFieldError(password, 'Inserisci la password.');
            isValid = false;
        } else if (password.value.length < 8) {
            showFieldError(password, 'La password deve avere almeno 8 caratteri.');
            isValid = false;
        }

        if (passwordConfirm && passwordConfirm.value !== password.value) {
            showFieldError(passwordConfirm, 'Le password non coincidono.');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });
}

function prepareForm(form) {
    var fields = form.querySelectorAll('input, select, textarea');

    fields.forEach(function (field) {
        if (field.type === 'hidden') {
            return;
        }

        ensureErrorElement(field);

        field.addEventListener('input', function () {
            clearFieldError(field);
        });
    });
}

function ensureErrorElement(field) {
    var group = field.closest('.field-group');

    if (!group || group.querySelector('.field-error')) {
        return;
    }

    var error = document.createElement('p');
    error.className = 'field-error';
    error.setAttribute('aria-live', 'polite');
    error.style.visibility = 'hidden';
    group.appendChild(error);
}

function showFieldError(field, message) {
    if (!field) {
        return;
    }

    var group = field.closest('.field-group');

    if (!group) {
        return;
    }

    var error = group.querySelector('.field-error');

    if (!error) {
        return;
    }

    field.setAttribute('aria-invalid', 'true');
    field.style.borderColor = '#c62828';
    error.textContent = message;
    error.style.visibility = 'visible';
}

function clearFieldError(field) {
    if (!field) {
        return;
    }

    var group = field.closest('.field-group');

    if (!group) {
        return;
    }

    var error = group.querySelector('.field-error');
    field.removeAttribute('aria-invalid');
    field.style.borderColor = '';

    if (error) {
        error.textContent = '';
        error.style.visibility = 'hidden';
    }
}

function clearFormErrors(form) {
    var fields = form.querySelectorAll('input, select, textarea');

    fields.forEach(function (field) {
        clearFieldError(field);
    });
}

function isValidEmail(value) {
    return value.includes('@') && value.includes('.');
}
