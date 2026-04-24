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

    if (timelineList && frameTimestamp && frameDescription && frameAuthor) {
        preparaTimeline(timelineList, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor);
    }
});

function caricaTag(frameId, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes) {
    fetch('php/api_get_tags.php?id_frame=' + encodeURIComponent(frameId))
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Errore HTTP ' + response.status);
            }

            return response.json();
        })
        .then(function (result) {
            if (!result.success) {
                throw new Error(result.message || 'Errore nel caricamento dei tag.');
            }

            disegnaTag(result.data, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes);
        })
        .catch(function (error) {
            console.error(error);
            svuotaElemento(tagLayer);
            resetSidebarMeta(sidebarMeta);
            resetSidebarTitle(sidebarTitle);
            aggiornaSidebarVotes(sidebarVotes, 0, 0);
            mostraMessaggioSidebar('Impossibile caricare i tag del frame.');
        });
}

function preparaTimeline(timelineList, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor) {
    timelineList.addEventListener('click', function (event) {
        const timelineItem = event.target.closest('.timeline-item');

        if (!timelineItem) {
            return;
        }

        cambiaFrame(timelineItem, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor);
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
        cambiaFrame(timelineItem, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor);
    });
}

function cambiaFrame(timelineItem, frameImage, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes, frameTimestamp, frameDescription, frameAuthor) {
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

    aggiornaFrameAttivo(timelineItem);
    svuotaElemento(tagLayer);
    resetSidebarMeta(sidebarMeta);
    resetSidebarTitle(sidebarTitle);
    aggiornaSidebarVotes(sidebarVotes, 0, 0);
    mostraMessaggioSidebar('Seleziona un Pulse-Tag per vedere i dettagli hardware.');
    caricaTag(frameId, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes);
}

function aggiornaFrameAttivo(timelineItem) {
    const activeItem = document.querySelector('.timeline-item--active');

    if (activeItem) {
        activeItem.classList.remove('timeline-item--active');
    }

    timelineItem.classList.add('timeline-item--active');
}

function disegnaTag(tags, tagLayer, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes) {
    svuotaElemento(tagLayer);

    if (!tags || tags.length === 0) {
        resetSidebarMeta(sidebarMeta);
        resetSidebarTitle(sidebarTitle);
        aggiornaSidebarVotes(sidebarVotes, 0, 0);
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
    // Supporta sia il formato flat dell'API sia il formato con oggetto hardware.
    const hardware = tag.hardware || {};

    return {
        nome_modello: tag.nome_modello || hardware.nome_modello || 'Hardware senza nome',
        produttore: tag.produttore || hardware.produttore || '',
        anno_rilascio: tag.anno_rilascio || hardware.anno_rilascio || '',
        descrizione: tag.descrizione || hardware.descrizione || '',
        curiosita: tag.curiosita || hardware.curiosita || '',
        upvotes: typeof tag.upvotes === 'number' ? tag.upvotes : 0,
        downvotes: typeof tag.downvotes === 'number' ? tag.downvotes : 0,
        autore_username: tag.autore && tag.autore.username ? tag.autore.username : '',
        creato_il: tag.creato_il || '',
    };
}

function mostraDettagliHardware(hardware, sidebarContent, sidebarMeta, sidebarTitle, sidebarVotes) {
    svuotaElemento(sidebarContent);
    aggiornaSidebarMeta(sidebarMeta, hardware.autore_username, hardware.creato_il);
    aggiornaSidebarTitle(sidebarTitle, hardware.nome_modello);
    aggiornaSidebarVotes(sidebarVotes, hardware.upvotes, hardware.downvotes);

    aggiungiDettaglio(sidebarContent, 'Produttore', hardware.produttore);
    aggiungiDettaglio(sidebarContent, 'Anno rilascio', hardware.anno_rilascio);
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

function aggiornaSidebarVotes(sidebarVotes, upvotes, downvotes) {
    svuotaElemento(sidebarVotes);
    sidebarVotes.appendChild(creaVoteBadge('up', upvotes));
    sidebarVotes.appendChild(creaVoteBadge('down', downvotes));
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

function creaVoteBadge(type, value) {
    const badge = document.createElement('span');
    const icon = document.createElement('span');
    const count = document.createElement('span');

    badge.className = 'hardware-vote-badge hardware-vote-badge--' + type;
    badge.setAttribute('aria-label', (type === 'up' ? 'Upvote: ' : 'Downvote: ') + value);
    icon.className = 'hardware-vote-badge__icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = type === 'up' ? '▲' : '▼';

    count.className = 'hardware-vote-badge__count';
    count.textContent = String(value);

    badge.appendChild(icon);
    badge.appendChild(count);

    return badge;
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
