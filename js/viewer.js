document.addEventListener('DOMContentLoaded', function () {
    const frameImage = document.getElementById('frame-image');
    const tagLayer = document.getElementById('tag-layer');
    const sidebarContent = document.getElementById('sidebar-content');
    const timelineList = document.querySelector('.timeline-list');
    const frameTimestamp = document.getElementById('frame-timestamp');
    const frameDescription = document.getElementById('frame-description');

    if (!frameImage || !tagLayer || !sidebarContent) {
        console.error('Elementi del viewer non trovati.');
        return;
    }

    const frameId = frameImage.dataset.frameId;

    if (!frameId || !/^[1-9][0-9]*$/.test(frameId)) {
        mostraMessaggioSidebar('Frame non valido.');
        console.error('data-frame-id mancante o non valido sull\'immagine principale.');
        return;
    }

    caricaTag(frameId, tagLayer, sidebarContent);

    if (timelineList && frameTimestamp && frameDescription) {
        preparaTimeline(timelineList, frameImage, tagLayer, sidebarContent, frameTimestamp, frameDescription);
    }
});

function caricaTag(frameId, tagLayer, sidebarContent) {
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

            disegnaTag(result.data, tagLayer, sidebarContent);
        })
        .catch(function (error) {
            console.error(error);
            svuotaElemento(tagLayer);
            mostraMessaggioSidebar('Impossibile caricare i tag del frame.');
        });
}

function preparaTimeline(timelineList, frameImage, tagLayer, sidebarContent, frameTimestamp, frameDescription) {
    timelineList.addEventListener('click', function (event) {
        const timelineItem = event.target.closest('.timeline-item');

        if (!timelineItem) {
            return;
        }

        cambiaFrame(timelineItem, frameImage, tagLayer, sidebarContent, frameTimestamp, frameDescription);
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
        cambiaFrame(timelineItem, frameImage, tagLayer, sidebarContent, frameTimestamp, frameDescription);
    });
}

function cambiaFrame(timelineItem, frameImage, tagLayer, sidebarContent, frameTimestamp, frameDescription) {
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

    aggiornaFrameAttivo(timelineItem);
    svuotaElemento(tagLayer);
    mostraMessaggioSidebar('Seleziona un Pulse-Tag per vedere i dettagli hardware.');
    caricaTag(frameId, tagLayer, sidebarContent);
}

function aggiornaFrameAttivo(timelineItem) {
    const activeItem = document.querySelector('.timeline-item--active');

    if (activeItem) {
        activeItem.classList.remove('timeline-item--active');
    }

    timelineItem.classList.add('timeline-item--active');
}

function disegnaTag(tags, tagLayer, sidebarContent) {
    svuotaElemento(tagLayer);

    if (!tags || tags.length === 0) {
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
            mostraDettagliHardware(hardware, sidebarContent);
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
    };
}

function mostraDettagliHardware(hardware, sidebarContent) {
    svuotaElemento(sidebarContent);

    const title = document.createElement('h3');
    title.textContent = hardware.nome_modello;
    sidebarContent.appendChild(title);

    aggiungiDettaglio(sidebarContent, 'Produttore', hardware.produttore);
    aggiungiDettaglio(sidebarContent, 'Anno rilascio', hardware.anno_rilascio);
    aggiungiParagrafo(sidebarContent, hardware.descrizione);

    if (hardware.curiosita) {
        aggiungiDettaglio(sidebarContent, 'Curiosità', hardware.curiosita);
    }
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
