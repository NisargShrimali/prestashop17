<?php

class CustomPopupInfo extends ObjectModel
{
    public $id_custompopup_info;
    public $active;
    public $background_shadow;
    public $title;
    public $content;
    public $image;
    public $animation;
    public $modal_size;
    public $background_color;
    public $audience;
    public $trigger;
    public $trigger_value;
    public $display_frequency;
    public $frequency_value;
    public $start_datetime;
    public $end_datetime;
    public $display_pages;

    public static $definition = [
        'table' => 'custompopup_info',
        'primary' => 'id_custompopup_info',
        'fields' => [
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'background_shadow' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'title' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true],
            'content' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'animation' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'modal_size' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'background_color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor'],
            'audience' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'trigger' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'trigger_value' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'display_frequency' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'frequency_value' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'start_datetime' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false],
            'end_datetime' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'required' => false],
            'display_pages' => ['type' => self::TYPE_STRING, 'validate' => 'isJson'],
        ],
    ];
}