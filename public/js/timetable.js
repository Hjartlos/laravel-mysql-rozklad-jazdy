document.addEventListener('DOMContentLoaded', function() {
    const lineSelect = document.getElementById('line_id');
    const daySelect = document.getElementById('day_id');
    const stopSelect = document.getElementById('stop_select');
    const departureTimeInput = document.getElementById('departure_time');
    const addStopBtn = document.getElementById('addStopBtn');
    const stopsList = document.getElementById('stops-list');
    const noStopsAlert = document.getElementById('no-stops-alert');
    const saveBtn = document.getElementById('saveBtn');
    const stopsCounter = document.getElementById('stops-counter');
    const paginationContainer = document.getElementById('pagination-container');
    const paginationElement = document.getElementById('pagination');
    const timetableForm = document.getElementById('timetableForm') || saveBtn.closest('form');

    let stopCounter = window.initialStopCount || 0;
    let stopsPerPage = 5;
    let currentPage = 1;
    let allStopItems = [];
    let pageAssignments = {};

    function loadStopsForLine(lineId) {
        if (!lineId) {
            stopSelect.innerHTML = '<option value="">Najpierw wybierz linię</option>';
            stopSelect.disabled = true;
            return;
        }

        fetch(`/admin/timetable/line/${lineId}/stops`)
            .then(response => response.json())
            .then(stops => {
                stopSelect.innerHTML = '<option value="">Wybierz przystanek</option>';
                stops.forEach(stop => {
                    const option = document.createElement('option');
                    option.value = stop.stop_id;
                    option.dataset.name = stop.stop_name;
                    option.textContent = `${stop.stop_name} (nr ${stop.pivot.sequence})`;
                    stopSelect.appendChild(option);
                });
                stopSelect.disabled = false;

                stopsPerPage = stops.length > 0 ? stops.length : 5;

                currentPage = 1;
                reassignItemsToPages();
                updatePagination();
            })
            .catch(error => {
                console.error('Błąd pobierania przystanków:', error);
                alert('Wystąpił błąd podczas pobierania przystanków');
            });
    }

    function addStop() {
        const stopId = stopSelect.value;
        if (!stopId) {
            alert('Wybierz przystanek');
            return;
        }

        const stopName = stopSelect.options[stopSelect.selectedIndex].dataset.name;
        const departureTime = departureTimeInput.value;

        if (!departureTime) {
            alert('Podaj czas odjazdu');
            return;
        }

        let stopExists = false;
        document.querySelectorAll('input[name^="times"][name$="[stop_id]"]').forEach(input => {
            if (input.value == stopId) {
                stopExists = true;
            }
        });

        if (stopExists) {
            alert(`Przystanek ${stopName} już istnieje w rozkładzie.`);
            return;
        }

        const index = document.querySelectorAll('input[name^="times"][name$="[stop_id]"]').length;

        const stopItem = document.createElement('div');
        stopItem.className = 'time-badge-container mb-2';
        stopItem.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="time-badge bg-light me-2">${departureTime}</div>
                <div class="fw-bold">${stopName}</div>
                <button type="button" class="btn btn-sm btn-danger ms-auto remove-stop">
                    <i class="fas fa-times"></i>
                </button>
                <input type="hidden" name="times[${index}][stop_id]" value="${stopId}">
                <input type="hidden" name="times[${index}][departure_time]" value="${departureTime}">
                ${window.isEditMode ? `<input type="hidden" name="times[${index}][time_id]" value="">` : ''}
            </div>
        `;

        allStopItems.push(stopItem);
        assignItemToPage(stopItem, currentPage);

        stopCounter++;
        stopsCounter.textContent = stopCounter + ' przystanków';

        stopItem.querySelector('.remove-stop').addEventListener('click', function() {
            removeStopItem(stopItem);
        });

        stopSelect.value = '';
        departureTimeInput.value = '';

        updatePagination();
        updateNoStopsAlert();
        updateSaveButton();
    }

    function removeStopItem(stopItem) {
        const index = allStopItems.indexOf(stopItem);
        if (index > -1) {
            const page = getItemPage(stopItem);

            delete pageAssignments[stopItem.uniqueId];

            allStopItems.splice(index, 1);

            stopCounter--;
            stopsCounter.textContent = stopCounter + ' przystanków';

            const currentPageItems = getItemsForPage(currentPage);
            if (currentPageItems.length === 0 && currentPage > 1) {
                currentPage--;
            }

            updatePagination();
            updateNoStopsAlert();
            updateSaveButton();
        }
    }

    function assignUniqueId(element) {
        if (!element.uniqueId) {
            element.uniqueId = 'stop_' + Math.random().toString(36).substr(2, 9);
        }
        return element.uniqueId;
    }

    function assignItemToPage(item, page) {
        const id = assignUniqueId(item);
        pageAssignments[id] = page;
    }

    function getItemPage(item) {
        return pageAssignments[item.uniqueId] || 1;
    }

    function getItemsForPage(page) {
        return allStopItems.filter(item => getItemPage(item) === page);
    }

    function reassignItemsToPages() {
        pageAssignments = {};

        for (let i = 0; i < allStopItems.length; i++) {
            const page = Math.floor(i / stopsPerPage) + 1;
            assignItemToPage(allStopItems[i], page);
        }
    }

    function updatePagination() {
        if (!paginationContainer) {
            if (allStopItems.length === 0) {
                stopsList.innerHTML = '';
            } else {
                renderAllItemsWithoutPagination();
            }
            return;
        }

        if (allStopItems.length === 0) {
            paginationContainer.style.display = 'none';
            stopsList.innerHTML = '';
            return;
        }

        const pageCount = Math.max(1, Math.ceil(allStopItems.length / stopsPerPage));

        if (pageCount <= 1) {
            paginationContainer.style.display = 'none';
            renderItemsForPage(1);
            return;
        }

        if (currentPage > pageCount) {
            currentPage = pageCount;
        }

        paginationContainer.style.display = 'flex';

        paginationElement.innerHTML = '';

        renderItemsForPage(currentPage);

        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#">&laquo;</a>`;
        prevLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage > 1) {
                currentPage--;
                updatePagination();
            }
        });
        paginationElement.appendChild(prevLi);

        for (let i = 1; i <= pageCount; i++) {
            const li = document.createElement('li');
            li.className = `page-item ${currentPage === i ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
            li.addEventListener('click', function(e) {
                e.preventDefault();
                currentPage = i;
                updatePagination();
            });
            paginationElement.appendChild(li);
        }

        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage === pageCount ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#">&raquo;</a>`;
        nextLi.addEventListener('click', function(e) {
            e.preventDefault();
            if (currentPage < pageCount) {
                currentPage++;
                updatePagination();
            }
        });
        paginationElement.appendChild(nextLi);

        renderItemsForPage(currentPage);
    }

    function renderAllItemsWithoutPagination() {
        stopsList.innerHTML = '';
        for (const item of allStopItems) {
            stopsList.appendChild(item);
        }
    }

    function renderItemsForPage(page) {
        stopsList.innerHTML = '';

        const pageItems = getItemsForPage(page);

        for (const item of pageItems) {
            stopsList.appendChild(item);
        }
    }

    function updateNoStopsAlert() {
        if (allStopItems.length > 0) {
            noStopsAlert.style.display = 'none';
        } else {
            noStopsAlert.style.display = 'block';
        }
    }

    function updateSaveButton() {
        const lineSelected = lineSelect.value !== '';
        const daySelected = daySelect ? daySelect.value !== '' : true;
        const hasStops = allStopItems.length > 0;

        saveBtn.disabled = !(lineSelected && daySelected && hasStops);
    }

    function initializeExistingStops() {
        if (window.existingStops && window.existingStops.length > 0) {
            window.existingStops.forEach((stop, index) => {
                const stopItem = document.createElement('div');
                stopItem.className = 'time-badge-container mb-2';
                stopItem.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="time-badge bg-light me-2">${stop.departure_time}</div>
                        <div class="fw-bold">${stop.stop_name}</div>
                        <button type="button" class="btn btn-sm btn-danger ms-auto remove-stop">
                            <i class="fas fa-times"></i>
                        </button>
                        <input type="hidden" name="times[${index}][stop_id]" value="${stop.stop_id}">
                        <input type="hidden" name="times[${index}][departure_time]" value="${stop.departure_time}">
                        <input type="hidden" name="times[${index}][time_id]" value="${stop.time_id}">
                    </div>
                `;

                stopItem.querySelector('.remove-stop').addEventListener('click', function() {
                    removeStopItem(stopItem);
                });

                allStopItems.push(stopItem);
            });

            reassignItemsToPages();
            updatePagination();
            updateNoStopsAlert();
        }
    }

    if (lineSelect) {
        lineSelect.addEventListener('change', function() {
            loadStopsForLine(this.value);
        });
    }

    if (addStopBtn) {
        addStopBtn.addEventListener('click', addStop);
    }

    if (daySelect) {
        daySelect.addEventListener('change', updateSaveButton);
    }

    if (timetableForm) {
        timetableForm.addEventListener('submit', function(e) {
            if (allStopItems.length === 0) {
                e.preventDefault();
                alert('Dodaj przynajmniej jeden przystanek z czasem odjazdu!');
            }
        });
    }

    if (window.isEditMode) {
        loadStopsForLine(lineSelect.value);
        initializeExistingStops();
    } else if (lineSelect.value) {
        loadStopsForLine(lineSelect.value);
    }

    updateNoStopsAlert();
    updateSaveButton();
});
