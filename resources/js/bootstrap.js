import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
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
            await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
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
                import('@/swal.js').then(m => m.swalError('Anda tidak memiliki akses untuk melakukan aksi ini'));
            }
        }
        return Promise.reject(error);
    }
);

export { ensureCsrf };
export default api;
