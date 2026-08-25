import { useCart } from '../contexts/CartContext';

export default function Header({ onCartOpen }) {
    const { itemCount } = useCart();

    return (
        <header className="header-bar glass" style={{
            position: 'fixed', top: 0, left: 0, right: 0,
            height: 'var(--header-height)',
            zIndex: 100,
            display: 'flex', alignItems: 'center',
            padding: '0 var(--space-6)',
            borderBottom: '1px solid var(--color-border)',
        }}>
            <div className="container" style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', maxWidth: '100%' }}>

                {/* LOGO */}
                <a href="/" style={{ display: 'flex', alignItems: 'center', gap: 'var(--space-3)', textDecoration: 'none' }}>
                    <span style={{ fontSize: '1.75rem', lineHeight: 1 }}>🍙</span>
                    <div>
                        <div style={{
                            fontFamily: 'var(--font-japanese)',
                            fontSize: 'var(--text-xl)',
                            fontWeight: 900,
                            letterSpacing: '-0.02em',
                            color: 'var(--color-text-primary)',
                        }}>
                            Onigiri <span className="text-gradient">Express</span>
                        </div>
                        <div style={{
                            fontSize: 'var(--text-xs)',
                            color: 'var(--color-text-muted)',
                            letterSpacing: '0.08em',
                            textTransform: 'uppercase',
                        }}>
                            El sabor de Japón
                        </div>
                    </div>
                </a>

                {/* NAV */}
                <nav className="hide-mobile" style={{ display: 'flex', gap: 'var(--space-8)', alignItems: 'center' }}>
                    <a href="/" style={{ color: 'var(--color-text-secondary)', fontSize: 'var(--text-sm)', fontWeight: 500, transition: 'color 150ms', ':hover': { color: 'var(--color-text-primary)' } }}>
                        Menú
                    </a>
                    <a href="/pos" style={{ color: 'var(--color-text-secondary)', fontSize: 'var(--text-sm)', fontWeight: 500 }}>
                        POS Cajero
                    </a>
                </nav>

                {/* CARRITO */}
                <button
                    onClick={onCartOpen}
                    style={{
                        display: 'flex', alignItems: 'center', gap: 'var(--space-2)',
                        padding: 'var(--space-2) var(--space-4)',
                        background: itemCount > 0 ? 'var(--color-accent-glow)' : 'transparent',
                        border: `1px solid ${itemCount > 0 ? 'var(--color-border-accent)' : 'var(--color-border)'}`,
                        borderRadius: 'var(--radius-md)',
                        color: itemCount > 0 ? 'var(--color-accent-light)' : 'var(--color-text-secondary)',
                        cursor: 'pointer',
                        transition: 'all var(--transition-base)',
                        fontFamily: 'var(--font-primary)',
                        fontSize: 'var(--text-sm)',
                        fontWeight: 600,
                    }}
                    aria-label={`Ver carrito (${itemCount} items)`}
                >
                    <span style={{ fontSize: '1.2rem' }}>🛒</span>
                    {itemCount > 0 && (
                        <span
                            key={itemCount}
                            style={{
                                background: 'var(--color-accent)',
                                color: 'var(--color-text-inverse)',
                                borderRadius: 'var(--radius-full)',
                                padding: '2px 8px',
                                fontSize: 'var(--text-xs)',
                                fontWeight: 800,
                                animation: 'cartBounce 400ms cubic-bezier(0.34, 1.56, 0.64, 1)',
                            }}
                        >
                            {itemCount}
                        </span>
                    )}
                    <span className="hide-mobile">Carrito</span>
                </button>
            </div>
        </header>
    );
}
