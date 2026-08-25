import { useEffect } from 'react';
import { useCart } from '../contexts/CartContext';

/**
 * Toast — Notificación flotante para feedback del carrito.
 * Aparece automáticamente cuando hay un toast en el CartContext.
 */
export default function Toast() {
    const { toast } = useCart();

    if (!toast) return null;

    const colors = {
        error:   { bg: 'var(--color-danger)',  icon: '⚠️' },
        success: { bg: 'var(--color-success)', icon: '✓' },
        info:    { bg: 'var(--color-info)',    icon: 'ℹ️' },
    };

    const { bg, icon } = colors[toast.type] || colors.info;

    return (
        <div
            role="alert"
            style={{
                position: 'fixed',
                bottom: 'var(--space-6)',
                left: '50%',
                transform: 'translateX(-50%)',
                zIndex: 500,
                animation: 'slideUp 300ms cubic-bezier(0.34, 1.56, 0.64, 1)',
                display: 'flex', alignItems: 'center', gap: 'var(--space-3)',
                background: 'var(--color-bg-elevated)',
                border: `1px solid ${bg}`,
                borderLeft: `4px solid ${bg}`,
                borderRadius: 'var(--radius-md)',
                padding: 'var(--space-3) var(--space-5)',
                boxShadow: 'var(--shadow-lg)',
                maxWidth: '90vw',
                whiteSpace: 'nowrap',
            }}
        >
            <span style={{ fontSize: '1.1rem' }}>{icon}</span>
            <span style={{ fontSize: 'var(--text-sm)', color: 'var(--color-text-primary)', fontWeight: 500 }}>
                {toast.message}
            </span>
        </div>
    );
}
