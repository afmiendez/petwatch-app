class MissingPets {
    constructor() {
        this.form = document.getElementById('petsSearchForm');
        this.container = document.getElementById('petsContainer');
        this.nav = document.getElementById('petsPaginationNav');

        this.startEvents();
        this.submitSighting();
    }

    // creates event listeners for the search form and pagination links
    startEvents() {
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.loadPets();
        });

        document.addEventListener('click', (e) => {
            const link = e.target.closest('.ajax-page-link');
            if (link) {
                e.preventDefault();
                const params = new URL(link.href).searchParams.toString();
                this.loadPets(params);
            }
        });

        this.loadPets();
    }

    // sends a fetch request to the server and passes the received data to the loading functions
    loadPets(params = "") {
        if (!params) {
            params = new URLSearchParams(new FormData(this.form)).toString();
        }

        fetch(`missingpetsajaxrequests.php?${params}`)
            .then(response => response.json())
            .then(data => {
                this.loadCards(data.pets);
                this.loadPagination(data.pagination, params);
            });
    }

    // generates the "card" div for each pet and adds it to the results container
    loadCards(pets) {
        const container = document.getElementById('petsContainer');
        if (!container) return;

        container.innerHTML = '';

        pets.forEach(pet => {
            let petStatusLabel = 'Lost';
            let badgeStatusClass = 'bg-danger';

            if (pet.status.toLowerCase() === 'found') {
                petStatusLabel = 'Found';
                badgeStatusClass = 'bg-success';
            }

            container.innerHTML += `
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <img src="images/animal_images/${pet.photo}" class="card-img-top">
        
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <h4 class="card-title">${pet.name}</h4>
                            <span class="badge rounded-pill ${badgeStatusClass} ms-auto">${petStatusLabel}</span>
                        </div>
                        <p class="card-text mb-1"><strong>Owner: </strong>${pet.user_id}</p>
                        <p class="card-text mb-1"><strong>Species: </strong>${pet.species}</p>
                        <p class="card-text mb-1"><strong>Breed: </strong>${pet.breed}</p>
                        <p class="card-text mb-1"><strong>Colour: </strong>${pet.color}</p>
                        <p class="card-text mb-1"><strong>Date: </strong>${pet.date}</p>
                        <p class="card-text mb-1"><strong>Description: </strong></p>
                        <textarea disabled class="form-control">${pet.description}</textarea>
                        
                        <div class="d-grid mt-3">
                            <button class="btn btn-primary btn-sm" onclick="missingPetsPage.showForm('${pet.id}', '${pet.name}')">
                                Report Sighting
                            </button>
                        </div>

                        <div class="d-flex align-items-center gap-2 mt-3">
                            <p class="card-text text-muted ms-auto">Pet ID: ${pet.id}</p>
                        </div>
                    </div>
                </div>
            </div>`;
        });
    }

    // creates the pagination system with numbers and previous and next arrows at the bottom
    loadPagination(meta, currentParams) {
        if (!this.nav) return;

        const params = new URLSearchParams(currentParams);
        let html = '<ul class="pagination justify-content-center mt-4">';

        const prevPage = meta.currentPage - 1;
        const prevDisabled = (meta.currentPage <= 1) ? 'disabled' : '';
        params.set('page', prevPage);
        html += `
        <li class="page-item ${prevDisabled}">
            <a class="page-link ajax-page-link" href="?${params.toString()}" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>`;

        // creates the numbered list of pages
        const range = 2;
        for (let i = 1; i <= meta.totalPages; i++) {
            if (i === 1 || i === meta.totalPages || (i >= meta.currentPage - range && i <= meta.currentPage + range)) {
                if (i === 2 && meta.currentPage - range > 2) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
                params.set('page', i);
                const active = i === meta.currentPage ? 'active' : '';
                html += `
                <li class="page-item ${active}">
                    <a class="page-link ajax-page-link" href="?${params.toString()}">${i}</a>
                </li>`;
                if (i === meta.totalPages - 1 && meta.currentPage + range < meta.totalPages - 1) {
                    html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                }
            }
        }

        const nextPage = meta.currentPage + 1;
        const nextDisabled = (meta.currentPage >= meta.totalPages) ? 'disabled' : '';
        params.set('page', nextPage);
        html += `
        <li class="page-item ${nextDisabled}">
            <a class="page-link ajax-page-link" href="?${params.toString()}" aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>`;

        this.nav.innerHTML = html + '</ul>';
    }

    // opens the bootstrap modal to report a pet sighting
    showForm(id, name) {
        document.getElementById('selectedPetName').innerText = name;
        document.getElementById('sightingPetId').value = id;
        new bootstrap.Modal(document.getElementById('sightingModal')).show();
    }

    // handles the sighting form submission and shows a success message using the showMessage() function
    submitSighting() {
        const form = document.getElementById('sightingsForm');
        if (!form) return;
        form.addEventListener('submit', (e) => {
            e.preventDefault();
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

// runs everything related to the missing pets page once the browser has finished loading the page
document.addEventListener('DOMContentLoaded', () => {
    window.missingPetsPage = new MissingPets();
});