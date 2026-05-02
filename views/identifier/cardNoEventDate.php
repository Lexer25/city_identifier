<?php
// identifier/views/identifier/cardNoEventDate.php
/**
 * Отображение карт, не имеющих отметки о проходе после указанной даты
 */

// Считаем разницу с указанной датой для отображения в заголовке
$eventDate = Arr::get($arg, 'event_date');
$diff = Date::span(strtotime($eventDate), time(), 'years,months,days');

$diffText = '';
if ($diff['years'] > 0) {
    $diffText .= $diff['years'] . 'г. ';
}
if ($diff['months'] > 0) {
    $diffText .= $diff['months'] . 'мес. ';
}
if ($diff['days'] > 0 || empty($diffText)) {
    $diffText .= $diff['days'] . 'дн.';
}

// Определение заголовков таблицы
$headers = array(
    'ID_CARD' => 'ID карты',
    'TIMESTART' => 'Начало действия',
    'TIMEEND' => 'Окончание действия',
    'ACTIVE' => 'Активна',
    'IDTYPE' => 'Тип карты',
    'CREATEDAT' => 'Дата создания',
    'ID_PEP' => 'ID сотрудника',
    'FIO' => 'ФИО',
    'ID_ORG' => 'ID организации',
    'ORGNAME' => 'Название организации',
    'ID_PARENT' => 'ID родительской организации',
    'ORGPARENTNAME' => 'Родительская организация',
    'lastevent' => 'Последнее событие'
);

$title = __('Список карт, не имеющих отметки о проходе до указанной даты :date (:diff)', array(
    ':date' => $eventDate,
    ':diff' => trim($diffText)
));

$custom_info = '<p><strong>Примечание:</strong> В данный список включены карты, последний проход по которым был до ' . 
               htmlspecialchars($eventDate) . ' (' . trim($diffText) . ' назад).</p>';

// Подключаем общий шаблон таблицы
include('_table.php');
?>