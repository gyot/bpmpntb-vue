<template>
<div>
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-6">
        <div>
            <h2 class="text-xl font-semibold" style="color:var(--color-text-primary)">
                <i class="fas fa-chart-line mr-2" style="color:var(--color-primary)"></i>Analytics Chatbot
            </h2>
            <p class="text-sm" style="color:var(--color-text-secondary)">Statistik penggunaan chatbot SI INTAN</p>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <span class="text-xs" style="color:#94a3b8">{{ lastUpdated }}</span>
            <div class="flex items-center gap-2 p-1.5 rounded-xl" style="background:#f8fafc;border:1px solid #e2e8f0;">
                <select v-model="reportMonth" class="report-select">
                    <option v-for="m in 12" :key="m" :value="m">{{ monthNames[m-1] }}</option>
                </select>
                <select v-model="reportYear" class="report-select">
                    <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                </select>
                <button class="report-btn" @click="generateReport">
                    <i class="fas fa-file-pdf mr-1"></i>Generate Laporan
                </button>
            </div>
            <button class="refresh-btn" @click="loadAnalytics" :disabled="loading">
                <i class="fas fa-sync-alt mr-1" :class="{'fa-spin':loading}"></i> Refresh
            </button>
        </div>
    </div>

    <!-- TODAY -->
    <div class="section-title"><i class="fas fa-calendar-day"></i> Hari Ini</div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#eff6ff;color:#2563eb;"><i class="fas fa-comments"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.today) }}</div><div class="stat-label">Total Pesan</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-users"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.today_unique_users) }}</div><div class="stat-label">User Unik</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-robot"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.today_ai_calls) }}</div><div class="stat-label">Panggilan AI</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#f3e8ff;color:#9333ea;"><i class="fas fa-key"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.today_keyword) }}</div><div class="stat-label">Keyword Match</div></div>
            </div>
        </div>
    </div>

    <!-- THIS WEEK -->
    <div class="section-title"><i class="fas fa-calendar-week"></i> Minggu Ini</div>
    <div class="grid grid-cols-2 gap-3 mb-6">
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#e0f2fe;color:#0284c7;"><i class="fas fa-envelope"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.week_total) }}</div><div class="stat-label">Total Pesan</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#fce7f3;color:#db2777;"><i class="fas fa-user-check"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.week_active_users) }}</div><div class="stat-label">Active Users</div></div>
            </div>
        </div>
    </div>

    <!-- THIS MONTH -->
    <div class="section-title"><i class="fas fa-calendar-alt"></i> Bulan Ini</div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#dbeafe;color:#1d4ed8;"><i class="fas fa-user-plus"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.month_new_users) }}</div><div class="stat-label">User Baru</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#dcfce7;color:#15803d;"><i class="fas fa-comment-dots"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.month_livechat) }}</div><div class="stat-label">Sesi Live Chat</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#fee2e2;color:#dc2626;"><i class="fas fa-exclamation-triangle"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.month_complaints) }}</div><div class="stat-label">Pengaduan</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#f0fdf4;color:#22c55e;"><i class="fas fa-door-open"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.month_open_sessions) }}</div><div class="stat-label">Sesi Aktif</div></div>
            </div>
        </div>
    </div>

    <!-- TOP QUERIES + RESPONSE RATIO -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="stat-card">
            <div class="section-title" style="margin-bottom:10px;"><i class="fas fa-chart-bar"></i> Top 10 Pertanyaan (Bulan Ini)</div>
            <div v-if="d.topQueries && d.topQueries.length">
                <div v-for="(q, i) in d.topQueries" :key="i" class="query-item">
                    <div class="query-text">{{ i+1 }}. {{ q.user_message }}</div>
                    <span class="query-count">{{ q.count }}x</span>
                </div>
            </div>
            <div v-else class="text-sm" style="color:#94a3b8">Belum ada data pertanyaan.</div>
        </div>
        <div class="stat-card">
            <div class="section-title" style="margin-bottom:10px;"><i class="fas fa-pie-chart"></i> Sumber Response (Hari Ini)</div>
            <div v-if="hasTodaySources">
                <div v-for="(count, source) in d.today_sources" :key="source" class="mb-3">
                    <div class="flex justify-between text-sm">
                        <span class="font-medium" style="color:var(--color-text-primary)">{{ capitalizeSource(source) }}</span>
                        <span style="color:#94a3b8">{{ count }} ({{ todaySourcePercent(count) }}%)</span>
                    </div>
                    <div class="ratio-bar"><div class="ratio-bar-fill" :style="{width: todaySourcePercent(count)+'%', background: getSourceColor(source)}"></div></div>
                </div>
            </div>
            <div v-else class="text-sm" style="color:#94a3b8">Belum ada data hari ini.</div>
        </div>
    </div>

    <!-- HOURLY ACTIVITY -->
    <div class="stat-card mb-6">
        <div class="section-title" style="margin-bottom:10px;"><i class="fas fa-clock"></i> Aktivitas Per Jam (Hari Ini)</div>
        <div class="chart-container" style="height:220px;"><canvas ref="hourlyChartRef"></canvas></div>
    </div>

    <!-- TOKEN / COST / PERFORMANCE -->
    <div class="section-title"><i class="fas fa-coins"></i> Token, Biaya &amp; Performa — Bulan Ini</div>
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#eef2ff;color:#4f46e5;"><i class="fas fa-database"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.tokens_month) }}</div><div class="stat-label">Total Token (Bulan)</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#ecfeff;color:#0891b2;"><i class="fas fa-bolt"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.tokens_today) }}</div><div class="stat-label">Token Hari Ini</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#f0fdf4;color:#16a34a;"><i class="fas fa-dollar-sign"></i></div>
                <div><div class="stat-value">{{ formatNumber(d.prompt_tokens_month) }}</div><div class="stat-label">Prompt Token</div></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="flex items-center gap-3">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-stopwatch"></i></div>
                <div>
                    <div class="stat-value">{{ avgLatency }}<span style="font-size:14px;font-weight:500;"> ms</span></div>
                    <div class="stat-label">Latensi Rata-rata</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Laporan Harian -->
    <div class="stat-card mb-6">
        <div class="section-title" style="margin-bottom:10px;"><i class="fas fa-table"></i> Laporan Harian (30 hari terakhir)</div>
        <div v-if="reportData.length" class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr style="background:#f8fafc;"><th class="table-header">Tanggal</th><th class="table-header">Percakapan</th><th class="table-header">Token</th></tr></thead>
                <tbody><tr v-for="r in reportData" :key="r.date" style="border-bottom:1px solid #f1f5f9;"><td class="table-cell">{{ formatDate(r.date) }}</td><td class="table-cell font-medium">{{ formatNumber(r.total) }}</td><td class="table-cell">{{ formatNumber(r.tokens) }}</td></tr></tbody>
            </table>
        </div>
        <div v-else class="text-sm" style="color:#94a3b8">Memuat laporan...</div>
    </div>

    <!-- Daily Chart -->
    <div class="stat-card mb-6">
        <div class="section-title" style="margin-bottom:10px;"><i class="fas fa-chart-area"></i> Grafik Percakapan Harian</div>
        <div class="chart-container" style="height:250px;"><canvas ref="dailyChartRef"></canvas></div>
    </div>

    <!-- TOKEN PER USER -->
    <div class="section-title"><i class="fas fa-user-shield"></i> Pemakaian Token per User</div>
    <div class="stat-card mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-2 mb-3">
            <div>
                <div style="font-weight:700;color:#0f172a;">Audit pemakaian AI bulan ini</div>
                <div class="text-xs" style="color:#94a3b8;line-height:1.5;">
                    Kuota harian: <b>1.000.000</b> token per user. Ditampilkan maksimal 50 user teratas berdasarkan token bulan ini.
                </div>
            </div>
        </div>
        <div v-if="d.user_usage && d.user_usage.length" class="overflow-x-auto" style="border:1px solid #e2e8f0;border-radius:12px;">
            <table class="user-usage-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>AI Calls</th>
                        <th>Token Bulan Ini</th>
                        <th>Kuota Hari Ini</th>
                        <th>Biaya/Latency</th>
                        <th>IP &amp; Aktif</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(u, i) in d.user_usage" :key="u.chatbot_user_id">
                        <td style="width:36px;color:#94a3b8;font-weight:700;">{{ i+1 }}</td>
                        <td>
                            <div style="font-weight:700;color:#0f172a;">{{ u.nama }}</div>
                            <div class="text-xs" style="color:#94a3b8;">{{ u.instansi }}</div>
                            <div class="text-xs" style="color:#94a3b8;">Kontak: {{ u.kontak_masked }}</div>
                        </td>
                        <td>
                            <div style="font-weight:700;color:#0f172a;">{{ formatNumber(u.ai_calls) }}</div>
                        </td>
                        <td>
                            <div style="font-weight:700;color:#0f172a;">{{ formatNumber(u.total_tokens) }}</div>
                            <div class="text-xs" style="color:#94a3b8;">Prompt {{ formatNumber(u.prompt_tokens) }} · Jawaban {{ formatNumber(u.completion_tokens) }}</div>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                                <span style="font-weight:700;" :style="{color: quotaColor(u)}">{{ formatNumber(u.daily_quota?.used || 0) }}</span>
                                <span class="text-xs" style="color:#94a3b8;">{{ u.daily_quota?.percent || 0 }}%</span>
                            </div>
                            <div class="quota-progress" :title="formatNumber(u.daily_quota?.used||0) + ' dari ' + formatNumber(u.daily_quota?.limit||0) + ' token'">
                                <div class="quota-progress-fill" :style="{width: (u.daily_quota?.percent||0)+'%', background: quotaColor(u)}"></div>
                            </div>
                            <div class="text-xs" style="color:#94a3b8;">Sisa {{ formatNumber(u.daily_quota?.remaining||0) }} / {{ formatNumber(u.daily_quota?.limit||0) }}</div>
                            <div v-if="u.daily_quota?.exceeded" style="font-size:11px;color:#dc2626;font-weight:700;margin-top:2px;">Kuota penuh</div>
                        </td>
                        <td>
                            <div style="font-weight:700;color:#0f172a;">{{ u.has_pricing ? '$' + (u.estimated_cost || 0).toFixed(4) : 'N/A' }}</div>
                            <div class="text-xs" style="color:#94a3b8;">{{ formatNumber(u.avg_response_ms) }} ms avg</div>
                        </td>
                        <td>
                            <div style="font-weight:600;color:#475569;">{{ u.last_ip }}</div>
                            <div class="text-xs" style="color:#94a3b8;">{{ formatDateTime(u.last_active_at) }}</div>
                        </td>
                        <td>
                            <button class="reset-quota-btn" @click="resetUserQuota(u.chatbot_user_id)" :disabled="resettingId === u.chatbot_user_id">
                                <i class="fas fa-rotate-left"></i> Reset Kuota
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="text-sm" style="color:#94a3b8">Belum ada pemakaian token AI bulan ini.</div>
    </div>

    <!-- KNOWLEDGE BASE HEALTH -->
    <div class="section-title"><i class="fas fa-heartbeat"></i> Kesehatan Knowledge Base</div>
    <div class="stat-card mb-6">
        <div v-if="kbHealth">
            <div v-if="kbHealth.null_embeddings > 0" style="background:#fef2f2;color:#dc2626;border-radius:8px;padding:8px 12px;font-size:13px;margin-bottom:8px;">
                <i class="fas fa-exclamation-circle"></i> <b>{{ formatNumber(kbHealth.null_embeddings) }}</b> chunk tanpa embedding. Jalankan <b>Regenerate Embeddings</b> di Knowledge Base.
            </div>
            <div v-else style="background:#f0fdf4;color:#16a34a;border-radius:8px;padding:8px 12px;font-size:13px;margin-bottom:8px;">
                <i class="fas fa-circle-check"></i> Semua chunk punya embedding.
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mt-3">
                <div><div class="stat-value" style="font-size:22px;">{{ formatNumber(kbHealth.active_documents) }}<span style="font-size:13px;color:#94a3b8;font-weight:500;">/{{ formatNumber(kbHealth.total_documents) }}</span></div><div class="stat-label">Dokumen Aktif</div></div>
                <div><div class="stat-value" style="font-size:22px;">{{ formatNumber(kbHealth.total_chunks) }}</div><div class="stat-label">Total Chunk</div></div>
                <div><div class="stat-value" style="font-size:22px;" :style="{color: kbHealth.null_embeddings > 0 ? '#dc2626' : '#0f172a'}">{{ formatNumber(kbHealth.null_embeddings) }}</div><div class="stat-label">Embedding Kosong</div></div>
                <div><div class="stat-value" style="font-size:15px;font-weight:600;padding-top:6px;">{{ formatDateTime(kbHealth.last_generated_at) }}</div><div class="stat-label">Update Terakhir</div></div>
            </div>
        </div>
        <div v-else class="text-sm" style="color:#94a3b8">Data knowledge base belum tersedia.</div>
    </div>
</div>
</template>

<script setup>
import { ref, onMounted, computed, nextTick } from 'vue';
import api from '@/bootstrap.js';
import { swalConfirm, swalError, swalSuccess } from '@/swal.js';
import { loadChart } from '@/composables/useChart.js';

const d = ref({
    today: 0, today_unique_users: 0, today_ai_calls: 0, today_keyword: 0,
    week_total: 0, week_active_users: 0,
    month_new_users: 0, month_livechat: 0, month_open_sessions: 0, month_complaints: 0,
    total: 0, sources: {}, today_sources: {}, topQueries: [],
    users: 0, sessions: 0, totalTokens: 0, avgResponseTime: 0,
    hourly_activity: [], tokens_month: 0, tokens_today: 0,
    prompt_tokens_month: 0, completion_tokens_month: 0,
});
const reportData = ref([]);
const loading = ref(false);
const resettingId = ref(null);
const lastUpdated = ref('-');
const hourlyChartRef = ref(null);
const dailyChartRef = ref(null);
let hourlyChartInstance = null;
let dailyChartInstance = null;

const now = new Date();
const reportMonth = ref(now.getMonth() + 1);
const reportYear = ref(now.getFullYear());
const monthNames = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const yearOptions = computed(() => { const y = []; for (let i = now.getFullYear() + 1; i >= 2020; i--) y.push(i); return y; });

const hasTodaySources = computed(() => d.value.today_sources && Object.keys(d.value.today_sources).length > 0);
const todaySourceTotal = computed(() => d.value.today_sources ? Object.values(d.value.today_sources).reduce((a, b) => a + Number(b), 0) : 0);
const avgLatency = computed(() => d.value.avgResponseTime ? Math.round(d.value.avgResponseTime) : 0);
const kbHealth = computed(() => d.value.kb_health || null);

function formatNumber(n) { if (!n && n !== 0) return '0'; return Number(n).toLocaleString('id-ID'); }
function formatDate(dateStr) { if (!dateStr) return '-'; const dt = new Date(dateStr); return dt.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short' }); }
function formatDateTime(value) { if (!value) return '-'; const date = new Date(String(value).replace(' ', 'T')); if (Number.isNaN(date.getTime())) return String(value); return date.toLocaleString('id-ID'); }
function capitalizeSource(s) { const n = { ai: 'AI (OpenAI)', keyword: 'Keyword Match', intent: 'Intent Detection', menu: 'Menu Navigasi', quota: 'Quota Exceeded' }; return n[s] || s; }
function getSourceColor(s) { const c = { ai: '#3b82f6', keyword: '#22c55e', menu: '#f59e0b', intent: '#8b5cf6' }; return c[s] || '#94a3b8'; }
function todaySourcePercent(count) { const t = todaySourceTotal.value; return t === 0 ? 0 : Math.round((Number(count) / t) * 100); }
function quotaColor(u) { const q = u.daily_quota; if (!q) return '#16a34a'; if (q.exceeded) return '#dc2626'; if (q.percent >= 80) return '#f59e0b'; return '#16a34a'; }

function destroyChart(ref) { if (ref) { ref.destroy(); return null; } return null; }

function renderHourlyChart() {
    if (!hourlyChartRef.value || !window.Chart) return;
    if (hourlyChartInstance) { hourlyChartInstance.destroy(); hourlyChartInstance = null; }
    const data = d.value.hourly_activity || [];
    if (!data.length) return;
    const labels = data.map(h => { const parts = (h.hour || '').split(' '); return parts[1] ? parts[1].substring(0, 5) : h.hour; });
    const values = data.map(h => h.count || 0);
    hourlyChartInstance = new window.Chart(hourlyChartRef.value, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Pesan', data: values, backgroundColor: 'rgba(59,130,246,0.6)', borderColor: 'rgba(59,130,246,1)', borderWidth: 1, borderRadius: 6 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
        }
    });
}

function renderDailyChart() {
    if (!dailyChartRef.value || !window.Chart) return;
    if (dailyChartInstance) { dailyChartInstance.destroy(); dailyChartInstance = null; }
    const data = reportData.value;
    if (!data.length) return;
    const labels = data.map(r => { const dt = new Date(r.date); return dt.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }); });
    const values = data.map(r => Number(r.total || 0));
    dailyChartInstance = new window.Chart(dailyChartRef.value, {
        type: 'bar',
        data: { labels, datasets: [{ label: 'Percakapan', data: values, backgroundColor: 'rgba(59,130,246,0.6)', borderColor: 'rgba(59,130,246,1)', borderWidth: 1, borderRadius: 6 }] },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1, precision: 0 }, grid: { color: 'rgba(0,0,0,0.05)' } }, x: { grid: { display: false } } }
        }
    });
}

async function loadAnalytics() {
    loading.value = true;
    try {
        await loadChart();
        const [anRes, repRes] = await Promise.all([api.get('/chatbot/admin/analytics'), api.get('/chatbot/admin/analytics/report')]);
        d.value = { ...d.value, ...anRes.data };
        reportData.value = repRes.data.daily || [];
        lastUpdated.value = 'Terakhir: ' + new Date().toLocaleTimeString('id-ID');
        await nextTick();
        renderHourlyChart();
        renderDailyChart();
    } catch (e) { console.error('Analytics error:', e); } finally { loading.value = false; }
}

async function generateReport() {
    try {
        const res = await api.get('/chatbot/admin/analytics/pdf', {
            params: { month: reportMonth.value, year: reportYear.value },
            responseType: 'blob'
        });
        const blob = new Blob([res.data], { type: 'application/pdf' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Analytics-Chatbot-SI-INTAN-${monthNames[reportMonth.value-1]}-${reportYear.value}.pdf`;
        a.click();
        URL.revokeObjectURL(url);
    } catch (e) { console.error('PDF report error:', e); }
}

async function resetUserQuota(userId) {
    const confirmed = await swalConfirm('Reset kuota token user ini?', 'Ya, Reset');
    if (!confirmed) return;
    resettingId.value = userId;
    try {
        await api.post(`/chatbot/admin/analytics/user-usage/${userId}/reset-quota`);
        swalSuccess('Kuota user berhasil direset.');
        await loadAnalytics();
    } catch (e) {
        console.error('Reset quota error:', e);
        swalError('Gagal reset kuota user.');
    } finally {
        resettingId.value = null;
    }
}

onMounted(() => { loadAnalytics(); });
</script>

<style scoped>
.stat-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 18px; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06); transition: all .2s ease; }
.stat-card:hover { box-shadow: 0 8px 24px rgba(15, 23, 42, 0.10); transform: translateY(-2px); }
.stat-value { font-size: 28px; font-weight: 700; line-height: 1.1; color: #0f172a; }
.stat-label { font-size: 13px; color: #64748b; font-weight: 500; margin-top: 4px; }
.stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
.section-title { font-size: 16px; font-weight: 700; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; }
.section-title i { color: #3b82f6; }
.query-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }
.query-item:last-child { border-bottom: none; }
.query-text { font-size: 14px; font-weight: 500; color: #0f172a; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.query-count { background: #eff6ff; color: #2563eb; border-radius: 999px; padding: 2px 12px; font-size: 12px; font-weight: 700; flex-shrink: 0; margin-left: 8px; }
.ratio-bar { height: 8px; border-radius: 999px; background: #e2e8f0; overflow: hidden; margin-top: 8px; }
.ratio-bar-fill { height: 100%; border-radius: 999px; transition: width .6s ease; }
.chart-container { position: relative; width: 100%; }
.table-header { background: #f8fafc; color: #475569; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; text-align: left; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
.table-cell { padding: 12px; color: #0f172a; }
.report-select { height: 34px; border: 1px solid #cbd5e1; border-radius: 9px; background: #fff; color: #0f172a; font-size: 12px; font-weight: 600; padding: 0 10px; outline: none; font-family: 'Quicksand', sans-serif; }
.report-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.12); }
.report-btn { height: 34px; border: none; border-radius: 9px; padding: 0 14px; background: linear-gradient(135deg, #10b981, #059669); color: #fff; font-size: 12px; font-weight: 800; cursor: pointer; white-space: nowrap; box-shadow: 0 8px 16px rgba(5,150,105,.18); transition: all .15s ease; font-family: 'Quicksand', sans-serif; }
.report-btn:hover { opacity: .92; transform: translateY(-1px); }
.refresh-btn { background: #3b82f6; color: #fff; border: none; border-radius: 10px; padding: 8px 18px; font-size: 13px; font-weight: 600; cursor: pointer; transition: all .15s ease; font-family: 'Quicksand', sans-serif; }
.refresh-btn:hover { opacity: .85; transform: translateY(-1px); }
.refresh-btn:disabled { opacity: .5; cursor: not-allowed; }
.user-usage-table { width: 100%; border-collapse: collapse; font-size: 13px; min-width: 780px; }
.user-usage-table th { background: #f8fafc; color: #475569; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; text-align: left; padding: 10px 12px; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
.user-usage-table td { padding: 12px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
.user-usage-table tr:hover td { background: #f8fafc; }
.reset-quota-btn {
    border: none;
    border-radius: 8px;
    padding: 7px 10px;
    background: #fee2e2;
    color: #dc2626;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all .15s ease;
    font-family: 'Quicksand', sans-serif;
}
.reset-quota-btn:hover { background: #fecaca; transform: translateY(-1px); }
.reset-quota-btn:disabled { opacity: .55; cursor: not-allowed; transform: none; }
.quota-progress {
    height: 8px;
    width: 160px;
    max-width: 100%;
    border-radius: 999px;
    background: #e2e8f0;
    overflow: hidden;
    margin: 6px 0 4px;
}
.quota-progress-fill {
    height: 100%;
    border-radius: 999px;
    transition: width .4s ease;
}
</style>
