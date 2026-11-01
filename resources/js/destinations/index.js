import L from 'leaflet';
window.L = L;
import 'leaflet/dist/leaflet.css';
import 'leaflet-control-geocoder';
import 'leaflet-control-geocoder/dist/Control.Geocoder.css';

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

const tableBody = document.getElementById('destinationsTableBody');
const token = localStorage.getItem('admin_token');

// Restricción geográfica para El Salvador
const elSalvadorBounds = L.latLngBounds([13.0, -90.2], [14.5, -87.7]);

const createElSalvadorGeocoder = () => {
    return L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: 'Buscar en El Salvador...',
        errorMessage: 'No se encontró el lugar.',
        geocoder: L.Control.Geocoder.nominatim({
            geocodeargs: { countrycodes: 'sv', bounded: 1, viewbox: '-90.13,13.15,-87.68,14.45' }
        })
    });
};

// Función auxiliar para generar el HTML de una fila de la tabla adaptada al diseño visual
const createDestinationRowHTML = (item) => {
    const imageUrl = item.image_url ? `/storage/${item.image_url}` : null;
    const imageTag = imageUrl 
        ? `<img src="${imageUrl}" class="rounded-3 shadow-sm border" width="110" height="70" style="object-fit: cover;" alt="${item.name}">`
        : `<div class="rounded-3 border bg-light text-muted d-flex align-items-center justify-content-center" style="width: 110px; height: 70px; font-size: 0.75rem;">Sin Imagen</div>`;

    let badgeStyle = 'background-color: #e2f9f1; color: #2A9D8F; border: 1px solid #c2e8e0; font-weight: 500;'; 
    let categoryIcon = 'bi-geo-alt-fill';
    const cat = (item.category || '').toLowerCase();
    if (cat.includes('playa')) categoryIcon = 'bi-water';
    else if (cat.includes('montaña')) categoryIcon = 'bi-tree-fill';
    else if (cat.includes('cultural') || cat.includes('cultura')) categoryIcon = 'bi-camera-fill';
    else if (cat.includes('lago')) categoryIcon = 'bi-droplet-fill';
    else if (cat.includes('histórico')) categoryIcon = 'bi-bank';

    const isActive = (item.status === 'activo');
    const statusBadge = isActive
        ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Activo</span>`
        : `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Inactivo</span>`;

    return `
        <td class="px-4 fw-bold" style="color: #6c757d;">#${String(item.id).padStart(3, '0')}</td>
        <td>${imageTag}</td>
        <td class="fw-bold" style="color: #212529;">${item.name}</td>
        <td style="color: #6c757d;">${item.municipality || ''}, ${item.department || ''}</td>
        <td><span class="badge px-3 py-2 rounded-pill" style="${badgeStyle}"><i class="bi ${categoryIcon} me-1"></i> ${item.category}</span></td>
        <td>${statusBadge}</td>
        <td class="text-end px-4">
            <button class="btn btn-sm p-1 me-2 edit-btn border-0 bg-transparent" data-id="${item.id}" title="Editar" style="color: #0D2A4D !important;">
                <i class="bi bi-pencil fs-5"></i>
            </button>
            <button class="btn btn-sm p-1 delete-btn border-0 bg-transparent" data-id="${item.id}" title="Desactivar" style="color: #f2994a !important;">
                <i class="bi bi-trash fs-5"></i>
            </button>
        </td>
    `;
};

// CARGAR DESTINOS EN LA TABLA
async function loadDestinations() {
    if (!tableBody) return;

    try {
        const res = await fetch('/api/admin/destinations', {
            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
        });

        if (!res.ok) throw new Error('Error al obtener los destinos');

        const data = await res.json();
        const destinations = Array.isArray(data) ? data : (data.destinations || data.data || []);
        
        tableBody.innerHTML = '';

        if (!destinations || destinations.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">No hay destinos registrados.</td></tr>`;
            return;
        }

        destinations.forEach((item) => {
            const tr = document.createElement('tr');
            tr.setAttribute('data-row-id', item.id);
            tr.innerHTML = createDestinationRowHTML(item);
            tableBody.appendChild(tr);
        });
    } catch (err) {
        console.error('Error cargando destinos:', err);
    }
}

loadDestinations();

// VISTA PREVIA DE IMAGEN EN CREACIÓN 
const imageInput = document.getElementById('image_url');
if (imageInput) {
    imageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        const previewContainer = imageInput.closest('.border-dashed');
        const previewText = previewContainer?.querySelector('.fw-semibold');
        
        if (file && previewContainer) {
            const reader = new FileReader();
            reader.onload = (event) => {
                previewContainer.style.backgroundImage = `url('${event.target.result}')`;
                previewContainer.style.backgroundSize = 'cover';
                previewContainer.style.backgroundPosition = 'center';
                if (previewText) previewText.textContent = `Archivo seleccionado: ${file.name}`;
            };
            reader.readAsDataURL(file);
        }
    });
}

// CREACIÓN DE DESTINOS
const createForm = document.getElementById('createDestinationForm');
const createModalEl = document.getElementById('createDestinationModal');

let createMap = null;
let createMarker = null;

if (createModalEl) {
    createModalEl.addEventListener('shown.bs.modal', () => {
        if (!createMap) {
            createMap = L.map('createMap', {
                maxBounds: elSalvadorBounds,
                maxBoundsViscosity: 1.0,
                minZoom: 8,
                maxZoom: 19
            }).setView([13.6929, -89.2182], 9);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(createMap);

            createElSalvadorGeocoder().on('markgeocode', function(e) {
                const latlng = e.geocode.center;
                createMap.setView(latlng, 14);
                document.getElementById('latitude').value = latlng.lat.toFixed(7);
                document.getElementById('longitude').value = latlng.lng.toFixed(7);
                if (createMarker) createMarker.setLatLng(latlng);
                else createMarker = L.marker(latlng).addTo(createMap);
            }).addTo(createMap);

            createMap.on('click', (e) => {
                const { lat, lng } = e.latlng;
                document.getElementById('latitude').value = lat.toFixed(7);
                document.getElementById('longitude').value = lng.toFixed(7);
                if (createMarker) createMarker.setLatLng([lat, lng]);
                else createMarker = L.marker([lat, lng]).addTo(createMap);
            });
        } else {
            createMap.invalidateSize();
        }
    });
}

if (createForm) {
    createForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(createForm);

        Swal.fire({
            title: 'Guardando destino...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const res = await fetch('/api/admin/destinations', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
                body: formData
            });
            const data = await res.json().catch(() => ({}));

            if (res.status === 422) {
                Swal.fire({ icon: 'error', title: 'Validación', html: Object.values(data.errors ?? {}).flat().join('<br>') });
                return;
            }
            if (!res.ok) throw new Error(data.message ?? 'Error');

            const modalInstance = window.bootstrap.Modal.getInstance(createModalEl);
            if (modalInstance) modalInstance.hide();
            createForm.reset();
            
            const previewContainer = imageInput?.closest('.border-dashed');
            if (previewContainer) {
                previewContainer.style.backgroundImage = 'none';
                const previewText = previewContainer.querySelector('.fw-semibold');
                if (previewText) previewText.textContent = 'Arrastra una imagen aquí o haz clic para seleccionar';
            }

            Swal.fire({ 
                icon: 'success', 
                title: '¡Éxito!', 
                text: 'Destino creado correctamente.', 
                timer: 1200, 
                showConfirmButton: false 
            }).then(() => {
                loadDestinations();
            });

        } catch (err) {
            Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
        }
    });
}

// VISTA PREVIA DE IMAGEN EN EDICIÓN
const editImageInput = document.getElementById('edit_image_url');
if (editImageInput) {
    editImageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        const previewContainer = editImageInput.closest('.border-dashed');
        const previewText = previewContainer?.querySelector('.fw-semibold');
        
        if (file && previewContainer) {
            const reader = new FileReader();
            reader.onload = (event) => {
                previewContainer.style.backgroundImage = `url('${event.target.result}')`;
                previewContainer.style.backgroundSize = 'cover';
                previewContainer.style.backgroundPosition = 'center';
                if (previewText) previewText.textContent = `Nueva imagen: ${file.name}`;
            };
            reader.readAsDataURL(file);
        }
    });
}

// MANEJO DE CLICS (EDITAR Y DESACTIVAR)
let editMap = null;
let editMarker = null;

document.addEventListener('click', async (e) => {
    // Botón Editar (Abrir modal)
    const editBtn = e.target.closest('.edit-btn');
    if (editBtn) {
        const id = editBtn.dataset.id;
        try {
            const res = await fetch(`/api/admin/destinations/${id}`, {
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
            });
            if (!res.ok) throw new Error('No se pudo obtener el registro.');

            const responseData = await res.json();
            const dest = responseData.destination || responseData;
            if (!dest) return;

            document.getElementById('edit_destination_id').value = dest.id || '';
            document.getElementById('edit_name').value = dest.name || '';
            document.getElementById('edit_department').value = dest.department || '';
            document.getElementById('edit_municipality').value = dest.municipality || '';
            document.getElementById('edit_description').value = dest.description || '';
            document.getElementById('edit_latitude').value = dest.latitude || '';
            document.getElementById('edit_longitude').value = dest.longitude || '';
            document.getElementById('edit_category').value = dest.category || '';
            document.getElementById('edit_traveler_type').value = dest.traveler_type || '';
            
            const statusVal = dest.status || 'activo';
            document.getElementById('edit_status').value = statusVal;
            const statusSwitch = document.getElementById('edit_status_switch');
            if (statusSwitch) statusSwitch.checked = (statusVal === 'activo');

            // Resetear vista previa de edición al abrir el modal
            if (editImageInput) {
                editImageInput.value = ''; // Limpia el input file anterior
                const previewContainer = editImageInput.closest('.border-dashed');
                if (previewContainer) {
                    previewContainer.style.backgroundImage = 'none';
                    const previewText = previewContainer.querySelector('.fw-semibold');
                    if (previewText) previewText.textContent = 'Haz clic para cambiar la imagen';
                }
            }

            const modalEl = document.getElementById('editDestinationModal');
            window.bootstrap.Modal.getOrCreateInstance(modalEl).show();

            setTimeout(() => {
                const lat = parseFloat(dest.latitude) || 13.6929;
                const lng = parseFloat(dest.longitude) || -89.2182;

                if (!editMap) {
                    editMap = L.map('editMap', {
                        maxBounds: elSalvadorBounds,
                        maxBoundsViscosity: 1.0,
                        minZoom: 8,
                        maxZoom: 19
                    }).setView([lat, lng], 13);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(editMap);

                    createElSalvadorGeocoder().on('markgeocode', function(ev) {
                        const latlng = ev.geocode.center;
                        editMap.setView(latlng, 14);
                        document.getElementById('edit_latitude').value = latlng.lat.toFixed(7);
                        document.getElementById('edit_longitude').value = latlng.lng.toFixed(7);
                        if (editMarker) editMarker.setLatLng(latlng);
                        else editMarker = L.marker(latlng).addTo(editMap);
                    }).addTo(editMap);

                    editMap.on('click', (ev) => {
                        document.getElementById('edit_latitude').value = ev.latlng.lat.toFixed(7);
                        document.getElementById('edit_longitude').value = ev.latlng.lng.toFixed(7);
                        if (editMarker) editMarker.setLatLng(ev.latlng);
                        else editMarker = L.marker(ev.latlng).addTo(editMap);
                    });
                } else {
                    editMap.invalidateSize();
                    editMap.setView([lat, lng], 13);
                }

                if (editMarker) {
                    editMarker.setLatLng([lat, lng]);
                } else {
                    editMarker = L.marker([lat, lng]).addTo(editMap);
                }
            }, 300);

        } catch (err) {
            Swal.fire('Error', 'No se pudieron cargar los datos para edición.', 'error');
        }
        return;
    }

    // Botón Desactivar
    const deleteBtn = e.target.closest('.delete-btn');
    if (deleteBtn) {
        const id = deleteBtn.dataset.id;
        const confirm = await Swal.fire({
            title: '¿Desactivar destino?',
            text: "El registro cambiará su estado a inactivo.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0D2A4D',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        });

        if (confirm.isConfirmed) {
            Swal.fire({
                title: 'Desactivando...',
                text: 'Actualizando registro',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            try {
                const res = await fetch(`/api/admin/destinations/${id}`, {
                    method: 'DELETE',
                    headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
                });
                
                if (!res.ok) throw new Error('Error al desactivar');

                Swal.fire({ 
                    icon: 'success', 
                    title: 'Desactivado', 
                    text: 'El destino ha sido desactivado.', 
                    timer: 1000, 
                    showConfirmButton: false 
                }).then(async () => {
                    const row = document.querySelector(`tr[data-row-id="${id}"]`);
                    if (row) {
                        const fetchSingle = await fetch(`/api/admin/destinations/${id}`, {
                            headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` }
                        });
                        if (fetchSingle.ok) {
                            const singleData = await fetchSingle.json();
                            const destObj = singleData.destination || singleData;
                            row.innerHTML = createDestinationRowHTML(destObj);
                        }
                    }
                });

            } catch (err) {
                Swal.fire('Error', 'No se pudo completar la operación.', 'error');
            }
        }
    }
});

// ENVÍO DE ACTUALIZACIÓN (EDITAR)
const editForm = document.getElementById('editDestinationForm');

if (editForm) {
    editForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('edit_destination_id').value;
        const formData = new FormData(editForm);
        formData.delete('_method');

        Swal.fire({
            title: 'Actualizando cambios...',
            text: 'Guardando información',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        try {
            const res = await fetch(`/api/admin/destinations/${id}`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Authorization': `Bearer ${token}` },
                body: formData
            });
            const data = await res.json().catch(() => ({}));

            if (res.status === 422) {
                Swal.fire({ icon: 'error', title: 'Error de Validación', html: Object.values(data.errors ?? {}).flat().join('<br>') });
                return;
            }
            if (!res.ok) throw new Error(data.message ?? 'Error');

            const editModalEl = document.getElementById('editDestinationModal');
            const modalInstance = window.bootstrap.Modal.getInstance(editModalEl);
            if (modalInstance) modalInstance.hide();
            
            Swal.fire({ 
                icon: 'success', 
                title: '¡Actualizado!', 
                text: 'Destino modificado con éxito.', 
                timer: 1200, 
                showConfirmButton: false 
            }).then(() => {
                const row = document.querySelector(`tr[data-row-id="${id}"]`);
                const updatedItem = data.destination || data;
                if (row && updatedItem && (updatedItem.id || updatedItem.name)) {
                    row.innerHTML = createDestinationRowHTML(updatedItem);
                } else {
                    loadDestinations();
                }
            });

        } catch (err) {
            Swal.fire('Error', `No se pudo actualizar: ${err.message}`, 'error');
        }
    });
}