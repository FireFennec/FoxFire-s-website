/**
 * Initializes the personal shopping list and its asynchronous actions.
 */
function initShoppingList() {
    const addForm = document.getElementById('shopping-list-add-form');
    const items = document.getElementById('shopping-list-items');
    const emptyMessage = document.getElementById('shopping-list-empty');
    const errorMessage = document.getElementById('shopping-list-error');

    const clearForm = document.getElementById('shopping-list-clear-form');
    const clearButton = document.getElementById('shopping-list-clear-button');
    const clearDialog = document.getElementById('shopping-list-clear-dialog');
    const clearCancel = document.getElementById('shopping-list-clear-cancel');
    const clearConfirm = document.getElementById('shopping-list-clear-confirm');

    if (
        !addForm ||
        !items ||
        !emptyMessage ||
        !errorMessage ||
        !clearForm ||
        !clearButton ||
        !clearDialog ||
        !clearCancel ||
        !clearConfirm
    ) {
        return;
    }

    // Prevent duplicate event listeners.
    if (addForm.dataset.initialized === 'true') {
        return;
    }

    addForm.dataset.initialized = 'true';

    let pendingRequests = 0;
    let isClearing = false;

    /**
     * Displays an error message.
     */
    function showError(message) {
        errorMessage.textContent = message;
        errorMessage.hidden = false;
    }

    /**
     * Clears the current error message.
     */
    function clearError() {
        errorMessage.textContent = '';
        errorMessage.hidden = true;
    }

    /**
     * Updates the visibility of the empty list message
     * and the Clear All form.
     */
    function updateEmptyState() {
        const isEmpty = items.children.length === 0;

        emptyMessage.hidden = !isEmpty;
        clearForm.hidden = isEmpty;
    }

    /**
     * Updates the shopping progress based on purchased items.
     */
    function updateProgress() {
        const progressBar = document.getElementById(
            'shopping-list-progress-bar'
        );

        const progressCount = document.getElementById(
            'shopping-list-progress-count'
        );

        if (!progressBar || !progressCount) {
            return;
        }

        const totalItems = items.querySelectorAll(
            '.shopping-list-item'
        ).length;

        const purchasedItems = items.querySelectorAll(
            '.shopping-list-item.is-purchased'
        ).length;

        const progress = totalItems > 0
            ? Math.round(purchasedItems / totalItems * 100)
            : 0;

        progressBar.value = progress;
        progressCount.textContent = `${purchasedItems}/${totalItems}`;
    }

    /**
     * Sends an asynchronous form request to the server.
     */
    async function sendRequest(form) {
        pendingRequests++;
        clearButton.disabled = true;

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json().catch(() => null);

            if (!response.ok || !data?.success) {
                throw new Error(
                    data?.error || 'Something went wrong. Please try again.'
                );
            }

            return data;
        } finally {
            pendingRequests--;
            clearButton.disabled = pendingRequests > 0 || isClearing;
        }
    }

    /**
     * Adds a new product without reloading the page.
     */
    addForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (isClearing) {
            return;
        }

        clearError();

        const button = addForm.querySelector('button[type="submit"]');

        if (button.disabled) {
            return;
        }

        button.disabled = true;

        try {
            const data = await sendRequest(addForm);

            items.insertAdjacentHTML('beforeend', data.html);

            addForm.reset();
            updateEmptyState();
            updateProgress();

            document.getElementById('shopping-list-product-name').focus();
        } catch (error) {
            showError(error.message);
        } finally {
            button.disabled = false;
        }
    });

    /**
     * Toggles the purchased status of a product.
     */
    items.addEventListener('change', async (event) => {
        const checkbox = event.target;

        if (!checkbox.matches('.shopping-list-checkbox')) {
            return;
        }

        if (isClearing) {
            checkbox.checked = !checkbox.checked;
            return;
        }

        clearError();

        const form = checkbox.closest('.shopping-list-toggle-form');
        const item = checkbox.closest('.shopping-list-item');
        const previousState = !checkbox.checked;

        checkbox.disabled = true;

        try {
            const data = await sendRequest(form);

            checkbox.checked = data.isPurchased;

            item.classList.toggle(
                'is-purchased',
                data.isPurchased
            );

            updateProgress();
        } catch (error) {
            checkbox.checked = previousState;
            showError(error.message);
        } finally {
            checkbox.disabled = false;
        }
    });

    /**
     * Removes a product without reloading the page.
     */
    items.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!form.matches('.shopping-list-delete-form')) {
            return;
        }

        event.preventDefault();

        if (isClearing) {
            return;
        }

        clearError();

        const item = form.closest('.shopping-list-item');
        const button = form.querySelector('button[type="submit"]');
        const checkbox = item.querySelector('.shopping-list-checkbox');

        if (button.disabled || checkbox.disabled) {
            return;
        }

        button.disabled = true;
        checkbox.disabled = true;

        try {
            await sendRequest(form);

            item.remove();
            updateEmptyState();
            updateProgress();
        } catch (error) {
            showError(error.message);
        } finally {
            button.disabled = false;
            checkbox.disabled = false;
        }
    });

    /**
     * Opens the Clear All confirmation dialog.
     */
    clearButton.addEventListener('click', () => {
        if (
            items.children.length === 0 ||
            pendingRequests > 0 ||
            isClearing ||
            clearDialog.open
        ) {
            return;
        }

        clearError();
        clearDialog.showModal();
    });

    /**
     * Closes the confirmation dialog without removing products.
     */
    clearCancel.addEventListener('click', () => {
        if (!isClearing) {
            clearDialog.close();
        }
    });

    /**
     * Prevents closing the dialog while products are being removed.
     */
    clearDialog.addEventListener('cancel', (event) => {
        if (isClearing) {
            event.preventDefault();
        }
    });

    /**
     * Removes all shopping list items after confirmation.
     */
    clearForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (
            isClearing ||
            pendingRequests > 0 ||
            !clearDialog.open ||
            items.children.length === 0
        ) {
            return;
        }

        clearError();

        isClearing = true;

        clearButton.disabled = true;
        clearConfirm.disabled = true;
        clearCancel.disabled = true;

        try {
            await sendRequest(clearForm);

            items.replaceChildren();

            updateEmptyState();
            updateProgress();

            clearDialog.close();

            document.getElementById('shopping-list-product-name').focus();
        } catch (error) {
            clearDialog.close();
            showError(error.message);
        } finally {
            isClearing = false;

            clearButton.disabled = false;
            clearConfirm.disabled = false;
            clearCancel.disabled = false;
        }
    });

    updateEmptyState();
    updateProgress();
}

// Initialize the shopping list on regular and Turbo page loads.
document.addEventListener('DOMContentLoaded', initShoppingList);
document.addEventListener('turbo:load', initShoppingList);

// Allow initialization after the DOM has already loaded.
if (document.readyState !== 'loading') {
    initShoppingList();
}

// Remove the initialization marker before Turbo caches the page.
document.addEventListener('turbo:before-cache', () => {
    const addForm = document.getElementById('shopping-list-add-form');

    if (addForm) {
        delete addForm.dataset.initialized;
    }
});
