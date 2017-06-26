<?php

namespace Framework;

class SnippetTables {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

    public function resetAutoIncrement($table) {

		$q = sprintf('SELECT (MAX(`Id`)+1) as autonext FROM %s', $table);

		$q = db()->fetch($q);

		if($q AND $row = $q->row()){

			$q = sprintf('ALTER TABLE %s AUTO_INCREMENT = %d', $table, $row['autonext']);

			db()->fetch($q);

			return true;
		}

		return false;
	}
}

/* END CLASS: SnippetTables */