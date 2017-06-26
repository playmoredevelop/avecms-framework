<?php

class ExampleController {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	public function autocompleteAction() {
		
		$table = PREFIX . '_pricelist';

		$SEARCH = snippets()->request->get('search', false);

		if (!empty($SEARCH)) {

			$SEARCH = preg_replace('#[^a-zA-ZА-Яа-я\d\s]#ui', '', $SEARCH);
			$words = preg_split('#[\s]+#ui', mb_strtolower($SEARCH));

			$words = array_slice($words, 0, 5);

			$where = [];
			foreach ($words as $word) {
				if (is_numeric($word)) {
					$where['WORDS'][] = $word;
					$where['OR'][] = sprintf("nomencl_code = %d", intval($word));
				} else {
					$where['WORDS'][] = $word;
				}
			}
			!empty($where['OR']) AND $where['OR'] = implode(' OR ', $where['OR']);
			!empty($where['WORDS']) AND $where['WORDS'] = sprintf("searchable LIKE '%s'", db()->avedb()->Escape(implode('%', $where['WORDS'])) . '%');

			$q = str_replace(['{table}', '{where}'], [
				$table,
				'(' . implode(') OR (', $where) . ')'
			], 'SELECT nomencl_code as data, title_name as value FROM {table} WHERE {where} LIMIT 6');

			$q = db()->fetch($q);

			if($q->count()){

				snippets()->response->json(['suggestions' => $q->assoc()]);
			}

			snippets()->response->json(['suggestions' => []]);
		}
		
	}
}