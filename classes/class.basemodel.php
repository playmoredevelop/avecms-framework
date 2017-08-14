<?php

namespace Framework;

class BaseModel extends DB {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	protected $table = false;
	protected $f_primary = false;
	
	public function getPrimary($ids) {
		
		is_array($ids) AND $ids = implode(', ', array_map('intval', $ids));
		
		$q = sprintf('SELECT * FROM `%s` WHERE `%s` IN (%s)', $this->table, $this->f_primary, $ids);
		
		return $this->fetch($q)->assoc($this->f_primary);
	}
}