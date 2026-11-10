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
		set_time_limit(600); // 600 секунд
		$listIdentifier=array();//начальные значения пустой массив
		//получаю массив: идентификатор - дата последнего прохода
		$sql='select  e.id_card, max(e.datetime) from events e
		where e.id_eventtype in (46, 50, 65, 70, 71, 145)
		group by e.id_card';
		return array_column(DB::query(Database::SELECT, iconv('UTF-8', 'CP1251',$sql))
					->execute(Database::instance('fb'))
					->as_array(), null, 'ID_CARD');
		
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
	//echo Debug::vars('71', $cardsArray);exit;	
		$filteredArray = array_filter($cardsArray, function($card) use ($cutoffDate) {
			// Если lastevent пустое или null
			if (empty($card['lastevent'])) {
				return false;
			}
			
			// Если карта не активна, то не выводить
			if (empty($card['ACTIVE'])) {
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
	
	/**10.11.2026 Отчёт: количество проходов (события типов 50 и 70) по каждому человеку за период.
	 *  Период берётся из $this->arg: date_from, date_to (Y-m-d), окончание включается.
	 *  Возвращает массив строк: ID_PEP, FIO, ORGNAME, PASS_COUNT (0, если проходов не было).
	 */
	public function peoplePassCount()
	{
		$period = $this->_reportPeriod();

		if ($period === NULL)
		{
			return array();
		}

		list($from, $to) = $period;

		// 1. Агрегат по людям. В events поле ESS1 содержит id_pep
		//    (поле id_pep в events не заполняется), поэтому карту не джойним.
		//    Отдельным запросом, а не одним LEFT JOIN: в InterBase общий JOIN
		//    с подсчётом по всем людям выполняется ~100 с (проверено на стенде).
		$sql = "select e.ess1, count(*) as cnt
				from events e
				where e.id_eventtype in (50, 70)
				  and e.datetime >= timestamp '".$from."'
				  and e.datetime < timestamp '".$to."'
				  and e.ess1 is not null
				group by e.ess1";

		$counts = array();

		try
		{
			$rows = DB::query(Database::SELECT, iconv('UTF-8', 'CP1251', $sql))
				->execute(Database::instance('fb'))
				->as_array();

			foreach ($rows as $row)
			{
				$counts[(string) Arr::get($row, 'ESS1')] = (int) Arr::get($row, 'CNT');
			}
		}
		catch (Exception $e)
		{
			Log::instance()->add(Log::ERROR, 'peoplePassCount (counts): '.$e->getMessage());
			$this->mess = 'Ошибка выборки событий: '.$e->getMessage();
			return array();
		}

		// 2. Все люди с организацией — в том числе без карт и без событий,
		//    чтобы в отчёте для них был явный 0.
		$sql = "select p.id_pep,
					coalesce(p.surname, '')||' '||coalesce(p.name, '')||' '||coalesce(p.patronymic, '') as fio,
					o.name as orgname
				from people p
				left join organization o on o.id_org = p.id_org";

		try
		{
			$people = DB::query(Database::SELECT, iconv('UTF-8', 'CP1251', $sql))
				->execute(Database::instance('fb'))
				->as_array();
		}
		catch (Exception $e)
		{
			Log::instance()->add(Log::ERROR, 'peoplePassCount (people): '.$e->getMessage());
			$this->mess = 'Ошибка выборки людей: '.$e->getMessage();
			return array();
		}

		$result = array();

		foreach ($people as $person)
		{
			$id = (string) Arr::get($person, 'ID_PEP');

			$result[] = array(
				'ID_PEP'     => Arr::get($person, 'ID_PEP'),
				'FIO'        => trim((string) Arr::get($person, 'FIO')),
				'ORGNAME'    => Arr::get($person, 'ORGNAME'),
				'PASS_COUNT' => isset($counts[$id]) ? $counts[$id] : 0,
			);
		}

		// Сортировка по ФИО: данные из БД в CP1251, порядок кириллицы в ней сохраняется.
		usort($result, function($a, $b) {
			$cmp = strcmp($a['FIO'], $b['FIO']);
			return ($cmp === 0) ? strcmp((string) $a['ORGNAME'], (string) $b['ORGNAME']) : $cmp;
		});

		return $result;
	}

	/** Проверка периода отчёта. Возвращает array(начало, конец) для SQL (конец — следующий день, не включая) или NULL. */
	private function _reportPeriod()
	{
		$from = trim((string) Arr::get($this->arg, 'date_from', ''));
		$to   = trim((string) Arr::get($this->arg, 'date_to', ''));

		if ($from === '' OR $to === '')
		{
			$this->mess = 'Не указан период отчёта';
			return NULL;
		}

		$date_from = DateTime::createFromFormat('!Y-m-d', $from);
		$date_to   = DateTime::createFromFormat('!Y-m-d', $to);

		if ( ! $date_from OR $date_from->format('Y-m-d') !== $from)
		{
			$this->mess = 'Некорректная дата начала периода';
			return NULL;
		}

		if ( ! $date_to OR $date_to->format('Y-m-d') !== $to)
		{
			$this->mess = 'Некорректная дата окончания периода';
			return NULL;
		}

		if ($date_from > $date_to)
		{
			$this->mess = 'Дата начала больше даты окончания периода';
			return NULL;
		}

		// Конец периода включается, поэтому верхняя граница — начало следующего дня.
		$next_day = clone $date_to;
		$next_day->modify('+1 day');

		return array(
			$date_from->format('Y-m-d').' 00:00:00',
			$next_day->format('Y-m-d').' 00:00:00',
		);
	}
	
	
	
	
	
	/** P0: безопасное кодирование значения для SQL-литерала.
	 *  Драйвер ODBC (DSN HL) не поддерживает PDO::quote() (возвращает FALSE),
	 *  поэтому параметризация Kohana здесь не работает. Используем штатное
	 *  для InterBase удвоение одинарной кавычки: ' -> ''.
	 */
	private function quoteCardValue($value)
	{
		if ( ! is_scalar($value))
		{
			return NULL;
		}

		$value = (string) $value;

		// Управляющие символы в идентификаторе карты недопустимы
		$value = str_replace(array("\0", "\r", "\n"), '', $value);

		if ($value === '')
		{
			return NULL;
		}

		return "'" . str_replace("'", "''", $value) . "'";
	}

	/** P0: собирает безопасный список литералов для IN (...) */
	private function buildCardList($cards)
	{
		$quoted = array();

		foreach ((array) $cards as $card)
		{
			$value = $this->quoteCardValue($card);

			if ($value !== NULL)
			{
				$quoted[] = $value;
			}
		}

		return empty($quoted) ? NULL : implode(',', $quoted);
	}

	/**29.03.2026 функция продлевает time_end для указанного массива карт.
		*/
	public function prolong($cards, $date)
	{
		if (empty($cards)) {
			return false;
		}
		
		// Validate date format
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
			$this->mess = 'Invalid date format';
			return false;
		}

		// Проверяем календарную корректность (2026-02-31 и т.п.)
		list($year, $month, $day) = array_map('intval', explode('-', $date));
		if (!checkdate($month, $day, $year)) {
			$this->mess = 'Invalid date';
			return false;
		}

		$card_list = $this->buildCardList($cards);
		if ($card_list === NULL) {
			$this->mess = 'Invalid card list';
			return false;
		}

		$sql = 'UPDATE card c
				SET c.timeend = \''.$date.'\'
				WHERE c.id_card IN (' . $card_list . ')';
//echo Debug::vars('171', $sql);exit;		
		try {
			$query = DB::query(Database::UPDATE, $sql);
			$query->execute(Database::instance('fb'));
			return true;
		} catch (Exception $e) {
			Log::instance()->add(Log::DEBUG, $e->getMessage());
			$this->mess = $e->getMessage();
			return false;
		}
	}
	
	
	/**1.02.2026 функция устанавилвает ACTIVE=0 для указанного массива карт.
		*/
	public function setUnactive($cards)
	{
		//echo Debug::vars('197', $cards);exit;
		if (empty($cards)) {
			return false;
		}
		
		$card_list = $this->buildCardList($cards);
		if ($card_list === NULL) {
			$this->mess = 'Invalid card list';
			return false;
		}

		$sql = 'UPDATE card c
				SET c."ACTIVE" = 0
				WHERE c.id_card IN (' . $card_list . ')';
//echo Debug::vars('213', $sql);exit;
		try {
			$query = DB::query(Database::UPDATE, $sql);
			$query->execute(Database::instance('fb'));
			return true;
		} catch (Exception $e) {
			Log::instance()->add(Log::DEBUG, $e->getMessage());
			$this->mess = $e->getMessage();
			return false;
		}
	}
	
	
	
	/**27.03.2026 удаляет карты из указанного массива карт.
		*/
	public function delCardArray($cards)
	{
		if (empty($cards)) {
			return false;
		}
		
		$card_list = $this->buildCardList($cards);
		if ($card_list === NULL) {
			$this->mess = 'Invalid card list';
			return false;
		}

		$sql = 'DELETE FROM card c
				WHERE c.id_card IN (' . $card_list . ')';
//echo Debug::vars('244', $sql);exit;
		try {
			$query = DB::query(Database::DELETE, $sql);
			$query->execute(Database::instance('fb'));
			return true;
		} catch (Exception $e) {
			Log::instance()->add(Log::DEBUG, $e->getMessage());
			$this->mess = $e->getMessage();
			return false;
		}
	}
	
	
	/**
			 * Получает количество карт, срок действия которых истекает до указанной даты
			 *
			 * @param string $date Дата в формате Y-m-d H:i:s
			 * @return int Количество карт
			 */
			public function getCountCardLateNextTime($date)
			{
			 // Validate date format
			 if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $date)) {
			 	return 0;
			 }
			 
			 // Use parameterized query to prevent SQL injection
			 $sql = "SELECT COUNT(*) FROM card c WHERE c.timeend > 'now' AND c.timeend < CAST(? AS TIMESTAMP)";
			 
			 $query = DB::query(Database::SELECT, $sql)
			 	->param(0, $date);
			 
			 $result = $query->execute(Database::instance('fb'));
			 
			 return (int) $result->get('COUNT');
			}
                        
                 
                // Информация о картах, срок действия которых истек
		public function getcardexpired()
		{
			$query=DB::query(Database::SELECT, 'select count(*) from card c where c.timeend<\'now\' and c."ACTIVE">0.')
			->execute(Database::instance('fb'));
			return (int)$query->get('COUNT');
			
		}
                
                
                // Информация о картах, срок действия которых истек. 2.10.2020 Которые не активны!!! 
		public function getCardNotActive()
		{
			$query=DB::query(Database::SELECT, 'select count(*) from card c where c."ACTIVE"<1.')
		->execute(Database::instance('fb'));
		return $query->get('COUNT');
			
		}
		
		
		/**2.04.2026 Количество карт, выданных сотрудникам
		*/
		
		public function getPeopleCardCount()
		{
			$sql='select count(*) from card c
				join people p on p.id_pep=c.id_pep
				where p.id_org not in (2,3)
				';
			$query=DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'));
		return $query->get('COUNT');
			
		}
		
		
		/**2.04.2026 Количество карт, выданных гостям
		*/
		
		public function getGuestCardCount()
		{
			$sql='select count(*) from card c
				join people p on p.id_pep=c.id_pep
				where p.id_org in (2)
				';
			//echo Debug::vars('314', $sql); exit;
			$query=DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'));
		return $query->get('COUNT');
			
		}
		
		/**2.04.2026 Количество карт, выданных гостям в архиве
		*/
		
		public function getGuestArchiveCardCount()
		{
			$sql='select count(*) from card c
				join people p on p.id_pep=c.id_pep
				where p.id_org in (3)
				';
			$query=DB::query(Database::SELECT, $sql)
			->execute(Database::instance('fb'));
		return $query->get('COUNT');
			
		}
		
		
		
		
	
}
	

