<?php
//echo Debug::vars('2', count($list), $type);exit;
//считаю количество лет, месяцев, дней от текущей даты.
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
?>
<script type="text/javascript">
      $(document).ready(function() {
    	    $("#check_all").click(function () {
				if (!$("#check_all").is(":checked"))
    	            $(".checkbox").prop("checked",false);
    	        else
    	            $(".checkbox").prop("checked",true);
    	    });
    	});
  	
</script> 
<br>
<br>
<br>
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo __('Список карт, не имеющих отметки о проходе до указанной даты :date (:diff)', array(
        ':date' => $eventDate,
        ':diff' => trim($diffText)
    )); ?></h3>
  </div>

  

 <?php
/* 	$title=array('ID_CARD'
    ,'TIMESTART'
    ,'TIMEEND'
    ,'"ACTIVE"'
    ,'ID_CARDTYPE'
    ,'IDTYPE'
    ,'CREATEDAT'
    ,'ID_PEP'
    ,'FIO'
    ,'ID_ORG'
    ,'ORGNAME'
    ,'ID_PARENT'
    ,'ORGPARENTNAME'); */
	
	$title=array('ID_CARD'
    ,'TIMESTART'
    ,'TIMEEND'
	,'"ACTIVE"'
    ,'IDTYPE'
    ,'CREATEDAT'
    ,'ID_PEP'
    ,'FIO'
    ,'ID_ORG'
    ,'ORGNAME'
    ,'ID_PARENT'
    ,'ORGPARENTNAME'
	,'lastevent');
	
	//$title=array_keys(reset($list));
	
?>	
  <div class="panel-body">
  
	<?	echo __('Всего найдено записей').' ';
		echo isset($total_row_count)? $total_row_count : '0';
	
	echo '<br>';
		
	$show_row=0;
	$show_row=isset($rows_per_page)? $rows_per_page : '0';
	if($total_row_count<$show_row) $show_row=$total_row_count;
	echo __('Из них показаны ').' ';
		echo $show_row;
		
		
	echo '<br>';
		echo __('Для получения всего списка сохраните список в файл. В файле будет полный набор данных.');?>	
	
	<?echo Form::open('identifier/save_csv');
			echo Form::button('todo', __('Сохранить список в файл'), array('value'=>$type,'class'=>'btn btn-primary', 'type' => 'submit'));
			echo Form::hidden('arg', json_encode($arg));//сохраняю параметры выборки для передачи в POST
		
		
		echo Form::close();
		?>
	



	<?echo Form::open('identifier/control', array('class'=>'form-inline'));?>
	
			
		
		<table id="tablesorter" class="table table-striped table-hover table-condensed tablesorter">
		<thead allign="center">
			<tr>
			<th>№ п/п</th>
			<th>
				Выделить<br><label><input type="checkbox" name="identifier" id="check_all"></label>
			</th>
			<?php
	
				foreach($title as $key)
								{
									echo '<th>';
									
										echo $key;
									echo '</th>';
								}
			
		
			?>
			
		</tr>
		</thead>
		
		<tbody>
			<?php
			$sn=0;
			foreach($list as $key=>$value)
			{
				//echo Debug::vars('110', $value);exit;
				echo '<tr>';
					echo '<td>';
						echo ++$sn;
					echo '</td>';
				echo '<td>
					<label>'.Form::checkbox('identifier[]', '\''.Arr::get($value, 'ID_CARD').'\'', FALSE, array('class'=>'checkbox')).'</label>
				</td>';
						echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ID_CARD'));
						echo '</td>';
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'TIMESTART'));
						echo '</td>';
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'TIMEEND'));
						echo '</td>';
					echo '<td>';
							echo Arr::get($value, 'ACTIVE');
						echo '</td>';
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'IDTYPE'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'CREATEDAT'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ID_PEP'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'FIO'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ID_ORG'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ORGNAME'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ID_PARENT'));
						echo '</td>';
					
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'ORGPARENTNAME'));
						echo '</td>';
					echo '<td>';
							echo iconv('windows-1251','UTF-8', Arr::get($value, 'lastevent'));
						echo '</td>';
					
				echo '</tr>';
				
				
			}
			
			?>
		
		</tbody>

		
		<tr>
		</tr>
		</table>
 
	  <nav class="navbar navbar-default navbar-fixed-bottom disable" role="navigation">
  <div class="container">
  <div class="row">



	<button 
		  	type="submit" 
		  	class="btn btn-success" 
		  	name="todo"  
		  	value="unactive" 
		  	<?php if(!Auth::instance()->logged_in()) echo 'disabled'?>
		  	onclick="return confirm('<?echo __('people_unactive_alert')?>') ? true : false;"><?echo __('people_unactive')?>
	</button>
  	  
  	<button type="submit" 
			class="btn btn-danger pull-right" 
			name="todo"  
			value="delete" 
			disabled
			<?php if(!Auth::instance()->logged_in()) echo 'disabled'?> onclick="return confirm('<?echo __('people_delete_alert')?>') ? true : false;"><?echo __('card_delete')?>
	</button>
	
	</div>
	</div>
</nav>						  
							
	</div>
</div>
