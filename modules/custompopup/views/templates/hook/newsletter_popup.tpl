{if $newsletter && $newsletter->active}
<div id="custompopup-newsletter-overlay" class="custompopup-newsletter-overlay">
  <div class="custompopup-newsletter-modal">
    <span class="custompopup-newsletter-close">&times;</span>
    <h3>{$newsletter->title|escape:'htmlall':'UTF-8'}</h3>
    <p>{$newsletter->content|escape:'htmlall':'UTF-8'}</p>

    <form method="post" id="custompopup-newsletter-form">
      {if $newsletter->show_name_fields}
        <input type="text" name="first_name" placeholder="First Name" />
        <input type="text" name="last_name" placeholder="Last Name" />
      {/if}
      <input type="email" name="email" placeholder="Enter email address..." required />
      <label>
        <input type="checkbox" name="privacy" required />
        By selecting continue you confirm that you have read our <a href="/privacy-policy" target="_blank">data protection information</a> and accepted our <a href="/terms-and-conditions" target="_blank">general terms and conditions</a>.
      </label>
      <p><small>Fields marked with asterisks (*) are required.</small></p>
      <button type="submit">{$newsletter->button_text|escape:'htmlall':'UTF-8'}</button>
    </form>
  </div>
</div>
{/if}
