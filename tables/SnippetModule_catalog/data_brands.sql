--
-- Структура таблицы `data_brands`
--

CREATE TABLE `data_brands` (
  `PID` int(10) UNSIGNED NOT NULL,
  `brand_id` int(11) UNSIGNED DEFAULT NULL,
  `brand_name` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `data_brands`
--
ALTER TABLE `data_brands`
  ADD PRIMARY KEY (`PID`),
  ADD KEY `brand_name` (`brand_name`),
  ADD KEY `brand_id` (`brand_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `data_brands`
--
ALTER TABLE `data_brands`
  MODIFY `PID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;