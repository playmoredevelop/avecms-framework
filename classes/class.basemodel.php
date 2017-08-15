<?php

namespace Framework\Models;

class BaseModel {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	protected $table = false;
	protected $f_primary = false;
	
	protected function fetch($q) {
		return db()->fetch($q);
	}
	
	protected function getBuilder() {
		return db()->getBuilder();
	}
	
	/**
	 * Выборка по primary полю
	 * - можно передать массив id
	 * @param int|array $ids
	 * @return assoc array
	 */
	public function getPrimary($ids) {
		
		is_array($ids) AND $ids = implode(', ', array_map('intval', $ids));
		
		$q = sprintf('SELECT * FROM `%s` WHERE `%s` IN (%s)', $this->table, $this->f_primary, $ids);
		
		return $this->fetch($q)->assoc($this->f_primary);
	}
	
	/**
	 * Выборка по значениям указанного поля
	 * @param string $field
	 * @param string|int|array $values
	 * @return assoc array
	 */
	public function getByField($field, $values) {
		
		is_array($values) AND $values = implode("', '", $values);
		
		$q = sprintf("SELECT * FROM `%s` WHERE `%s` IN ('%s')", $this->table, $field, $values);
		
		return $this->fetch($q)->assoc($this->f_primary);
	}
	
	/**
	 * Вернет билдер запросов для выборки списка элементов
	 * @param int $page
	 * @param int $count
	 * @return QueryBuilder
	 */
	public function getListBuilder($page = 1, $count = 50) {
		
		$qb = $this->getBuilder();
		$qb->from($this->table);
		$qb->limit($count, $page);
		
		return $qb;
	}
}