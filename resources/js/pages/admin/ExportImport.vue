<template>
<div>
    <h2 class="text-2xl font-bold mb-2" style="color:var(--color-text-primary)">Export & Import Data</h2>
    <p class="text-sm mb-6" style="color:var(--color-text-secondary)">Kelola data {{ groupLabel }} — export untuk backup, import untuk restore</p>

    <div v-if="loadingTypes" class="text-center py-12">
        <i class="fas fa-spinner fa-spin text-2xl" style="color:var(--color-text-secondary)"></i>
    </div>

    <div v-else>
        <div class="card p-4 mb-6">
            <label class="input-label">Pilih Data</label>
            <div class="flex flex-wrap gap-2 mt-2">
                <button v-for="t in groupTypes" :key="t.key" @click="selectType(t)"
                    class="type-chip" :class="{'active': selected?.key === t.key}">
                    {{ t.label }}
                    <span class="type-count">{{ t.count }}</span>
                </button>
            </div>
        </div>

        <div v-if="selected" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)">
                    <i class="fas fa-download" style="color:var(--color-primary)"></i>Export {{ selected.label }}
                </h3>
                <div v-if="selected.has_date" class="mb-4">
                    <label class="input-label">Periode Data</label>
                    <div class="grid grid-cols-2 gap-3 mt-2">
                        <div>
                            <label class="text-xs mb-1 block" style="color:var(--color-text-secondary)">Dari Tanggal</label>
                            <input type="date" v-model="dateFrom" class="input-field">
                        </div>
                        <div>
                            <label class="text-xs mb-1 block" style="color:var(--color-text-secondary)">Sampai Tanggal</label>
                            <input type="date" v-model="dateTo" class="input-field">
                        </div>
                    </div>
                    <div class="flex items-center gap-2 mt-2">
                        <button @click="setQuickDate('7')" class="quick-btn">7 Hari</button>
                        <button @click="setQuickDate('30')" class="quick-btn">30 Hari</button>
                        <button @click="setQuickDate('90')" class="quick-btn">3 Bulan</button>
                        <button @click="setQuickDate('365')" class="quick-btn">1 Tahun</button>
                        <button @click="dateFrom='';dateTo=''" class="quick-btn">Semua</button>
                    </div>
                </div>
                <div class="mb-4 p-3 rounded-lg bg-gray-50 border border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-xs" style="color:var(--color-text-secondary)">{{ dateFrom || dateTo ? 'Data dalam periode' : 'Total data' }}</span>
                        <span class="text-lg font-bold" style="color:var(--color-text-primary)">{{ selected.count }}</span>
                    </div>
                </div>
                <div class="space-y-3">
                    <button @click="doExport('json')" :disabled="exporting || selected.count === 0" class="btn-primary w-full">
                        <i :class="exporting ? 'fa-spinner fa-spin' : 'fa-file-code'" class="fas mr-2"></i>Export JSON
                    </button>
                    <button @click="doExport('xlsx')" :disabled="exporting || selected.count === 0" class="btn-success w-full">
                        <i :class="exporting ? 'fa-spinner fa-spin' : 'fa-file-excel'" class="fas mr-2"></i>Export Excel (.xlsx)
                    </button>
                </div>
                <div class="mt-3 p-3 rounded-lg bg-blue-50 border border-blue-100">
                    <p class="text-xs text-blue-700"><i class="fas fa-info-circle mr-1"></i>Export JSON dapat di-import kembali.{{ selected.is_konten ? ' Link lengkap konten disertakan.' : '' }}</p>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)">
                    <i class="fas fa-upload" style="color:var(--color-accent)"></i>Import {{ selected.label }}
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="input-label">File Import (.json / .xlsx)</label>
                        <div class="upload-zone" @click="$refs.importInput.click()" @dragover.prevent="dragOver=true" @dragleave="dragOver=false" @drop.prevent="handleDrop" :class="{'drag-over': dragOver}">
                            <input ref="importInput" type="file" accept=".json,.xlsx,.xls" class="hidden" @change="handleFile">
                            <div v-if="!importFile" class="text-center">
                                <i class="fas fa-cloud-upload-alt text-2xl mb-2" style="color:var(--color-text-secondary)"></i>
                                <p class="text-sm" style="color:var(--color-text-secondary)">Klik atau drag file ke sini</p>
                            </div>
                            <div v-else class="flex items-center gap-3">
                                <i class="fas fa-file text-xl" style="color:var(--color-primary)"></i>
                                <div>
                                    <p class="text-sm font-semibold" style="color:var(--color-text-primary)">{{ importFile.name }}</p>
                                    <p class="text-xs" style="color:var(--color-text-secondary)">{{ formatSize(importFile.size) }}</p>
                                </div>
                                <button @click.stop="clearImport" class="ml-auto text-red-400 hover:text-red-600"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <button @click="doImport" :disabled="importing || !importFile" class="btn-warning w-full">
                        <i :class="importing ? 'fa-spinner fa-spin' : 'fa-upload'" class="fas mr-2"></i>{{ importing ? 'Mengimport...' : 'Import Data' }}
                    </button>
                    <button @click="downloadTemplate" :disabled="downloading" class="btn-template w-full">
                        <i :class="downloading ? 'fa-spinner fa-spin' : 'fa-download'" class="fas mr-2"></i>Download Template
                    </button>
                </div>

                <div v-if="importResult" class="mt-4 p-3 rounded-lg" :class="importResult.error ? 'bg-red-50 border border-red-100' : 'bg-green-50 border border-green-100'">
                    <p class="text-xs font-semibold" :class="importResult.error ? 'text-red-700' : 'text-green-700'">
                        <i :class="importResult.error ? 'fa-times-circle' : 'fa-check-circle'" class="fas mr-1"></i>
                        {{ importResult.error || importResult.message }}
                    </p>
                    <div v-if="importResult.errors && importResult.errors.length" class="mt-2">
                        <p class="text-xs text-red-500 font-semibold">Detail error:</p>
                        <ul class="text-xs text-red-400 list-disc ml-4">
                            <li v-for="(err, i) in importResult.errors" :key="i">{{ err }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="card p-12 text-center">
            <i class="fas fa-database text-4xl mb-3" style="color:var(--color-text-secondary)"></i>
            <p class="text-sm" style="color:var(--color-text-secondary)">Pilih jenis data di atas untuk export atau import</p>
        </div>
    </div>
</div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/bootstrap.js';
import { swalSuccess, swalError } from '@/swal.js';

const route = useRoute();
const allTypes = ref([]);
const loadingTypes = ref(true);
const exporting = ref(false);
const importing = ref(false);
const downloading = ref(false);
const dragOver = ref(false);
const importFile = ref(null);
const importInput = ref(null);
const importResult = ref(null);
const selected = ref(null);
const dateFrom = ref('');
const dateTo = ref('');

const groupMap = {
    'konten': ['konten-berita','konten-artikel','konten-buletin','konten-jurnal','konten-kliping','konten-pengumuman','konten-galeri','konten-unduhan','konten-profil','konten-renstra','konten-lakin','konten-perjanjian_kinerja'],
    'kategori': ['kategori-berita','kategori-artikel','kategori-buletin','kategori-jurnal','kategori-kliping','kategori-pengumuman','kategori-galeri','kategori-unduhan','kategori-profil','kategori-renstra','kategori-lakin','kategori-perjanjian_kinerja'],
    'media': ['sliders','layanans','external-links'],
    'chatbot': ['chatbot-responses','chatbot-intents','ai-configs','chatbot-settings'],
    'broadcast': ['broadcast'],
    'ppid': ['ppid-informations','ppid-standards','ppid-regulations','ppid-external-links'],
    'pengaturan': ['settings','users'],
};

const groupLabels = {
    'konten': 'Konten', 'kategori': 'Kategori', 'media': 'Media',
    'chatbot': 'Si Intan', 'broadcast': 'Broadcast', 'ppid': 'PPID', 'pengaturan': 'Pengaturan',
};

const group = computed(() => route.params.group || '');
const groupLabel = computed(() => groupLabels[group.value] || group.value);
const groupTypes = computed(() => {
    const keys = groupMap[group.value] || [];
    return allTypes.value.filter(t => keys.includes(t.key));
});

function selectType(t) {
    selected.value = t;
    importFile.value = null;
    importResult.value = null;
    dateFrom.value = '';
    dateTo.value = '';
}

function setQuickDate(days) {
    const to = new Date();
    const from = new Date();
    from.setDate(from.getDate() - parseInt(days));
    dateFrom.value = from.toISOString().slice(0, 10);
    dateTo.value = to.toISOString().slice(0, 10);
}

async function loadTypes() {
    loadingTypes.value = true;
    try {
        const { data } = await api.get('/export-import/types');
        allTypes.value = data;
        if (groupTypes.value.length === 1) {
            selected.value = groupTypes.value[0];
        }
    } catch (e) {}
    loadingTypes.value = false;
}

async function doExport(format) {
    if (!selected.value) return;
    exporting.value = true;
    try {
        const params = { format };
        if (dateFrom.value) params.from = dateFrom.value;
        if (dateTo.value) params.to = dateTo.value;
        const response = await api.get(`/export-import/${selected.value.key}`, {
            params,
            responseType: format === 'xlsx' ? 'blob' : 'json',
        });
        const ext = format === 'xlsx' ? 'xlsx' : 'json';
        const mime = format === 'xlsx' ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' : 'application/json';
        const blob = format === 'xlsx' ? response.data : new Blob([JSON.stringify(response.data, null, 2)], { type: mime });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `export_${selected.value.key}_${new Date().toISOString().slice(0,10)}.${ext}`;
        a.click();
        URL.revokeObjectURL(url);
        swalSuccess(`${selected.value.label} berhasil di-export`);
    } catch (e) {
        swalError('Gagal export data');
    }
    exporting.value = false;
}

function handleFile(e) {
    importFile.value = e.target.files?.[0] || null;
    importResult.value = null;
}

function handleDrop(e) {
    dragOver.value = false;
    importFile.value = e.dataTransfer.files?.[0] || null;
    importResult.value = null;
}

function clearImport() {
    importFile.value = null;
    importResult.value = null;
    if (importInput.value) importInput.value.value = '';
}

async function doImport() {
    if (!importFile.value || !selected.value) return;
    importing.value = true;
    importResult.value = null;
    const fd = new FormData();
    fd.append('file', importFile.value);
    try {
        const { data } = await api.post(`/export-import/${selected.value.key}`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        importResult.value = data;
        loadTypes();
        swalSuccess(data.message || 'Import berhasil');
    } catch (e) {
        const msg = e.response?.data?.error || e.response?.data?.message || 'Gagal import data';
        importResult.value = { error: msg };
        swalError(msg);
    }
    importing.value = false;
}

async function downloadTemplate() {
    if (!selected.value) return;
    downloading.value = true;
    try {
        const response = await api.get(`/export-import/${selected.value.key}/template`, { responseType: 'blob' });
        const url = URL.createObjectURL(response.data);
        const a = document.createElement('a');
        a.href = url;
        a.download = `template_${selected.value.key}.xlsx`;
        a.click();
        URL.revokeObjectURL(url);
        swalSuccess('Template berhasil di-download');
    } catch (e) {
        swalError('Gagal download template');
    }
    downloading.value = false;
}

function formatSize(bytes) {
    if (!bytes) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

watch(() => route.params.group, () => {
    selected.value = null;
    importFile.value = null;
    importResult.value = null;
    dateFrom.value = '';
    dateTo.value = '';
});

onMounted(() => { loadTypes(); });
</script>

<style scoped>
.input-label {
    @apply block text-xs font-semibold uppercase tracking-wider mb-1.5;
    color: var(--color-text-secondary);
}
.type-chip {
    @apply px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors border cursor-pointer;
    border-color: var(--color-border);
    color: var(--color-text-primary);
    background: var(--color-bg);
}
.type-chip:hover { background: var(--color-border); }
.type-chip.active {
    background: var(--color-primary);
    color: #fff;
    border-color: var(--color-primary);
}
.type-chip.active .type-count {
    background: rgba(255,255,255,0.25);
    color: #fff;
}
.type-count {
    @apply ml-1.5 px-1.5 py-0.5 rounded-full text-[10px] font-bold;
    background: var(--color-border);
    color: var(--color-text-secondary);
}
.btn-primary {
    @apply px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-colors;
    background: var(--color-primary);
}
.btn-primary:hover:not(:disabled) { opacity: 0.9; }
.btn-primary:disabled { @apply opacity-50 cursor-not-allowed; }
.btn-success {
    @apply px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-colors;
    background: #16a34a;
}
.btn-success:hover:not(:disabled) { background: #15803d; }
.btn-success:disabled { @apply opacity-50 cursor-not-allowed; }
.btn-warning {
    @apply px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-colors;
    background: #f59e0b;
}
.btn-warning:hover:not(:disabled) { background: #d97706; }
.btn-warning:disabled { @apply opacity-50 cursor-not-allowed; }
.btn-template {
    @apply px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors border;
    border-color: var(--color-border);
    color: var(--color-text-primary);
    background: var(--color-bg);
}
.btn-template:hover:not(:disabled) { background: var(--color-border); }
.btn-template:disabled { @apply opacity-50 cursor-not-allowed; }
.upload-zone {
    @apply border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-colors;
    border-color: var(--color-border);
}
.upload-zone:hover, .upload-zone.drag-over {
    border-color: var(--color-primary);
    background: rgba(var(--color-primary-rgb), 0.03);
}
.input-field {
    @apply w-full px-3 py-2 border rounded-lg text-sm;
    border-color: var(--color-border);
    background: var(--color-bg);
    color: var(--color-text-primary);
}
.input-field:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 0 0 2px rgba(var(--color-primary-rgb), 0.15);
}
.quick-btn {
    @apply px-2 py-1 rounded text-[10px] font-semibold transition-colors border cursor-pointer;
    border-color: var(--color-border);
    color: var(--color-text-secondary);
    background: var(--color-bg);
}
.quick-btn:hover {
    background: var(--color-primary);
    color: #fff;
    border-color: var(--color-primary);
}
</style>
