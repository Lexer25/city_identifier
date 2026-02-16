<?php
//echo Debug::vars('2', count($list), $type);//exit;

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
<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title"><?php echo __('Список всех карт с меткой последнего прохода.');?></h3>
  </div>
  

 <?php
 
 //получаю список колонок из первой строки данных
 

	
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
  
	<?
	echo __('Всего найдено записей').' ';
		echo isset($total_row_count)? $total_row_count : '0';
	
	echo '<br>';
		
	echo __('Из них показаны ').' ';
		echo isset($rows_per_page)? $rows_per_page : '0';
		
		
	echo '<br>';
		echo __('Для получения всего списка сохраните список в файл. В файле будет полный набор данных.');
		echo Form::open('identifier/save_csv');
			echo Form::button('todo', __('Сохранить список в файл'), array('value'=>$type,'class'=>'btn btn-primary', 'type' => 'submit'));
		
		
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

</nav>						  
							
	</div>
</div>
