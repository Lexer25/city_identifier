<?php
// identifier/views/identifier/_table.php
/**
 * Общий шаблон для отображения таблицы карт
 * Доступные переменные:
 * - $list: массив данных для отображения
 * - $headers: массив заголовков таблицы (ключ => отображаемое имя)
 * - $total_row_count: общее количество записей
 * - $rows_per_page: количество отображаемых строк
 * - $type: тип отчета (для экспорта)
 * - $arg: аргументы запроса (для передачи в форму экспорта)
 * - $show_actions: показывать ли панель действий (по умолчанию true)
 * - $custom_info: дополнительная информация над таблицей (опционально)
 * - $title: заголовок таблицы (опционально)
 */
 
ini_set('memory_limit', '256M');
?>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title">
            <?php echo isset($title) ? htmlspecialchars($title) : htmlspecialchars(__('Список карт')); ?>
        </h3>
    </div>
    
    <div class="panel-body">
        <!-- Информационная панель -->
        <div class="alert alert-info">
            <?php 
            echo __('Всего найдено записей') . ' ' . (isset($total_row_count) ? $total_row_count : '0');
            echo '<br>';
            
            $show_row = 0;
            $show_row = isset($rows_per_page) ? $rows_per_page : '0';
            if (isset($total_row_count) && $total_row_count < $show_row) {
                $show_row = $total_row_count;
            }

            echo __('Для получения всего списка сохраните список в файл. В файле будет полный набор данных.');
            ?>
        </div>
        
        <!-- Кнопка экспорта -->
        <div class="mb-3" style="margin-bottom: 15px;">
            <?php 
            echo Form::open('identifier/save_csv', array('class' => 'form-inline'));
            echo Form::button('todo', __('Сохранить список в файл'), array(
                'value' => isset($type) ? $type : '',
                'class' => 'btn btn-primary',
                'type' => 'submit'
            ));
            
            if (isset($arg)) {
                echo Form::hidden('arg', htmlspecialchars(json_encode($arg)));
            }
            echo Form::close();
            ?>
        </div>
        
        <!-- Дополнительная информация (если передана) -->
        <?php if (isset($custom_info) && !empty($custom_info)) { ?>
            <div class="custom-info mb-3" style="margin-bottom: 15px;">
                <?php echo $custom_info; ?>
            </div>
        <?php } ?>
        
        <!-- Основная форма с таблицей -->
        <?php echo Form::open('identifier/control', array('class' => 'form-inline', 'id' => 'cards-form')); ?>
        
        <?php if (isset($list) && !empty($list)) { ?>
            
            <!-- Пагинация НАД таблицей -->
            <div id="pager-top" class="pager" style="margin-bottom: 15px;">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-default first"><i class="glyphicon glyphicon-step-backward"></i> Первая</button>
                            <button type="button" class="btn btn-sm btn-default prev"><i class="glyphicon glyphicon-backward"></i> Назад</button>
                            <button type="button" class="btn btn-sm btn-default next">Вперед <i class="glyphicon glyphicon-forward"></i></button>
                            <button type="button" class="btn btn-sm btn-default last">Последняя <i class="glyphicon glyphicon-step-forward"></i></button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 text-right">
                        <div class="pagination-info" style="display: inline-block; margin-right: 15px;">
                            <span class="pagedisplay"></span>
                        </div>
                        
                        <div class="pagination-size" style="display: inline-block;">
                            <label style="margin-right: 5px; font-weight: normal;">Показывать:</label>
                            <select class="pagesize form-control input-sm" style="width: auto; display: inline-block;">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                        
                        <div class="pagination-goto" style="display: inline-block; margin-left: 15px;">
                            <label style="margin-right: 5px; font-weight: normal;">Страница:</label>
                            <input type="text" class="pagenum form-control input-sm" size="4" style="width: 60px; display: inline-block;">
                        </div>
                    </div>
                </div>
            </div>
            <!--Настройка пагинации -->
			<!--<div class="pager">
				<img src="images/first.png" class="first" alt="First" />
				<img src="images/prev.png" class="prev" alt="Prev" />
				<span class="pagedisplay" data-pager-output-filtered="{startRow:input} &ndash; {endRow} / {filteredRows} of {totalRows} total rows"></span>
				<img src="images/next.png" class="next" alt="Next" />
				<img src="images/last.png" class="last" alt="Last" />
				<select class="pagesize" title="Select page size">
				<option value="10">10</option>
				<option value="20">20</option>
				<option value="30">30</option>
				<option value="all">Все</option>
				</select>
				<select class="gotoPage" title="Select page number"></select>
			</div>
			-->

            <!-- Таблица -->
            <div class="table-responsive">
                <table id="tablesorter" class="table table-striped table-hover table-condensed tablesorter table-bordered">
                    <thead>
                        <tr>
                            <th width="50">№</th>
                            <th width="80">
                                <div class="text-center">
                                    <label class="d-block">
                                        Выделить все
                                        <input type="checkbox" id="check_all" class="form-check-input">
                                    </label>
                                </div>
                            </th>
                            <?php foreach ($headers as $header_key => $header_title) { ?>
                                <th><?php echo htmlspecialchars($header_title); ?></th>
                            <?php } ?>
                        </th>
                    </thead>
                    
                    <tbody>
                        <?php 
                        $sn = 0;
                        foreach ($list as $index => $row) {
                            $cardId = isset($row['ID_CARD']) ? $row['ID_CARD'] : '';
                            $safeCardId = htmlspecialchars($cardId, ENT_QUOTES, 'UTF-8');
                        ?>
                            <tr>
                                <td class="text-center"><?php echo ++$sn; ?></td>
                                <td class="text-center">
                                    <label>
                                        <?php echo Form::checkbox('identifier[]', $safeCardId, false, array(
                                            'class' => 'checkbox form-check-input',
                                            'data-card-id' => $safeCardId
                                        )); ?>
                                    </label>
                                </td>
                                <?php foreach (array_keys($headers) as $field) { ?>
                                    <td>
                                        <?php
                                        $value = isset($row[$field]) ? $row[$field] : '';
                                        // Преобразование кодировки только если нужно
                                        if (!mb_check_encoding($value, 'UTF-8')) {
                                            $value = iconv('windows-1251', 'UTF-8', $value);
                                        }
                                        echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                                        ?>
                                    </td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Пагинация ПОД таблицей -->
            <div id="pager-bottom" class="pager" style="margin-top: 15px;">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-default first"><i class="glyphicon glyphicon-step-backward"></i> Первая</button>
                            <button type="button" class="btn btn-sm btn-default prev"><i class="glyphicon glyphicon-backward"></i> Назад</button>
                            <button type="button" class="btn btn-sm btn-default next">Вперед <i class="glyphicon glyphicon-forward"></i></button>
                            <button type="button" class="btn btn-sm btn-default last">Последняя <i class="glyphicon glyphicon-step-forward"></i></button>
                        </div>
                    </div>
                    
                    <div class="col-md-6 col-sm-12 text-right">
                        <div class="pagination-info" style="display: inline-block; margin-right: 15px;">
                            <span class="pagedisplay"></span>
                        </div>
                        
                        <div class="pagination-size" style="display: inline-block;">
                            <label style="margin-right: 5px; font-weight: normal;">Показывать:</label>
                            <select class="pagesize form-control input-sm" style="width: auto; display: inline-block;">
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="30">30</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                                <option value="500">500</option>
                            </select>
                        </div>
                        
                        <div class="pagination-goto" style="display: inline-block; margin-left: 15px;">
                            <label style="margin-right: 5px; font-weight: normal;">Страница:</label>
                            <input type="text" class="pagenum form-control input-sm" size="4" style="width: 60px; display: inline-block;">
                        </div>
                    </div>
                </div>
            </div>
            
        <?php } else { ?>
            <div class="alert alert-warning">
                <?php echo htmlspecialchars(__('Нет данных для отображения')); ?>
            </div>
        <?php } ?>
        
        <!-- Панель действий -->
        <?php if (isset($show_actions) ? $show_actions : true) { ?>
            <div class="card mt-3" style="margin-top: 20px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <?php if (Auth::instance()->logged_in()) { ?>
                            <div>
                                <button type="submit" 
                                        class="btn btn-success" 
                                        name="todo"  
                                        value="unactive"
                                        onclick="return confirm('<?php echo htmlspecialchars(addslashes(__('people_unactive_alert'))); ?>')">
                                    <?php echo htmlspecialchars(__('people_unactive')); ?>
                                </button>
                                
                                <button type="submit" 
                                        class="btn btn-danger" 
                                        name="todo"  
                                        value="delete"
                                        disabled
                                        onclick="return confirm('<?php echo htmlspecialchars(addslashes(__('people_delete_alert'))); ?>')">
                                    <?php echo htmlspecialchars(__('card_delete')); ?>
                                </button>
                            </div>
                            
                            <div class="text-muted">
                                <small><?php echo __('Выбрано карт'); ?>: <span id="selected-count">0</span></small>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-danger w-100">
                                <?php echo htmlspecialchars(__('Для выполнения действий необходимо авторизоваться')); ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        <?php } ?>
        
        <?php echo Form::close(); ?>
    </div>
</div>

<style type="text/css">
/* Стили для пагинации */
.pager {
    margin: 10px 0;
    padding: 8px;
    background: #f9f9f9;
    border-radius: 4px;
    border: 1px solid #e3e3e3;
}

.pager .btn-group {
    margin-bottom: 5px;
}

.pager .btn-sm {
    padding: 5px 10px;
    font-size: 12px;
    line-height: 1.5;
}

.pager .btn-default {
    color: #333;
    background-color: #fff;
    border-color: #ccc;
}

.pager .btn-default:hover:not(:disabled) {
    background-color: #e6e6e6;
    border-color: #adadad;
}

.pager .btn-default:disabled {
    opacity: 0.65;
    cursor: not-allowed;
}

.pager .pagedisplay {
    font-weight: bold;
    margin: 0 10px;
}

.pager select.form-control,
.pager input.form-control {
    margin-left: 5px;
}

.pager label {
    margin-bottom: 0;
    font-weight: normal;
}

/* Стили для таблицы */
.table-responsive {
    overflow-x: auto;
}

.table-bordered {
    border: 1px solid #ddd;
}

.table-bordered > thead > tr > th,
.table-bordered > tbody > tr > td {
    border: 1px solid #ddd;
}

.table-striped > tbody > tr:nth-child(odd) {
    background-color: #f9f9f9;
}

.table-hover > tbody > tr:hover {
    background-color: #f5f5f5;
}

.table-condensed > thead > tr > th,
.table-condensed > tbody > tr > td {
    padding: 5px;
}

/* Стили для чекбоксов */
.form-check-input {
    margin: 0;
    cursor: pointer;
}

.text-center {
    text-align: center;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    var $table = $("#tablesorter");
    
    // Инициализация tablesorter с пагинацией
    if ($.fn.tablesorter && $.fn.tablesorterPager) {
        $table.tablesorter({
            theme: 'blue',
            widgets: ['zebra', 'filter'],
            widgetOptions: {
                filter_reset: '.reset-filter',
                filter_searchDelay: 300,
                filter_placeholder: { search: 'Поиск...' }
            }
        });
        
        $table.tablesorterPager({
            container: $(".pager"),
            cssGoto: '.pagenum',
            cssPageDisplay: '.pagedisplay',
            cssPageSize: '.pagesize',
            cssFirst: '.first',
            cssPrev: '.prev',
            cssNext: '.next',
            cssLast: '.last',
            output: 'Показано {startRow} - {endRow} из {totalRows} записей',
            page: 0,
            size: 50,
            updateArrows: true
        });
        
        console.log('Пагинация инициализирована');
    }
    
    // ========== РАБОТА С ЧЕКБОКСАМИ ==========
    
    // Функция получения только видимых чекбоксов (с учетом пагинации и фильтрации)
    function getVisibleCheckboxes() {
        return $(".checkbox").filter(function() {
            var $row = $(this).closest("tr");
            return $row.is(":visible");
        });
    }
    
    // Обновление состояния главного чекбокса
    function updateMasterCheckbox() {
        var $visible = getVisibleCheckboxes();
        var total = $visible.length;
        var checked = $visible.filter(":checked").length;
        
        var $masterCheck = $("#check_all");
        
        if (total === 0) {
            $masterCheck.prop("checked", false);
            $masterCheck.prop("disabled", true);
        } else {
            $masterCheck.prop("disabled", false);
            $masterCheck.prop("checked", total === checked);
        }
        
        if (checked > 0 && checked < total) {
            $masterCheck.prop("indeterminate", true);
        } else {
            $masterCheck.prop("indeterminate", false);
        }
        
        $('#selected-count').text(checked);
        
        // Обновляем текст кнопок
        var $btnUnactive = $("button[name='todo'][value='unactive']");
        var $btnDelete = $("button[name='todo'][value='delete']");
        
        if ($btnUnactive.length) {
            if (checked > 0) {
                $btnUnactive.html("Сделать неактивными (" + checked + ")");
                $btnUnactive.prop('disabled', false);
            } else {
                $btnUnactive.html("Сделать неактивными");
                $btnUnactive.prop('disabled', true);
            }
        }
        
        if ($btnDelete.length) {
            if (checked > 0) {
                $btnDelete.html("Удалить карты (" + checked + ")");
                $btnDelete.prop('disabled', false);
            } else {
                $btnDelete.html("Удалить карты");
                $btnDelete.prop('disabled', true);
            }
        }
    }
    
    // Переключение всех видимых чекбоксов
    function toggleAllVisibleCheckboxes() {
        var $visible = getVisibleCheckboxes();
        var shouldCheck = $("#check_all").prop("checked");
        $visible.prop("checked", shouldCheck);
        updateMasterCheckbox();
    }
    
    // Обработчик главного чекбокса
    $("#check_all").off('change').on('change', function() {
        toggleAllVisibleCheckboxes();
    });
    
    // Обработчик всех чекбоксов
    $(document).off('change', '.checkbox').on('change', '.checkbox', function() {
        updateMasterCheckbox();
    });
    
    // Обновляем чекбоксы при изменении страницы, фильтрации или сортировке
    $table.on('pagerComplete filterEnd sortEnd', function() {
        setTimeout(function() {
            // Сбрасываем главный чекбокс
            $("#check_all").prop("checked", false);
            $("#check_all").prop("indeterminate", false);
            updateMasterCheckbox();
        }, 50);
    });
    
    // Перехват отправки формы - отправляем только видимые выбранные карты
    $("#cards-form").off('submit').on('submit', function(e) {
        var $visibleChecked = getVisibleCheckboxes().filter(":checked");
        
        if ($visibleChecked.length === 0) {
            e.preventDefault();
            alert("Не выбрано ни одной видимой карты!");
            return false;
        }
        
        var $clickedButton = $(document.activeElement);
        
        if ($clickedButton.val() === 'delete') {
            var confirmMsg = "Будет удалено " + $visibleChecked.length + 
                           " карт (только видимые в текущем фильтре). Подтверждаете удаление?";
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        } else if ($clickedButton.val() === 'unactive') {
            var confirmMsg = "Будет деактивировано " + $visibleChecked.length + 
                           " карт (только видимые в текущем фильтре). Подтверждаете операцию?";
            if (!confirm(confirmMsg)) {
                e.preventDefault();
                return false;
            }
        }
        
        // Отключаем все невидимые чекбоксы, чтобы они не отправились на сервер
        $(".checkbox").each(function() {
            var $checkbox = $(this);
            var $row = $checkbox.closest("tr");
            if (!$row.is(":visible")) {
                $checkbox.prop('disabled', true);
            } else {
                $checkbox.prop('disabled', false);
            }
        });
        
        // Снимаем выделение со всех скрытых чекбоксов
        $(".checkbox").filter(function() {
            var $row = $(this).closest("tr");
            return !$row.is(":visible");
        }).prop("checked", false);
        
        return true;
    });
    
    // Начальная инициализация
    setTimeout(function() {
        updateMasterCheckbox();
        console.log('Чекбоксы инициализированы');
    }, 200);
});
</script>

<?php if (isset($exec_time)) { ?>
<!-- Информация о времени генерации -->
<span id="time-bottom" style="display:none;">
    <?php echo __('Страница подготовлена за :time сек.', array(':time' => number_format($exec_time, 3))); ?>
</span>
<?php } ?>