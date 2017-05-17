<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Parser extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->helper('parser');
		$this->load->library('simple_html_dom');
		$this->load->database();
		$this->load->model('parser_model');
	}

	// КУРСИВ

	public function mainCursiv($qty=3)
	{
		$host  = 'http://cursiv.ru';
		$url   = 'http://cursiv.ru/';
		$links = array();
		$str   = getPage($url);             // получаем страницу
		$this->simple_html_dom->load($str); // скармливаем классу
		$i=0;
		foreach($this->simple_html_dom->find('div[id=news] a') as $e){
			// в 0-й - Архив новостей
			if ($i>0){
				$link = $host.$e->href;
				// в массив пишем только те значения, которые по мнению фильтра
				// являются URL
				if (filter_var($link, FILTER_VALIDATE_URL)){
					$links[] = $link;
					if (count($links) == $qty) break;
				}
			}
			$i++;
		}
		arsort($links);                  // сортируем в порядке убывания № новости
		$this->simple_html_dom->clear();
		return $links;
	}
	public function pagesCursiv($links=array())
	{
		// проходим по всем ссылкам из массива $links
		foreach ($links as $link) {
			$str = getPage($link);                  // получаем страницу
			$this->simple_html_dom->load($str);
			$e = $this->simple_html_dom->find('h1');
			$h1 = $e[0]->plaintext;                 // достаём из неё  h1
			$h1 = html_entity_decode($h1);

			// у нас есть ссылка и h1, проверим нет ли аналогичной записи в БД
	 		$h1 = htmlspecialchars($h1, ENT_QUOTES);
	 		$hash = md5($link . $h1);
	 		// есть ли запись в БД?
	 		$replay = $this->parser_model->find_hash($hash);
			if ($replay == false) {
				$e = $this->simple_html_dom->find('div[class=text]');
				$content = $e[0]->plaintext;            // и текст статьи
				$this->simple_html_dom->clear();
				$content = cropContent($content, 250);  // обрезаем текст до 250 символов
				// заменяем имеющиеся html-сущности на символы html
				$content = html_entity_decode($content);
				// заполняем поля запроса и пишем анонс статьи в БД
				$fields = array(
					null,
					'\''.date('Y-m-d H:i:s').'\'',
					'\''.$hash. '\'',
					3,
					$this->db->escape($link),
					$this->db->escape($h1),
					$this->db->escape(htmlspecialchars($content, ENT_QUOTES))
					);
				$replay = $this->parser_model->i_ad($fields);
				if ($replay === false){
				 	echo __METHOD__ . ' Ошибка записи анонса статьи в базу данных.'.
				 	'<br>'.$h1.'<br>'.$content.'<br>';
				}
			}else{
				$this->simple_html_dom->clear();
				// просто ничего не пишем в БД
				echo "Запись с ad_hash = {$hash} имеет ad_id = {$replay}.<br>";
			}

		}
	}

	// ЧАСТНИК

	public function newsChastnik($qty=3)
	{
		$host = 'https://chastnik.ru';
		$url  = 'https://chastnik.ru/news/';
		$links = array();
		$allLinks = array();

		$str   = getPage($url);             // получаем страницу
		$this->simple_html_dom->load($str); // скармливаем классу
		foreach($this->simple_html_dom->find('div[class=block__title news__title] a') as $e){
			$allLinks[] = $e->href;
		}
		$allLinks = array_unique($allLinks);
		foreach ($allLinks as $adds) {
			$link = $host.$adds;
			// в массив пишем только те значения, которые по мнению фильтра
			// являются URL
			if (filter_var($link, FILTER_VALIDATE_URL)){
				$links[] = $link;
				if (count($links) == $qty) break;
			}
		}
		ksort($links, SORT_NUMERIC);      // упорядочиваем индексы
		$this->simple_html_dom->clear();
		return $links;
	}

	public function pagesChastnik($links=array())
	{

		// проходим по всем ссылкам из массива $links
		foreach ($links as $link){
			$str = getPage($link);                  // получаем страницу
			$this->simple_html_dom->load($str);
			$e = $this->simple_html_dom->find('h1[class=news-detail__title]');
			$h1 = $e[0]->plaintext;                 // достаём из неё  h1
			$h1 = html_entity_decode($h1);

			// у нас есть ссылка и h1, проверим нет ли аналогичной записи в БД
	 		$h1 = htmlspecialchars($h1, ENT_QUOTES);
	 		$hash = md5($link . $h1);
	 		// есть ли запись в БД?
	 		$replay = $this->parser_model->find_hash($hash);
			if ($replay == false){
				$e = $this->simple_html_dom->find('div[class=news-detail__text content]');
				$content = $e[0]->plaintext;

				$this->simple_html_dom->clear();
				$content = cropContent($content, 250);  // обрезаем текст до 250 символов
				// заменяем имеющиеся html-сущности на символы html
				$content = html_entity_decode($content);
				// заполняем поля запроса и пишем анонс статьи в БД
				$fields = array(
					null,
					'\''.date('Y-m-d H:i:s').'\'',
					'\''.$hash. '\'',
					1,
					$this->db->escape($link),
					$this->db->escape($h1),
					$this->db->escape(htmlspecialchars($content, ENT_QUOTES))
					);
				$replay = $this->parser_model->i_ad($fields);
				if ($replay === false){
				 	echo __METHOD__ . ' Ошибка записи анонса статьи в базу данных.'.
				 	'<br>'.$h1.'<br>'.$content.'<br>';
				}
			}else{
				$this->simple_html_dom->clear();
				// просто ничего не пишем в БД
				echo "Запись с ad_hash = {$hash} имеет ad_id = {$replay}.<br>";
			}

		}
	}

	// IVANOVO NEWS

	public function newsIN($qty=3)
	{
		$host = 'http://ivanovonews.ru';
		$url  = 'http://ivanovonews.ru/news/';
		$links = array();
		$str   = getPage($url);             // получаем страницу
		$this->simple_html_dom->load($str); // скармливаем классу
		foreach($this->simple_html_dom->find('ul[class=news] a[class=name]') as $e){
				$link = $host.$e->href;
				// в массив пишем только те значения, которые по мнению фильтра
				// являются URL
				if (filter_var($link, FILTER_VALIDATE_URL)){
					$links[] = $link;
					if (count($links) == $qty) break;
				}
		}
		arsort($links);                  // сортируем в порядке убывания № новости
		$this->simple_html_dom->clear();
		return $links;
	}

	public function pagesIN($links=array())
	{
		// проходим по всем ссылкам из массива $links
		foreach ($links as $link){
			$str = getPage($link);                  // получаем страницу
			$this->simple_html_dom->load($str);
			$e = $this->simple_html_dom->find('h1');
			$h1 = $e[0]->plaintext;                 // достаём из неё  h1
			$h1 = html_entity_decode($h1);

			// у нас есть ссылка и h1, проверим нет ли аналогичной записи в БД
	 		$h1 = htmlspecialchars($h1, ENT_QUOTES);
	 		$hash = md5($link . $h1);
	 		// есть ли запись в БД?
	 		$replay = $this->parser_model->find_hash($hash);
			if ($replay == false){
				// подчистим некоторые элементы:h1,дату,жирный шрифт
				$e[0]->innertext = '';
				$e = $this->simple_html_dom->find('div[id=detail-text] div[class=date]');
				$e[0]->innertext = '';
				$e = $this->simple_html_dom->find('div[id=detail-text] div[style="font-weight: bold"]');
				$e[0]->innertext = '';
				$e = $this->simple_html_dom->find('div[id=detail-text]');
				$content = $e[0]->plaintext;

				$this->simple_html_dom->clear();
				$content = cropContent($content, 250);  // обрезаем текст до 250 символов
				// заменяем имеющиеся html-сущности на символы html
				$content = html_entity_decode($content);
				// заполняем поля запроса и пишем анонс статьи в БД
				$fields = array(
					null,
					'\''.date('Y-m-d H:i:s').'\'',
					'\''.$hash. '\'',
					2,
					$this->db->escape($link),
					$this->db->escape($h1),
					$this->db->escape(htmlspecialchars($content, ENT_QUOTES))
					);
				$replay = $this->parser_model->i_ad($fields);
				if ($replay === false){
				 	echo __METHOD__ . ' Ошибка записи анонса статьи в базу данных.'.
				 	'<br>'.$h1.'<br>'.$content.'<br>';
				}
			}else{
				// просто ничего не пишем в БД
				echo "Запись с ad_hash = {$hash} имеет ad_id = {$replay}.<br>";
			}
		}
	}

	// ПОГОДА

	public function meteoInfo()
	{
		$url = 'http://meteoinfo.ru/rss/forecasts/27347';
		$str = getPage($url);
		$mi  = new SimpleXMLElement($str);
		for ($i=0; $i <2 ; $i++) {
			$fields = array(
				$i+1,
				'\''.date('Y-m-d H:i:s').'\'',
				$this->db->escape(htmlspecialchars($mi->channel->item[$i]->title)),
				$this->db->escape(htmlspecialchars($mi->channel->item[$i]->description))
			);
			// запрос к модели на обновление строки в таблице метео инфо
			$replay = $this->parser_model->u_mi($fields);
			if ($replay === false){
			 	echo __METHOD__ . ' Ошибка обновления метео инфо в базе данных.';
			}
		}
	}

	public function cron($psw='')
	{
		$hash = '$2y$10$CUw83L.88hBijvrm6O8OkuHDLzYeiyyWUsQanaB5jqIE2vzEAke/m';
		// justnow
		$noError = true;

		if ( ! password_verify($psw, $hash)) $noError = false;

		if ($noError){
			$links = array();
			// количество статей, забираемых с 1 сайта
		    $qty = 3;
			// расписание запуска скрипта
		    $timing = array('09', '12', '13', '17', '19');
		    $oclock = date('H');
		    if ( ! in_array($oclock, $timing)) $noError = false;
		    $minute = date('i');
		    // скрипт имеет возможность запуска только в 0 минуту часа
		    if ($minute > 0) $noError = false;

		}
		if ($noError){
		    switch ($oclock) {
		    	case '09':
		    		// парсим Курсив
					$links = $this->mainCursiv($qty);
					$this->pagesCursiv($links);
					// парсим Частник
					$links = $this->newsChastnik($qty);
					$this->pagesChastnik($links);
					// парсим IvanovoNews
					$links = $this->newsIN($qty);
					$this->pagesIN($links);
		    		break;
		    	case '12':
					// получаем прогноз погоды
					$this->meteoInfo();
		    		break;
		    	case '13':
					// парсим Курсив
					$links = $this->mainCursiv($qty);
					$this->pagesCursiv($links);
					// парсим Частник
					$links = $this->newsChastnik($qty);
					$this->pagesChastnik($links);
					// парсим IvanovoNews
					$links = $this->newsIN($qty);
					$this->pagesIN($links);
		    		break;
		    	case '17':
		    		// парсим Курсив
					$links = $this->mainCursiv($qty);
					$this->pagesCursiv($links);
					// парсим Частник
					$links = $this->newsChastnik($qty);
					$this->pagesChastnik($links);
					// парсим IvanovoNews
					$links = $this->newsIN($qty);
					$this->pagesIN($links);
		    		break;
		    	case '19':
					// получаем прогноз погоды
					$this->meteoInfo();
		    		break;
		    	default:
			    	exit('Hack?!');
			    	break;
		    }
		}
	}
	// удаление по cron записей старше $age дней
	public function deleteOld($age = 30)
	{
		$this->config->load('tuning');
		$noError = true;
		// полюбасу оставляем записи за 10 крайних дней
		if ($age < 10){
			$noError = false;
		}
		if ($noError){
			$now = date('Y-m-d H:i:s');
			$fromMoment = modifyTime($now, '-'.$age.'day');

			$respond = $this->parser_model->delete($fromMoment);
			if ($respond === true){
				echo "Записи старше $fromMoment успешно удалены.";
			} else{
				$noError = false;
			}
		}
		if ($noError === false) {
			$e_mail    = $this->config->item('report_email');
			$subject   = 'Ошибка выполнения cron';
			$message[] = '<html>';
			$message[] = '<head><title>Ошибка выполнения cron</title></head>';
			$message[] = '<body>Что-то пошло не так при удалнении записей из БД.<br>';
			$message[] = "Удалялись записи старше $fromMoment .";
			$message[] = 'Нужно смотреть сайт slice и БД.';
			$message[] = '</body></html>';

            mail_utf8($e_mail, $e_mail, $subject, implode("\r\n", $message));
		}
	}
	// добавление значения поля ad_hash в таблицу digest
	/*
	public function addHashField()
	{
		$rows =$this->parser_model->all_for_hash();
		if ($rows !== false){
			$i = 0;
			foreach ($rows as $row){
				// формируем уникальный хеш-код для каждой записи
				$hash = '\''.md5($row['src_link'] . $row['ad_title']).'\'' ;

				$res = $this->parser_model->upd_record($row['ad_id'], $hash);
				if ($res === false){
					echo "Неудачное обновление записи с ad_id = {$row['ad_id']}<br>";
				} else $i++;
			}
			echo "Обновлено {$i} записей.";
		}

	}
	*/

}