<?php defined('SYSPATH') or die('No direct script access.');


define('IDENTIFIER_VERSION', '4.6.5');//29/06/2026


Kohana::$config->load('menu')
     ->set('identifier', array(
        'title' => 'Идентификаторы',
        'url' => 'identifier',
        'icon' => 'fa-cog',
        'order' => 60,
        // P0: раздел доступен только авторизованным — не показываем его гостям
        'show' => array('logged_in' => TRUE),
    ));


