let chartPromise = null;

export function loadChart() {
    if (chartPromise) return chartPromise;
    chartPromise = new Promise((resolve, reject) => {
        if (window.Chart) return resolve(window.Chart);
        const s = document.createElement('script');
        s.src = '/vendor/chartjs/chart.umd.js';
        s.onload = () => resolve(window.Chart);
        s.onerror = () => reject(new Error('Failed to load Chart.js'));
        document.head.appendChild(s);
    });
    return chartPromise;
}
