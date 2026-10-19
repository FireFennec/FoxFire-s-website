import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['list', 'addButton'];

    connect() {
        // Add the first ingredient field if the collection is empty.
        if (this.listTarget.children.length === 0) {
            this.add();
        }
    }

    add() {
        const index = Number(this.listTarget.dataset.index);
        const prototype = this.listTarget.dataset.prototype;

        // Generate a unique field name for Symfony CollectionType.
        const html = prototype.replace(/__name__/g, index);

        const template = document.createElement('template');
        template.innerHTML = html.trim();

        const input = template.content.firstElementChild;

        if (!input) {
            return;
        }

        // Match the structure of existing ingredient fields.
        const entry = document.createElement('div');
        entry.classList.add('ingredient-entry');

        const item = document.createElement('div');
        item.classList.add('ingredient-item');

        input.setAttribute('aria-label', 'Ingredient');

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.classList.add('remove-ingredient');
        removeButton.setAttribute('aria-label', 'Remove ingredient');
        removeButton.textContent = 'X';

        // Stimulus will call remove() when this button is clicked.
        removeButton.dataset.action = 'ingredients#remove';

        item.appendChild(input);
        item.appendChild(removeButton);
        entry.appendChild(item);

        this.listTarget.appendChild(entry);
        this.listTarget.dataset.index = index + 1;

        input.focus();
    }

    remove(event) {
        const entry = event.currentTarget.closest('.ingredient-entry');

        if (!entry) {
            return;
        }

        entry.remove();
        this.addButtonTarget.focus();
    }
}
