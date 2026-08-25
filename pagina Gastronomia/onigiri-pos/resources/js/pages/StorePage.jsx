import { useState, useEffect } from 'react';
import { fetchCatalog } from '../api';
import ProductCard from '../components/ProductCard';

/**
 * StorePage — Vista principal de la tienda online (E-commerce).
 * Incluye: Hero, filtro de categorías y grilla de productos.
 */
export default function StorePage() {
    const [products, setProducts]         = useState([]);
    const [loading, setLoading]           = useState(true);
    const [activeCategory, setActiveCategory] = useState('all');

    useEffect(() => {
        fetchCatalog()
            .then(setProducts)
            .catch(console.error)
            .finally(() => setLoading(false));
    }, []);

    // Obtener categorías únicas
    const categories = [
        { id: 'all', name: 'Todo el menú', emoji: '🍱' },
        ...Object.values(
            products.reduce((acc, p) => {
                if (!acc[p.category.id]) {
                    acc[p.category.id] = { id: p.category.slug, name: p.category.name, emoji: getCategoryEmoji(p.category.slug) };
                }
                return acc;
            }, {})
        ),
    ];

    const filtered = activeCategory === 'all'
        ? products
        : products.filter(p => p.category.slug === activeCategory);

    return (
        <main style={{ paddingTop: 'var(--header-height)', minHeight: '100vh' }}>

            {/* ─── HERO ─── */}
            <section style={{
                background: 'linear-gradient(135deg, var(--color-bg-primary) 0%, var(--color-nori) 50%, var(--color-bg-primary) 100%)',
                padding: 'var(--space-20) var(--space-6)',
                textAlign: 'center',
                position: 'relative',
                overflow: 'hidden',
            }}>
                {/* Fondo decorativo */}
                <div style={{
                    position: 'absolute', inset: 0,
                    backgroundImage: 'radial-gradient(circle at 20% 80%, rgba(201,168,76,0.08) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(232,160,180,0.06) 0%, transparent 50%)',
                }} />

                <div style={{ position: 'relative', maxWidth: 640, margin: '0 auto' }}>
                    <div style={{
                        display: 'inline-block',
                        fontSize: 'var(--text-xs)', fontWeight: 700,
                        letterSpacing: '0.15em', textTransform: 'uppercase',
                        color: 'var(--color-accent)', marginBottom: 'var(--space-4)',
                        border: '1px solid var(--color-border-accent)',
                        padding: '4px 16px', borderRadius: 'var(--radius-full)',
                        background: 'var(--color-accent-glow)',
                    }}>
                        Comida Rápida Asiática · Lima, Perú
                    </div>

                    <h1 className="animate-fade-in" style={{
                        fontSize: 'clamp(2.5rem, 8vw, 4rem)',
                        fontWeight: 900,
                        lineHeight: 1.1,
                        marginBottom: 'var(--space-5)',
                        letterSpacing: '-0.03em',
                    }}>
                        Los mejores <br />
                        <span className="text-gradient">onigiris</span>{' '}
                        <span style={{ fontFamily: 'var(--font-japanese)' }}>de la ciudad</span>
                    </h1>

                    <p className="animate-slide-up" style={{
                        fontSize: 'var(--text-lg)',
                        color: 'var(--color-text-secondary)',
                        maxWidth: 480, margin: '0 auto',
                        lineHeight: 1.7,
                    }}>
                        Preparados al momento con ingredientes frescos. El auténtico sabor japonés, ahora en tus manos.
                    </p>

                    <div style={{ marginTop: 'var(--space-8)', fontSize: '5rem', animation: 'float 3s ease-in-out infinite' }}>
                        🍙
                    </div>
                </div>
            </section>

            {/* ─── FILTROS DE CATEGORÍA ─── */}
            <section style={{
                position: 'sticky', top: 'var(--header-height)', zIndex: 50,
                background: 'var(--color-bg-secondary)',
                borderBottom: '1px solid var(--color-border)',
                padding: 'var(--space-4) var(--space-6)',
            }}>
                <div className="container" style={{ overflowX: 'auto', paddingBottom: 'var(--space-2)' }}>
                    <div style={{ display: 'flex', gap: 'var(--space-3)', minWidth: 'max-content' }}>
                        {categories.map(cat => (
                            <button
                                key={cat.id}
                                id={`filter-${cat.id}`}
                                onClick={() => setActiveCategory(cat.id)}
                                style={{
                                    display: 'flex', alignItems: 'center', gap: 'var(--space-2)',
                                    padding: 'var(--space-2) var(--space-4)',
                                    borderRadius: 'var(--radius-full)',
                                    border: `1px solid ${activeCategory === cat.id ? 'var(--color-accent)' : 'var(--color-border)'}`,
                                    background: activeCategory === cat.id ? 'var(--color-accent-glow)' : 'transparent',
                                    color: activeCategory === cat.id ? 'var(--color-accent-light)' : 'var(--color-text-secondary)',
                                    fontFamily: 'var(--font-primary)', fontWeight: 600,
                                    fontSize: 'var(--text-sm)', cursor: 'pointer',
                                    transition: 'all var(--transition-fast)', whiteSpace: 'nowrap',
                                }}
                            >
                                <span>{cat.emoji}</span>
                                <span>{cat.name}</span>
                            </button>
                        ))}
                    </div>
                </div>
            </section>

            {/* ─── GRILLA DE PRODUCTOS ─── */}
            <section style={{ padding: 'var(--space-10) var(--space-6)' }}>
                <div className="container">
                    {loading ? (
                        <div style={{
                            display: 'grid',
                            gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))',
                            gap: 'var(--space-6)',
                        }}>
                            {[...Array(6)].map((_, i) => (
                                <div key={i}>
                                    <div className="skeleton" style={{ height: 200, borderRadius: 'var(--radius-lg)' }} />
                                    <div className="skeleton" style={{ height: 20, margin: 'var(--space-3) 0', borderRadius: 'var(--radius-sm)', width: '70%' }} />
                                    <div className="skeleton" style={{ height: 14, borderRadius: 'var(--radius-sm)', width: '50%' }} />
                                </div>
                            ))}
                        </div>
                    ) : (
                        <>
                            <div style={{
                                marginBottom: 'var(--space-6)',
                                display: 'flex', alignItems: 'center', gap: 'var(--space-4)',
                            }}>
                                <h2 style={{ fontSize: 'var(--text-2xl)', fontWeight: 800 }}>
                                    {categories.find(c => c.id === activeCategory)?.name ?? 'Menú'}
                                </h2>
                                <span style={{
                                    background: 'var(--color-surface-2)', color: 'var(--color-text-muted)',
                                    padding: '3px 12px', borderRadius: 'var(--radius-full)',
                                    fontSize: 'var(--text-xs)', fontWeight: 700,
                                }}>
                                    {filtered.length} productos
                                </span>
                            </div>

                            {filtered.length === 0 ? (
                                <div style={{ textAlign: 'center', padding: 'var(--space-16)', color: 'var(--color-text-muted)' }}>
                                    <div style={{ fontSize: '3rem', marginBottom: 'var(--space-4)' }}>🔍</div>
                                    <p>No hay productos en esta categoría</p>
                                </div>
                            ) : (
                                <div style={{
                                    display: 'grid',
                                    gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))',
                                    gap: 'var(--space-6)',
                                }}>
                                    {filtered.map((product, idx) => (
                                        <div
                                            key={product.id}
                                            className="animate-slide-up"
                                            style={{ animationDelay: `${idx * 60}ms`, animationFillMode: 'both', opacity: 0 }}
                                        >
                                            <ProductCard product={product} />
                                        </div>
                                    ))}
                                </div>
                            )}
                        </>
                    )}
                </div>
            </section>
        </main>
    );
}

function getCategoryEmoji(slug) {
    const map = {
        'onigiris-clasicos' : '🍙',
        'onigiris-premium'  : '✨',
        'onigiris-veganos'  : '🥬',
        'bebidas'           : '🍵',
        'snacks-y-extras'   : '🍡',
    };
    return map[slug] ?? '🍱';
}
