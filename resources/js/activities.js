import * as bootstrap from "bootstrap";

document.addEventListener("DOMContentLoaded", async function () {
    const tableBody = document.getElementById("activitiesTableBody");
    const searchInput = document.getElementById("searchInput");
    const destinationFilter = document.getElementById("destinationFilter");
    const showingCount = document.getElementById("showingCount");

    const modalEl = document.getElementById("activityModal");
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    const modalTitle = document.getElementById("activityModalLabel");
    const form = document.getElementById("activityForm");
    const saveBtn = document.getElementById("saveActivityBtn");

    const fields = {
        id: document.getElementById("activity_id"),
        name: document.getElementById("activity_name"),
        destination: document.getElementById("destination_id"),
        price: document.getElementById("price"),
        people: document.getElementById("people"),
        duracion: document.getElementById("duracion"),
        notes: document.getElementById("notes"),
        status: document.getElementById("status_switch"),
    };

    const statusGroup = document.getElementById("statusGroup");
    const statusLabel = document.getElementById("statusLabel");

    // Texto del interruptor según su posición
    fields.status.addEventListener("change", () => {
        statusLabel.textContent = fields.status.checked ? "Activo" : "Inactivo";
    });

    let allActivities = [];

    // Evita que un texto con etiquetas HTML se dibuje como HTML
    const esc = (value) => {
        const div = document.createElement("div");
        div.textContent = value ?? "";
        return div.innerHTML;
    };

    // "02:30:00" -> "2 h 30 min"
    const formatDuration = (time) => {
        if (!time) return "-";
        const [h, m] = time.split(":").map(Number);
        if (h && m) return `${h} h ${m} min`;
        return h ? `${h} h` : `${m} min`;
    };

    // ===== Carga inicial =====
    await Promise.all([loadDestinations(), loadActivities()]);

    // Destinos para el filtro y el formulario (solo activos en el formulario)
    async function loadDestinations() {
        try {
            const res = await window.api("/admin/destinations");
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const destinations = await res.json();

            destinations.forEach((dest) => {
                destinationFilter.insertAdjacentHTML(
                    "beforeend",
                    `<option value="${dest.id}">${esc(dest.name)}</option>`,
                );

                if (dest.status === "activo") {
                    fields.destination.insertAdjacentHTML(
                        "beforeend",
                        `<option value="${dest.id}">${esc(dest.name)}</option>`,
                    );
                }
            });
        } catch (error) {
            console.error("Destinos:", error);
        }
    }

    async function loadActivities() {
        try {
            const res = await window.api("/admin/activities");
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            allActivities = await res.json();
            renderActivities();
        } catch (error) {
            console.error(error);
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Error al cargar las actividades.</td></tr>`;
        }
    }

    // ===== Renderizado de la tabla =====
    function renderActivities() {
        const text = searchInput.value.toLowerCase();
        const destId = destinationFilter.value;

        const filtered = allActivities.filter((act) => {
            const matchesText =
                act.activity_name.toLowerCase().includes(text) ||
                (act.destination?.name || "").toLowerCase().includes(text);
            const matchesDest =
                !destId || String(act.destination_id) === destId;
            return matchesText && matchesDest;
        });

        showingCount.textContent = `Mostrando ${filtered.length} de ${allActivities.length} actividades`;

        if (filtered.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">No se encontraron actividades.</td></tr>`;
            return;
        }

        tableBody.innerHTML = filtered
            .map((act) => {
                const isActive = act.status === "activo";

                // Mismo estilo de insignias, botones y celdas que la tabla de Destinos
                const statusBadge = isActive
                    ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Activo</span>`
                    : `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-x-circle-fill me-1"></i> Inactivo</span>`;

                const deleteBtn = isActive
                    ? `<button class="btn btn-sm btn-light border btn-delete" data-id="${act.id}" title="Desactivar">
                        <i class="bi bi-trash text-danger"></i>
                   </button>`
                    : "";

                return `
                <tr>
                    <td class="px-4 fw-bold text-secondary">#${String(act.id).padStart(3, "0")}</td>
                    <td>
                        <div class="fw-bold text-dark">${esc(act.activity_name)}</div>
                        <div class="small text-muted text-truncate" style="max-width: 260px;">${esc(act.notes || "")}</div>
                    </td>
                    <td class="text-muted">${esc(act.destination?.name || "Sin destino")}</td>
                    <td class="fw-semibold text-dark">$${Number(act.price).toFixed(2)}</td>
                    <td class="text-muted"><i class="bi bi-people me-1"></i>${act.people}</td>
                    <td class="text-muted"><i class="bi bi-clock me-1"></i>${formatDuration(act.duracion)}</td>
                    <td>${statusBadge}</td>
                    <td class="text-end px-4 text-nowrap">
                        <button class="btn btn-sm btn-light border me-1 btn-edit" data-id="${act.id}" title="Editar">
                            <i class="bi bi-pencil text-primary"></i>
                        </button>
                        ${deleteBtn}
                    </td>
                </tr>`;
            })
            .join("");
    }

    searchInput.addEventListener("input", renderActivities);
    destinationFilter.addEventListener("change", renderActivities);

    // ===== Abrir modal: crear =====
    document.getElementById("newActivityBtn").addEventListener("click", () => {
        form.reset();
        form.classList.remove("was-validated");
        fields.id.value = "";
        statusGroup.classList.add("d-none"); // una actividad nueva siempre nace activa
        modalTitle.textContent = "Agregar Actividad";
        saveBtn.textContent = "Guardar Actividad";
        modal.show();
    });

    // ===== Botones de la tabla: editar y desactivar =====
    tableBody.addEventListener("click", (e) => {
        const editBtn = e.target.closest(".btn-edit");
        if (editBtn) openEdit(editBtn.dataset.id);

        const deleteBtn = e.target.closest(".btn-delete");
        if (deleteBtn) confirmDelete(deleteBtn.dataset.id);
    });

    // Usa los datos ya cargados en memoria (no hace otra petición)
    function openEdit(id) {
        const act = allActivities.find((a) => a.id == id);
        if (!act) return;

        form.classList.remove("was-validated");
        fields.id.value = act.id;
        fields.name.value = act.activity_name;
        fields.price.value = Number(act.price).toFixed(2);
        fields.people.value = act.people;
        fields.duracion.value = (act.duracion || "").slice(0, 5); // "02:30:00" -> "02:30"
        fields.notes.value = act.notes || "";

        // Si el destino está inactivo, se agrega temporalmente para no perder el valor
        if (
            !fields.destination.querySelector(
                `option[value="${act.destination_id}"]`,
            )
        ) {
            fields.destination.insertAdjacentHTML(
                "beforeend",
                `<option value="${act.destination_id}">${esc(act.destination?.name || "Destino inactivo")}</option>`,
            );
        }
        fields.destination.value = act.destination_id;

        // Estado: permite reactivar una actividad desactivada
        fields.status.checked = act.status === "activo";
        statusLabel.textContent = fields.status.checked ? "Activo" : "Inactivo";
        statusGroup.classList.remove("d-none");

        modalTitle.textContent = "Editar Actividad";
        saveBtn.textContent = "Guardar Cambios";
        modal.show();
    }

    // ===== Guardar (crear o editar) =====
    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.classList.add("was-validated");
            return;
        }

        const id = fields.id.value;
        const payload = {
            destination_id: fields.destination.value,
            activity_name: fields.name.value.trim(),
            notes: fields.notes.value.trim() || null,
            people: fields.people.value,
            price: fields.price.value,
            duracion: fields.duracion.value,
        };

        // Solo al editar se envía el estado del interruptor
        if (id) payload.status = fields.status.checked ? "activo" : "inactivo";

        saveBtn.disabled = true;

        try {
            const res = await window.api(
                id ? `/admin/activities/${id}` : "/admin/activities",
                {
                    method: id ? "PUT" : "POST",
                    body: JSON.stringify(payload),
                },
            );
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
            if (!res.ok) throw new Error(data.message || `HTTP ${res.status}`);

            modal.hide();
            form.classList.remove("was-validated");
            await loadActivities();

            Swal.fire({
                icon: "success",
                title: data.message,
                showConfirmButton: false,
                timer: 1500,
            });
        } catch (error) {
            Swal.fire({ icon: "error", title: "Error", text: error.message });
        } finally {
            saveBtn.disabled = false;
        }
    });

    // ===== Desactivar (eliminado lógico) =====
    function confirmDelete(id) {
        Swal.fire({
            title: "¿Desactivar esta actividad?",
            text: "Dejará de estar disponible para agregarse a nuevos itinerarios.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#f26419",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, desactivar",
            cancelButtonText: "Cancelar",
        }).then(async (result) => {
            if (!result.isConfirmed) return;

            try {
                const res = await window.api(`/admin/activities/${id}`, {
                    method: "DELETE",
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok)
                    throw new Error(data.message || `HTTP ${res.status}`);

                await loadActivities();
                Swal.fire({
                    icon: "success",
                    title: "¡Desactivada!",
                    showConfirmButton: false,
                    timer: 1500,
                });
            } catch (error) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: error.message,
                });
            }
        });
    }
});