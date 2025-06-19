<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'ps_emailsubscription/ps_emailsubscription.php';

class Ps_EmailSubscriptionOverride extends Ps_EmailSubscription
{
    public function hookDisplayNewsletterRegistration($params)
    {
        $config = Db::getInstance()->getRow('SELECT * FROM '._DB_PREFIX_.'custompopup_newsletter WHERE active = 1');

        if (!$config) {
            return parent::hookDisplayNewsletterRegistration($params);
        }

        $this->context->smarty->assign([
            'custom_newsletter_title' => $config['title'],
            'custom_newsletter_content' => $config['content'],
            'custom_newsletter_button' => $config['button_text'],
        ]);
        $this->context->smarty->assign([
    'value' => '',
    'conditions' => '',
    'msg' => '',
    'nw_error' => false,
    'hookName' => 'displayFooter',
]);

        return $this->context->smarty->fetch(_PS_MODULE_DIR_.'custompopup/views/templates/hook/newsletter_popup.tpl');
    }
}
