<template>
<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="color:#0f172a;font-size:18px;">📚 Knowledge Base RAG</h4>
            <p class="text-xs" style="color:#64748b;">Kelola dokumen pengetahuan untuk meningkatkan akurasi jawaban chatbot Si INTAN</p>
        </div>
        <div class="flex gap-2">
            <button class="kb-btn kb-btn-green" @click="regenerateAll" :disabled="regenLoading">
                <i class="fas fa-sync-alt" :class="{'fa-spin':regenLoading}"></i> Generate Embeddings
            </button>
            <button class="kb-btn kb-btn-blue" @click="openDocModal()">
                <i class="fas fa-plus"></i> Tambah Dokumen
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="kb-stat-card"><div class="kb-stat-number">{{ stats.documents ?? '-' }}</div><div class="kb-stat-label">Total Dokumen</div></div>
        <div class="kb-stat-card"><div class="kb-stat-number">{{ stats.categories ?? '-' }}</div><div class="kb-stat-label">Kategori</div></div>
        <div class="kb-stat-card"><div class="kb-stat-number">{{ stats.chunks ?? '-' }}</div><div class="kb-stat-label">Total Chunks</div></div>
        <div class="kb-stat-card"><div class="kb-stat-number">{{ stats.withEmbedding ?? '-' }}</div><div class="kb-stat-label">Dengan Embedding</div></div>
    </div>

    <!-- Categories -->
    <div class="kb-card mb-4">
        <div class="flex items-center justify-between mb-3">
            <h6 class="fw-bold" style="color:#0f172a;font-size:14px;"><i class="fas fa-folder mr-2" style="color:#2563eb;"></i>Kategori</h6>
            <button class="kb-btn kb-btn-blue" style="padding:6px 14px;font-size:12px;" @click="openCatModal()"><i class="fas fa-plus"></i> Tambah Kategori</button>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="kb-chip" :class="{'active':!filterCatId}" @click="filterCat(null)">Semua <span class="kb-chip-count">{{ totalDocCount }}</span></span>
            <span v-for="c in categories" :key="c.id" class="kb-chip" :class="{'active':filterCatId===c.id}" @click="filterCat(c.id)">{{ c.name }} <span class="kb-chip-count">{{ c.documents_count||0 }}</span></span>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="kb-card">
        <div class="flex items-center justify-between mb-3 flex-wrap gap-3">
            <h6 class="fw-bold" style="color:#0f172a;font-size:14px;"><i class="fas fa-file-alt mr-2" style="color:#2563eb;"></i>Dokumen</h6>
            <div class="kb-search-wrap">
                <i class="fas fa-search kb-search-icon"></i>
                <input v-model="searchQuery" @input="debounceSearch" type="text" class="kb-search" placeholder="Cari dokumen...">
                <button v-if="searchQuery" @click="searchQuery='';loadDocuments(1)" class="kb-search-clear">&times;</button>
            </div>
        </div>

        <div v-if="loadingDocs" class="text-center py-10"><div class="kb-spinner"></div><div class="text-xs mt-2" style="color:#94a3b8;">Memuat data...</div></div>

        <div v-else-if="documents.length" class="overflow-x-auto">
            <table class="kb-table">
                <thead><tr>
                    <th>No</th><th>Judul</th><th>Kategori</th><th>Chunks</th><th>Embedding</th><th>Tokens</th><th>Status</th><th>Tanggal</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                    <tr v-for="(d,i) in documents" :key="d.id">
                        <td style="color:#94a3b8;font-weight:600;">{{ (pagination.from||0)+i }}</td>
                        <td><div class="fw-semibold" style="color:#0f172a;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" :title="d.title">{{ d.title }}</div></td>
                        <td><span class="text-xs" style="color:#64748b;">{{ d.category?.name||'-' }}</span></td>
                        <td style="font-weight:600;">{{ d.chunks_count||0 }}</td>
                        <td>
                            <span v-if="d.null_embeddings>0" class="kb-badge kb-badge-red" title="Ada chunk tanpa embedding"><i class="fas fa-exclamation-triangle"></i> {{ d.null_embeddings }}</span>
                            <span v-else class="kb-badge kb-badge-green"><i class="fas fa-check"></i> OK</span>
                        </td>
                        <td>{{ formatNum(d.token_count) }}</td>
                        <td><span class="kb-badge" :class="d.status==='active'?'kb-badge-green':'kb-badge-yellow'">{{ d.status }}</span></td>
                        <td class="text-xs" style="color:#94a3b8;">{{ formatDate(d.updated_at) }}</td>
                        <td>
                            <div class="flex gap-1">
                                <button @click="openDocModal(d.id)" class="kb-action-btn kb-action-edit" title="Edit"><i class="fas fa-edit"></i></button>
                                <button @click="regenDoc(d.id,$event)" class="kb-action-btn kb-action-regen" title="Regenerate Embedding"><i class="fas fa-sync-alt"></i></button>
                                <button @click="delDoc(d.id)" class="kb-action-btn kb-action-delete" title="Hapus"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="empty-state"><div class="empty-icon">📄</div><h4>Belum ada dokumen</h4><p>Tambah dokumen untuk memulai Knowledge Base.</p></div>

        <div v-if="pagination.total" class="flex items-center justify-between mt-4 px-2 flex-wrap gap-2">
            <div class="text-xs" style="color:#64748b;">Menampilkan {{ pagination.from }} - {{ pagination.to }} dari {{ pagination.total }} dokumen</div>
            <div class="flex gap-1">
                <button v-for="p in paginationPages" :key="p" @click="typeof p==='number'&&loadDocuments(p)" class="kb-page-btn" :class="{'active':p===pagination.current,'disabled':p==='...'}">{{ p }}</button>
            </div>
        </div>
    </div>

    <!-- Modal: Category -->
    <div v-if="showCatModal" class="kb-modal-overlay" @click.self="showCatModal=false">
        <div class="kb-modal kb-modal-sm">
            <div class="kb-modal-header"><h5>{{ editCatId?'Edit':'Tambah' }} Kategori</h5><button @click="showCatModal=false" class="kb-modal-close">&times;</button></div>
            <div class="kb-modal-body">
                <label class="kb-label">Nama Kategori</label>
                <input v-model="catForm.name" type="text" class="kb-input" placeholder="Contoh: SPMB" maxlength="255">
                <label class="kb-label mt-3">Deskripsi</label>
                <textarea v-model="catForm.description" class="kb-input" rows="2" placeholder="Deskripsi singkat..." maxlength="1000"></textarea>
            </div>
            <div class="kb-modal-footer">
                <button @click="showCatModal=false" class="kb-btn kb-btn-gray">Batal</button>
                <button @click="saveCategory" class="kb-btn kb-btn-blue"><i class="fas fa-save mr-1"></i> Simpan</button>
            </div>
        </div>
    </div>

    <!-- Modal: Document -->
    <div v-if="showDocModal" class="kb-modal-overlay" @click.self="showDocModal=false">
        <div class="kb-modal kb-modal-lg">
            <div class="kb-modal-header"><h5>{{ editDocId?'Edit':'Tambah' }} Dokumen</h5><button @click="showDocModal=false" class="kb-modal-close">&times;</button></div>
            <div class="kb-modal-body">
                <!-- Input Mode Tabs -->
                <div class="flex gap-2 mb-4">
                    <button @click="inputMode='manual'" class="kb-tab" :class="{'active':inputMode==='manual'}"><i class="fas fa-keyboard mr-1"></i> Tulis Manual</button>
                    <button @click="inputMode='pdf'" class="kb-tab" :class="{'active':inputMode==='pdf'}"><i class="fas fa-file-pdf mr-1"></i> Upload PDF</button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="md:col-span-2"><label class="kb-label"><i class="fas fa-heading mr-1" style="color:#3b82f6;"></i> Judul Dokumen</label><input v-model="docForm.title" type="text" class="kb-input" placeholder="Contoh: Panduan SPMB 2026"></div>
                    <div><label class="kb-label"><i class="fas fa-folder mr-1" style="color:#3b82f6;"></i> Kategori</label><select v-model="docForm.category_id" class="kb-input"><option :value="null">-- Tanpa Kategori --</option><option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option></select></div>
                </div>

                <!-- Manual -->
                <div v-if="inputMode==='manual'" class="mb-4">
                    <label class="kb-label"><i class="fas fa-align-left mr-1" style="color:#3b82f6;"></i> Isi Dokumen</label>
                    <textarea v-model="docForm.content" class="kb-input" rows="10" placeholder="Tulis atau paste isi dokumen di sini..."></textarea>
                </div>

                <!-- PDF Upload -->
                <div v-if="inputMode==='pdf'" class="mb-4">
                    <label class="kb-label"><i class="fas fa-file-pdf mr-1" style="color:#dc2626;"></i> Upload File PDF</label>
                    <div v-if="!selectedPdf" class="kb-dropzone" @click="$refs.pdfInput.click()" @dragover.prevent="dropActive=true" @dragleave="dropActive=false" @drop.prevent="handleDrop($event)" :class="{'active':dropActive}">
                        <i class="fas fa-cloud-upload-alt" style="font-size:36px;color:#94a3b8;"></i>
                        <p class="text-sm" style="color:#64748b;">Klik atau drag & drop file PDF di sini</p>
                        <p class="text-xs" style="color:#94a3b8;">Maksimal 10MB</p>
                        <input ref="pdfInput" type="file" accept=".pdf" style="display:none;" @change="handlePdfSelect($event)">
                    </div>
                    <div v-else class="kb-pdf-info">
                        <div class="flex items-center justify-between">
                            <div><i class="fas fa-file-pdf mr-2" style="color:#dc2626;"></i><span class="fw-semibold text-sm">{{ selectedPdf.name }}</span><span class="text-xs ml-2" style="color:#64748b;">{{ (selectedPdf.size/1024/1024).toFixed(2) }} MB</span></div>
                            <button @click="removePdf" class="kb-btn-sm kb-btn-red"><i class="fas fa-times"></i></button>
                        </div>
                    </div>
                    <div v-if="pdfParsing" class="text-center py-4"><div class="kb-spinner"></div><p class="text-xs mt-2" style="color:#64748b;">Membaca PDF...</p></div>
                    <div v-if="pdfResult" class="mt-2 rounded-lg p-3 text-sm" :class="pdfResult.ok?'kb-alert-green':'kb-alert-red'">
                        <strong>{{ pdfResult.ok ? '✅ PDF berhasil dibaca!' : '⚠️ PDF belum bisa diproses' }}</strong><br>{{ pdfResult.message }}
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div><label class="kb-label"><i class="fas fa-toggle-on mr-1" style="color:#3b82f6;"></i> Status</label><select v-model="docForm.status" class="kb-input"><option value="active">Active (digunakan untuk RAG)</option><option value="draft">Draft (tidak digunakan)</option></select></div>
                    <div class="flex items-end"><button @click="previewChunks" class="kb-btn kb-btn-gray"><i class="fas fa-eye mr-1"></i> Preview Chunks</button></div>
                </div>

                <!-- Stats Bar -->
                <div v-if="chunkPreview" class="kb-stats-bar mb-3">
                    <div class="text-center"><div class="kb-stat-number" style="font-size:20px;">{{ chunkPreview.chunk_count }}</div><div class="kb-stat-label">Chunks</div></div>
                    <div class="text-center"><div class="kb-stat-number" style="font-size:20px;">{{ formatNum(chunkPreview.token_count) }}</div><div class="kb-stat-label">Tokens</div></div>
                    <div class="text-center"><div class="kb-stat-number" style="font-size:20px;">${{ ((chunkPreview.token_count||0)/1000000*0.02).toFixed(4) }}</div><div class="kb-stat-label">Est. Biaya</div></div>
                </div>

                <!-- Chunk Preview List -->
                <div v-if="chunkPreview?.chunks?.length" class="mb-4">
                    <label class="kb-label mb-2"><i class="fas fa-puzzle-piece mr-1" style="color:#3b82f6;"></i> Preview Chunks</label>
                    <div class="kb-chunk-list">
                        <div v-for="(chunk,i) in chunkPreview.chunks" :key="i" class="kb-chunk-item">
                            <div class="kb-chunk-num">Chunk {{ i+1 }}</div>
                            <div class="text-xs" style="color:#475569;">{{ chunk.substring(0,300) }}{{ chunk.length>300?'...':'' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="kb-modal-footer">
                <button @click="showDocModal=false" class="kb-btn kb-btn-gray">Batal</button>
                <button @click="saveDocument" class="kb-btn kb-btn-blue" :disabled="savingDoc">
                    <i :class="savingDoc?'fa-spinner fa-spin':'fa-save'" class="fas mr-1"></i>
                    {{ savingDoc?'Menyimpan...':(editDocId?'Update Dokumen':'Simpan & Generate Embeddings') }}
                </button>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import {ref,reactive,computed,onMounted} from 'vue';
import api from '@/bootstrap.js';
import {swalConfirm,swalError,swalSuccess,swalLoading,swalClose} from '@/swal.js';

const BASE='/chatbot/admin/knowledge-base';
const categories=ref([]);
const documents=ref([]);
const stats=ref({});
const filterCatId=ref(null);
const searchQuery=ref('');
const loadingDocs=ref(false);
const regenLoading=ref(false);
const savingDoc=ref(false);
const pagination=ref({current:1,last:1,from:0,to:0,total:0});
let searchTimer=null;

// Category modal
const showCatModal=ref(false);
const editCatId=ref(null);
const catForm=reactive({name:'',description:''});

// Document modal
const showDocModal=ref(false);
const editDocId=ref(null);
const inputMode=ref('manual');
const docForm=reactive({title:'',content:'',category_id:null,status:'active'});
const selectedPdf=ref(null);
const pdfParsing=ref(false);
const pdfResult=ref(null);
const dropActive=ref(false);
const chunkPreview=ref(null);

const totalDocCount=computed(()=>categories.value.reduce((s,c)=>s+(c.documents_count||0),0));

const paginationPages=computed(()=>{
    const cur=pagination.value.current,last=pagination.value.last;
    if(last<=7)return Array.from({length:last},(_,i)=>i+1);
    const pages=[];
    if(cur>3){pages.push(1);if(cur>4)pages.push('...');}
    for(let i=Math.max(1,cur-2);i<=Math.min(last,cur+2);i++)pages.push(i);
    if(cur<last-2){if(cur<last-3)pages.push('...');pages.push(last);}
    return pages;
});

function formatNum(n){if(!n&&n!==0)return'-';return Number(n).toLocaleString('id-ID');}
function formatDate(d){if(!d)return'-';return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'short',year:'numeric'});}

async function load(){
    try{const[cat,st]=await Promise.all([api.get(BASE+'/categories'),api.get(BASE+'/stats')]);
        categories.value=cat.data.categories||[];stats.value=st.data;
    }catch(e){}
    loadDocuments(1);
}

async function loadDocuments(page){
    loadingDocs.value=true;
    try{
        let url=`${BASE}/documents?page=${page}`;
        if(filterCatId.value)url+=`&category_id=${filterCatId.value}`;
        if(searchQuery.value.trim())url+=`&search=${encodeURIComponent(searchQuery.value.trim())}`;
        const{data}=await api.get(url);
        const pag=data.documents||data;
        documents.value=pag.data||[];
        pagination.value={current:pag.current_page||1,last:pag.last_page||1,from:pag.from||0,to:pag.to||0,total:pag.total||0};
    }catch(e){console.error(e);}
    loadingDocs.value=false;
}

function debounceSearch(){if(searchTimer)clearTimeout(searchTimer);searchTimer=setTimeout(()=>loadDocuments(1),400);}
function filterCat(id){filterCatId.value=id;loadDocuments(1);}

// Category CRUD
function openCatModal(id){
    editCatId.value=id||null;catForm.name='';catForm.description='';
    if(id){const c=categories.value.find(x=>x.id===id);if(c){catForm.name=c.name;catForm.description=c.description||'';}}
    showCatModal.value=true;
}
async function saveCategory(){
    if(!catForm.name.trim()){swalError('Nama kategori wajib diisi!');return;}
    try{if(editCatId.value)await api.put(`${BASE}/categories/${editCatId.value}`,catForm);else await api.post(`${BASE}/categories`,catForm);
        swalSuccess('Kategori tersimpan!');showCatModal.value=false;load();
    }catch(e){swalError('Gagal menyimpan kategori');}
}
async function deleteCategory(id){if(!await swalConfirm('Hapus kategori dan semua dokumennya?'))return;try{await api.delete(`${BASE}/categories/${id}`);swalSuccess('Dihapus!');load();}catch(e){swalError('Gagal');}}

// Document CRUD
async function openDocModal(id){
    editDocId.value=id||null;docForm.title='';docForm.content='';docForm.category_id=null;docForm.status='active';
    inputMode.value='manual';selectedPdf.value=null;pdfResult.value=null;chunkPreview.value=null;savingDoc.value=false;
    if(id){
        try{const{data}=await api.get(`${BASE}/documents/${id}/edit`);
            docForm.title=data.title;docForm.content=data.content||'';docForm.category_id=data.category_id;docForm.status=data.status;
        }catch(e){swalError('Gagal memuat dokumen');return;}
    }
    showDocModal.value=true;
}
async function saveDocument(){
    if(!docForm.title.trim()){swalError('Judul wajib diisi!');return;}
    if(inputMode.value==='manual'&&!docForm.content.trim()){swalError('Isi dokumen wajib diisi!');return;}
    if(inputMode.value==='pdf'&&!selectedPdf.value&&!editDocId.value){swalError('Pilih file PDF!');return;}
    savingDoc.value=true;
    try{
        const fd=new FormData();
        fd.append('title',docForm.title);fd.append('content',docForm.content||'');fd.append('category_id',docForm.category_id||'');fd.append('status',docForm.status);
        if(inputMode.value==='pdf'&&selectedPdf.value)fd.append('pdf_file',selectedPdf.value);
        if(editDocId.value)fd.append('_method','PUT');
        const url=editDocId.value?`${BASE}/documents/${editDocId.value}`:`${BASE}/documents`;
        await api.post(url,fd,{headers:{'Content-Type':'multipart/form-data'},timeout:120000});
        swalSuccess('Dokumen tersimpan & embedding digenerate!');showDocModal.value=false;load();
    }catch(e){swalError(e.response?.data?.message||'Gagal menyimpan dokumen');}
    savingDoc.value=false;
}
async function delDoc(id){if(!await swalConfirm('Hapus dokumen ini? Semua chunks juga akan dihapus.'))return;try{await api.delete(`${BASE}/documents/${id}`);swalSuccess('Dihapus!');load();}catch(e){swalError('Gagal');}}

// PDF handling
function handlePdfSelect(e){const f=e.target.files?.[0];if(f)selectedPdf.value=f;pdfResult.value=null;parsePdf();}
function handleDrop(e){dropActive.value=false;const f=e.dataTransfer.files?.[0];if(f&&f.type==='application/pdf'){selectedPdf.value=f;pdfResult.value=null;parsePdf();}}
function removePdf(){selectedPdf.value=null;pdfResult.value=null;}
async function parsePdf(){
    if(!selectedPdf.value)return;pdfParsing.value=true;
    try{const fd=new FormData();fd.append('pdf_file',selectedPdf.value);
        const{data}=await api.post(`${BASE}/parse-pdf`,fd,{headers:{'Content-Type':'multipart/form-data'},timeout:60000});
        pdfResult.value={ok:data.status==='ok',message:data.status==='ok'?`${data.chunk_count} chunks · ${formatNum(data.token_count)} tokens`:data.message};
        if(data.status==='ok'&&!docForm.title.trim())docForm.title=selectedPdf.value.name.replace(/\.pdf$/i,'');
    }catch(e){
        const msg=e.response?.data?.message||e.message||'Gagal membaca PDF';
        console.error('PDF parse error:',e);
        pdfResult.value={ok:false,message:msg};
    }
    pdfParsing.value=false;
}

// Chunks
async function previewChunks(){
    const content=inputMode.value==='manual'?docForm.content.trim():(pdfResult.value?.ok?docForm.content.trim():'');
    if(!content){swalError(inputMode.value==='pdf'?'PDF belum berhasil dibaca.':'Isi dokumen belum diisi!');return;}
    try{const{data}=await api.post(`${BASE}/preview-chunks`,{content});
        chunkPreview.value={chunk_count:data.count||0,token_count:data.token_count||0,chunks:data.chunks||[]};
    }catch(e){swalError('Gagal preview chunks');}
}

// Regenerate
async function regenDoc(id,e){if(e){e.stopPropagation();e.preventDefault();}
    if(!await swalConfirm('Generate ulang embedding untuk dokumen ini?'))return;
    swalLoading('Regenerate...');try{await api.post(`${BASE}/regenerate-document/${id}`,{},{timeout:120000});swalClose();swalSuccess('Embedding regenerated!');load();}catch(e){swalClose();swalError('Gagal');}
}
async function regenerateAll(){
    if(!await swalConfirm('Generate ulang semua embedding? Proses ini mungkin memakan waktu.'))return;
    regenLoading.value=true;swalLoading('Generate semua embedding...');
    try{const{data}=await api.post(`${BASE}/regenerate-embeddings`,{},{timeout:120000});swalClose();swalSuccess(`Selesai: ${data.processed||0} chunks diproses`);load();}catch(e){swalClose();swalError('Gagal');}
    regenLoading.value=false;
}

onMounted(load);
</script>

<style scoped>
.kb-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:18px;box-shadow:0 4px 12px rgba(15,23,42,.06);transition:all .2s}
.kb-card:hover{box-shadow:0 8px 24px rgba(15,23,42,.10);transform:translateY(-2px)}
.kb-stat-card{background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:1px solid #bae6fd;border-radius:12px;padding:16px 20px;text-align:center}
.kb-stat-number{font-size:28px;font-weight:700;color:#0369a1}
.kb-stat-label{font-size:12px;color:#64748b;margin-top:4px}
.kb-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:all .15s;font-family:'Quicksand',sans-serif}
.kb-btn:hover{transform:translateY(-1px)}.kb-btn:disabled{opacity:.6;cursor:not-allowed;transform:none}
.kb-btn-blue{background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff}
.kb-btn-green{background:linear-gradient(135deg,#059669,#10b981);color:#fff}
.kb-btn-gray{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0}
.kb-btn-red{background:#fee2e2;color:#dc2626;border:none;padding:4px 10px;border-radius:6px;cursor:pointer;font-size:12px}
.kb-btn-sm{padding:4px 10px;border-radius:6px;border:none;cursor:pointer;font-size:12px}
.kb-chip{display:inline-flex;align-items:center;gap:6px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;padding:6px 14px;font-size:13px;font-weight:500;color:#334155;cursor:pointer;transition:all .15s}
.kb-chip:hover,.kb-chip.active{background:#2563eb;color:#fff;border-color:#2563eb}
.kb-chip-count{background:rgba(255,255,255,.3);padding:1px 8px;border-radius:999px;font-size:11px}
.kb-search-wrap{position:relative}
.kb-search{padding:8px 34px 8px 34px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;width:240px;outline:none;font-family:'Quicksand',sans-serif;transition:all .2s}
.kb-search:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.kb-search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px}
.kb-search-clear{position:absolute;right:8px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:16px;background:none;border:none;cursor:pointer}
.kb-table{width:100%;border-collapse:collapse;font-size:13px}
.kb-table th{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;padding:10px 14px;text-align:left;white-space:nowrap}
.kb-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.kb-table tr:hover td{background:#f8fafc}
.kb-badge{display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:3px 10px;border-radius:999px}
.kb-badge-green{background:#dcfce7;color:#15803d}.kb-badge-red{background:#fee2e2;color:#dc2626}.kb-badge-yellow{background:#fef9c3;color:#a16207}
.kb-action-btn{width:30px;height:30px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:all .15s}
.kb-action-btn:hover{transform:translateY(-1px)}
.kb-action-edit{background:#dbeafe;color:#2563eb}.kb-action-edit:hover{background:#bfdbfe}
.kb-action-delete{background:#fee2e2;color:#dc2626}.kb-action-delete:hover{background:#fecaca}
.kb-action-regen{background:#e0f2fe;color:#0284c7}.kb-action-regen:hover{background:#bae6fd}
.kb-page-btn{min-width:32px;height:32px;border-radius:8px;border:1px solid #e2e8f0;background:#fff;color:#334155;font-size:12px;font-weight:600;cursor:pointer;transition:all .15s}
.kb-page-btn.active{background:#2563eb;color:#fff;border-color:#2563eb}
.kb-page-btn.disabled{opacity:.4;cursor:default}
.kb-spinner{width:32px;height:32px;border:3px solid #e2e8f0;border-top-color:#3b82f6;border-radius:50%;animation:spin .7s linear infinite;margin:0 auto}
@keyframes spin{to{transform:rotate(360deg)}}
.empty-state{text-align:center;padding:60px 20px;color:#94a3b8}
.empty-icon{font-size:48px;margin-bottom:12px}
.empty-state h4{font-weight:600;color:#64748b;margin-bottom:6px}
.kb-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);z-index:10000;display:flex;align-items:center;justify-content:center;padding:1rem}
.kb-modal{background:#fff;border-radius:16px;box-shadow:0 24px 48px rgba(2,6,23,.25);width:100%;max-height:90vh;display:flex;flex-direction:column;overflow:hidden}
.kb-modal-sm{max-width:440px}.kb-modal-lg{max-width:800px}
.kb-modal-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0}
.kb-modal-header h5{font-size:16px;font-weight:700;color:#0f172a;margin:0}
.kb-modal-close{background:none;border:none;font-size:22px;color:#94a3b8;cursor:pointer;padding:4px 8px;border-radius:6px}.kb-modal-close:hover{background:#f1f5f9}
.kb-modal-body{padding:20px;overflow-y:auto;flex:1}
.kb-modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:12px 20px;border-top:1px solid #e2e8f0}
.kb-label{display:block;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:#64748b;margin-bottom:6px}
.kb-input{width:100%;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:14px;outline:none;font-family:'Quicksand',sans-serif;transition:all .2s}
.kb-input:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.kb-tab{padding:6px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .15s;font-family:'Quicksand',sans-serif}
.kb-tab.active{background:#2563eb;color:#fff}.kb-tab:not(.active){background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0}
.kb-dropzone{border:2px dashed #cbd5e1;border-radius:12px;padding:30px;text-align:center;cursor:pointer;transition:all .2s;background:#f8fafc}
.kb-dropzone.active{border-color:#3b82f6;background:#eff6ff}
.kb-pdf-info{margin-top:10px;padding:10px 14px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px}
.kb-stats-bar{background:linear-gradient(135deg,#f0f9ff,#e0f2fe);border:1px solid #bae6fd;border-radius:10px;padding:12px 16px;display:flex;gap:24px}
.kb-chunk-list{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px;max-height:200px;overflow-y:auto}
.kb-chunk-item{background:#fff;border:1px solid #e2e8f0;border-radius:8px;padding:10px 14px;margin-bottom:8px}
.kb-chunk-item:last-child{margin-bottom:0}
.kb-chunk-num{font-weight:700;color:#2563eb;font-size:12px;margin-bottom:4px}
.kb-alert-green{background:#dcfce7;border:1px solid #bbf7d0;color:#15803d}
.kb-alert-red{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c}
</style>
