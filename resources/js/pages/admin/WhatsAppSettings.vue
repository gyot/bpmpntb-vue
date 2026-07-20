<template>
<div>
    <h2 class="text-2xl font-bold mb-2" style="color:var(--color-text-primary)">Pengaturan WhatsApp Gateway</h2>
    <p class="text-sm mb-6" style="color:var(--color-text-secondary)">Konfigurasi API WhatsApp untuk pengaduan dan notifikasi chatbot INTAN</p>
    <div v-if="success" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6 flex items-center"><i class="fas fa-check-circle mr-2"></i>{{ success }}</div>
    <form @submit.prevent="save" class="space-y-6">
        <div class="card p-6">
            <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-server" style="color:var(--color-primary)"></i>Koneksi API</h3>
            <div>
                <label class="input-label">Domain WhatsApp API</label>
                <input v-model="form.wa_domain" type="url" class="input-field" placeholder="https://wapi1.gdoank.my.id" required>
                <p class="text-xs mt-1" style="color:var(--color-text-secondary)">URL base dari WhatsApp gateway API</p>
            </div>
        </div>
        <div class="card p-6">
            <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-graduation-cap" style="color:var(--color-accent)"></i>Pengaduan SPMB</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="input-label">Nomor WhatsApp SPMB</label>
                    <input v-model="form.wa_spmb_number" type="text" class="input-field" placeholder="6281805297478@c.us" required>
                    <p class="text-xs mt-1" style="color:var(--color-text-secondary)">Format: 628xxx@c.us</p>
                </div>
                <div>
                    <label class="input-label">Status</label>
                    <select v-model="form.wa_spmb_enabled" class="input-field"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
                </div>
            </div>
            <div class="mt-3 p-3 rounded-lg bg-blue-50 border border-blue-100">
                <p class="text-xs text-blue-700"><i class="fas fa-info-circle mr-1"></i>Pengaduan SPMB akan dikirim ke nomor ini via <code>/api/whatsapp/send-message</code></p>
            </div>
        </div>
        <div class="card p-6">
            <h3 class="text-sm font-semibold mb-5 flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-users" style="color:var(--color-secondary)"></i>Group WhatsApp</h3>
            <div>
                <label class="input-label">Nama Group WhatsApp</label>
                <input v-model="form.wa_group_name" type="text" class="input-field" placeholder="GROUP ULT BPMP NTB 2024" required>
                <p class="text-xs mt-1" style="color:var(--color-text-secondary)">Pengaduan umum dikirim ke group ini via <code>/send-group</code></p>
            </div>
        </div>
        <button type="submit" :disabled="saving" class="btn-primary"><i :class="saving?'fa-spinner fa-spin':'fa-save'" class="fas mr-2"></i>{{ saving?"Menyimpan...":"Simpan Pengaturan" }}</button>
    </form>
    <div class="card p-6 mt-6">
        <h3 class="text-sm font-semibold mb-4" style="color:var(--color-text-primary)">Cara Kerja</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                <div class="flex items-center gap-2 mb-2"><div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center"><i class="fas fa-comments text-sm text-blue-600"></i></div><span class="text-sm font-bold" style="color:var(--color-text-primary)">Pertanyaan Umum</span></div>
                <p class="text-xs" style="color:var(--color-text-secondary)">User pilih Hubungi Admin ketik pesan kirim ke Group WhatsApp</p>
            </div>
            <div class="p-4 rounded-xl bg-gray-50 border border-gray-100">
                <div class="flex items-center gap-2 mb-2"><div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center"><i class="fas fa-graduation-cap text-sm text-amber-600"></i></div><span class="text-sm font-bold" style="color:var(--color-text-primary)">Pengaduan SPMB</span></div>
                <p class="text-xs" style="color:var(--color-text-secondary)">User pilih Pengaduan SPMB ketik pengaduan kirim langsung ke Nomor WhatsApp SPMB</p>
            </div>
        </div>
    </div>
</div>
</template>
<script setup>
import {ref,reactive,onMounted} from "vue";
import api from "@/bootstrap.js";
import {swalSuccess,swalError} from "@/swal.js";
const success=ref("");const saving=ref(false);
const form=reactive({wa_domain:"",wa_spmb_number:"",wa_group_name:"",wa_spmb_enabled:"1"});
onMounted(async()=>{try{const{data}=await api.get("/chatbot/admin/whatsapp-settings");Object.assign(form,data);}catch(e){}});
async function save(){saving.value=true;success.value="";try{await api.post("/chatbot/admin/whatsapp-settings",form);success.value="Pengaturan WhatsApp berhasil disimpan!";}catch(e){swalError("Gagal menyimpan");}saving.value=false;}
</script>
<style scoped>.input-label{@apply block text-xs font-semibold uppercase tracking-wider mb-1.5;color:var(--color-text-secondary);}</style>
