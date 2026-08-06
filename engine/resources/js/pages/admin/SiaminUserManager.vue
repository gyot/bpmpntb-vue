<template>
<div>
    <div class="um-card mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="um-title" style="margin-bottom:0">Manajemen User SIAMIN</h3>
            <button @click="load" class="um-btn um-btn-gray"><i class="fas fa-sync-alt mr-1"></i>Refresh</button>
        </div>
        <div class="flex flex-col md:flex-row gap-3 mb-4">
            <div class="flex-1 relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input v-model="search" type="text" placeholder="Cari nama, email, atau NIP..." class="um-input" style="padding-left:2.2rem">
            </div>
            <select v-model="filter" class="um-input" style="width:auto">
                <option value="all">Semua User</option>
                <option value="registered">Sudah Terdaftar</option>
                <option value="unregistered">Belum Terdaftar</option>
            </select>
        </div>
        <div class="overflow-x-auto">
            <table class="um-table">
                <thead><tr>
                    <th>#</th><th>Nama</th><th>Email</th><th>NIP</th><th>Role SIAMIN</th><th>Status</th><th>Role Lokal</th><th>Aksi</th>
                </tr></thead>
                <tbody>
                    <tr v-for="u in filteredUsers" :key="u.id_user">
                        <td style="color:#94a3b8;font-weight:600;">{{ u.id_user }}</td>
                        <td class="fw-semibold" style="color:#0f172a;">{{ u.pegawai?.nama || u.email || '-' }}</td>
                        <td style="color:#64748b;">{{ u.email || '-' }}</td>
                        <td style="color:#64748b;font-size:12px;">{{ u.pegawai?.nip || '-' }}</td>
                        <td><span class="um-badge um-badge-gray">{{ u.role }}</span></td>
                        <td><span class="um-badge" :class="u.status==='aktif'?'um-badge-green':'um-badge-red'">{{ u.status }}</span></td>
                        <td>
                            <span v-if="u.local_role" class="um-badge um-badge-blue">{{ u.local_role }}</span>
                            <span v-else class="um-badge um-badge-yellow">Belum Terdaftar</span>
                        </td>
                        <td>
                            <div class="flex gap-1">
                                <button @click="openSetRole(u)" class="um-action um-action-edit" :title="u.local_role?'Edit Role':'Set Role'">
                                    <i class="fas" :class="u.local_role?'fa-edit':'fa-plus'"></i>
                                </button>
                                <button v-if="u.local_role" @click="revokeAccess(u)" class="um-action um-action-delete" title="Cabut Akses">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="!loading && !filteredUsers.length" class="text-center py-8 text-gray-400">
            {{ search || filter !== 'all' ? 'Tidak ada user yang cocok' : 'Belum ada data user' }}
        </div>
        <div v-if="loading" class="text-center py-8"><i class="fas fa-spinner fa-spin text-xl text-gray-400"></i></div>
    </div>

    <!-- Modal Set Role -->
    <div v-if="showModal" class="um-modal-overlay" @click.self="showModal=false">
        <div class="um-modal">
            <div class="um-modal-header">
                <h5><i class="fas fa-user-gear mr-2" style="color:#2563eb;"></i>{{ selectedUser?.local_role ? 'Edit' : 'Set' }} Role User</h5>
                <button @click="showModal=false" class="um-modal-close">&times;</button>
            </div>
            <div class="um-modal-body">
                <div class="space-y-3">
                    <div><label class="um-label">Nama</label><div class="um-readonly">{{ selectedUser?.pegawai?.nama || '-' }}</div></div>
                    <div><label class="um-label">Email</label><div class="um-readonly">{{ selectedUser?.email }}</div></div>
                    <div><label class="um-label">Role SIAMIN</label><div class="um-readonly">{{ selectedUser?.role }}</div></div>
                    <div>
                        <label class="um-label">Role Lokal</label>
                        <select v-model="roleForm.role" class="um-input">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="um-modal-footer">
                <button @click="showModal=false" class="um-btn um-btn-gray">Batal</button>
                <button @click="saveRole" class="um-btn um-btn-blue"><i class="fas fa-save mr-1"></i> Simpan</button>
            </div>
        </div>
    </div>
</div>
</template>

<script setup>
import {ref,reactive,computed,onMounted} from 'vue';
import api from '@/bootstrap.js';
import {swalConfirm,swalError,swalSuccess} from '@/swal.js';

const users=ref([]);
const loading=ref(false);
const search=ref('');
const filter=ref('all');
const showModal=ref(false);
const selectedUser=ref(null);
const roleForm=reactive({role:'user'});

const filteredUsers=computed(()=>{
    let list=users.value;
    if(filter.value==='registered')list=list.filter(u=>u.local_role);
    if(filter.value==='unregistered')list=list.filter(u=>!u.local_role);
    if(search.value.trim()){
        const q=search.value.toLowerCase();
        list=list.filter(u=>
            (u.pegawai?.nama||'').toLowerCase().includes(q)||
            (u.email||'').toLowerCase().includes(q)||
            (u.pegawai?.nip||'').toLowerCase().includes(q)
        );
    }
    return list;
});

async function load(){
    loading.value=true;
    try{const{data}=await api.get('/siamin/users');users.value=data;}catch(e){swalError('Gagal memuat data user SIAMIN');}
    loading.value=false;
}

function openSetRole(u){
    selectedUser.value=u;
    roleForm.role=u.local_role||'user';
    showModal.value=true;
}

async function saveRole(){
    try{
        await api.post('/siamin/set-role',{
            id_user:selectedUser.value.id_user,
            role:roleForm.role,
            name:selectedUser.value.pegawai?.nama||null,
            email:selectedUser.value.email||null,
        });
        swalSuccess('Role berhasil disimpan!');
        showModal.value=false;
        load();
    }catch(e){swalError(e.response?.data?.message||'Gagal menyimpan role');}
}

async function revokeAccess(u){
    if(!await swalConfirm(`Cabut akses user "${u.pegawai?.nama||u.email}"?`))return;
    try{await api.delete(`/siamin/revoke-role/${u.id_user}`);swalSuccess('Akses user berhasil dicabut!');load();}catch(e){swalError('Gagal mencabut akses');}
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
.um-badge-green{background:#dcfce7;color:#16a34a}
.um-badge-red{background:#fee2e2;color:#dc2626}
.um-badge-yellow{background:#fef9c3;color:#ca8a04}
.um-action{width:30px;height:30px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:12px;transition:all .15s}
.um-action:hover{transform:translateY(-1px)}
.um-action-edit{background:#dbeafe;color:#2563eb}
.um-action-delete{background:#fee2e2;color:#dc2626}
.um-modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);z-index:10000;display:flex;align-items:center;justify-content:center;padding:1rem}
.um-modal{background:#fff;border-radius:16px;box-shadow:0 24px 48px rgba(2,6,23,.25);width:100%;max-width:440px;max-height:90vh;display:flex;flex-direction:column;overflow:hidden}
.um-modal-header{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e2e8f0}
.um-modal-header h5{font-size:15px;font-weight:700;color:#0f172a;margin:0;display:flex;align-items:center}
.um-modal-close{background:none;border:none;font-size:22px;color:#94a3b8;cursor:pointer;padding:4px 8px;border-radius:6px}
.um-modal-close:hover{background:#f1f5f9}
.um-modal-body{padding:20px;overflow-y:auto;flex:1}
.um-modal-footer{display:flex;justify-content:flex-end;gap:8px;padding:12px 20px;border-top:1px solid #e2e8f0}
.um-readonly{padding:9px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;font-size:13px;color:#334155}
</style>
