import { useState, useEffect, useCallback } from 'react';
import { getOrders, updateStatus, getInvoice } from '../api';

/**
 * PosPage — Módulo del Cajero (Kiosk Mode).
 *
 * - Desktop-first (layout en dos columnas)
 * - Lista órdenes activas confirmadas/en preparación
 * - Permite cambiar el estado de cada orden
 * - Muestra el botón de descarga de factura cuando el PDF está listo
 * - Se refresca automáticamente cada 15 segundos
 */
export default function PosPage() {
    const [orders, setOrders]       = useState([]);
    const [loading, setLoading]     = useState(true);
    const [selected, setSelected]   = useState(null);
    const [invoiceData, setInvoice] = useState(null);
    const [updating, setUpdating]   = useState(false);

    const loadOrders = useCallback(async () => {
        try {
            const data = await getOrders('pos');
            setOrders(data);
            // Actualizar la orden seleccionada si sigue en la lista
            if (selected) {
                const updated = data.find(o => o.id === selected.id);
                if (updated) setSelected(updated);
            }
        } catch (err) {
            console.error('Error cargando órdenes:', err);
        } finally {
            setLoading(false);
        }
    }, [selected]);

    // Auto-refresh cada 15 segundos
    useEffect(() => {
        loadOrders();
        const interval = setInterval(loadOrders, 15000);
        return () => clearInterval(interval);
    }, []);

    const handleSelectOrder = async (order) => {
        setSelected(order);
        setInvoice(null);
        // Intentar cargar datos de factura si existe
        if (order.invoice_id) {
            try {
                const inv = await getInvoice(order.invoice_id);
                setInvoice(inv);
            } catch (_) {}
        }
    };

    const handleStatus = async (orderId, newStatus) => {
        setUpdating(true);
        try {
            await updateStatus(orderId, newStatus);
            await loadOrders();
        } finally {
            setUpdating(false);
        }
    };

    const statusColors = {
        confirmed:  { bg: 'rgba(76,140,232,0.1)',  border: '#4c8ce8', text: '#4c8ce8',  label: '✓ Confirmado' },
        preparing:  { bg: 'rgba(232,168,76,0.1)',  border: '#e8a84c', text: '#e8a84c',  label: '🔥 Preparando' },
        ready:      { bg: 'rgba(76,175,107,0.1)',  border: '#4caf6b', text: '#4caf6b',  label: '✅ Listo' },
        delivered:  { bg: 'rgba(160,152,128,0.1)', border: '#a09880', text: '#a09880',  label: '📦 Entregado' },
    };

    const nextStatus = {
        confirmed: 'preparing',
        preparing: 'ready',
        ready:     'delivered',
    };

    const nextStatusLabel = {
        confirmed: '🔥 Marcar en preparación',
        preparing: '✅ Marcar como listo',
        ready:     '📦 Marcar como entregado',
    };

    return (
        <main style={{
            paddingTop: 'var(--header-height)',
            minHeight: '100vh',
            display: 'grid',
            gridTemplateColumns: '360px 1fr',
        }}>

            {/* ─── COLUMNA IZQUIERDA: LISTA DE ÓRDENES ─── */}
            <aside style={{
                borderRight: '1px solid var(--color-border)',
                overflowY: 'auto',
                background: 'var(--color-bg-secondary)',
                height: `calc(100vh - var(--header-height))`,
                position: 'sticky',
                top: 'var(--header-height)',
            }}>
                <div style={{
                    padding: 'var(--space-5) var(--space-5)',
                    borderBottom: '1px solid var(--color-border)',
                    display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                }}>
                    <div>
                        <h1 style={{ fontSize: 'var(--text-lg)', fontWeight: 800 }}>📋 Órdenes Activas</h1>
                        <p style={{ fontSize: 'var(--text-xs)', color: 'var(--color-text-muted)', marginTop: 2 }}>
                            Actualización cada 15s
                        </p>
                    </div>
                    <button
                        onClick={loadOrders}
                        className="btn btn-ghost btn-icon"
                        title="Actualizar"
                        aria-label="Actualizar órdenes"
                    >
                        🔄
                    </button>
                </div>

                {loading ? (
                    <div style={{ padding: 'var(--space-6)', display: 'flex', flexDirection: 'column', gap: 'var(--space-3)' }}>
                        {[...Array(4)].map((_, i) => (
                            <div key={i} className="skeleton" style={{ height: 80, borderRadius: 'var(--radius-md)' }} />
                        ))}
                    </div>
                ) : orders.length === 0 ? (
                    <div style={{ textAlign: 'center', padding: 'var(--space-16)', color: 'var(--color-text-muted)' }}>
                        <div style={{ fontSize: '3rem', marginBottom: 'var(--space-4)' }}>✅</div>
                        <p style={{ fontWeight: 600 }}>Todo al día</p>
                        <p style={{ fontSize: 'var(--text-sm)', marginTop: 'var(--space-2)' }}>No hay órdenes pendientes</p>
                    </div>
                ) : (
                    <div style={{ padding: 'var(--space-3)', display: 'flex', flexDirection: 'column', gap: 'var(--space-2)' }}>
                        {orders.map(order => {
                            const sc = statusColors[order.status] || statusColors.confirmed;
                            const isSelected = selected?.id === order.id;

                            return (
                                <button
                                    key={order.id}
                                    id={`order-${order.id}`}
                                    onClick={() => handleSelectOrder(order)}
                                    style={{
                                        width: '100%', textAlign: 'left', padding: 'var(--space-4)',
                                        borderRadius: 'var(--radius-md)',
                                        border: `1px solid ${isSelected ? 'var(--color-accent)' : 'var(--color-border)'}`,
                                        background: isSelected ? 'var(--color-accent-glow)' : 'var(--color-surface-1)',
                                        cursor: 'pointer', transition: 'all var(--transition-fast)',
                                        fontFamily: 'var(--font-primary)',
                                    }}
                                >
                                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 'var(--space-2)' }}>
                                        <span style={{ fontWeight: 800, fontSize: 'var(--text-sm)', color: isSelected ? 'var(--color-accent-light)' : 'var(--color-text-primary)' }}>
                                            {order.order_number}
                                        </span>
                                        <span style={{
                                            fontSize: '10px', fontWeight: 700, padding: '2px 8px',
                                            borderRadius: 'var(--radius-full)',
                                            background: sc.bg, color: sc.text, border: `1px solid ${sc.border}`,
                                        }}>
                                            {sc.label}
                                        </span>
                                    </div>
                                    <div style={{ fontSize: 'var(--text-sm)', color: 'var(--color-text-secondary)' }}>
                                        {order.customer_name}
                                    </div>
                                    <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 'var(--space-2)' }}>
                                        <span style={{ fontSize: 'var(--text-xs)', color: 'var(--color-text-muted)' }}>
                                            {order.items?.length ?? 0} items
                                        </span>
                                        <span style={{ fontSize: 'var(--text-sm)', fontWeight: 700, color: 'var(--color-accent-light)' }}>
                                            S/ {parseFloat(order.total).toFixed(2)}
                                        </span>
                                    </div>
                                </button>
                            );
                        })}
                    </div>
                )}
            </aside>

            {/* ─── COLUMNA DERECHA: DETALLE ─── */}
            <section style={{ padding: 'var(--space-8)', overflowY: 'auto' }}>
                {!selected ? (
                    <div style={{
                        display: 'flex', flexDirection: 'column',
                        alignItems: 'center', justifyContent: 'center',
                        height: '60vh', gap: 'var(--space-4)',
                        color: 'var(--color-text-muted)',
                    }}>
                        <div style={{ fontSize: '5rem' }}>👈</div>
                        <h2 style={{ fontSize: 'var(--text-xl)', fontWeight: 700 }}>Selecciona una orden</h2>
                        <p style={{ fontSize: 'var(--text-sm)' }}>Haz clic en una orden de la lista para ver los detalles</p>
                    </div>
                ) : (
                    <div className="animate-fade-in" style={{ maxWidth: 700, margin: '0 auto' }}>
                        {/* ENCABEZADO ORDEN */}
                        <div style={{
                            display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start',
                            marginBottom: 'var(--space-6)',
                            background: 'var(--color-bg-card)', padding: 'var(--space-6)',
                            borderRadius: 'var(--radius-lg)', border: '1px solid var(--color-border)',
                        }}>
                            <div>
                                <h2 style={{ fontSize: 'var(--text-2xl)', fontWeight: 900, letterSpacing: '-0.02em' }}>
                                    {selected.order_number}
                                </h2>
                                <p style={{ color: 'var(--color-text-secondary)', marginTop: 'var(--space-1)' }}>
                                    👤 {selected.customer_name}
                                    {selected.customer_phone && ` · 📞 ${selected.customer_phone}`}
                                </p>
                                <p style={{ color: 'var(--color-text-muted)', fontSize: 'var(--text-sm)', marginTop: 'var(--space-1)' }}>
                                    {selected.channel === 'online' ? '🌐 Pedido online' : '🏪 POS local'}
                                    {' · '}
                                    {selected.delivery_type === 'pickup' ? '🏪 Retiro en local' : '🛵 Delivery'}
                                </p>
                            </div>
                            <div style={{
                                textAlign: 'right',
                                background: statusColors[selected.status]?.bg,
                                border: `1px solid ${statusColors[selected.status]?.border}`,
                                padding: 'var(--space-3) var(--space-4)',
                                borderRadius: 'var(--radius-md)',
                            }}>
                                <div style={{ fontSize: 'var(--text-sm)', fontWeight: 800, color: statusColors[selected.status]?.text }}>
                                    {statusColors[selected.status]?.label}
                                </div>
                                <div style={{ fontSize: 'var(--text-xl)', fontWeight: 900, color: 'var(--color-accent-light)', marginTop: 'var(--space-1)' }}>
                                    S/ {parseFloat(selected.total).toFixed(2)}
                                </div>
                            </div>
                        </div>

                        {/* ITEMS */}
                        <div style={{
                            background: 'var(--color-bg-card)', borderRadius: 'var(--radius-lg)',
                            border: '1px solid var(--color-border)', marginBottom: 'var(--space-5)',
                            overflow: 'hidden',
                        }}>
                            <div style={{ padding: 'var(--space-4) var(--space-6)', borderBottom: '1px solid var(--color-border)', fontWeight: 700 }}>
                                🍙 Productos del pedido
                            </div>
                            {(selected.items || []).map((item, idx) => (
                                <div key={item.id || idx} style={{
                                    display: 'flex', justifyContent: 'space-between', alignItems: 'center',
                                    padding: 'var(--space-4) var(--space-6)',
                                    borderBottom: idx < selected.items.length - 1 ? '1px solid var(--color-border)' : 'none',
                                }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 'var(--space-4)' }}>
                                        <div style={{
                                            width: 40, height: 40, borderRadius: 'var(--radius-sm)',
                                            background: 'var(--color-surface-2)',
                                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                                            fontSize: '1.4rem',
                                        }}>
                                            🍙
                                        </div>
                                        <div>
                                            <div style={{ fontWeight: 600 }}>{item.product_name}</div>
                                            {item.customization_notes && (
                                                <div style={{ fontSize: 'var(--text-xs)', color: 'var(--color-text-muted)', marginTop: 2 }}>
                                                    📝 {item.customization_notes}
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    <div style={{ textAlign: 'right' }}>
                                        <div style={{ fontWeight: 700, color: 'var(--color-accent-light)' }}>
                                            x{item.quantity}
                                        </div>
                                        <div style={{ fontSize: 'var(--text-sm)', color: 'var(--color-text-secondary)' }}>
                                            S/ {parseFloat(item.line_total).toFixed(2)}
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>

                        {/* TOTALES */}
                        <div style={{
                            background: 'var(--color-bg-card)', borderRadius: 'var(--radius-lg)',
                            border: '1px solid var(--color-border)', padding: 'var(--space-5) var(--space-6)',
                            marginBottom: 'var(--space-5)',
                            display: 'flex', flexDirection: 'column', gap: 'var(--space-3)',
                        }}>
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 'var(--text-sm)', color: 'var(--color-text-secondary)' }}>
                                <span>Subtotal</span><span>S/ {parseFloat(selected.subtotal).toFixed(2)}</span>
                            </div>
                            <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 'var(--text-sm)', color: 'var(--color-text-secondary)' }}>
                                <span>IGV ({selected.tax_rate}%)</span><span>S/ {parseFloat(selected.tax_amount).toFixed(2)}</span>
                            </div>
                            <div style={{
                                display: 'flex', justifyContent: 'space-between',
                                fontSize: 'var(--text-xl)', fontWeight: 800,
                                color: 'var(--color-accent-light)',
                                borderTop: '1px solid var(--color-border)', paddingTop: 'var(--space-3)',
                            }}>
                                <span>TOTAL</span><span>S/ {parseFloat(selected.total).toFixed(2)}</span>
                            </div>
                            {selected.payment_method && (
                                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 'var(--text-sm)', color: 'var(--color-text-secondary)', borderTop: '1px solid var(--color-border)', paddingTop: 'var(--space-3)' }}>
                                    <span>Pago: {selected.payment_method?.toUpperCase()}</span>
                                    {selected.change_amount > 0 && (
                                        <span style={{ color: 'var(--color-success)', fontWeight: 700 }}>
                                            Vuelto: S/ {parseFloat(selected.change_amount).toFixed(2)}
                                        </span>
                                    )}
                                </div>
                            )}
                        </div>

                        {/* ACCIONES */}
                        <div style={{ display: 'flex', gap: 'var(--space-3)' }}>
                            {nextStatus[selected.status] && (
                                <button
                                    id={`pos-action-${selected.id}`}
                                    className="btn btn-primary btn-lg"
                                    style={{ flex: 2 }}
                                    disabled={updating}
                                    onClick={() => handleStatus(selected.id, nextStatus[selected.status])}
                                >
                                    {updating
                                        ? <span className="animate-spin" style={{ display: 'inline-block' }}>⟳</span>
                                        : nextStatusLabel[selected.status]
                                    }
                                </button>
                            )}

                            {/* FACTURA / PDF */}
                            {invoiceData?.pdf_ready ? (
                                <a
                                    href={invoiceData.pdf_url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="btn btn-ghost btn-lg"
                                    style={{ flex: 1 }}
                                    id={`download-invoice-${selected.id}`}
                                >
                                    📄 Descargar PDF
                                </a>
                            ) : invoiceData ? (
                                <div className="btn btn-ghost btn-lg" style={{ flex: 1, opacity: 0.5, cursor: 'default', justifyContent: 'center' }}>
                                    ⏳ PDF generándose...
                                </div>
                            ) : null}
                        </div>
                    </div>
                )}
            </section>
        </main>
    );
}
