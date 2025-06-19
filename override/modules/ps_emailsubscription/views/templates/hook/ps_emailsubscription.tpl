
<div class="email_subscription block_newsletter" id="blockEmailSubscription_{$hookName}">
  <h4>{l s='Newsletter' d='Modules.Emailsubscription.Shop'}</h4>
  {if $msg}
    <p class="notification {if $nw_error}notification-error{else}notification-success{/if}">{$msg}</p>
  {/if}
  <form action="{$urls.current_url}#blockEmailSubscription_{$hookName}" method="post">
    <input type="email" name="email" value="{$value}" placeholder="{l s='Your e-mail' d='Modules.Emailsubscription.Shop'}" required />
    {if $conditions}
      <p>{$conditions}</p>
    {/if}
    {hook h='displayNewsletterRegistration'}
    <input type="hidden" value="{$hookName}" name="blockHookName" />
    <input type="submit" value="ok" name="submitNewsletter" />
    {hook h='displayGDPRConsent' id_module=$id_module}
    <input type="hidden" name="action" value="0" />
  </form>
</div>
