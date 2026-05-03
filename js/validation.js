document.addEventListener('DOMContentLoaded', function () {
    setupLoginValidation();
    setupRegisterValidation();
    setupDashboardValidation();
    setupDashboardPanels();
    setupDashboardContent();
});

function setupLoginValidation() {
    let form = document.getElementById('login-form');

    if (!form) {
        return;
    }

    prepareForm(form);

    form.addEventListener('submit', function (event) {
        clearFormErrors(form);

        let username = form.querySelector('[name="username"]');
        let password = form.querySelector('[name="password"]');
        let isValid = true;

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
    let form = document.getElementById('register-form');

    if (!form) {
        return;
    }

    prepareForm(form);

    form.addEventListener('submit', function (event) {
        clearFormErrors(form);

        let email = form.querySelector('[name="email"]');
        let username = form.querySelector('[name="username"]');
        let password = form.querySelector('[name="password"]');
        let passwordConfirm = form.querySelector('[name="password_confirm"]');
        let isValid = true;

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
    let container = document.querySelector('[data-dashboard-active]');
    let buttons = document.querySelectorAll('[data-dashboard-target]');
    let panels = document.querySelectorAll('[data-dashboard-panel]');

    if (!container || buttons.length === 0 || panels.length === 0) {
        return;
    }

    function activatePanel(target) {
        buttons.forEach(function (button) {
            let isActive = button.getAttribute('data-dashboard-target') === target;
            button.classList.toggle('dashboard-switcher__button--active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        panels.forEach(function (panel) {
            let isActive = panel.getAttribute('data-dashboard-panel') === target;
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

function setupDashboardContent() {
    let buttons = document.querySelectorAll('[data-content-action]');
    let list = document.getElementById('my-content-list');
    let status = document.getElementById('my-content-status');

    if (buttons.length === 0 || !list || !status) {
        return;
    }

    let currentAction = '';
    let currentScope = '';
    let currentSort = '';
    let currentDirection = 'desc';
    let currentPage = 1;

    function setStatus(message) {
        status.textContent = message;
    }

    function setActiveButton(action, scope) {
        buttons.forEach(function (button) {
            let buttonScope = button.getAttribute('data-content-scope') || '';
            let isActive = button.getAttribute('data-content-action') === action && buttonScope === scope;
            button.classList.toggle('dashboard-stat--active', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
    }

    function buildListUrl(action, scope, sort, direction, page) {
        let url = 'api_crud.php?action=' + encodeURIComponent(action);

        if (scope) {
            url += '&scope=' + encodeURIComponent(scope);
        }

        if (sort) {
            url += '&sort=' + encodeURIComponent(sort);
            url += '&direction=' + encodeURIComponent(direction);
        }

        if (page && page > 1) {
            url += '&page=' + encodeURIComponent(page);
        }

        return url;
    }

    function loadContent(action, scope, sort, direction, page) {
        currentAction = action;
        currentScope = scope || '';
        currentSort = sort || '';
        currentDirection = direction || 'desc';
        currentPage = page || 1;
        list.setAttribute('data-current-action', action);
        setActiveButton(action, currentScope);
        setStatus('Caricamento in corso...');
        list.innerHTML = '';

        fetch(buildListUrl(action, currentScope, currentSort, currentDirection, currentPage), {
            method: 'GET',
        })
            .then(function (response) {
                return response.json().then(function (result) {
                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Errore durante il caricamento.');
                    }

                    return result;
                });
            })
            .then(function (result) {
                let items = result.data.items || [];
                let total = typeof result.data.total === 'number' ? result.data.total : items.length;
                let pageInfo = paginationInfo(result.data, total);

                if (items.length === 0 && total > 0 && pageInfo.page > pageInfo.totalPages) {
                    loadContent(action, currentScope, currentSort, currentDirection, pageInfo.totalPages);
                    return;
                }

                currentPage = pageInfo.page;
                renderContent(action, items, pageInfo);
                setStatus(statusMessage(total, pageInfo));
                updateStatFromAction(action, total, currentScope);
            })
            .catch(function (error) {
                list.innerHTML = '';
                setStatus(error.message || 'Errore durante il caricamento.');
            });
    }

    function renderContent(action, items, pageInfo) {
        if (items.length === 0) {
            list.innerHTML = '<p class="empty-state">Nessun contenuto da mostrare.</p>';
            return;
        }

        let wrapper = document.createElement('div');
        let table = document.createElement('table');
        let thead = document.createElement('thead');
        let tbody = document.createElement('tbody');

        wrapper.className = 'my-content-table-wrap';
        table.className = 'my-content-table';
        thead.appendChild(createTableHead(action));

        items.forEach(function (item) {
            tbody.appendChild(createContentRow(action, item));
        });

        table.appendChild(thead);
        table.appendChild(tbody);
        wrapper.appendChild(table);
        list.appendChild(wrapper);

        if (pageInfo.totalPages > 1) {
            list.appendChild(createPagination(pageInfo));
        }
    }

    function paginationInfo(data, total) {
        let pageSize = Number(data.page_size || 10);
        let totalPages = Number(data.total_pages || Math.ceil(total / pageSize) || 1);
        let page = Number(data.page || currentPage || 1);

        return {
            page: page,
            pageSize: pageSize,
            totalPages: Math.max(1, totalPages),
            hasPrev: Boolean(data.has_prev),
            hasNext: Boolean(data.has_next),
        };
    }

    function statusMessage(total, pageInfo) {
        let base = total === 1 ? '1 contenuto trovato.' : total + ' contenuti trovati.';

        if (total > pageInfo.pageSize) {
            return base + ' Pagina ' + pageInfo.page + ' di ' + pageInfo.totalPages + '.';
        }

        return base;
    }

    function createPagination(pageInfo) {
        let controls = document.createElement('div');
        let prevButton = document.createElement('button');
        let nextButton = document.createElement('button');
        let label = document.createElement('span');

        controls.className = 'content-pagination';
        prevButton.type = 'button';
        prevButton.className = 'content-pagination__button';
        prevButton.textContent = 'Precedente';
        prevButton.disabled = !pageInfo.hasPrev;
        prevButton.setAttribute('data-page', String(pageInfo.page - 1));

        label.className = 'content-pagination__label';
        label.textContent = 'Pagina ' + pageInfo.page + ' di ' + pageInfo.totalPages;

        nextButton.type = 'button';
        nextButton.className = 'content-pagination__button';
        nextButton.textContent = 'Successivo';
        nextButton.disabled = !pageInfo.hasNext;
        nextButton.setAttribute('data-page', String(pageInfo.page + 1));

        controls.appendChild(prevButton);
        controls.appendChild(label);
        controls.appendChild(nextButton);

        return controls;
    }

    function createTableHead(action) {
        let row = document.createElement('tr');
        let labels = tableColumns(action);

        labels.forEach(function (label) {
            let th = document.createElement('th');

            if (label.sort) {
                let button = document.createElement('button');
                button.type = 'button';
                button.className = 'table-sort-button';
                button.setAttribute('data-sort-key', label.sort);
                button.textContent = label.text;

                if (currentSort === label.sort) {
                    button.setAttribute('aria-sort', currentDirection === 'asc' ? 'ascending' : 'descending');
                    button.textContent += currentDirection === 'asc' ? ' ↑' : ' ↓';
                }

                th.appendChild(button);
            } else {
                th.textContent = label.text;
            }

            row.appendChild(th);
        });

        return row;
    }

    function tableColumns(action) {
        if (action === 'list_my_films') {
            return [
                { text: 'Contenuto', sort: 'name' },
                { text: 'Dettagli', sort: '' },
                { text: 'Data', sort: 'date' },
                { text: 'Autore', sort: 'author' },
                { text: '', sort: '' },
            ];
        }

        return [
            { text: 'Contenuto', sort: 'name' },
            { text: 'Film/frame', sort: 'film' },
            { text: 'Voti', sort: 'votes' },
            { text: 'Data', sort: 'date' },
            { text: 'Autore', sort: 'author' },
            { text: '', sort: '' },
        ];
    }

    function createContentRow(action, item) {
        let row = document.createElement('tr');
        let button = document.createElement('button');
        let deleteConfig = getDeleteConfig(action, item);
        let actions = createRowActions(action, item, button);
        let cells;

        button.type = 'button';
        button.className = 'my-content-delete';
        button.textContent = 'Elimina';
        button.setAttribute('data-delete-action', deleteConfig.action);
        button.setAttribute('data-delete-id-name', deleteConfig.idName);
        button.setAttribute('data-delete-id', deleteConfig.id);

        if (action === 'list_my_films') {
            cells = [
                item.titolo + ' (' + item.anno_uscita + ')',
                item.regista,
                shortDate(item.creato_il),
                item.autore,
                actions,
            ];
        } else if (action === 'list_my_frames') {
            cells = [
                item.descrizione_scena || 'Frame ' + (item.timestamp_frame || 'n/d'),
                item.film_titolo + ' · ' + (item.timestamp_frame || 'n/d'),
                formatVotes(item),
                shortDate(item.creato_il),
                item.autore,
                actions,
            ];
        } else {
            cells = [
                item.produttore + ' · ' + item.nome_modello,
                item.film_titolo + ' · ' + (item.timestamp_frame || 'n/d'),
                formatVotes(item),
                shortDate(item.creato_il),
                item.autore,
                actions,
            ];
        }

        cells.forEach(function (value) {
            let cell = document.createElement('td');

            if (value instanceof HTMLElement) {
                cell.appendChild(value);
            } else {
                cell.textContent = value || '-';
            }

            row.appendChild(cell);
        });

        return row;
    }

    function createRowActions(action, item, deleteButton) {
        let wrapper = document.createElement('div');
        let editLink = editLinkFor(action, item);

        wrapper.className = 'my-content-actions';

        if (editLink) {
            wrapper.appendChild(editLink);
        }

        wrapper.appendChild(deleteButton);

        return wrapper;
    }

    function editLinkFor(action, item) {
        let link = document.createElement('a');

        if (currentScope === 'all') {
            return null;
        }

        if (action === 'list_my_films') {
            link.href = 'dashboard.php?tab=create_film&edit_film=' + encodeURIComponent(item.id_film);
        } else if (action === 'list_my_frames') {
            link.href = 'dashboard.php?tab=create_frame&edit_frame=' + encodeURIComponent(item.id_frame);
        } else {
            return null;
        }

        link.className = 'my-content-edit';
        link.textContent = 'Modifica';

        return link;
    }

    function getDeleteConfig(action, item) {
        if (action === 'list_my_films') {
            return { action: 'delete_film', idName: 'id_film', id: item.id_film };
        }

        if (action === 'list_my_frames') {
            return { action: 'delete_frame', idName: 'id_frame', id: item.id_frame };
        }

        return { action: 'delete_tag', idName: 'id_tag', id: item.id_tag };
    }

    function formatVotes(item) {
        let upvotes = Number(item.upvotes || 0);
        let downvotes = Number(item.downvotes || 0);

        return '+' + upvotes + ' / -' + downvotes;
    }

    function deleteContent(button) {
        let action = button.getAttribute('data-delete-action');
        let idName = button.getAttribute('data-delete-id-name');
        let id = button.getAttribute('data-delete-id');
        let formData = new FormData();

        if (!window.confirm('Eliminare questo contenuto?')) {
            return;
        }

        formData.append('action', action);
        formData.append(idName, id);
        button.disabled = true;
        setStatus('Eliminazione in corso...');

        fetch('api_crud.php', {
            method: 'POST',
            body: formData,
        })
            .then(function (response) {
                return response.json().then(function (result) {
                    if (!response.ok || !result.success) {
                        throw new Error(result.message || 'Errore durante l\'eliminazione.');
                    }

                    return result;
                });
            })
            .then(function (result) {
                setStatus(result.message || 'Contenuto eliminato.');
                loadContent(currentAction, currentScope, currentSort, currentDirection, currentPage);
                refreshDashboardStats();
            })
            .catch(function (error) {
                button.disabled = false;
                setStatus(error.message || 'Errore durante l\'eliminazione.');
            });
    }

    function refreshDashboardStats() {
        buttons.forEach(function (button) {
            let action = button.getAttribute('data-content-action');
            let scope = button.getAttribute('data-content-scope') || '';

            fetch(buildListUrl(action, scope, '', 'desc', 1), {
                method: 'GET',
            })
                .then(function (response) {
                    return response.ok ? response.json() : null;
                })
                .then(function (result) {
                    if (result && result.success && result.data.items) {
                        let total = typeof result.data.total === 'number'
                            ? result.data.total
                            : result.data.items.length;
                        updateStatFromAction(action, total, scope);
                    }
                })
                .catch(function () {});
        });
    }

    function updateStatFromAction(action, count, scope) {
        let selectedButton = null;

        buttons.forEach(function (button) {
            let buttonScope = button.getAttribute('data-content-scope') || '';

            if (button.getAttribute('data-content-action') === action && buttonScope === (scope || '')) {
                selectedButton = button;
            }
        });

        let value = selectedButton ? selectedButton.querySelector('.dashboard-stat__value') : null;

        if (value) {
            value.textContent = count;
        }
    }

    function shortDate(value) {
        if (!value) {
            return '-';
        }

        return value.slice(0, 10);
    }

    buttons.forEach(function (button) {
        button.setAttribute('aria-pressed', 'false');
        button.addEventListener('click', function () {
            currentSort = '';
            currentDirection = 'desc';
            currentPage = 1;
            loadContent(
                button.getAttribute('data-content-action'),
                button.getAttribute('data-content-scope') || '',
                currentSort,
                currentDirection,
                currentPage
            );
        });
    });

    list.addEventListener('click', function (event) {
        let sortButton = event.target.closest('[data-sort-key]');

        if (sortButton) {
            let nextSort = sortButton.getAttribute('data-sort-key');
            let nextDirection = currentSort === nextSort && currentDirection === 'desc' ? 'asc' : 'desc';
            loadContent(currentAction, currentScope, nextSort, nextDirection, 1);
            return;
        }

        let pageButton = event.target.closest('[data-page]');

        if (pageButton && !pageButton.disabled) {
            let nextPage = Number(pageButton.getAttribute('data-page'));

            if (nextPage > 0) {
                loadContent(currentAction, currentScope, currentSort, currentDirection, nextPage);
            }

            return;
        }

        let button = event.target.closest('[data-delete-action]');

        if (button) {
            deleteContent(button);
        }
    });
}

function setupFilmValidation() {
    let form = document.getElementById('film-form');

    if (!form) {
        return;
    }

    prepareForm(form);

    form.addEventListener('submit', function (event) {
        clearFormErrors(form);

        let title = form.querySelector('[name="titolo"]');
        let year = form.querySelector('[name="anno_uscita"]');
        let director = form.querySelector('[name="regista"]');
        let isValid = true;

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
    let form = document.getElementById('frame-form');

    if (!form) {
        return;
    }

    prepareForm(form);

    form.addEventListener('submit', function (event) {
        clearFormErrors(form);

        let film = form.querySelector('[name="id_film"]');
        let timestamp = form.querySelector('[name="timestamp_frame"]');
        let image = form.querySelector('[name="immagine_frame"]');
        let isValid = true;

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

        if (form.querySelector('[name="action"]').value !== 'update_frame' && (!image || !image.files || image.files.length === 0)) {
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
    let fields = form.querySelectorAll('input, select, textarea');

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
    let group = field.closest('.field-group');

    if (!group || group.querySelector('.field-error')) {
        return;
    }

    let error = document.createElement('p');
    error.className = 'field-error';
    error.setAttribute('aria-live', 'polite');
    error.style.visibility = 'hidden';
    group.appendChild(error);
}

function showFieldError(field, message) {
    if (!field) {
        return;
    }

    let group = field.closest('.field-group');

    if (!group) {
        return;
    }

    let error = group.querySelector('.field-error');

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

    let group = field.closest('.field-group');

    if (!group) {
        return;
    }

    let error = group.querySelector('.field-error');
    field.removeAttribute('aria-invalid');
    field.style.borderColor = '';

    if (error) {
        error.textContent = '';
        error.style.visibility = 'hidden';
    }
}

function clearFormErrors(form) {
    let fields = form.querySelectorAll('input, select, textarea');

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
