<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/CustomPopupBanner.php';
require_once dirname(__FILE__) . '/classes/CustomPopupInfo.php';

class CustomPopup extends Module
{
    public function __construct()
    {
        $this->name = 'custompopup';
        $this->tab = 'front_office_features';
        $this->version = '1.1.0';
        $this->author = 'Icreative Technologies';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Custom Pop-Up Content');
        $this->description = $this->l('Display and manage customizable banner and info popups.');

        $this->tabs = [
            [
                'name' => 'Custom Popups',
                'class_name' => 'AdminCustomPopup',
                'parent_class_name' => 'AdminParentModulesSf',
                'visible' => true,
            ],
        ];
    }

    public function install()
    {
        return parent::install()
            && $this->installDB()
            && $this->registerHook('displayBanner')
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayFooter')
            && $this->installTab()
            && $this->fixDatabaseDates();
    }

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallDB()
            && $this->uninstallTab();
    }

    protected function installDB()
    {
        $db = Db::getInstance();

        $query1 = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."custompopup_banner` (
            `id_custompopup_banner` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `active` TINYINT(1) NOT NULL DEFAULT 0,
            `closable` TINYINT(1) NOT NULL DEFAULT 1,
            `background_color` VARCHAR(32),
            `font_color` VARCHAR(32),
            `marquee` TINYINT(1) NOT NULL DEFAULT 0,
            `content` TEXT,
            `start_datetime` DATETIME DEFAULT NULL,
            `end_datetime` DATETIME DEFAULT NULL
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4";

        $query2 = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."custompopup_info` (
            `id_custompopup_info` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `active` TINYINT(1) NOT NULL DEFAULT 0,
            `background_shadow` TINYINT(1) NOT NULL DEFAULT 0,
            `title` VARCHAR(255) NOT NULL,
            `content` TEXT,
            `image` VARCHAR(255),
            `animation` VARCHAR(64),
            `modal_size` ENUM('small', 'medium', 'large') DEFAULT 'medium',
            `background_color` VARCHAR(32),
            `audience` TINYINT(1) NOT NULL DEFAULT 0,
            `trigger` VARCHAR(64),
            `trigger_value` VARCHAR(64),
            `display_frequency` ENUM('session', 'hours', 'days', 'always') DEFAULT 'session',
            `frequency_value` INT DEFAULT 0,
            `start_datetime` DATETIME NULL DEFAULT NULL,
            `end_datetime` DATETIME NULL DEFAULT NULL,
            `display_pages` TEXT
        ) ENGINE="._MYSQL_ENGINE_." DEFAULT CHARSET=utf8mb4";

        return $db->execute($query1) && $db->execute($query2);
    }

    protected function uninstallDB()
    {
        $db = Db::getInstance();
        return $db->execute("DROP TABLE IF EXISTS `"._DB_PREFIX_."custompopup_banner`")
            && $db->execute("DROP TABLE IF EXISTS `"._DB_PREFIX_."custompopup_info`");
    }

    protected function fixDatabaseDates()
    {
        $db = Db::getInstance();
        return $db->execute('
            UPDATE ' . _DB_PREFIX_ . 'custompopup_banner
            SET start_datetime = NULL
            WHERE start_datetime = \'\' OR start_datetime = \'0000-00-00 00:00:00\'
        ') && $db->execute('
            UPDATE ' . _DB_PREFIX_ . 'custompopup_banner
            SET end_datetime = NULL
            WHERE end_datetime = \'\' OR end_datetime = \'0000-00-00 00:00:00\'
        ') && $db->execute('
            UPDATE ' . _DB_PREFIX_ . 'custompopup_info
            SET start_datetime = NULL
            WHERE start_datetime = \'\' OR start_datetime = \'0000-00-00 00:00:00\'
        ') && $db->execute('
            UPDATE ' . _DB_PREFIX_ . 'custompopup_info
            SET end_datetime = NULL
            WHERE end_datetime = \'\' OR end_datetime = \'0000-00-00 00:00:00\'
        ');
    }

    protected function installTab()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminCustomPopup';
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Custom Popups';
        }
        $tab->id_parent = (int)Tab::getIdFromClassName('AdminParentModulesSf');
        $tab->module = $this->name;
        return $tab->add();
    }

    protected function uninstallTab()
    {
        $id_tab = (int)Tab::getIdFromClassName('AdminCustomPopup');
        if ($id_tab) {
            $tab = new Tab($id_tab);
            return $tab->delete();
        }
        return true;
    }

    public function hookDisplayBanner()
    {
        PrestaShopLogger::addLog('hookDisplayBanner called', 1);

        $output = '';
        $now = new DateTime('now', new DateTimeZone('UTC'));
        $current_page = $this->getCurrentPage();

        $banner = $this->getOrCreateBanner();
        PrestaShopLogger::addLog('Banner active: ' . $banner->active, 1);
        PrestaShopLogger::addLog('Banner start_datetime: ' . ($banner->start_datetime ?: 'NULL'), 1);
        PrestaShopLogger::addLog('Banner end_datetime: ' . ($banner->end_datetime ?: 'NULL'), 1);

        if ($banner && (int)$banner->active === 1) {
            $start = $banner->start_datetime ? new DateTime($banner->start_datetime, new DateTimeZone('UTC')) : null;
            $end = $banner->end_datetime ? new DateTime($banner->end_datetime, new DateTimeZone('UTC')) : null;

            if (!($start && $now < $start) && !($end && $now > $end)) {
                PrestaShopLogger::addLog('Banner time constraints passed', 1);

                $this->context->smarty->assign([
                    'popup_content' => $banner->content,
                    'popup_bg' => $banner->background_color,
                    'popup_font' => $banner->font_color,
                    'popup_closable' => $banner->closable,
                    'popup_marquee' => $banner->marquee,
                    'popup_type' => 'banner',
                ]);

                Media::addJsDef([
                    'banner_start_datetime' => $start ? $start->format('Y-m-d\TH:i:s\Z') : null,
                    'banner_end_datetime' => $end ? $end->format('Y-m-d\TH:i:s\Z') : null,
                ]);

                $template_path = $this->local_path . 'views/templates/hook/popup.tpl';
                if (file_exists($template_path)) {
                    PrestaShopLogger::addLog('Rendering banner template: ' . $template_path, 1);
                    $output .= $this->display(__FILE__, 'views/templates/hook/popup.tpl');
                } else {
                    PrestaShopLogger::addLog('Banner template not found: ' . $template_path, 3);
                }
            } else {
                PrestaShopLogger::addLog('Banner not displayed due to time constraints', 1);
            }
        } else {
            PrestaShopLogger::addLog('Banner not active or invalid', 1);
        }

        return $output;
    }

    public function hookDisplayFooter()
    {
        PrestaShopLogger::addLog('hookDisplayFooter called', 1);

        $now = new DateTime('now', new DateTimeZone('UTC'));
        $current_page = $this->getCurrentPage();
        $is_logged = $this->context->customer->isLogged();

        $query = (new DbQuery())
            ->select('*')
            ->from('custompopup_info')
            ->where('active = 1');
        $popups = Db::getInstance()->executeS($query);

        if (!$popups || !is_array($popups)) {
            PrestaShopLogger::addLog('No active info popups found', 1);
            return '';
        }

        $filtered_popups = [];
        foreach ($popups as $popup_data) {
            $popup = new CustomPopupInfo((int)$popup_data['id_custompopup_info']);
            if (!Validate::isLoadedObject($popup)) {
                PrestaShopLogger::addLog('Failed to load popup ID: ' . $popup_data['id_custompopup_info'], 3);
                continue;
            }
            PrestaShopLogger::addLog('Processing popup ID: ' . $popup->id, 1);

            $start = null;
            if ($popup->start_datetime) {
                try {
                    $start = new DateTime($popup->start_datetime, new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Invalid start_datetime for popup ID ' . $popup->id . ': ' . $popup->start_datetime, 3);
                }
            }
            $end = null;
            if ($popup->end_datetime) {
                try {
                    $end = new DateTime($popup->end_datetime, new DateTimeZone('UTC'));
                } catch (Exception $e) {
                    PrestaShopLogger::addLog('Invalid end_datetime for popup ID ' . $popup->id . ': ' . $popup->end_datetime, 3);
                }
            }
            if (($start && $now < $start) || ($end && $now > $end)) {
                PrestaShopLogger::addLog('Popup ID ' . $popup->id . ' skipped due to schedule', 1);
                continue;
            }

            if ($popup->audience && !$is_logged) {
                PrestaShopLogger::addLog('Popup ID ' . $popup->id . ' skipped due to audience (not logged in)', 1);
                continue;
            }

            $display_pages = $popup->display_pages ? json_decode($popup->display_pages, true) : [];
            if (!empty($display_pages)) {
                $page_match = false;
                foreach ($display_pages as $page) {
                    if ($page === 'all_categories' && preg_match('/^category_/', $current_page)) {
                        $page_match = true;
                    } elseif ($page === 'all_products' && preg_match('/^product_/', $current_page)) {
                        $page_match = true;
                    } elseif ($page === $current_page) {
                        $page_match = true;
                    }
                }
                if (!$page_match) {
                    PrestaShopLogger::addLog('Popup ID ' . $popup->id . ' skipped due to page mismatch', 1);
                    continue;
                }
            }

            $filtered_popups[] = [
                'id' => $popup->id,
                'title' => $popup->title,
                'content' => $popup->content,
                'image' => $popup->image ? __PS_BASE_URI__ . $popup->image : '',
                'animation' => $popup->animation ?: 'fade',
                'modal_size' => $popup->modal_size ?: 'medium',
                'background_color' => $popup->background_color,
                'background_shadow' => $popup->background_shadow,
                'trigger' => $popup->trigger ?: 'onload',
                'trigger_value' => $popup->trigger_value ?: '',
                'display_frequency' => $popup->display_frequency ?: 'always',
                'frequency_value' => (int)$popup->frequency_value,
            ];
        }

        if (empty($filtered_popups)) {
            PrestaShopLogger::addLog('No popups matched after filtering', 1);
            return '';
        }

        $this->context->smarty->assign([
            'popups' => $filtered_popups,
            'module_dir' => $this->_path,
        ]);

        PrestaShopLogger::addLog('Rendering info popup template with ' . count($filtered_popups) . ' popups', 1);
        $output = $this->display(__FILE__, 'views/templates/hook/info_popup.tpl');
        $output .= '<div style="height: 3000px; background: #f0f0f0;">Test Scroll Content</div>';
        return $output;
    }

    public function hookDisplayHeader()
    {
        if ($this->context->controller instanceof FrontController) {
            $this->context->controller->registerStylesheet(
                'module-custompopup-style',
                'modules/' . $this->name . '/views/css/custom_popup.css',
                ['media' => 'all', 'priority' => 1000]
            );
            $this->context->controller->registerJavascript(
                'module-custompopup-js',
                'modules/' . $this->name . '/views/js/custom_popup.js',
                ['position' => 'bottom', 'priority' => 1000]
            );
            $this->context->controller->registerStylesheet(
                'custompopup-animate',
                'modules/' . $this->name . '/views/css/animate.min.css',
                ['media' => 'all', 'priority' => 80]
            );
        }
    }

    public function getContent()
    {
        $output = '';
        $banner = $this->getOrCreateBanner();

        if (Tools::isSubmit('submit_custompopup_banner')) {
            $errors = [];
            $backgroundColor = Tools::getValue('background_color');
            $fontColor = Tools::getValue('font_color');
            $content = Tools::getValue('content');
            $startDatetime = Tools::getValue('start_datetime');
            $endDatetime = Tools::getValue('end_datetime');

            if (empty($backgroundColor)) {
                $errors[] = $this->l('Background color is required.');
            } elseif (!Validate::isColor($backgroundColor)) {
                $errors[] = $this->l('Please enter a valid background color.');
            }

            if (empty($fontColor)) {
                $errors[] = $this->l('Font color is required.');
            } elseif (!Validate::isColor($fontColor)) {
                $errors[] = $this->l('Please enter a valid font color.');
            }

            if (empty($content)) {
                $errors[] = $this->l('Banner content is required.');
            }

            if (!empty($startDatetime) && !Validate::isDateFormat($startDatetime)) {
                $errors[] = $this->l('The start date format is invalid.');
            }

            if (!empty($endDatetime) && !Validate::isDateFormat($endDatetime)) {
                $errors[] = $this->l('The end date format is invalid.');
            }

            if (count($errors) === 0) {
                $banner->active = (int)Tools::getValue('active');
                $banner->closable = (int)Tools::getValue('closable');
                $banner->background_color = $backgroundColor;
                $banner->font_color = $fontColor;
                $banner->marquee = (int)Tools::getValue('marquee');
                $banner->content = $content;

                $timezone = new DateTimeZone('Asia/Kolkata');
                $utc = new DateTimeZone('UTC');
                $banner->start_datetime = $startDatetime ? (new DateTime($startDatetime, $timezone))->setTimezone($utc)->format('Y-m-d H:i:s') : null;
                $banner->end_datetime = $endDatetime ? (new DateTime($endDatetime, $timezone))->setTimezone($utc)->format('Y-m-d H:i:s') : null;

                if ($banner->save()) {
                    $output .= $this->displayConfirmation($this->l('Banner settings have been updated.'));
                } else {
                    $output .= $this->displayError($this->l('Could not update the banner settings.'));
                }
            } else {
                $output .= $this->displayError($errors);
            }
        }

        return $output . $this->renderForm($banner);
    }

    public function getOrCreateBanner()
    {
        $id = Db::getInstance()->getValue(
            (new DbQuery())
                ->select('id_custompopup_banner')
                ->from('custompopup_banner')
        );

        if ($id) {
            return new CustomPopupBanner((int)$id);
        }

        $banner = new CustomPopupBanner();
        $banner->active = 0;
        $banner->closable = 1;
        $banner->background_color = '#ffffff';
        $banner->font_color = '#000000';
        $banner->marquee = 0;
        $banner->content = '<p>Welcome to our store!</p>';
        $banner->add();

        return $banner;
    }

    protected function getCurrentPage()
    {
        $controller = $this->context->controller->php_self;
        $id_product = (int)Tools::getValue('id_product');
        $id_category = (int)Tools::getValue('id_category');

        if ($id_product) {
            return 'product_' . $id_product;
        } elseif ($id_category) {
            return 'category_' . $id_category;
        } elseif ($controller) {
            return $controller;
        }
        return 'index';
    }

    public function renderForm(CustomPopupBanner $banner)
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        $fields_form = [
            'form' => [
                'legend' => ['title' => $this->l('Banner Settings')],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Display Banner'),
                        'name' => 'active',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Closable'),
                        'name' => 'closable',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Background Color'),
                        'name' => 'background_color',
                        'required' => true,
                    ],
                    [
                        'type' => 'color',
                        'label' => $this->l('Font Color'),
                        'name' => 'font_color',
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Enable Marquee'),
                        'name' => 'marquee',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'label' => $this->l('Banner Content'),
                        'name' => 'content',
                        'autoload_rte' => true,
                        'required' => true,
                    ],
                    [
                        'type' => 'datetime',
                        'label' => $this->l('Start Date & Time'),
                        'name' => 'start_datetime',
                        'required' => false,
                        'desc' => 'Select when the banner should start displaying.',
                        'class' => 'fixed-width-xl datetimepicker'
                    ],
                    [
                        'type' => 'datetime',
                        'name' => 'end_datetime',
                        'label' => $this->l('End Date & Time'),
                        'required' => false,
                        'desc' => 'Select when the banner should stop displaying.',
                        'class' => 'fixed-width-xl datetimepicker'
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save Banner'),
                    'name' => 'submit_custompopup_banner',
                    'class' => 'btn btn-default pull-right',
                ]
            ]
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->submit_action = 'submit_custompopup_banner';

        $start_local = '';
        if ($banner->start_datetime) {
            try {
                $start_local = (new DateTime($banner->start_datetime, new DateTimeZone('UTC')))
                    ->setTimezone(new DateTimeZone('Asia/Kolkata'))
                    ->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                PrestaShopLogger::addLog('Invalid start_datetime for banner: ' . $banner->start_datetime, 3);
                $start_local = '';
            }
        }

        $end_local = '';
        if ($banner->end_datetime) {
            try {
                $end_local = (new DateTime($banner->end_datetime, new DateTimeZone('UTC')))
                    ->setTimezone(new DateTimeZone('Asia/Kolkata'))
                    ->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                PrestaShopLogger::addLog('Invalid end_datetime for banner: ' . $banner->end_datetime, 3);
                $end_local = '';
            }
        }

        $helper->fields_value = [
            'active' => $banner->active,
            'closable' => $banner->closable,
            'background_color' => $banner->background_color,
            'font_color' => $banner->font_color,
            'marquee' => $banner->marquee,
            'content' => $banner->content,
            'start_datetime' => $start_local,
            'end_datetime' => $end_local,
        ];

        return $helper->generateForm([$fields_form]);
    }
}