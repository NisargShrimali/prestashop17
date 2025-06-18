document.addEventListener('DOMContentLoaded', function () {
  const overlay = document.getElementById('custompopup-newsletter-overlay');
  const closeBtn = document.querySelector('.custompopup-newsletter-close');

  if (overlay) {
    // Show modal after delay or immediately
    setTimeout(() => {
      overlay.style.display = 'flex';
    }, 1000); // show after 1 second

    // Close handler
    closeBtn?.addEventListener('click', () => {
      overlay.style.display = 'none';
    });

    // Close on ESC
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') overlay.style.display = 'none';
    });
  }
});
