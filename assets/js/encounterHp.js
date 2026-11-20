/**
 * Handles damage, healing and automatic encounter synchronization.
 */

const encounterHpSyncInterval = 2000;

let encounterHpSyncTimer = null;
let encounterHpSyncController = null;
let encounterStructure = null;

/**
 * Updates the HP bar and displayed HP values.
 */
function updateCombatantHp(combatantCard, currentHp, maxHp) {
    const hpBar = combatantCard.querySelector('.encounter-hp-bar');
    const hpFill = combatantCard.querySelector('.encounter-hp-fill');
    const hpText = combatantCard.querySelector('.encounter-hp-text');

    if (!hpBar || !hpFill || !hpText) {
        return;
    }

    const hpPercentage = maxHp > 0
        ? Math.max(0, Math.min(100, currentHp / maxHp * 100))
        : 0;

    // Match the HP state used by the Twig template.
    const hpState = hpPercentage <= 0
        ? 'defeated'
        : hpPercentage <= 25
            ? 'critical'
            : hpPercentage <= 50
                ? 'wounded'
                : 'healthy';

    hpFill.style.width = `${hpPercentage}%`;
    hpBar.dataset.hpState = hpState;

    hpBar.setAttribute('aria-valuenow', String(currentHp));
    hpBar.setAttribute('aria-valuemax', String(maxHp));

    hpText.textContent = `${currentHp} / ${maxHp} HP`;
}

/**
 * Creates a comparison key excluding current HP.
 */
function getEncounterStructure(combatants) {
    return JSON.stringify(
        [...combatants]
            .sort((a, b) => Number(a.id) - Number(b.id))
            .map((combatant) => ({
                id: combatant.id,
                name: combatant.name,
                side: combatant.side,
                initiative: combatant.initiative,
                maxHp: combatant.maxHp
            }))
    );
}

/**
 * Saves the state of an HP input before replacing its card.
 */
function saveCombatantInput(card) {
    const input = card.querySelector('.encounter-hp-input');

    if (!input) {
        return null;
    }

    return {
        value: input.value,
        focused: document.activeElement === input,
        selectionStart: input.selectionStart,
        selectionEnd: input.selectionEnd
    };
}

/**
 * Restores an HP input after its card has been replaced.
 */
function restoreCombatantInput(card, state) {
    if (!state) {
        return;
    }

    const input = card.querySelector('.encounter-hp-input');

    if (!input) {
        return;
    }

    input.value = state.value;

    if (state.focused) {
        input.focus({ preventScroll: true });

        if (state.selectionStart !== null && state.selectionEnd !== null) {
            try {
                input.setSelectionRange(
                    state.selectionStart,
                    state.selectionEnd
                );
            } catch (error) {
                // Some input types do not support text selection.
            }
        }
    }
}

/**
 * Registers Damage and Heal handlers on combatant cards.
 */
function initializeEncounterHpForms() {
    document.querySelectorAll('.encounter-hp-form').forEach((form) => {
        if (form.dataset.hpInitialized === 'true') {
            return;
        }

        form.dataset.hpInitialized = 'true';

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const combatantCard = form.closest('.encounter-combatant');
            const amountInput = form.querySelector('.encounter-hp-input');
            const buttons = form.querySelectorAll('.encounter-action-button');
            const errorElement = combatantCard.querySelector('.encounter-hp-error');

            const amount = Number(amountInput.value);
            const action = event.submitter?.value;

            errorElement.hidden = true;
            errorElement.textContent = '';

            if (!Number.isSafeInteger(amount) || amount <= 0) {
                errorElement.textContent = 'Enter a positive whole number.';
                errorElement.hidden = false;
                return;
            }

            if (!['damage', 'heal'].includes(action)) {
                return;
            }

            combatantCard.dataset.hpUpdating = 'true';

            buttons.forEach((button) => {
                button.disabled = true;
            });

            try {
                const formData = new FormData(form);

                formData.set('amount', String(amount));
                formData.set('action', action);

                const response = await fetch(form.getAttribute('action'), {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || 'Unable to update HP.');
                }

                updateCombatantHp(
                    combatantCard,
                    data.currentHp,
                    data.maxHp
                );

                amountInput.value = '';

            } catch (error) {
                errorElement.textContent =
                    error.message || 'Unable to update HP.';

                errorElement.hidden = false;
            } finally {
                delete combatantCard.dataset.hpUpdating;

                buttons.forEach((button) => {
                    button.disabled = false;
                });
            }
        });
    });
}

/**
 * Updates only cards whose structure has changed.
 */
function updateCombatantList(container, html, combatants) {
    const template = document.createElement('template');
    template.innerHTML = html;

    const incomingCards = Array.from(
        template.content.querySelectorAll('.encounter-combatant')
    );

    const existingCards = new Map(
        Array.from(container.querySelectorAll('.encounter-combatant'))
            .map((card) => [card.dataset.combatantId, card])
    );

    const combatantsById = new Map(
        combatants.map((combatant) => [
            String(combatant.id),
            combatant
        ])
    );

    const orderedCards = [];

    for (const incomingCard of incomingCards) {
        const id = incomingCard.dataset.combatantId;
        const existingCard = existingCards.get(id);
        const combatant = combatantsById.get(id);

        if (!existingCard || !combatant) {
            orderedCards.push(incomingCard);
            continue;
        }

        const existingName = existingCard.querySelector('h3')?.textContent.trim();
        const incomingName = incomingCard.querySelector('h3')?.textContent.trim();

        const existingInitiative = existingCard.querySelector(
            '.encounter-initiative'
        )?.textContent.trim();

        const incomingInitiative = incomingCard.querySelector(
            '.encounter-initiative'
        )?.textContent.trim();

        const existingMaxHp = existingCard.querySelector(
            '.encounter-hp-bar'
        )?.getAttribute('aria-valuemax');

        const incomingMaxHp = incomingCard.querySelector(
            '.encounter-hp-bar'
        )?.getAttribute('aria-valuemax');

        const hasChanged =
            existingName !== incomingName ||
            existingInitiative !== incomingInitiative ||
            existingMaxHp !== incomingMaxHp;

        if (!hasChanged) {
            orderedCards.push(existingCard);
            continue;
        }

        // Never replace a card during an active HP request.
        if (existingCard.dataset.hpUpdating === 'true') {
            orderedCards.push(existingCard);
            continue;
        }

        const inputState = saveCombatantInput(existingCard);

        restoreCombatantInput(incomingCard, inputState);

        orderedCards.push(incomingCard);
    }

    // Move existing DOM elements without recreating their listeners.
    // New and modified cards receive their handlers afterwards.
    if (orderedCards.length > 0) {
        container.replaceChildren(...orderedCards);
    } else {
        container.replaceChildren(template.content);
    }
}

/**
 * Loads HTML when the encounter structure changes.
 */
async function synchronizeEncounterStructure(controller, combatants) {
    const response = await fetch('/dnd/encounter/combatants-html', {
        method: 'GET',
        headers: {
            'Accept': 'application/json'
        },
        cache: 'no-store',
        signal: controller.signal
    });

    if (!response.ok) {
        throw new Error('Unable to synchronize encounter combatants.');
    }

    const data = await response.json();

    if (controller.signal.aborted || !document.querySelector('.encounter-page')) {
        return false;
    }

    const alliesContainer = document.querySelector('#encounter-allies');
    const enemiesContainer = document.querySelector('#encounter-enemies');

    if (!alliesContainer || !enemiesContainer) {
        return false;
    }

    // Defer structural changes while any HP submission is running.
    if (document.querySelector('.encounter-combatant[data-hp-updating="true"]')) {
        return false;
    }

    updateCombatantList(alliesContainer, data.allies, combatants);
    updateCombatantList(enemiesContainer, data.enemies, combatants);

    initializeEncounterHpForms();

    return true;
}

/**
 * Fetches the latest encounter state from Symfony.
 */
async function synchronizeEncounterHp() {
    if (!document.querySelector('.encounter-page')) {
        return;
    }

    if (encounterHpSyncController) {
        return;
    }

    const controller = new AbortController();
    encounterHpSyncController = controller;

    try {
        const response = await fetch('/dnd/encounter/hp-status', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            cache: 'no-store',
            signal: controller.signal
        });

        if (!response.ok) {
            throw new Error('Unable to synchronize encounter HP.');
        }

        const data = await response.json();

        if (controller.signal.aborted || !document.querySelector('.encounter-page')) {
            return;
        }

        const newStructure = getEncounterStructure(data.combatants);

        const displayedIds = Array.from(
            document.querySelectorAll('.encounter-combatant')
        ).map((card) => Number(card.dataset.combatantId)).sort((a, b) => a - b);

        const serverIds = data.combatants
            .map((combatant) => Number(combatant.id))
            .sort((a, b) => a - b);

        const idsChanged =
            JSON.stringify(displayedIds) !== JSON.stringify(serverIds);

        if (idsChanged ||
            (encounterStructure !== null && encounterStructure !== newStructure)
        ) {
            const updated = await synchronizeEncounterStructure(
                controller,
                data.combatants
            );

            if (!updated) {
                return;
            }
        }

        encounterStructure = newStructure;

        for (const combatant of data.combatants) {
            const combatantCard = document.querySelector(
                `.encounter-combatant[data-combatant-id="${combatant.id}"]`
            );

            if (!combatantCard || combatantCard.dataset.hpUpdating === 'true') {
                continue;
            }

            updateCombatantHp(
                combatantCard,
                combatant.currentHp,
                combatant.maxHp
            );
        }

    } catch (error) {
        if (error.name !== 'AbortError') {
            console.error('Encounter synchronization failed:', error);
        }
    } finally {
        if (encounterHpSyncController === controller) {
            encounterHpSyncController = null;
        }
    }
}

/**
 * Starts polling every two seconds.
 */
function startEncounterHpSync() {
    stopEncounterHpSync();

    if (!document.querySelector('.encounter-page')) {
        return;
    }

    synchronizeEncounterHp();

    encounterHpSyncTimer = window.setInterval(() => {
        if (!document.hidden) {
            synchronizeEncounterHp();
        }
    }, encounterHpSyncInterval);
}

/**
 * Stops polling and cancels unfinished requests.
 */
function stopEncounterHpSync() {
    if (encounterHpSyncTimer !== null) {
        window.clearInterval(encounterHpSyncTimer);
        encounterHpSyncTimer = null;
    }

    if (encounterHpSyncController) {
        encounterHpSyncController.abort();
        encounterHpSyncController = null;
    }

    encounterStructure = null;
}

/**
 * Initializes the tracker after page navigation.
 */
function initializeEncounterHp() {
    initializeEncounterHpForms();
    startEncounterHpSync();
}

document.addEventListener('turbo:load', initializeEncounterHp);

document.addEventListener('turbo:before-cache', stopEncounterHpSync);
document.addEventListener('turbo:before-render', stopEncounterHpSync);

document.addEventListener('visibilitychange', () => {
    if (!document.hidden && document.querySelector('.encounter-page')) {
        synchronizeEncounterHp();
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeEncounterHp);
} else {
    initializeEncounterHp();
}
