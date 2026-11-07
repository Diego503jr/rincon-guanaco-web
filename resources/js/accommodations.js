import * as bootstrap from "bootstrap";
window.bootstrap = bootstrap;

const tableBody = document.getElementById("accommodationsTableBody");
const token = localStorage.getItem("admin_token");
let allAccommodations = []; // Almacena el listado completo para el buscador

// Renderizar fila de la tabla
// Generar HTML de la fila de la tabla para Alojamientos
const createAccRowHTML = (item) => {
    const imageUrl = item.image_url || null;
    const imageTag = imageUrl
        ? `<img src="${imageUrl}" class="rounded-3 shadow-sm border" width="90" height="60" style="object-fit: cover;" alt="${item.name}">`
        : `<div class="rounded-3 border bg-light text-muted d-flex align-items-center justify-content-center" style="width: 90px; height: 60px; font-size: 0.70rem;">Sin Imagen</div>`;

    // Ubicación: prioriza el destino asociado o la dirección escrita
    const locationStr = item.destination
        ? `${item.destination.municipality || ""}, ${item.destination.department || ""}`
        : item.address || "Sin dirección";

    // Contacto (Teléfono y Correo)
    const phoneText = item.contact_phone
        ? `<i class="bi bi-telephone me-1"></i>${item.contact_phone}`
        : "";
    const emailText = item.email
        ? `<i class="bi bi-envelope me-1"></i>${item.email}`
        : "";
    const contactInfo = [phoneText, emailText].filter(Boolean).join(" • ");

    // Horarios de entrada y salida
    const checkIn = item.check_in ? item.check_in.substring(0, 5) : "N/D";
    const checkOut = item.check_out ? item.check_out.substring(0, 5) : "N/D";

    // Icono o estilo según el tipo de alojamiento
    let typeIcon = "bi-house-door-fill";
    const typeLower = (item.type || "").toLowerCase();
    if (typeLower.includes("hotel")) typeIcon = "bi-building";
    else if (typeLower.includes("hostal")) typeIcon = "bi-shop";
    else if (typeLower.includes("apartamento")) typeIcon = "bi-buildings";

    const isActive = item.status === "activo";
    const statusBadge = isActive
        ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Activo</span>`
        : `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Inactivo</span>`;

    return `
        <td class="px-3 fw-bold text-secondary">#${String(item.id).padStart(3, "0")}</td>
        <td>${imageTag}</td>
        <td>
            <div class="fw-bold text-dark">${item.name}</div>
            <small class="text-muted d-block" style="font-size: 0.75rem;">${contactInfo || "Sin contacto registrado"}</small>
        </td>
        <td class="text-muted small">
            <div><i class="bi bi-geo-alt text-danger me-1"></i>${locationStr}</div>
            ${item.address ? `<small class="text-secondary d-truncate" style="max-width: 180px;" title="${item.address}">${item.address}</small>` : ""}
        </td>
        <td>
            <span class="badge text-capitalize" style="background-color: #e2f9f1; color: #2A9D8F; border: 1px solid #c2e8e0; font-weight: 500;">
                <i class="bi ${typeIcon} me-1"></i>${item.type || "N/A"}
            </span>
        </td>
        <td>
            <div class="fw-bold text-dark">$${parseFloat(item.price || 0).toFixed(2)} <small class="text-muted fw-normal">/ noche</small></div>
            <small class="text-muted" style="font-size: 0.75rem;"><i class="bi bi-clock me-1"></i>In: ${checkIn} | Out: ${checkOut}</small>
        </td>
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

// Función para renderizar la tabla dinámicamente con los elementos filtrados
function renderAccommodationsTable(items) {
    if (!tableBody) return;
    tableBody.innerHTML = "";

    if (!items || items.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="8" class="text-center py-4 text-muted">No se encontraron registros.</td></tr>`;
        return;
    }

    items.forEach((item) => {
        const tr = document.createElement("tr");
        tr.setAttribute("data-row-id", item.id);
        tr.innerHTML = createAccRowHTML(item);
        tableBody.appendChild(tr);
    });
}

// Cargar listado en la tabla
async function loadAccommodations() {
    if (!tableBody) return;
    try {
        const res = await fetch("/api/admin/accommodations", {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${token}`,
            },
        });
        if (!res.ok) throw new Error("Error al cargar alojamientos");
        const data = await res.json();
        allAccommodations = Array.isArray(data)
            ? data
            : data.accommodations || data.data || [];

        renderAccommodationsTable(allAccommodations);
    } catch (err) {
        console.error("Error cargando alojamientos:", err);
    }
}

// Cargar destinos en los selectores
async function loadDestinationsForAccSelect() {
    try {
        const res = await fetch("/api/admin/destinations", {
            headers: {
                Accept: "application/json",
                Authorization: `Bearer ${token}`,
            },
        });
        if (!res.ok) return;
        const data = await res.json();
        const destinations = Array.isArray(data)
            ? data
            : data.destinations || data.data || [];

        const createSelect = document.getElementById(
            "create_acc_destination_id",
        );
        const editSelect = document.getElementById("edit_acc_destination_id");
        const optionsHtml =
            '<option value="" disabled selected>Busca y selecciona un destino...</option>' +
            destinations
                .map(
                    (d) =>
                        `<option value="${d.id}">${d.name} (${d.department})</option>`,
                )
                .join("");

        if (createSelect) createSelect.innerHTML = optionsHtml;
        if (editSelect) editSelect.innerHTML = optionsHtml;
    } catch (err) {
        console.error("Error al cargar destinos:", err);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    loadAccommodations();
    loadDestinationsForAccSelect();
});

// Filtro de búsqueda en tiempo real para Alojamientos
const searchAccommodationInput = document.getElementById(
    "searchAccommodationInput",
);
if (searchAccommodationInput) {
    searchAccommodationInput.addEventListener("input", (e) => {
        const query = e.target.value.toLowerCase();
        const filtered = allAccommodations.filter((item) => {
            const nameMatch =
                item.name && item.name.toLowerCase().includes(query);
            const typeMatch =
                item.type && item.type.toLowerCase().includes(query);
            const addressMatch =
                item.address && item.address.toLowerCase().includes(query);
            const destMatch =
                item.destination &&
                ((item.destination.municipality &&
                    item.destination.municipality
                        .toLowerCase()
                        .includes(query)) ||
                    (item.destination.department &&
                        item.destination.department
                            .toLowerCase()
                            .includes(query)));
            return nameMatch || typeMatch || addressMatch || destMatch;
        });
        renderAccommodationsTable(filtered);
    });
}

// Función auxiliar para aplicar vista previa de imagen en el contenedor punteado
function setupImagePreview(inputId) {
    const inputElement = document.getElementById(inputId);
    if (!inputElement) return;

    inputElement.addEventListener("change", (e) => {
        const file = e.target.files[0];
        const container = inputElement.closest(".border-dashed");
        const previewText =
            container?.querySelector(".fw-semibold") ||
            container?.querySelector("span.small");

        if (file && container) {
            const reader = new FileReader();
            reader.onload = (ev) => {
                container.style.backgroundImage = `url('${ev.target.result}')`;
                container.style.backgroundSize = "cover";
                container.style.backgroundPosition = "center";
                if (previewText)
                    previewText.textContent = `Seleccionada: ${file.name}`;
            };
            reader.readAsDataURL(file);
        }
    });
}

// Inicializar vistas previas para Crear y Editar
setupImagePreview("acc_image_url");
setupImagePreview("edit_acc_image_url");

//  Enviar Formulario de Creación
const createAccForm = document.getElementById("createAccommodationForm");
if (createAccForm) {
    createAccForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const formData = new FormData(createAccForm);
        Swal.fire({
            title: "Guardando...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const res = await fetch("/api/admin/accommodations", {
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

            window.bootstrap.Modal.getInstance(
                document.getElementById("createAccommodationModal"),
            )?.hide();
            createAccForm.reset();

            const createContainer = document.querySelector(
                "#createAccommodationModal .border-dashed",
            );
            if (createContainer) {
                createContainer.style.backgroundImage = "none";
                const txt = createContainer.querySelector(".fw-semibold");
                if (txt)
                    txt.textContent =
                        "Arrastra una imagen aquí o haz clic para seleccionar";
            }

            Swal.fire({
                icon: "success",
                title: "¡Éxito!",
                timer: 1200,
                showConfirmButton: false,
            }).then(() => loadAccommodations());
        } catch (err) {
            Swal.fire("Error", "No se pudo registrar el alojamiento.", "error");
        }
    });
}

//  Abrir modal de Edición y cargar datos
document.addEventListener("click", async (e) => {
    const editBtn = e.target.closest(".edit-btn");
    if (editBtn) {
        const id = editBtn.dataset.id;
        try {
            const res = await fetch(`/api/admin/accommodations/${id}`, {
                headers: {
                    Accept: "application/json",
                    Authorization: `Bearer ${token}`,
                },
            });
            if (!res.ok) throw new Error("No se pudo obtener el registro");
            const data = await res.json();
            const item = data.accommodation || data;

            document.getElementById("edit_acc_id").value = item.id;
            document.getElementById("edit_acc_destination_id").value =
                item.destination_id || "";
            document.getElementById("edit_acc_reserva").value =
                item.reserva || "";
            document.getElementById("edit_acc_price").value = item.price || "";
            document.getElementById("edit_acc_name").value = item.name || "";
            document.getElementById("edit_acc_address").value =
                item.address || "";
            document.getElementById("edit_acc_contact_phone").value =
                item.contact_phone || "";
            document.getElementById("edit_acc_email").value = item.email || "";
            document.getElementById("edit_acc_type").value =
                item.type || "hotel";
            document.getElementById("edit_acc_check_in").value =
                item.check_in || "";
            document.getElementById("edit_acc_check_out").value =
                item.check_out || "";

            const statusVal = item.status || "activo";
            document.getElementById("edit_acc_status").value = statusVal;
            const statusSwitch = document.getElementById(
                "edit_acc_status_switch",
            );
            if (statusSwitch) statusSwitch.checked = statusVal === "activo";

            const editContainer = document.querySelector(
                "#editAccommodationModal .border-dashed",
            );
            if (editContainer) {
                if (item.image_url) {
                    editContainer.style.backgroundImage = `url('${item.image_url}')`;
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

            window.bootstrap.Modal.getOrCreateInstance(
                document.getElementById("editAccommodationModal"),
            ).show();
        } catch (err) {
            Swal.fire("Error", "No se pudieron cargar los datos.", "error");
        }
    }
});

// Enviar Formulario de Edición
const editAccForm = document.getElementById("editAccommodationForm");
if (editAccForm) {
    editAccForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = document.getElementById("edit_acc_id").value;
        const formData = new FormData(editAccForm);

        Swal.fire({
            title: "Actualizando...",
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading(),
        });

        try {
            const res = await fetch(`/api/admin/accommodations/${id}`, {
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

            window.bootstrap.Modal.getInstance(
                document.getElementById("editAccommodationModal"),
            )?.hide();
            Swal.fire({
                icon: "success",
                title: "¡Actualizado!",
                timer: 1200,
                showConfirmButton: false,
            }).then(() => loadAccommodations());
        } catch (err) {
            Swal.fire(
                "Error",
                "No se pudo actualizar el alojamiento.",
                "error",
            );
        }
    });
}

// Desactivar registro
document.addEventListener("click", async (e) => {
    const deleteBtn = e.target.closest(".delete-btn");
    if (deleteBtn) {
        const id = deleteBtn.dataset.id;
        const confirm = await Swal.fire({
            title: "¿Desactivar alojamiento?",
            text: "El registro cambiará su estado a inactivo.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#0D2A4D",
            cancelButtonColor: "#d33",
            confirmButtonText: "Sí, desactivar",
        });

        if (confirm.isConfirmed) {
            try {
                const res = await fetch(`/api/admin/accommodations/${id}`, {
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
                    timer: 1000,
                    showConfirmButton: false,
                }).then(() => loadAccommodations());
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
