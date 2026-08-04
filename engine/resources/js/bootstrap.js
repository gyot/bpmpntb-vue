import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },

    transformResponse: [function (data) {
        if (typeof data === 'string') {
            data = data.replace(/^\uFEFF/, '').trim();
            try { return JSON.parse(data); } catch (e) { return data; }
        }
        return data;
    }],
});

let csrfInitialized = false;

async function ensureCsrf() {
    if (!csrfInitialized) {
        try {
            await axios.get('/sanctum/csrf-cookie', {  });
            csrfInitialized = true;
        } catch (e) {
            console.error('CSRF cookie failed:', e);
        }
    }
}

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        config.headers['X-CSRF-TOKEN'] = csrfToken;
    }
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            if (window.location.pathname.startsWith('/admin')) {
                window.location.href = '/login';
            }
        }
        if (error.response?.status === 403) {
            if (typeof window !== 'undefined') {
                const msg = error.response?.data?.message || 'Anda tidak memiliki akses untuk melakukan aksi ini';
                console.error('[403] status:', error.response?.status);
                console.error('[403] statusText:', error.response?.statusText);
                console.error('[403] headers:', JSON.stringify(error.response?.headers));
                console.error('[403] message:', msg);
                console.error('[403] data:', error.response?.data);
                console.error('[403] config url:', error.config?.url);
                console.error('[403] config method:', error.config?.method);
                import('@/swal.js').then(m => m.swalError(msg));
            }
        }
        return Promise.reject(error);
    }
);

export { ensureCsrf };
export default api;
