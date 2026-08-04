<template>
<div>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2" style="color:var(--color-text-primary)">
                <span style="color:var(--color-primary)">🎯</span> Intent Chatbot
            </h2>
            <p class="text-sm mt-1" style="color:var(--color-text-secondary)">Kelola intent untuk respon otomatis chatbot SI INTAN</p>
        </div>
        <button class="btn-primary" @click="openAddModal"><i class="fas fa-plus mr-2"></i>Tambah Intent</button>
    </div>

    <div class="card p-4 mb-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94a3b8"><i class="fas fa-search"></i></span>
                <input v-model="searchQuery" @input="debounceSearch" type="text" class="input-field pl-10" placeholder="Cari kata kunci atau respon..." />
                <button v-if="searchQuery" @click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-sm" style="color:#94a3b8">✕</button>
            </div>
            <div class="flex items-center gap-2 text-sm" style="color:var(--color-text-secondary)">
                <span class="font-semibold" style="color:var(--color-text-primary)">{{ totalItems }}</span> intent ditemukan
            </div>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th class="table-header" style="width:50px">#</th>
                        <th class="table-header">Kata Kunci</th>
                        <th class="table-header">Respon</th>
                        <th class="table-header" style="width:120px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, idx) in filteredIntents" :key="item.id" class="border-t" style="border-color:#f1f5f9;">
                        <td class="table-cell" style="color:#94a3b8">{{ (currentPage - 1) * perPage + idx + 1 }}</td>
                        <td class="table-cell">
                            <span class="badge-keyword">{{ item.keyword }}</span>
                        </td>
                        <td class="table-cell">
                            <div class="response-preview">{{ truncate(item.response, 120) }}</div>
                        </td>
                        <td class="table-cell">
                            <div class="flex gap-1">
                                <button @click="openEditModal(item)" class="action-btn blue" title="Edit"><i class="fas fa-edit"></i></button>
                                <button @click="deleteIntent(item.id)" class="action-btn red" title="Hapus"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="loading" class="text-center py-12">
            <div class="spinner"></div>
            <p class="text-sm mt-3" style="color:#94a3b8">Memuat data...</p>
        </div>

        <div v-else-if="!filteredIntents.length" class="text-center py-12">
            <div style="font-size:48px;opacity:.4;margin-bottom:12px">🎯</div>
            <p class="text-sm font-medium" style="color:#64748b">{{ searchQuery ? 'Tidak ditemukan intent yang cocok' : 'Belum ada intent' }}</p>
            <p class="text-xs mt-1" style="color:#94a3b8">{{ searchQuery ? 'Coba kata kunci lain' : 'Klik tombol "Tambah Intent" untuk menambahkan' }}</p>
        </div>

        <div v-if="totalPages > 1" class="flex items-center justify-between px-4 py-3 border-t" style="border-color:#f1f5f9;">
            <span class="text-xs" style="color:#94a3b8">Halaman {{ currentPage }} dari {{ totalPages }}</span>
            <div class="flex gap-1">
                <button @click="goToPage(currentPage - 1)" :disabled="currentPage <= 1" class="page-btn">&laquo;</button>
                <button v-for="p in visiblePages" :key="p" @click="goToPage(p)" class="page-btn" :class="{active: p === currentPage}">{{ p }}</button>
                <button @click="goToPage(currentPage + 1)" :disabled="currentPage >= totalPages" class="page-btn">&raquo;</button>
            </div>
        </div>
    </div>

    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="text-lg font-bold" style="color:#0f172a">{{ editingId ? 'Edit' : 'Tambah' }} Intent</h3>
                <button @click="closeModal" class="modal-close">✕</button>
            </div>
            <form @submit.prevent="saveIntent" class="modal-body">
                <div class="mb-4">
                    <label class="input-label">Kata Kunci <span class="text-red-500">*</span></label>
                    <input v-model="form.keyword" type="text" class="input-field" placeholder="Contoh: layanan, ppdb, kurikulum" required maxlength="128" />
                    <p class="text-xs mt-1" style="color:#94a3b8">Kata kunci yang akan memicu respon ini (maks 128 karakter)</p>
                </div>
                <div class="mb-6">
                    <label class="input-label">Respon <span class="text-red-500">*</span></label>
                    <textarea v-model="form.response" rows="5" class="input-field" placeholder="Tulis respon yang akan diberikan chatbot..." required></textarea>
                    <p class="text-xs mt-1" style="color:#94a3b8">Respon akan digunakan oleh AI sebagai panduan jawaban</p>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="closeModal" class="btn-ghost border" style="border-color:#e2e8f0">Batal</button>
                    <button type="submit" class="btn-primary" :disabled="saving">
                        <i v-if="saving" class="fas fa-spinner fa-spin mr-2"></i>
                        <i v-else class="fas fa-save mr-2"></i>
                        {{ editingId ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/bootstrap.js';
import { swalConfirm, swalError, swalSuccess } from '@/swal.js';

const intents = ref([]);
const loading = ref(false);
const saving = ref(false);
const searchQuery = ref('');
const showModal = ref(false);
const editingId = ref(null);
const form = ref({ keyword: '', response: '' });
const currentPage = ref(1);
const perPage = 15;
let searchTimer = null;

const totalItems = computed(() => filteredIntents.value.length);
const filteredIntents = computed(() => {
    if (!searchQuery.value) return paginatedIntents.value;
    const q = searchQuery.value.toLowerCase();
    const all = intents.value.filter(i =>
        (i.keyword || '').toLowerCase().includes(q) ||
        (i.response || '').toLowerCase().includes(q)
    );
    return all.slice((currentPage.value - 1) * perPage, currentPage.value * perPage);
});
const paginatedIntents = computed(() => {
    return intents.value.slice((currentPage.value - 1) * perPage, currentPage.value * perPage);
});
const totalPages = computed(() => {
    const source = searchQuery.value
        ? intents.value.filter(i => (i.keyword||'').toLowerCase().includes(searchQuery.value.toLowerCase()) || (i.response||'').toLowerCase().includes(searchQuery.value.toLowerCase()))
        : intents.value;
    return Math.ceil(source.length / perPage) || 1;
});
const visiblePages = computed(() => {
    const pages = [];
    const start = Math.max(1, currentPage.value - 2);
    const end = Math.min(totalPages.value, start + 4);
    for (let i = start; i <= end; i++) pages.push(i);
    return pages;
});

function truncate(str, len) { return str && str.length > len ? str.substring(0, len) + '...' : (str || '-'); }

function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => { currentPage.value = 1; }, 400);
}

function clearSearch() { searchQuery.value = ''; currentPage.value = 1; }
function goToPage(p) { if (p >= 1 && p <= totalPages.value) currentPage.value = p; }

function openAddModal() {
    editingId.value = null;
    form.value = { keyword: '', response: '' };
    showModal.value = true;
}

function openEditModal(item) {
    editingId.value = item.id;
    form.value = { keyword: item.keyword, response: item.response };
    showModal.value = true;
}

function closeModal() { showModal.value = false; editingId.value = null; }

async function loadIntents() {
    loading.value = true;
    try {
        const { data } = await api.get('/chatbot-intents');
        intents.value = Array.isArray(data) ? data : (data.data || []);
    } catch (e) { console.error(e); }
    finally { loading.value = false; }
}

async function saveIntent() {
    if (!form.value.keyword.trim() || !form.value.response.trim()) return;
    saving.value = true;
    try {
        if (editingId.value) {
            await api.put(`/chatbot-intents/${editingId.value}`, form.value);
        } else {
            await api.post('/chatbot-intents', form.value);
        }
        swalSuccess(editingId.value ? 'Intent berhasil diupdate!' : 'Intent berhasil ditambahkan!');
        closeModal();
        await loadIntents();
    } catch (e) { swalError('Gagal menyimpan intent.'); }
    finally { saving.value = false; }
}

async function deleteIntent(id) {
    if (!await swalConfirm('Hapus intent ini?')) return;
    try {
        await api.delete(`/chatbot-intents/${id}`);
        swalSuccess('Intent berhasil dihapus!');
        await loadIntents();
    } catch (e) { swalError('Gagal menghapus intent.'); }
}

onMounted(loadIntents);
</script>

<style scoped>
.input-field { width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px; outline:none; transition:border-color .2s; font-family:'Quicksand',sans-serif; }
.input-field:focus { border-color:#60a5fa; box-shadow:0 0 0 3px rgba(59,130,246,.1); }
.input-label { display:block; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; margin-bottom:6px; color:#475569; }
.badge-keyword { display:inline-block; background:#eff6ff; color:#2563eb; border-radius:8px; padding:3px 10px; font-size:12px; font-weight:700; }
.response-preview { font-size:13px; color:#475569; line-height:1.5; max-width:400px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.action-btn { padding:6px 10px; border-radius:8px; font-size:12px; cursor:pointer; transition:all .15s; border:1px solid; }
.action-btn.blue { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
.action-btn.blue:hover { background:#dbeafe; }
.action-btn.red { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
.action-btn.red:hover { background:#fee2e2; }
.table-header { background:#f8fafc; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.03em; text-align:left; padding:10px 12px; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.table-cell { padding:12px; color:#0f172a; }
.page-btn { padding:6px 10px; border:1px solid #e2e8f0; border-radius:8px; font-size:12px; font-weight:600; cursor:pointer; background:#fff; color:#475569; transition:all .15s; font-family:'Quicksand',sans-serif; }
.page-btn:hover:not(:disabled) { background:#eff6ff; border-color:#bfdbfe; color:#2563eb; }
.page-btn.active { background:#2563eb; color:#fff; border-color:#2563eb; }
.page-btn:disabled { opacity:.4; cursor:not-allowed; }
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.4); backdrop-filter:blur(4px); z-index:9999; display:flex; align-items:center; justify-content:center; animation:fadeIn .2s ease; }
.modal-content { background:#fff; border-radius:18px; width:100%; max-width:520px; margin:16px; box-shadow:0 24px 54px rgba(2,6,23,.25); animation:slideUp .25s ease; }
.modal-header { display:flex; align-items:center; justify-content:space-between; padding:18px 20px; border-bottom:1px solid #f1f5f9; }
.modal-close { background:none; border:none; font-size:18px; color:#94a3b8; cursor:pointer; padding:4px 8px; border-radius:6px; }
.modal-close:hover { background:#f1f5f9; color:#475569; }
.modal-body { padding:20px; }
.spinner { width:36px; height:36px; border:3px solid #e5e7eb; border-top-color:#3b82f6; border-radius:50%; animation:spin .7s linear infinite; margin:0 auto; }
@keyframes spin { to { transform:rotate(360deg) } }
@keyframes fadeIn { from { opacity:0 } to { opacity:1 } }
@keyframes slideUp { from { opacity:0; transform:translateY(16px) } to { opacity:1; transform:translateY(0) } }
</style>