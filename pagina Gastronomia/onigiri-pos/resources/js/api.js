/**
 * api.js — Cliente HTTP centralizado para la SPA.
 * Configura axios con el CSRF token de Laravel y la base URL de la API.
 */
import axios from 'axios';

const api = axios.create({
    baseURL: '/api',
    headers: {
        'Content-Type': 'application/json',
        'Accept':        'application/json',
        'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content,
    },
});

// Interceptor: mostrar errores en consola durante desarrollo
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (import.meta.env.DEV) {
            console.error('[API Error]', error.response?.data || error.message);
        }
        return Promise.reject(error);
    }
);

// ─────────────────────────────────────────────────────────────────────────────
// CATALOG
// ─────────────────────────────────────────────────────────────────────────────
export const fetchCatalog   = ()  => api.get('/catalog').then(r => Array.isArray(r.data?.data) ? r.data.data : []);
export const fetchFeatured  = ()  => api.get('/catalog/featured').then(r => Array.isArray(r.data?.data) ? r.data.data : []);
export const fetchStock     = (id) => api.get(`/catalog/${id}/stock`).then(r => r.data);

// ─────────────────────────────────────────────────────────────────────────────
// CART
// ─────────────────────────────────────────────────────────────────────────────
export const validateCart   = (items) => api.post('/cart/validate', { items });
export const getProductStock = (id)   => api.get(`/cart/stock/${id}`).then(r => r.data);

// ─────────────────────────────────────────────────────────────────────────────
// ORDERS
// ─────────────────────────────────────────────────────────────────────────────
export const createOrder    = (data)          => api.post('/orders', data).then(r => r.data);
export const confirmOrder   = (id)            => api.post(`/orders/${id}/confirm`).then(r => r.data);
export const getOrders      = (view = 'pos')  => api.get(`/orders?view=${view}`).then(r => Array.isArray(r.data?.data) ? r.data.data : []);
export const getOrder       = (id)            => api.get(`/orders/${id}`).then(r => r.data.data);
export const updateStatus   = (id, status)    => api.patch(`/orders/${id}/status`, { status }).then(r => r.data);

// ─────────────────────────────────────────────────────────────────────────────
// INVOICES
// ─────────────────────────────────────────────────────────────────────────────
export const getInvoice     = (id) => api.get(`/invoices/${id}`).then(r => r.data.data);
export const retryInvoice   = (id) => api.post(`/invoices/${id}/retry`).then(r => r.data);

export default api;
