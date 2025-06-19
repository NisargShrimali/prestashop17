<!-- newsletter_popup.tpl -->
<div class="modal fade" id="newsletterModal" tabindex="-1" role="dialog" aria-labelledby="newsletterModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">

      <!-- Modal Header -->
      <div class="modal-header">
        <h5 class="modal-title" id="newsletterModalLabel">{$custom_newsletter_title|escape:'html'}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="{l s='Close'}">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <p class="mb-3">{$custom_newsletter_content nofilter}</p>

        <div class="newsletter-form-wrapper">
          {include file="module:ps_emailsubscription/views/templates/hook/ps_emailsubscription.tpl"}
        </div>
      </div>

    </div>
  </div>
</div>

{* Pass button text safely to JS *}
<script>
  var custom_newsletter_button = {$custom_newsletter_button|json_encode nofilter};
</script>
<script src="{$module_dir}views/js/newsletter.js"></script>
