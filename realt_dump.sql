-- phpMyAdmin SQL Dump
-- version 4.9.1
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Ноя 12 2020 г., 16:00
-- Версия сервера: 5.7.21-20-beget-5.7.21-20-1-log
-- Версия PHP: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `redmit_test`
--

-- --------------------------------------------------------

--
-- Структура таблицы `general_data`
--
-- Создание: Окт 29 2020 г., 15:20
--

DROP TABLE IF EXISTS `general_data`;
CREATE TABLE `general_data` (
  `id` int(11) NOT NULL COMMENT 'ID объявления',
  `id_source` int(11) NOT NULL COMMENT 'ID источника',
  `description` varchar(3000) NOT NULL,
  `link` varchar(700) NOT NULL COMMENT 'Ссылка на объявление',
  `status` varchar(300) NOT NULL COMMENT 'Статус из таблицы G Sheet',
  `auction` varchar(300) NOT NULL COMMENT 'Торг. G Sheets. ',
  `bonus` varchar(1000) NOT NULL COMMENT 'Бонус. G Sheets.',
  `owners` varchar(1000) NOT NULL COMMENT 'Собственники. G Sheets.',
  `registered` varchar(1000) NOT NULL COMMENT 'Прописанные. G Sheets.',
  `check_out` varchar(1000) NOT NULL COMMENT '	Выписаться. G Sheets.',
  `constitutive` varchar(1000) NOT NULL COMMENT 'Правоустанавливающие. G Sheets.	',
  `price_sale` varchar(300) NOT NULL COMMENT 'Цена продажи. G sheets',
  `target_profit` varchar(300) NOT NULL COMMENT 'Целевой доход. G Sheets',
  `comments` varchar(3000) NOT NULL COMMENT 'Комментарии',
  `price_actual` int(11) NOT NULL COMMENT 'Актуальная цена',
  `price_start` int(11) NOT NULL COMMENT 'Цена при попадании в БД',
  `address` varchar(400) NOT NULL COMMENT 'Адрес',
  `street` varchar(200) NOT NULL COMMENT 'Улица',
  `house` varchar(20) NOT NULL COMMENT 'Номер дома',
  `square_meter_price` varchar(100) NOT NULL COMMENT 'Цена кв. метра',
  `phones` varchar(100) NOT NULL COMMENT 'Номера телефонов',
  `complex_name` varchar(100) NOT NULL COMMENT 'Название ЖК',
  `repair` varchar(100) NOT NULL COMMENT 'Ремонт',
  `balconies` varchar(100) NOT NULL COMMENT 'Балкон',
  `restroom` varchar(100) NOT NULL COMMENT 'Туалет',
  `ceiling_height` varchar(100) NOT NULL COMMENT 'Высота потолка',
  `total_area` varchar(100) NOT NULL COMMENT 'Общая площадь',
  `current_floor` int(11) NOT NULL COMMENT 'Текущий этаж',
  `total_floors` int(11) NOT NULL COMMENT 'Всего этажей',
  `objecttype` varchar(80) NOT NULL COMMENT 'Тип объекта',
  `building_year` varchar(200) NOT NULL COMMENT 'Год постройки',
  `views_all` int(11) NOT NULL COMMENT 'Всего просмотров',
  `ad_published` varchar(100) NOT NULL COMMENT 'Когда опубликовано',
  `ad_added` date NOT NULL COMMENT 'Когда добавлено в бд',
  `ad_remove` date DEFAULT NULL COMMENT 'Когда было удалено',
  `with_photos` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Имеет фото'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Базовая информция о объявлении';

-- --------------------------------------------------------

--
-- Структура таблицы `history_data`
--
-- Создание: Окт 29 2020 г., 15:20
--

DROP TABLE IF EXISTS `history_data`;
CREATE TABLE `history_data` (
  `id` int(11) NOT NULL COMMENT 'ID записи. Обычный инкремент',
  `id_ad` int(11) NOT NULL DEFAULT '0' COMMENT 'ID объявления',
  `id_source` int(11) NOT NULL COMMENT 'ID источника объявления',
  `date_added` date NOT NULL COMMENT 'Дата добавления этой строки',
  `price` int(11) NOT NULL COMMENT 'цена в рос. рублях на эту дату',
  `square_meter_price` varchar(30) NOT NULL COMMENT 'Цена кв.метра',
  `phones` varchar(70) NOT NULL COMMENT 'Телефоны через запятую',
  `views` int(11) NOT NULL COMMENT 'просмотров в этот день'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='История изменений атрибутов';

-- --------------------------------------------------------

--
-- Структура таблицы `market_data`
--
-- Создание: Окт 29 2020 г., 15:20
--

DROP TABLE IF EXISTS `market_data`;
CREATE TABLE `market_data` (
  `id` int(11) NOT NULL COMMENT 'Инкремент для уникальности',
  `address` varchar(500) NOT NULL COMMENT 'Адрес жилья',
  `objecttype` varchar(100) NOT NULL COMMENT 'Тип объекта',
  `price` int(11) NOT NULL COMMENT 'Цена продажи',
  `square_meter_price` int(11) NOT NULL COMMENT 'Рыночная цена кв.метра',
  `house` varchar(20) NOT NULL COMMENT 'Номер дома',
  `street` varchar(100) NOT NULL COMMENT 'Название улицы',
  `current_floor` int(11) NOT NULL COMMENT 'Этаж',
  `total_floors` int(11) NOT NULL COMMENT 'Этажность'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Рынчочные цены квартир';

-- --------------------------------------------------------

--
-- Структура таблицы `sources`
--
-- Создание: Окт 29 2020 г., 15:20
--

DROP TABLE IF EXISTS `sources`;
CREATE TABLE `sources` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL,
  `hint` varchar(25) NOT NULL COMMENT 'Что показываем юзеру при выводе',
  `link_prefix` varchar(300) NOT NULL COMMENT 'Для формирования ссылки объявления'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Источники наших обновлений';

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `general_data`
--
ALTER TABLE `general_data`
  ADD PRIMARY KEY (`id`,`id_source`) USING BTREE;

--
-- Индексы таблицы `history_data`
--
ALTER TABLE `history_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_ad` (`id_ad`,`id_source`);

--
-- Индексы таблицы `market_data`
--
ALTER TABLE `market_data`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `sources`
--
ALTER TABLE `sources`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `history_data`
--
ALTER TABLE `history_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID записи. Обычный инкремент';

--
-- AUTO_INCREMENT для таблицы `market_data`
--
ALTER TABLE `market_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Инкремент для уникальности';

--
-- AUTO_INCREMENT для таблицы `sources`
--
ALTER TABLE `sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
