<?php

class AdminCustomPopupController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'custompopup_info';
        $this->className = 'CustomPopupInfo';
        $this->identifier = 'id_custompopup_info';
        $this->lang = false;
        $this->context = Context::getContext();
        $this->module = Module::getInstanceByName('custompopup');

        parent::__construct();

        $this->fields_list = [
            'id_custompopup_info' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ],
            'title' => [
                'title' => $this->l('Title'),
                'align' => 'left'
            ],
            'active' => [
                'title' => $this->l('Status'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'callback' => 'displayStatus'
            ],
            'audience' => [
                'title' => $this->l('Audience'),
                'align' => 'center',
                'callback' => 'displayAudience'
            ],
            'display_pages' => [
                'title' => $this->l('Display Pages'),
                'align' => 'left',
                'callback' => 'displayPages'
            ],
            'display_frequency' => [
                'title' => $this->l('Frequency'),
                'align' => 'left',
                'callback' => 'displayFrequency'
            ],
            'start_datetime' => [
                'title' => $this->l('Schedule'),
                'align' => 'left',
                'callback' => 'displaySchedule'
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'icon' => 'icon-trash',
                'confirm' => $this->l('Delete selected items?')
            ],
        ];

        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');
        $this->addRowAction('preview');
    }

    public function displayStatus($status)
    {
        return $status ? $this->l('Active') : $this->l('Inactive');
    }

    public function displayAudience($value)
    {
        return $value ? $this->l('Registered Users') : $this->l('All');
    }

    public function displayPages($pages)
    {
        if (!$pages) {
            return $this->l('All Pages');
        }
        $pages_array = json_decode($pages, true);
        if (!is_array($pages_array)) {
            return $pages;
        }

        $display = [];
        foreach ($pages_array as $page) {
            if ($page === 'all_categories') {
                $display[] = $this->l('All Categories');
            } elseif ($page === 'all_products') {
                $display[] = $this->l('All Products');
            } else {
                $display[] = $page;
            }
        }
        return implode(', ', $display);
    }

    public function displayFrequency($value, $row)
    {
        $frequency_value = isset($row['frequency_value']) ? (int)$row['frequency_value'] : 0;
        switch ($value) {
            case 'session':
                return $this->l('Per Session');
            case 'hours':
                return sprintf($this->l('Every %d Hour(s)'), $frequency_value);
            case 'days':
                return sprintf($this->l('Every %d Day(s)'), $frequency_value);
            case 'always':
                return $this->l('Always');
            default:
                return $value;
        }
    }

    public function displaySchedule($value, $row)
    {
        $start = $value ? (new DateTime($value, new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Asia/Kolkata'))
            ->format('Y-m-d H:i') : '';
        $end = isset($row['end_datetime']) && $row['end_datetime'] ? (new DateTime($row['end_datetime'], new DateTimeZone('UTC')))
            ->setTimezone(new DateTimeZone('Asia/Kolkata'))
            ->format('Y-m-d H:i') : '';
        if ($start && $end) {
            return sprintf('%s to %s', $start, $end);
        } elseif ($start) {
            return sprintf($this->l('From %s'), $start);
        } elseif ($end) {
            return sprintf($this->l('Until %s'), $end);
        }
        return $this->l('No Schedule');
    }

    public function processPreview()
    {
        $id = (int)Tools::getValue('previewcustompopup_info');

        // Debug: Log the ID being requested
        file_put_contents(_PS_ROOT_DIR_ . '/var/logs/preview_debug.log', "Preview ID: $id\n", FILE_APPEND);

        if (!$id) {
            file_put_contents(_PS_ROOT_DIR_ . '/var/logs/preview_debug.log', "Error: No ID provided\n", FILE_APPEND);
            echo "Error: No popup ID provided.";
            exit;
        }

        $popup = new CustomPopupInfo($id);
        if (!Validate::isLoadedObject($popup)) {
            file_put_contents(_PS_ROOT_DIR_ . '/var/logs/preview_debug.log', "Error: Invalid popup ID $id\n", FILE_APPEND);
            echo "Error: Invalid popup ID.";
            exit;
        }

        // Debug: Log the popup data
        file_put_contents(_PS_ROOT_DIR_ . '/var/logs/preview_debug.log', "Popup Data: " . print_r($popup, true) . "\n", FILE_APPEND);

        try {
            $html = '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Popup Preview</title>
                <style>
                    body {
                        margin: 0;
                        font-family: Arial, sans-serif;
                        background: rgba(0, 0, 0, 0.5);
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 100vh;
                        overflow: auto;
                    }
                    .popup-overlay {
                        position: fixed;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        background: rgba(0, 0, 0, 0.6);
                        z-index: 999;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                    }
                    .popup-container {
                        position: relative;
                        background-color: ' . htmlspecialchars($popup->background_color ?: '#ffffff', ENT_QUOTES, 'UTF-8') . ';
                        padding: 30px;
                        border-radius: 10px;
                        box-shadow: ' . ($popup->background_shadow ? '0 6px 12px rgba(0, 0, 0, 0.3)' : 'none') . ';
                        max-width: ' . ($popup->modal_size === 'small' ? '600px' : ($popup->modal_size === 'medium' ? '800px' : '1000px')) . ';
                        width: 90%;
                        box-sizing: border-box;
                        z-index: 1000;
                        overflow: auto;
                        max-height: 90vh;
                        display: flex;
                        flex-wrap: wrap;
                        align-items: flex-start;
                    }
                    .popup-left {
                        flex: 1;
                        min-width: 200px;
                        margin-right: 20px;
                    }
                    .popup-right {
                        flex: 1;
                        min-width: 200px;
                    }
                    .popup-title {
                        font-size: 28px;
                        font-weight: bold;
                        margin: 0 0 15px;
                        color: #222;
                        text-align: left;
                        line-height: 1.2;
                    }
                    .popup-content {
                        font-size: 16px;
                        line-height: 1.6;
                        color: #333;
                        text-align: left;
                        margin: 0;
                    }
                    .popup-content img {
                        max-width: 100%;
                        height: auto;
                        border-radius: 5px;
                    }
                    .popup-image {
                        max-width: 100%;
                        height: auto;
                        display: block;
                        margin: 0 0 15px;
                        border-radius: 5px;
                    }
                    .popup-close {
                        position: absolute;
                        top: 15px;
                        right: 20px;
                        font-size: 30px;
                        color: #555;
                        cursor: pointer;
                        transition: color 0.3s ease;
                        line-height: 1;
                    }
                    .popup-close:hover {
                        color: #000;
                    }
                    @media (max-width: 600px) {
                        .popup-container {
                            flex-direction: column;
                        }
                        .popup-left, .popup-right {
                            margin-right: 0;
                            min-width: 100%;
                        }
                    }
                    @keyframes fade {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    @keyframes slide {
                        from { transform: translateY(-50%); }
                        to { transform: translateY(0); }
                    }
                    @keyframes zoom {
                        from { transform: scale(0.7); }
                        to { transform: scale(1); }
                    }
                    .popup-container {
                        animation: ' . htmlspecialchars($popup->animation ?: 'fade', ENT_QUOTES, 'UTF-8') . ' 0.5s ease-in-out;
                    }
                </style>
            </head>
            <body>
                <div class="popup-overlay">
                    <div class="popup-container">
                        <span class="popup-close" onclick="window.close()">×</span>
                        <div class="popup-left">
                            ' . ($popup->title ? '<h2 class="popup-title">' . htmlspecialchars($popup->title, ENT_QUOTES, 'UTF-8') . '</h2>' : '') . '
                            ' . ($popup->image && file_exists(_PS_ROOT_DIR_ . '/' . $popup->image) ? '<img src="' . __PS_BASE_URI__ . htmlspecialchars($popup->image, ENT_QUOTES, 'UTF-8') . '" class="popup-image" alt="Popup Image">' : '') . '
                        </div>
                        <div class="popup-right">
                            <div class="popup-content">' . htmlspecialchars_decode($popup->content, ENT_QUOTES) . '</div>
                        </div>
                    </div>
                </div>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        // Ensure popup is visible on load
                        const overlay = document.querySelector(".popup-overlay");
                        overlay.style.display = "flex";

                        // Close popup on overlay click (outside popup-container)
                        overlay.addEventListener("click", function(e) {
                            if (e.target === overlay) {
                                window.close();
                            }
                        });

                        // Prevent clicks inside popup-container from closing the popup
                        document.querySelector(".popup-container").addEventListener("click", function(e) {
                            e.stopPropagation();
                        });
                    });
                </script>
            </body>
            </html>';

            echo $html;
            exit;
        } catch (Exception $e) {
            file_put_contents(_PS_ROOT_DIR_ . '/var/logs/preview_debug.log', "Error generating HTML: " . $e->getMessage() . "\n", FILE_APPEND);
            echo "Error generating preview: " . $e->getMessage();
            exit;
        }
    }

    public function displayPreviewLink($token, $id)
    {
        $url = $this->context->link->getAdminLink('AdminCustomPopup', true, [], ['previewcustompopup_info' => (int)$id]);
        return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" title="' . $this->l('Preview') . '" target="_blank">' .
               '<i class="icon-eye"></i> ' . $this->l('Preview') . '</a>';
    }

    public function renderForm()
    {
        $categories = Category::getCategories((int)$this->context->language->id, true, false);
        $category_options = [
            ['id' => 'all_categories', 'name' => $this->l('All Categories')],
        ];
        foreach ($categories as $cat) {
            $category_options[] = [
                'id' => 'category_' . $cat['id_category'],
                'name' => $cat['name'],
            ];
        }

        $products = Product::getProducts((int)$this->context->language->id, 0, 0, 'id_product', 'ASC');
        $product_options = [
            ['id' => 'all_products', 'name' => $this->l('All Products')],
        ];
        foreach ($products as $prod) {
            $product_options[] = [
                'id' => 'product_' . $prod['id_product'],
                'name' => $prod['name'],
            ];
        }

        $popup = new CustomPopupInfo((int)Tools::getValue('id_custompopup_info'));
        $display_pages = is_string($popup->display_pages) ? json_decode($popup->display_pages, true) : [];

        $fields_form = [
            'form' => [
                'legend' => ['title' => $this->l('Info Popup Settings')],
                'input' => [
                    ['type' => 'hidden', 'name' => 'id_custompopup_info'],
                    ['type' => 'text', 'label' => $this->l('Title'), 'name' => 'title', 'required' => true],
                    ['type' => 'switch', 'label' => $this->l('Active'), 'name' => 'active', 'is_bool' => true, 'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
                    ['type' => 'switch', 'label' => $this->l('Background Shadow'), 'name' => 'background_shadow', 'is_bool' => true, 'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
                    ['type' => 'textarea', 'label' => $this->l('Content'), 'name' => 'content', 'autoload_rte' => true, 'required' => true],
                    ['type' => 'file', 'label' => $this->l('Image'), 'name' => 'image', 'desc' => $this->l('Upload an image.')],
                    ['type' => 'hidden', 'name' => 'existing_image'],
                    ['type' => 'free', 'label' => $this->l('Preview'), 'name' => 'image_preview'],
                    ['type' => 'select', 'label' => $this->l('Animation'), 'name' => 'animation', 'options' => ['query' => [['id' => 'fade', 'name' => $this->l('Fade')], ['id' => 'slide', 'name' => $this->l('Slide')], ['id' => 'zoom', 'name' => $this->l('Zoom')]], 'id' => 'id', 'name' => 'name']],
                    ['type' => 'select', 'label' => $this->l('Modal Size'), 'name' => 'modal_size', 'options' => ['query' => [['id' => 'small', 'name' => $this->l('Small')], ['id' => 'medium', 'name' => $this->l('Medium')], ['id' => 'large', 'name' => $this->l('Large')]], 'id' => 'id', 'name' => 'name']],
                    ['type' => 'color', 'label' => $this->l('Background Color'), 'name' => 'background_color'],
                    ['type' => 'switch', 'label' => $this->l('Audience (Registered Users Only)'), 'name' => 'audience', 'is_bool' => true, 'values' => [['id' => 'on', 'value' => 1, 'label' => $this->l('Yes')], ['id' => 'off', 'value' => 0, 'label' => $this->l('No')]]],
                    ['type' => 'select', 'label' => $this->l('Trigger'), 'name' => 'trigger', 'options' => ['query' => [['id' => 'onload', 'name' => $this->l('On Page Load')], ['id' => 'scroll', 'name' => $this->l('On Scroll')], ['id' => 'exit', 'name' => $this->l('On Exit Intent')]], 'id' => 'id', 'name' => 'name']],
                    ['type' => 'text', 'label' => $this->l('Trigger Value'), 'name' => 'trigger_value'],
                    ['type' => 'select', 'label' => $this->l('Display Frequency'), 'name' => 'display_frequency', 'options' => ['query' => [['id' => 'session', 'name' => $this->l('Per Session')], ['id' => 'hours', 'name' => $this->l('Every X Hours')], ['id' => 'days', 'name' => $this->l('Every X Days')], ['id' => 'always', 'name' => $this->l('Always')]], 'id' => 'id', 'name' => 'name']],
                    ['type' => 'text', 'label' => $this->l('Frequency Value'), 'name' => 'frequency_value'],
                    ['type' => 'datetime', 'label' => $this->l('Start Date & Time'), 'name' => 'start_datetime'],
                    ['type' => 'datetime', 'label' => $this->l('End Date & Time'), 'name' => 'end_datetime'],
                    ['type' => 'select', 'multiple' => true, 'label' => $this->l('Display Pages'), 'name' => 'display_pages[]', 'options' => ['query' => array_merge($category_options, $product_options), 'id' => 'id', 'name' => 'name']],
                ],
                'submit' => ['title' => $this->l('Save'), 'name' => 'submitInfoPopup'],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitInfoPopup';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminCustomPopup');
        $helper->token = Tools::getAdminTokenLite('AdminCustomPopup');

        $helper->fields_value = [
            'id_custompopup_info' => $popup->id,
            'title' => $popup->title,
            'active' => $popup->active,
            'background_shadow' => $popup->background_shadow,
            'content' => $popup->content,
            'image' => '',
            'existing_image' => $popup->image,
            'image_preview' => $popup->image ? '<img src="' . __PS_BASE_URI__ . $popup->image . '" height="100" style="margin-top:10px;border:1px solid #ccc;padding:5px;">' : '',
            'animation' => $popup->animation,
            'modal_size' => $popup->modal_size,
            'background_color' => $popup->background_color,
            'audience' => $popup->audience,
            'trigger' => $popup->trigger,
            'trigger_value' => $popup->trigger_value,
            'display_frequency' => $popup->display_frequency,
            'frequency_value' => $popup->frequency_value,
            'start_datetime' => $popup->start_datetime,
            'end_datetime' => $popup->end_datetime,
            'display_pages[]' => $display_pages ?: [],
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitInfoPopup')) {
            $id = (int)Tools::getValue('id_custompopup_info');
            $popup = $id ? new CustomPopupInfo($id) : new CustomPopupInfo();

            $popup->title = Tools::getValue('title');
            $popup->active = (int)Tools::getValue('active');
            $popup->background_shadow = (int)Tools::getValue('background_shadow');
            $popup->content = Tools::getValue('content');
            $popup->animation = Tools::getValue('animation');
            $popup->modal_size = Tools::getValue('modal_size');
            $popup->background_color = Tools::getValue('background_color');
            $popup->audience = (int)Tools::getValue('audience');
            $popup->trigger = Tools::getValue('trigger');
            $popup->trigger_value = Tools::getValue('trigger_value');
            $popup->display_frequency = Tools::getValue('display_frequency');
            $popup->frequency_value = (int)Tools::getValue('frequency_value');
            $start = Tools::getValue('start_datetime');
            $end = Tools::getValue('end_datetime');

            $popup->start_datetime = !empty($start) ? $start : null;
            $popup->end_datetime = !empty($end) ? $end : null;

            $raw_pages = Tools::getValue('display_pages');
$normalized_pages = [];

foreach ($raw_pages as $page) {
    // Convert category_2 (Home) to 'home'
    if ($page === 'category_2') {
        $normalized_pages[] = 'home';
    } else {
        $normalized_pages[] = $page;
    }
}

$popup->display_pages = json_encode($normalized_pages);


            if (isset($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
                $image_path = 'modules/custompopup/views/img/' . basename($_FILES['image']['name']);
                if (move_uploaded_file($_FILES['image']['tmp_name'], _PS_ROOT_DIR_ . '/' . $image_path)) {
                    $popup->image = $image_path;
                } else {
                    $this->errors[] = $this->l('Failed to upload image.');
                }
            } elseif (Tools::getValue('existing_image')) {
                $popup->image = Tools::getValue('existing_image');
            }

            if ($popup->save()) {
                $this->confirmations[] = $this->l('Popup saved successfully.');
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomPopup'));
            } else {
                $this->errors[] = $this->l('An error occurred while saving the popup.');
            }
        }

        if (Tools::getIsset('previewcustompopup_info')) {
            return $this->processPreview();
        }

        if (Tools::getIsset('duplicatecustompopup_info')) {
            return $this->processDuplicate();
        }

        if (Tools::getIsset('deletecustompopup_info')) {
            return $this->processDelete();
        }

        if (Tools::isSubmit('submitBulkdeletecustompopup_info')) {
            return $this->processBulkDelete();
        }

        return parent::postProcess();
    }

    public function processDelete()
    {
        $id = (int)Tools::getValue('id_custompopup_info');
        if ($id) {
            $popup = new CustomPopupInfo($id);
            if (Validate::isLoadedObject($popup)) {
                if ($popup->delete()) {
                    $this->confirmations[] = $this->l('Popup deleted successfully.');
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomPopup'));
                } else {
                    $this->errors[] = $this->l('An error occurred while deleting the popup.');
                }
            } else {
                $this->errors[] = $this->l('Invalid popup ID.');
            }
        }
        return parent::processDelete();
    }

    public function processDuplicate()
    {
        $id = (int)Tools::getValue('id_custompopup_info');
        if ($id) {
            $popup = new CustomPopupInfo($id);
            if (Validate::isLoadedObject($popup)) {
                $new_popup = new CustomPopupInfo();
                $new_popup->title = $popup->title . ' (Copy)';
                $new_popup->active = $popup->active;
                $new_popup->background_shadow = $popup->background_shadow;
                $new_popup->content = $popup->content;
                $new_popup->image = $popup->image;
                $new_popup->animation = $popup->animation;
                $new_popup->modal_size = $popup->modal_size;
                $new_popup->background_color = $popup->background_color;
                $new_popup->audience = $popup->audience;
                $new_popup->trigger = $popup->trigger;
                $new_popup->trigger_value = $popup->trigger_value;
                $new_popup->display_frequency = $popup->display_frequency;
                $new_popup->frequency_value = $popup->frequency_value;
                $new_popup->start_datetime = $popup->start_datetime;
                $new_popup->end_datetime = $popup->end_datetime;
                $new_popup->display_pages = $popup->display_pages;

                if ($new_popup->save()) {
                    $this->confirmations[] = $this->l('Popup duplicated successfully.');
                    Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomPopup'));
                } else {
                    $this->errors[] = $this->l('An error occurred while duplicating the popup.');
                }
            } else {
                $this->errors[] = $this->l('Invalid popup ID.');
            }
        }

        return parent::processDuplicate();
    }

    public function processBulkDelete()
    {
        $ids = Tools::getValue($this->table . 'Box');
        if (is_array($ids) && !empty($ids)) {
            foreach ($ids as $id) {
                $popup = new CustomPopupInfo((int)$id);
                if (Validate::isLoadedObject($popup)) {
                    $popup->delete();
                }
            }
            $this->confirmations[] = $this->l('Selected popups deleted successfully.');
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminCustomPopup'));
        } else {
            $this->errors[] = $this->l('No popups selected.');
        }
        return parent::processBulkDelete();
    }
}