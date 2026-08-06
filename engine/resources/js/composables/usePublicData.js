import { ref, readonly } from 'vue';
import api from '@/bootstrap.js';

const setting = ref(null);
const beranda = ref(null);
const layanan = ref([]);
const visitorStats = ref(null);
const loading = ref(false);
let fetchPromise = null;

export function usePublicData() {
    async function fetchAll() {
        if (fetchPromise) return fetchPromise;
        fetchPromise = (async () => {
            loading.value = true;
            try {
                const results = await Promise.allSettled([
                    api.get('/settings'),
                    api.get('/beranda'),
                    api.get('/layanans-public'),
                    api.get('/visitor-stats'),
                ]);
                const [s, b, l, v] = results;
                if (s.status === 'fulfilled') setting.value = s.value.data;
                if (b.status === 'fulfilled') beranda.value = b.value.data;
                if (l.status === 'fulfilled') layanan.value = l.value.data || [];
                if (v.status === 'fulfilled') visitorStats.value = v.value.data;
            } catch (e) {
                console.error('[usePublicData]', e);
            } finally {
                loading.value = false;
            }
        })();
        return fetchPromise;
    }

    return {
        setting: readonly(setting),
        beranda: readonly(beranda),
        layanan: readonly(layanan),
        visitorStats: readonly(visitorStats),
        loading: readonly(loading),
        fetchAll,
    };
}
