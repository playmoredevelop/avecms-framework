<?php

class TestController {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */
	
	public function indexAction() {
		
		$q = db()->getBuilder();
		
		$q->select(['f.field1', 'f2.field2', 'alias' => 'f2.field3', ':total' => 'COUNT(f.field1)']);
		$q->from('documents d');
		$q->join('fields f', 'd.Id = f.doc_id');
		$q->join('fields f2', 'd.Id = f2.doc_id');
		
		
		debug($q);
	}
}