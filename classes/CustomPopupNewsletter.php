<?php
class CustomPopupNewsletter extends ObjectModel
{
    public $id_custompopup_newsletter;
    public $active;
    public $title;
    public $content;
    public $button_text;
    public $start_datetime;
    public $end_datetime;

    public static $definition = [
        'table' => 'custompopup_newsletter',
        'primary' => 'id_custompopup_newsletter',
        'fields' => [
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'content' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true],
            'button_text' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
            'start_datetime' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'end_datetime' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function __construct($id = null, $id_lang = null)
    {
        parent::__construct($id, $id_lang);
    }

    public function save($null_values = false, $auto_date = true)
    {
        return parent::save($null_values, $auto_date);
    }
}