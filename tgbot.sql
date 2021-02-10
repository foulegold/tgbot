-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Фев 10 2021 г., 05:36
-- Версия сервера: 10.3.22-MariaDB
-- Версия PHP: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `tgbot`
--

-- --------------------------------------------------------

--
-- Структура таблицы `pages`
--

CREATE TABLE `pages` (
  `page_id` int(15) NOT NULL COMMENT 'ID профиля',
  `social_network_id` int(11) DEFAULT NULL COMMENT 'Соц. сеть',
  `link` varchar(255) NOT NULL COMMENT 'Ссылка'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `pages`
--

INSERT INTO `pages` (`page_id`, `social_network_id`, `link`) VALUES
(2780153, 1, 'http://vk.com/id2780153'),
(183876708, 1, 'http://vk.com/id183876708'),
(192515534, 1, 'http://vk.com/id192515534'),
(574129984, 1, 'http://vk.com/id574129984');

-- --------------------------------------------------------

--
-- Структура таблицы `social_networks`
--

CREATE TABLE `social_networks` (
  `id` int(11) NOT NULL,
  `name` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `social_networks`
--

INSERT INTO `social_networks` (`id`, `name`) VALUES
(2, 'instagram.com'),
(1, 'vk.com');

-- --------------------------------------------------------

--
-- Структура таблицы `tg_admins`
--

CREATE TABLE `tg_admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL DEFAULT '',
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `get_errors` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Получать ошибки'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tg_admins`
--

INSERT INTO `tg_admins` (`id`, `username`, `active`, `get_errors`) VALUES
(347860214, 'foulegold', 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `tg_answers`
--

CREATE TABLE `tg_answers` (
  `id` int(11) NOT NULL,
  `command_in_id` int(11) NOT NULL COMMENT 'Id входящей команды',
  `answer` varchar(255) NOT NULL COMMENT 'Команда для ответа пользователю',
  `itsa_button` tinyint(4) NOT NULL DEFAULT 1,
  `inline_button` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Является ли поле кнопкой клавиатуры',
  `active` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tg_answers`
--

INSERT INTO `tg_answers` (`id`, `command_in_id`, `answer`, `itsa_button`, `inline_button`, `active`) VALUES
(1, 1, 'Подключить подписку', 1, 0, 1),
(2, 1, 'Мои подписки', 1, 0, 1),
(3, 1, 'Здесь будет приветствие', 0, 0, 1),
(4, 2, 'Выберите соц. сеть для подключения', 0, 1, 1),
(5, 2, 'vk.com', 1, 1, 1),
(6, 2, 'instagram.com', 1, 1, 1),
(7, 2, 'Отмена', 1, 1, 1),
(9, 3, 'Для подключения подписки, необходимо прислать ссылку на страницу/группу, или id.\r\nМожно подключить только открытые профили.', 0, 0, 1),
(10, 3, 'Отмена', 1, 1, 1),
(11, 4, 'Для подключения подписки, необходимо прислать ссылку на страницу/группу, или id.\r\nМожно подключить только открытые профили.', 0, 0, 1),
(12, 4, 'Отмена', 1, 1, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `tg_chat_history`
--

CREATE TABLE `tg_chat_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `itsa_bot_message` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Сообщение писал сам бот?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `tg_commands`
--

CREATE TABLE `tg_commands` (
  `id` int(11) NOT NULL,
  `command_in` varchar(255) NOT NULL,
  `wait_answer` tinyint(4) NOT NULL DEFAULT 0,
  `type_of_answer` int(11) DEFAULT NULL,
  `previous_command_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tg_commands`
--

INSERT INTO `tg_commands` (`id`, `command_in`, `wait_answer`, `type_of_answer`, `previous_command_id`) VALUES
(1, '/start', 0, NULL, 0),
(2, 'подключить подписку', 0, NULL, 1),
(3, 'vk.com', 1, 2, 2),
(4, 'instagram.com', 1, 3, 2);

-- --------------------------------------------------------

--
-- Структура таблицы `tg_currentlevel`
--

CREATE TABLE `tg_currentlevel` (
  `user_id` int(11) NOT NULL,
  `current_command_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tg_currentlevel`
--

INSERT INTO `tg_currentlevel` (`user_id`, `current_command_id`) VALUES
(347860214, 1),
(242066691, 4);

-- --------------------------------------------------------

--
-- Структура таблицы `tg_subscription`
--

CREATE TABLE `tg_subscription` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'ID tg пользователя',
  `page_id` int(15) NOT NULL COMMENT 'ID профиля',
  `active` tinyint(4) NOT NULL DEFAULT 1 COMMENT 'Активность',
  `wall_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tg_subscription`
--

INSERT INTO `tg_subscription` (`id`, `user_id`, `page_id`, `active`, `wall_count`) VALUES
(5, 347860214, 183876708, 1, 12),
(6, 347860214, 192515534, 1, 344),
(7, 347860214, 2780153, 0, 0),
(8, 347860214, 574129984, 0, 0);

-- --------------------------------------------------------

--
-- Структура таблицы `tg_types_of_answers`
--

CREATE TABLE `tg_types_of_answers` (
  `id` int(11) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tg_types_of_answers`
--

INSERT INTO `tg_types_of_answers` (`id`, `type`) VALUES
(1, 'link'),
(2, 'vk_linkID'),
(3, 'in_linkID');

-- --------------------------------------------------------

--
-- Структура таблицы `tg_users`
--

CREATE TABLE `tg_users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL DEFAULT '',
  `first_name` varchar(100) NOT NULL DEFAULT '',
  `last_name` varchar(100) NOT NULL DEFAULT '',
  `language_code` varchar(10) NOT NULL DEFAULT '',
  `is_bot` tinyint(1) NOT NULL DEFAULT 0,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `tg_users`
--

INSERT INTO `tg_users` (`id`, `username`, `first_name`, `last_name`, `language_code`, `is_bot`, `active`) VALUES
(242066691, 'iliavovk', 'Илья', 'Вовк', 'en', 0, 1),
(347860214, 'foulegold', 'Дмитрий', 'Голденко', 'ru', 0, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `vk_auth`
--

CREATE TABLE `vk_auth` (
  `username` varchar(50) NOT NULL,
  `token` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `vk_auth`
--

INSERT INTO `vk_auth` (`username`, `token`) VALUES
('79136976169', '3015f9ba95ee42899219c441bdea2b1dab92a37183c4ca2f31d5ce93530fd51bf1e9b17ca0e1f7aa98a23'),
('79231656362', '15af6c2a138780d7e878d8efe9e1618a06fcf3bfd80871d158c8d22235091adb7b53b20facc8a98ec7fbc');

-- --------------------------------------------------------

--
-- Структура таблицы `vk_errors`
--

CREATE TABLE `vk_errors` (
  `error_code` int(11) NOT NULL,
  `error_msg_en` varchar(255) NOT NULL DEFAULT '',
  `error_msg_ru` varchar(255) NOT NULL DEFAULT '',
  `description_en` varchar(255) NOT NULL DEFAULT '',
  `description_ru` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `vk_errors`
--

INSERT INTO `vk_errors` (`error_code`, `error_msg_en`, `error_msg_ru`, `description_en`, `description_ru`) VALUES
(113, '', 'Неверный идентификатор пользователя.', '', 'Убедитесь, что Вы используете верный идентификатор.');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`page_id`),
  ADD KEY `social_network` (`social_network_id`);

--
-- Индексы таблицы `social_networks`
--
ALTER TABLE `social_networks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `name` (`name`);

--
-- Индексы таблицы `tg_admins`
--
ALTER TABLE `tg_admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`),
  ADD KEY `username` (`username`);

--
-- Индексы таблицы `tg_answers`
--
ALTER TABLE `tg_answers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`),
  ADD KEY `command_in_id` (`command_in_id`) USING BTREE;

--
-- Индексы таблицы `tg_chat_history`
--
ALTER TABLE `tg_chat_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `tg_commands`
--
ALTER TABLE `tg_commands`
  ADD PRIMARY KEY (`id`),
  ADD KEY `command_in` (`command_in`),
  ADD KEY `type_of_answer` (`type_of_answer`);

--
-- Индексы таблицы `tg_currentlevel`
--
ALTER TABLE `tg_currentlevel`
  ADD PRIMARY KEY (`user_id`) USING BTREE,
  ADD KEY `current_command_id` (`current_command_id`);

--
-- Индексы таблицы `tg_subscription`
--
ALTER TABLE `tg_subscription`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`),
  ADD KEY `user_id` (`user_id`) USING BTREE,
  ADD KEY `tg_subscription_ibfk_2` (`page_id`);

--
-- Индексы таблицы `tg_types_of_answers`
--
ALTER TABLE `tg_types_of_answers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `tg_users`
--
ALTER TABLE `tg_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `active` (`active`),
  ADD KEY `username` (`username`);

--
-- Индексы таблицы `vk_auth`
--
ALTER TABLE `vk_auth`
  ADD PRIMARY KEY (`username`);

--
-- Индексы таблицы `vk_errors`
--
ALTER TABLE `vk_errors`
  ADD PRIMARY KEY (`error_code`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `social_networks`
--
ALTER TABLE `social_networks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `tg_answers`
--
ALTER TABLE `tg_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `tg_chat_history`
--
ALTER TABLE `tg_chat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `tg_commands`
--
ALTER TABLE `tg_commands`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `tg_subscription`
--
ALTER TABLE `tg_subscription`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `tg_types_of_answers`
--
ALTER TABLE `tg_types_of_answers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `pages`
--
ALTER TABLE `pages`
  ADD CONSTRAINT `pages_ibfk_1` FOREIGN KEY (`social_network_id`) REFERENCES `social_networks` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tg_answers`
--
ALTER TABLE `tg_answers`
  ADD CONSTRAINT `tg_answers_ibfk_1` FOREIGN KEY (`command_in_id`) REFERENCES `tg_commands` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tg_chat_history`
--
ALTER TABLE `tg_chat_history`
  ADD CONSTRAINT `tg_chat_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_users` (`id`);

--
-- Ограничения внешнего ключа таблицы `tg_commands`
--
ALTER TABLE `tg_commands`
  ADD CONSTRAINT `tg_commands_ibfk_1` FOREIGN KEY (`type_of_answer`) REFERENCES `tg_types_of_answers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tg_currentlevel`
--
ALTER TABLE `tg_currentlevel`
  ADD CONSTRAINT `tg_currentlevel_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `tg_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tg_currentlevel_ibfk_4` FOREIGN KEY (`current_command_id`) REFERENCES `tg_commands` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `tg_subscription`
--
ALTER TABLE `tg_subscription`
  ADD CONSTRAINT `tg_subscription_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tg_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tg_subscription_ibfk_2` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
