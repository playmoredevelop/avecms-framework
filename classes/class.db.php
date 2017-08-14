<?php

namespace Framework;

class DB {

	public $full_logger = false;
	private $_query_log = [];
	private $_last_query = '';
	/** @var AVE_DB_Result */
	private $_results = false;

	/**
	 * Работа с БД напрямую
	 * @global type $AVE_DB
	 * @return AVE_DB
	 */
	public function avedb() {
		global $AVE_DB;
		return $AVE_DB;
	}
	
	public function getBuilder() {
		
		if(!class_exists('\Framework\QueryBuilder')){
			require FPATH.'/class.querybuilder.php';
		}
		
		return new QueryBuilder();
	}

	/**
	 * @return AVE_DB_Result
	 */
	public function getAveResults() {

		return $this->_results;
	}

	public function getMySqlResults() {

		return $this->_results->getResult();
	}

	/**
	 * Фетчит запрос и возвращает объект для дальнейшей обработки результата
	 * @param type $query
	 * @return \extendsDB
	 */
	public function fetch($query) {
		
		if($query instanceof QueryBuilder){
			$query = $query->getQueryString();
		}

		if($this->full_logger){
			$this->_query_log[] = $query;
		}
		$this->_last_query = $query;
		$this->_results = $this->avedb()->Query($query);
		return $this;
	}

	/**
	 * Вернет ассоциативный массив из результата запроса, если он прежде был выполнен удачно.
	 * @param type $primary
	 * @return array
	 */
	public function assoc($primary = false) {

		$rows = [];
		if($this->_results){
			while($row = $this->_results->FetchAssocArray()){
				if($primary){
					$rows[$row[$primary]] = $row;
				} else {
					$rows[] = $row;
				}
			}
		}

		if(!empty($rows)) {
			return $rows;
		}
		return false;
	}

	/**
	 * Кол-во строк в текущей выборке
	 * @return int
	 */
	public function count() {

		if($this->_results){
			return $this->_results->NumRows();
		}

		return 0;
	}

	public function column($field, $primary = false) {

		$rows = [];
		if($this->_results->NumRows()){
			while($row = $this->_results->FetchAssocArray()){
				if($primary){
					$rows[$row[$primary]] = $row[$field];
				} else {
					$rows[] = $row[$field];
				}
			}
		}

		if(!empty($rows)) {
			return $rows;
		}
		return false;
	}

	public function row() {

		$row = [];
		if($this->_results){
			$row = $this->_results->FetchAssocArray();
		}

		return $row;
	}

	public function get_queries_list() {

		return $this->_query_log;
	}

	public function get_last_query() {

		return $this->_last_query;
	}

	/**
	 * Метод обновления данных в таблице
	 * @param string $table
	 * @param array $data
	 * @param string $where
	 * @param int $limit
	 * @return affected num rows
	 */
	public function update($table, $data, $where, $limit = 1) {

		$set = [];
		foreach($data as $field => $value){
			if(is_array($value) OR is_object($value)){
				$set[] = sprintf('`%s` = \'%s\'', $field, json_encode($value));
				continue;
			}
			if(is_string($value)){
				$set[] = sprintf('`%s` = \'%s\'', $field, $this->avedb()->Escape($value));
				continue;
			}
			if(is_null($value)){
				$set[] = sprintf('`%s` = NULL', $field); continue;
			}
			if(empty($value)){
				$set[] = "`{$field}` = ''"; continue;
			}
			$set[] = sprintf('`%s` = %s', $field, $value);
		}

		$q = str_replace(['{table}', '{set}', '{where}', '{limit}'], [
			$table,
			implode(', ', $set),
			$where,
			(int)$limit
		], 'UPDATE {table} SET {set} WHERE {where} LIMIT {limit}');

		if($this->full_logger) {
			$this->_query_log[] = $q;
		}
		$this->_last_query = $q;

		$q = $this->avedb()->Query($q);

		return $this->avedb()->getAffectedRows();
	}

	/**
	 * Метод добавления данных в таблицу
	 * @param string $table
	 * @param array $data
	 * @return type
	 */
	public function insert($table, $data) {

		$fields = array_keys($data);
		$values = array_values($data);

		foreach($values as $i => $value){
			if(is_array($value) OR is_object($value)){
				$values[$i] = "'".json_encode($value)."'"; continue;
			}
			if(is_string($value)){
				$values[$i] = "'".$this->avedb()->Escape($value)."'"; continue;
			}
			if(is_null($value)){
				$values[$i] = 'NULL'; continue;
			}
			if(empty($value)){
				$values[$i] = "''"; continue;
			}
		}

		$q = str_replace(['{table}', '{fields}', '{values}'], [
			$table,
			'`'.implode('`, `', $fields).'`',
			implode(', ', $values)
		], 'INSERT INTO `{table}` ({fields}) VALUES ({values})');

		if($this->full_logger) {
			$this->_query_log[] = $q;
		}
		$this->_last_query = $q;

		if($this->avedb()->Query($q)){
			return $this->avedb()->InsertId();
		} else {
			//debug($q);
			return false;
		}
		
	}

	/**
	 * Метод посчета кол-ва строк в таблице с условием
	 * @param type $table
	 * @param type $where
	 * @return type
	 */
	public function total($table, $where = '') {

		$q = str_replace(['{table}', '{where}'], [
			$table,
			(!empty($where)) ? ' WHERE '.$where : ''
		], 'SELECT COUNT(1) as total FROM {table}{where}');

		$q = $this->fetch($q)->row();

		return intval($q['total']);
	}

	/**
	 *
	 * @param type $table
	 * @param type $limit
	 * @param type $offset
	 * @return extendsDB
	 */
	public function get($table, $limit = false, $offset = 0) {

		$q = str_replace(['{table}', '{limit}', '{offset}'], [
			$table,
			($limit) ? ' LIMIT '.(int)$limit : '',
			($limit) ? ' OFFSET '.(int)$offset : '',
		], 'SELECT * FROM {table}{limit}{offset}');

		return $this->fetch($q);
	}

	public function transaction_start() {

		return $this->avedb()->Query('START TRANSACTION');
	}

	public function transaction_commit() {

		return $this->avedb()->Query('COMMIT');
	}
}