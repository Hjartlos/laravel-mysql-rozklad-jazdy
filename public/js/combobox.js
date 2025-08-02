function initCombobox(inputId, hiddenId, dropdownId, itemsSelector) {
    const input = document.getElementById(inputId);
    const hidden = document.getElementById(hiddenId);
    const dropdown = document.getElementById(dropdownId);
    const items = document.querySelectorAll(itemsSelector);
    let typingTimer;

    input.addEventListener('input', function() {
        clearTimeout(typingTimer);

        typingTimer = setTimeout(() => {
            const query = this.value.toLowerCase().trim();
            let hasMatches = false;

            items.forEach(item => {
                const stopName = item.textContent.toLowerCase().trim();
                const isMatch = stopName.includes(query);
                item.style.display = isMatch ? 'block' : 'none';
                if (isMatch) hasMatches = true;
            });

            dropdown.style.display = (query && hasMatches) ? 'block' : 'none';
        }, 100);
    });

    input.addEventListener('click', function() {
        if (input.value.trim() !== '') {
            input.dispatchEvent(new Event('input'));
        } else {
            items.forEach(item => item.style.display = 'block');
            dropdown.style.display = 'block';
        }
    });

    items.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();

            const stopId = this.dataset.stopId;
            const stopName = this.dataset.stopName;

            input.value = stopName;
            hidden.value = stopId;
            dropdown.style.display = 'none';
            hidden.dispatchEvent(new Event('change'));
        });
    });

    document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    if (hidden.value) {
        const selectedItem = Array.from(items).find(item =>
            item.dataset.stopId === hidden.value);
        if (selectedItem) {
            input.value = selectedItem.dataset.stopName;
        }
    }
}
