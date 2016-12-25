<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Digest extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->library('session');
        $this->load->helper(array('url','parser'));
        $this->load->database();
		$this->load->model('parser_model');
	}

	public function index()
	{

		$qty        =3;             // количество выбираемых новостей из одного источника
		$idsPubs    = array(1,2,3); // id источников информации
		$namePubs   = array(
			'Частник',
			'IvanovoNews',
			'Курсив');
		$noError    = true;         // индикатор ошибки
		$msg        ='';            // сообщение (при ошибке)
		$viewedTime = '';           // момент времени, на который просматриваются новости
		$pagination = array(
			'/digest/toLeft/',
			'/digest/toRight/');
		$row = array();

		// стартуем сессию, если в сессии нет timing , то
		// определяем временной диапазон, имеющихся новостей (левая граница и правая)

		if ( ! isset($_SESSION['timing'])) {
			$_SESSION['timing'] = array();
			// пределим левую и правую границу временного диапазона
			$row = $this->parser_model->border('left');
			if ($row !== false) $_SESSION['timing']['left'] = $row['ad_date'];
			else {
				$noError = false;
				$msg.= __METHOD__.' Нет левой границы. Ошибка БД.';
			}
			$row = $this->parser_model->border('right');
			if ($row !== false) $_SESSION['timing']['right'] = $row['ad_date'];
			else {
				$noError = false;
				$msg.= __METHOD__.' Нет правой границы. Ошибка БД.';
			}
		}
		if ($noError){
			// первое обращение к сайту
			if (! isset($_SESSION['timing']['viewedTime'])){
				$_SESSION['timing']['viewedTime'] = $_SESSION['timing']['right'];
				$viewedTime = $_SESSION['timing']['right'];
			}else{
				// если обращения уже были, то должно уже заранее быть сформировано
				// $_SESSION['timing']['viewedTime']
				$viewedTime = $_SESSION['timing']['viewedTime'];
				// за время пока смотрят страницу информация в БД может обновиться
				$row = $this->parser_model->border('right');
				if ($row !== false) $_SESSION['timing']['right'] = $row['ad_date'];
				else {
					$noError = false;
					$msg.= __METHOD__.' Нет правой границы. Ошибка БД.';
				}
			}
			// далее выбираем по 3 новости из каждого источника, с условием
			// ad_date <= viewedTime
			$work = array();
			foreach ($idsPubs as $idPub) {
				$queryRes = $this->parser_model->find_ads($viewedTime, $idPub, $qty);
				if ($queryRes === false){
					$noError = false;
					$msg.=__METHOD__."Нет данных для {$idPub} источника на {$viewedTime}";
				}
				if ($noError){
					for ($i=0; $i <count($queryRes) ; $i++) {
						$work = $queryRes[$i];
						foreach ($work as $key => $value) {
						 	$data[$key][$idPub][$i] = $value;
						}
					}  // /for
				}
			}  // /foreach
		}   // /if
		// нужен ещё и прогноз погоды
		if ($noError) {
			$queryRes = $this->parser_model->mi();
			if ($queryRes === false){
				$noError = false;
				$msg.=__METHOD__." Нет метео данных.";
			}
			for ($i=0; $i < count($queryRes); $i++) {
				$work = $queryRes[$i];
				foreach ($work as $key => $value) {
					$data[$key][$i] = $value;
				}
			}
		}

		// данные для страницы собраны, переходим к выводу
		if ($noError){
			// для правой и левой границы временного интервала поправляем ссылки
			if ($viewedTime == $_SESSION['timing']['right']) $pagination[1] = 'javascript:void(0);';
			if ($viewedTime == $_SESSION['timing']['left']) $pagination[0] = 'javascript:void(0);';

			// добавляем ещё данные для передачи во view
			// для новостей:
			$data['idsPubs']    = $idsPubs;            // id источников
			$data['namePubs']   = $namePubs;           // названия
			$data['viewedTime'] = regTime($viewedTime);// время в привычном формате
			// для пагинации
			$data['pagination'] = $pagination;

			$this->load->view('header', $data);
			$this->load->view('corpus', $data);
			$this->load->view('bottom');
		}

		if ($noError === false){
			echo $msg;
			exit;
		}
	} // /index

	public function toLeft()
	{
		$noError = true;
		$msg = '';
		$row =array();

		$tView = $_SESSION['timing']['viewedTime'];
		$tView = modifyTime($tView, '-30 minute');
		$row = $this->parser_model->until($tView);
		if ($row === false){
			$msg.= __METHOD__." Не нашлось данных левее {$tView}.";
			$noError = false;
		}

		if ($noError){
			// не забываем про то, что уже нашли
			$_SESSION['timing']['viewedTime'] = $row['ad_date'];
			redirect(base_url('/digest/'), 'location', 301);
		}

		echo $msg;
		exit;
	}
	public function toRight()
	{
		$noError = true;
		$msg = '';
		$row =array();
		$tView = $_SESSION['timing']['viewedTime'];
		$row = $this->parser_model->later($tView);
		if ($row === false){
			$msg.= __METHOD__." Не нашлось данных правее {$tView}.";
			$noError = false;
		}
		if ($noError) {
			$tView = $row['ad_date'];
			$tView = modifyTime($tView, '+50 minute');
			$_SESSION['timing']['viewedTime'] = $tView;
			$this->toLeft();
		}
	}

	public function toBegin()
	{
		$row = array();
		$row = $this->parser_model->border('right');
		if ($row !== false) $_SESSION['timing']['right'] = $row['ad_date'];
		else {
			$noError = false;
			$msg.= __METHOD__.' Нет правой границы. Ошибка БД.';
		}
		$_SESSION['timing']['viewedTime'] = $_SESSION['timing']['right'];
		$this->index();
	}

}
