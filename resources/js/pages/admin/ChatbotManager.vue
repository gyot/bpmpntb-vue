<template>
<div>
    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Kelola Chatbot INTAN</h2>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <div class="card p-5"><div class="text-sm" style="color:var(--color-text-secondary)">Total Percakapan</div><div class="text-2xl font-bold" style="color:var(--color-primary)">{{ analytics.total || 0 }}</div></div>
        <div class="card p-5"><div class="text-sm" style="color:var(--color-text-secondary)">Hari Ini</div><div class="text-2xl font-bold" style="color:var(--color-accent)">{{ analytics.today || 0 }}</div></div>
        <div class="card p-5"><div class="text-sm" style="color:var(--color-text-secondary)">Total User</div><div class="text-2xl font-bold" style="color:var(--color-secondary)">{{ analytics.users || 0 }}</div></div>
    </div>

    <div class="card p-6 mb-6">
        <h3 class="text-sm font-semibold mb-4" style="color:var(--color-text-primary)">{{ editKwId ? 'Edit' : 'Tambah' }} Keyword Response</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div><label class="input-label">Kata Kunci *</label><input v-model="kwForm.keyword" type="text" class="input-field" placeholder="Contoh: layanan" required></div>
            <div><label class="input-label">Respon (boleh HTML) *</label><textarea v-model="kwForm.response" rows="2" class="input-field" required></textarea></div>
        </div>
        <div class="flex gap-2">
            <button @click="saveKeyword" class="btn-primary"><i class="fas fa-save mr-2"></i>{{ editKwId ? 'Update' : 'Simpan' }}</button>
            <button v-if="editKwId" @click="editKwId=null;kwForm.keyword='';kwForm.response=''" class="btn-ghost border border-gray-200">Batal</button>
        </div>
    </div>

    <div class="card overflow-hidden mb-8">
        <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
            <h3 class="text-sm font-semibold" style="color:var(--color-text-primary)">Keyword Responses</h3>
            <span class="badge badge-primary">{{ keywords.length }}</span>
        </div>
        <table class="w-full"><thead class="bg-gray-50/80"><tr><th class="table-header">#</th><th class="table-header">Keyword</th><th class="table-header">Respon</th><th class="table-header">Aksi</th></tr></thead>
        <tbody><tr v-for="k in keywords" :key="k.id" class="border-t border-gray-100 hover:bg-gray-50/50"><td class="table-cell text-gray-400">{{ k.id }}</td><td class="table-cell font-medium" style="color:var(--color-text-primary)">{{ k.keyword }}</td><td class="table-cell text-sm max-w-md truncate" style="color:var(--color-text-secondary)" v-html="(k.response||'').substring(0,100)"></td><td class="table-cell"><div class="flex gap-1"><button @click="editKwId=k.id;kwForm.keyword=k.keyword;kwForm.response=k.response" class="p-2 rounded-lg hover:bg-blue-50" style="color:var(--color-primary)"><i class="fas fa-edit text-sm"></i></button><button @click="deleteKeyword(k.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500"><i class="fas fa-trash text-sm"></i></button></div></td></tr></tbody></table>
        <div v-if="!keywords.length" class="text-center py-8 text-gray-400"><i class="fas fa-robot text-3xl mb-2"></i><p class="text-sm">Belum ada keyword.</p></div>
    </div>

    <div class="card p-6 mb-6">
        <h3 class="text-sm font-semibold mb-4" style="color:var(--color-text-primary)">{{ editIntentId ? 'Edit' : 'Tambah' }} Intent</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div><label class="input-label">Keyword Intent</label><input v-model="intentForm.keyword" type="text" class="input-field"></div>
            <div><label class="input-label">Respon Intent</label><textarea v-model="intentForm.response" rows="2" class="input-field"></textarea></div>
        </div>
        <div class="flex gap-2">
            <button @click="saveIntent" class="btn-primary"><i class="fas fa-save mr-2"></i>{{ editIntentId ? 'Update' : 'Simpan' }}</button>
            <button v-if="editIntentId" @click="editIntentId=null;intentForm.keyword='';intentForm.response=''" class="btn-ghost border border-gray-200">Batal</button>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-gray-100"><h3 class="text-sm font-semibold" style="color:var(--color-text-primary)">Intents</h3></div>
        <table class="w-full"><thead class="bg-gray-50/80"><tr><th class="table-header">#</th><th class="table-header">Keyword</th><th class="table-header">Respon</th><th class="table-header">Aksi</th></tr></thead>
        <tbody><tr v-for="i in intents" :key="i.id" class="border-t border-gray-100 hover:bg-gray-50/50"><td class="table-cell text-gray-400">{{ i.id }}</td><td class="table-cell font-medium" style="color:var(--color-text-primary)">{{ i.keyword }}</td><td class="table-cell text-sm max-w-md truncate" style="color:var(--color-text-secondary)" v-html="(i.response||'').substring(0,100)"></td><td class="table-cell"><div class="flex gap-1"><button @click="editIntentId=i.id;intentForm.keyword=i.keyword;intentForm.response=i.response" class="p-2 rounded-lg hover:bg-blue-50" style="color:var(--color-primary)"><i class="fas fa-edit text-sm"></i></button><button @click="deleteIntent(i.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500"><i class="fas fa-trash text-sm"></i></button></div></td></tr></tbody></table>
        <div v-if="!intents.length" class="text-center py-8 text-gray-400"><i class="fas fa-brain text-3xl mb-2"></i><p class="text-sm">Belum ada intent.</p></div>
    </div>
</div>
</template>

<script setup>
import {ref,reactive,onMounted} from 'vue';
import api from '@/bootstrap.js';
import {swalConfirm,swalError,swalSuccess} from '@/swal.js';

const keywords=ref([]);const intents=ref([]);const analytics=ref({});
const editKwId=ref(null);const kwForm=reactive({keyword:'',response:''});
const editIntentId=ref(null);const intentForm=reactive({keyword:'',response:''});

async function load(){try{const[kw,int,an]=await Promise.all([api.get('/chatbot-responses'),api.get('/chatbot-intents'),api.get('/chatbot-analytics')]);keywords.value=kw.data;intents.value=int.data;analytics.value=an.data;}catch(e){}}

async function saveKeyword(){try{if(editKwId.value)await api.put(`/chatbot-responses/${editKwId.value}`,kwForm);else await api.post('/chatbot-responses',kwForm);swalSuccess('Tersimpan!');editKwId.value=null;kwForm.keyword='';kwForm.response='';load();}catch(e){swalError('Gagal');}}
async function deleteKeyword(id){if(!await swalConfirm('Hapus keyword ini?'))return;try{await api.delete(`/chatbot-responses/${id}`);swalSuccess('Dihapus!');load();}catch(e){swalError('Gagal');}}

async function saveIntent(){try{if(editIntentId.value)await api.put(`/chatbot-intents/${editIntentId.value}`,intentForm);else await api.post('/chatbot-intents',intentForm);swalSuccess('Tersimpan!');editIntentId.value=null;intentForm.keyword='';intentForm.response='';load();}catch(e){swalError('Gagal');}}
async function deleteIntent(id){if(!await swalConfirm('Hapus intent ini?'))return;try{await api.delete(`/chatbot-intents/${id}`);swalSuccess('Dihapus!');load();}catch(e){swalError('Gagal');}}

onMounted(load);
</script>
<style scoped>.input-label{@apply block text-xs font-semibold uppercase tracking-wider mb-1.5;color:var(--color-text-secondary);}</style>
