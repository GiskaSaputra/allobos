// Warna grafik dibaca dari token CSS supaya ikut tema.
function token(name, alpha = 1) {
    const value = getComputedStyle(document.documentElement).getPropertyValue(`--${name}`).trim();
    return `rgb(${value} / ${alpha})`;
}

export async function initCharts() {
    const canvases = document.querySelectorAll('canvas[data-chart]');
    if (!canvases.length) return;
    const { default: Chart } = await import('chart.js/auto');
    const charts = [];

    const build = (canvas) => {
        const config = JSON.parse(canvas.dataset.chart);
        return new Chart(canvas, {
            type: config.type,
            data: {
                labels: config.labels,
                datasets: config.series.map((s) => ({
                    label: s.label,
                    data: s.data,
                    borderColor: token(s.color ?? 'brand'),
                    backgroundColor: token(s.color ?? 'brand', config.type === 'line' ? 0.12 : 0.8),
                    fill: config.type === 'line',
                    tension: 0.35,
                    borderRadius: 8,
                    pointRadius: 3,
                })),
            },
            options: {
                maintainAspectRatio: false,
                animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? false : undefined,
                indexAxis: config.horizontal ? 'y' : 'x',
                plugins: { legend: { display: config.series.length > 1, labels: { color: token('fg-body') } } },
                scales: {
                    x: { ticks: { color: token('fg-muted') }, grid: { color: token('line', 0.5) } },
                    y: { beginAtZero: true, max: config.max, ticks: { color: token('fg-muted'), precision: 0 }, grid: { color: token('line', 0.5) } },
                },
            },
        });
    };

    canvases.forEach((canvas) => charts.push([canvas, build(canvas)]));
    document.addEventListener('tema', () => charts.forEach((entry) => {
        entry[1].destroy();
        entry[1] = build(entry[0]);
    }));
}
