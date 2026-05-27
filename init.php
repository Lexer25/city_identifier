<?php defined('SYSPATH') or die('No direct script access.');


define('IDENTIFIER_VERSION', '4.6.2');//29/03/2026


Kohana::$config->load('menu')
     ->set('identifier', array(
        'title' => 'Идентификаторы',
        'url' => 'identifier',
        'icon' => 'fa-cog',
        'order' => 60,
       
    ));


