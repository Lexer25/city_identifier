<?php
// identifier/views/identifier/peoplePassCount.php
/**
 * Отчёт: количество проходов (события типов 50 и 70) по каждому человеку за период.
 * Данные готовит Model_identifier::peoplePassCount().
 */

$dateFrom = Arr::get($arg, 'date_from');
$dateTo   = Arr::get($arg, 'date_to');

// Определение заголовков таблицы
$headers = array(
    'ID_PEP'     => 'ID сотрудника',
    'FIO'        => 'ФИО',
    'ORGNAME'    => 'Организация',
    'PASS_COUNT' => 'Количество проходов',
);

$title = __('Количество проходов по людям за период с :from по :to', array(
    ':from' => $dateFrom,
    ':to'   => $dateTo,
));

$custom_info = '<p><strong>Примечание:</strong> считаются все события типов 50 и 70 за период включительно. '
             . 'В таблицу включены все люди из базы СКУД; для людей без проходов указан 0.</p>';

// Это отчёт по людям, а не по картам: массовые действия и выбор карт здесь неприменимы
$show_actions = false;
$show_checkboxes = false;

// Подключаем общий шаблон таблицы
include('_table.php');
