<?php defined('SYSPATH') OR die('No direct access allowed.'); ?>

<script type="text/javascript">
$(document).ready(function() {
    $("#check_all").click(function () {
        $(".checkbox").prop("checked", this.checked);
    });
});
</script>

<div class="panel panel-primary">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo htmlspecialchars(__('Список карт, не имеющих отметки о проходе')); ?></h3>
    </div>
    
    <?php
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
    ?>
    
    <div class="panel-body">
        <div class="alert alert-info">
           
        </div>
        
        <div class="mb-3">
            <?php
			echo __('Всего найдено записей').' ';
		echo isset($total_row_count)? $total_row_count : '0';
	
	echo '<br>';
		
	$show_row=0;
	$show_row=isset($rows_per_page)? $rows_per_page : '0';
	if($total_row_count<$show_row) $show_row=$total_row_count;
	echo __('Из них показаны ').' ';
		echo $show_row;
		
		
            echo Form::open('identifier/save_csv', array('class' => 'form-inline'));
            echo Form::button('export', htmlspecialchars(__('Сохранить список в файл')), array(
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
        
        <?php echo Form::open('identifier/control', array('class' => 'form-inline', 'id' => 'cards-form')); ?>
        
        <?php if (isset($list) && !empty($list)): ?>
            <div class="table-responsive">
                <table id="cards-table" class="table table-striped table-hover table-condensed">
                    <thead>
                        <tr>
                            <th>№</th>
                            <th>
                                <div class="text-center">
                                    <label class="d-block">
                                        Выделить все
                                        <input type="checkbox" id="check_all" class="form-check-input">
                                    </label>
                                </div>
                            </th>
                            <?php foreach ($headers as $header): ?>
                                <th><?php echo htmlspecialchars($header); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    
                    <tbody>
                        <?php 
						$sn=0;
						foreach ($list as $index => $row): ?>
                            <?php
                            // Безопасное получение значений с проверкой существования
                            $cardId = isset($row['ID_CARD']) ? $row['ID_CARD'] : '';
                            $safeCardId = htmlspecialchars($cardId, ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr>
                                <td><?php echo (++$sn); ?></td>
                                <td class="text-center">
                                    <label>
                                        <?php echo Form::checkbox('identifier[]', $safeCardId, false, array(
                                            'class' => 'checkbox form-check-input',
                                            'data-card-id' => $safeCardId
                                        )); ?>
                                    </label>
                                </td>
                                <?php foreach (array_keys($headers) as $field): ?>
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
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <?php echo htmlspecialchars(__('Нет данных для отображения')); ?>
            </div>
        <?php endif; ?>
        
        <!-- Панель действий -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <?php if (Auth::instance()->logged_in()): ?>
                        <div>
                            <button type="submit" 
                                    class="btn btn-success" 
                                    name="action" 
                                    value="deactivate"
                                    onclick="return confirm('<?php echo htmlspecialchars(addslashes(__('people_unactive_alert'))); ?>')">
                                <?php echo htmlspecialchars(__('people_unactive')); ?>
                            </button>
                            
                            <button type="submit" 
                                    class="btn btn-danger pull-right" 
                                    name="action" 
                                    value="delete"
                                    disabled
                                    onclick="return confirm('<?php echo htmlspecialchars(addslashes(__('people_delete_alert'))); ?>')">
                                <?php echo htmlspecialchars(__('card_delete')); ?>
                            </button>
                        </div>
                        
                        <div class="text-muted">
                            <small>Выбрано карт: <span id="selected-count">0</span></small>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-danger w-100">
                            <?php echo htmlspecialchars(__('Для выполнения действий необходимо авторизоваться')); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <?php echo Form::close(); ?>
    </div>
</div>

<script type="text/javascript">
$(document).ready(function() {
    // Выделение всех чекбоксов
    $("#check_all").click(function() {
        var isChecked = $(this).prop('checked');
        $(".checkbox").prop("checked", isChecked).trigger('change');
    });
    
    // Подсчет выбранных элементов
    $(document).on('change', '.checkbox', function() {
        updateSelectedCount();
    });
    
    function updateSelectedCount() {
        var selectedCount = $('.checkbox:checked').length;
        $('#selected-count').text(selectedCount);
        
        // Активировать/деактивировать кнопки в зависимости от выбора
        var hasSelection = selectedCount > 0;
        $('button[name="action"]').prop('disabled', !hasSelection);
    }
    
    // Инициализация счетчика
    updateSelectedCount();
    
    // Валидация формы
    $('#cards-form').submit(function(e) {
        if ($('.checkbox:checked').length === 0) {
            alert('<?php echo htmlspecialchars(addslashes(__('Пожалуйста, выберите хотя бы одну карту'))); ?>');
            e.preventDefault();
            return false;
        }
        
        // Дополнительная проверка для удаления
        if ($('button[name="action"]:focus').val() === 'delete') {
            var selectedCount = $('.checkbox:checked').length;
            return confirm('<?php echo htmlspecialchars(addslashes(__('Вы уверены, что хотите удалить выбранные карты? Это действие необратимо.'))); ?>');
        }
    });
});
</script>