document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('reportForm');
    const fromInput = document.getElementById('from');
    const toInput = document.getElementById('to');
    const typeSelect = document.getElementById('type');
    const previewBtn = document.getElementById('previewBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const frame = document.getElementById('previewFrame');
    const loading = document.getElementById('previewLoading');

    let currentUrl = null; // URL temporal del último PDF generado
    const loadingHTML = loading.innerHTML; // para restaurar el spinner después de un error

    // Fecha local en formato YYYY-MM-DD (toISOString usa UTC y puede cambiar el día)
    const toInputDate = (date) => {
        const pad = (n) => String(n).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
    };

    // Por defecto: del 1 de enero del año actual a hoy
    const today = new Date();
    fromInput.value = toInputDate(new Date(today.getFullYear(), 0, 1));
    toInput.value = toInputDate(today);
    toInput.max = toInputDate(today);

    // Valida en el navegador antes de pedir el PDF
    function isValid() {
        if (!fromInput.value || !toInput.value) {
            Swal.fire({ icon: 'warning', title: 'Fechas incompletas', text: 'Selecciona el rango de fechas.' });
            return false;
        }
        if (fromInput.value > toInput.value) {
            Swal.fire({ icon: 'warning', title: 'Rango inválido', text: 'La fecha "Desde" no puede ser mayor que "Hasta".' });
            return false;
        }
        return true;
    }

    // Pide el PDF a la API y devuelve un Blob
    async function fetchPdf() {
        const params = new URLSearchParams({
            type: typeSelect.value,
            from: fromInput.value,
            to: toInput.value,
        });

        const res = await window.api(`/admin/pdf-reports?${params}`);

        if (res.status === 422) {
            const data = await res.json();
            throw new Error(Object.values(data.errors ?? {}).flat().join('\n'));
        }
        if (!res.ok) throw new Error(`HTTP ${res.status}`);

        return res.blob();
    }

    // ===== Vista previa =====
    async function loadPreview() {
        if (!isValid()) return;

        loading.innerHTML = loadingHTML;
        loading.classList.remove('d-none');
        frame.classList.add('d-none');
        previewBtn.disabled = true;

        try {
            const blob = await fetchPdf();

            if (currentUrl) URL.revokeObjectURL(currentUrl); // libera el PDF anterior
            currentUrl = URL.createObjectURL(blob);

            frame.src = currentUrl;
            frame.classList.remove('d-none');
            loading.classList.add('d-none');
        } catch (error) {
            console.error(error);
            loading.textContent = 'No se pudo generar la vista previa.';
            Swal.fire({ icon: 'error', title: 'Error', text: error.message });
        } finally {
            previewBtn.disabled = false;
        }
    }

    // ===== Descargar =====
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!isValid()) return;

        downloadBtn.disabled = true;
        Swal.fire({ title: 'Generando PDF...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const blob = await fetchPdf();
            const url = URL.createObjectURL(blob);

            // Enlace temporal para forzar la descarga con un nombre claro
            const link = document.createElement('a');
            link.href = url;
            link.download = `reporte-${typeSelect.value}-${fromInput.value}-a-${toInput.value}.pdf`;
            document.body.appendChild(link);
            link.click();
            link.remove();
            URL.revokeObjectURL(url);

            Swal.fire({ icon: 'success', title: '¡Reporte descargado!', showConfirmButton: false, timer: 1500 });
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: 'error', title: 'Error', text: error.message });
        } finally {
            downloadBtn.disabled = false;
        }
    });

    // La vista previa se actualiza sola al cambiar un filtro, o con el botón del ojo
    previewBtn.addEventListener('click', loadPreview);
    [fromInput, toInput, typeSelect].forEach(input => input.addEventListener('change', loadPreview));

    loadPreview();
});