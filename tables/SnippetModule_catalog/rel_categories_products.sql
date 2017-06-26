--
-- Структура таблицы `rel_categories_products`
--

CREATE TABLE `rel_categories_products` (
  `PID` int(11) UNSIGNED NOT NULL,
  `product_code` int(11) UNSIGNED NOT NULL,
  `category_id` mediumint(4) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `rel_categories_products`
--
ALTER TABLE `rel_categories_products`
  ADD PRIMARY KEY (`PID`),
  ADD KEY `product_code` (`product_code`),
  ADD KEY `category_id` (`category_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `rel_categories_products`
--
ALTER TABLE `rel_categories_products`
  MODIFY `PID` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;