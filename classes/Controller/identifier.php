<?php defined('SYSPATH') or die('No direct script access.');

/**31.01.2025 класс identifier задуман как набор методов, позволяющих работать с идентификаторами.
* задача минимуму - поиск "мертвых" душ.
//  https://localhost/city/index.php/identifier
*/

class Controller_identifier extends Controller_Template {
   public $template = 'template';
  
   //Широки шаблон
   //для использьвания необходимо указать 
  
   
   

   
  public $options = array(
				'cardNoEvent' => 'Список идентификаторов, не имеющих отметки о событиях.',
				'allCards' => 'Список всех идентификаторов.',
				'cardNoEventDate' => 'Список идентификаторов, у которых нет событий после указанной даты.',
				'peoplePassCount' => 'Количество проходов по людям за период.',
				
			);
   
  	
	public function before()
	{
		parent::before();

		// P0: раздел содержит персональные данные и операции над картами,
		// поэтому доступен только авторизованным пользователям.
		// Проверка в шаблоне (_table.php) скрывает только кнопки, но не защищает action.
		if ( ! Auth::instance()->logged_in())
		{
			$this->set_flash_message('warning', __('Для работы с разделом «Идентификаторы» необходимо авторизоваться'));
			$this->redirect('dashboard');
		}
	}

	/**
	 * P0: проверка CSRF-токена для всех изменяющих запросов.
	 * Токен выводится в формах (index.php, _table.php) и приходит в поле csrf_token.
	 */
	private function check_csrf()
	{
		$token = (string) Arr::get($_POST, 'csrf_token', '');

		if ( ! Security::check($token))
		{
			Kohana::$log->add(Log::WARNING, 'identifier: отклонён запрос без корректного CSRF-токена');

			$this->set_flash_message('error', __('Запрос отклонён: устаревшая сессия или отсутствует CSRF-токен. Обновите страницу и повторите.'));
			$this->redirect('identifier');
		}
	}
	
	public function action_index()
	{
		
	
		$content = View::factory('identifier/index', array(//начальная страница для работы с идентификаторами.
		'options'=>$this->options,
			
		));
        $this->template->content = $content;
		//echo View::factory('profiler/stats');
		
	}
	
	public function action_cardNoEventDate()
	{
		// Маршрут оставлен для совместимости со старыми ссылками.
		// Раньше здесь вызывался несуществующий Model_identifier::getLastEvent()
		// и страница отдавала 500 со стектрейсом. Отчёт 1 строится через /identifier/action.
		$this->redirect('identifier');
	}
	
	
	/** 5.02.2026 тут ожидаю команды для подготовки отчетов
	*/
	public function action_action()
	{
		$this->check_csrf();

		//echo Debug::vars('39', $_POST);exit;
		Kohana::$log->add(Log::DEBUG, '62 identifier::action_control - POST: ' . print_r($_POST, true));		

			// Создаем валидацию
			$post = Validation::factory($_POST)
				->rule('todo', 'not_empty', array(':value'))
				->rule('todo', 'in_array', array(':value', array_keys($this->options)));

			// Если выбран отчёт «карты без событий после даты» — проверяем дату
			if (Arr::get($_POST, 'todo') === 'cardNoEventDate') {
				// Проверяем дату
				$post->rule('event_date', 'not_empty')
					 ->rule('event_date', 'date');
			}

			// Отчёт 4: количество проходов по людям — нужны обе даты периода
			if (Arr::get($_POST, 'todo') === 'peoplePassCount') {
				$post->rule('date_from', 'not_empty')
					 ->rule('date_from', 'date')
					 ->rule('date_to', 'not_empty')
					 ->rule('date_to', 'date');
			}
			$arg=array();
			// Проверяем данные
			if ($_POST && $post->check()) {
			
			//сохраняю дату в сессию
			$event_date = $this->request->post('event_date');
			$date_from = $this->request->post('date_from');
			$date_to = $this->request->post('date_to');
			$rows_per_page = $this->request->post('rows_per_page');
            //Session::instance()->set('session_event_date', $event_date);
			Cookie::set('session_event_date', $event_date, 30 * 86400); // 30 дней в секундах
			Cookie::set('session_date_from', $date_from, 30 * 86400); // 30 дней в секундах
			Cookie::set('session_date_to', $date_to, 30 * 86400); // 30 дней в секундах
			Cookie::set('session_rows_per_page', $rows_per_page, 30 * 86400); // 30 дней в секундах
			
			
				$rows_per_page=Arr::get($post, 'rows_per_page', 50);
				// Данные валидны
				$todo = $post['todo'];
			
				switch ($todo) {
					case 'cardNoEvent':
						// Обработка для cardNoEvent
						$data=Model::factory('identifier')->cardNoEvent();
						//echo Debug::vars('93', count($data));exit;
						$view='cardNoEvent';
						break;
					case 'cardNoEventDate':
						// Обработка для cardNoEventDate (нужна дата)
						//параметры (в т.ч. дата) передаются в модель в свойства arg
						$model=Model::factory('identifier');
						$model->arg=array('event_date'=>$event_date);//передал аргументы (только дату, без служебных полей формы)
						
						$data = $model->cardNoEventDate();//получил результат
						$arg=$model->arg;//запоминаю аргументы для передачи в view
						$view='cardNoEventDate';//форма для вывода результата
						
						break;
					case 'allCards':
						// Обработка для allCards
						$data=Model::factory('identifier')->allCards();
						$view='allCards';
						break;
					case 'peoplePassCount':
						// Отчёт 4: количество проходов (события 50 и 70) по людям за период
						$model = Model::factory('identifier');
						$model->arg = array('date_from' => $date_from, 'date_to' => $date_to);
						$data = $model->peoplePassCount();

						if ($model->mess)
						{
							$this->set_flash_message('error', $model->mess);
							$this->redirect('identifier');
						}

						$arg = $model->arg;
						$view = 'peoplePassCount';
						break;
					case 'expiredCards':
						// Обработка для allCards
						$data=Model::factory('identifier')->expiredCards();
						$view='expiredCards';
						break;
					case 'inactiveCards':
						// Обработка для allCards
						$data=Model::factory('identifier')->inactiveCards();
						$view='inactiveCards';
						break;
					case 'invalidFormat':
						// Обработка для allCards
						$data=Model::factory('identifier')->invalidFormat();
						$view='invalidFormat';
						break;
					default:
						// Неизвестный тип отчёта: раньше здесь был echo Debug::vars(); exit;
						$this->set_flash_message('error', __('Неизвестный тип отчёта'));
						$this->redirect('identifier');
					break;					
											
					
				}
				 $session_event_date = Session::instance()->get('event_date', null);
				 
			
			$this->set_full_width(true);
				$content = View::factory(__('identifier/:view', array(':view'=>$view)), array(//начальная страница для работы с идентификаторами.
				//	'list'=>array_slice($data,0, $rows_per_page),
					'list'=>$data,
					'total_row_count'=>count($data),
					'rows_per_page'=>$rows_per_page,
					'type'=>$todo,
					'arg'=>$arg,
					'todo'=>$todo,
					'session_event_date'=>$session_event_date,
					
				));
			
				$this->template->content = $content;
				//echo View::factory('profiler/stats');
		
		
		
			} else {
				// Неверные/неполные данные формы (например, некорректный период)
				$this->set_flash_message('warning', __('Проверьте заполнение формы: даты указаны неверно или не заполнены'));
				$this->redirect('identifier');
			}
			
			
		
	}
	
	// Старый обработчик массовых действий удалён: он начинался с отладочного
	// echo Debug::vars($_POST); exit; и дублировал action_control (без CSRF/авторизации).
	// Массовые операции выполняются через action_control.
	
	/** 1.02.2026 сохранение в cvs массива подготовленной выборки
	*@input название модели, передается в POST
	*/
	public function action_save_csv() {
		$this->check_csrf();
		//echo Debug::vars('191', $_POST);exit;
		$post = Validation::factory($_POST)
				->rule('todo', 'not_empty', array(':value'))
				->rule('todo', 'in_array', array(':value', array_keys($this->options)));

			// Если форма отправлена и нажата кнопка карт без событий до даты
			if (isset($_POST['cardNoEvent'])) {
				// Проверяем дату
				$post->rule('event_date', 'not_empty')
					 ->rule('event_date', 'date');
			}

			// Проверяем данные
			if ($_POST && $post->check()) {
				
				$jsonData = Arr::get($_POST, 'arg', '');
				$arg = json_decode($jsonData, true); // true для массива

				// Страховка от битого/пустого arg: не передаём NULL в модель
				if ( ! is_array($arg)) {
					Kohana::$log->add(Log::WARNING, 'identifier::save_csv - некорректный arg: '.print_r($jsonData, true));
					$arg = array();
				}
				if ($this->request->method() === 'POST') {
						
						// Важно: полностью отключаем все шаблоны
						$this->auto_render = FALSE;
					
						// Не должно быть никакого вывода ДО заголовков!
						$method=Arr::get($post, 'todo');
												
						$model = Model::factory('identifier');
						$model->arg=$arg;
						if (method_exists($model, $method)) {
							$big_array = $model->$method();

							// Ошибка выборки/некорректный период — не отдаём пустой файл
							if ($model->mess) {
								$this->set_flash_message('error', $model->mess);
								$this->redirect('identifier');
							}
						} else {
							// Обработка ошибки
							throw new Kohana_Exception('Method :method not found', 
								[':method' => $method]);
						}

						$filename = 'export_' . Arr::get($post, 'todo') .'_'.date('Y-m-d_H-i-s') . '.csv';
						
						// 1. Очищаем все буферы вывода
						while (ob_get_level()) {
							ob_end_clean();
						}
						
						// 2. Устанавливаем заголовки
						header('Content-Type: application/octet-stream');
						header('Content-Disposition: attachment; filename="' . $filename . '"');
						header('Content-Transfer-Encoding: binary');
						header('Expires: 0');
						header('Cache-Control: must-revalidate');
						header('Pragma: public');
						
						// 3. Выводим BOM
						//echo "\xEF\xBB\xBF";
						
						// 4. Выводим CSV данные
						$output = fopen('php://output', 'w');
						
						if (empty($big_array)) {
							fputcsv($output, array(__('Нет данных за указанный период')), ';', '"');
						} else {
							fputcsv($output, array_keys(reset($big_array)), ';', '"');
							foreach ($big_array as $row) {
								fputcsv($output, $row, ';', '"');
							}
						}
						
						fclose($output);
						
						// 5. Завершаем скрипт
						
				}
			} else {
				// Раньше здесь выводился дамп Validation и exit (утечка отладочной информации)
				$this->set_flash_message('error', __('Не удалось подготовить файл: неверные параметры запроса'));
				$this->redirect('identifier');
			}
			exit;
	}
	//=======================28.03.2026 
	/**
		 * Обработка массовых действий с идентификаторами (картами)
		 * Принимает POST запросы от формы в представлениях identifier
		 */
		public function action_control()
		{
			$this->check_csrf();

			// Логируем входящие данные для отладки
			Kohana::$log->add(Log::DEBUG, '308 identifier::action_control - POST: ' . print_r($_POST, true));
			
			// Получаем и очищаем идентификаторы
			$identifiers = Arr::get($_POST, 'identifier', array());
			$prolong_date = Arr::get($_POST, 'prolong_date', null);
			
			if (is_array($identifiers)) {
				$identifiers = array_map(function($id) {
					// Кавычки больше не «срезаем»: экранирование выполняет модель.
					// Здесь убираем только пробельные символы по краям.
					if ( ! is_scalar($id))
					{
						return '';
					}

					return trim((string) $id);
				}, $identifiers);
				$identifiers = array_filter($identifiers, 'strlen');
			} else {
				$identifiers = array();
			}
			
			// Получаем тип операции
			$todo = Arr::get($_POST, 'todo');
			
			// Проверяем наличие идентификаторов
			if (empty($identifiers)) {
				Kohana::$log->add(Log::WARNING, '327 identifier::action_control - Не выбрано ни одного идентификатора');
				$this->set_flash_message('warning', __('Не выбрано ни одной карты для выполнения операции'));
				$this->redirect('identifier');
				return;
			}
			
			// Выполняем операцию в зависимости от типа
			switch ($todo) {
				case 'unactive':
					$this->process_unactive($identifiers);
					break;
					
				case 'delete':
					$this->process_delete($identifiers);
					break;
					
				case 'prolong': // пример дополнительной операции
					$this->process_prolong($identifiers, $prolong_date);
					break;
					
				default:
					Kohana::$log->add(Log::WARNING, '349 identifier::action_control - Неизвестная операция: :todo', 
						array(':todo' => $todo));
					$this->set_flash_message('error', __('Неизвестная операция: :operation', array(':operation' => $todo)));
					$this->redirect('identifier');
					return;
			}
			
			// P0: фиксированный редирект вместо $this->request->referrer().
			// Referer подконтролен клиенту (open redirect), а без него пользователь
			// уходил на дашборд и терял контекст отчёта.
			$this->redirect('identifier');
		}

		/**
		 * продление срока действия идентификаторов
		 * @param array $identifiers Массив ID карт
		 * @param prolong_date дата, до которой надо продлись карты
		 */
		private function process_prolong($identifiers, $prolong_date)
		{
			$result = $this->prolong_identifiers($identifiers, $prolong_date);
			
			// Устанавливаем flash сообщение
			$flash_type = $result['success'] ? 'success' : ($result['count_success'] > 0 ? 'warning' : 'error');
			
			$message = $result['message'];
			if (!empty($result['errors_list'])) {
				$message .= ' ' . __('Детали') . ': ' . implode('; ', array_slice($result['errors_list'], 0, 3));
				if (count($result['errors_list']) > 3) {
					$message .= '...';
				}
			}
			
			$this->set_flash_message($flash_type, $message);
			
			// Логируем результат
			Kohana::$log->add(Log::INFO, '382 prolong_identifiers завершена. Успешно: :success, Ошибок: :error', 
				array(':success' => $result['count_success'], ':error' => $result['count_error']));
		}

		/**
		 * Обработка деактивации карт
		 * @param array $identifiers Массив ID карт
		 */
		private function process_unactive($identifiers)
		{
			$result = $this->unactive_identifiers($identifiers);
			
			// Устанавливаем flash сообщение
			$flash_type = $result['success'] ? 'success' : ($result['count_success'] > 0 ? 'warning' : 'error');
			
			$message = $result['message'];
			if (!empty($result['errors_list'])) {
				$message .= ' ' . __('Детали') . ': ' . implode('; ', array_slice($result['errors_list'], 0, 3));
				if (count($result['errors_list']) > 3) {
					$message .= '...';
				}
			}
			
			$this->set_flash_message($flash_type, $message);
			
			// Логируем результат
			Kohana::$log->add(Log::INFO, '382 process_unactive завершена. Успешно: :success, Ошибок: :error', 
				array(':success' => $result['count_success'], ':error' => $result['count_error']));
		}

		/**
		 * Обработка удаления карт
		 * @param array $identifiers Массив ID карт
		 */
		private function process_delete($identifiers)
		{
			$result = $this->delete_identifiers($identifiers);
			
			// Устанавливаем flash сообщение
			$flash_type = $result['success'] ? 'success' : ($result['count_success'] > 0 ? 'warning' : 'error');
			
			$message = $result['message'];
			if (!empty($result['errors_list'])) {
				$message .= ' ' . __('Детали') . ': ' . implode('; ', array_slice($result['errors_list'], 0, 3));
				if (count($result['errors_list']) > 3) {
					$message .= '...';
				}
			}
			
			$this->set_flash_message($flash_type, $message);
			
			// Логируем результат
			Kohana::$log->add(Log::INFO, '408 process_delete завершена. Успешно: :success, Ошибок: :error', 
				array(':success' => $result['count_success'], ':error' => $result['count_error']));
		}

		/**
		 * Установка flash сообщения
		 * @param string $type Тип сообщения (success, warning, error, info)
		 * @param string $text Текст сообщения
		 */
		private function set_flash_message($type, $text)
		{
			Session::instance()->set('flash_message', array(
				'type' => $type,
				'text' => $text
			));
		}
		
		/**
		 * Деактивация выбранных идентификаторов (карт)
		 * @param array $identifiers Массив ID карт
		 * @return array Результат операции
		 */
		private function prolong_identifiers($identifiers, $prolong_date)
		{
			if (empty($identifiers)) {
				return array(
					'success' => false,
					'message' => __('Не выбрано ни одной карты'),
					'count_success' => 0,
					'count_error' => 0
				);
			}
			
			$total_success = 0;
			$total_errors = 0;
			$errors_list = array();
			
			// Разбиваем на чанки для безопасности SQL
			$chunks = array_chunk($identifiers, 500);
			
			$model = Model::factory('identifier');
			
			foreach ($chunks as $chunk_index => $chunk) {
				try {
					
					if ($model->prolong($chunk, $prolong_date)) {
						$total_success += count($chunk);
						Kohana::$log->add(Log::INFO, '455 Продлено :count карт (chunk :chunk)', 
							array(':count' => count($chunk), ':chunk' => $chunk_index + 1));
					} else {
						$error_msg = $model->mess ?: 'Неизвестная ошибка';
						$total_errors += count($chunk);
						$errors_list[] = "Chunk " . ($chunk_index + 1) . ": " . $error_msg;
						Kohana::$log->add(Log::ERROR, '461 Ошибка продления: :error', 
							array(':error' => $error_msg));
					}
				} catch (Exception $e) {
					$total_errors += count($chunk);
					$errors_list[] = "Chunk " . ($chunk_index + 1) . ": " . $e->getMessage();
					Kohana::$log->add(Log::ERROR, '468 Исключение при продлении: :error', 
						array(':error' => $e->getMessage()));
				}
			}
			
			return array(
				'success' => ($total_errors == 0),
				'message' => sprintf(__('Продлено карт: %d. Ошибок: %d.'), $total_success, $total_errors),
				'count_success' => $total_success,
				'count_error' => $total_errors,
				'errors_list' => $errors_list,
				'identifiers' => $identifiers
			);
		}

		/**
		 * Деактивация выбранных идентификаторов (карт)
		 * @param array $identifiers Массив ID карт
		 * @return array Результат операции
		 */
		private function unactive_identifiers($identifiers)
		{
			if (empty($identifiers)) {
				return array(
					'success' => false,
					'message' => __('Не выбрано ни одной карты'),
					'count_success' => 0,
					'count_error' => 0
				);
			}
			
			$total_success = 0;
			$total_errors = 0;
			$errors_list = array();
			
			// Разбиваем на чанки для безопасности SQL
			$chunks = array_chunk($identifiers, 500);
			
			$model = Model::factory('identifier');
			
			foreach ($chunks as $chunk_index => $chunk) {
				try {
					
					if ($model->setUnactive($chunk)) {
						$total_success += count($chunk);
						Kohana::$log->add(Log::INFO, '455 Деактивировано :count карт (chunk :chunk)', 
							array(':count' => count($chunk), ':chunk' => $chunk_index + 1));
					} else {
						$error_msg = $model->mess ?: 'Неизвестная ошибка';
						$total_errors += count($chunk);
						$errors_list[] = "Chunk " . ($chunk_index + 1) . ": " . $error_msg;
						Kohana::$log->add(Log::ERROR, '461 Ошибка деактивации: :error', 
							array(':error' => $error_msg));
					}
				} catch (Exception $e) {
					$total_errors += count($chunk);
					$errors_list[] = "Chunk " . ($chunk_index + 1) . ": " . $e->getMessage();
					Kohana::$log->add(Log::ERROR, '468 Исключение при деактивации: :error', 
						array(':error' => $e->getMessage()));
				}
			}
			
			return array(
				'success' => ($total_errors == 0),
				'message' => sprintf(__('Деактивировано карт: %d. Ошибок: %d.'), $total_success, $total_errors),
				'count_success' => $total_success,
				'count_error' => $total_errors,
				'errors_list' => $errors_list,
				'identifiers' => $identifiers
			);
		}

		/**
		 * Удаление выбранных идентификаторов (карт)
		 * @param array $identifiers Массив ID карт
		 * @return array Результат операции
		 */
		private function delete_identifiers($identifiers)
		{
			if (empty($identifiers)) {
				return array(
					'success' => false,
					'message' => __('Не выбрано ни одной карты'),
					'count_success' => 0,
					'count_error' => 0
				);
			}
			
			$total_success = 0;
			$total_errors = 0;
			$errors_list = array();
			
			// Разбиваем на чанки для безопасности SQL
			$chunks = array_chunk($identifiers, 500);
			
			foreach ($chunks as $chunk_index => $chunk) {
				try {
					$model = Model::factory('identifier');
					if ($model->delCardArray($chunk)) {
						$total_success += count($chunk);
						Kohana::$log->add(Log::INFO, '510 Удалено :count карт (chunk :chunk)', 
							array(':count' => count($chunk), ':chunk' => $chunk_index + 1));
					} else {
						$error_msg = $model->mess ?: 'Неизвестная ошибка';
						$total_errors += count($chunk);
						$errors_list[] = "Chunk " . ($chunk_index + 1) . ": " . $error_msg;
						Kohana::$log->add(Log::ERROR, '516 Ошибка удаления: :error', 
							array(':error' => $error_msg));
					}
				} catch (Exception $e) {
					$total_errors += count($chunk);
					$errors_list[] = "Chunk " . ($chunk_index + 1) . ": " . $e->getMessage();
					Kohana::$log->add(Log::ERROR, '522 Исключение при удалении: :error', 
						array(':error' => $e->getMessage()));
				}
			}
			
			return array(
				'success' => ($total_errors == 0),
				'message' => sprintf(__('Удалено карт: %d. Ошибок: %d.'), $total_success, $total_errors),
				'count_success' => $total_success,
				'count_error' => $total_errors,
				'errors_list' => $errors_list,
				'identifiers' => $identifiers
			);
		}
	
}
