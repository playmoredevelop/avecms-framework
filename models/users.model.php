<?php

namespace Framework\Models;

class UsersModel extends BaseModel {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	public function __construct() {
		
		$this->table = PREFIX.'_users';
		$this->f_primary = 'Id';
	}
}