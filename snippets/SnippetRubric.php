<?php

class SnippetRubric {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

	private $t_documents = PREFIX.'_documents';
	private $t_documents_fields = PREFIX.'_document_fields';
	private $t_rub_fields = PREFIX.'_rubric_fields';

    public function getRubricDocuments($rubId, $limit = 100, $offset = 0, $where = '', $orderBy = 'document_published DESC') {

		$rubId = (int)$rubId;
		$limit = (int)$limit;
		$offset = (int)$offset;
		$where = !empty($where) ? 'AND '.$where : '';

		$q = "SELECT * FROM {$this->t_documents} WHERE rubric_id = {$rubId} AND document_status = 1 {$where} ORDER BY {$orderBy} LIMIT {$limit} OFFSET {$offset}";
		//exit($q);
		$documents = db()->fetch($q)->assoc('Id');

		if(!empty($documents)){

			$doc_ids = array_keys($documents);
			$doc_ids = implode(',', $doc_ids);
			$aliases = $this->getFieldsAlias($rubId);
			$fields = db()->fetch("SELECT * FROM {$this->t_documents_fields} WHERE document_id IN ({$doc_ids})")->assoc();

			if(!empty($fields) AND !empty($aliases)){

				foreach($fields as $field){
					$did = (int)$field['document_id'];
					$fid = (int)$field['rubric_field_id'];
					$alias = $fid;
					if(array_key_exists($fid, $aliases)){
						if(!empty($aliases[$fid]['rubric_field_alias'])){
							$alias = $aliases[$fid]['rubric_field_alias'];
						}
						switch ($aliases[$fid]['rubric_field_type']) {
							// если поле документ из рурики (чекбокс) то нарезаем значения в массив
							case 'doc_from_rub_check':
								$field['field_value'] = explode('||', trim($field['field_value'], '|'));
								break;
						}
					} 
					$documents[$did]['fields'][$alias] = $field['field_value'];
				}
			}
		}

		//debug($documents);
		return $documents;
	}

	public function getDocumentsListWhere($rub_id, $field_id, $field_val) {

		$q = "SELECT d.Id
				FROM {t_documents} d
				INNER JOIN {t_document_fields} df ON d.Id = df.document_id AND df.rubric_field_id = {fid}
				WHERE d.rubric_id = {rid}
					AND d.document_status = 1
					AND df.field_value = '{fval}'";

		$q = db()->fetch(str_replace(['{t_documents}', '{t_document_fields}', '{fid}', '{rid}', '{fval}'], [
			$this->t_documents,
			$this->t_documents_fields,
			(int)$field_id,
			(int)$rub_id,
			db()->avedb()->Escape($field_val)
		], $q));

		if($q->count()){
			return $q->column('Id');
		}

		return false;
	}

	public function getCountDocuments($rubId) {

		$q = "SELECT COUNT(1) as total FROM {$this->t_documents} WHERE rubric_id = {$rubId} AND document_status = 1";
		return db()->fetch($q)->row()['total'];
	}

	public function getFieldsAlias($rubId) {
		
		static $rub_ids = [];

		$rubId = (int)$rubId;
		
		if( ! array_key_exists($rubId, $rub_ids)){
			
			$q = db()->fetch("SELECT Id, rubric_field_alias, rubric_field_title, rubric_field_type 
											FROM {$this->t_rub_fields}
											WHERE rubric_id = {$rubId}");
			if($q->count() > 0){
				$rub_ids[$rubId] = $q->assoc('Id');
			} else {
				$rub_ids[$rubId] = false;
			}
		}

		return $rub_ids[$rubId];
	}

	
}

/* END CLASS: SnippetRubric */