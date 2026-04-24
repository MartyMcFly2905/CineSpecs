document.addEventListener('DOMContentLoaded', function () {
    setupLoginValidation();
    setupRegisterValidation();
    setupDashboardValidation();
    setupDashboardPanels();
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

function setupDashboardValidation() {
    setupFilmValidation();
    setupFrameValidation();
}

function setupDashboardPanels() {
    var container = document.querySelector('[data-dashboard-active]');
    var buttons = document.querySelectorAll('[data-dashboard-target]');
    var panels = document.querySelectorAll('[data-dashboard-panel]');

    if (!container || buttons.length === 0 || panels.length === 0) {
        return;
    }

    function activatePanel(target) {
        buttons.forEach(function (button) {
            var isActive = button.getAttribute('data-dashboard-target') === target;
            button.classList.toggle('dashboard-switcher__button--active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
            var isActive = panel.getAttribute('data-dashboard-panel') === target;
            panel.hidden = !isActive;
            panel.classList.toggle('dashboard-panel--active', isActive);
        });

        container.setAttribute('data-dashboard-active', target);
    }

    buttons.forEach(function (button) {
        button.addEventListener('click', function () {
            activatePanel(button.getAttribute('data-dashboard-target'));
        });
    });

    activatePanel(container.getAttribute('data-dashboard-active'));
}

function setupFilmValidation() {
    var form = document.getElementById('film-form');

    if (!form) {
        return;
    }

    prepareForm(form);

    form.addEventListener('submit', function (event) {
        clearFormErrors(form);

        var title = form.querySelector('[name="titolo"]');
        var year = form.querySelector('[name="anno_uscita"]');
        var director = form.querySelector('[name="regista"]');
        var isValid = true;

        if (!title || title.value.trim() === '') {
            showFieldError(title, 'Inserisci il titolo del film.');
            isValid = false;
        }

        if (!year || year.value.trim() === '') {
            showFieldError(year, 'Inserisci l\'anno di uscita.');
            isValid = false;
        } else if (!isValidYear(year.value.trim())) {
            showFieldError(year, 'Inserisci un anno valido.');
            isValid = false;
        }

        if (!director || director.value.trim() === '') {
            showFieldError(director, 'Inserisci il regista.');
            isValid = false;
        }

        if (!isValid) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });
}

function setupFrameValidation() {
    var form = document.getElementById('frame-form');

    if (!form) {
        return;
    }

    prepareForm(form);

    form.addEventListener('submit', function (event) {
        clearFormErrors(form);

        var film = form.querySelector('[name="id_film"]');
        var timestamp = form.querySelector('[name="timestamp_frame"]');
        var image = form.querySelector('[name="immagine_frame"]');
        var isValid = true;

        if (!film || film.value.trim() === '') {
            showFieldError(film, 'Seleziona un film.');
            isValid = false;
        }

        if (!timestamp || timestamp.value.trim() === '') {
            showFieldError(timestamp, 'Inserisci il timestamp.');
            isValid = false;
        } else if (!isValidTime(timestamp.value.trim())) {
            showFieldError(timestamp, 'Usa il formato HH:MM o HH:MM:SS.');
            isValid = false;
        }

        if (!image || !image.files || image.files.length === 0) {
            showFieldError(image, 'Carica l\'immagine del frame.');
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

        field.addEventListener('change', function () {
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

function isValidYear(value) {
    if (!/^[0-9]{4}$/.test(value)) {
        return false;
    }

    return Number(value) >= 1888 && Number(value) <= new Date().getFullYear();
}

function isValidTime(value) {
    return /^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/.test(value);
}
