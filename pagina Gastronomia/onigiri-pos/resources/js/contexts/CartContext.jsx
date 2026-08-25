/**
 * CartContext.jsx — Estado global del carrito con Optimistic UI.
 *
 * PATRÓN OPTIMISTIC UI:
 *  1. El usuario añade un producto → la UI reacciona INMEDIATAMENTE.
 *  2. En background se llama a /api/cart/validate.
 *  3. Si el backend dice que no hay stock → se REVIERTE silenciosamente
 *     y se muestra un toast de error.
 *  4. El usuario nunca ve un loading state al agregar productos.
 */
import { createContext, useContext, useReducer, useCallback, useEffect } from 'react';
import { validateCart } from '../api';

// ─────────────────────────────────────────────────────────────────────────────
// ESTADO INICIAL
// ─────────────────────────────────────────────────────────────────────────────
const initialState = {
    items: [],           // [{ product, quantity, notes }]  — siempre array
    isValidating: false,
    lastValidation: null,
    toast: null,
};

// ─────────────────────────────────────────────────────────────────────────────
// REDUCER
// ─────────────────────────────────────────────────────────────────────────────
function cartReducer(state, action) {
    switch (action.type) {
        case 'ADD_ITEM': {
            const existing = state.items.find(i => i.product.id === action.product.id);
            if (existing) {
                return {
                    ...state,
                    items: state.items.map(i =>
                        i.product.id === action.product.id
                            ? { ...i, quantity: i.quantity + 1 }
                            : i
                    ),
                };
            }
            return {
                ...state,
                items: [...state.items, { product: action.product, quantity: 1, notes: '' }],
            };
        }

        case 'REMOVE_ITEM':
            return {
                ...state,
                items: state.items.filter(i => i.product.id !== action.productId),
            };

        case 'UPDATE_QUANTITY': {
            if (action.quantity <= 0) {
                return { ...state, items: state.items.filter(i => i.product.id !== action.productId) };
            }
            return {
                ...state,
                items: state.items.map(i =>
                    i.product.id === action.productId ? { ...i, quantity: action.quantity } : i
                ),
            };
        }

        case 'UPDATE_NOTES':
            return {
                ...state,
                items: state.items.map(i =>
                    i.product.id === action.productId ? { ...i, notes: action.notes } : i
                ),
            };

        // REVERTIR item específico (Optimistic UI: stock insuficiente)
        case 'REVERT_ITEM': {
            if (action.previousQuantity === 0) {
                return { ...state, items: state.items.filter(i => i.product.id !== action.productId) };
            }
            return {
                ...state,
                items: state.items.map(i =>
                    i.product.id === action.productId ? { ...i, quantity: action.previousQuantity } : i
                ),
            };
        }

        case 'CLEAR_CART':
            return { ...state, items: [] };

        case 'SET_VALIDATING':
            return { ...state, isValidating: action.value };

        case 'SET_VALIDATION':
            return { ...state, lastValidation: action.result };

        case 'SET_TOAST':
            return { ...state, toast: action.toast };

        case 'CLEAR_TOAST':
            return { ...state, toast: null };

        default:
            return state;
    }
}

// ─────────────────────────────────────────────────────────────────────────────
// CONTEXT
// ─────────────────────────────────────────────────────────────────────────────
const CartContext = createContext(null);

export function CartProvider({ children }) {
    const [state, dispatch] = useReducer(cartReducer, initialState);

    // Limpiar toast automáticamente
    useEffect(() => {
        if (state.toast) {
            const timer = setTimeout(() => dispatch({ type: 'CLEAR_TOAST' }), 3500);
            return () => clearTimeout(timer);
        }
    }, [state.toast]);

    // ─────────────────────────────────────────────────────────────────────
    // addItem — OPTIMISTIC UI
    // ─────────────────────────────────────────────────────────────────────
    const addItem = useCallback(async (product) => {
        const currentItems = state.items || [];
        // 1. Snapshot del estado anterior (para revertir)
        const existing  = currentItems.find(i => i.product.id === product.id);
        const prevQty   = existing?.quantity ?? 0;
        const newQty    = prevQty + 1;

        // 2. ACTUALIZACIÓN OPTIMISTA — inmediata en la UI
        dispatch({ type: 'ADD_ITEM', product });

        // 3. Validación en background con el backend
        try {
            const currentItems = state.items.map(i => ({
                product_id: i.product.id,
                quantity: i.product.id === product.id ? newQty : i.quantity,
            }));

            // Si el producto no está en el carrito aún, añadirlo al array de validación
            if (!existing) {
                currentItems.push({ product_id: product.id, quantity: 1 });
            }

            const response = await validateCart(currentItems);
            const result   = response.data;

            dispatch({ type: 'SET_VALIDATION', result });

            // 4. Si hay items inválidos → REVERTIR los afectados
            if (!result.valid) {
                const invalidItem = result.items.find(
                    i => i.product_id === product.id && !i.valid
                );

                if (invalidItem) {
                    dispatch({ type: 'REVERT_ITEM', productId: product.id, previousQuantity: prevQty });
                    dispatch({
                        type: 'SET_TOAST',
                        toast: { type: 'error', message: invalidItem.message },
                    });
                }
            }
        } catch (err) {
            // Error de red: mantener el estado optimista pero notificar
            console.warn('[Cart] Error de validación, manteniendo estado optimista.', err);
        }
    }, [state.items]);

    const removeItem     = useCallback((productId) =>
        dispatch({ type: 'REMOVE_ITEM', productId }), []);

    const updateQuantity = useCallback((productId, quantity) =>
        dispatch({ type: 'UPDATE_QUANTITY', productId, quantity }), []);

    const updateNotes    = useCallback((productId, notes) =>
        dispatch({ type: 'UPDATE_NOTES', productId, notes }), []);

    const clearCart      = useCallback(() => dispatch({ type: 'CLEAR_CART' }), []);

    // ─────────────────────────────────────────────────────────────────────
    // COMPUTED VALUES
    // ─────────────────────────────────────────────────────────────────────
    const safeItems  = state.items || [];
    const itemCount  = safeItems.reduce((sum, i) => sum + i.quantity, 0);
    const subtotal   = safeItems.reduce((sum, i) => sum + i.product.price * i.quantity, 0);
    const taxAmount  = subtotal * 0.18;
    const total      = subtotal + taxAmount;

    const cartItems  = safeItems.map(i => ({
        product_id: i.product.id,
        quantity:   i.quantity,
        notes:      i.notes,
    }));

    return (
        <CartContext.Provider value={{
            items:        safeItems,
            cartItems,
            itemCount,
            subtotal,
            taxAmount,
            total,
            isValidating: state.isValidating,
            toast:        state.toast,
            addItem,
            removeItem,
            updateQuantity,
            updateNotes,
            clearCart,
        }}>
            {children}
        </CartContext.Provider>
    );
}

export const useCart = () => {
    const ctx = useContext(CartContext);
    if (!ctx) throw new Error('useCart debe usarse dentro de <CartProvider>');
    return ctx;
};
