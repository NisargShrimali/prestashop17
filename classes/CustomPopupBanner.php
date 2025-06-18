<?php

     if (!class_exists('CustomPopupBanner')) {
         class CustomPopupBanner extends ObjectModel
         {
             public $id_custompopup_banner;
             public $active;
             public $closable;
             public $background_color;
             public $font_color;
             public $marquee;
             public $content;
             public $start_datetime;
             public $end_datetime;

             public static $definition = [
                 'table' => 'custompopup_banner',
                 'primary' => 'id_custompopup_banner',
                 'fields' => [
                     'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
                     'closable' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
                     'background_color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 32],
                     'font_color' => ['type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 32],
                     'marquee' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
                     'content' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml'],
                     'start_datetime' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
                     'end_datetime' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
                 ],
             ];
         }
     }