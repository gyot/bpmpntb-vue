<template>
    <div>
        <div class="flex items-center justify-between mb-8">
            <div>
                <h2 class="text-2xl font-bold" style="color:var(--color-text-primary)">Pengaturan Tema Website</h2>
                <p class="text-sm mt-1" style="color:var(--color-text-secondary)">Kustomisasi warna dan tampilan website secara real-time</p>
            </div>
            <div class="flex items-center gap-3">
                <button @click="cancelChanges" :disabled="!hasChanges" class="btn-ghost border border-gray-200 disabled:opacity-40 disabled:cursor-not-allowed"><i class="fas fa-undo mr-2"></i>Batalkan</button>
                <button @click="resetToDefault" class="btn-ghost border border-red-200 text-red-600 hover:bg-red-50"><i class="fas fa-redo mr-2"></i>Reset Default</button>
                <button @click="saveTheme" :disabled="!hasChanges || saving" class="btn-primary disabled:opacity-40 disabled:cursor-not-allowed"><i :class="saving?'fa-spinner fa-spin':'fa-save'" class="fas mr-2"></i>{{ saving?'Menyimpan...':'Simpan Tema' }}</button>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-1 space-y-6">
                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100"><h3 class="text-sm font-bold flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-palette text-sm" style="color:var(--color-accent)"></i>Warna Utama</h3></div>
                    <div class="p-6 space-y-5">
                        <div v-for="item in mainColors" :key="item.key">
                            <label class="input-label">{{ item.label }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" :value="localTheme[item.key]" @input="e => { localTheme[item.key] = e.target.value; onColorChange(); }" class="w-10 h-10 rounded-xl cursor-pointer border-2 border-gray-200 p-0.5 hover:border-gray-400 transition">
                                <input type="text" :value="localTheme[item.key]" @input="e => onHexInput(item.key, e.target.value)" class="input-field font-mono flex-1" maxlength="9" placeholder="#000000">
                                <div class="w-10 h-10 rounded-xl border-2 border-gray-100 shadow-inner flex-shrink-0" :style="{background: localTheme[item.key]}"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100"><h3 class="text-sm font-bold flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-layer-group text-sm" style="color:var(--color-accent)"></i>Background & Text</h3></div>
                    <div class="p-6 space-y-5">
                        <div v-for="item in bgColors" :key="item.key">
                            <label class="input-label">{{ item.label }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" :value="localTheme[item.key]" @input="e => { localTheme[item.key] = e.target.value; onColorChange(); }" class="w-10 h-10 rounded-xl cursor-pointer border-2 border-gray-200 p-0.5 hover:border-gray-400 transition">
                                <input type="text" :value="localTheme[item.key]" @input="e => onHexInput(item.key, e.target.value)" class="input-field font-mono flex-1" maxlength="9" placeholder="#000000">
                                <div class="w-10 h-10 rounded-xl border-2 border-gray-100 shadow-inner flex-shrink-0" :style="{background: localTheme[item.key]}"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100"><h3 class="text-sm font-bold flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-columns text-sm" style="color:var(--color-accent)"></i>Navbar & Sidebar</h3></div>
                    <div class="p-6 space-y-5">
                        <div v-for="item in navColors" :key="item.key">
                            <label class="input-label">{{ item.label }}</label>
                            <div class="flex items-center gap-3">
                                <input type="color" :value="localTheme[item.key]" @input="e => { localTheme[item.key] = e.target.value; onColorChange(); }" class="w-10 h-10 rounded-xl cursor-pointer border-2 border-gray-200 p-0.5 hover:border-gray-400 transition">
                                <input type="text" :value="localTheme[item.key]" @input="e => onHexInput(item.key, e.target.value)" class="input-field font-mono flex-1" maxlength="9" placeholder="#000000">
                                <div class="w-10 h-10 rounded-xl border-2 border-gray-100 shadow-inner flex-shrink-0" :style="{background: localTheme[item.key]}"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-2">
                <div class="card overflow-hidden sticky top-24">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-bold flex items-center gap-2" style="color:var(--color-text-primary)"><i class="fas fa-eye text-sm" style="color:var(--color-accent)"></i>Live Preview</h3>
                        <span class="badge badge-primary text-[10px]">Real-time</span>
                    </div>
                    <div class="p-6">
                        <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm" :style="{background:localTheme.background_color, color:localTheme.text_primary_color}">
                            <div class="h-11 flex items-center justify-between px-4" :style="{background:localTheme.navbar_bg_color, color:localTheme.navbar_text_color}">
                                <div class="flex items-center gap-2">
                                    <div class="w-5 h-5 rounded" style="background:rgba(255,255,255,0.2)"></div>
                                    <span class="text-xs font-bold">BPMP NTB</span>
                                </div>
                                <div class="flex gap-3 text-[10px] opacity-70">
                                    <span>Beranda</span><span>Profil</span><span>Layanan</span>
                                </div>
                            </div>
                            <div class="flex" style="min-height:300px">
                                <div class="w-32 p-3 flex-shrink-0" :style="{background:localTheme.sidebar_bg_color, color:localTheme.sidebar_text_color}">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-[11px] font-medium" :style="{background:localTheme.primary_color, color:'#fff'}"><i class="fas fa-th-large text-[10px]"></i>Dashboard</div>
                                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-[11px] opacity-60"><i class="fas fa-newspaper text-[10px]"></i>Berita</div>
                                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg text-[11px] opacity-60"><i class="fas fa-cog text-[10px]"></i>Settings</div>
                                    </div>
                                </div>
                                <div class="flex-1 p-4" :style="{background:localTheme.background_color}">
                                    <h4 class="text-sm font-bold mb-3" :style="{color:localTheme.text_primary_color}">Dashboard</h4>
                                    <div class="grid grid-cols-3 gap-3 mb-4">
                                        <div class="p-3 rounded-xl border border-gray-100" :style="{background:localTheme.surface_color}">
                                            <div class="text-[10px] mb-1" :style="{color:localTheme.text_secondary_color}">Total Berita</div>
                                            <div class="text-lg font-bold" :style="{color:localTheme.primary_color}">24</div>
                                        </div>
                                        <div class="p-3 rounded-xl border border-gray-100" :style="{background:localTheme.surface_color}">
                                            <div class="text-[10px] mb-1" :style="{color:localTheme.text_secondary_color}">Artikel</div>
                                            <div class="text-lg font-bold" :style="{color:localTheme.secondary_color}">18</div>
                                        </div>
                                        <div class="p-3 rounded-xl border border-gray-100" :style="{background:localTheme.surface_color}">
                                            <div class="text-[10px] mb-1" :style="{color:localTheme.text_secondary_color}">Pengunjung</div>
                                            <div class="text-lg font-bold" :style="{color:localTheme.accent_color}">1.2k</div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2 mb-4">
                                        <span class="px-3 py-1.5 rounded-full text-[11px] font-bold text-white" :style="{background:localTheme.primary_color}">Primary</span>
                                        <span class="px-3 py-1.5 rounded-full text-[11px] font-bold text-white" :style="{background:localTheme.secondary_color}">Secondary</span>
                                        <span class="px-3 py-1.5 rounded-full text-[11px] font-bold text-white" :style="{background:localTheme.accent_color}">Accent</span>
                                    </div>
                                    <div class="rounded-xl border border-gray-100 p-3 mb-3" :style="{background:localTheme.surface_color}">
                                        <div class="text-[11px] font-bold mb-1" :style="{color:localTheme.text_primary_color}">Judul Berita Terbaru</div>
                                        <div class="text-[10px]" :style="{color:localTheme.text_secondary_color}">Lorem ipsum dolor sit amet, consectetur adipiscing elit...</div>
                                    </div>
                                    <div class="flex gap-2 mb-3">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold text-white" :style="{background:localTheme.primary_color}">Publish</span>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-bold text-white" :style="{background:localTheme.accent_color}">Berita</span>
                                    </div>
                                    <div class="p-2 rounded-xl text-[10px] border-l-4" :style="{background:localTheme.accent_color+'15', borderLeftColor:localTheme.accent_color, color:localTheme.text_primary_color}">
                                        <i class="fas fa-info-circle mr-1" :style="{color:localTheme.accent_color}"></i>Contoh alert dengan accent color
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '@/bootstrap.js';
import { applyTheme, resetTheme, DEFAULTS } from '@/theme.js';
import { swalSuccess, swalError } from '@/swal.js';

const saving = ref(false);
const originalTheme = ref({});
const localTheme = reactive({ ...DEFAULTS });
const hasChanges = computed(() => JSON.stringify(localTheme) !== JSON.stringify(originalTheme.value));

const mainColors = [
    { key: 'primary_color', label: 'Primary Color' },
    { key: 'secondary_color', label: 'Secondary Color' },
    { key: 'accent_color', label: 'Accent Color' },
];
const bgColors = [
    { key: 'background_color', label: 'Background' },
    { key: 'surface_color', label: 'Surface / Card' },
    { key: 'text_primary_color', label: 'Text Primary' },
    { key: 'text_secondary_color', label: 'Text Secondary' },
];
const navColors = [
    { key: 'navbar_bg_color', label: 'Navbar Background' },
    { key: 'navbar_text_color', label: 'Navbar Text' },
    { key: 'sidebar_bg_color', label: 'Sidebar Background' },
    { key: 'sidebar_text_color', label: 'Sidebar Text' },
];

function onColorChange() { applyTheme({ ...localTheme }); }

function onHexInput(key, val) {
    if (/^#[0-9A-Fa-f]{3,8}$/.test(val)) {
        localTheme[key] = val;
        onColorChange();
    }
}

function cancelChanges() { Object.assign(localTheme, { ...originalTheme.value }); onColorChange(); }
function resetToDefault() { Object.assign(localTheme, { ...DEFAULTS }); onColorChange(); }

async function saveTheme() {
    saving.value = true;
    try {
        const fd = new FormData();
        Object.entries(localTheme).forEach(([k, v]) => fd.append(k, v));
        const { data } = await api.post('/settings-admin', fd, { headers: { 'Content-Type': 'multipart/form-data' } });
        originalTheme.value = { ...localTheme };
        swalSuccess('Tema berhasil disimpan!');
    } catch (e) {
        console.error('Save theme error:', e.response?.data);
        swalError(e.response?.data?.message || JSON.stringify(e.response?.data?.errors) || 'Gagal menyimpan tema');
    }
    saving.value = false;
}

onMounted(async () => {
    try {
        const { data } = await api.get('/settings-admin');
        if (data) {
            const themeKeys = Object.keys(DEFAULTS);
            const loaded = {};
            themeKeys.forEach(k => { loaded[k] = data[k] || DEFAULTS[k]; });
            Object.assign(localTheme, loaded);
            originalTheme.value = { ...loaded };
            applyTheme(loaded);
        }
    } catch (e) { console.error('Load theme error:', e); }
});
</script>
