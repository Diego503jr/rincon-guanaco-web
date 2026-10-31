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
            allReports = await res.json();
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
            const reportReason = report.nota || report.type_report;

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
                                    <span class="badge rounded-pill px-3 py-1" style="background-color: #dc2626 !important; font-size: 0.75rem;">${report.type_report}</span>
                                </div>
                                <p class="text-dark mb-2 comment-text" style="font-size: 1rem; line-height: 1.4;">
                                    "${reviewComment}"
                                </p>
                                <p class="text-danger mb-0" style="font-size: 0.9rem; line-height: 1.4;">
                                    <strong>Nota de reporte:</strong> ${reportReason}
                                </p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-3 flex-shrink-0">
                            <button type="button" class="btn bg-secondary text-primary fw-medium border px-3 btn-discard" data-id="${report.id}">
                                Descartar reporte
                            </button>
                            <button type="button" class="btn btn-accent text-white fw-medium px-3 btn-delete-report" data-id="${report.id}">
                                Eliminar reporte
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
                            body: JSON.stringify({ status: 'inactivo' })
                        });
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);

                        await new Promise(resolve => setTimeout(resolve, 500));
                        removeFromView(reportId);

                        Swal.fire({ icon: 'success', title: '¡Descartado!', showConfirmButton: false, timer: 1500 });
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error', text: error.message });
                    }
                }
            });
        }

        // --- LÓGICA: ELIMINAR REPORTE FÍSICAMENTE ---
        const deleteBtn = e.target.closest('.btn-delete-report');
        if (deleteBtn) {
            e.preventDefault();
            const reportId = deleteBtn.getAttribute('data-id');

            Swal.fire({
                title: '¿Eliminar reporte permanentemente?',
                text: "Esta acción borrará completamente el reporte de la base de datos. La reseña original no será afectada.",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Eliminando...', text: 'Borrando registro...', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });

                    try {
                        // Petición DELETE directa al ID del reporte
                        const res = await window.api(`/admin/reports/${reportId}`, {
                            method: 'DELETE'
                        });

                        if (!res.ok) {
                            const errorData = await res.json().catch(() => ({}));
                            const errorMsg = errorData.message || res.statusText || 'Error desconocido';
                            throw new Error(`HTTP ${res.status}: ${errorMsg}`);
                        }

                        await new Promise(resolve => setTimeout(resolve, 500));
                        removeFromView(reportId);

                        Swal.fire({ icon: 'success', title: '¡Eliminado!', text: 'El reporte fue borrado completamente.', showConfirmButton: false, timer: 1500 });
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Error al eliminar', text: error.message });
                    }
                }
            });
        }
    });

    // Función auxiliar para actualizar la memoria y la vista (reutilizable)
    function removeFromView(reportId) {
        allReports = allReports.filter(r => r.id != reportId);

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