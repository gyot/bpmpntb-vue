<template>
    <div>
        <h2 class="text-2xl font-bold text-gray-800 mb-6">{{ isEdit ? 'Edit' : 'Tambah' }} {{ jenis }}</h2>
        <div v-if="success" class="bg-green-100 text-green-700 px-4 py-3 rounded mb-4 flex items-center">
            <i class="fas fa-check-circle mr-2"></i>{{ success }}
        </div>
        <div v-if="errMsg" class="bg-red-100 text-red-700 px-4 py-3 rounded mb-4 flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ errMsg }}
        </div>

        <form @submit.prevent="save" class="bg-white rounded-xl shadow p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-bold mb-1">Judul *</label>
                    <input v-model="form.title" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Kategori</label>
                    <select v-model="form.id_kategori" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option :value="null">- Pilih -</option>
                        <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.title }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Penulis</label>
                    <input v-model="form.writer" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Tanggal</label>
                    <input v-model="form.tanggal" type="date" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Tags</label>
                    <input v-model="form.tags" type="text" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Pisahkan dengan koma">
                </div>
                <div>
                    <label class="block text-sm font-bold mb-1">Status</label>
                    <select v-model="form.status" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option :value="1">Publish</option>
                        <option :value="2">Draft</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold mb-2">Thumbnail</label>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-shrink-0">
                        <div v-if="thumbPreview" class="relative group">
                            <img :src="thumbPreview" class="w-48 h-36 object-cover rounded-lg border-2 border-gray-200 shadow-sm">
                            <button type="button" @click="clearThumb"
                                class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 shadow opacity-0 group-hover:opacity-100 transition">
                                <i class="fas fa-times"></i>
                            </button>
                            <div v-if="compressing" class="absolute inset-0 bg-black/50 rounded-lg flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin text-white text-xl"></i>
                            </div>
                            <div class="text-xs text-gray-500 mt-1 text-center" v-if="thumbSize">{{ thumbSize }}</div>
                        </div>
                        <div v-else class="w-48 h-36 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                            <div class="text-center text-gray-400">
                                <i class="fas fa-image text-3xl mb-2"></i>
                                <p class="text-xs">Preview</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-1">
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-400 hover:bg-blue-50/50 transition cursor-pointer"
                            @click="$refs.thumbInput.click()"
                            @dragover.prevent="dragOver = true"
                            @dragleave.prevent="dragOver = false"
                            @drop.prevent="handleDrop($event, 'thumb')"
                            @paste="handlePaste($event, 'thumb')"
                            :class="{'border-blue-400 bg-blue-50': dragOver}" tabindex="0">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                            <p class="text-sm text-gray-600 font-medium">Klik, drag & drop, atau <span class="text-blue-600">paste</span> gambar</p>
                            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP - Otomatis di-compress ke max 1280px</p>
                        </div>
                        <input ref="thumbInput" type="file" accept="image/*" class="hidden" @change="e => handleFileSelect(e, 'thumb')">
                        <div v-if="compressInfo" class="text-xs text-green-600 mt-2 flex items-center">
                            <i class="fas fa-compress-alt mr-1"></i>{{ compressInfo }}
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold mb-2">File Lampiran</label>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 hover:bg-blue-50/50 transition cursor-pointer"
                    @click="$refs.fileInput.click()"
                    @dragover.prevent
                    @drop.prevent="handleDrop($event, 'file')"
                    @paste="handlePaste($event, 'file')" tabindex="0">
                    <div v-if="filePreview" class="flex items-center justify-center gap-3">
                        <i class="fas fa-file text-2xl text-blue-500"></i>
                        <div class="text-left">
                            <p class="text-sm font-medium text-gray-700">{{ filePreview.name }}</p>
                            <p class="text-xs text-gray-400">{{ filePreview.size }}</p>
                        </div>
                        <button type="button" @click.stop="clearFile" class="text-red-500 hover:text-red-700 ml-2"><i class="fas fa-times"></i></button>
                    </div>
                    <div v-else>
                        <i class="fas fa-paperclip text-xl text-gray-400 mb-1"></i>
                        <p class="text-sm text-gray-500">Klik atau paste file (PDF, DOC, ZIP, dll)</p>
                    </div>
                </div>
                <input ref="fileInput" type="file" class="hidden" @change="e => handleFileSelect(e, 'file')">
            </div>

            <div>
                <label class="block text-sm font-bold mb-2">Konten</label>
                <div ref="editorWrap" style="position:relative">
                    <div ref="editorRef"></div>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Klik gambar di editor untuk resize dan atur text wrapping. Paste gambar langsung untuk upload.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t">
                <button type="submit" :disabled="saving" class="inline-flex items-center bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition shadow-sm font-medium">
                    <i :class="saving?'fa-spinner fa-spin':'fa-save'" class="fas mr-2"></i>{{ saving ? 'Menyimpan...' : 'Simpan' }}
                </button>
                <router-link :to="`/admin/konten/${jenis}`" class="inline-flex items-center bg-gray-500 text-white px-6 py-2.5 rounded-lg hover:bg-gray-600 transition font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </router-link>
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, onBeforeUnmount, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/bootstrap.js';
import { swalWarning } from '@/swal.js';
import { loadQuill } from '@/composables/useQuill.js';

const route = useRoute();
const router = useRouter();
const jenis = ref(route.params.jenis);
const isEdit = computed(() => !!route.params.id);
const kategoris = ref([]);
const success = ref('');
const errMsg = ref('');
const saving = ref(false);
const existingImage = ref('');
const editorRef = ref(null);
const editorWrap = ref(null);
const thumbInput = ref(null);
const fileInput = ref(null);
const thumbPreview = ref(null);
const filePreview = ref(null);
const compressing = ref(false);
const compressInfo = ref('');
const thumbSize = ref('');
const dragOver = ref(false);
let quill = null;
let activeImg = null;
let overlayEl = null;

const form = reactive({ title:'', id_kategori:null, writer:'', tanggal:'', tags:'', status:1, content:'', thumbnail:null, file:null });

function formatFileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
    return (bytes/(1024*1024)).toFixed(1) + ' MB';
}

async function compressImage(file, maxWidth = 1280, quality = 0.8) {
    compressing.value = true;
    const originalSize = file.size;
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let w = img.width, h = img.height;
                if (w > maxWidth) { h = Math.round((h * maxWidth) / w); w = maxWidth; }
                canvas.width = w; canvas.height = h;
                canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                const ext = file.name.split('.').pop().toLowerCase();
                const mime = ext === 'png' ? 'image/png' : ext === 'webp' ? 'image/webp' : 'image/jpeg';
                const qual = mime === 'image/png' ? undefined : quality;
                canvas.toBlob((blob) => {
                    const compressedFile = new File([blob], file.name, { type: mime });
                    const reduction = originalSize > 0 ? ((1 - blob.size / originalSize) * 100).toFixed(0) : 0;
                    compressInfo.value = `${formatFileSize(originalSize)} → ${formatFileSize(blob.size)} (${reduction}% lebih kecil)`;
                    thumbSize.value = formatFileSize(blob.size);
                    compressing.value = false;
                    resolve(compressedFile);
                }, mime, qual);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function getClipboardImage(event) {
    const items = event.clipboardData?.items;
    if (!items) return null;
    for (const item of items) { if (item.type.startsWith('image/')) return item.getAsFile(); }
    return null;
}

async function handlePaste(event, type) {
    if (type === 'thumb') {
        const img = getClipboardImage(event);
        if (img) {
            event.preventDefault();
            const compressed = await compressImage(img);
            form.thumbnail = compressed;
            thumbPreview.value = URL.createObjectURL(compressed);
        }
    }
}

function handleDrop(event, type) {
    dragOver.value = false;
    const files = event.dataTransfer?.files;
    if (!files?.length) return;
    if (type === 'thumb') processThumb(files[0]); else processFile(files[0]);
}

function handleFileSelect(event, type) {
    const files = event.target.files;
    if (!files?.length) return;
    if (type === 'thumb') processThumb(files[0]); else processFile(files[0]);
}

async function processThumb(file) {
    if (!file.type.startsWith('image/')) { swalWarning('File harus berupa gambar'); return; }
    const compressed = await compressImage(file);
    form.thumbnail = compressed;
    thumbPreview.value = URL.createObjectURL(compressed);
}

function processFile(file) {
    form.file = file;
    filePreview.value = { name: file.name, size: formatFileSize(file.size) };
}

function clearThumb() {
    form.thumbnail = null;
    if (thumbPreview.value) URL.revokeObjectURL(thumbPreview.value);
    thumbPreview.value = null;
    compressInfo.value = ''; thumbSize.value = '';
    if (thumbInput.value) thumbInput.value.value = '';
}

function clearFile() {
    form.file = null; filePreview.value = null;
    if (fileInput.value) fileInput.value.value = '';
}

async function loadKategori() {
    try { const { data } = await api.get(`/kategori/${jenis.value}`); kategoris.value = data; } catch(e) {}
}

async function loadPost() {
    if (!isEdit.value) return;
    try {
        const { data } = await api.get(`/posts/${jenis.value}/${route.params.id}`);
        const d = data.data;
        form.title = d.title; form.id_kategori = d.id_kategori; form.writer = d.writer;
        form.tanggal = d.tanggal?.substring(0,10) || ''; form.tags = d.tags; form.status = d.status;
        form.content = d.content || '';
        existingImage.value = d.images;
        if (d.images) thumbPreview.value = `/upload/${jenis.value}/thm-${d.images}`;
        await nextTick();
        if (quill) quill.root.innerHTML = form.content;
    } catch(e) { console.error(e); }
}

async function uploadQuillImage(file) {
    try {
        const base64 = await new Promise((resolve, reject) => { const r = new FileReader(); r.onload = () => resolve(r.result); r.onerror = reject; r.readAsDataURL(file); });
        const { data } = await api.post('/quil-upload-image', { images: [base64] });
        if (data.success && data.urls?.length) return data.urls[0];
    } catch(e) { console.error('Upload failed', e); }
    return null;
}

function showImageOverlay(imgEl) {
    hideOverlay();
    activeImg = imgEl;

    const container = editorWrap.value;
    const containerRect = container.getBoundingClientRect();
    const imgRect = imgEl.getBoundingClientRect();

    const overlay = document.createElement('div');
    overlay.style.cssText = `position:absolute;z-index:100;pointer-events:auto;`;
    overlay.className = 'quill-img-overlay';

    const top = imgRect.top - containerRect.top + container.scrollTop;
    const left = imgRect.left - containerRect.left + container.scrollLeft;

    overlay.style.top = top + 'px';
    overlay.style.left = left + 'px';
    overlay.style.width = imgRect.width + 'px';
    overlay.style.height = imgRect.height + 'px';

    const border = document.createElement('div');
    border.style.cssText = 'position:absolute;inset:-2px;border:2px solid #3b82f6;border-radius:4px;pointer-events:none;';
    overlay.appendChild(border);

    const handle = document.createElement('div');
    handle.style.cssText = 'position:absolute;bottom:-7px;right:-7px;width:14px;height:14px;background:#3b82f6;border:2px solid white;border-radius:50%;cursor:nwse-resize;z-index:10;box-shadow:0 1px 3px rgba(0,0,0,0.3);';
    overlay.appendChild(handle);

    let startX, startW, startH;
    handle.addEventListener('mousedown', (e) => {
        e.preventDefault(); e.stopPropagation();
        startX = e.clientX;
        startW = imgEl.offsetWidth; startH = imgEl.offsetHeight;
        const ratio = startW / startH;
        const onMove = (ev) => {
            const dx = ev.clientX - startX;
            const newW = Math.max(60, startW + dx);
            imgEl.style.width = newW + 'px';
            imgEl.style.height = Math.round(newW / ratio) + 'px';
            overlay.style.width = newW + 'px';
            overlay.style.height = Math.round(newW / ratio) + 'px';
        };
        const onUp = () => {
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
        };
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });

    const popup = document.createElement('div');
    popup.style.cssText = 'position:absolute;top:-42px;left:50%;transform:translateX(-50%);background:#1f2937;border-radius:8px;padding:4px 6px;display:flex;gap:2px;z-index:50;box-shadow:0 4px 12px rgba(0,0,0,0.3);';

    const actions = [
        { label: 'Kiri', fn: () => setImgStyle(imgEl, 'left') },
        { label: 'Kanan', fn: () => setImgStyle(imgEl, 'right') },
        { label: 'Full', fn: () => setImgStyle(imgEl, '') },
        { label: '50%', fn: () => { imgEl.style.width='50%'; imgEl.style.height='auto'; refreshOverlay(); } },
        { label: '75%', fn: () => { imgEl.style.width='75%'; imgEl.style.height='auto'; refreshOverlay(); } },
        { label: '100%', fn: () => { imgEl.style.width='100%'; imgEl.style.height='auto'; refreshOverlay(); } },
        { label: 'Hapus', fn: () => { imgEl.remove(); hideOverlay(); if(quill) quill.update('user'); }, color: '#f87171' },
    ];

    actions.forEach(a => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.textContent = a.label;
        btn.style.cssText = `color:${a.color||'white'};background:none;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;font-size:11px;white-space:nowrap;font-family:Inter,sans-serif;`;
        btn.addEventListener('mouseenter', () => btn.style.background = 'rgba(255,255,255,0.15)');
        btn.addEventListener('mouseleave', () => btn.style.background = '');
        btn.addEventListener('mousedown', (e) => { e.preventDefault(); e.stopPropagation(); });
        btn.addEventListener('click', (e) => { e.preventDefault(); e.stopPropagation(); a.fn(); });
        popup.appendChild(btn);
    });

    overlay.appendChild(popup);
    container.appendChild(overlay);
    overlayEl = overlay;
}

function setImgStyle(imgEl, floatDir) {
    if (floatDir === 'left') {
        imgEl.style.cssText = 'max-width:100%;height:auto;border-radius:4px;float:left;width:45%;margin:0 16px 8px 0;display:block;';
    } else if (floatDir === 'right') {
        imgEl.style.cssText = 'max-width:100%;height:auto;border-radius:4px;float:right;width:45%;margin:0 0 8px 16px;display:block;';
    } else {
        imgEl.style.cssText = 'max-width:100%;height:auto;border-radius:4px;float:none;width:100%;margin:8px 0;display:block;';
    }
    hideOverlay();
}

function refreshOverlay() {
    if (activeImg) showImageOverlay(activeImg);
}

function hideOverlay() {
    if (overlayEl) { overlayEl.remove(); overlayEl = null; }
    activeImg = null;
}

function initQuill() {
    if (!window.Quill || !editorRef.value) return;

    quill = new Quill(editorRef.value, {
        theme: 'snow',
        placeholder: 'Tulis konten di sini... (paste gambar langsung ke editor)',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'font': [] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                ['blockquote', 'code-block'],
                ['link', 'image', 'video'],
                ['clean'],
            ],
        }
    });

    quill.getModule('toolbar').addHandler('image', () => {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        input.setAttribute('multiple', 'multiple');
        input.click();
        input.onchange = async () => {
            for (const file of input.files) {
                const range = quill.getSelection(true);
                const idx = range ? range.index : quill.getLength() - 1;
                quill.insertText(idx, '⏳ Upload...', { color: '#999' });
                const url = await uploadQuillImage(file);
                quill.deleteText(idx, 13);
                if (url) {
                    quill.insertEmbed(idx, 'image', url);
                    quill.setSelection(idx + 1);
                }
            }
        };
    });

    quill.root.addEventListener('paste', async (e) => {
        const items = e.clipboardData?.items;
        if (!items) return;
        for (const item of items) {
            if (item.type.startsWith('image/')) {
                e.preventDefault();
                const file = item.getAsFile();
                const range = quill.getSelection(true);
                const idx = range ? range.index : quill.getLength() - 1;
                quill.insertText(idx, '⏳ Upload...', { color: '#999' });
                const url = await uploadQuillImage(file);
                quill.deleteText(idx, 13);
                if (url) {
                    quill.insertEmbed(idx, 'image', url);
                    quill.setSelection(idx + 1);
                }
                return;
            }
        }
    });

    quill.root.addEventListener('click', (e) => {
        if (e.target.tagName === 'IMG') {
            e.preventDefault();
            showImageOverlay(e.target);
        }
    });

    document.addEventListener('mousedown', (e) => {
        if (overlayEl && !e.target.closest('.quill-img-overlay') && !e.target.closest('.ql-toolbar')) {
            hideOverlay();
        }
    });

    quill.on('text-change', () => { form.content = quill.root.innerHTML; });
}

async function save() {
    hideOverlay();
    if (quill) form.content = quill.root.innerHTML;
    saving.value = true; success.value = ''; errMsg.value = '';
    try {
        const fd = new FormData();
        fd.append('title', form.title);
        fd.append('status', form.status);
        if (form.id_kategori) fd.append('id_kategori', form.id_kategori);
        if (form.writer) fd.append('writer', form.writer);
        if (form.tanggal) fd.append('tanggal', form.tanggal);
        if (form.tags) fd.append('tags', form.tags);
        fd.append('content', form.content || '');
        if (form.thumbnail) fd.append('thumbnail', form.thumbnail);
        if (form.file) fd.append('file', form.file);
        fd.append('_method', isEdit.value ? 'PUT' : 'POST');

        if (isEdit.value) {
            await api.post(`/posts/${jenis.value}/${route.params.id}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
        } else {
            await api.post(`/posts/${jenis.value}`, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
        }
        success.value = 'Data berhasil disimpan!';
        setTimeout(() => router.push(`/admin/konten/${jenis.value}`), 1000);
    } catch (e) { errMsg.value = e.response?.data?.message || 'Gagal menyimpan'; }
    saving.value = false;
}

onMounted(async () => {
    await loadKategori();
    await loadPost();
    await loadQuill();
    setTimeout(initQuill, 200);
});

onBeforeUnmount(() => { hideOverlay(); });
</script>
