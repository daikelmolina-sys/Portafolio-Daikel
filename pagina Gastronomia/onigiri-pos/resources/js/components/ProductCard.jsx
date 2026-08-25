import { useState } from 'react';
import { useCart } from '../contexts/CartContext';

/**
 * ProductCard — Tarjeta de producto para la tienda con:
 *  - Imagen con overlay de hover
 *  - Badge de descuento
 *  - Tiempo de preparación
 *  - Botón "Añadir" con micro-animación (sin alert nativo)
 *  - Feedback visual al agregar (✓ flash)
 */
export default function ProductCard({ product }) {
    const { addItem } = useCart();
    const [added, setAdded]     = useState(false);
    const [ripple, setRipple]   = useState(false);

    const handleAdd = async () => {
        if (ripple) return;

        setRipple(true);
        await addItem(product);

        setAdded(true);
        setTimeout(() => { setAdded(false); setRipple(false); }, 1200);
    };

    const placeholderEmoji = {
        'onigiris-clasicos' : '🍙',
        'onigiris-premium'  : '✨',
        'onigiris-veganos'  : '🥬',
        'bebidas'           : '🍵',
        'snacks-y-extras'   : '🍡',
    }[product.category?.slug] ?? '🍙';

    return (
        <article
            className="card"
            style={{ display: 'flex', flexDirection: 'column', position: 'relative', overflow: 'hidden' }}
        >
            {/* BADGE DESCUENTO */}
            {product.compare_price && (
                <div style={{
                    position: 'absolute', top: 'var(--space-3)', left: 'var(--space-3)',
                    background: 'var(--color-danger)', color: '#fff',
                    padding: '3px 10px', borderRadius: 'var(--radius-full)',
                    fontSize: 'var(--text-xs)', fontWeight: 800, zIndex: 2,
                    letterSpacing: '0.02em',
                }}>
                    -{product.discount_pct}%
                </div>
            )}

            {/* IMAGEN / PLACEHOLDER */}
            <div style={{
                height: 200,
                background: 'linear-gradient(135deg, var(--color-bg-elevated), var(--color-surface-2))',
                display: 'flex', alignItems: 'center', justifyContent: 'center',
                position: 'relative', overflow: 'hidden',
                transition: 'all var(--transition-base)',
            }}>
                {product.main_image ? (
                    <img
                        src={product.main_image}
                        alt={product.name}
                        style={{ width: '100%', height: '100%', objectFit: 'cover' }}
                    />
                ) : (
                    <span
                        className="animate-float"
                        style={{ fontSize: '5rem', userSelect: 'none', filter: 'drop-shadow(0 8px 24px rgba(0,0,0,0.4))' }}
                    >
                        {placeholderEmoji}
                    </span>
                )}

                {/* PREP TIME BADGE */}
                <div style={{
                    position: 'absolute', bottom: 'var(--space-3)', right: 'var(--space-3)',
                    background: 'rgba(10,10,10,0.8)', backdropFilter: 'blur(8px)',
                    padding: '3px 10px', borderRadius: 'var(--radius-full)',
                    fontSize: 'var(--text-xs)', color: 'var(--color-text-secondary)',
                    border: '1px solid var(--color-border)',
                }}>
                    ⏱ {product.prep_time}min
                </div>
            </div>

            {/* CONTENIDO */}
            <div className="card-body" style={{ flex: 1, display: 'flex', flexDirection: 'column', gap: 'var(--space-3)' }}>

                {/* CATEGORÍA + TAGS */}
                <div style={{ display: 'flex', gap: 'var(--space-2)', flexWrap: 'wrap', alignItems: 'center' }}>
                    <span className="badge badge-accent" style={{ fontSize: '10px' }}>
                        {product.category?.name}
                    </span>
                    {product.tags?.slice(0, 2).map(tag => (
                        <span key={tag} style={{
                            fontSize: '10px', color: 'var(--color-text-muted)',
                            background: 'var(--color-surface-1)', padding: '2px 8px',
                            borderRadius: 'var(--radius-full)', border: '1px solid var(--color-border)',
                        }}>
                            #{tag}
                        </span>
                    ))}
                </div>

                {/* NOMBRE */}
                <h3 style={{
                    fontSize: 'var(--text-base)', fontWeight: 700,
                    color: 'var(--color-text-primary)', lineHeight: 1.3,
                }}>
                    {product.name}
                </h3>

                {/* DESCRIPCIÓN */}
                <p style={{
                    fontSize: 'var(--text-sm)', color: 'var(--color-text-secondary)',
                    lineHeight: 1.5, flex: 1,
                    overflow: 'hidden', display: '-webkit-box',
                    WebkitLineClamp: 2, WebkitBoxOrient: 'vertical',
                }}>
                    {product.short_description}
                </p>

                {/* PRECIO + BOTÓN */}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', marginTop: 'auto' }}>
                    <div>
                        <div style={{
                            fontSize: 'var(--text-xl)', fontWeight: 800,
                            color: 'var(--color-accent-light)',
                        }}>
                            S/ {product.price.toFixed(2)}
                        </div>
                        {product.compare_price && (
                            <div style={{
                                fontSize: 'var(--text-sm)', color: 'var(--color-text-muted)',
                                textDecoration: 'line-through',
                            }}>
                                S/ {product.compare_price.toFixed(2)}
                            </div>
                        )}
                    </div>

                    {/* BOTÓN AÑADIR */}
                    <button
                        id={`add-to-cart-${product.id}`}
                        onClick={handleAdd}
                        disabled={ripple}
                        style={{
                            display: 'flex', alignItems: 'center', gap: 'var(--space-2)',
                            padding: 'var(--space-2) var(--space-4)',
                            background: added ? 'var(--color-success)' : 'var(--color-accent)',
                            color: 'var(--color-text-inverse)',
                            border: 'none', borderRadius: 'var(--radius-md)',
                            fontFamily: 'var(--font-primary)', fontWeight: 700,
                            fontSize: 'var(--text-sm)', cursor: ripple ? 'default' : 'pointer',
                            transition: 'all var(--transition-spring)',
                            transform: ripple ? 'scale(0.94)' : 'scale(1)',
                            boxShadow: added ? '0 0 20px rgba(76,175,107,0.4)' : 'none',
                            minWidth: 100,
                            justifyContent: 'center',
                        }}
                        aria-label={`Agregar ${product.name} al carrito`}
                    >
                        {added ? (
                            <>✓ <span>¡Listo!</span></>
                        ) : (
                            <>+ <span>Agregar</span></>
                        )}
                    </button>
                </div>
            </div>
        </article>
    );
}
