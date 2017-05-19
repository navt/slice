# slice

Приложение на CodeIgniter парсит 3 сайта-источника для сбора новостей.<br>
Парсер запускается по cron 3 раза в день. Используется класс simple_html_dom.<br>
Используются 2 таблицы базы данных:<br>

	-- Структура таблицы `slc_digest`

	CREATE TABLE `slc_digest` (
	  `ad_id` int(11) NOT NULL,
	  `ad_date` datetime NOT NULL,
	  `ad_hash` char(32) DEFAULT NULL,
	  `src_id` int(11) NOT NULL,
	  `src_link` varchar(255) NOT NULL,
	  `ad_title` varchar(255) NOT NULL,
	  `ad_text` text NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

	-- Структура таблицы `slc_meteoinfo`

	CREATE TABLE `slc_meteoinfo` (
	  `mi_id` int(11) NOT NULL,
	  `mi_date` datetime NOT NULL,
	  `mi_title` varchar(50) NOT NULL,
	  `mi_description` varchar(255) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;