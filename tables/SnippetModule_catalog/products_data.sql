--
-- Структура таблицы `products_data`
--

CREATE TABLE `products_data` (
  `PID` int(11) UNSIGNED NOT NULL,
  `product_doc_id` int(11) UNSIGNED NOT NULL,
  `code` int(11) UNSIGNED DEFAULT NULL,
  `sku` int(11) UNSIGNED DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `is_drug` tinyint(1) UNSIGNED DEFAULT NULL,
  `is_b_a_d` tinyint(1) UNSIGNED DEFAULT NULL,
  `is_pregnancy` tinyint(1) UNSIGNED DEFAULT NULL,
  `is_lactation` smallint(5) UNSIGNED DEFAULT NULL,
  `country_id` mediumint(8) UNSIGNED DEFAULT NULL,
  `manufacturer_id` mediumint(8) UNSIGNED DEFAULT NULL,
  `brand_id` mediumint(8) UNSIGNED DEFAULT NULL,
  `line_id` mediumint(8) UNSIGNED DEFAULT NULL,
  `age` tinyint(3) UNSIGNED DEFAULT NULL,
  `price` int(11) UNSIGNED DEFAULT NULL,
  `status` tinyint(1) UNSIGNED NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `products_data`
--
ALTER TABLE `products_data`
  ADD PRIMARY KEY (`PID`),
  ADD KEY `product_doc_id` (`product_doc_id`),
  ADD KEY `brand_id` (`brand_id`),
  ADD KEY `price` (`price`),
  ADD KEY `status` (`status`),
  ADD KEY `code` (`code`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `products_data`
--
ALTER TABLE `products_data`
  MODIFY `PID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;