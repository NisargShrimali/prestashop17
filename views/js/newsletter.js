document.addEventListener('DOMContentLoaded', function () {
  // ===== Storage Utility =====
  function getStorage(key) {
    try { return localStorage.getItem(key); } catch (e) { return getCookie(key); }
  }

  function setStorage(key, value) {
    try { localStorage.setItem(key, value); } catch (e) { setCookie(key, value, 30); }
  }

  function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
  }

  function setCookie(name, value, days) {
    const expires = new Date();
    expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
    document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/`;
  }

  // ===== Should Show Popup =====
  function shouldShowPopup(popupId, frequency) {
    const keyShown = `custompopup_${popupId}`;
    const keyClosed = `custompopup_closed_${popupId}`;
    const now = Date.now();
    const hour = 3600000;
    const day = 24 * hour;

    const lastShown = parseInt(getStorage(keyShown)) || 0;
    const userClosed = getStorage(keyClosed);

    // Frequency-based logic
    const elapsed = now - lastShown;

    let allowed = false;

    if (!lastShown) {
      allowed = true;
    } else if (frequency === 'always') {
      allowed = true;
    } else if (frequency === 'session') {
      allowed = !sessionStorage.getItem(keyShown);
    } else if (frequency.endsWith('h')) {
      const hours = parseInt(frequency);
      if (elapsed > hours * hour) allowed = true;
    } else if (frequency.endsWith('d')) {
      const days = parseInt(frequency);
      if (elapsed > days * day) allowed = true;
    }

    if (!allowed) return false;

    // If user closed before, and it's still within timeout, don't show
    if (userClosed === '1' && !allowed) return false;

    return true;
  }

  function markPopupShown(popupId, frequency) {
    const keyShown = `custompopup_${popupId}`;
    const now = Date.now();
    setStorage(keyShown, now);

    if (frequency === 'session') {
      sessionStorage.setItem(keyShown, now);
    }
  }

  // ===== Show Main Popup =====
  const popupId = 1;
  const frequency = '24h'; // Options: 'always', 'session', '2h', '3d'
  const modal = document.querySelector('#custom-popup-modal');

  if (modal && shouldShowPopup(popupId, frequency)) {
    modal.classList.add('visible');
    markPopupShown(popupId, frequency); // mark as shown now

    const closeBtn = modal.querySelector('.custom-popup-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        modal.classList.remove('visible');
        setStorage(`custompopup_closed_${popupId}`, '1');
      });
    }
  }

  // ===== Show Newsletter Modal (optional) =====
  setTimeout(function () {
    const newsletterModal = document.getElementById('newsletterModal');
    if (newsletterModal) {
      $('#newsletterModal').modal('show');

      setTimeout(() => {
        const buttons = newsletterModal.querySelectorAll('[name="submitNewsletter"]');
        buttons.forEach(btn => {
          if (btn.tagName.toLowerCase() === 'input') {
            btn.value = typeof custom_newsletter_button !== 'undefined' ? custom_newsletter_button : 'Subscribe';
          } else {
            btn.textContent = typeof custom_newsletter_button !== 'undefined' ? custom_newsletter_button : 'Subscribe';
          }
        });
      }, 300);

      // Optional: close tracking
      newsletterModal.querySelectorAll('[data-dismiss="modal"], .close').forEach(btn => {
        btn.addEventListener('click', () => {
          setStorage('newsletter_closed', '1');
        });
      });
    }
  }, 500);
});