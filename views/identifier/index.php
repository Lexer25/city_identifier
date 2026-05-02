
<br>
<br>
<br>
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo htmlspecialchars(__('Информация по идентификаторам') . ' ' . date('Y-m-d H:i:s')) ?></h3>
  </div>
  <p><b>Внимание!</b></p>
  Подготовка по отчетам может занимать длительное время!<br>
  Для сокращения времени вывод информации на экран рекомендую ограничить "Количество строк на экране" (допустимое значение до 500).<br>
  В файл экспортируются все данные, независимо от значения "Количество строк на экране".<br>
	<p><b>Удаление идентификаторов.</b></p>
  Для удаления неактивных идентификаторов используйте инструмет Артонит Сити Центр - Количество неактивных карт, просмотр списка, выбор вариантов действия.<br>
  
  <div class="panel-body">
  <hr>
  <p><b>Отчет 1: список идентификаторов, которые не ходили после указанной даты.</b></p>
  Будут выбраны идентификаторы, которые не имеют отметки о проходе после указанной даты.
  После получения списка будет возможность выбрать идентификаторы и сделать их неактивными.<br>
  Возможен экспорт списка в файл csv для последующего анализа.
    <?php
	
    echo Form::open('identifier/action');
	echo Form::hidden('page', 1); // Скрытое поле для номера страницы
    ?>
        
    <div class="input-group mb-3">
        <label for="event_date_picker" class="w-100 mb-1">Дата события:</label>
        <?php
        // Определяем значение для поля event_date
        // Если в сессии есть event_date, используем его, иначе текущую дату
		//Вспоминаю дату из предыдущего запроса.
		$session_event_date = Cookie::get('session_event_date');
		
        if (isset($session_event_date) && !empty($session_event_date)) {
            $event_date_value = $session_event_date;
        } else {
            $event_date_value = date('Y-m-d');
        }
        
        // Обеспечиваем, что дата не больше текущей
		$current_date = date('Y-m-d');
        if ($event_date_value > $current_date) {
            $event_date_value = $current_date;
        }
  		//вывод календаря
        echo Form::input('event_date', $event_date_value, [
            'type' => 'date',
            'class' => 'form-control date-picker',
            'placeholder' => 'Выберите дату',
            'max' => $current_date,
            'id' => 'event_date_picker',
            'title' => 'Выберите дату не позднее сегодняшнего дня',
            'required' => 'required'
        ]);
        ?>
    </div>
    <small class="text-muted">Максимальная доступная дата: <?php echo htmlspecialchars(date('d.m.Y')); ?></small>


	<?php
		echo '<br>';
		 echo Form::button('todo', 'Получить Отчет 1', [
            'type' => 'submit',
            'class' => 'btn btn-primary btn-lg',
			'value'=>'cardNoEventDate',
        ]);
	?>
 <hr>
<p><b>Отчет 2: список идентификаторов, не имеющих отметки о событиях.</b></p>
Будут выбраны идентификаторы, у которых нет отметки о проходе.
Будет подготовлен список идентификаторов, у которых нет ни одной отметки о проходе.<br>
После получения списка будет возможность выбрать идентификаторы и сделать их неактивными.<br>
Возможен экспорт списка в файл csv для последующего анализа.
<?php

	//echo Form::open('identifier/action');

		echo '<br>';
		 echo Form::button('todo', 'Получить Отчет 2', [
            'type' => 'submit',
            'class' => 'btn btn-primary btn-lg',
			'value'=>'cardNoEvent',
        ]);

	
?> 
 <hr>
<p><b>Отчет 3: список всех идентификаторов с датой последнего события.</b></p> 
Будут выбраные все идентификаторы, зарегистрированные в базе данных СКУД. При наличии событий о проходе будет указана дата последнего прохода.<br>
 Возможен экспорт списка в файл csv для последующего анализа.
<?php

		
		echo '<br>';
		 echo Form::button('todo', 'Получить отчет 3', [
            'type' => 'submit',
            'class' => 'btn btn-primary btn-lg',
			'value'=>'allCards'
        ]);
		
		
	echo Form::close();
	
?> 
 <hr>   
 <?php
//вывод номера сборки
    echo 'mod version ' . (defined('IDENTIFIER_VERSION') ? IDENTIFIER_VERSION : 'unknown');
?>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var datePicker = document.getElementById('event_date_picker');
    var rowsPerPage = document.getElementById('rows_per_page');
    var today = new Date().toISOString().split('T')[0];
    
    // Устанавливаем максимальную дату
    datePicker.max = today;
    
    // Валидация даты при изменении
    datePicker.addEventListener('change', function() {
        if (this.value > today) {
            alert('Нельзя выбирать будущие даты!');
            this.value = today;
        }
    });
    
    // Валидация количества строк при изменении
    rowsPerPage.addEventListener('change', function() {
        var value = parseInt(this.value);
        if (isNaN(value) || value < 1) {
            this.value = 1;
        } else if (value > 500) {
            this.value = 500;
        }
    });
    
    rowsPerPage.addEventListener('input', function() {
        var value = parseInt(this.value);
        if (isNaN(value) || value < 1) {
            this.value = 1;
        } else if (value > 500) {
            this.value = 500;
        }
    });
    
    // Находим форму и добавляем валидацию при отправке
    var form = datePicker.closest('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Валидация даты
            if (datePicker.value > today) {
                e.preventDefault();
                alert('Ошибка: выбрана будущая дата. Пожалуйста, выберите текущую или прошедшую дату.');
                datePicker.value = today;
                datePicker.focus();
                return false;
            }
            
            // Валидация количества строк
            var rowsValue = parseInt(rowsPerPage.value);
            if (isNaN(rowsValue) || rowsValue < 1 || rowsValue > 500) {
                e.preventDefault();
                alert('Ошибка: количество строк должно быть числом от 1 до 500.');
                rowsPerPage.value = 50;
                rowsPerPage.focus();
                return false;
            }
            
       
            
           
        });
    }
});
</script>