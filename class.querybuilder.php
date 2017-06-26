<?php

namespace Framework;

define('NLT', PHP_EOL.chr(9));

class QueryBuilder {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	private $_select = [];
	private $_from = '';
	private $_joins = [];
	private $_where = [];
	private $_limit = false;
	private $_offset = false;
	
	public function select($fields) {
		$this->_select = array_merge($this->_select, $fields);
		return $this;
	}
	
	public function from($table) {
		$this->_from = $table;
		return $this;
	}
	
	public function join($table, $on, $type = 'INNER') {
		$this->_joins[$table] = $type.' JOIN '.$table.' ON '.$on;
		return $this;
	}
	
	public function limit($limit, $offset) {
		$this->_limit = (int)$limit;
		$this->_offset = (int)$offset;
	}
	
	public function assoc($primary = false) {
		
		debug($this->getSql());
	}
	
	public function getSql() {
		
		$select = function(){
			$select = [];
			foreach($this->_select as $as => $field){
				if(is_string($as)){
					if(mb_strstr($as, ':')){
						$select[$as] = $field.' as '.$as;
					} else {
						$select[$as] = $field.' as '.$as;
					}
				} else {
					$select[$as] = $field;
				}
			}
			return 'SELECT '.implode(', ' . NLT, $select);
		};
		
		$from = function(){
			if(!empty($this->_joins)){
				return 'FROM ' . $this->_from . NLT . implode(NLT, $this->_joins);
			}
			return 'FROM ' . $this->_from;
		};
		
		$condition = function(){
			if(!empty($this->_where)){
				
			}
		};
		
		return implode(PHP_EOL, [
				$select(),
				$from(),
				$condition(),
			]);
	}
}