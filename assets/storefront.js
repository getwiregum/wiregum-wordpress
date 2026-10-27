/* Native dialog supplies focus trapping, Escape, inert background and focus return. */
(() => {
  const dialog = document.getElementById('wg-cart');
  if (!dialog) return;
  document.addEventListener('click', (event) => {
    if (!(event.target instanceof Element)) return;
    if (event.target.closest('[data-wg-cart-open]') && !dialog.open) dialog.showModal();
    if (event.target.closest('[data-wg-cart-close]')) dialog.close();
  });
  dialog.addEventListener('click', (event) => {
    const bounds = dialog.getBoundingClientRect();
    if (event.target === dialog && (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom)) dialog.close();
  });
})();
