import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Global wishlist helper using localStorage
document.addEventListener('alpine:init', () => {
    Alpine.store('wishlist', {
        items: JSON.parse(localStorage.getItem('market_wishlist') || '[]'),
        has(id) {
            return this.items.includes(Number(id));
        },
        toggle(id) {
            const numId = Number(id);
            if (this.has(numId)) {
                this.items = this.items.filter(i => i !== numId);
            } else {
                this.items.push(numId);
            }
            localStorage.setItem('market_wishlist', JSON.stringify(this.items));
        },
        count() {
            return this.items.length;
        }
    });

    Alpine.store('cart', {
        count: 0,
        notify() {
            alert("Savatcha va to'lov oqimi 05-bosqichda faollashtiriladi. Hozircha tanlangan mahsulot narx kalkulyatori to'liq ishlaydi!");
        }
    });
});

Alpine.start();
