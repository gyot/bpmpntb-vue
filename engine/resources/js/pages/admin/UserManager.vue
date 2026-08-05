<template>
<div>
    <!-- User Form -->
    <form @submit.prevent="save" class="um-card mb-6">
        <h3 class="um-title">{{ editId?'Edit':'Tambah' }} User</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-5">
            <div><label class="um-label">Nama</label><input v-model="form.name" type="text" class="um-input" required></div>
            <div><label class="um-label">Email</label><input v-model="form.email" type="email" class="um-input" required></div>
            <div><label class="um-label">Role</label>
                <select v-model="form.role" class="um-input">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div><label class="um-label">Password {{ editId?'(kosongkan jika tidak diubah)':'' }}</label><input v-model="form.password" type="password" class="um-input" :required="!editId"></div>
            <div><label class="um-label">Konfirmasi Password</label><input v-model="form.password_confirmation" type="password" class="um-input"></div>
        </div>
        <div class="flex gap-2">
            <button type="submit" class="um-btn um-btn-blue"><i class="fas fa-save mr-1"></i>{{ editId?'Update':'Simpan' }}</button>
            <button v-if="editId" type="button" @click="cancelEdit" class="um-btn um-btn-gray">Batal</button>
        </div>
    </form>

    <!-- Users Table -->
    <div class="um-card">
        <div class="flex items-center justify-between mb-4">
            <h3 class="um-title" style="margin-bottom:0">Daftar User</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="um-table">
                <thead><tr>
                    <th>#</th><th>Nama</th><th>Email</th><th>Role</th><th>Menu Access</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                    <tr v-for="u in users" :key="u.id">
                        <td style="color:#94a3b8;font-weight:600;">{{ u.id }}</td>
                        <td class="fw-semibold" style="color:#0f172a;">{{ u.name }}</td>
                        <td style="color:#64748b;">{{ u.email }}</td>
                        <td>
                            <span class="um-badge" :class="u.role==='admin'?'um-badge-blue':'um-badge-gray'">{{ u.role }}</span>
                        </td>
                        <td>
                            <span class="text-xs" style="color:#64748b;">{{ u.menu_access_count ?? 0 }} menu</span>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <button @click="edit(u)" class="um-action um-action-edit" title="Edit"><i class="fas fa-edit"></i></button>
                                <button @click="openMenuAccess(u)" class="um-action um-action-menu" title="Atur Menu Access"><i class="fas fa-shield-halved"></i></button>
                                <button @click="destroy(u.id)" class="um-action um-action-delete" title="Hapus"><i class="fas fa-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Menu Access -->
    <div v-if="showMenuModal" class="um-modal-overlay" @click.self="showMenuModal=false">
        <div class="um-modal">
            <div class="um-modal-header">
                <h5><i class="fas fa-shield-halved mr-2" style="color:#2563eb;"></i>Menu Access: {{ selectedUser?.name }}</h5>
                <button @click="showMenuModal=false" class="um-modal-close">&times;</button>
            </div>
            <div class="um-modal-body">
                <p class="text-xs mb-4" style="color:#64748b;">Centang menu yang boleh diakses. Klik panah untuk mengatur akses sub menu secara spesifik.</p>
                <div class="space-y-2">
                    <div v-for="(label, key) in allMenus" :key="key" class="um-menu-group">
                        <label class="um-menu-item" :class="{'um-menu-expanded': expandedMenus.has(key)}">
                            <input type="checkbox" :checked="selectedMenus.includes(key)" @change="toggleMenu(key)" class="um-checkbox">
                            <div class="um-menu-info" @click.self="toggleMenu(key)">
                                <div>
                                    <div class="fw-semibold text-sm" style="color:#0f172a;">{{ label }}</div>
                                    <div class="text-xs" style="color:#94a3b8;">{{ menuDescriptions[key] }}</div>
                                </div>
                            </div>
                            <button v-if="allSubMenus[key] && Object.keys(allSubMenus[key]).length" @click="toggleExpand(key)" class="um-expand-btn" :class="{'um-expand-open': expandedMenus.has(key)}" type="button">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </label>
                        <div v-if="expandedMenus.has(key) && allSubMenus[key]" class="um-submenu-list">
                            <label v-for="(subLabel, subKey) in allSubMenus[key]" :key="subKey" class="um-submenu-item">
                                <input type="checkbox" :value="subKey" :checked="isSubChecked(key, subKey)" @change="toggleSubMenu(key, subKey)" class="um-checkbox um-checkbox-sm">
                                <span class="text-xs" style="color:#334155;">{{ subLabel }}</span>
                            </label>
                            <div class="um-submenu-hint">
                                <i class="fas fa-info-circle mr-1" style="color:#94a3b8;"></i>
                                <span v-if="isSubMenuSelectionActive(key)">Hanya sub menu terpilih yang dapat diakses</span>
                                <span v-else>Semua sub menu dapat diakses</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="um-modal-footer">
                <button @click="selectAllMenus" class="um-btn um-btn-gray" style="font-size:12px;">Pilih Semua</button>
                <button @click="showMenuModal=false" class="um-btn um-btn-gray">Batal</button>
                <button @click="saveMenuAccess" class="um-btn um-btn-blue"><i class="fas fa-save mr-1"></i> Simpan</button>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import {ref,reactive,onMounted} from 'vue';
import api from '@/bootstrap.js';
import {swalConfirm,swalError,swalSuccess} from '@/swal.js';

const users=ref([]);
const editId=ref(null);
const form=reactive({name:'',email:'',password:'',password_confirmation:'',role:'user'});

const showMenuModal=ref(false);
const selectedUser=ref(null);
const selectedMenus=ref([]);
const selectedSubMenus=ref({});
const allMenus=ref({});
const allSubMenus=ref({});
const expandedMenus=ref(new Set());

const menuDescriptions={
    dashboard:'Halaman dashboard dan statistik',
    konten:'Kelola Berita, Artikel, Buletin, Jurnal, dll',
    kategori:'Kelola kategori konten',
    media:'Kelola Sliders, Layanan, Link Eksternal',
    chatbot:'Si Intan: Dashboard, Intent, Live Chat, Analytics, KB, AI Config',
    broadcast:'Kelola WhatsApp Broadcast',
    ppid:'Kelola PPID',
    pengaturan:'Pengaturan Website, Tema, Users',
};

async function load(){
    try{const{data}=await api.get('/users');users.value=data.data||data;}catch(e){}
}

function edit(u){
    editId.value=u.id;
    form.name=u.name;form.email=u.email;form.role=u.role;
    form.password='';form.password_confirmation='';
}

function cancelEdit(){
    editId.value=null;
    form.name='';form.email='';form.password='';form.password_confirmation='';form.role='user';
}

async function save(){
    try{
        const payload={...form};
        if(editId.value&&!payload.password){delete payload.password;delete payload.password_confirmation;}
        if(editId.value)await api.put(`/users/${editId.value}`,payload);
        else await api.post('/users',payload);
        swalSuccess('User berhasil disimpan!');cancelEdit();load();
    }catch(e){swalError(e.response?.data?.message||'Gagal');}
}

async function destroy(id){
    if(!await swalConfirm('Hapus user ini?'))return;
    try{await api.delete(`/users/${id}`);swalSuccess('User dihapus!');load();}catch(e){swalError(e.response?.data?.message||'Gagal');}
}

async function openMenuAccess(u){
    selectedUser.value=u;
    expandedMenus.value=new Set();
    try{
        const[menusRes,accessRes]=await Promise.all([api.get('/users/menus/list'),api.get(`/users/${u.id}/menu-access`)]);
        allMenus.value=menusRes.data.menus||menusRes.data;
        allSubMenus.value=menusRes.data.subMenus||{};
        selectedMenus.value=accessRes.data.menus||[];
        selectedSubMenus.value=accessRes.data.subMenus||{};
    }catch(e){allMenus.value={};allSubMenus.value={};selectedMenus.value=[];selectedSubMenus.value={};}
    showMenuModal.value=true;
}

function toggleMenu(key){
    const idx=selectedMenus.value.indexOf(key);
    if(idx>=0){
        selectedMenus.value.splice(idx,1);
        delete selectedSubMenus.value[key];
    }else{
        selectedMenus.value.push(key);
    }
}

function toggleExpand(key){
    const s=new Set(expandedMenus.value);
    if(s.has(key))s.delete(key);else s.add(key);
    expandedMenus.value=s;
}

function isSubChecked(menuKey,subKey){
    return selectedSubMenus.value[menuKey]?.includes(subKey)||false;
}

function isSubMenuSelectionActive(menuKey){
    return selectedSubMenus.value[menuKey]?.length>0;
}

function toggleSubMenu(menuKey,subKey){
    if(!selectedMenus.value.includes(menuKey))selectedMenus.value.push(menuKey);
    if(!selectedSubMenus.value[menuKey])selectedSubMenus.value[menuKey]=[];
    const arr=selectedSubMenus.value[menuKey];
    const idx=arr.indexOf(subKey);
    if(idx>=0)arr.splice(idx,1);else arr.push(subKey);
}

function selectAllMenus(){
    selectedMenus.value=Object.keys(allMenus.value);
    selectedSubMenus.value={};
}

async function saveMenuAccess(){
    try{
        const payload={
            menus:selectedMenus.value,
            subMenus:{},
        };
        for(const menu of selectedMenus.value){
            if(selectedSubMenus.value[menu]?.length){
                payload.subMenus[menu]=selectedSubMenus.value[menu];
            }
        }
        await api.put(`/users/${selectedUser.value.id}/menu-access`,payload);
        swalSuccess('Menu access berhasil diupdate!');
        showMenuModal.value=false;load();
    }catch(e){swalError(e.response?.data?.message||'Gagal');}
}

onMounted(load);
</script>

<style scoped>
.um-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:20px;box-shadow:0 4px 12px rgba(15,23,42,.06)}
.um-title{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:16px}
.um-label{display:block;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:#64748b;margin-bottom:6px}
.um-input{width:100%;padding:9px 14px;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;outline:none;font-family:'Quicksand',sans-serif;transition:all .2s}
.um-input:focus{border-color:#60a5fa;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.um-btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;transition:all .15s;font-family:'Quicksand',sans-serif}
.um-btn:hover{transform:translateY(-1px)}
.um-btn-blue{background:linear-gradient(135deg,#2563eb,#3b82f6);color:#fff}
.um-btn-gray{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0}
.um-table{width:100%;border-collapse:collapse;font-size:13px}
.um-table th{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.05em;border-bottom:2px solid #e2e8f0;padding:10px 14px;text-align:left;white-space:nowrap}
.um-table td{padding:10px 14px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
.um-table tr:hover td{background:#f8fafc}
.fw-semibold{font-weight:600}
.um-badge{display:inline-block;font-size:11px;font-weight:600;padding:3px 10px;border-radius:999px}
.um-badge-blue{background:#dbeafe;color:#2563eb}
.um-badge-gray{background:#f1f5f9;color:#64748b}
.um-action{width:30px;height:30px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:all .15s}
.um-action:hover{transform:translateY(-1px)}
.um-action-edit{background:#dbeafe;color:#2563eb}
.um-action-menu{background:#e0e7ff;color:#4f46e5}
.um-action-delete{background:#fee2e2;color:#dc2626}
.um-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);z-index:10000;display:flex;align-items:center;justify-content:center;padding:1rem}
.um-modal{background:#fff;border-radius:16px;box-shadow:0 24px 48px rgba(2,6,23,.25);width:100%;max-width:500px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden}
.um-modal-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0}
.um-modal-header h5{font-size:15px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center}
.um-modal-close{background:none;border:none;font-size:22px;color:#94a3b8;cursor:pointer;padding:4px 8px;border-radius:6px}
.um-modal-close:hover{background:#f1f5f9}
.um-modal-body{padding:20px;overflow-y:auto;flex:1}
.um-modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:12px 20px;border-top:1px solid #e2e8f0}
.um-menu-item{display:flex;align-items:flex-start;gap:12px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;transition:all .15s}
.um-menu-item:hover{border-color:#93c5fd;background:#f0f7ff}
.um-menu-expanded{border-color:#93c5fd;background:#f0f7ff;border-bottom-left-radius:0;border-bottom-right-radius:0}
.um-menu-group{border-radius:10px}
.um-menu-info{flex:1;display:flex;align-items:flex-start}
.um-expand-btn{width:28px;height:28px;border-radius:6px;border:none;background:#f1f5f9;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:10px;transition:all .2s;flex-shrink:0}
.um-expand-btn:hover{background:#e2e8f0;color:#334155}
.um-expand-open{transform:rotate(180deg)}
.um-submenu-list{border:1px solid #93c5fd;border-top:none;background:#f8fafc;padding:10px 14px 10px 44px;border-bottom-left-radius:10px;border-bottom-right-radius:10px;display:flex;flex-direction:column;gap:6px}
.um-submenu-item{display:flex;align-items:center;gap:8px;padding:4px 0;cursor:pointer}
.um-submenu-item:hover span{color:#2563eb}
.um-checkbox{width:18px;height:18px;accent-color:#2563eb;margin-top:2px;cursor:pointer}
.um-checkbox-sm{width:15px;height:15px;margin-top:0}
.um-submenu-hint{font-size:10px;color:#94a3b8;padding:4px 0 0;display:flex;align-items:center}
</style>
