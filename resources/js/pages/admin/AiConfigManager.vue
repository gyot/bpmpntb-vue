<template>
<div>
    <h2 class="text-2xl font-bold mb-6" style="color:var(--color-text-primary)">Konfigurasi AI Chatbot</h2>
    <p class="text-sm mb-6" style="color:var(--color-text-secondary)">Atur provider AI untuk chatbot INTAN (OpenAI, OpenAI-compatible, Ollama)</p>

    <form @submit.prevent="save" class="card p-6 mb-6">
        <h3 class="text-sm font-semibold mb-4" style="color:var(--color-text-primary)">{{ editId ? 'Edit' : 'Tambah' }} Konfigurasi AI</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div><label class="input-label">Nama</label><input v-model="form.name" type="text" class="input-field" placeholder="OpenAI GPT-4o Mini" required></div>
            <div><label class="input-label">Provider</label><select v-model="form.provider_type" class="input-field"><option value="openai">OpenAI</option><option value="openai_compatible">OpenAI Compatible</option><option value="ollama">Ollama</option></select></div>
            <div><label class="input-label">Base URL</label><input v-model="form.base_url" type="text" class="input-field" placeholder="https://api.openai.com/v1" required></div>
            <div><label class="input-label">API Key</label><input v-model="form.api_key" type="password" class="input-field" placeholder="sk-..."></div>
            <div><label class="input-label">Chat Model</label><input v-model="form.chat_model" type="text" class="input-field" placeholder="gpt-4o-mini" required></div>
            <div><label class="input-label">Max Tokens</label><input v-model.number="form.max_tokens" type="number" class="input-field" min="1"></div>
            <div><label class="input-label">Temperature</label><input v-model.number="form.temperature" type="number" step="0.1" min="0" max="2" class="input-field"></div>
            <div class="flex items-center gap-2 pt-6"><input v-model="form.is_active" type="checkbox" id="is_active" class="w-4 h-4"><label for="is_active" class="text-sm font-medium" style="color:var(--color-text-primary)">Aktifkan</label></div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn-primary"><i class="fas fa-save mr-2"></i>{{ editId ? 'Update' : 'Simpan' }}</button>
            <button v-if="editId" type="button" @click="cancelEdit" class="btn-ghost border border-gray-200">Batal</button>
        </div>
    </form>

    <div class="space-y-3">
        <div v-for="c in configs" :key="c.id" class="card p-4 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :style="{background:c.is_active?'var(--color-primary)':'#e5e7eb',color:c.is_active?'white':'#9ca3af'}"><i class="fas fa-robot"></i></div>
            <div class="flex-1 min-w-0">
                <h4 class="text-sm font-semibold" style="color:var(--color-text-primary)">{{ c.name }}</h4>
                <div class="flex items-center gap-2 mt-1">
                    <span class="badge text-[10px]" :class="c.is_active?'badge-success':'badge-warning'">{{ c.is_active?'Aktif':'Nonaktif' }}</span>
                    <span class="text-[10px]" style="color:var(--color-text-secondary)">{{ c.provider_type }} · {{ c.chat_model }}</span>
                </div>
            </div>
            <div class="flex gap-1">
                <button @click="testConfig(c.id)" class="p-2 rounded-lg hover:bg-green-50 text-green-600 transition" title="Test"><i class="fas fa-plug text-sm"></i></button>
                <button @click="editConfig(c)" class="p-2 rounded-lg hover:bg-blue-50 transition" style="color:var(--color-primary)" title="Edit"><i class="fas fa-edit text-sm"></i></button>
                <button @click="deleteConfig(c.id)" class="p-2 rounded-lg hover:bg-red-50 text-red-500 transition" title="Hapus"><i class="fas fa-trash text-sm"></i></button>
            </div>
        </div>
        <div v-if="!configs.length" class="card text-center py-12"><i class="fas fa-robot text-4xl text-gray-200 mb-3"></i><p class="text-sm" style="color:var(--color-text-secondary)">Belum ada konfigurasi AI.</p></div>
    </div>
</div>
</template>

<script setup>
import {ref,reactive,onMounted} from 'vue';
import api from '@/bootstrap.js';
import {swalConfirm,swalError,swalSuccess,swalLoading,swalClose} from '@/swal.js';

const configs=ref([]);const editId=ref(null);
const form=reactive({name:'',provider_type:'openai_compatible',base_url:'',api_key:'',chat_model:'',max_tokens:1000,temperature:0.7,is_active:false});

async function load(){try{const{data}=await api.get('/ai-configs');configs.value=data;}catch(e){}}
function editConfig(c){editId.value=c.id;Object.assign(form,{name:c.name,provider_type:c.provider_type,base_url:c.base_url,api_key:'',chat_model:c.chat_model,max_tokens:c.max_tokens,temperature:c.temperature,is_active:c.is_active});}
function cancelEdit(){editId.value=null;Object.assign(form,{name:'',provider_type:'openai_compatible',base_url:'',api_key:'',chat_model:'',max_tokens:1000,temperature:0.7,is_active:false});}

async function save(){
    try{
        const payload={...form};if(!payload.api_key)delete payload.api_key;
        if(editId.value)await api.put(`/ai-configs/${editId.value}`,payload);
        else await api.post('/ai-configs',payload);
        swalSuccess('Konfigurasi AI disimpan!');cancelEdit();load();
    }catch(e){swalError(e.response?.data?.message||'Gagal');}
}

async function testConfig(id){
    swalLoading('Menguji koneksi AI...');
    try{const{data}=await api.post(`/ai-configs/${id}/test`);swalClose();
        if(data.status==='ok')swalSuccess(`Koneksi OK! Model: ${data.model}\n${data.message}`);
        else swalError(data.message||'Gagal');
    }catch(e){swalClose();swalError('Gagal menguji koneksi');}
}

async function deleteConfig(id){if(!await swalConfirm('Hapus konfigurasi ini?'))return;try{await api.delete(`/ai-configs/${id}`);swalSuccess('Dihapus!');load();}catch(e){swalError('Gagal');}}

onMounted(load);
</script>
<style scoped>.input-label{@apply block text-xs font-semibold uppercase tracking-wider mb-1.5;color:var(--color-text-secondary);}</style>
