document.addEventListener('DOMContentLoaded', async function () {
    const searchInput = document.getElementById('searchInput');
    const container = document.getElementById('reportsContainer');
    const noResultsMsg = document.getElementById('noResultsMsg');
    const showingCount = document.getElementById('showingCount');
    const loadingMsg = document.getElementById('loadingMsg');

    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const pageIndicator = document.getElementById('pageIndicator');

    let allReports = [];
    let filteredReports = [];

    let currentPage = 1;
    const itemsPerPage = 5;

    try {
        const res = await window.api('/admin/reports');
        if (res.ok) {
            const rawReports = await res.json();

            // Crear un Set para llevar el registro de las combinaciones únicas
            const uniqueCombos = new Set();

            // Filtrar el arreglo original para dejar solo uno por combinación
            allReports = rawReports.filter(report => {
                const reviewId = report.review ? report.review.id : report.review_id;
                const nota = report.nota || '';

                // Crear un identificador único para esta combinación
                const comboKey = `${reviewId}-${nota}`;

                // Si la combinación ya existe en el Set, es un duplicado y lo ignoramos
                if (uniqueCombos.has(comboKey)) {
                    return false;
                }

                // Si es la primera vez que lo vemos, lo añadimos al Set y lo conservamos
                uniqueCombos.add(comboKey);
                return true;
            });

            filteredReports = allReports;
            renderReports();
        } else {
            loadingMsg.textContent = "Error al cargar los reportes.";
        }
    } catch (error) {
        console.error(error);
        loadingMsg.textContent = "Error de conexión.";
    }

    //Función para renderizar
    function renderReports() {
        container.innerHTML = '';

        if (filteredReports.length === 0) {
            noResultsMsg.style.display = 'block';
            showingCount.textContent = `Mostrando 0 reportes`;
            updatePaginationControls(0);
            return;
        }

        noResultsMsg.style.display = 'none';

        const totalPages = Math.ceil(filteredReports.length / itemsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;

        const reportsToRender = filteredReports.slice(startIndex, endIndex);

        showingCount.textContent = `Mostrando ${startIndex + 1} al ${Math.min(endIndex, filteredReports.length)} de ${filteredReports.length} reportes`;

        reportsToRender.forEach(report => {
            const reportedUser = report.review && report.review.user ? report.review.user.name : 'Usuario Desconocido';
            const avatar = reportedUser.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
            const date = new Date(report.created_at).toLocaleDateString('es-ES');
            const reviewComment = (report.review && report.review.comment) ? report.review.comment : 'Sin comentario de texto.';
            const reportReason = report.type_report || 'Sin especificar';
            const reportNota = report.nota || '';

            const cardHTML = `
                <div class="card border border-light-subtle rounded-3 shadow-sm report-card">
                    <div class="card-body p-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <div class="d-flex align-items-start w-100">
                            <div class="bg-secondary rounded-circle d-flex justify-content-center align-items-center text-white fw-bold me-3 flex-shrink-0 mt-1" 
                                 style="width: 52px; height: 52px; font-size: 1.1rem;">
                                ${avatar}
                            </div>
                            <div class="w-100">
                                <div class="d-flex align-items-center mb-2 gap-2">
                                    <h5 class="mb-0 fw-bold user-name text-primary">${reportedUser}</h5>
                                    <span class="text-body-color" style="font-size: 0.9rem;">${date}</span>
                                </div>
                                <p class="text-dark mb-2 comment-text" style="font-size: 1rem; line-height: 1.4;">
                                    "${reviewComment}"
                                </p>
                                <p class="text-danger mb-0" style="font-size: 0.9rem; line-height: 1.4;">
                                    <strong>Motivo de reporte:</strong> ${reportNota}
                                </p>
                            </div>
                        </div>
                       <div class="d-flex justify-content-end gap-3 flex-shrink-0">
                            <button type="button" class="btn bg-secondary border-0 fw-medium px-3 btn-discard text-dark" 
                                    data-id="${report.id}" 
                                    data-review-id="${report.review ? report.review.id : report.review_id}" 
                                    data-nota="${reportNota}">
                                Descartar reporte
                            </button>
                            <button type="button" class="btn btn-accent text-white fw-medium px-3 btn-delete-report" 
                                    data-id="${report.id}" 
                                    data-review-id="${report.review ? report.review.id : report.review_id}" 
                                    data-nota="${reportNota}">
                                Confirmar reporte
                            </button>
                        </div>
                    </div>
                </div>`;

            container.insertAdjacentHTML('beforeend', cardHTML);
        });

        updatePaginationControls(totalPages);
    }

    //Controles de Paginación
    function updatePaginationControls(totalPages) {
        pageIndicator.textContent = currentPage;
        prevBtn.disabled = currentPage === 1 || totalPages === 0;
        nextBtn.disabled = currentPage === totalPages || totalPages === 0;
    }

    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderReports();
        }
    });

    nextBtn.addEventListener('click', () => {
        const totalPages = Math.ceil(filteredReports.length / itemsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderReports();
        }
    });

    // Filtro de Búsqueda
    searchInput.addEventListener('input', function () {
        const filter = this.value.toLowerCase();

        filteredReports = allReports.filter(report => {
            const name = report.review && report.review.user ? report.review.user.name.toLowerCase() : '';
            const reviewComment = (report.review && report.review.comment) ? report.review.comment.toLowerCase() : 'sin comentario de texto.';
            const reportReason = (report.nota || report.type_report || '').toLowerCase();
            return name.includes(filter) || reviewComment.includes(filter) || reportReason.includes(filter);
        });

        currentPage = 1;
        renderReports();
    });

    // Lógica para botones de la tarjeta (Descartar y Eliminar)
    document.addEventListener('click', function (e) {

        // --- LÓGICA: DESCARTAR REPORTE (Cambio de estado) ---
        const discardBtn = e.target.closest('.btn-discard');
        if (discardBtn) {
            e.preventDefault();
            const reportId = discardBtn.getAttribute('data-id');

            // OBTENER ATRIBUTOS DE AGRUPACIÓN
            const reviewId = discardBtn.getAttribute('data-review-id');
            const nota = discardBtn.getAttribute('data-nota');

            Swal.fire({
                title: '¿Descartar este reporte?',
                text: "El reporte dejará de ser visible en esta lista.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2a9d8f',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Sí, descartar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Procesando...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
                    try {
                        const res = await window.api(`/admin/reports/${reportId}/status`, {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ status: 'descartado' })
                        });
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);

                        await new Promise(resolve => setTimeout(resolve, 500));

                        // LLAMADO A FUNCIÓN QUE BORRA EN LOTE DE RESEÑAS 
                        removeGroupFromView(reviewId, nota, false);

                        Swal.fire({ icon: 'success', title: '¡Descartado!', showConfirmButton: false, timer: 1500 });
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: error.message });
                    }
                }
            });
        }

        // --- LÓGICA: CONFIRMAR REPORTE (Ocultar reseña) ---
        const confirmBtn = e.target.closest('.btn-delete-report');
        if (confirmBtn) {
            e.preventDefault();
            const reportId = confirmBtn.getAttribute('data-id');

            // OBTENER ATRIBUTOS DE AGRUPACIÓN
            const reviewId = confirmBtn.getAttribute('data-review-id');
            const nota = confirmBtn.getAttribute('data-nota');

            Swal.fire({
                title: '¿Confirmar este reporte?',
                text: "La reseña original será ocultada y este reporte será confirmado.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, confirmar y ocultar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Procesando...', text: 'Actualizando registros...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                    try {
                        const res = await window.api(`/admin/reports/${reportId}/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ status: 'confirmado' })
                        });

                        if (!res.ok) {
                            const errorData = await res.json().catch(() => ({}));
                            const errorMsg = errorData.message || res.statusText || 'Error desconocido';
                            throw new Error(`HTTP ${res.status}: ${errorMsg}`);
                        }

                        await new Promise(resolve => setTimeout(resolve, 500));

                        // LLAMADO A LA FUNCIÓN QUE BORRA EN LOTE
                        removeGroupFromView(reviewId, nota, true);

                        Swal.fire({ icon: 'success', title: '¡Confirmado!', text: 'La reseña fue ocultada exitosamente.', showConfirmButton: false, timer: 1500 });
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error al confirmar', text: error.message });
                    }
                }
            });
        }
    });

    function removeGroupFromView(reviewId, nota, isConfirmed) {
        allReports = allReports.filter(r => {
            const rReviewId = (r.review ? r.review.id : r.review_id).toString();
            const rNota = r.nota || '';

            if (isConfirmed) {
                // Si se confirmó, eliminamos de la vista TODOS los reportes de esta reseña
                return rReviewId !== reviewId.toString();
            } else {
                // Si se descartó, eliminamos solo los que coincidan en reseña + nota
                const isMatch = (rReviewId === reviewId.toString() && rNota === nota);
                return !isMatch;
            }
        });

        const filter = searchInput.value.toLowerCase();
        filteredReports = allReports.filter(report => {
            const name = report.review && report.review.user ? report.review.user.name.toLowerCase() : '';
            const reviewComment = (report.review && report.review.comment) ? report.review.comment.toLowerCase() : '';
            const reportReason = (report.nota || report.type_report || '').toLowerCase();
            return name.includes(filter) || reviewComment.includes(filter) || reportReason.includes(filter);
        });

        const totalPages = Math.ceil(filteredReports.length / itemsPerPage);
        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;

        renderReports();
    }

});