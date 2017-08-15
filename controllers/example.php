<?php

class ExampleController extends \Framework\Controllers\BaseController {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	public function autocompleteAction() {
		
		$table = PREFIX . '_pricelist';

		$SEARCH = snippets()->request->get('search', false);
		
		$qb = db()->getBuilder();
		$qb->from($table)->select([
			'data' => 'nomencl_code',
			'value' => 'title_name'
		]);

		if (!empty($SEARCH)) {

			$SEARCH = preg_replace('#[^a-zA-ZА-Яа-я\d\s]#ui', '', $SEARCH);
			$words = preg_split('#[\s]+#ui', mb_strtolower($SEARCH));
			$words = array_slice($words, 0, 5);

			$qb->where_group(function($qb) use ($words){
				foreach ($words as $word) {
					if (is_numeric($word)) {
						$qb->where_or('nomencl_code', intval($word));
					}
				}	
			}, 'OR');
			$qb->where('searchable', 'LIKE', db()->avedb()->Escape(implode('%', $words)) . '%');
			$qb->limit(6);

			$q = db()->fetch($qb);

			if($q->count()){

				snippets()->response->json(['suggestions' => $q->assoc()]);
			}

			snippets()->response->json(['suggestions' => []]);
		}
		
	}
	
	public function callmodelAction() {
		
		debug($this->users()->getPrimary(1));
	}
}