<template>
    <div>
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold capitalize" style="color:var(--color-text-primary)">{{ jenis }}</h2>
                <p class="text-sm mt-1" style="color:var(--color-text-secondary)">Total: {{ total }} data</p>
            </div>
            <button @click="openModal()" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>Tambah {{ jenis }}
            </button>
        </div>

        <div class="card mb-6">
            <div class="p-4 flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input v-model="search" @input="debounceLoad" type="text" placeholder="Cari judul atau tag..." class="input-field pl-11">
                </div>
                <select v-model="sortBy" @change="load()" class="input-field w-auto">
                    <option value="tanggal">Urut: Tanggal</option>
                    <option value="title">Urut: Judul</option>
                    <option value="id">Urut: ID</option>
                    <option value="status">Urut: Status</option>
                    <option value="viewer">Urut: Views</option>
                </select>
                <button @click="toggleSortDir" class="btn-ghost border border-gray-200">
                    <i class="fas" :class="sortDir==='desc'?'fa-sort-amount-down':'fa-sort-amount-up'"></i>
                </button>
            </div>
        </div>

        <div v-if="loading" class="text-center py-16">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-500"></i>
            <p class="text-gray-500 mt-3">Memuat data...</p>
        </div>

        <div v-else>
            <div class="space-y-4">
                <div v-for="item in posts" :key="item.id"
                    class="card overflow-hidden hover:shadow-md hover:border-[var(--color-primary)]/30 transition-all duration-200 group">
                    <div class="flex flex-col sm:flex-row">
                        <div class="sm:w-48 md:w-56 flex-shrink-0 bg-gray-100 relative overflow-hidden" style="min-height:180px">
                            <img v-if="item.images" :src="`/upload/${jenis}/thm-${item.images}`"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                @error="$event.target.src=`/upload/${jenis}/${item.images}`">
                            <div v-else class="w-full h-full flex items-center justify-center text-gray-400">
                                <i class="fas fa-image text-4xl"></i>
                            </div>
                            <div class="absolute top-2 left-2 bg-black/60 text-white text-xs px-2 py-1 rounded">#{{ item.id }}</div>
                        </div>
                        <div class="flex-1 p-5 flex flex-col justify-between min-w-0">
                            <div>
                                <h3 class="text-lg font-bold mb-2 line-clamp-2 group-hover:text-[var(--color-primary)] transition-colors" style="color:var(--color-text-primary)">{{ item.title }}</h3>
                                <p class="text-sm leading-relaxed line-clamp-2 mb-4" style="color:var(--color-text-secondary)">{{ stripHtml(item.content) }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 mb-4">
                                <span class="inline-flex items-center text-xs text-gray-500"><i class="fas fa-user mr-1.5 text-gray-400"></i>{{ item.writer || '-' }}</span>
                                <span class="inline-flex items-center text-xs text-gray-500"><i class="fas fa-calendar mr-1.5 text-gray-400"></i>{{ formatDate(item.tanggal) }}</span>
                                <span :class="item.status===1?'bg-green-100 text-green-700':'bg-yellow-100 text-yellow-700'"
                                    class="inline-flex items-center text-xs font-medium px-2.5 py-0.5 rounded-full">
                                    <i :class="item.status===1?'fa-check-circle':'fa-clock'" class="fas mr-1.5 text-[10px]"></i>{{ item.status===1?'Publish':'Draft' }}
                                </span>
                                <span class="inline-flex items-center text-xs bg-blue-50 text-blue-700 px-2.5 py-0.5 rounded-full">
                                    <i class="fas fa-folder mr-1.5 text-[10px]"></i>{{ item.kategori?.title || 'Tanpa Kategori' }}
                                </span>
                                <span v-if="item.viewer" class="text-xs text-gray-500"><i class="fas fa-eye mr-1.5"></i>{{ item.viewer }}</span>
                            </div>
                            <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                                <a :href="`/post/${jenis}/${item.id}/${item.slug||''}`" target="_blank"
                                    class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition">
                                    <i class="fas fa-eye mr-2"></i>View
                                </a>
                                <button @click="openModal(item)"
                                    class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-white rounded-xl hover:opacity-90 transition shadow-sm" style="background:var(--color-primary)">
                                    <i class="fas fa-edit mr-2"></i>Edit
                                </button>
                                <button @click="destroy(item.id)"
                                    class="inline-flex items-center px-3.5 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-sm">
                                    <i class="fas fa-trash mr-2"></i>Hapus
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="posts.length===0" class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
                <i class="fas fa-inbox text-5xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 text-lg">Belum ada data {{ jenis }}</p>
                <button @click="openModal()" class="inline-flex items-center mt-4 text-blue-600 hover:text-blue-800 font-medium">
                    <i class="fas fa-plus mr-2"></i>Tambah {{ jenis }} baru
                </button>
            </div>

            <div v-if="lastPage > 1" class="flex justify-center items-center gap-1.5 mt-8">
                <button @click="load(1)" :disabled="currentPage<=1" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition"><i class="fas fa-angle-double-left"></i></button>
                <button @click="load(currentPage-1)" :disabled="currentPage<=1" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition"><i class="fas fa-angle-left"></i></button>
                <template v-for="p in paginationRange" :key="p">
                    <span v-if="p==='...'" class="w-8 h-8 flex items-center justify-center text-xs text-gray-400">···</span>
                    <button v-else @click="load(p)" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-semibold transition-all"
                        :class="p===currentPage?'text-white shadow-md':'text-gray-500 hover:bg-gray-100'"
                        :style="p===currentPage?{background:'var(--color-primary)'}:{}">{{ p }}</button>
                </template>
                <button @click="load(currentPage+1)" :disabled="currentPage>=lastPage" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition"><i class="fas fa-angle-right"></i></button>
                <button @click="load(lastPage)" :disabled="currentPage>=lastPage" class="w-8 h-8 rounded-lg flex items-center justify-center text-xs text-gray-400 hover:bg-gray-100 disabled:opacity-30 transition"><i class="fas fa-angle-double-right"></i></button>
                <span class="ml-2 text-xs text-gray-400">{{ currentPage }}/{{ lastPage }}</span>
            </div>
        </div>

        <teleport to="body">
            <div v-if="modalOpen" class="fixed inset-0 z-[1060] overflow-hidden">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity" @click="closeModal"></div>
                <div class="absolute inset-4 md:inset-y-6 md:inset-x-12 lg:inset-y-8 lg:inset-x-24 bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden">
                    <div class="flex items-center justify-between px-6 py-4 border-b bg-gray-50 rounded-t-2xl flex-shrink-0">
                        <h3 class="text-xl font-bold text-gray-800">
                            <i :class="editId?'fa-edit':'fa-plus'" class="fas mr-2 text-blue-600"></i>{{ editId ? 'Edit' : 'Tambah' }} {{ jenis }}
                        </h3>
                        <button @click="closeModal" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded-lg transition">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-6">
                        <div v-if="modalSuccess" class="bg-green-100 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>{{ modalSuccess }}
                        </div>
                        <div v-if="modalError" class="bg-red-100 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>{{ modalError }}
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                            <div>
                                <label class="block text-sm font-bold mb-1">Judul *</label>
                                <input v-model="mform.title" type="text" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Kategori</label>
                                <select v-model="mform.id_kategori" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option :value="null">- Pilih -</option>
                                    <option v-for="k in kategoris" :key="k.id" :value="k.id">{{ k.title }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Penulis</label>
                                <input v-model="mform.writer" type="text" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Tanggal</label>
                                <input v-model="mform.tanggal" type="date" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Tags</label>
                                <input v-model="mform.tags" type="text" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Pisahkan dengan koma">
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Status</label>
                                <select v-model="mform.status" class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-blue-500">
                                    <option :value="1">Publish</option>
                                    <option :value="2">Draft</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-bold mb-2">Thumbnail</label>
                            <div class="flex flex-col sm:flex-row gap-4">
                                <div class="flex-shrink-0">
                                    <div v-if="thumbPreview" class="relative group">
                                        <img :src="thumbPreview" class="w-44 h-32 object-cover rounded-lg border-2 border-gray-200">
                                        <button type="button" @click="clearThumb"
                                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 opacity-0 group-hover:opacity-100 transition shadow">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <div v-if="mCompressing" class="absolute inset-0 bg-black/50 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-spinner fa-spin text-white"></i>
                                        </div>
                                        <div v-if="mCompressInfo" class="text-xs text-green-600 mt-1 text-center">{{ mCompressInfo }}</div>
                                    </div>
                                    <div v-else class="w-44 h-32 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center bg-gray-50">
                                        <div class="text-center text-gray-400"><i class="fas fa-image text-2xl mb-1"></i><p class="text-xs">Preview</p></div>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-5 text-center hover:border-blue-400 hover:bg-blue-50/50 transition cursor-pointer"
                                        @click="$refs.modalThumbInput.click()"
                                        @dragover.prevent="modalDragOver=true" @dragleave.prevent="modalDragOver=false"
                                        @drop.prevent="handleModalDrop($event,'thumb')" @paste="handleModalPaste($event,'thumb')"
                                        :class="{'border-blue-400 bg-blue-50':modalDragOver}" tabindex="0">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 mb-1"></i>
                                        <p class="text-sm text-gray-600">Klik, drag & drop, atau <span class="text-blue-600">paste</span> gambar</p>
                                        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP - Auto compress max 1280px</p>
                                    </div>
                                    <input ref="modalThumbInput" type="file" accept="image/*" class="hidden" @change="e=>handleModalFileSelect(e,'thumb')">
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-bold mb-2">File Lampiran</label>
                            <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-blue-400 transition cursor-pointer"
                                @click="$refs.modalFileInput.click()"
                                @drop.prevent="handleModalDrop($event,'file')" @paste="handleModalPaste($event,'file')" tabindex="0">
                                <div v-if="filePreview" class="flex items-center justify-center gap-3">
                                    <i class="fas fa-file text-xl text-blue-500"></i>
                                    <div class="text-left">
                                        <p class="text-sm font-medium text-gray-700">{{ filePreview.name }}</p>
                                        <p class="text-xs text-gray-400">{{ filePreview.size }}</p>
                                    </div>
                                    <button type="button" @click.stop="clearFile" class="text-red-500 hover:text-red-700"><i class="fas fa-times"></i></button>
                                </div>
                                <div v-else>
                                    <i class="fas fa-paperclip text-lg text-gray-400 mb-1"></i>
                                    <p class="text-sm text-gray-500">Klik atau paste file</p>
                                </div>
                            </div>
                            <input ref="modalFileInput" type="file" class="hidden" @change="e=>handleModalFileSelect(e,'file')">
                        </div>

                        <div class="mb-5">
                            <label class="block text-sm font-bold mb-2">Konten</label>
                            <div ref="modalEditorWrap" style="position:relative">
                                <div ref="modalEditorRef"></div>
                            </div>
                            <p class="text-xs text-gray-400 mt-1"><i class="fas fa-info-circle mr-1"></i>Klik gambar untuk resize & wrap. Paste gambar untuk upload.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t bg-gray-50 flex-shrink-0">
                        <button @click="closeModal" class="px-5 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition font-medium">Batal</button>
                        <button @click="saveModal" :disabled="modalSaving"
                            class="inline-flex items-center bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 disabled:opacity-50 transition font-medium shadow-sm">
                            <i :class="modalSaving?'fa-spinner fa-spin':'fa-save'" class="fas mr-2"></i>{{ modalSaving?'Menyimpan...':'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>
        </teleport>
    </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import api from '@/bootstrap.js';
import { swalConfirm, swalError, swalSuccess, swalWarning } from '@/swal.js';
import { loadQuill } from '@/composables/useQuill.js';

const route = useRoute();
const jenis = ref(route.params.jenis);
const posts = ref([]);
const search = ref('');
const sortBy = ref('tanggal');
const sortDir = ref('desc');
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const loading = ref(true);
const kategoris = ref([]);
let debounceTimer = null;

const bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function formatDate(tgl) { if(!tgl)return'-';const d=new Date(tgl);return isNaN(d)?tgl:`${d.getDate()} ${bulan[d.getMonth()]} ${d.getFullYear()}`; }
function stripHtml(html) { if(!html)return'';const t=document.createElement('div');t.innerHTML=html;return t.textContent?.substring(0,200)||''; }
function formatFileSize(b){if(!b)return'';if(b<1024)return b+' B';if(b<1024*1024)return(b/1024).toFixed(1)+' KB';return(b/(1024*1024)).toFixed(1)+' MB';}

const paginationRange = computed(() => {
    const pages = [], cur = currentPage.value, last = lastPage.value;
    if (last <= 7) { for (let i=1;i<=last;i++) pages.push(i); }
    else { pages.push(1); if(cur>3)pages.push('...'); for(let i=Math.max(2,cur-1);i<=Math.min(last-1,cur+1);i++)pages.push(i); if(cur<last-2)pages.push('...'); pages.push(last); }
    return pages;
});

function toggleSortDir() { sortDir.value = sortDir.value==='desc'?'asc':'desc'; load(); }

async function load(page=1) {
    loading.value = true;
    try {
        const { data } = await api.get(`/posts/${jenis.value}`, { params:{page,search:search.value,sort_by:sortBy.value,sort_dir:sortDir.value,per_page:12} });
        posts.value = data.data; currentPage.value = data.current_page; lastPage.value = data.last_page; total.value = data.total;
    } catch(e) { console.error(e); posts.value = []; }
    loading.value = false;
}
function debounceLoad() { clearTimeout(debounceTimer); debounceTimer = setTimeout(()=>load(), 300); }
async function destroy(id) { if(!await swalConfirm('Yakin ingin menghapus data ini?'))return; try{await api.delete(`/posts/${jenis.value}/${id}`);swalSuccess('Data berhasil dihapus');load(currentPage.value);}catch(e){swalError('Gagal menghapus');} }

async function loadKategori() { try{const{data}=await api.get(`/kategori/${jenis.value}`);kategoris.value=data;}catch(e){} }

// Modal state
const modalOpen = ref(false);
const editId = ref(null);
const modalSuccess = ref('');
const modalError = ref('');
const modalSaving = ref(false);
const modalEditorRef = ref(null);
const modalEditorWrap = ref(null);
const modalThumbInput = ref(null);
const modalFileInput = ref(null);
const thumbPreview = ref(null);
const filePreview = ref(null);
const mCompressing = ref(false);
const mCompressInfo = ref('');
const modalDragOver = ref(false);
let quill = null;
let activeImg = null;
let overlayEl = null;
let mousedownHandler = null;

const mform = reactive({ title:'', id_kategori:null, writer:'', tanggal:'', tags:'', status:1, content:'', thumbnail:null, file:null });

function resetForm() {
    Object.assign(mform, { title:'', id_kategori:null, writer:'', tanggal:'', tags:'', status:1, content:'', thumbnail:null, file:null });
    thumbPreview.value = null; filePreview.value = null; mCompressInfo.value = '';
    editId.value = null; modalSuccess.value = ''; modalError.value = '';
}

async function openModal(item = null) {
    resetForm();
    if (item) {
        editId.value = item.id;
        mform.title = item.title; mform.id_kategori = item.id_kategori; mform.writer = item.writer;
        mform.tanggal = item.tanggal?.substring(0,10)||''; mform.tags = item.tags; mform.status = item.status;
        mform.content = item.content || '';
        if (item.images) thumbPreview.value = `/upload/${jenis.value}/thm-${item.images}`;
    }
    modalOpen.value = true;
    await nextTick();
    await loadQuill();
    setTimeout(initModalQuill, 150);
}

function closeModal() {
    destroyQuill();
    modalOpen.value = false;
}

function destroyQuill() {
    hideOverlay();
    if (mousedownHandler) { document.removeEventListener('mousedown', mousedownHandler); mousedownHandler = null; }
    quill = null;
}

async function compressImage(file, maxWidth=1280, quality=0.8) {
    mCompressing.value = true;
    const orig = file.size;
    return new Promise(resolve => {
        const reader = new FileReader();
        reader.onload = e => {
            const img = new Image();
            img.onload = () => {
                const c = document.createElement('canvas');
                let w=img.width, h=img.height;
                if(w>maxWidth){h=Math.round(h*maxWidth/w);w=maxWidth;}
                c.width=w;c.height=h;
                c.getContext('2d').drawImage(img,0,0,w,h);
                const ext=file.name.split('.').pop().toLowerCase();
                const mime=ext==='png'?'image/png':ext==='webp'?'image/webp':'image/jpeg';
                c.toBlob(blob => {
                    const f=new File([blob],file.name,{type:mime});
                    const r=orig>0?((1-blob.size/orig)*100).toFixed(0):0;
                    mCompressInfo.value=`${formatFileSize(orig)} → ${formatFileSize(blob.size)} (${r}%)`;
                    mCompressing.value = false;
                    resolve(f);
                }, mime, mime==='image/png'?undefined:quality);
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function getClipboardImg(e){const i=e.clipboardData?.items;if(!i)return null;for(const it of i)if(it.type.startsWith('image/'))return it.getAsFile();return null;}

async function handleModalPaste(e,type){
    if(type==='thumb'){const img=getClipboardImg(e);if(img){e.preventDefault();const c=await compressImage(img);mform.thumbnail=c;thumbPreview.value=URL.createObjectURL(c);}}
}
function handleModalDrop(e,type){modalDragOver.value=false;const f=e.dataTransfer?.files?.[0];if(!f)return;if(type==='thumb')processModalThumb(f);else{mform.file=f;filePreview.value={name:f.name,size:formatFileSize(f.size)};}}
function handleModalFileSelect(e,type){const f=e.target.files?.[0];if(!f)return;if(type==='thumb')processModalThumb(f);else{mform.file=f;filePreview.value={name:f.name,size:formatFileSize(f.size)};}}
async function processModalThumb(f){if(!f.type.startsWith('image/')){swalWarning('File harus berupa gambar');return;}const c=await compressImage(f);mform.thumbnail=c;thumbPreview.value=URL.createObjectURL(c);}
function clearThumb(){mform.thumbnail=null;if(thumbPreview.value)URL.revokeObjectURL(thumbPreview.value);thumbPreview.value=null;mCompressInfo.value='';if(modalThumbInput.value)modalThumbInput.value.value='';}
function clearFile(){mform.file=null;filePreview.value=null;if(modalFileInput.value)modalFileInput.value.value='';}

async function uploadQuillImage(file){
    try{
        const base64=await new Promise((resolve,reject)=>{const r=new FileReader();r.onload=()=>resolve(r.result);r.onerror=reject;r.readAsDataURL(file);});
        const{data}=await api.post('/quil-upload-image',{images:[base64]});
        if(data.success&&data.urls?.length)return data.urls[0];
    }catch(e){console.error(e);}
    return null;
}

function showImageOverlay(imgEl){
    hideOverlay();activeImg=imgEl;
    const container=modalEditorWrap.value;if(!container)return;
    const cRect=container.getBoundingClientRect(),iRect=imgEl.getBoundingClientRect();
    const ov=document.createElement('div');ov.className='quill-img-overlay';
    ov.style.cssText=`position:absolute;z-index:100;top:${iRect.top-cRect.top+container.scrollTop}px;left:${iRect.left-cRect.left+container.scrollLeft}px;width:${iRect.width}px;height:${iRect.height}px;`;
    const border=document.createElement('div');border.style.cssText='position:absolute;inset:-2px;border:2px solid #3b82f6;border-radius:4px;pointer-events:none;';ov.appendChild(border);
    const handle=document.createElement('div');handle.style.cssText='position:absolute;bottom:-7px;right:-7px;width:14px;height:14px;background:#3b82f6;border:2px solid white;border-radius:50%;cursor:nwse-resize;z-index:10;box-shadow:0 1px 3px rgba(0,0,0,0.3);';ov.appendChild(handle);
    let sx,sw,sh;
    handle.addEventListener('mousedown',e=>{e.preventDefault();e.stopPropagation();sx=e.clientX;sw=imgEl.offsetWidth;sh=imgEl.offsetHeight;const r=sw/sh;
        const onM=ev=>{const nw=Math.max(60,sw+ev.clientX-sx);imgEl.style.width=nw+'px';imgEl.style.height=Math.round(nw/r)+'px';ov.style.width=nw+'px';ov.style.height=Math.round(nw/r)+'px';};
        const onU=()=>{document.removeEventListener('mousemove',onM);document.removeEventListener('mouseup',onU);};
        document.addEventListener('mousemove',onM);document.addEventListener('mouseup',onU);
    });
    const popup=document.createElement('div');popup.style.cssText='position:absolute;top:-42px;left:50%;transform:translateX(-50%);background:#1f2937;border-radius:8px;padding:4px 6px;display:flex;gap:2px;z-index:50;box-shadow:0 4px 12px rgba(0,0,0,0.3);';
    [{l:'Kiri',d:'left'},{l:'Kanan',d:'right'},{l:'Full',d:''},{l:'50%',a:()=>{imgEl.style.width='50%';imgEl.style.height='auto';showImageOverlay(imgEl);}},{l:'75%',a:()=>{imgEl.style.width='75%';imgEl.style.height='auto';showImageOverlay(imgEl);}},{l:'100%',a:()=>{imgEl.style.width='100%';imgEl.style.height='auto';showImageOverlay(imgEl);}},{l:'Hapus',c:'#f87171',a:()=>{imgEl.remove();hideOverlay();if(quill)quill.update('user');}}].forEach(x=>{
        const b=document.createElement('button');b.type='button';b.textContent=x.l;b.style.cssText=`color:${x.c||'white'};background:none;border:none;padding:4px 8px;border-radius:4px;cursor:pointer;font-size:11px;white-space:nowrap;font-family:Inter,sans-serif;`;
        b.addEventListener('mouseenter',()=>b.style.background='rgba(255,255,255,0.15)');b.addEventListener('mouseleave',()=>b.style.background='');
        b.addEventListener('mousedown',e=>{e.preventDefault();e.stopPropagation();});
        b.addEventListener('click',e=>{e.preventDefault();e.stopPropagation();if(x.a){x.a();return;}if(x.d==='left')imgEl.style.cssText='max-width:100%;height:auto;border-radius:4px;float:left;width:45%;margin:0 16px 8px 0;display:block;';else if(x.d==='right')imgEl.style.cssText='max-width:100%;height:auto;border-radius:4px;float:right;width:45%;margin:0 0 8px 16px;display:block;';else imgEl.style.cssText='max-width:100%;height:auto;border-radius:4px;float:none;width:100%;margin:8px 0;display:block;';hideOverlay();});
        popup.appendChild(b);
    });
    ov.appendChild(popup);container.appendChild(ov);overlayEl=ov;
}
function hideOverlay(){if(overlayEl){overlayEl.remove();overlayEl=null;}activeImg=null;}

function initModalQuill(){
    if(!window.Quill||!modalEditorRef.value)return;
    quill=new Quill(modalEditorRef.value,{theme:'snow',placeholder:'Tulis konten di sini...',modules:{toolbar:[[{header:[1,2,3,4,5,6,false]}],[{font:[]}],['bold','italic','underline','strike'],[{color:[]},{background:[]}],[{align:[]}],[{list:'ordered'},{list:'bullet'}],['blockquote','code-block'],['link','image','video'],['clean']]}});
    quill.getModule('toolbar').addHandler('image',()=>{
        const input=document.createElement('input');input.setAttribute('type','file');input.setAttribute('accept','image/*');input.setAttribute('multiple','multiple');input.click();
        input.onchange=async()=>{for(const file of input.files){const range=quill.getSelection(true);const idx=range?range.index:quill.getLength()-1;quill.insertText(idx,'⏳ Upload...',{color:'#999'});const url=await uploadQuillImage(file);quill.deleteText(idx,13);if(url){quill.insertEmbed(idx,'image',url);quill.setSelection(idx+1);}}};
    });
    quill.root.addEventListener('paste',async e=>{const items=e.clipboardData?.items;if(!items)return;for(const item of items){if(item.type.startsWith('image/')){e.preventDefault();const file=item.getAsFile();const range=quill.getSelection(true);const idx=range?range.index:quill.getLength()-1;quill.insertText(idx,'⏳ Upload...',{color:'#999'});const url=await uploadQuillImage(file);quill.deleteText(idx,13);if(url){quill.insertEmbed(idx,'image',url);quill.setSelection(idx+1);}return;}}});
    quill.root.addEventListener('click',e=>{if(e.target.tagName==='IMG'){e.preventDefault();showImageOverlay(e.target);}});
    mousedownHandler=e=>{if(overlayEl&&!e.target.closest('.quill-img-overlay')&&!e.target.closest('.ql-toolbar'))hideOverlay();};
    document.addEventListener('mousedown',mousedownHandler);
    quill.on('text-change',()=>{mform.content=quill.root.innerHTML;});
    if(mform.content)quill.root.innerHTML=mform.content;
}

async function saveModal(){
    hideOverlay();if(quill)mform.content=quill.root.innerHTML;
    if(!mform.title.trim()){modalError.value='Judul wajib diisi';return;}
    modalSaving.value=true;modalSuccess.value='';modalError.value='';
    try{
        const fd=new FormData();
        fd.append('title',mform.title);fd.append('status',mform.status);
        if(mform.id_kategori)fd.append('id_kategori',mform.id_kategori);
        if(mform.writer)fd.append('writer',mform.writer);
        if(mform.tanggal)fd.append('tanggal',mform.tanggal);
        if(mform.tags)fd.append('tags',mform.tags);
        fd.append('content',mform.content||'');
        if(mform.thumbnail)fd.append('thumbnail',mform.thumbnail);
        if(mform.file)fd.append('file',mform.file);
        fd.append('_method',editId.value?'PUT':'POST');
        if(editId.value) await api.post(`/posts/${jenis.value}/${editId.value}`,fd,{headers:{'Content-Type':'multipart/form-data'}});
        else await api.post(`/posts/${jenis.value}`,fd,{headers:{'Content-Type':'multipart/form-data'}});
        modalSuccess.value='Data berhasil disimpan!';
        setTimeout(()=>{closeModal();load(editId.value?currentPage.value:1);},800);
    }catch(e){modalError.value=e.response?.data?.message||'Gagal menyimpan';}
    modalSaving.value=false;
}

async function openEditFromRoute(id) {
    try {
        const { data } = await api.get(`/posts/${jenis.value}/${id}`);
        openModal(data.data);
    } catch(e) { console.error(e); }
}

watch(() => route.params.jenis, (v) => { jenis.value = v; search.value = ''; load(); });
watch(() => route.params.id, (v) => { if(v) openEditFromRoute(v); }, { immediate: true });
onMounted(async()=>{await loadKategori();load();});
</script>

<style scoped>
.line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
</style>
