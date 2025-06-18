$(document).ready(function () {
    console.log('Custom Popup Script Loaded at', new Date().toLocaleString('en-US', { timeZone: 'Asia/Kolkata' }));
    console.log('jQuery available:', typeof $);

    // Global scroll event tests
    $(window).on('scroll', function () {
        console.log('Global window scroll event fired, scrollTop:', $(window).scrollTop());
    });
    $(document).on('scroll', function () {
        console.log('Global document scroll event fired, scrollTop:', $(window).scrollTop());
    });

    const $popups = $('.custom-info-popup');

    console.log(`Found ${$popups.length} popup(s)`);
    $popups.each(function () {
        const $popup = $(this);
        console.log(`Popup ID: ${$popup.data('popup-id')}, Trigger: ${$popup.data('trigger')}, Title: ${$popup.find('h2').text()}`);
    });

    if (!$popups.length) {
        console.log('No info popups found');
        return;
    }

    let activePopup = null;

    function setPopupLastShown(popupId) {
        localStorage.setItem(`custom_popup_${popupId}_last_shown`, Date.now().toString());
    }

    function canShowPopup($popup) {
        const id = $popup.data('popup-id');
        let frequency = $popup.data('frequency') || 'always';
        let value = parseInt($popup.data('frequency-value')) || 0;
        const lastShownKey = `custom_popup_${id}_last_shown`;
        const lastShown = localStorage.getItem(lastShownKey);
        const now = Date.now();

        console.log(`Checking popup ID ${id}: frequency=${frequency}, value=${value}, lastShown=${lastShown}`);

        if ((frequency === 'hours' || frequency === 'days') && value === 0) {
            frequency = 'always';
        }

        if (frequency === 'always') {
            console.log(`Popup ID ${id}: Allowed (always)`);
            return true;
        }

        if (frequency === 'session') {
            if (!sessionStorage.getItem(`custom_popup_${id}_shown`)) {
                sessionStorage.setItem(`custom_popup_${id}_shown`, '1');
                console.log(`Popup ID ${id}: Allowed (first time in session)`);
                return true;
            }
            console.log(`Popup ID ${id}: Blocked (already shown in session)`);
            return false;
        }

        if (!lastShown) {
            console.log(`Popup ID ${id}: Allowed (never shown before)`);
            return true;
        }

        const elapsed = now - parseInt(lastShown);

        if (frequency === 'hours') {
            const allowed = elapsed >= value * 60 * 60 * 1000;
            console.log(`Popup ID ${id}: hours check, elapsed=${elapsed}, threshold=${value * 60 * 60 * 1000}, allowed=${allowed}`);
            return allowed;
        }

        if (frequency === 'days') {
            const allowed = elapsed >= value * 24 * 60 * 60 * 1000;
            console.log(`Popup ID ${id}: days check, elapsed=${elapsed}, threshold=${value * 24 * 60 * 60 * 1000}, allowed=${allowed}`);
            return allowed;
        }

        console.log(`Popup ID ${id}: Allowed (default)`);
        return true;
    }

    function showPopup($popup) {
        const popupId = $popup.data('popup-id');
        const animation = $popup.data('animation') || 'fade';

        const $content = $popup.find('.popup-content');
        console.log(`Attempting to show popup ID ${popupId}, current display: ${$popup.css('display')}`);
        $popup.show();

        $content.removeClass('popup-animate-fade popup-animate-slide popup-animate-zoom');

        if (animation === 'fade') {
            $content.addClass('popup-animate-fade');
        } else if (animation === 'slide') {
            $content.addClass('popup-animate-slide');
        } else if (animation === 'zoom') {
            $content.addClass('popup-animate-zoom');
        }

        setPopupLastShown(popupId);
        activePopup = $popup;
        console.log(`Showing popup ID ${popupId} with animation: ${animation}, new display: ${$popup.css('display')}`);
    }

    function closePopup($popup) {
        const $content = $popup.find('.popup-content');
        $content.removeClass('popup-animate-fade popup-animate-slide popup-animate-zoom');
        $popup.hide();
        activePopup = null;
        console.log(`Closed popup ID ${$popup.data('popup-id')}`);
    }

    $popups.each(function () {
        const $popup = $(this);
        const popupId = $popup.data('popup-id');
        const trigger = $popup.data('trigger') || 'onload';
        const triggerValue = $popup.data('trigger-value') || '50';

        console.log(`Processing Popup ID ${popupId}: trigger=${trigger}, value=${triggerValue}`);

        if (!canShowPopup($popup)) {
            console.log(`Popup ID ${popupId} skipped due to frequency`);
            return;
        }

        $popup.find('.popup-close').on('click', function (e) {
            e.preventDefault();
            console.log(`Close button clicked for popup ID ${popupId}`);
            closePopup($popup);
        });

        $popup.find('.popup-overlay').on('click', function (e) {
            if (e.target === this) {
                console.log(`Overlay clicked for popup ID ${popupId}`);
                closePopup($popup);
            }
        });

        if (trigger === 'onload') {
            console.log(`Popup ID ${popupId}: Triggering onload`);
            setTimeout(() => showPopup($popup), 100);
        } else if (trigger === 'scroll') {
    console.log(`Binding scroll event for Popup ID ${popupId}`);
    const documentHeight = $(document).height();
    const windowHeight = $(window).height();
    const triggerPercentage = parseInt(triggerValue) || 50;
    const triggerThreshold = documentHeight * (triggerPercentage / 100);

    console.log(`Page scrollable: documentHeight=${documentHeight}, windowHeight=${windowHeight}`);
    console.log(`Popup ID ${popupId}: Scroll trigger threshold=${triggerThreshold} (${triggerPercentage}%)`);

    let shown = false;
    let fallbackTriggered = false;

    const handleScroll = () => {
        const scrollTop = $(window).scrollTop();
        console.log(`Popup ID ${popupId}: scrollTop=${scrollTop}, shown=${shown}, activePopup=${!!activePopup}`);

        if (shown || activePopup) {
            console.log(`Popup ID ${popupId}: Skipped scroll check`);
            return;
        }

        if (scrollTop >= triggerThreshold) {
            console.log(`✅ Popup ID ${popupId}: Scroll threshold met, showing popup`);
            showPopup($popup);
            shown = true;
            $(window).off('scroll', handleScroll);
            $(document).off('scroll', handleScroll);
        }
    };

    $(window).on('scroll', handleScroll);
    $(document).on('scroll', handleScroll);

    // Fallback if no scroll activity happens in 5 seconds
    setTimeout(() => {
        const scrollTop = $(window).scrollTop();
        if (!shown && !activePopup && scrollTop < triggerThreshold) {
            console.log(`⚠️ Popup ID ${popupId}: Fallback triggered (no scroll after 5s)`);
            showPopup($popup);
            shown = true;
            fallbackTriggered = true;
        } else {
            console.log(`⏩ Popup ID ${popupId}: Fallback skipped, scrollTop=${scrollTop}`);
        }
    }, 5000);


        } else if (trigger === 'exit') {
            console.log(`Binding exit event for Popup ID ${popupId}`);
            let shown = false;
            $(document).on('mouseleave', function (e) {
                if (shown || activePopup) return;

                if (e.clientY <= 0) {
                    showPopup($popup);
                    shown = true;
                    console.log(`Popup ID ${popupId}: Triggered on exit`);
                }
            });
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && activePopup) {
            console.log('Escape key pressed, closing active popup');
            closePopup(activePopup);
        }
        if (e.key === 'p') {
            const $popup = $('.custom-info-popup[data-popup-id="1"]');
            if ($popup.length) {
                console.log('P key pressed, showing popup ID 1');
                showPopup($popup);
            } else {
                console.log('P key pressed, but no popup with ID 1 found');
                $popups.each(function () {
                    console.log('Available popup ID:', $(this).data('popup-id'));
                });
            }
        }
    });
});