<template>
    <PublicLayout>
        <section class="relative overflow-hidden pt-28 pb-20" style="background:linear-gradient(180deg, #2563eb 0%, #1d4ed8 100%)">
            <div class="absolute inset-0 opacity-[0.05]" style="background-image:radial-gradient(rgba(255,255,255,0.4) 1px, transparent 1px);background-size:32px 32px"></div>
            <div class="absolute top-10 right-[15%] w-[400px] h-[400px] rounded-full opacity-10 animate-float-slow" style="background:radial-gradient(circle, #60A5FA 0%, transparent 70%);filter:blur(80px)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center">
                <div class="section-label justify-center !text-white/40 !before:bg-white/20">Survei</div>
                <h1 class="text-4xl md:text-5xl font-extrabold mb-5" style="color:rgba(255,255,255,0.95);letter-spacing:-0.03em">Survey Kepuasan Pelanggan</h1>
                <p class="text-lg max-w-2xl mx-auto" style="color:rgba(255,255,255,0.55);line-height:1.7">Hasil survei kepuasan masyarakat terhadap layanan BPMP Provinsi Nusa Tenggara Barat</p>
                <div class="flex justify-center gap-3 mt-8">
                    <a href="#ikm" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold transition-all" style="background:rgba(255,255,255,0.15);color:rgba(255,255,255,0.9);border:1px solid rgba(255,255,255,0.2)" @click.prevent="scrollTo('ikm')"><i class="fas fa-chart-bar text-[11px]"></i>Laporan IKM</a>
                    <a href="#profile" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-semibold transition-all" style="background:rgba(255,255,255,0.08);color:rgba(255,255,255,0.7);border:1px solid rgba(255,255,255,0.12)" @click.prevent="scrollTo('profile')"><i class="fas fa-users text-[11px]"></i>Karakteristik Responden</a>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0"><svg viewBox="0 0 1440 80" fill="none" preserveAspectRatio="none" class="w-full h-12"><path d="M0,40 C360,80 720,0 1080,40 C1260,60 1380,50 1440,40 L1440,80 L0,80 Z" fill="white"/></svg></div>
        </section>

        <section id="ikm" class="py-20 bg-white relative overflow-hidden">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full opacity-[0.03] animate-float-slow" style="background:radial-gradient(circle, var(--color-primary) 0%, transparent 70%);filter:blur(120px)"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center mb-12">
                    <div class="section-label justify-center">IKM</div>
                    <h2 class="section-title">Indeks Kepuasan Masyarakat</h2>
                    <p class="section-subtitle mx-auto">Data IKM per triwulan dari Google Sheet publik</p>
                </div>

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-10 max-w-4xl mx-auto">
                    <div class="card p-5 text-center">
                        <span class="text-xs font-semibold" style="color:var(--color-text-secondary)">Status</span>
                        <div class="text-sm font-bold mt-1" :style="{color: loading ? 'var(--color-text-secondary)' : 'var(--color-primary)'}">{{ loading ? 'Memuat...' : 'Terhubung' }}</div>
                    </div>
                    <div class="card p-5 text-center">
                        <span class="text-xs font-semibold" style="color:var(--color-text-secondary)">Total Tahun</span>
                        <div class="text-2xl font-extrabold mt-1" style="color:var(--color-primary)">{{ years.length }}</div>
                    </div>
                    <div class="card p-5 text-center">
                        <span class="text-xs font-semibold" style="color:var(--color-text-secondary)">Total Triwulan</span>
                        <div class="text-2xl font-extrabold mt-1" style="color:var(--color-primary)">{{ totalQuarters }}</div>
                    </div>
                    <div class="card p-5 text-center">
                        <span class="text-xs font-semibold" style="color:var(--color-text-secondary)">Total Responden</span>
                        <div class="text-2xl font-extrabold mt-1" style="color:var(--color-primary)">{{ totalRespondents.toLocaleString('id-ID') }}</div>
                    </div>
                </div>

                <div v-if="loading" class="space-y-6">
                    <div v-for="i in 2" :key="i" class="card p-6"><div class="skeleton h-8 w-48 mb-4"></div><div class="grid grid-cols-2 gap-4"><div class="skeleton h-64 rounded-xl"></div><div class="skeleton h-64 rounded-xl"></div></div></div>
                </div>

                <div v-else-if="error" class="card p-10 text-center max-w-lg mx-auto">
                    <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center" style="background:#ffebee"><i class="fas fa-exclamation-triangle text-2xl" style="color:#c62828"></i></div>
                    <p class="font-semibold mb-2" style="color:#37474F">Gagal Memuat Data</p>
                    <p class="text-sm" style="color:var(--color-text-secondary)">{{ error }}</p>
                    <button @click="loadData" class="btn-primary mt-6 py-2.5 px-6 text-xs">Coba Lagi</button>
                </div>

                <div v-else class="space-y-8">
                    <div v-for="year in years" :key="year" class="card overflow-hidden">
                        <div class="px-6 py-5 flex items-center justify-between" style="background:rgba(37,99,235,0.04);border-bottom:1px solid rgba(37,99,235,0.08)">
                            <div>
                                <h3 class="text-xl font-bold" style="color:#263238">Tahun {{ year }}</h3>
                                <p class="text-xs mt-1" style="color:var(--color-text-secondary)">{{ getYearReportCount(year) }} triwulan berisi data</p>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-semibold" style="color:var(--color-text-secondary)">Responden</div>
                                <div class="text-lg font-extrabold" style="color:var(--color-primary)">{{ getYearRespondents(year).toLocaleString('id-ID') }}</div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                            <div v-for="q in getYearQuarters(year)" :key="q.quarter" class="p-6" :class="{'lg:border-r': q.quarter % 2 !== 0}" style="border-bottom:1px solid #f0f0f0">
                                <div class="flex items-center justify-between mb-4">
                                    <div>
                                        <h4 class="text-sm font-bold" style="color:#37474F">Triwulan {{ q.quarter }}</h4>
                                        <p class="text-xs" style="color:var(--color-text-secondary)">{{ q.periodLabel }}</p>
                                    </div>
                                    <span class="badge" :class="getPredicateBadgeClass(q.predicate)">{{ q.predicate }}</span>
                                </div>
                                <div class="text-center py-4 mb-4 rounded-xl" style="background:rgba(37,99,235,0.04)">
                                    <div class="text-4xl font-extrabold" style="color:var(--color-primary)">{{ formatNum(q.ikm) }}</div>
                                    <div class="text-xs font-semibold mt-1" style="color:var(--color-text-secondary)">Nilai IKM</div>
                                </div>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-xs" style="min-width:320px">
                                        <thead>
                                            <tr style="background:#f8fafc">
                                                <th class="text-left py-2 px-2 font-semibold" style="color:var(--color-text-secondary)">No</th>
                                                <th class="text-left py-2 px-2 font-semibold" style="color:var(--color-text-secondary)">Unsur Pelayanan</th>
                                                <th class="text-center py-2 px-2 font-semibold" style="color:var(--color-text-secondary)">Rata-rata</th>
                                                <th class="text-center py-2 px-2 font-semibold" style="color:var(--color-text-secondary)">Tertimbang</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="s in q.summaries" :key="s.number" style="border-bottom:1px solid #f5f5f5">
                                                <td class="py-2 px-2" style="color:var(--color-text-secondary)">{{ s.number }}</td>
                                                <td class="py-2 px-2" style="color:#37474F">{{ s.label }}</td>
                                                <td class="py-2 px-2 text-center font-semibold" style="color:#455A64">{{ formatNum(s.average) }}</td>
                                                <td class="py-2 px-2 text-center font-semibold" style="color:var(--color-primary)">{{ formatNum(s.weightedAverage) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot>
                                            <tr style="background:rgba(37,99,235,0.04)">
                                                <td colspan="2" class="py-2 px-2 font-bold" style="color:#37474F">Nilai Indeks</td>
                                                <td colspan="2" class="py-2 px-2 text-center font-extrabold" style="color:var(--color-primary)">{{ formatNum(q.indexValue) }}</td>
                                            </tr>
                                            <tr style="background:rgba(37,99,235,0.04)">
                                                <td colspan="2" class="py-2 px-2 font-bold" style="color:#37474F">Responden</td>
                                                <td colspan="2" class="py-2 px-2 text-center font-extrabold" style="color:var(--color-primary)">{{ q.respondents }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <div v-if="reportLinks[getPeriodeName(q, year)]" class="mt-4 text-center">
                                    <a :href="reportLinks[getPeriodeName(q, year)]" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-xs font-semibold py-2 px-4 rounded-lg transition-all hover:-translate-y-0.5" style="background:rgba(37,99,235,0.06);color:var(--color-primary)"><i class="fas fa-file-pdf text-[10px]"></i>Unduh Laporan</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="profile" class="py-20 relative overflow-hidden" style="background:#f8fafc">
            <div class="divider-gradient mb-20"></div>
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
                <div class="text-center mb-12">
                    <div class="section-label justify-center">Responden</div>
                    <h2 class="section-title">Karakteristik Responden</h2>
                    <p class="section-subtitle mx-auto">Data profil responden per tahun dan triwulan dari Google Sheet publik</p>
                </div>
                <div v-if="profileLoading" class="card p-10 text-center"><div class="skeleton h-8 w-64 mx-auto mb-4"></div><div class="skeleton h-48 w-full max-w-2xl mx-auto rounded-xl"></div></div>
                <div v-else-if="profileError" class="card p-10 text-center max-w-lg mx-auto">
                    <div class="w-16 h-16 rounded-full mx-auto mb-4 flex items-center justify-center" style="background:#fff8e1"><i class="fas fa-exclamation-triangle text-2xl" style="color:#f57f17"></i></div>
                    <p class="font-semibold mb-2" style="color:#37474F">Gagal Memuat Data</p>
                    <p class="text-sm" style="color:var(--color-text-secondary)">{{ profileError }}</p>
                </div>
                <div v-else-if="profileYears.length === 0" class="card p-10 text-center"><p style="color:var(--color-text-secondary)">Belum ada data karakteristik responden.</p></div>
                <div v-else class="space-y-8">
                    <div v-for="year in profileYears" :key="'p'+year" class="card overflow-hidden">
                        <div class="px-6 py-5" style="background:rgba(245,158,11,0.04);border-bottom:1px solid rgba(245,158,11,0.08)">
                            <h3 class="text-xl font-bold" style="color:#263238">Tahun {{ year }}</h3>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">
                            <div v-for="q in getProfileQuarters(year)" :key="'pq'+q.quarter" class="p-6" :class="{'lg:border-r': q.quarter % 2 !== 0}" style="border-bottom:1px solid #f0f0f0">
                                <h4 class="text-sm font-bold mb-4" style="color:#37474F">Triwulan {{ q.quarter }} <span class="font-normal text-xs" style="color:var(--color-text-secondary)">({{ q.periodLabel }})</span></h4>
                                <div v-if="q.rows.length === 0" class="text-center py-6 text-sm" style="color:var(--color-text-secondary)">Tidak ada data</div>
                                <div v-else class="overflow-x-auto">
                                    <table class="w-full text-xs" style="min-width:300px;border-collapse:collapse">
                                        <thead><tr style="background:#fff8e1"><th class="text-left py-2 px-2 border font-semibold" style="border-color:#f0e0b0;color:#6d4e0f">Karakteristik</th><th class="text-left py-2 px-2 border font-semibold" style="border-color:#f0e0b0;color:#6d4e0f">Indikator</th><th class="text-center py-2 px-2 border font-semibold" style="border-color:#f0e0b0;color:#6d4e0f">Jumlah</th><th class="text-center py-2 px-2 border font-semibold" style="border-color:#f0e0b0;color:#6d4e0f">%</th></tr></thead>
                                        <tbody><tr v-for="(r, ri) in q.rows" :key="ri"><td class="py-1.5 px-2 border font-semibold" style="border-color:#f0e0b0;color:#37474F">{{ r.characteristic }}</td><td class="py-1.5 px-2 border" style="border-color:#f0e0b0;color:#455A64">{{ r.indicator }}</td><td class="py-1.5 px-2 border text-center font-semibold" style="border-color:#f0e0b0;color:#37474F">{{ r.total }}</td><td class="py-1.5 px-2 border text-center font-semibold" style="border-color:#f0e0b0;color:#C68A00">{{ formatNum(r.percent) }}%</td></tr></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';

const SPREADSHEET_ID = '1DI1wH0rIkDW2MksadViG-a4TwBR48MItJ5yrnO317_Q';
const IKM_SHEET = 'KONVERSI NILAI';
const PROFILE_SHEET = 'DB';
const REPORT_SHEET = 'Laporan';
const WEIGHT = 0.111;
const QUARTERS = [
    { quarter: 1, periodLabel: 'Januari - Maret' },
    { quarter: 2, periodLabel: 'April - Juni' },
    { quarter: 3, periodLabel: 'Juli - September' },
    { quarter: 4, periodLabel: 'Oktober - Desember' },
];
const TARGETS = ['Persyaratan','Prosedur','Waktu Pelayanan','Akses Layanan','Kesesuaian Produk Layanan','Kompetensi Pelaksana','Perilaku Pelaksana','Kualitas Sarana dan Prasarana','Penanganan Pengaduan'];
const GENDER_ORDER = ['Laki-Laki','Perempuan','Tidak Diisi'];
const EDUCATION_ORDER = ['SD','SMP','SMA/SMK/MA','Diploma','S1','S2','S3','Tidak Diisi'];
const RESPONDENT_ORDER = ['Guru','Kepala Sekolah','Pengawas Sekolah','Tenaga Kependidikan','Pegawai Dinas Pendidikan dan Kebudayaan','Pegawai Bappeda/Bapperida','Pegawai BKD','Pegawai Dinas Kesehatan','Dosen / Akademisi','Peserta Didik / Mahasiswa','Masyarakat Umum / Lainnya','Tidak Diisi'];

const loading = ref(true);
const error = ref('');
const years = ref([]);
const quarterData = ref({});
const reportLinks = ref({});
const profileLoading = ref(true);
const profileError = ref('');
const profileYears = ref([]);
const profileData = ref({});

const totalQuarters = computed(() => Object.values(quarterData.value).reduce((s, arr) => s + arr.length, 0));
const totalRespondents = computed(() => Object.values(quarterData.value).flat().reduce((s, q) => s + q.respondents, 0));

function formatNum(v, d = 2) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: d }).format(v || 0); }
function getPredicate(ikm) { if (ikm < 65) return 'Tidak Baik'; if (ikm < 76.61) return 'Kurang Baik'; if (ikm < 88.31) return 'Baik'; return 'Sangat Baik'; }
function getPredicateBadgeClass(p) { return p === 'Sangat Baik' ? 'badge-success' : p === 'Baik' ? 'badge-primary' : p === 'Kurang Baik' ? 'badge-warning' : 'badge-danger'; }
function getPeriodeName(q, year) { return `Triwulan ${q.quarter} ${year}`; }
function getYearReportCount(year) { return (quarterData.value[year] || []).length; }
function getYearRespondents(year) { return (quarterData.value[year] || []).reduce((s, q) => s + q.respondents, 0); }
function getYearQuarters(year) { return quarterData.value[year] || []; }
function getProfileQuarters(year) { return profileData.value[year] || []; }

function scrollTo(id) { document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' }); }

function parseResp(text) { const s = text.indexOf('{'), e = text.lastIndexOf('}'); if (s === -1 || e === -1) throw new Error('Format tidak dikenali'); return JSON.parse(text.slice(s, e + 1)); }
function parseDate(cell) {
    if (!cell) return null;
    if (typeof cell.v === 'string') {
        const m = cell.v.match(/^Date\((\d+),(\d+),(\d+)(?:,(\d+),(\d+),(\d+))?\)$/);
        if (m) return new Date(+m[1], +m[2], +m[3], +(m[4]||0), +(m[5]||0), +(m[6]||0));
        const d = new Date(cell.v);
        if (!isNaN(d.getTime())) return d;
    }
    return null;
}
function colMap(cols) { return cols.reduce((m, c, i) => { const key = (c.label || '').trim(); if (key) m[key] = i; return m; }, {}); }
function findCol(cm, ...names) { for (const n of names) { if (cm[n] !== undefined) return cm[n]; } return undefined; }
function numVal(cell) { if (!cell) return null; if (typeof cell.v === 'number') return cell.v; if (typeof cell.v === 'string') { const n = Number(cell.v.replace(/\./g, '').replace(',', '.')); return Number.isFinite(n) ? n : null; } return null; }
function txtVal(cell) { if (!cell) return ''; if (typeof cell.v === 'string') return cell.v; if (typeof cell.v === 'number') return String(cell.v); if (typeof cell.f === 'string') return cell.f; return ''; }

async function fetchSheet(name) {
    const url = `https://docs.google.com/spreadsheets/d/${SPREADSHEET_ID}/gviz/tq?sheet=${encodeURIComponent(name)}&tqx=out:json`;
    const r = await fetch(url);
    if (!r.ok) throw new Error(`Gagal mengambil sheet "${name}" (${r.status})`);
    const text = await r.text();
    if (!text.includes('"cols"')) throw new Error(`Sheet "${name}" tidak mengembalikan data. Pastikan sheet publik (Anyone with link).`);
    return parseResp(text).table;
}

async function loadData() {
    loading.value = true; error.value = '';
    try {
        const [ikmTable, rptTable] = await Promise.all([fetchSheet(IKM_SHEET), fetchSheet(REPORT_SHEET)]);
        const rptMap = colMap(rptTable.cols);
        rptTable.rows.forEach(row => {
            const p = txtVal(row.c[rptMap['Judul']]).trim();
            const l = txtVal(row.c[rptMap['File Laporan']]).trim();
            if (p && l) reportLinks.value[p] = l;
        });
        const cm = colMap(ikmTable.cols);
        const dateIdx = findCol(cm, 'Tanggal Survei', 'Timestamp', 'Tanggal');
        if (dateIdx === undefined) throw new Error('Kolom tanggal tidak ditemukan. Kolom tersedia: ' + Object.keys(cm).join(', '));
        const missing = TARGETS.filter(h => cm[h] === undefined);
        if (missing.length) throw new Error(`Header tidak ditemukan: ${missing.join(', ')}`);
        const yrs = new Set();
        const qd = {};
        ikmTable.rows.forEach(row => {
            const dt = parseDate(row.c[dateIdx]);
            if (!dt || isNaN(dt.getTime())) return;
            const yr = dt.getFullYear(), qr = Math.floor(dt.getMonth() / 3) + 1;
            yrs.add(yr);
            if (!qd[yr]) qd[yr] = {};
            if (!qd[yr][qr]) qd[yr][qr] = [];
            qd[yr][qr].push(row);
        });
        const result = {};
        for (const yr of [...yrs].sort((a, b) => b - a)) {
            result[yr] = [];
            for (const qConf of QUARTERS) {
                const rows = (qd[yr] && qd[yr][qConf.quarter]) || [];
                if (!rows.length) continue;
                const summaries = TARGETS.map((h, i) => {
                    const ci = cm[h];
                    const vals = rows.map(r => numVal(r.c[ci])).filter(v => v !== null);
                    const avg = vals.length ? vals.reduce((s, v) => s + v, 0) / vals.length : 0;
                    return { number: i + 1, label: h, average: avg, weightedAverage: avg * WEIGHT };
                });
                const idx = summaries.reduce((s, i) => s + i.weightedAverage, 0);
                const ikm = idx * 25;
                result[yr].push({ quarter: qConf.quarter, periodLabel: qConf.periodLabel, summaries, indexValue: idx, ikm, predicate: getPredicate(ikm), respondents: rows.length });
            }
        }
        years.value = [...yrs].sort((a, b) => b - a);
        quarterData.value = result;
    } catch (e) { error.value = e.message; } finally { loading.value = false; }
}

async function loadProfile() {
    profileLoading.value = true; profileError.value = '';
    try {
        const table = await fetchSheet(PROFILE_SHEET);
        const cm = colMap(table.cols);
        const dateIdx = findCol(cm, 'Timestamp', 'Tanggal Survei', 'Tanggal');
        const jenisIdx = findCol(cm, 'Jenis atau Unsur Responden');
        const genderIdx = findCol(cm, 'Jenis Kelamin');
        const eduIdx = findCol(cm, 'Pendidikan Terakhir', 'Pendidikan');
        if (dateIdx === undefined) throw new Error('Kolom tanggal tidak ditemukan. Kolom tersedia: ' + Object.keys(cm).join(', '));
        const yrs = new Set();
        const grouped = {};
        table.rows.forEach(row => {
            const dt = parseDate(row.c[dateIdx]);
            if (!dt || isNaN(dt.getTime())) return;
            const yr = dt.getFullYear(), qr = Math.floor(dt.getMonth() / 3) + 1;
            yrs.add(yr);
            if (!grouped[yr]) grouped[yr] = {};
            if (!grouped[yr][qr]) grouped[yr][qr] = [];
            grouped[yr][qr].push(row);
        });
        const result = {};
        for (const yr of [...yrs].sort((a, b) => b - a)) {
            result[yr] = [];
            for (const qConf of QUARTERS) {
                const rows = (grouped[yr] && grouped[yr][qConf.quarter]) || [];
                const profileRows = [];
                if (rows.length) {
                    const genderCount = {};
                    const eduCount = {};
                    const typeCount = {};
                    rows.forEach(r => {
                        const g = genderIdx !== undefined ? txtVal(r.c[genderIdx]).trim() : '';
                        const e = eduIdx !== undefined ? txtVal(r.c[eduIdx]).trim() : '';
                        const t = jenisIdx !== undefined ? txtVal(r.c[jenisIdx]).trim() : '';
                        genderCount[g || 'Tidak Diisi'] = (genderCount[g || 'Tidak Diisi'] || 0) + 1;
                        eduCount[e || 'Tidak Diisi'] = (eduCount[e || 'Tidak Diisi'] || 0) + 1;
                        typeCount[t || 'Tidak Diisi'] = (typeCount[t || 'Tidak Diisi'] || 0) + 1;
                    });
                    const total = rows.length;
                    const addRows = (counts, order, label) => {
                        const sorted = Object.entries(counts).sort((a, b) => { const ai = order.indexOf(a[0]), bi = order.indexOf(b[0]); return (ai === -1 ? 999 : ai) - (bi === -1 ? 999 : bi); });
                        sorted.forEach(([k, v]) => { profileRows.push({ characteristic: label, indicator: k, total: v, percent: (v / total) * 100 }); });
                    };
                    addRows(genderCount, GENDER_ORDER, 'Jenis Kelamin');
                    addRows(eduCount, EDUCATION_ORDER, 'Pendidikan');
                    addRows(typeCount, RESPONDENT_ORDER, 'Jenis Responden');
                }
                result[yr].push({ quarter: qConf.quarter, periodLabel: qConf.periodLabel, rows: profileRows });
            }
        }
        profileYears.value = [...yrs].sort((a, b) => b - a);
        profileData.value = result;
    } catch (e) { profileError.value = e.message; } finally { profileLoading.value = false; }
}

onMounted(() => { loadData(); loadProfile(); });
</script>
