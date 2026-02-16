<?php defined('SYSPATH') OR die('No direct access allowed.');

/**31.01.2026 Модель для отображения состояние идентификаторов.
*/
class Model_identifier extends Model
{
	public $mess;//сообщения разные
	public $arg=array();//аргументы
	
	/**1.02.2026 карты без событий о проходе
	*/
	
	
	public function cardsWithEvents()
	{
		$listIdentifier=array();//начальные значения пустой массив	
		//получаю массив: идентификатор - дата последнего прохода
		$sql='select e.id_card, max(e.datetime) from events e
		where e.id_eventtype in (46, 50, 65, 70, 71, 145)
		group by e.id_card';
		return array_column(DB::query(Database::SELECT, iconv('UTF-8', 'CP1251',$sql))
					->execute(Database::instance('fb'))
					->as_array(), null, 'ID_CARD');
		set_time_limit(600); // 600 секунд
		
	}
	
	public function cardsFullList()
	{
		$sql='select 
    c.id_card
    ,c.timestart
    ,c.timeend
    ,c."ACTIVE"
    ,c.id_cardtype
    ,ct.smallname  as idtype
    ,c.createdat
    ,p.id_pep
    ,p.surname||\' \'||p.name||\' \'||p.patronymic as fio
    ,o.id_org
    ,o.name as orgname
    ,o.id_parent
    ,o2.name as orgparentname
     from card c
     join people p on c.id_pep=p.id_pep
     join organization o on p.id_org=o.id_org
     join organization o2 on o2.id_org=o.id_parent
     join cardtype ct on c.id_cardtype=ct.id';
	return array_column(DB::query(Database::SELECT, iconv('UTF-8', 'CP1251',$sql))
					->execute(Database::instance('fb'))
					->as_array(), null, 'ID_CARD');
		
	}
	
	/**5.02.2026 вывод списка карт, у которых нет отметок о проходах до указанной даты
	*/
	public function cardNoEventDate($dateBefor=null)
	{
		
		$dateBefor=Arr::get($this->arg,'event_date');
		if (is_null(Arr::get($this->arg,'event_date'))) {
			$dateBefor = date('Y-m-d');
		}
		
		
		$cutoffDate = DateTime::createFromFormat('Y-m-d', $dateBefor);
	
		//получаю весь список карт с метками прохода
		$cardsArray =$this->allCards();
		//и выбираю те записи, у которых метка времени lastevent меньше указанной
		
		$filteredArray = array_filter($cardsArray, function($card) use ($cutoffDate) {
			// Если lastevent пустое или null
			if (empty($card['lastevent'])) {
				return false;
			}
			
			// Преобразуем lastevent в объект DateTime
			$lasteventDate = DateTime::createFromFormat('Y-m-d H:i:s', $card['lastevent']);
			
			// Если преобразование не удалось, пропускаем запись
			if (!$lasteventDate) {
				
				return false;
			}
			
			// Сравниваем даты
			return $lasteventDate < $cutoffDate;
			});
		
		
		return $filteredArray;
		
	}
	
	/** 1.02.2026 г модель возвращает список карт, у которых нет отметок о проходах
	*/
	public function cardNoEvent()
	{
	
		$listWhoGo = $this->cardsWithEvents();	//массив карт с отметками о проходах		
		$listIdentifier = $this->cardsFullList();//массив всех карт			
	
	//теперь выбираю элементы из массива $listIdentifier, которых нет в массиве $listWhoGo. Это и будут карты без проходов
	$result = array_diff_key($listIdentifier, $listWhoGo);
	
	//теперь для каждого элемента добавляю время прохода
	foreach ($result as &$key)
	{
		
		$key['lastevent']=Arr::get(Arr::get($listWhoGo,Arr::get($key,'ID_CARD')), 'MAX');
		
		
	}
		unset($key);
		
	return $result;		
			
	
		
	}
	
	
	/** 1.02.2026 г модель возвращает список карт, у которых добавлена отметка о проходах при их наличии
	*/
	public function allCards()
	{
	
	
		$listWhoGo = $this->cardsWithEvents();			
		$listIdentifier = $this->cardsFullList();			
	
	
	foreach ($listIdentifier as &$key)
	{
		
		$key['lastevent']=Arr::get(Arr::get($listWhoGo,Arr::get($key,'ID_CARD')), 'MAX');
		
		
	}
		unset($key);
		
	return $listIdentifier;		
			
	
		
	}
	
	
	
	
	
	/**1.02.2026 функция устанавилвает ACTIVE=0 для указанного массива карт.
		*/	
	public function setUnactive($cards)
	{
		$sql=__('update card c 
			set c."ACTIVE"=0 
			where c.id_card in (:card_array)
			', array(
			':card_array'=>implode(",", $cards)));
			
		
		try
			{
			$query = DB::query(Database::UPDATE, $sql)
			->execute(Database::instance('fb'));
			return true;
			} catch (Exception $e) {
				Log::instance()->add(Log::DEBUG, $e->getMessage());
				$this->mess=$e->getMessage();
				return 	false;
			}
			
		
	}
}
	

