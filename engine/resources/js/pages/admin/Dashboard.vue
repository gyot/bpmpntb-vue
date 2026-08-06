<template>
    <div>
        <!-- Year selector -->
        <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
            <h2 class="text-lg font-bold" style="color:var(--color-text-primary)">
                <i class="fas fa-tachometer-alt mr-2" style="color:var(--color-primary)"></i>Dashboard
            </h2>
            <div class="flex items-center gap-2">
                <select v-model="selectedYear" @change="fetchStats" class="year-select">
                    <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                </select>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div v-for="item in kategoriCards" :key="item.jenis" class="card-k stat-card" :style="{'border-left-color': item.color}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm mb-1" style="color:var(--color-text-secondary)">{{ item.label }}</p>
                        <h3 class="text-2xl font-bold" style="color:var(--color-text-primary)">{{ item.total }}</h3>
                    </div>
                    <div class="stat-icon" :style="{background: item.color + '18'}">
                        <i :class="item.icon" class="text-xl" :style="{color: item.color}"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts: Line + Doughnut -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="card-k p-6 chart-card">
                <button @click="copyChart(lineChartRef)" class="chart-copy-btn" title="Copy grafik"><i class="fas fa-image"></i></button>
                <canvas ref="lineChartRef" height="280"></canvas>
            </div>
            <div class="card-k p-6 chart-card">
                <button @click="copyChart(pieChartRef)" class="chart-copy-btn" title="Copy grafik"><i class="fas fa-image"></i></button>
                <div class="flex justify-center mb-4">
                    <div style="width:220px;height:220px">
                        <canvas ref="pieChartRef"></canvas>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                    <div v-for="item in kategoriCards" :key="'leg-'+item.jenis" class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full flex-shrink-0" :style="{background: item.color}"></span>
                        <span class="text-xs" style="color:var(--color-text-secondary)">{{ item.label }} ({{ item.total }})</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Viewer Table -->
        <div class="card-k p-6 mb-6">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
                <h3 class="text-base font-semibold" style="color:var(--color-text-primary)">
                    <i class="fas fa-table mr-2" style="color:var(--color-primary)"></i>Jumlah Viewer per Kategori
                </h3>
                <div class="flex items-center gap-2">
                    <button @click="copyTable" class="copy-btn" :disabled="!stats">
                        <i class="fas fa-copy"></i><span>Copy Tabel</span>
                    </button>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table ref="viewerTableRef" class="w-full text-xs md:text-sm viewer-table">
                    <thead>
                        <tr>
                            <th class="viewer-th" rowspan="2">Bulan</th>
                            <th v-for="t in types" :key="'h-'+t.jenis" class="viewer-th text-center" colspan="2">{{ t.label }}</th>
                            <th class="viewer-th text-center" colspan="2">Total Bulanan</th>
                        </tr>
                        <tr>
                            <template v-for="t in types" :key="'sh-'+t.jenis">
                                <th class="viewer-sub-th text-center">Unggah</th>
                                <th class="viewer-sub-th text-center">Dilihat</th>
                            </template>
                            <th class="viewer-sub-th text-center">Unggah</th>
                            <th class="viewer-sub-th text-center">Dilihat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(m, i) in monthNames" :key="'row-'+i">
                            <td class="viewer-td font-semibold">{{ m }}</td>
                            <template v-for="(t, ti) in types" :key="'d-'+t.jenis+'-'+i">
                                <td class="viewer-td text-center">{{ t.data[i] || '-' }}</td>
                                <td class="viewer-td text-center">{{ viewerTypes[ti]?.data[i] || '-' }}</td>
                            </template>
                            <td class="viewer-td text-center font-bold">{{ monthUploadTotal[i] }}</td>
                            <td class="viewer-td text-center font-bold">{{ monthViewerTotal[i] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Yearly stats charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="card-k p-6 chart-card">
                <button @click="copyChart(yearlyUploadRef)" class="chart-copy-btn" title="Copy grafik"><i class="fas fa-image"></i></button>
                <canvas ref="yearlyUploadRef" height="250"></canvas>
            </div>
            <div class="card-k p-6 chart-card">
                <button @click="copyChart(yearlyViewerRef)" class="chart-copy-btn" title="Copy grafik"><i class="fas fa-image"></i></button>
                <canvas ref="yearlyViewerRef" height="250"></canvas>
            </div>
        </div>

        <!-- Monthly stats charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="card-k p-6 chart-card">
                <button @click="copyChart(monthlyUploadRef)" class="chart-copy-btn" title="Copy grafik"><i class="fas fa-image"></i></button>
                <canvas ref="monthlyUploadRef" height="250"></canvas>
            </div>
            <div class="card-k p-6 chart-card">
                <button @click="copyChart(monthlyViewerRef)" class="chart-copy-btn" title="Copy grafik"><i class="fas fa-image"></i></button>
                <canvas ref="monthlyViewerRef" height="250"></canvas>
            </div>
        </div>

        <div v-if="toast.show" class="copy-toast" :class="toast.type">
            <i :class="toast.type==='success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle'"></i>
            <span>{{ toast.message }}</span>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue';
import api from '@/bootstrap.js';
import { loadChart } from '@/composables/useChart.js';

const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);
const yearOptions = computed(() => {
    const years = [];
    for (let y = currentYear; y >= 2024; y--) years.push(y);
    return years;
});

const stats = ref(null);
const monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
const types = computed(() => stats.value?.monthly_uploads || []);
const viewerTypes = computed(() => stats.value?.monthly_viewers || []);

const monthUploadTotal = computed(() => {
    if (!stats.value) return [];
    const totals = [];
    for (let i = 0; i < 12; i++) {
        totals.push(stats.value.monthly_uploads.reduce((s, t) => s + (t.data[i] || 0), 0));
    }
    return totals;
});
const monthViewerTotal = computed(() => {
    if (!stats.value) return [];
    const totals = [];
    for (let i = 0; i < 12; i++) {
        totals.push(stats.value.monthly_viewers.reduce((s, t) => s + (t.data[i] || 0), 0));
    }
    return totals;
});

const kategoriColors = {
    artikel: '#3b82f6',
    berita: '#22c55e',
    buletin: '#7c3aed',
    jurnal: '#eab308',
    kliping: '#ec4899',
    pengumuman: '#ef4444',
    galeri: '#6366f1',
    unduhan: '#14b8a6',
};
const kategoriIcons = {
    artikel: 'fas fa-file-alt',
    berita: 'fas fa-newspaper',
    buletin: 'fas fa-book-open',
    jurnal: 'fas fa-book',
    kliping: 'fas fa-clone',
    pengumuman: 'fas fa-bullhorn',
    galeri: 'fas fa-images',
    unduhan: 'fas fa-download',
};

const kategoriCards = computed(() => {
    if (!stats.value) return [];
    return stats.value.total_per_kategori.map(item => ({
        ...item,
        color: kategoriColors[item.jenis] || '#6b7280',
        icon: kategoriIcons[item.jenis] || 'fas fa-file',
    }));
});

const lineChartRef = ref(null);
const pieChartRef = ref(null);
const yearlyUploadRef = ref(null);
const yearlyViewerRef = ref(null);
const monthlyUploadRef = ref(null);
const monthlyViewerRef = ref(null);
const viewerTableRef = ref(null);
const toast = reactive({ show: false, message: '', type: 'success' });

let charts = {};

function showToast(message, type = 'success') {
    toast.message = message;
    toast.type = type;
    toast.show = true;
    setTimeout(() => { toast.show = false; }, 2500);
}

function destroyChart(key) {
    if (charts[key]) { charts[key].destroy(); charts[key] = null; }
}

function destroyAllCharts() {
    Object.keys(charts).forEach(destroyChart);
}

onMounted(() => fetchStats());

async function fetchStats() {
    destroyAllCharts();
    try {
        await loadChart();
        const { data } = await api.get('/dashboard-stats', { params: { year: selectedYear.value } });
        stats.value = data;
        await nextTick();
        renderCharts();
    } catch (e) { console.error(e); }
}

function renderCharts() {
    if (!stats.value || !window.Chart) return;
    const s = stats.value;
    const catLabels = s.total_per_kategori.map(t => t.label);
    const catTotals = s.total_per_kategori.map(t => t.total);
    const catColors = s.total_per_kategori.map(t => kategoriColors[t.jenis] || '#6b7280');

    if (lineChartRef.value) {
        destroyChart('line');
        charts.line = new Chart(lineChartRef.value, {
            type: 'line',
            data: {
                labels: monthNames,
                datasets: s.monthly_uploads.map(t => ({
                    label: t.label,
                    data: t.data,
                    fill: false,
                    tension: 0.2,
                    borderWidth: 2,
                    borderColor: kategoriColors[t.jenis],
                    pointRadius: 3,
                })),
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Grafik Postingan per Bulan', font: { size: 14, weight: '600' } },
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 12, font: { size: 11 } } },
                },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
            },
        });
    }

    if (pieChartRef.value) {
        destroyChart('pie');
        charts.pie = new Chart(pieChartRef.value, {
            type: 'doughnut',
            data: {
                labels: catLabels,
                datasets: [{ data: catTotals, backgroundColor: catColors }],
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Jumlah Postingan per Kategori', font: { size: 14, weight: '600' } },
                    legend: { display: false },
                },
            },
        });
    }

    if (yearlyUploadRef.value) {
        destroyChart('yUpload');
        charts.yUpload = new Chart(yearlyUploadRef.value, {
            type: 'line',
            data: {
                labels: s.stat_tahunan.map(r => r.tahun),
                datasets: [{
                    label: 'Total Unggah',
                    data: s.stat_tahunan.map(r => r.unggahan),
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    tension: 0.4,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Statistik Total Unggah 3 Tahun Terakhir', font: { size: 14, weight: '600' } },
                    legend: { display: false },
                },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
            },
        });
    }

    if (yearlyViewerRef.value) {
        destroyChart('yViewer');
        charts.yViewer = new Chart(yearlyViewerRef.value, {
            type: 'line',
            data: {
                labels: s.stat_tahunan.map(r => r.tahun),
                datasets: [{
                    label: 'Total Dilihat',
                    data: s.stat_tahunan.map(r => r.viewer),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.08)',
                    tension: 0.4,
                    fill: true,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Statistik Total Dilihat 3 Tahun Terakhir', font: { size: 14, weight: '600' } },
                    legend: { display: false },
                },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
            },
        });
    }

    if (monthlyUploadRef.value) {
        destroyChart('mUpload');
        charts.mUpload = new Chart(monthlyUploadRef.value, {
            type: 'bar',
            data: {
                labels: monthNames,
                datasets: [{
                    label: 'Total Unggahan',
                    data: s.stat_bulanan.map(r => r.unggahan),
                    backgroundColor: '#6366f1',
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Statistik Unggahan per Bulan', font: { size: 14, weight: '600' } },
                    legend: { display: false },
                },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
            },
        });
    }

    if (monthlyViewerRef.value) {
        destroyChart('mViewer');
        charts.mViewer = new Chart(monthlyViewerRef.value, {
            type: 'bar',
            data: {
                labels: monthNames,
                datasets: [{
                    label: 'Total Viewer',
                    data: s.stat_bulanan.map(r => r.viewer),
                    backgroundColor: '#22c55e',
                    borderRadius: 4,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    title: { display: true, text: 'Statistik Viewer per Bulan', font: { size: 14, weight: '600' } },
                    legend: { display: false },
                },
                scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } },
            },
        });
    }
}

async function copyChart(canvasRef) {
    try {
        if (!canvasRef) { showToast('Grafik belum dimuat', 'error'); return; }
        const dataUrl = canvasRef.toDataURL('image/png');
        const res = await fetch(dataUrl);
        const blob = await res.blob();
        await navigator.clipboard.write([new ClipboardItem({ 'image/png': blob })]);
        showToast('Grafik berhasil disalin! Paste di Word.');
    } catch {
        try {
            const dataUrl = canvasRef.toDataURL('image/png');
            const w = window.open('');
            w.document.write(`<img src="${dataUrl}" style="max-width:100%">`);
            w.document.write('<p style="font-family:sans-serif;color:#666">Klik kanan gambar > Copy Image, lalu paste di Word.</p>');
            showToast('Grafik dibuka di tab baru. Klik kanan > Copy Image.');
        } catch { showToast('Gagal menyalin grafik', 'error'); }
    }
}

function buildHtmlTable() {
    if (!stats.value) return '';
    const s = stats.value;
    const types = s.monthly_uploads;
    const vTypes = s.monthly_viewers;

    let html = '<table border="1" cellpadding="4" cellspacing="0" style="border-collapse:collapse;font-family:sans-serif;font-size:9pt">';
    html += '<thead><tr>';
    html += '<th style="background:#f3f4f6;font-weight:bold;text-align:center" rowspan="2">Bulan</th>';
    types.forEach(t => {
        html += `<th style="background:#f3f4f6;font-weight:bold;text-align:center" colspan="2">${t.label}</th>`;
    });
    html += '<th style="background:#f3f4f6;font-weight:bold;text-align:center" colspan="2">Total Bulanan</th>';
    html += '</tr><tr>';
    types.forEach(() => {
        html += '<th style="background:#f9fafb;font-weight:600;text-align:center;font-size:8pt">Unggah</th>';
        html += '<th style="background:#f9fafb;font-weight:600;text-align:center;font-size:8pt">Dilihat</th>';
    });
    html += '<th style="background:#f9fafb;font-weight:600;text-align:center;font-size:8pt">Unggah</th>';
    html += '<th style="background:#f9fafb;font-weight:600;text-align:center;font-size:8pt">Dilihat</th>';
    html += '</tr></thead><tbody>';

    monthNames.forEach((m, i) => {
        html += '<tr>';
        html += `<td style="font-weight:600;padding:4px 8px;white-space:nowrap">${m}</td>`;
        types.forEach((t, ti) => {
            html += `<td style="text-align:center;padding:4px 6px">${t.data[i] || '-'}</td>`;
            html += `<td style="text-align:center;padding:4px 6px">${vTypes[ti]?.data[i] || '-'}</td>`;
        });
        html += `<td style="text-align:center;font-weight:bold;padding:4px 6px">${monthUploadTotal.value[i]}</td>`;
        html += `<td style="text-align:center;font-weight:bold;padding:4px 6px">${monthViewerTotal.value[i]}</td>`;
        html += '</tr>';
    });

    html += '</tbody></table>';
    return html;
}

async function copyTable() {
    try {
        const html = buildHtmlTable();
        if (!html) { showToast('Tidak ada data', 'error'); return; }
        const blob = new Blob([html], { type: 'text/html' });
        const txtBlob = new Blob([viewerTableRef.value?.innerText || ''], { type: 'text/plain' });
        await navigator.clipboard.write([new ClipboardItem({ 'text/html': blob, 'text/plain': txtBlob })]);
        showToast('Tabel berhasil disalin! Paste di Word.');
    } catch {
        try {
            const html = buildHtmlTable();
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            tmp.style.position = 'fixed';
            tmp.style.left = '-9999px';
            document.body.appendChild(tmp);
            const range = document.createRange();
            range.selectNodeContents(tmp);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
            document.execCommand('copy');
            sel.removeAllRanges();
            document.body.removeChild(tmp);
            showToast('Tabel berhasil disalin! Paste di Word.');
        } catch { showToast('Gagal menyalin', 'error'); }
    }
}
</script>

<style scoped>
.card-k {
    background: var(--color-surface, #fff);
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.06);
    border: 1px solid #f3f4f6;
}
.stat-card {
    padding: 1.25rem 1.5rem;
    border-left: 4px solid transparent;
    transition: all 0.3s ease;
}
.stat-card:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}
.stat-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.year-select {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: var(--color-text-primary, #1f293b);
    cursor: pointer;
    outline: none;
    font-family: 'Quicksand', sans-serif;
    transition: all 0.15s;
}
.year-select:focus {
    border-color: var(--color-primary, #2563eb);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
}
.viewer-table th,
.viewer-table td {
    border: 1px solid #e5e7eb;
}
.viewer-th {
    background: #f9fafb;
    padding: 8px 10px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--color-text-secondary, #6b7280);
}
.viewer-sub-th {
    background: #f9fafb;
    padding: 4px 8px;
    font-size: 9px;
    font-weight: 600;
    color: var(--color-text-secondary, #6b7280);
}
.viewer-td {
    padding: 6px 10px;
    color: var(--color-text-primary, #1f293b);
    white-space: nowrap;
}
.copy-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    background: #fff;
    color: var(--color-text-secondary, #6b7280);
    cursor: pointer;
    transition: all 0.15s;
}
.copy-btn:hover:not(:disabled) {
    border-color: var(--color-primary, #2563eb);
    color: var(--color-primary, #2563eb);
}
.copy-btn:disabled { opacity: 0.4; cursor: not-allowed; }
.chart-card { position: relative; }
.chart-copy-btn {
    position: absolute;
    top: 12px;
    right: 12px;
    z-index: 2;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    font-size: 12px;
    border: 1.5px solid #e5e7eb;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(4px);
    color: var(--color-text-secondary, #6b7280);
    cursor: pointer;
    transition: all 0.15s;
    opacity: 0;
}
.chart-card:hover .chart-copy-btn { opacity: 1; }
.chart-copy-btn:hover {
    border-color: var(--color-primary, #2563eb);
    color: var(--color-primary, #2563eb);
    background: #fff;
}
.copy-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    animation: toastIn 0.3s ease-out;
}
.copy-toast.success { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.copy-toast.error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
@keyframes toastIn {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>
