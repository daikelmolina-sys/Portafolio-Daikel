/**
 * app.jsx — Entry point de la SPA React.
 *
 * Estructura:
 *  CartProvider → BrowserRouter → Routes
 *    /        → StorePage  (E-commerce para clientes)
 *    /pos     → PosPage    (Módulo cajero — Kiosk Mode)
 *
 * Componentes globales: Header, CartDrawer, Toast
 */
import { StrictMode, useState } from 'react';
import { createRoot } from 'react-dom/client';
import { BrowserRouter, Routes, Route } from 'react-router-dom';

import { CartProvider } from './contexts/CartContext';
import Header     from './components/Header';
import CartDrawer from './components/CartDrawer';
import Toast      from './components/Toast';
import StorePage  from './pages/StorePage';
import PosPage    from './pages/PosPage';

function App() {
    const [cartOpen, setCartOpen] = useState(false);

    return (
        <BrowserRouter>
            <CartProvider>
                {/* Barra de navegación global */}
                <Header onCartOpen={() => setCartOpen(true)} />

                {/* Carrito deslizante (siempre montado, visible según estado) */}
                <CartDrawer isOpen={cartOpen} onClose={() => setCartOpen(false)} />

                {/* Toasts de feedback */}
                <Toast />

                {/* Rutas principales */}
                <Routes>
                    <Route path="/"    element={<StorePage />} />
                    <Route path="/pos" element={<PosPage />} />
                    <Route path="*"    element={
                        <div style={{
                            display: 'flex', flexDirection: 'column',
                            alignItems: 'center', justifyContent: 'center',
                            minHeight: '100vh', gap: 'var(--space-4)',
                            color: 'var(--color-text-muted)',
                        }}>
                            <div style={{ fontSize: '5rem' }}>404</div>
                            <h2 style={{ fontSize: 'var(--text-2xl)', fontWeight: 800 }}>Página no encontrada</h2>
                            <a href="/" className="btn btn-primary">← Volver al menú</a>
                        </div>
                    } />
                </Routes>
            </CartProvider>
        </BrowserRouter>
    );
}

createRoot(document.getElementById('root')).render(
    <StrictMode>
        <App />
    </StrictMode>
);
