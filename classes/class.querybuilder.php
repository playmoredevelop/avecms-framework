<?php

namespace Framework;

define('NLT', PHP_EOL.chr(9));
define('LT', chr(9));

class QueryBuilderMethods {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	protected $select = false;
	protected $from = false;
	protected $joins = false;
	protected $conditions = false;
	protected $groupby = false;
	protected $orderby = false;
	protected $having = false;
	protected $limit = 50;
	protected $offset = 0;
	protected $raw = [
		'select' => false,
		'where' => false
	];
	protected $autogroup = 0;
	protected $grouplogic = [
		0 => 'OR'
	];
	
	public function getQueryString() {
		
		$select = function(){
			if(is_array($this->select)){
				$select = [];
				foreach($this->select as $alias => $field){
					if(is_string($alias)){
						if(mb_strstr($alias, ':')){
							// COUNT(1) as `total`
							$alias = trim($alias, ':');
						} else {
							$field = $this->qf($field);
						}
						$select[$alias] = sprintf('%s AS `%s`', $field, $alias);
					} else {
						$select[] = $this->qf($field);
					}
				}
				return 'SELECT '.implode(', ' . NLT, $select);
			}
			return 'SELECT *';
		};
		
		$from = function(){
			$from = [];
			foreach($this->from as $alias => $table){
				if(is_string($alias)){
					$from[] = "{$this->qf($table)} AS `{$alias}`";
				} else {
					$from[] = $this->qf($table);
				}
			}
			return PHP_EOL.'FROM '.implode(', ' . NLT, $from);
		};
		
		$joins = function(){
			if(empty($this->joins)) {
				return '';
			}
			$joins = [];
			foreach($this->joins as $join){
				if(is_array($join['table'])){
					$alias = key($join['table']);
					$table = current($join['table']);
					$join['table'] = "{$this->qf($table)} AS `{$alias}`";
				} else {
					$join['table'] = $this->qf($join['table']);
				}
				if( ! mb_strstr($join['condition'], ':')){
					$replace = [];
					foreach((array)preg_split('#[\=\>\<]{1,2}#i', $join['condition']) as $field){
						$replace[trim($field)] = $this->qf($field);
					}
					$join['condition'] = 'ON '.str_replace('ON ', '', str_replace(array_keys($replace), array_values($replace), $join['condition']));
				} else {
					$join['condition'] = trim($join['condition'], ':');
				}
				$join['type'] = implode(' ', [$join['type'], 'JOIN']);
				$joins[] = implode(' ', $join);
			}
			return NLT.implode(NLT, $joins);
		};
		
		$where = function(){
			$where = [];
			foreach($this->conditions as $group => $items){
				$before = ''; $after = ''; $logic = '';
				// если есть группировка, то оборачиваем в скобки
				if(array_key_exists($group, $this->grouplogic)) {
					$before = '('; $after = ')'; $logic = $this->grouplogic[$group].' ';
				}
				// перебираем все условия в группе
				foreach($items as $item){
					if(!empty($where[$group])){
						// a = 123
						$where[$group][] = implode(' ', [NLT.$item['join'], $this->qf($item['field']), $item['operand'], $item['value']]);
					} else {
						$where[$group][] = implode(' ', [$this->qf($item['field']), $item['operand'], $item['value']]);
					}
				}
				// если условие не первое, то прицепляем логический оператор группы
				if(count($where) > 1){
					$where[$group] = $logic.$before.implode(' ', $where[$group]).$after;
				} else {
					$where[$group] = $before.implode(' ', $where[$group]).$after;
				}
			}
			
			if(!empty($this->raw['where'])){
				$where[] = NLT.$this->raw['where'];
			}
			if(!empty($where)){
				return PHP_EOL.'WHERE '.implode(' ', $where);
			}
			return '';
		};
		
		$groupby = function(){
			if(!empty($this->groupby)){
				$groupby = [];
				foreach($this->groupby as $field){
					$groupby[] = $this->qf($field);
				}
				return PHP_EOL.'GROUP BY '.implode(', ', $groupby);
			}
			return '';
		};
		
		$having = function(){
			
		};
		
		$orderby = function(){
			if(!empty($this->orderby)){
				$orderby = [];
				foreach($this->orderby as $field => $sort){
					$orderby[] = $this->qf($field).' '.mb_strtoupper($sort);
				}
				return PHP_EOL.'ORDER BY '.implode(', ', $orderby);
			}
			return '';
		};
		
		$limit_offset = function(){
			return 'LIMIT '.$this->limit.' OFFSET '.$this->offset;
		};
		
		return implode(' ', [
			$select(),
			$from(),
			$joins(),
			$where(),
			$groupby(),
			$having(),
			$orderby(),
			$limit_offset(),
		]);
	}
	
	protected function addWhereType($args, $type = 'AND') {
		
		switch (count($args)) {
			case 1 : 
				if(is_string($args[0])){
					$this->raw['where'] = $type.' '.$args[0];
				}
				return $this;
			case 2:	
				if(is_array($args[1])){
					return $this->addWhere($args[0], $args[1], false, $type); 
				} else {
					return $this->addWhere($args[0], $args[1], '=', $type); 
				}
			case 3:
				return $this->addWhere($args[0], $args[2], $args[1], $type);
		}
		return $this;
	}
	
	protected function addWhere($field, $value = false, $operand = false, $join = 'AND') {
		
		switch (gettype($value)) {
			case 'array':
					$value = sprintf("('%s')", implode("', '", $value));
					$operand = trim(str_replace('IN', '', $operand).' IN');
				break;
			case 'NULL':
					$value = 'NULL';
					$operand = trim('IS '.$operand);
				break;
			default:
					$value = sprintf("'%s'", $value);
					if(false === $operand){
						$operand = '=';
					}
				break;
		}
		
		$this->conditions[$this->autogroup][] = [
			'join' => $join,
			'field' => $field,
			'value' => $value,
			'operand' => $operand,
		];
		return $this;
	}
	
	protected function qf(string $name) {
		
		return '`'.str_replace('.', '`.`', trim($name)).'`';
	}
}

class QueryBuilder extends QueryBuilderMethods {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	public function select(array $fields) {
		
		$this->select = $fields;
		return $this;
	}
	
	public function from($tables) {
		
		is_string($tables) AND $tables = explode(',', $tables);
		
		$this->from = $tables;
		return $this;
	}
	
	public function join($table, $condition, $type = 'INNER') {
		
		$this->joins[] = [
			'type' => $type, // INNER JOIN
			'table' => $table, // table t1
			'condition' => $condition, // ON t1.f = t2.f
		];
		return $this;
	}
	
	public function where() {
		
		$args = func_get_args();
		return $this->addWhereType($args, 'AND');
	}
	
	public function where_or() {
		
		$args = func_get_args();
		return $this->addWhereType($args, 'OR');
	}
	
	public function where_group(callable $callback, $logic = 'AND') {
		
		$autogroup = snippets()->string->random(3);
		$this->autogroup = $autogroup;
		$this->grouplogic[$autogroup] = $logic;
		call_user_func($callback, $this);
		$this->autogroup = 0;
		
		return $this;
	}
	
	public function group_by($field) {
		
		if(is_array($field)){
			$this->groupby = $field;
		} else {
			$this->groupby[] = $field;
		}
		return $this;
	}
	
	public function order_by($field, $sort = 'ASC') {
		
		if(is_array($field)){
			$this->orderby = $field;
		} else {
			$this->orderby[$field] = $sort;
		}
		return $this;
	}
	
	public function having() {
		
	}
	
	public function limit($count, $page = 1) {
		
		($page < 1) AND $page = 1;
		$this->limit = $count;
		$this->offset = abs($page - 1) * $count;
		return $this;
	}
	
	
}