<?php

class SnippetDocuments {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

	private $t_documents = PREFIX.'_documents';
	private $t_doc_fields = PREFIX.'_document_fields';
	private $t_rub_fields = PREFIX.'_rubric_fields';
	private $viewed_cookie = 'documents_viewed';
	private $viewed_count = 10;

	private static $container = [];

	public function getDocuments($doc_ids) {

		$hash = snippets()->string->hash(['getDocuments', $doc_ids]);

		if( ! array_key_exists($hash, self::$container)){
			self::$container[$hash] = $this->_getDocuments($doc_ids);
		}

		return self::$container[$hash];
	}

	public function getFields($doc_ids) {

		is_array($doc_ids) AND $doc_ids = array_map('intval', $doc_ids);

		$q = str_replace(['{t_doc_fields}', '{t_rub_fields}', '{doc_ids}'], [
			$this->t_doc_fields,
			$this->t_rub_fields,
			is_array($doc_ids) ? implode(', ', $doc_ids) : (int)$doc_ids
		], 'SELECT df.rubric_field_id,
					df.document_id,
					df.field_value,
					rf.rubric_field_alias
				FROM {t_doc_fields} df
				INNER JOIN {t_rub_fields} rf ON df.rubric_field_id = rf.Id
				WHERE document_id IN ({doc_ids})');

		$q = db()->fetch($q);

		$doc_fields = [];
		if($q->count() > 0){
			foreach($q->getMySqlResults() as $row){
				if(!empty($row['rubric_field_alias'])){
					$doc_fields[$row['document_id']][$row['rubric_field_alias']] = $row['field_value'];
				} else {
					$doc_fields[$row['document_id']][$row['rubric_field_id']] = $row['field_value'];
				}
			}
		}

		return $doc_fields;
	}

	private function _getDocuments($doc_ids, $orderby = 'd.document_published DESC') {

		is_array($doc_ids) AND $doc_ids = array_map('intval', $doc_ids);

		$q = str_replace(['{t_documents}', '{doc_ids}', '{doc_orderby}'], [
			$this->t_documents,
			is_array($doc_ids) ? implode(', ', $doc_ids) : (int)$doc_ids,
			(!empty($orderby)) ? 'ORDER BY '.$orderby : '',
		], 'SELECT * FROM {t_documents} d
				WHERE d.Id IN ({doc_ids}) {doc_orderby}');

		$q = db()->fetch($q);

		if($q->count() > 0){

			$documents = $q->assoc('Id');
			// переписываем только найденые
			$doc_ids = array_keys($documents);

			$fields = $this->getFields($doc_ids);

			foreach($doc_ids as $document_id){
				if(array_key_exists($document_id, $fields)){
					$documents[$document_id]['fields'] = $fields[$document_id];
				}
			}

			if(count($doc_ids) == 1){
				return current($documents);
			} else {
				return $documents;
			}
		}

		return false;
	}

	public function setViewed($doc_id) {

		$cookie_hash = md5($this->viewed_cookie);

		$viewed = $this->getViewed();
		$doc_id = intval($doc_id);
		
		if(is_array($viewed)){

			foreach($viewed as $i => $one){
				if($one == $doc_id){
					unset($viewed[$i]);
				}
			}

			array_unshift($viewed, $doc_id);

			$viewed = array_slice($viewed, 0, $this->viewed_count);

		} else {
			$viewed = [$doc_id];
		}

		setcookie($cookie_hash, json_encode($viewed), TIME + (86400 * 30), '/', '', false, false);
	}

	public function getViewed() {

		$cookie_hash = md5($this->viewed_cookie);

		if(!empty($_COOKIE[$cookie_hash])){

			$viewed = $_COOKIE[$cookie_hash];
			$viewed = json_decode($viewed, true);

			if (is_array($viewed)) {

				$viewed = array_map('intval', $viewed);

				if (!empty($viewed)) {
					return $viewed;
				}
			}
		}
		
		return false;
	}

}

/* END CLASS: SnippetDocuments */