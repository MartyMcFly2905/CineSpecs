// visualizzatore frame e tag
document.addEventListener('DOMContentLoaded', function () {
    const frameImage = document.getElementById('frame-image');
    const tagLayer = document.getElementById('tag-layer');
    const sidebarContent = document.getElementById('sidebar-content');
    const sidebarMeta = document.getElementById('sidebar-meta');
    const sidebarTitle = document.getElementById('sidebar-title');
    const sidebarVotes = document.getElementById('sidebar-votes');
    const timelineList = document.querySelector('.timeline-list');
    const frameTimestamp = document.getElementById('frame-timestamp');
    const frameDescription = document.getElementById('frame-description');
    const frameAuthor = document.getElementById('frame-author');
    const frameVotes = document.getElementById('frame-votes');
    const inspectionToggle = document.getElementById('inspection-toggle');

    if (!frameImage || !tagLayer || !sidebarContent || !sidebarMeta || !sidebarTitle || !sidebarVotes) {
        console.error('Elementi del viewer non trovati.');
        return;
    }

    const frameId = frameImage.dataset.frameId;

    if (!frameId || !/^[1-9][0-9]*$/.test(frameId)) {
        mostraMessaggioSidebar('Frame non valido.');
        console.error('data-frame-id mancante o non valido sull\'immagine principale.');
        return;
    }

    caricaTag(frameId, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes);

    if (inspectionToggle) {
        preparaIspezione(inspectionToggle, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes);
    }

    if (frameVotes) {
        preparaVotiContenuto(frameVotes, function (id, upvotes, downvotes) {
            aggiornaTimelineVotiFrame(id, upvotes, downvotes);
        });
    }

    preparaVotiContenuto(sidebarVotes);

    if (timelineList && frameTimestamp && frameDescription && frameAuthor) {
        preparaTimeline(timelineList, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor, frameVotes);
    }
});

// carica i tag del frame
async function caricaTag(frameId, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes) {
    try {
        const response = await fetch('api_get_tags.php?id_frame=' + encodeURIComponent(frameId));

        if (!response.ok) {
            throw new Error('Errore HTTP ' + response.status);
        }

        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Errore nel caricamento dei tag.');
        }

        disegnaTag(result.data, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes);
    } catch (error) {
        console.error(error);
        svuotaElemento(tagLayer);
        resetSidebarMeta(sidebarMeta);
        resetSidebarTitle(sidebarTitle);
        aggiornaSidebarVotes(sidebarVotes, '', 0, 0);
        mostraMessaggioSidebar('Impossibile caricare i tag del frame.');
    }
}

function preparaTimeline(timelineList, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor, frameVotes) {
    timelineList.addEventListener('click', function (event) {
        const timelineItem = event.target.closest('.timeline-item');

        if (!timelineItem) {
            return;
        }

        cambiaFrame(timelineItem, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor, frameVotes);
    });

    timelineList.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        const timelineItem = event.target.closest('.timeline-item');

        if (!timelineItem) {
            return;
        }

        event.preventDefault();
        cambiaFrame(timelineItem, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor, frameVotes);
    });
}

// cambia frame selezionato
function cambiaFrame(timelineItem, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor, frameVotes) {
    const frameId = timelineItem.dataset.frameId;
    const frameSrc = timelineItem.dataset.frameSrc;

    if (!frameId || !/^[1-9][0-9]*$/.test(frameId) || !frameSrc) {
        console.error('Frame della timeline non valido.');
        return;
    }

    frameImage.src = frameSrc;
    frameImage.alt = timelineItem.dataset.frameAlt || 'Frame del film';
    frameImage.dataset.frameId = frameId;
    frameTimestamp.textContent = timelineItem.dataset.frameTimestamp || 'Timestamp non disponibile';
    frameDescription.textContent = timelineItem.dataset.frameDescription || '';
    frameAuthor.textContent = timelineItem.dataset.frameAuthor || 'Aggiunto da: utente non disponibile';

    if (frameVotes) {
        aggiornaVotiContenuto(
            frameVotes,
            'frame',
            frameId,
            Number(timelineItem.dataset.frameUpvotes || 0),
            Number(timelineItem.dataset.frameDownvotes || 0)
        );
    }

    aggiornaFrameAttivo(timelineItem);
    svuotaElemento(tagLayer);
    resetSidebarMeta(sidebarMeta);
    resetSidebarTitle(sidebarTitle);
    aggiornaSidebarVotes(sidebarVotes, '', 0, 0);
    mostraMessaggioSidebar('Seleziona un Pulse-Tag per vedere i dettagli hardware.');
    caricaTag(frameId, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes);
}

// gestione voti
function preparaVotiContenuto(container, afterUpdate) {
    container.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-vote]');

        if (!button || button.disabled) {
            return;
        }

        const targetType = container.dataset.voteTarget;
        const targetId = container.dataset.voteId;
        const vote = button.dataset.vote;
        const formData = new FormData();

        if (!targetType || !targetId || !/^[1-9][0-9]*$/.test(targetId)) {
            return;
        }

        formData.append('target_type', targetType);
        formData.append(targetType === 'frame' ? 'id_frame' : 'id_tag', targetId);
        formData.append('vote', vote);
        button.disabled = true;

        try {
            const response = await fetch('api_vote.php', {
                method: 'POST',
                body: formData,
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Errore durante il voto.');
            }

            aggiornaVotiContenuto(container, targetType, targetId, result.data.upvotes, result.data.downvotes);

            if (afterUpdate) {
                afterUpdate(targetId, result.data.upvotes, result.data.downvotes);
            }
        } catch (error) {
            console.error(error);
        } finally {
            button.disabled = false;
        }
    });
}

function aggiornaVotiContenuto(container, targetType, targetId, upvotes, downvotes) {
    const upCount = container.querySelector('[data-vote-count="up"]');
    const downCount = container.querySelector('[data-vote-count="down"]');

    container.dataset.voteTarget = targetType;
    container.dataset.voteId = targetId || '';

    if (upCount) {
        upCount.textContent = String(upvotes);
    }

    if (downCount) {
        downCount.textContent = String(downvotes);
    }
}

function aggiornaTimelineVotiFrame(frameId, upvotes, downvotes) {
    const timelineItem = document.querySelector('[data-frame-id="' + frameId + '"]');

    if (!timelineItem) {
        return;
    }

    timelineItem.dataset.frameUpvotes = String(upvotes);
    timelineItem.dataset.frameDownvotes = String(downvotes);
}

// modalita per aggiungere tag
function preparaIspezione(inspectionToggle, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes) {
    let inspectionActive = false;
    const frameBox = frameImage.closest('.frame-tag-layer');

    inspectionToggle.addEventListener('click', function () {
        inspectionActive = !inspectionActive;
        inspectionToggle.setAttribute('aria-pressed', inspectionActive ? 'true' : 'false');
        inspectionToggle.textContent = inspectionActive ? 'Ispezione attiva' : 'Aggiungi tag';

        if (frameBox) {
            frameBox.classList.toggle('frame-tag-layer--inspect', inspectionActive);
        }

        resetSidebarMeta(sidebarMeta);
        resetSidebarTitle(sidebarTitle);
        aggiornaSidebarVotes(sidebarVotes, '', 0, 0);
        mostraMessaggioSidebar(inspectionActive ? 'Clicca sul punto del frame in cui vuoi aggiungere il tag.' : 'Seleziona un Pulse-Tag per vedere i dettagli hardware.');
    });

    frameImage.addEventListener('click', function (event) {
        if (!inspectionActive) {
            return;
        }

        const coordinates = calcolaCoordinateFrame(event, frameImage);

        if (!coordinates) {
            mostraMessaggioSidebar('Coordinate non valide.');
            return;
        }

        mostraFormIspezione(
            frameImage.dataset.frameId,
            coordinates.x,
            coordinates.y,
            tagLayer,
            sidebarContent,
            sidebarMeta,
            sidebarTitle,
            sidebarVotes
        );
    });
}

function calcolaCoordinateFrame(event, frameImage) {
    const rect = frameImage.getBoundingClientRect();

    if (!rect.width || !rect.height) {
        return null;
    }

    const x = ((event.clientX - rect.left) / rect.width) * 100;
    const y = ((event.clientY - rect.top) / rect.height) * 100;

    return {
        x: limitaPercentuale(x),
        y: limitaPercentuale(y),
    };
}

function limitaPercentuale(value) {
    return Math.max(0, Math.min(100, Number(value.toFixed(2))));
}

function mostraFormIspezione(frameId, coordX, coordY, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes) {
    svuotaElemento(sidebarContent);
    aggiornaSidebarTitle(sidebarTitle, 'Nuovo tag');
    aggiornaSidebarVotes(sidebarVotes, '', 0, 0);
    svuotaElemento(sidebarMeta);

    const form = document.createElement('form');
    const message = document.createElement('p');

    form.className = 'inspection-form';
    message.className = 'inspection-message';
    message.setAttribute('aria-live', 'polite');

    aggiungiInputNascosto(form, 'id_frame', frameId);
    aggiungiInputNascosto(form, 'coord_x', coordX);
    aggiungiInputNascosto(form, 'coord_y', coordY);

    aggiungiSelectHardware(form);
    aggiungiCampoTesto(form, 'nome_modello', 'Nome modello', 'text', true, 120);
    aggiungiCampoTesto(form, 'produttore', 'Produttore', 'text', true, 120);
    aggiungiCampoTesto(form, 'anno_rilascio', 'Anno produzione', 'number', false, null);
    aggiungiAreaTesto(form, 'descrizione', 'Descrizione');
    aggiungiAreaTesto(form, 'curiosita', 'Curiosità');
    aggiungiCheckbox(form, 'prop_fittizio', 'Prop fittizio');

    const submitGroup = document.createElement('div');
    const submitButton = document.createElement('button');
    submitGroup.className = 'field-group';
    submitButton.type = 'submit';
    submitButton.textContent = 'Salva tag';
    submitGroup.appendChild(submitButton);
    form.appendChild(submitGroup);
    form.appendChild(message);

    form.addEventListener('change', function (event) {
        if (event.target.name === 'id_hardware') {
            aggiornaCampiManuali(form);
        }
    });

    form.addEventListener('submit', function (event) {
        event.preventDefault();
        salvaTag(form, submitButton, message, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes);
    });

    sidebarContent.appendChild(form);
    aggiornaCampiManuali(form);
}

function aggiungiSelectHardware(form) {
    const group = document.createElement('div');
    const label = document.createElement('label');
    const select = document.createElement('select');
    const emptyOption = document.createElement('option');
    const template = document.getElementById('hardware-options-template');

    group.className = 'field-group';
    label.setAttribute('for', 'inspection-hardware');
    label.textContent = 'Hardware esistente';
    select.name = 'id_hardware';
    select.id = 'inspection-hardware';
    emptyOption.value = '';
    emptyOption.textContent = 'Seleziona già esistente';
    select.appendChild(emptyOption);

    if (template) {
        select.appendChild(template.content.cloneNode(true));
    }

    group.appendChild(label);
    group.appendChild(select);
    form.appendChild(group);
}

function aggiungiCampoTesto(form, name, labelText, type, required, maxLength) {
    const group = document.createElement('div');
    const label = document.createElement('label');
    const input = document.createElement('input');

    group.className = 'field-group';
    label.setAttribute('for', 'inspection-' + name);
    label.textContent = labelText;
    input.type = type;
    input.name = name;
    input.id = 'inspection-' + name;

    if (required) {
        input.required = true;
    }

    if (maxLength) {
        input.maxLength = maxLength;
    }

    if (name === 'anno_rilascio') {
        input.step = '1';
    }

    group.appendChild(label);
    group.appendChild(input);
    form.appendChild(group);
}

function aggiungiAreaTesto(form, name, labelText) {
    const group = document.createElement('div');
    const label = document.createElement('label');
    const textarea = document.createElement('textarea');

    group.className = 'field-group';
    label.setAttribute('for', 'inspection-' + name);
    label.textContent = labelText;
    textarea.name = name;
    textarea.id = 'inspection-' + name;
    textarea.rows = 3;

    group.appendChild(label);
    group.appendChild(textarea);
    form.appendChild(group);
}

function aggiungiCheckbox(form, name, labelText) {
    const group = document.createElement('div');
    const label = document.createElement('label');
    const input = document.createElement('input');

    group.className = 'field-group checkbox-group';
    label.className = 'checkbox-label';
    input.type = 'checkbox';
    input.name = name;
    input.value = '1';
    label.appendChild(input);
    label.appendChild(document.createTextNode(labelText));
    group.appendChild(label);
    form.appendChild(group);
}

function aggiungiInputNascosto(form, name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
}

function aggiornaCampiManuali(form) {
    const selectedHardware = form.querySelector('[name="id_hardware"]').value !== '';
    const manualFields = form.querySelectorAll('[name="nome_modello"], [name="produttore"], [name="anno_rilascio"], [name="descrizione"], [name="curiosita"], [name="prop_fittizio"]');

    manualFields.forEach(function (field) {
        field.disabled = selectedHardware;
    });

    form.querySelector('[name="nome_modello"]').required = !selectedHardware;
    form.querySelector('[name="produttore"]').required = !selectedHardware;
}

// salva il nuovo tag
async function salvaTag(form, submitButton, message, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes) {
    const selectedHardware = form.querySelector('[name="id_hardware"]').value !== '';
    const nomeModello = form.querySelector('[name="nome_modello"]').value.trim();
    const produttore = form.querySelector('[name="produttore"]').value.trim();

    if (!selectedHardware && (nomeModello === '' || produttore === '')) {
        message.textContent = 'Scegli un hardware esistente oppure compila nome modello e produttore.';
        return;
    }

    submitButton.disabled = true;
    message.textContent = 'Salvataggio in corso...';

    try {
        const response = await fetch('api_save_tag.php', {
            method: 'POST',
            body: new FormData(form),
        });
        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Errore durante il salvataggio.');
        }

        const frameId = form.querySelector('[name="id_frame"]').value;

        caricaTag(frameId, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes);
        resetSidebarMeta(sidebarMeta);
        resetSidebarTitle(sidebarTitle);
        aggiornaSidebarVotes(sidebarVotes, '', 0, 0);
        mostraMessaggioSidebar(result.message || 'Tag salvato.');
    } catch (error) {
        message.textContent = error.message || 'Errore durante il salvataggio.';
    } finally {
        submitButton.disabled = false;
    }
}

function aggiornaFrameAttivo(timelineItem) {
    const activeItem = document.querySelector('.timeline-item--active');

    if (activeItem) {
        activeItem.classList.remove('timeline-item--active');
    }

    timelineItem.classList.add('timeline-item--active');
}

// disegna i tag sul frame
function disegnaTag(tags, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes) {
    svuotaElemento(tagLayer);

    if (!tags || tags.length === 0) {
        resetSidebarMeta(sidebarMeta);
        resetSidebarTitle(sidebarTitle);
        aggiornaSidebarVotes(sidebarVotes, '', 0, 0);
        mostraMessaggioSidebar('Nessun hardware taggato in questo frame.');
        return;
    }

    tags.forEach(function (tag) {
        const hardware = preparaDatiHardware(tag);
        const pulseTag = document.createElement('button');

        pulseTag.type = 'button';
        pulseTag.className = 'pulse-tag';
        pulseTag.style.position = 'absolute';
        pulseTag.style.left = Number(tag.coord_x) + '%';
        pulseTag.style.top = Number(tag.coord_y) + '%';
        pulseTag.style.transform = 'translate(-50%, -50%)';
        pulseTag.setAttribute('aria-label', 'Mostra dettagli ' + hardware.nome_modello);

        pulseTag.addEventListener('click', function () {
            mostraDettagliHardware(hardware, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes);
        });

        tagLayer.appendChild(pulseTag);
    });
}

function preparaDatiHardware(tag) {
    const hardware = tag.hardware || {};

    return {
        nome_modello:   tag.nome_modello   || hardware.nome_modello   || 'Hardware senza nome',
        produttore:     tag.produttore     || hardware.produttore     || '',
        anno_rilascio:  tag.anno_rilascio  || hardware.anno_rilascio  || '',
        descrizione:    tag.descrizione    || hardware.descrizione    || '',
        curiosita:      tag.curiosita      || hardware.curiosita      || '',
        prop_fittizio:  tag.prop_fittizio  !== undefined ? tag.prop_fittizio  : (hardware.prop_fittizio !== undefined ? hardware.prop_fittizio : false),
        id_tag:         tag.id_tag         || '',
        upvotes:        typeof tag.upvotes   === 'number' ? tag.upvotes   : 0,
        downvotes:      typeof tag.downvotes === 'number' ? tag.downvotes : 0,
        autore_username: tag.autore && tag.autore.username ? tag.autore.username : '',
        creato_il:      tag.creato_il || '',
    };
}

// mostra dettagli hardware
function mostraDettagliHardware(hardware, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes) {
    svuotaElemento(sidebarContent);
    aggiornaSidebarMeta(sidebarMeta, hardware.autore_username, hardware.creato_il);
    aggiornaSidebarTitle(sidebarTitle, hardware.nome_modello);
    aggiornaSidebarVotes(sidebarVotes, hardware.id_tag, hardware.upvotes, hardware.downvotes);

    if (hardware.prop_fittizio) {
        aggiungiDettaglio(sidebarContent, 'Tipologia', 'Prop fittizio');
    } else {
        aggiungiDettaglio(sidebarContent, 'Tipologia', 'Hardware reale');
    }

    aggiungiDettaglio(sidebarContent, 'Produttore', hardware.produttore);
    aggiungiDettaglio(sidebarContent, 'Anno produzione', hardware.anno_rilascio);
    aggiungiParagrafo(sidebarContent, hardware.descrizione);

    if (hardware.curiosita) {
        aggiungiDettaglio(sidebarContent, 'Curiosità', hardware.curiosita);
    }
}

function aggiornaSidebarMeta(sidebarMeta, username, createdAt) {
    svuotaElemento(sidebarMeta);

    const label = document.createElement('span');
    label.className = 'hardware-sidebar-meta__label';
    label.textContent = username ? 'Tag di ' + username : 'Autore non disponibile';
    sidebarMeta.appendChild(label);

    const dateText = formatDate(createdAt);

    if (dateText) {
        const date = document.createElement('span');
        date.className = 'hardware-sidebar-meta__date';
        date.textContent = dateText;
        sidebarMeta.appendChild(date);
    }
}

function resetSidebarMeta(sidebarMeta) {
    svuotaElemento(sidebarMeta);

    const label = document.createElement('span');
    label.className = 'hardware-sidebar-meta__label';
    label.textContent = 'Seleziona un tag';
    sidebarMeta.appendChild(label);
}

function aggiornaSidebarTitle(sidebarTitle, value) {
    sidebarTitle.textContent = value || 'Prop';
}

function resetSidebarTitle(sidebarTitle) {
    sidebarTitle.textContent = 'Prop';
}

function aggiornaSidebarVotes(sidebarVotes, idTag, upvotes, downvotes) {
    aggiornaVotiContenuto(sidebarVotes, 'tag', idTag, upvotes, downvotes);

    sidebarVotes.querySelectorAll('[data-vote]').forEach(function (button) {
        button.disabled = sidebarVotes.dataset.canVote !== '1' || !idTag;
    });
}

function formatDate(value) {
    if (!value) {
        return '';
    }

    const date = new Date(value.replace(' ', 'T'));

    if (Number.isNaN(date.getTime())) {
        return '';
    }

    return date.toLocaleDateString('it-IT');
}

function aggiungiDettaglio(container, label, value) {
    if (!value) {
        return;
    }

    const paragraph = document.createElement('p');
    const strong = document.createElement('strong');

    strong.textContent = label + ': ';
    paragraph.appendChild(strong);
    paragraph.appendChild(document.createTextNode(value));
    container.appendChild(paragraph);
}

function aggiungiParagrafo(container, text) {
    if (!text) {
        return;
    }

    const paragraph = document.createElement('p');
    paragraph.textContent = text;
    container.appendChild(paragraph);
}

function mostraMessaggioSidebar(message) {
    const sidebarContent = document.getElementById('sidebar-content');

    if (!sidebarContent) {
        return;
    }

    svuotaElemento(sidebarContent);
    aggiungiParagrafo(sidebarContent, message);
}

function svuotaElemento(element) {
    while (element.firstChild) {
        element.removeChild(element.firstChild);
    }
}
