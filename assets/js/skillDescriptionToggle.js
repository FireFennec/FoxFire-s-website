document.addEventListener('click', (event) => {
    const button = event.target.closest('.skill-description-toggle');

    if (!button) {
        return;
    }

    const container = button.closest('.skill-description-container');

    const shortDescription = container.querySelector('.skill-description-short');
    const longDescription = container.querySelector('.skill-description-long');

    const isExpanded = button.getAttribute('aria-expanded') === 'true';

    shortDescription.hidden = !isExpanded;
    longDescription.hidden = isExpanded;

    button.setAttribute('aria-expanded', String(!isExpanded));
    button.textContent = isExpanded ? 'Show details' : 'Show summary';
});
