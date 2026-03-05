/**
 * bottom-sheet.js — Mobile filter bottom sheet.
 * Opens/closes the bottom sheet via toggle button.
 * Traps focus and handles overlay click to close.
 */

const overlay = document.querySelector('.bottom-sheet-overlay');
const sheet   = document.querySelector('.bottom-sheet');
const openBtn = document.querySelector('[data-open-filter]');
const closeBtns = document.querySelectorAll('[data-close-filter]');

function openSheet() {
    if (!sheet || !overlay) return;
    sheet.classList.add('is-open');
    overlay.classList.add('is-open');
    document.body.style.overflow = 'hidden';
    sheet.querySelector('button, [href], input, select')?.focus();
}

function closeSheet() {
    if (!sheet || !overlay) return;
    sheet.classList.remove('is-open');
    overlay.classList.remove('is-open');
    document.body.style.overflow = '';
    openBtn?.focus();
}

openBtn?.addEventListener('click', openSheet);
closeBtns.forEach((btn) => btn.addEventListener('click', closeSheet));
overlay?.addEventListener('click', closeSheet);

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && sheet?.classList.contains('is-open')) {
        closeSheet();
    }
});
