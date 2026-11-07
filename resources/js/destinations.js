import L from "leaflet";
window.L = L;
import "leaflet/dist/leaflet.css";
import "leaflet-control-geocoder";
import "leaflet-control-geocoder/dist/Control.Geocoder.css";

import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;

document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("destinationsTableBody");
    const token = localStorage.getItem("admin_token");
    let allDestinations = []; // Almacena el listado completo para el buscador

    // Restricción geográfica para El Salvador
    const elSalvadorBounds = L.latLngBounds([13.0, -90.2], [14.5, -87.7]);

    const createElSalvadorGeocoder = () => {
        return L.Control.geocoder({
            defaultMarkGeocode: false,
            placeholder: "Buscar en El Salvador...",
            errorMessage: "No se encontró el lugar.",
            geocoder: L.Control.Geocoder.nominatim({
                geocodeargs: {
                    countrycodes: "sv",
                    bounded: 1,
                    viewbox: "-90.13,13.15,-87.68,14.45",
                },
            }),
        });
    };

    // Generar HTML de la fila de la tabla con todos los campos
    const createDestinationRowHTML = (item) => {
        const imageUrl = item.image_url || null;
        const imageTag = imageUrl
            ? `<img src="${imageUrl}" class="rounded-3 shadow-sm border" width="90" height="60" style="object-fit: cover;" alt="${item.name}">`
            : `<div class="rounded-3 border bg-light text-muted d-flex align-items-center justify-content-center" style="width: 90px; height: 60px; font-size: 0.70rem;">Sin Imagen</div>`;

        const locationStr = `${item.municipality || ""}, ${item.department || ""}`;

        // Determinar icono según la categoría
        let categoryIcon = "bi-geo-alt-fill";
        const cat = (item.category || "").toLowerCase();
        if (cat.includes("playa")) {
            categoryIcon = "bi-water"; 
        } else if (cat.includes("montaña")) {
            categoryIcon = "bi-tree-fill"; 
        } else if (cat.includes("cultural")) {
            categoryIcon = "bi-bank"; 
        } else if (
            cat.includes("gastronómico") ||
            cat.includes("gastronomico")
        ) {
            categoryIcon = "bi-cup-hot-fill"; 
        } else if (cat.includes("aventura")) {
            categoryIcon = "bi-compass-fill"; 
        } else if (cat.includes("relax")) {
            categoryIcon = "bi-sun-fill"; 
        }

        const isActive = item.status === "activo";
        const statusBadge = isActive
            ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Activo</span>`
            : `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Inactivo</span>`;

        // Recortar la descripción para que la tabla luzca limpia y ordenada
        const shortDesc = item.description
            ? item.description.length > 45
                ? item.description.substring(0, 45) + "..."
                : item.description
            : "Sin descripción";

        return `
        <td class="px-3 fw-bold text-secondary">#${String(item.id).padStart(3, "0")}</td>
        <td>${imageTag}</td>
        <td>
            <div class="fw-bold text-dark">${item.name}</div>
            <small class="text-muted text-truncate d-inline-block" style="max-width: 220px;" title="${item.description || ""}">${shortDesc}</small>
        </td>
        <td class="text-muted small">${locationStr}</td>
        <td>
            <span class="badge" style="background-color: #e2f9f1; color: #2A9D8F; border: 1px solid #c2e8e0; font-weight: 500;">
                <i class="bi ${categoryIcon} me-1"></i>${item.category || "N/A"}
            </span>
        </td>
        <td class="text-muted small">${item.traveler_type || "General"}</td>
        <td>${statusBadge}</td>
        <td class="text-end px-3">
            <button class="btn btn-sm p-1 me-1 edit-btn border-0 bg-transparent shadow-none" data-id="${item.id}" title="Editar" style="color: #0D2A4D !important;">
                <i class="bi bi-pencil fs-5"></i>
            </button>
            <button class="btn btn-sm p-1 delete-btn border-0 bg-transparent shadow-none" data-id="${item.id}" title="Desactivar" style="color: #f2994a !important;">
                <i class="bi bi-trash fs-5"></i>
            </button>
        </td>
    `;
    };
    // Función para renderizar la tabla dinámicamente
    function renderTable(items) {
        if (!tableBody) return;
        tableBody.innerHTML = "";

        if (!items || items.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">No se encontraron registros.</td></tr>`;
            return;
        }

        items.forEach((item) => {
            const tr = document.createElement("tr");
            tr.setAttribute("data-row-id", item.id);
            tr.innerHTML = createDestinationRowHTML(item);
            tableBody.appendChild(tr);
        });
    }

    // Cargar listado de Destinos desde la API
    async function loadDestinations() {
        if (!tableBody) return;

        try {
            const res = await fetch("/api/admin/destinations", {
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${token}`,
                },
            });

            if (!res.ok) throw new Error("Error al obtener los destinos");

            const data = await res.json();
            allDestinations = Array.isArray(data)
                ? data
                : data.destinations || data.data || [];
            renderTable(allDestinations);
        } catch (err) {
            console.error("Error cargando destinos:", err);
        }
    }

    loadDestinations();

    // Filtro de búsqueda en tiempo real
    const searchInput = document.getElementById("searchDestinationInput");
    if (searchInput) {
        searchInput.addEventListener("input", (e) => {
            const query = e.target.value.toLowerCase();
            const filtered = allDestinations.filter(
                (item) =>
                    (item.name && item.name.toLowerCase().includes(query)) ||
                    (item.department &&
                        item.department.toLowerCase().includes(query)) ||
                    (item.municipality &&
                        item.municipality.toLowerCase().includes(query)) ||
                    (item.category &&
                        item.category.toLowerCase().includes(query)),
            );
            renderTable(filtered);
        });
    }

    // Función auxiliar reutilizable para la vista previa de imágenes (Crear / Editar)
    function setupImagePreview(inputId) {
        const inputElement = document.getElementById(inputId);
        if (!inputElement) return;

        inputElement.addEventListener("change", (e) => {
            const file = e.target.files[0];
            const container = inputElement.closest(".border-dashed");
            const previewText = container?.querySelector(".fw-semibold");

            if (file && container) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    container.style.backgroundImage = `url('${event.target.result}')`;
                    container.style.backgroundSize = "cover";
                    container.style.backgroundPosition = "center";
                    if (previewText)
                        previewText.textContent = `Seleccionada: ${file.name}`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Inicializar vistas previas
    setupImagePreview("image_url");
    setupImagePreview("edit_image_url");

    // Creación de Destinos y Mapa
    const createForm = document.getElementById("createDestinationForm");
    const createModalEl = document.getElementById("createDestinationModal");

    let createMap = null;
    let createMarker = null;

    if (createModalEl) {
        createModalEl.addEventListener("shown.bs.modal", () => {
            if (!createMap) {
                createMap = L.map("createMap", {
                    maxBounds: elSalvadorBounds,
                    maxBoundsViscosity: 1.0,
                    minZoom: 8,
                    maxZoom: 19,
                }).setView([13.6929, -89.2182], 9);

                L.tileLayer(
                    "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
                    {
                        maxZoom: 19,
                    },
                ).addTo(createMap);

                createElSalvadorGeocoder()
                    .on("markgeocode", function (e) {
                        const latlng = e.geocode.center;
                        createMap.setView(latlng, 14);
                        document.getElementById("latitude").value =
                            latlng.lat.toFixed(7);
                        document.getElementById("longitude").value =
                            latlng.lng.toFixed(7);
                        if (createMarker) createMarker.setLatLng(latlng);
                        else createMarker = L.marker(latlng).addTo(createMap);
                    })
                    .addTo(createMap);

                createMap.on("click", (e) => {
                    const { lat, lng } = e.latlng;
                    document.getElementById("latitude").value = lat.toFixed(7);
                    document.getElementById("longitude").value = lng.toFixed(7);
                    if (createMarker) createMarker.setLatLng([lat, lng]);
                    else createMarker = L.marker([lat, lng]).addTo(createMap);
                });
            } else {
                createMap.invalidateSize();
            }
        });
    }

    if (createForm) {
        createForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(createForm);

            Swal.fire({
                title: "Guardando destino...",
                text: "Por favor espere",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const res = await fetch("/api/admin/destinations", {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                    body: formData,
                });
                const data = await res.json().catch(() => ({}));

                if (res.status === 422) {
                    Swal.fire({
                        icon: "error",
                        title: "Validación",
                        html: Object.values(data.errors ?? {})
                            .flat()
                            .join("<br>"),
                    });
                    return;
                }
                if (!res.ok) throw new Error(data.message ?? "Error");

                window.bootstrap.Modal.getInstance(createModalEl)?.hide();
                createForm.reset();

                const previewContainer = document.querySelector(
                    "#createDestinationModal .border-dashed",
                );
                if (previewContainer) {
                    previewContainer.style.backgroundImage = "none";
                    const previewText =
                        previewContainer.querySelector(".fw-semibold");
                    if (previewText)
                        previewText.textContent =
                            "Arrastra una imagen aquí o haz clic para seleccionar";
                }

                Swal.fire({
                    icon: "success",
                    title: "¡Éxito!",
                    text: "Destino creado correctamente.",
                    timer: 1200,
                    showConfirmButton: false,
                }).then(() => loadDestinations());
            } catch (err) {
                Swal.fire(
                    "Error",
                    "No se pudo conectar con el servidor.",
                    "error",
                );
            }
        });
    }

    // Manejo global de clics (Editar y Desactivar)
    let editMap = null;
    let editMarker = null;

    document.addEventListener("click", async (e) => {
        const editBtn = e.target.closest(".edit-btn");
        if (editBtn) {
            const id = editBtn.dataset.id;
            try {
                const res = await fetch(`/api/admin/destinations/${id}`, {
                    headers: {
                        Accept: "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                });
                if (!res.ok) throw new Error("No se pudo obtener el registro.");

                const responseData = await res.json();
                const dest = responseData.destination || responseData;
                if (!dest) return;

                document.getElementById("edit_destination_id").value =
                    dest.id || "";
                document.getElementById("edit_name").value = dest.name || "";
                document.getElementById("edit_department").value =
                    dest.department || "";
                document.getElementById("edit_municipality").value =
                    dest.municipality || "";
                document.getElementById("edit_description").value =
                    dest.description || "";
                document.getElementById("edit_latitude").value =
                    dest.latitude || "";
                document.getElementById("edit_longitude").value =
                    dest.longitude || "";
                document.getElementById("edit_category").value =
                    dest.category || "";
                document.getElementById("edit_traveler_type").value =
                    dest.traveler_type || "";

                const statusVal = dest.status || "activo";
                document.getElementById("edit_status").value = statusVal;
                const statusSwitch =
                    document.getElementById("edit_status_switch");
                if (statusSwitch) statusSwitch.checked = statusVal === "activo";

                // Mostrar imagen actual en la vista previa del modal de edición si existe
                const editContainer = document.querySelector(
                    "#editDestinationModal .border-dashed",
                );
                if (editContainer) {
                    if (dest.image_url) {
                        editContainer.style.backgroundImage = `url('${dest.image_url}')`;
                        editContainer.style.backgroundSize = "cover";
                        editContainer.style.backgroundPosition = "center";
                        const previewText =
                            editContainer.querySelector(".fw-semibold");
                        if (previewText)
                            previewText.textContent = "Imagen actual cargada";
                    } else {
                        editContainer.style.backgroundImage = "none";
                        const previewText =
                            editContainer.querySelector(".fw-semibold");
                        if (previewText)
                            previewText.textContent =
                                "Haz clic para cambiar la imagen";
                    }
                }

                const modalEl = document.getElementById("editDestinationModal");
                window.bootstrap.Modal.getOrCreateInstance(modalEl).show();

                setTimeout(() => {
                    const lat = parseFloat(dest.latitude) || 13.6929;
                    const lng = parseFloat(dest.longitude) || -89.2182;

                    if (!editMap) {
                        editMap = L.map("editMap", {
                            maxBounds: elSalvadorBounds,
                            maxBoundsViscosity: 1.0,
                            minZoom: 8,
                            maxZoom: 19,
                        }).setView([lat, lng], 13);

                        L.tileLayer(
                            "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
                            { maxZoom: 19 },
                        ).addTo(editMap);

                        createElSalvadorGeocoder()
                            .on("markgeocode", function (ev) {
                                const latlng = ev.geocode.center;
                                editMap.setView(latlng, 14);
                                document.getElementById("edit_latitude").value =
                                    latlng.lat.toFixed(7);
                                document.getElementById(
                                    "edit_longitude",
                                ).value = latlng.lng.toFixed(7);
                                if (editMarker) editMarker.setLatLng(latlng);
                                else
                                    editMarker =
                                        L.marker(latlng).addTo(editMap);
                            })
                            .addTo(editMap);

                        editMap.on("click", (ev) => {
                            document.getElementById("edit_latitude").value =
                                ev.latlng.lat.toFixed(7);
                            document.getElementById("edit_longitude").value =
                                ev.latlng.lng.toFixed(7);
                            if (editMarker) editMarker.setLatLng(ev.latlng);
                            else
                                editMarker = L.marker(ev.latlng).addTo(editMap);
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
                Swal.fire(
                    "Error",
                    "No se pudieron cargar los datos para edición.",
                    "error",
                );
            }
            return;
        }

        const deleteBtn = e.target.closest(".delete-btn");
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            const confirm = await Swal.fire({
                title: "¿Desactivar destino?",
                text: "El registro cambiará su estado a inactivo.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#0D2A4D",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, desactivar",
            });

            if (confirm.isConfirmed) {
                try {
                    const res = await fetch(`/api/admin/destinations/${id}`, {
                        method: "DELETE",
                        headers: {
                            Accept: "application/json",
                            Authorization: `Bearer ${token}`,
                        },
                    });

                    if (!res.ok) throw new Error("Error al desactivar");

                    Swal.fire({
                        icon: "success",
                        title: "Desactivado",
                        text: "El destino ha sido desactivado.",
                        timer: 1000,
                        showConfirmButton: false,
                    }).then(() => loadDestinations());
                } catch (err) {
                    Swal.fire(
                        "Error",
                        "No se pudo completar la operación.",
                        "error",
                    );
                }
            }
        }
    });

    // Envío de Actualización (Editar)
    const editForm = document.getElementById("editDestinationForm");
    if (editForm) {
        editForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const id = document.getElementById("edit_destination_id").value;
            const formData = new FormData(editForm);
            formData.delete("_method");

            Swal.fire({
                title: "Actualizando cambios...",
                text: "Guardando información",
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const res = await fetch(`/api/admin/destinations/${id}`, {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        Authorization: `Bearer ${token}`,
                    },
                    body: formData,
                });
                const data = await res.json().catch(() => ({}));

                if (res.status === 422) {
                    Swal.fire({
                        icon: "error",
                        title: "Error de Validación",
                        html: Object.values(data.errors ?? {})
                            .flat()
                            .join("<br>"),
                    });
                    return;
                }
                if (!res.ok) throw new Error(data.message ?? "Error");

                window.bootstrap.Modal.getInstance(
                    document.getElementById("editDestinationModal"),
                )?.hide();

                Swal.fire({
                    icon: "success",
                    title: "¡Actualizado!",
                    text: "Destino modificado con éxito.",
                    timer: 1200,
                    showConfirmButton: false,
                }).then(() => loadDestinations());
            } catch (err) {
                Swal.fire(
                    "Error",
                    `No se pudo actualizar: ${err.message}`,
                    "error",
                );
            }
        });
    }
});
