{if $popup_type == 'banner'}
    <div id="custom-popup-banner">
        <div class="custom-popup-container"
             style="--popup-bg: {$popup_bg}; --popup-font: {$popup_font}; background-color: {$popup_bg|escape:'htmlall':'UTF-8'}; color: {$popup_font|escape:'htmlall':'UTF-8'} !important;">
             
            {if $popup_closable}
                <span class="custom-popup-close"
                      style="color: {$popup_font|escape:'htmlall':'UTF-8'} !important;"
                      onclick="this.parentElement.style.display='none';">×</span>
            {/if}

            {if $popup_marquee}
                <div class="popup-marquee">
                    <span style="color: {$popup_font|escape:'htmlall':'UTF-8'} !important;">{$popup_content nofilter}</span>
                </div>
            {else}
                <div class="custom-popup-content">
                    <span style="color: {$popup_font|escape:'htmlall':'UTF-8'} !important;">{$popup_content nofilter}</span>
                </div>
            {/if}
        </div>
    </div>
{/if}