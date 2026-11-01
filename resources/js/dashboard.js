document.addEventListener('DOMContentLoaded', async function () {
    // Colores de la guía de estilo
    const COLORS = ['#0d2a4d', '#2a9d8f', '#f26419', '#6c8ebf', '#adb5bd'];

    const daysFilter = document.getElementById('daysFilter');
    let destinationsChart = null;
    let travelersChart = null;

    await loadStats();

    // El filtro solo afecta el gráfico de destinos, pero se recarga todo con una sola petición
    daysFilter.addEventListener('change', loadStats);

    async function loadStats() {
        try {
            const res = await window.api(`/admin/dashboard?days=${daysFilter.value}`);
            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            const stats = await res.json();

            renderCounters(stats.counters);
            renderDestinationsChart(stats.top_destinations);
            renderTravelersChart(stats.traveler_types);
        } catch (error) {
            console.error(error);
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron cargar las estadísticas.' });
        }
    }

    // ===== Tarjetas =====
    function renderCounters(counters) {
        Object.entries(counters).forEach(([key, counter]) => {
            document.getElementById(`count-${key}`).textContent = counter.total.toLocaleString('es-SV');
            document.getElementById(`trend-${key}`).innerHTML = trendHTML(counter);
        });
    }

        // "+12% vs. mes anterior" o, si no había registros antes de este mes, "+3 este mes"
    function trendHTML(counter) {
        if (counter.trend === null) {
            return counter.this_month > 0
                ? `<i class="bi bi-arrow-up-right text-success"></i> <span class="text-success">+${counter.this_month}</span> este mes`
                : 'Sin registros aún';
        }

        if (counter.trend === 0) {
            return `<i class="bi bi-dash"></i> 0% vs. mes anterior`;
        }

        const up = counter.trend > 0;
        const color = up ? 'text-success' : 'text-danger';
        const icon = up ? 'bi-arrow-up-right' : 'bi-arrow-down-right';
        return `<i class="bi ${icon} ${color}"></i> <span class="${color}">${up ? '+' : ''}${counter.trend}%</span> vs. mes anterior`;
    }

    // ===== Barras: destinos más visitados =====
    function renderDestinationsChart(data) {
        const canvas = document.getElementById('destinationsChart');
        const emptyMsg = document.getElementById('noDestinationsMsg');
        const isEmpty = data.labels.length === 0;

        canvas.classList.toggle('d-none', isEmpty);
        emptyMsg.classList.toggle('d-none', !isEmpty);
        emptyMsg.classList.toggle('d-flex', isEmpty);

        if (destinationsChart) destinationsChart.destroy();
        if (isEmpty) return;

        destinationsChart = new Chart(canvas, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Veces agregado',
                    data: data.data,
                    backgroundColor: data.labels.map((_, i) => COLORS[i % 3]),
                    borderRadius: 4,
                    maxBarThickness: 60,
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ===== Dona: tipos de viajero =====
    function renderTravelersChart(data) {
        const total = data.data.reduce((sum, value) => sum + value, 0);
        document.getElementById('travelersTotal').textContent = total.toLocaleString('es-SV');

        // Leyenda con cantidad y porcentaje (igual al diseño)
        document.getElementById('travelersLegend').innerHTML = data.labels.map((label, i) => {
            const value = data.data[i];
            const percent = total ? Math.round(value / total * 100) : 0;
            return `
                <li class="d-flex align-items-center py-1">
                    <span class="rounded-circle me-2 flex-shrink-0" style="width: 12px; height: 12px; background: ${COLORS[i]};"></span>
                    <span class="flex-grow-1">${label}</span>
                    <span class="fw-semibold me-4">${value}</span>
                    <span class="text-muted" style="width: 48px;">(${percent}%)</span>
                </li>`;
        }).join('');

        if (travelersChart) travelersChart.destroy();

        travelersChart = new Chart(document.getElementById('travelersChart'), {
            type: 'doughnut',
            data: {
                labels: data.labels,
                datasets: [{
                    data: data.data,
                    backgroundColor: COLORS.slice(0, data.labels.length),
                    borderWidth: 2,
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: { legend: { display: false } }
            }
        });
    }
});