class SightingMap {
    constructor() {
        // create the map from leaflet.js
        this.map = L.map('map', {maxZoom: 18}).setView([53.4808, -2.2426], 12);

        // I optimised the map by using leaflet.js built-in marker clustering functions since there are thousands of markers in the map
        this.markerGroup = L.markerClusterGroup();
        this.map.addLayer(this.markerGroup);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png?{foo}', {
            foo: 'bar',
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 20
        }).addTo(this.map);

        // variables used in the live search
        this.lastRequest = '';
        this.lastDataDisplayed = null;
        this.dbTimer = null;
        this.currentSearchRequest = null;

        // start all the functions
        this.startSearchSystem();
        this.currentUserLocation();
        this.loadData();
        this.submitSighting();
        this.paginationSystem();
        this.updateSearchFilters();
    }

    // gets all the information from markers.php
    loadData(params = "") {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'markers.php?' + params, true);
        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);

                // updates all the map markers, cards and pagination links with the new data
                this.updateMapMarkers(response.mapMarkers);
                this.updateCards(response.cardData);
                this.loadPagination(response.pagination, params);
            } else {
                console.log('error: ' + xhr.status);
            }
        };
        xhr.send();
    }

    // clears the map and adds new markers inside the cluster group to reduce lag
    updateMapMarkers(markers) {
        this.markerGroup.clearLayers();
        const markerList = [];

        // loop through every marker
        markers.forEach(sighting => {
            if (sighting.latitude && sighting.longitude) {
                const isFound = sighting.pet_status.toLowerCase() === 'found';
                const badgeStatus = isFound ? 'bg-success' : 'bg-danger';

                // customise the popup for the current sighting marker in the loop
                const popupText = `
                <div style="min-width: 200px;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="m-0"><strong>${sighting.pet_name}</strong></h6>
                        <span class="badge ${badgeStatus}">${sighting.pet_status}</span>
                    </div>
                    <p class="m-0"><strong>Reported by: </strong>${sighting.user_id}</p>
                    <p class="m-0"><strong>Type: </strong>${sighting.pet_species}, ${sighting.pet_breed}</p>
                    <p class="m-0"><strong>Colour: </strong>${sighting.pet_color}</p>
                    <p class="m-0"><strong>Location: </strong>${sighting.latitude}, ${sighting.longitude}</p>
                    <p class="m-0"><strong>Last seen on: </strong>${sighting.timestamp}</p>
                    <textarea class="form-control mt-2 mb-2" disabled>${sighting.comment}</textarea>
                    <div class="d-grid">
                        <button 
                        class="btn btn-sm btn-primary" 
                        onclick="petMap.showForm(
                            '${sighting.pet_id}', 
                            '${sighting.pet_name}', 
                            ${sighting.latitude}, 
                            ${sighting.longitude})">
                            Report new sighting
                        </button>
                    </div>
                </div>`;

                // add the marker with the coordinates and the popup to the map
                const marker = L.marker([sighting.latitude, sighting.longitude]).bindPopup(popupText);

                markerList.push(marker);
            }
        });

        this.markerGroup.addLayers(markerList);
    }

    // this function clears the old sighting "cards" and adds new ones
    updateCards(cardData) {
        const container = document.getElementById('sightingsContainer');
        if (!container) return;

        container.innerHTML = '';

        // loop through every sighting data and display the sighting card
        cardData.forEach(sighting => {
            const isFound = sighting.pet_status.toLowerCase() === 'found';
            const badgeStatus = isFound ? 'bg-success' : 'bg-danger';

            container.innerHTML += `
            <div class="col">
                <div class="card shadow-sm h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <strong>${sighting.pet_name}</strong>
                        <p class="text-muted my-auto">(ID: ${sighting.id})</p>
                        <span class="badge rounded-pill ${badgeStatus} ms-auto">${sighting.pet_status}</span>
                    </div>
                    <div class="card-body">
                        <p class="card-text"><strong>Reported by: </strong>${sighting.user_id}</p>
                        <p class="card-text"><strong>Type: </strong>${sighting.pet_species}, ${sighting.pet_breed}</p>
                        <p class="card-text"><strong>Colour: </strong>${sighting.pet_color}</p>
                        <p class="card-text"><strong>Location: </strong>${sighting.latitude}, ${sighting.longitude}</p>
                        <textarea class="form-control" disabled>${sighting.comment}</textarea>
                    </div>
                    <div class="card-footer text-muted"><p class="m-auto">${sighting.timestamp} GMT</p></div>
                </div>
            </div>`;
        });
    }

    // starts all the event listeners for the search bar
    startSearchSystem() {
        const searchBarInput = document.getElementById('searchBarInput');
        const searchBarDDS = document.getElementById('searchBarDDS');
        const searchBarForm = document.getElementById('searchBarForm');

        // if the search bar gets selected, then it will reload the previous "search suggestions" below if there are any
        searchBarInput.addEventListener('focus', () => {
            const query = searchBarInput.value.trim();
            if (query.length >= 2 && query === this.lastRequest && this.lastDataDisplayed) {
                this.loadSearchSuggestions(this.lastDataDisplayed);
            }
        });

        // listen for every keystroke with a 200ms delay so there are no unnecessary requests being processed
        searchBarInput.addEventListener('input', () => {
            const query = searchBarInput.value.trim();
            clearTimeout(this.dbTimer);
            if (query.length < 2) {
                searchBarDDS.classList.add('d-none');
                return;
            }
            this.dbTimer = setTimeout(() => this.getSearchSuggestions(query), 200);
        });

        // this function prevents the page from reloading and gets the information from the filters if the form is submitted
        searchBarForm.addEventListener('submit', (mEvent) => {
            mEvent.preventDefault();
            this.loadData(new URLSearchParams(new FormData(searchBarForm)).toString());
            searchBarDDS.classList.add('d-none');
        });

        // if the search bar is not currently selected then remove the dropdown list with the search suggestions
        document.addEventListener('click', (mEvent) => {
            if (!searchBarInput.contains(mEvent.target)) {
                searchBarDDS.classList.add('d-none');
            }
        });
    }

    // gets new pet suggestions based on what the user typed
    getSearchSuggestions(query) {
        if (this.currentSearchRequest) {
            this.currentSearchRequest.abort();
        }
        this.currentSearchRequest = new XMLHttpRequest();
        this.currentSearchRequest.open('GET', `markers.php?searchinput=${encodeURIComponent(query)}&limit=8`, true);
        this.currentSearchRequest.onreadystatechange = () => {
            if (this.currentSearchRequest.readyState === 4 && this.currentSearchRequest.status === 200) {
                const data = JSON.parse(this.currentSearchRequest.responseText);
                this.lastRequest = query;
                this.lastDataDisplayed = data.mapMarkers;
                this.loadSearchSuggestions(data.mapMarkers);
            } else {
                console.log('error: ' + this.currentSearchRequest.status);
            }
        };
        this.currentSearchRequest.send();
    }

    // displays the dropdown list for search suggestions and handles clicking on them
    loadSearchSuggestions(data) {
        const suggestions = document.getElementById('searchBarDDS');
        suggestions.innerHTML = '';
        if (data.length === 0) return suggestions.classList.add('d-none');

        data.slice(0, 8).forEach(item => {
            const a = document.createElement('a');
            a.className = "list-group-item list-group-item-action d-flex justify-content-between";
            a.innerHTML = `<strong>${item.pet_name}, ${item.pet_breed}</strong> <small>${item.timestamp}</small>`;

            // after clicking on the search suggestion, it zooms on the marker on the map and removes the dropdown list
            a.addEventListener('click', (mEvent) => {
                mEvent.preventDefault();

                document.getElementById('searchBarInput').value = item.pet_name;
                suggestions.classList.add('d-none');

                if (item.latitude && item.longitude) {
                    this.map.flyTo([item.latitude, item.longitude], 16, {
                        duration: 1.75
                    });

                    this.markerGroup.eachLayer((layer) => {
                        if (layer instanceof L.Marker) {
                            const latLng = layer.getLatLng();
                            if (latLng.lat == item.latitude && latLng.lng == item.longitude) {
                                layer.openPopup();
                            }
                        }
                    });
                }
            });
            suggestions.appendChild(a);
        });
        suggestions.classList.remove('d-none');
    }

    // adds event listeners to the search bar filters so the data reloads automatically when they change and making it ready for markers.php
    updateSearchFilters() {
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', () => {
                this.loadData(new URLSearchParams(new FormData(document.getElementById('searchBarForm'))).toString());
            });
        });
    }

    // listens for clicks on page numbers to update the sightings cards page without reloading the page
    paginationSystem() {
        document.addEventListener('click', (mEvent) => {
            const link = mEvent.target.closest('.ajax-page-link');
            if (link) {
                mEvent.preventDefault();
                this.loadData(new URL(link.href, window.location.origin).search.substring(1));
            }
        });
    }

    // creates the pagination system with numbers and previous and next arrows at the bottom
    loadPagination(metadata, currentParams) {
        const nav = document.getElementById('paginationNav');
        if (!nav) return;

        const params = new URLSearchParams(currentParams);
        let html = '<ul class="pagination justify-content-center mt-4">';

        const prevPage = metadata.currentPage - 1;
        const prevDisabled = (metadata.currentPage <= 1) ? 'disabled' : '';
        params.set('page', prevPage);
        html += `
        <li class="page-item ${prevDisabled}">
            <a class="page-link ajax-page-link" href="?${params.toString()}" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>`;

        // creates the numbered list of pages
        const range = 2;
        for (let i = 1; i <= metadata.totalPages; i++) {
            if (i === 1 || i === metadata.totalPages || (i >= metadata.currentPage - range && i <= metadata.currentPage + range)) {
                if (i === 2 && metadata.currentPage - range > 2) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                params.set('page', i);
                const activeClass = (i === metadata.currentPage) ? 'active' : '';
                html += `
                <li class="page-item ${activeClass}">
                    <a class="page-link ajax-page-link" href="?${params.toString()}">${i}</a>
                </li>`;
                if (i === metadata.totalPages - 1 && metadata.currentPage + range < metadata.totalPages - 1) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
        }

        const nextPage = metadata.currentPage + 1;
        const nextDisabled = (metadata.currentPage >= metadata.totalPages) ? 'disabled' : '';
        params.set('page', nextPage);
        html += `
        <li class="page-item ${nextDisabled}">
            <a class="page-link ajax-page-link" href="?${params.toString()}" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>`;

        html += '</ul>';
        nav.innerHTML = html;
    }

    // gets the user current location and centers the map on them
    currentUserLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(position => {
                L.circle(
                    [position.coords.latitude, position.coords.longitude],
                    { radius: 150, color: 'green' }
                ).addTo(this.map).bindPopup("You are here.");
                this.map.setView([position.coords.latitude, position.coords.longitude], 13);
            });
        }
    }

    // opens the bootstrap modal to report a pet sighting
    showForm(id, name, lat, lng) {
        if (!this.userLoggedIn) {
            this.showMessage("You must be logged in to report a sighting.", "danger");
            return;
        }
        document.getElementById('selectedPetName').innerText = name;
        document.getElementById('sightingPetId').value = id;
        document.getElementById('sightingLat').value = lat;
        document.getElementById('sightingLong').value = lng;
        new bootstrap.Modal(document.getElementById('sightingModal')).show();
    }

    // handles the sighting form submission and shows a success message using the showMessage() function
    submitSighting() {
        const form = document.getElementById('sightingsForm');
        if (!form) return;
        form.addEventListener('submit', (mEvent) => {
            mEvent.preventDefault();
            const formData = new FormData(form);
            formData.append('addsightingsbutton', 'true');
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'createsightings.php', true);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onreadystatechange = () => {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    this.showMessage("The sighting has been successfully added.", "success")
                    bootstrap.Modal.getInstance(document.getElementById('sightingModal')).hide();
                    this.loadData();
                    form.reset();
                } else {
                    console.log('error: ' + xhr.status);
                }
            };
            xhr.send(formData);
        });
    }

    // generates a custom bootstrap alert message at the top of the page
    showMessage(msg, displayType) {
        const containerDiv = document.getElementById('containerDisplayMsg');
        const divWrapper = document.createElement('div');
        divWrapper.innerHTML = [
            `<div class="alert alert-${displayType} alert-dismissible fade show" role="alert">`,
            `   <div>${msg}</div>`,
            '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
            '</div>'
        ].join('');

        containerDiv.append(divWrapper);
    }
}

// runs everything related to the map logic once the browser has finished loading the page
document.addEventListener('DOMContentLoaded', () => {
    window.petMap = new SightingMap();
});