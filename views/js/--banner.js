document.addEventListener('DOMContentLoaded', function() {
    console.log('Debug - Template Variables:', {
        popup_type: window.popup_type || 'Not defined',
        popup_marquee: window.popup_marquee || 'Not defined',
        popup_closable: window.popup_closable || 'Not defined',
        banner_start_datetime: window.banner_start_datetime || 'Not defined',
        banner_end_datetime: window.banner_end_datetime || 'Not defined'
    });

    const banner = document.querySelector('#custom-popup-banner');
    if (!banner) {
        console.error('Custom popup banner not found');
        return;
    }

    const container = banner.querySelector('.custom-popup-container');
    if (container) {
        console.log('Computed Styles (Container):', { fontColor: getComputedStyle(container).color });
        console.log('Inline Style (Container):', container.getAttribute('style'));
    }

    const marquee = banner.querySelector('.popup-marquee');
    if (marquee) {
        console.log('Marquee Inner HTML:', marquee.innerHTML);
        console.log('Computed Styles (Marquee):', { fontColor: getComputedStyle(marquee).color });
        // Check the first child element's color
        const marqueeChild = marquee.querySelector('*');
        if (marqueeChild) {
            console.log('Computed Styles (Marquee Child):', { fontColor: getComputedStyle(marqueeChild).color });
        }
    }

    const closeButton = banner.querySelector('.custom-popup-close');
    if (closeButton) {
        console.log('Computed Styles (Close Button):', { fontColor: getComputedStyle(closeButton).color });
    }

    banner.classList.add('active');
    console.log('Banner displayed');
});