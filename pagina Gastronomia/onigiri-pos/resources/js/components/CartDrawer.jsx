import { useCart } from '../contexts/CartContext';
import { createOrder, confirmOrder } from '../api';
import { useState } from 'react';

/**
 * CartDrawer — Panel lateral deslizante del carrito.
 * Mobile-friendly con overlay.
 */
export default function CartDrawer({ isOpen, onClose }) {
    const { items, subtotal, taxAmount, total, updateQuantity, removeItem, clearCart, cartItems } = useCart();
    const [step, setStep]       = useState('cart');    // cart | checkout | success
    const [loading, setLoading] = useState(false);
    const [orderId, setOrderId] = useState(null);
    const [form, setForm]       = useState({
        customer_name:  '',
        customer_email: '',
        customer_phone: '',
        delivery_type:  'pickup',
        payment_method: 'cash',
        amount_received: '',
    });

    const handleSubmit = async () => {
        if (!form.customer_name.trim()) return;
        setLoading(true);
        try {
            // 1. Crear pedido
            const order = await createOrder({
                ...form,
                channel: 'online',
                items:   cartItems,
                tax_rate: 18,
            });

            // 2. Confirmar (descuenta stock)
            await confirmOrder(order.data.id);

            setOrderId(order.data.id);
            setStep('success');
            clearCart();
        } catch (err) {
            alert(err.response?.data?.message || 'Error al procesar el pedido.');
        } finally {
            setLoading(false);
        }
    };

    if (!isOpen) return null;

    return (
        <>
            {/* OVERLAY */}
            <div
                onClick={onClose}
                style={{
                    position: 'fixed', inset: 0,
                    background: 'var(--color-bg-overlay)',
                    zIndex: 200,
                    animation: 'fadeIn 200ms ease forwards',
                    backdropFilter: 'blur(4px)',
                }}
            />

            {/* DRAWER */}
            <aside style={{
                position: 'fixed', top: 0, right: 0, bottom: 0,
                width: 'min(var(--sidebar-width), 100vw)',
                background: 'var(--color-bg-secondary)',
                borderLeft: '1px solid var(--color-border)',
                zIndex: 201,
                display: 'flex', flexDirection: 'column',
                animation: 'slideInRight 300ms cubic-bezier(0.4, 0, 0.2, 1) forwards',
            }}>

                {/* HEADER */}
                <div style={{
                    display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                    padding: 'var(--space-5) var(--space-6)',
                    borderBottom: '1px solid var(--color-border)',
                }}>
                    <div>
                        <h2 style={{ fontSize: 'var(--text-lg)', fontWeight: 700 }}>
                            {step === 'cart'     && '🛒 Mi carrito'}
                            {step === 'checkout' && '📋 Checkout'}
                            {step === 'success'  && '✅ ¡Pedido listo!'}
                        </h2>
                        {step === 'cart' && items.length > 0 && (
                            <p style={{ fontSize: 'var(--text-xs)', color: 'var(--color-text-muted)', marginTop: 2 }}>
                                {items.reduce((s, i) => s + i.quantity, 0)} producto(s)
                            </p>
                        )}
                    </div>
                    <button onClick={onClose} className="btn btn-ghost btn-icon" aria-label="Cerrar carrito">✕</button>
                </div>

                {/* ───── PASO: CARRITO ───── */}
                {step === 'cart' && (
                    <>
                        <div style={{ flex: 1, overflowY: 'auto', padding: 'var(--space-4) var(--space-6)' }}>
                            {items.length === 0 ? (
                                <div style={{ textAlign: 'center', padding: 'var(--space-16) 0', color: 'var(--color-text-muted)' }}>
                                    <div style={{ fontSize: '4rem', marginBottom: 'var(--space-4)' }}>🍙</div>
                                    <p>Tu carrito está vacío</p>
                                    <p style={{ fontSize: 'var(--text-sm)', marginTop: 'var(--space-2)' }}>
                                        Agrega tus onigiris favoritos
                                    </p>
                                </div>
                            ) : (
                                <div style={{ display: 'flex', flexDirection: 'column', gap: 'var(--space-4)' }}>
                                    {items.map(({ product, quantity }) => (
                                        <CartItem
                                            key={product.id}
                                            product={product}
                                            quantity={quantity}
                                            onUpdate={q => updateQuantity(product.id, q)}
                                            onRemove={() => removeItem(product.id)}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>

                        {items.length > 0 && (
                            <div style={{
                                padding: 'var(--space-5) var(--space-6)',
                                borderTop: '1px solid var(--color-border)',
                                display: 'flex', flexDirection: 'column', gap: 'var(--space-3)',
                            }}>
                                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 'var(--text-sm)', color: 'var(--color-text-secondary)' }}>
                                    <span>Subtotal</span><span>S/ {subtotal.toFixed(2)}</span>
                                </div>
                                <div style={{ display: 'flex', justifyContent: 'space-between', fontSize: 'var(--text-sm)', color: 'var(--color-text-secondary)' }}>
                                    <span>IGV (18%)</span><span>S/ {taxAmount.toFixed(2)}</span>
                                </div>
                                <div style={{
                                    display: 'flex', justifyContent: 'space-between',
                                    fontSize: 'var(--text-lg)', fontWeight: 800,
                                    color: 'var(--color-accent-light)',
                                    borderTop: '1px solid var(--color-border)', paddingTop: 'var(--space-3)',
                                }}>
                                    <span>Total</span><span>S/ {total.toFixed(2)}</span>
                                </div>
                                <button
                                    id="checkout-btn"
                                    className="btn btn-primary btn-lg"
                                    onClick={() => setStep('checkout')}
                                    style={{ width: '100%', marginTop: 'var(--space-2)' }}
                                >
                                    Continuar al pago →
                                </button>
                            </div>
                        )}
                    </>
                )}

                {/* ───── PASO: CHECKOUT ───── */}
                {step === 'checkout' && (
                    <div style={{ flex: 1, overflowY: 'auto', padding: 'var(--space-5) var(--space-6)', display: 'flex', flexDirection: 'column', gap: 'var(--space-4)' }}>
                        <div>
                            <label style={{ fontSize: 'var(--text-xs)', color: 'var(--color-text-muted)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.06em' }}>
                                Nombre *
                            </label>
                            <input className="input" style={{ marginTop: 'var(--space-2)' }}
                                placeholder="Tu nombre completo"
                                value={form.customer_name}
                                onChange={e => setForm(f => ({ ...f, customer_name: e.target.value }))}
                                id="checkout-name"
                            />
                        </div>
                        <div>
                            <label style={{ fontSize: 'var(--text-xs)', color: 'var(--color-text-muted)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.06em' }}>
                                Email
                            </label>
                            <input className="input" style={{ marginTop: 'var(--space-2)' }}
                                type="email" placeholder="tu@email.com"
                                value={form.customer_email}
                                onChange={e => setForm(f => ({ ...f, customer_email: e.target.value }))}
                                id="checkout-email"
                            />
                        </div>
                        <div>
                            <label style={{ fontSize: 'var(--text-xs)', color: 'var(--color-text-muted)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.06em' }}>
                                Tipo de entrega
                            </label>
                            <div style={{ display: 'flex', gap: 'var(--space-3)', marginTop: 'var(--space-2)' }}>
                                {['pickup', 'delivery'].map(t => (
                                    <button key={t}
                                        id={`delivery-${t}`}
                                        onClick={() => setForm(f => ({ ...f, delivery_type: t }))}
                                        style={{
                                            flex: 1, padding: 'var(--space-3)',
                                            border: `1px solid ${form.delivery_type === t ? 'var(--color-accent)' : 'var(--color-border)'}`,
                                            borderRadius: 'var(--radius-md)',
                                            background: form.delivery_type === t ? 'var(--color-accent-glow)' : 'transparent',
                                            color: form.delivery_type === t ? 'var(--color-accent-light)' : 'var(--color-text-secondary)',
                                            fontFamily: 'var(--font-primary)', fontWeight: 600,
                                            fontSize: 'var(--text-sm)', cursor: 'pointer',
                                            transition: 'all var(--transition-fast)',
                                        }}
                                    >
                                        {t === 'pickup' ? '🏪 Retiro' : '🛵 Delivery'}
                                    </button>
                                ))}
                            </div>
                        </div>
                        <div>
                            <label style={{ fontSize: 'var(--text-xs)', color: 'var(--color-text-muted)', fontWeight: 600, textTransform: 'uppercase', letterSpacing: '0.06em' }}>
                                Pago
                            </label>
                            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 'var(--space-2)', marginTop: 'var(--space-2)' }}>
                                {[
                                    { id: 'cash', label: '💵 Efectivo' },
                                    { id: 'card', label: '💳 Tarjeta' },
                                    { id: 'yape', label: '📱 Yape' },
                                    { id: 'plin', label: '📱 Plin' },
                                ].map(m => (
                                    <button key={m.id}
                                        id={`payment-${m.id}`}
                                        onClick={() => setForm(f => ({ ...f, payment_method: m.id }))}
                                        style={{
                                            padding: 'var(--space-2) var(--space-3)',
                                            border: `1px solid ${form.payment_method === m.id ? 'var(--color-accent)' : 'var(--color-border)'}`,
                                            borderRadius: 'var(--radius-md)',
                                            background: form.payment_method === m.id ? 'var(--color-accent-glow)' : 'transparent',
                                            color: form.payment_method === m.id ? 'var(--color-accent-light)' : 'var(--color-text-secondary)',
                                            fontFamily: 'var(--font-primary)', fontSize: 'var(--text-sm)',
                                            cursor: 'pointer', transition: 'all var(--transition-fast)',
                                        }}
                                    >
                                        {m.label}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {/* RESUMEN */}
                        <div style={{
                            background: 'var(--color-surface-1)', borderRadius: 'var(--radius-md)',
                            padding: 'var(--space-4)', border: '1px solid var(--color-border)',
                        }}>
                            <div style={{ fontWeight: 700, marginBottom: 'var(--space-3)', fontSize: 'var(--text-sm)' }}>Resumen</div>
                            {items.map(({ product, quantity }) => (
                                <div key={product.id} style={{
                                    display: 'flex', justifyContent: 'space-between',
                                    fontSize: 'var(--text-sm)', color: 'var(--color-text-secondary)',
                                    padding: 'var(--space-1) 0',
                                }}>
                                    <span>{product.name} x{quantity}</span>
                                    <span>S/ {(product.price * quantity).toFixed(2)}</span>
                                </div>
                            ))}
                            <div style={{ borderTop: '1px solid var(--color-border)', marginTop: 'var(--space-3)', paddingTop: 'var(--space-3)', display: 'flex', justifyContent: 'space-between', fontWeight: 800, color: 'var(--color-accent-light)' }}>
                                <span>Total</span><span>S/ {total.toFixed(2)}</span>
                            </div>
                        </div>

                        <div style={{ display: 'flex', gap: 'var(--space-3)' }}>
                            <button className="btn btn-ghost" style={{ flex: 1 }} onClick={() => setStep('cart')}>
                                ← Volver
                            </button>
                            <button
                                id="place-order-btn"
                                className="btn btn-primary"
                                style={{ flex: 2 }}
                                onClick={handleSubmit}
                                disabled={loading || !form.customer_name.trim()}
                            >
                                {loading ? (
                                    <span className="animate-spin" style={{ display: 'inline-block' }}>⟳</span>
                                ) : '✓ Confirmar pedido'}
                            </button>
                        </div>
                    </div>
                )}

                {/* ───── PASO: ÉXITO ───── */}
                {step === 'success' && (
                    <div style={{
                        flex: 1, display: 'flex', flexDirection: 'column',
                        alignItems: 'center', justifyContent: 'center',
                        padding: 'var(--space-8)', textAlign: 'center',
                        gap: 'var(--space-5)',
                    }}>
                        <div style={{ fontSize: '5rem', animation: 'scaleIn 500ms cubic-bezier(0.34, 1.56, 0.64, 1)' }}>🎉</div>
                        <h2 style={{ fontSize: 'var(--text-2xl)', fontWeight: 800 }}>¡Pedido confirmado!</h2>
                        <p style={{ color: 'var(--color-text-secondary)', fontSize: 'var(--text-sm)' }}>
                            Tu onigiri está siendo preparado.<br/>
                            Pedido #{orderId}
                        </p>
                        <div style={{
                            background: 'var(--color-accent-glow)',
                            border: '1px solid var(--color-border-accent)',
                            borderRadius: 'var(--radius-md)', padding: 'var(--space-4)',
                            fontSize: 'var(--text-sm)', color: 'var(--color-accent-light)',
                            width: '100%',
                        }}>
                            La factura se está generando en PDF.<br/>
                            La recibirás en breve.
                        </div>
                        <button className="btn btn-primary btn-lg" style={{ width: '100%' }} onClick={onClose}>
                            Seguir comprando
                        </button>
                    </div>
                )}
            </aside>
        </>
    );
}

// Sub-componente de item del carrito
function CartItem({ product, quantity, onUpdate, onRemove }) {
    return (
        <div style={{
            display: 'flex', gap: 'var(--space-3)', alignItems: 'center',
            padding: 'var(--space-3)', background: 'var(--color-surface-1)',
            borderRadius: 'var(--radius-md)', border: '1px solid var(--color-border)',
            animation: 'slideUp 250ms ease',
        }}>
            <div style={{
                width: 52, height: 52, borderRadius: 'var(--radius-sm)',
                background: 'var(--color-bg-elevated)',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                fontSize: '1.8rem', flexShrink: 0,
            }}>
                🍙
            </div>
            <div style={{ flex: 1, minWidth: 0 }}>
                <div style={{ fontWeight: 600, fontSize: 'var(--text-sm)', whiteSpace: 'nowrap', overflow: 'hidden', textOverflow: 'ellipsis' }}>
                    {product.name}
                </div>
                <div style={{ fontSize: 'var(--text-xs)', color: 'var(--color-accent-light)', fontWeight: 700 }}>
                    S/ {(product.price * quantity).toFixed(2)}
                </div>
            </div>
            <div style={{ display: 'flex', alignItems: 'center', gap: 'var(--space-2)' }}>
                <button onClick={() => onUpdate(quantity - 1)} style={{
                    width: 28, height: 28, borderRadius: 'var(--radius-sm)',
                    background: 'var(--color-surface-2)', border: '1px solid var(--color-border)',
                    color: 'var(--color-text-secondary)', cursor: 'pointer', fontFamily: 'inherit',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                }} aria-label="Reducir cantidad">
                    −
                </button>
                <span style={{ minWidth: 20, textAlign: 'center', fontSize: 'var(--text-sm)', fontWeight: 700 }}>
                    {quantity}
                </span>
                <button onClick={() => onUpdate(quantity + 1)} style={{
                    width: 28, height: 28, borderRadius: 'var(--radius-sm)',
                    background: 'var(--color-accent)', border: 'none',
                    color: 'var(--color-text-inverse)', cursor: 'pointer', fontFamily: 'inherit',
                    display: 'flex', alignItems: 'center', justifyContent: 'center', fontWeight: 700,
                }} aria-label="Aumentar cantidad">
                    +
                </button>
            </div>
        </div>
    );
}
