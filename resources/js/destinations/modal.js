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

    // CARGAR DESTINOS EN LA TABLA
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
            const destinations = Array.isArray(data)
                ? data
                : data.destinations || data.data || [];

            tableBody.innerHTML = "";

            if (!destinations || destinations.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="7" class="text-center py-4 text-muted">No hay destinos registrados.</td></tr>`;
                return;
            }

            destinations.forEach((item) => {
                const imageUrl = item.image_url
                    ? `/storage/${item.image_url}`
                    : null;
                const imageTag = imageUrl
                    ? `<img src="${imageUrl}" class="rounded-3 shadow-sm border" width="110" height="70" style="object-fit: cover;" alt="${item.name}">`
                    : `<div class="rounded-3 border bg-light text-muted d-flex align-items-center justify-content-center" style="width: 110px; height: 70px; font-size: 0.75rem;">Sin Imagen</div>`;

                let badgeStyle =
                    "background-color: #e2f0d9; color: #385723; border: 1px solid #c8e1b7;";
                let categoryIcon = "bi-geo-alt-fill";
                const cat = (item.category || "").toLowerCase();
                if (cat === "playa") {
                    badgeStyle =
                        "background-color: #e3f2fd; color: #0d47a1; border: 1px solid #bbdefb;";
                    categoryIcon = "bi-water";
                } else if (cat === "montaña") {
                    badgeStyle =
                        "background-color: #e8f5e9; color: #1b5e20; border: 1px solid #c8e6c9;";
                    categoryIcon = "bi-tree-fill";
                } else if (cat === "cultural") {
                    badgeStyle =
                        "background-color: #fff3e0; color: #e65100; border: 1px solid #ffe0b2;";
                    categoryIcon = "bi-camera-fill";
                }

                const isActive = item.status === "activo";
                const statusBadge = isActive
                    ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Activo</span>`
                    : `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Inactivo</span>`;

                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td class="px-4 fw-bold text-secondary">#${String(item.id).padStart(3, "0")}</td>
                    <td>${imageTag}</td>
                    <td class="fw-bold text-dark">${item.name}</td>
                    <td class="text-muted">${item.municipality || ""}, ${item.department || ""}</td>
                    <td><span class="badge px-3 py-2 rounded-pill fw-normal" style="${badgeStyle}"><i class="bi ${categoryIcon} me-1"></i> ${item.category}</span></td>
                    <td>${statusBadge}</td>
                    <td class="text-end px-4">
                        <button class="btn btn-sm btn-light border me-1 edit-btn" data-id="${item.id}" title="Editar">
                            <i class="bi bi-pencil text-primary"></i>
                        </button>
                        <button class="btn btn-sm btn-light border delete-btn" data-id="${item.id}" title="Desactivar">
                            <i class="bi bi-trash text-danger"></i>
                        </button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });
        } catch (err) {
            console.error("Error cargando destinos:", err);
        }
    }

    loadDestinations();

    // MANEJO DE CLICS (EDITAR Y DESACTIVAR)
    let editMap = null;
    let editMarker = null;

    document.addEventListener("click", async (e) => {
        // Botón Editar
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

                // Si la respuesta no es correcta, capturamos el texto o el error del servidor
                if (!res.ok) {
                    const errorText = await res.text();
                    console.error("Error del servidor:", res.status, errorText);
                    throw new Error(`Error HTTP: ${res.status}`);
                }

                const responseData = await res.json();
                const dest = responseData.destination || responseData;
                if (!dest) throw new Error("El objeto de destino está vacío");

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

                    if (editMarker) editMarker.setLatLng([lat, lng]);
                    else editMarker = L.marker([lat, lng]).addTo(editMap);
                }, 300);
            } catch (err) {
                console.error("Detalle completo del fallo en edición:", err);
                Swal.fire(
                    "Error",
                    "No se pudieron cargar los datos para edición. Revisa la consola (F12).",
                    "error",
                );
            }
            return;
        }

        // Botón Desactivar
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
                cancelButtonText: "Cancelar",
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
                        timer: 1200,
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

    // ENVÍO DE ACTUALIZACIÓN (EDITAR)
    const editForm = document.getElementById("editDestinationForm");
    const updateBtn = document.getElementById("updateDestinationBtn");

    if (editForm) {
        editForm.addEventListener("submit", async (e) => {
            e.preventDefault();
            const id = document.getElementById("edit_destination_id").value;
            const currentToken = localStorage.getItem("admin_token");
            const formData = new FormData(editForm);
            formData.delete("_method");

            updateBtn.disabled = true;
            updateBtn.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>Actualizando...';

            // Mostramos alerta con indicador de carga
            Swal.fire({
                title: "Actualizando cambios...",
                text: "Guardando información",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            try {
                const res = await fetch(`/api/admin/destinations/${id}`, {
                    method: "POST",
                    headers: {
                        Accept: "application/json",
                        Authorization: `Bearer ${currentToken}`,
                    },
                    body: formData,
                });

                const data = await res.json().catch(() => ({}));

                if (res.status === 422) {
                    const errores = data.errors
                        ? Object.values(data.errors).flat().join("<br>")
                        : "Error de validación";
                    Swal.fire({
                        icon: "error",
                        title: "Error de Validación",
                        html: errores,
                    });
                    return;
                }

                if (!res.ok) {
                    throw new Error(
                        data.message || `Error del servidor (${res.status})`,
                    );
                }

                // Ocultamos el modal de Bootstrap de forma segura
                const modalEl = document.getElementById("editDestinationModal");
                const modalInstance =
                    window.bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();

                Swal.fire({
                    icon: "success",
                    title: "¡Actualizado!",
                    text: "Destino modificado con éxito.",
                    timer: 1200,
                    showConfirmButton: false,
                }).then(() => {
                    // volvemos a cargar la tabla de forma asíncrona
                    loadDestinations();
                });
            } catch (err) {
                console.error("Error al actualizar:", err);
                Swal.fire(
                    "Error",
                    `No se pudo actualizar: ${err.message}`,
                    "error",
                );
            } finally {
                updateBtn.disabled = false;
                updateBtn.textContent = "Guardar Cambios";
            }
        });
    }
});
