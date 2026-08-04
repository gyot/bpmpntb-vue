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

    // Convert PUT/DELETE/PATCH to POST with _method spoofing
    // This bypasses hosting firewalls that block non-POST methods
    const method = (config.method || '').toLowerCase();
    if (method === 'put' || method === 'delete' || method === 'patch') {
        const upperMethod = method.toUpperCase();
        config.method = 'post';

        if (config.data instanceof FormData) {
            config.data.append('_method', upperMethod);
        } else if (typeof config.data === 'object' && config.data !== null) {
            config.data._method = upperMethod;
        } else {
            config.params = { ...config.params, _method: upperMethod };
        }
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
        return Promise.reject(error);
    }
);

export { ensureCsrf };
export default api;
