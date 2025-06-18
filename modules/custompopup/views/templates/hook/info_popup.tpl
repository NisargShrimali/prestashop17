{foreach from=$popups item=popup}
    <div class="custom-info-popup"
         id="popup-{$popup.id|escape:'html':'UTF-8'}"
         data-popup-id="{$popup.id|escape:'html':'UTF-8'}"
         data-trigger="{$popup.trigger|default:'onload'|escape:'html':'UTF-8'}"
         data-trigger-value="{$popup.trigger_value|default:'50'|escape:'html':'UTF-8'}"
         data-frequency="{$popup.display_frequency|default:'always'|escape:'html':'UTF-8'}"
         data-frequency-value="{$popup.frequency_value|default:'0'|escape:'html':'UTF-8'}"
         data-animation="{$popup.animation|default:'fade'|escape:'html':'UTF-8'}"
         style="display: none;">

        <div class="popup-overlay">
            <div class="popup-content {$popup.modal_size|default:'medium'} {if $popup.background_shadow}shadow{/if}"
                 style="background-color: {$popup.background_color|default:'#ffffff'};">

                <span class="popup-close">×</span>
                <h2>{$popup.title|escape:'htmlall':'UTF-8'}</h2>

                {if $popup.image}
                    <div class="popup-inner">
                        <div class="popup-image-wrapper">
                            <img src="{$popup.image|escape:'htmlall':'UTF-8'}"
                                 alt="{$popup.title|escape:'htmlall':'UTF-8'}"
                                 class="popup-image">
                        </div>
                        <div class="popup-content-wrapper">
                            <div class="popup-body">{$popup.content nofilter}</div>
                        </div>
                    </div>
                {else}
                    <div class="popup-body">{$popup.content nofilter}</div>
                {/if}
            </div>
        </div>
    </div>
{/foreach}
