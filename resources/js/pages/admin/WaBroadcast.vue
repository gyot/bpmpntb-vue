<template>
<div>
    <h2 class="text-2xl font-bold mb-2" style="color:var(--color-text-primary)">Broadcast WhatsApp</h2>
    <p class="text-sm mb-6" style="color:var(--color-text-secondary)">Kirim pesan WhatsApp personal ke banyak nomor sekaligus via Excel</p>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)">
                    <i class="fas fa-file-excel" style="color:var(--color-primary)"></i>Upload Data Excel
                </h3>
                <div class="space-y-4">
                    <div>
                        <label class="input-label">File Excel (.xlsx / .xls)</label>
                        <div class="upload-zone" @click="$refs.fileInput.click()" @dragover.prevent="dragOver=true" @dragleave="dragOver=false" @drop.prevent="handleDrop" :class="{'drag-over': dragOver}">
                            <input ref="fileInput" type="file" accept=".xlsx,.xls" class="hidden" @change="handleFile">
                            <div v-if="!fileName" class="text-center">
                                <i class="fas fa-cloud-upload-alt text-3xl mb-2" style="color:var(--color-text-secondary)"></i>
                                <p class="text-sm" style="color:var(--color-text-secondary)">Klik atau drag file Excel ke sini</p>
                                <p class="text-xs mt-1" style="color:var(--color-text-secondary)">Format kolom: <b>no</b>, <b>nama</b>, <b>nomor whatsapp</b></p>
                            </div>
                            <div v-else class="flex items-center gap-3">
                                <i class="fas fa-file-excel text-2xl text-green-600"></i>
                                <div>
                                    <p class="text-sm font-semibold" style="color:var(--color-text-primary)">{{ fileName }}</p>
                                    <p class="text-xs" style="color:var(--color-text-secondary)">{{ contacts.length }} kontak ditemukan</p>
                                </div>
                                <button @click.stop="clearFile" class="ml-auto text-red-400 hover:text-red-600">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <button @click="downloadTemplate" class="btn-template">
                                <i class="fas fa-download mr-2"></i>Download Template Excel
                            </button>
                        </div>
                        <div class="mt-2 p-3 rounded-lg bg-blue-50 border border-blue-100">
                            <p class="text-xs text-blue-700"><i class="fas fa-info-circle mr-1"></i>Kolom yang dibutuhkan: <b>no</b> (nomor urut), <b>nama</b> (nama penerima), <b>nomor whatsapp</b> (nomor HP). Nama kolom tidak case-sensitive.</p>
                        </div>
                    </div>

                    <div>
                        <label class="input-label">Pesan Broadcast</label>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs px-2 py-1 rounded cursor-pointer tag-btn" @click="insertPlaceholder('{nama}')">{ nama }</span>
                            <span class="text-xs px-2 py-1 rounded cursor-pointer tag-btn" @click="insertPlaceholder('{instansi}')">{ instansi }</span>
                        </div>
                        <textarea ref="msgInput" v-model="form.message" class="input-field" rows="6"
                            placeholder="Contoh: Halo {nama}, kami informasikan bahwa..."></textarea>
                        <p class="text-xs mt-1" style="color:var(--color-text-secondary)">Gunakan <code>{'{nama}'}</code> untuk menyisipkan nama penerima. Kolom lain dari Excel juga bisa dipakai: <code>{'{instansi}'}</code> dll.</p>
                    </div>

                    <div class="flex items-center gap-3 flex-wrap">
                        <button @click="sendBroadcast" :disabled="sending || !contacts.length || !form.message.trim()" class="btn-primary">
                            <i :class="sending ? 'fa-spinner fa-spin' : 'fa-paper-plane'" class="fas mr-2"></i>
                            {{ sending ? 'Mengirim...' : 'Kirim Broadcast' }}
                        </button>
                        <button @click="loadUsers" :disabled="loadingUsers" class="btn-secondary">
                            <i :class="loadingUsers ? 'fa-spinner fa-spin' : 'fa-users'" class="fas mr-2"></i>
                            Ambil dari User Chatbot
                        </button>
                        <button v-if="contacts.length" @click="showPreview = !showPreview" class="btn-secondary">
                            <i class="fas fa-eye mr-2"></i>{{ showPreview ? 'Sembunyikan' : 'Preview' }} Data
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="sending" class="card p-6">
                <h3 class="text-sm font-semibold mb-4 flex items-center gap-2" style="color:var(--color-text-primary)">
                    <i class="fas fa-satellite-dish" style="color:var(--color-primary)"></i>Progres Pengiriman
                </h3>
                <div class="mb-3">
                    <div class="flex justify-between items-center mb-1.5">
                        <span class="text-xs font-semibold" style="color:var(--color-text-secondary)">{{ progress.current }} dari {{ progress.total }} kontak</span>
                        <span class="text-xs font-bold" style="color:var(--color-text-primary)">{{ progressPercent }}%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-bar" :style="{width: progressPercent + '%'}"></div>
                    </div>
                </div>
                <div class="flex items-center gap-4 text-xs" style="color:var(--color-text-secondary)">
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-green-500"></span> Berhasil: {{ progress.sent }}</span>
                    <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Gagal: {{ progress.current - progress.sent }}</span>
                    <span v-if="waitingDelay > 0" class="flex items-center gap-1 text-amber-600">
                        <i class="fas fa-clock"></i> Menunggu {{ waitingDelay }}dtk...
                    </span>
                </div>
                <div v-if="progress.currentResult" class="mt-3 p-3 rounded-lg text-xs" :class="progress.currentResult.success ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100'">
                    <span :class="progress.currentResult.success ? 'text-green-700' : 'text-red-700'">
                        <i :class="progress.currentResult.success ? 'fa-check-circle' : 'fa-times-circle'" class="fas mr-1"></i>
                        {{ progress.currentResult.nama || progress.currentResult.number }} — {{ progress.currentResult.success ? 'Berhasil' : 'Gagal' }}
                        <span v-if="progress.currentResult.error" class="text-red-500">({{ progress.currentResult.error }})</span>
                    </span>
                </div>
            </div>

            <div v-if="contacts.length && showPreview" class="card p-6">
                <h3 class="text-sm font-semibold mb-4 flex items-center gap-2" style="color:var(--color-text-primary)">
                    <i class="fas fa-table" style="color:var(--color-primary)"></i>Preview Data ({{ contacts.length }} kontak)
                </h3>
                <div class="overflow-x-auto max-h-80 overflow-y-auto">
                    <table class="w-full text-sm">
                        <thead class="sticky top-0 bg-white">
                            <tr class="border-b" style="border-color:var(--color-border)">
                                <th class="text-left py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">No</th>
                                <th class="text-left py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">Nama</th>
                                <th class="text-left py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">Nomor Asli</th>
                                <th class="text-left py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">Nomor Diformat</th>
                                <th class="text-left py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">Pesan</th>
                                <th class="text-center py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(c, i) in contacts" :key="i" class="border-b" style="border-color:var(--color-border)">
                                <td class="py-2 px-3" style="color:var(--color-text-primary)">{{ c.no || i + 1 }}</td>
                                <td class="py-2 px-3" style="color:var(--color-text-primary)">{{ c.nama || '-' }}</td>
                                <td class="py-2 px-3" style="color:var(--color-text-secondary)">{{ c.rawNumber }}</td>
                                <td class="py-2 px-3">
                                    <span :class="c.valid ? 'text-green-600' : 'text-red-500'" class="font-mono text-xs">
                                        {{ c.number }} <i :class="c.valid ? 'fa-check-circle' : 'fa-times-circle'" class="fas ml-1"></i>
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-xs truncate max-w-[200px]" style="color:var(--color-text-secondary)">{{ previewMessage(c) }}</td>
                                <td class="py-2 px-3 text-center">
                                    <button @click="openPreviewDetail(c)" class="btn-detail" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="results.length" class="card p-6">
                <h3 class="text-sm font-semibold mb-4 flex items-center gap-2" style="color:var(--color-text-primary)">
                    <i class="fas fa-list-check" style="color:var(--color-primary)"></i>Hasil Pengiriman
                </h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b" style="border-color:var(--color-border)">
                                <th class="text-left py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">No</th>
                                <th class="text-left py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">Nama</th>
                                <th class="text-left py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">Nomor</th>
                                <th class="text-left py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">Status</th>
                                <th class="text-center py-2 px-3 font-semibold" style="color:var(--color-text-secondary)">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(r, i) in results" :key="i" class="border-b" style="border-color:var(--color-border)">
                                <td class="py-2 px-3" style="color:var(--color-text-primary)">{{ i + 1 }}</td>
                                <td class="py-2 px-3" style="color:var(--color-text-primary)">{{ r.nama || '-' }}</td>
                                <td class="py-2 px-3" style="color:var(--color-text-primary)">{{ r.number }}</td>
                                <td class="py-2 px-3">
                                    <span v-if="r.success" class="inline-flex items-center gap-1 text-green-600 text-xs font-semibold">
                                        <i class="fas fa-check-circle"></i> Berhasil
                                    </span>
                                    <span v-else class="inline-flex items-center gap-1 text-red-600 text-xs font-semibold">
                                        <i class="fas fa-times-circle"></i> Gagal
                                        <span v-if="r.error" class="font-normal text-red-400">({{ r.error }})</span>
                                    </span>
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <button @click="openDetail(r)" class="btn-detail" title="Lihat Detail">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 p-3 rounded-lg" :class="allSuccess ? 'bg-green-50 border border-green-100' : 'bg-amber-50 border border-amber-100'">
                    <p class="text-xs" :class="allSuccess ? 'text-green-700' : 'text-amber-700'">
                        <i :class="allSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle'" class="fas mr-1"></i>
                        {{ progress.sent }} dari {{ progress.total }} pesan berhasil terkirim
                    </p>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-4 flex items-center gap-2" style="color:var(--color-text-primary)">
                    <i class="fas fa-chart-pie" style="color:var(--color-accent)"></i>Ringkasan
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-xs" style="color:var(--color-text-secondary)">Total Kontak</span>
                        <span class="text-sm font-bold" style="color:var(--color-text-primary)">{{ contacts.length }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs" style="color:var(--color-text-secondary)">Nomor Valid</span>
                        <span class="text-sm font-bold text-green-600">{{ contacts.filter(c => c.valid).length }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs" style="color:var(--color-text-secondary)">Nomor Tidak Valid</span>
                        <span class="text-sm font-bold text-red-500">{{ contacts.filter(c => !c.valid).length }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-xs" style="color:var(--color-text-secondary)">Panjang Pesan</span>
                        <span class="text-sm font-bold" style="color:var(--color-text-primary)">{{ form.message.length }} karakter</span>
                    </div>
                    <div v-if="results.length" class="flex justify-between items-center">
                        <span class="text-xs" style="color:var(--color-text-secondary)">Berhasil</span>
                        <span class="text-sm font-bold text-green-600">{{ progress.sent }}</span>
                    </div>
                    <div v-if="results.length" class="flex justify-between items-center">
                        <span class="text-xs" style="color:var(--color-text-secondary)">Gagal</span>
                        <span class="text-sm font-bold text-red-600">{{ progress.total - progress.sent }}</span>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-4 flex items-center gap-2" style="color:var(--color-text-primary)">
                    <i class="fas fa-history" style="color:var(--color-secondary)"></i>Riwayat Broadcast
                </h3>
                <div v-if="history.length === 0" class="text-xs text-center py-4" style="color:var(--color-text-secondary)">
                    Belum ada riwayat
                </div>
                <div v-else class="space-y-3">
                    <div v-for="h in history" :key="h.id" class="rounded-lg border border-gray-100 overflow-hidden" style="background:var(--color-bg)">
                        <div class="p-3 cursor-pointer hover:bg-gray-50 transition-colors" @click="h._open = !h._open">
                            <div class="flex justify-between items-start mb-1">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-chevron-right text-[10px] transition-transform" :class="{'rotate-90': h._open}" style="color:var(--color-text-secondary)"></i>
                                    <span class="text-xs font-semibold" style="color:var(--color-text-primary)">{{ h.total_sent }}/{{ h.total_numbers }} terkirim</span>
                                </div>
                                <span class="text-xs" style="color:var(--color-text-secondary)">{{ formatDate(h.created_at) }}</span>
                            </div>
                            <p class="text-xs truncate ml-5" style="color:var(--color-text-secondary)">{{ h.message }}</p>
                        </div>
                        <div v-if="h._open && h._results && h._results.length" class="border-t" style="border-color:var(--color-border)">
                            <div class="p-3">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold" style="color:var(--color-text-primary)">Detail Pengiriman</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="h.total_sent === h.total_numbers ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'">
                                        {{ h.total_sent }}/{{ h.total_numbers }} berhasil
                                    </span>
                                </div>
                                <div class="overflow-x-auto max-h-60 overflow-y-auto">
                                    <table class="w-full text-xs">
                                        <thead class="sticky top-0 bg-white">
                                            <tr class="border-b" style="border-color:var(--color-border)">
                                                <th class="text-left py-1.5 px-2 font-semibold" style="color:var(--color-text-secondary)">No</th>
                                                <th class="text-left py-1.5 px-2 font-semibold" style="color:var(--color-text-secondary)">Nama</th>
                                                <th class="text-left py-1.5 px-2 font-semibold" style="color:var(--color-text-secondary)">Nomor</th>
                                                <th class="text-left py-1.5 px-2 font-semibold" style="color:var(--color-text-secondary)">Status</th>
                                                <th class="text-center py-1.5 px-2 font-semibold" style="color:var(--color-text-secondary)">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(r, ri) in h._results" :key="ri" class="border-b" style="border-color:var(--color-border)">
                                                <td class="py-1.5 px-2" style="color:var(--color-text-primary)">{{ ri + 1 }}</td>
                                                <td class="py-1.5 px-2" style="color:var(--color-text-primary)">{{ r.nama || '-' }}</td>
                                                <td class="py-1.5 px-2 font-mono" style="color:var(--color-text-primary)">{{ r.number }}</td>
                                                <td class="py-1.5 px-2">
                                                    <span v-if="r.success" class="text-green-600 font-semibold"><i class="fas fa-check-circle mr-1"></i>Berhasil</span>
                                                    <span v-else class="text-red-500 font-semibold"><i class="fas fa-times-circle mr-1"></i>Gagal</span>
                                                </td>
                                                <td class="py-1.5 px-2 text-center">
                                                    <button @click.stop="openDetail(r)" class="btn-detail" title="Lihat Detail"><i class="fas fa-eye"></i></button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div v-else-if="h._open" class="border-t p-3 text-xs text-center" style="border-color:var(--color-border); color:var(--color-text-secondary)">
                            Detail tidak tersedia untuk riwayat ini
                        </div>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <h3 class="text-sm font-semibold mb-3 flex items-center gap-2" style="color:var(--color-text-primary)">
                    <i class="fas fa-info-circle" style="color:var(--color-primary)"></i>Info
                </h3>
                <ul class="text-xs space-y-2" style="color:var(--color-text-secondary)">
                    <li><i class="fas fa-circle text-[6px] mr-1" style="color:var(--color-primary)"></i>Upload Excel dengan kolom: no, nama, nomor whatsapp</li>
                    <li><i class="fas fa-circle text-[6px] mr-1" style="color:var(--color-primary)"></i>Nomor diformat otomatis (08xxx → 628xxx)</li>
                    <li><i class="fas fa-circle text-[6px] mr-1" style="color:var(--color-primary)"></i>Gunakan {'{nama}'} untuk personalisasi pesan</li>
                    <li><i class="fas fa-circle text-[6px] mr-1" style="color:var(--color-primary)"></i>Jeda acak 35-70 detik antar pesan</li>
                    <li><i class="fas fa-circle text-[6px] mr-1" style="color:var(--color-primary)"></i>Riwayat broadcast tersimpan otomatis</li>
                </ul>
            </div>
        </div>
    </div>

    <div v-if="detailModal" class="modal-overlay bg-white"  @click.self="detailModal=null">
        <div class="modal-content bg-white" style="background-color: rgba(240, 248, 255, 0.9);">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold" style="color:var(--color-text-primary)">Detail Pengiriman</h3>
                <button @click="detailModal=null" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></button>
            </div>
            <div class="space-y-3">
                <div class="detail-row">
                    <span class="detail-label">Nama</span>
                    <span class="detail-value">{{ detailModal.nama || '-' }}</span>
                </div>
                <div v-if="detailModal.rawNumber" class="detail-row">
                    <span class="detail-label">Nomor Asli</span>
                    <span class="detail-value" style="color:var(--color-text-secondary)">{{ detailModal.rawNumber }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Nomor Diformat</span>
                    <span class="detail-value font-mono">{{ detailModal.number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status</span>
                    <span v-if="detailModal.success" class="text-green-600 text-xs font-semibold"><i class="fas fa-check-circle mr-1"></i>Berhasil</span>
                    <span v-else class="text-red-600 text-xs font-semibold"><i class="fas fa-times-circle mr-1"></i>Gagal <span v-if="detailModal.error" class="font-normal text-red-400">({{ detailModal.error }})</span></span>
                </div>
                <div>
                    <span class="detail-label mb-1 block">Pesan</span>
                    <div class="p-3 rounded-lg text-xs whitespace-pre-wrap" style="background:var(--color-bg); border:1px solid var(--color-border); color:var(--color-text-primary)">{{ detailModal.message || '-' }}</div>
                </div>
            </div>
            <div class="mt-4 text-right">
                <button @click="detailModal=null" class="btn-secondary text-xs">Tutup</button>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import api from '@/bootstrap.js';
import { swalSuccess, swalError } from '@/swal.js';
import * as XLSX from 'xlsx';

const sending = ref(false);
const loadingUsers = ref(false);
const results = ref([]);
const history = ref([]);
const contacts = ref([]);
const fileName = ref('');
const dragOver = ref(false);
const showPreview = ref(false);
const fileInput = ref(null);
const msgInput = ref(null);
const waitingDelay = ref(0);
const detailModal = ref(null);

const form = reactive({ message: '' });
const progress = reactive({ current: 0, total: 0, sent: 0, currentResult: null });

const progressPercent = computed(() => {
    if (!progress.total) return 0;
    return Math.round((progress.current / progress.total) * 100);
});

function normalizePhone(raw) {
    if (!raw) return { original: '', normalized: '', valid: false };
    let s = String(raw).replace(/[^0-9+]/g, '');
    if (s.startsWith('+')) s = s.substring(1);
    if (s.startsWith('62')) { s = '62' + s.substring(2).replace(/^0+/, ''); }
    else if (s.startsWith('0')) { s = '62' + s.substring(1); }
    else if (/^[1-9]/.test(s) && s.length >= 9 && s.length <= 13 && !s.startsWith('62')) { s = '62' + s; }
    s = s.replace(/[^0-9]/g, '');
    const valid = s.length >= 10 && s.length <= 15 && s.startsWith('62');
    return { original: String(raw), normalized: s, valid };
}

function parseExcelData(workbook) {
    const sheet = workbook.Sheets[workbook.SheetNames[0]];
    const json = XLSX.utils.sheet_to_json(sheet, { defval: '' });
    if (!json.length) return [];

    const sample = json[0];
    const keys = Object.keys(sample);
    const colMap = {};

    for (const k of keys) {
        const lk = k.toLowerCase().trim();
        if (lk === 'no' || lk === 'nomor' || lk === 'nr' || lk === 'nr.' || lk === '#' ) colMap.no = k;
        else if (lk === 'nama' || lk === 'name' || lk === 'nama lengkap') colMap.nama = k;
        else if (lk.includes('wa') || lk.includes('whatsapp') || lk.includes('no hp') || lk.includes('nohp') || lk.includes('no. hp') || lk.includes('nomor hp') || lk.includes('nomor whatsapp') || lk.includes('telp') || lk.includes('telepon') || lk.includes('phone') || lk.includes('hp')) colMap.number = k;
    }

    if (!colMap.number) {
        const guess = keys.find(k => {
            const val = String(sample[k]).replace(/[^0-9]/g, '');
            return val.length >= 8;
        });
        if (guess) colMap.number = guess;
    }

    if (!colMap.number) return [];

    return json.map((row, i) => {
        const rawNum = String(row[colMap.number] || '');
        const { original, normalized, valid } = normalizePhone(rawNum);
        return {
            no: colMap.no ? row[colMap.no] : i + 1,
            nama: colMap.nama ? String(row[colMap.nama] || '') : '',
            rawNumber: original,
            number: normalized,
            valid,
            extra: Object.fromEntries(
                Object.entries(row).filter(([k]) => !['no', colMap.number].includes(k)).map(([k, v]) => [k.toLowerCase().trim(), String(v || '')])
            )
        };
    }).filter(c => c.rawNumber.trim() !== '');
}

function handleFile(e) {
    const file = e.target.files?.[0];
    if (!file) return;
    readFile(file);
}

function handleDrop(e) {
    dragOver.value = false;
    const file = e.dataTransfer.files?.[0];
    if (file) readFile(file);
}

function downloadTemplate() {
    const data = [
        { no: 1, nama: 'Budi Santoso', 'nomor whatsapp': '081234567890' },
        { no: 2, nama: 'Siti Aminah', 'nomor whatsapp': '085678901234' },
        { no: 3, nama: 'Ahmad Dahlan', 'nomor whatsapp': '6289876543210' },
    ];
    const ws = XLSX.utils.json_to_sheet(data, { header: ['no', 'nama', 'nomor whatsapp'] });
    ws['!cols'] = [{ wch: 5 }, { wch: 25 }, { wch: 20 }];
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Broadcast');
    XLSX.writeFile(wb, 'template_broadcast_whatsapp.xlsx');
}

function readFile(file) {
    fileName.value = file.name;
    const reader = new FileReader();
    reader.onload = (e) => {
        try {
            const wb = XLSX.read(e.target.result, { type: 'array' });
            contacts.value = parseExcelData(wb);
            showPreview.value = true;
            if (!contacts.value.length) {
                swalError('Tidak ditemukan kolom nomor WhatsApp di file Excel');
            }
        } catch (err) {
            swalError('Gagal membaca file Excel');
            contacts.value = [];
        }
    };
    reader.readAsArrayBuffer(file);
}

function clearFile() {
    fileName.value = '';
    contacts.value = [];
    showPreview.value = false;
    results.value = [];
    if (fileInput.value) fileInput.value.value = '';
}

function insertPlaceholder(ph) {
    const el = msgInput.value;
    if (!el) { form.message += ph; return; }
    const start = el.selectionStart;
    const end = el.selectionEnd;
    form.message = form.message.substring(0, start) + ph + form.message.substring(end);
    nextTick(() => { el.selectionStart = el.selectionEnd = start + ph.length; el.focus(); });
}

function previewMessage(contact) {
    if (!form.message) return '-';
    let msg = form.message;
    msg = msg.replace(/\{nama\}/gi, contact.nama || '');
    if (contact.extra) {
        for (const [k, v] of Object.entries(contact.extra)) {
            msg = msg.replace(new RegExp(`\\{${k}\\}`, 'gi'), v);
        }
    }
    return msg;
}

function openDetail(r) {
    detailModal.value = r;
}

function openPreviewDetail(c) {
    detailModal.value = {
        nama: c.nama,
        number: c.number,
        rawNumber: c.rawNumber,
        valid: c.valid,
        success: c.valid,
        message: previewMessage(c),
        error: c.valid ? null : 'Nomor tidak valid',
    };
}

const allSuccess = computed(() => results.value.length > 0 && results.value.every(r => r.success));

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

async function loadUsers() {
    loadingUsers.value = true;
    try {
        const { data } = await api.get('/wa-broadcast/users');
        if (data.users && data.users.length) {
            const existingNums = new Set(contacts.value.map(c => c.number));
            let added = 0;
            for (const num of data.users) {
                const { original, normalized, valid } = normalizePhone(num);
                if (!existingNums.has(normalized)) {
                    contacts.value.push({ no: contacts.value.length + 1, nama: '', rawNumber: original, number: normalized, valid, extra: {} });
                    existingNums.add(normalized);
                    added++;
                }
            }
            swalSuccess(`${added} nomor ditambahkan`);
            showPreview.value = true;
        } else {
            swalError('Tidak ditemukan nomor user chatbot');
        }
    } catch (e) {
        swalError('Gagal mengambil data user');
    }
    loadingUsers.value = false;
}

async function sendBroadcast() {
    const validContacts = contacts.value.filter(c => c.valid);
    if (!validContacts.length) {
        swalError('Tidak ada nomor valid untuk dikirim');
        return;
    }
    if (!form.message.trim()) {
        swalError('Pesan tidak boleh kosong');
        return;
    }

    sending.value = true;
    results.value = [];
    progress.current = 0;
    progress.total = validContacts.length;
    progress.sent = 0;
    progress.currentResult = null;
    waitingDelay.value = 0;

    const token = localStorage.getItem('token');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');

    try {
        const response = await fetch('/api/wa-broadcast/send-stream', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'text/event-stream',
                'Authorization': token ? `Bearer ${token}` : '',
                'X-CSRF-TOKEN': csrfMeta?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                contacts: validContacts.map(c => ({ number: c.number, nama: c.nama, extra: c.extra || {} })),
                message: form.message
            })
        });

        if (!response.ok) {
            let errMsg = `HTTP ${response.status}`;
            try {
                const errBody = await response.text();
                console.error('Broadcast error response:', errBody);
                const errJson = JSON.parse(errBody);
                if (errJson.message) errMsg = errJson.message;
            } catch(e) {}
            swalError('Gagal memulai broadcast: ' + errMsg);
            sending.value = false;
            return;
        }

        const reader = response.body.getReader();
        const decoder = new TextDecoder();
        let buffer = '';

        while (true) {
            const { done, value } = await reader.read();
            if (done) break;
            buffer += decoder.decode(value, { stream: true });
            const lines = buffer.split('\n');
            buffer = lines.pop();

            for (const line of lines) {
                const trimmed = line.trim();
                if (!trimmed.startsWith('data:')) continue;
                const jsonStr = trimmed.substring(5).trim();
                if (!jsonStr) continue;
                try {
                    const data = JSON.parse(jsonStr);
                    if (data.type === 'start') {
                        progress.total = data.total;
                    } else if (data.type === 'progress') {
                        progress.current = data.current;
                        progress.sent = data.sent;
                        progress.currentResult = data.result;
                        results.value.push(data.result);
                        waitingDelay.value = 0;
                    } else if (data.type === 'waiting') {
                        waitingDelay.value = data.delay;
                        const interval = setInterval(() => {
                            if (waitingDelay.value > 1) {
                                waitingDelay.value--;
                            } else {
                                waitingDelay.value = 0;
                                clearInterval(interval);
                            }
                        }, 1000);
                    } else if (data.type === 'done') {
                        progress.current = data.total;
                        progress.sent = data.sent;
                        results.value = data.results;
                        waitingDelay.value = 0;
                        if (data.sent === data.total) {
                            swalSuccess(`Broadcast berhasil dikirim ke ${data.sent} nomor`);
                        } else {
                            swalError(`${data.total - data.sent} pesan gagal terkirim`);
                        }
                        loadHistory();
                    }
                } catch (e) {
                    console.error('SSE parse error:', e, 'raw:', jsonStr);
                }
            }
        }
    } catch (e) {
        console.error('Broadcast fetch error:', e);
        swalError('Gagal mengirim broadcast: ' + (e.message || 'Koneksi terputus'));
    }
    sending.value = false;
}

async function loadHistory() {
    try {
        const { data } = await api.get('/wa-broadcast/history');
        history.value = (data || []).map(h => ({
            ...h,
            _open: false,
            _results: (() => {
                try {
                    if (typeof h.results === 'string') return JSON.parse(h.results);
                    return h.results || [];
                } catch(e) { return []; }
            })()
        }));
    } catch (e) {}
}

onMounted(() => { loadHistory(); });
</script>

<style scoped>
.input-label {
    @apply block text-xs font-semibold uppercase tracking-wider mb-1.5;
    color: var(--color-text-secondary);
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
.btn-primary {
    @apply px-4 py-2 rounded-lg text-sm font-semibold text-white transition-colors;
    background: var(--color-primary);
}
.btn-primary:hover:not(:disabled) { opacity: 0.9; }
.btn-primary:disabled { @apply opacity-50 cursor-not-allowed; }
.btn-secondary {
    @apply px-4 py-2 rounded-lg text-sm font-semibold transition-colors border;
    border-color: var(--color-border);
    color: var(--color-text-primary);
    background: var(--color-bg);
}
.btn-secondary:hover:not(:disabled) { background: var(--color-border); }
.btn-secondary:disabled { @apply opacity-50 cursor-not-allowed; }
.btn-detail {
    @apply w-7 h-7 rounded-md text-xs transition-colors;
    color: var(--color-primary);
    background: rgba(var(--color-primary-rgb), 0.08);
}
.btn-detail:hover {
    background: rgba(var(--color-primary-rgb), 0.18);
}
.upload-zone {
    @apply border-2 border-dashed rounded-xl p-6 text-center cursor-pointer transition-colors;
    border-color: var(--color-border);
}
.upload-zone:hover, .upload-zone.drag-over {
    border-color: var(--color-primary);
    background: rgba(var(--color-primary-rgb), 0.03);
}
.tag-btn {
    background: var(--color-border);
    color: var(--color-text-primary);
    font-family: monospace;
}
.tag-btn:hover {
    background: var(--color-primary);
    color: #fff;
}
.btn-template {
    @apply px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors border;
    border-color: #16a34a;
    color: #16a34a;
    background: #f0fdf4;
}
.btn-template:hover {
    background: #16a34a;
    color: #fff;
}
.progress-track {
    @apply w-full h-2 rounded-full overflow-hidden;
    background: var(--color-border);
}
.progress-bar {
    @apply h-full rounded-full transition-all duration-500;
    background: linear-gradient(90deg, var(--color-primary), #22c55e);
}
.modal-overlay {
    @apply fixed inset-0 z-50 flex items-center justify-center;
    background: rgba(0,0,0,0.4);
    backdrop-filter: blur(2px);
}
.modal-content {
    @apply rounded-xl p-6 w-full max-w-md shadow-xl;
    background: var(--color-bg);
    border: 1px solid var(--color-border);
}
.detail-row {
    @apply flex items-center justify-between py-2 border-b;
    border-color: var(--color-border);
}
.detail-label {
    @apply text-xs font-semibold uppercase tracking-wider;
    color: var(--color-text-secondary);
}
.detail-value {
    @apply text-sm;
    color: var(--color-text-primary);
}
</style>
