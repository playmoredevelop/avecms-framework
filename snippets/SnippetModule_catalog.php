<?php

namespace Framework;

class SnippetModule_catalog {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

	private $t_items = PREFIX.'_module_catalog_items';
	private $t_documents = PREFIX.'_documents';
	private $t_relations = 'rel_categories_products';
	private $t_products = 'products_data';
	private $t_brands = 'data_brands';
	private $t_countries = 'data_countries';
	private $t_manufacturers = 'data_manufacturers';
	private $field_image_id = 14;

	private static $container = [];

	public function fetch_assoc_or_false($q, $primary = false) {

		$q = db()->fetch($q);
		if($q->count()){
			if($primary){
				return $q->assoc($primary);
			}
			return $q->assoc();
		}
		return false;
	}

	public function fetch_row_or_false($q, $primary = false) {

		$q = db()->fetch($q);
		if($q->count()){
			if($primary){
				return $q->row($primary);
			}
			return $q->row();
		}
		return false;
	}

	public function getSubcategories($root_id) {

		$hash = snippets()->string->hash(['getSubcategories', $root_id]);

		if( ! array_key_exists($hash, self::$container)){
			self::$container[$hash] = $this->_getSubcategories($root_id);
		}

		return self::$container[$hash];
	}

	public function getCategoryChilds($root_id = 0, $orderbyname = false) {

		$fields = [
			'td.Id',
			'td.rubric_id',
			'td.document_parent',
			'td.document_alias',
			//'td.document_title',
			'td.document_count_view',
			'ti.id as cat_id',
			'ti.name',
			'ti.parent_id',
		];

		$extra = '';
		($orderbyname) AND $extra = ' ORDER BY name ASC';

		$q = str_replace(['{t_items}', '{t_documents}', '{fields}', '{parent_id}'], [
			$this->t_items,
			$this->t_documents,
			implode(', ', $fields),
			(int)$root_id,
		], 'SELECT {fields} FROM `{t_items}` ti
			INNER JOIN `{t_documents}` td ON ti.document_id = td.Id
			WHERE parent_id = {parent_id} 
				AND (td.document_status = 1 AND td.document_deleted = 0)'.$extra);

		return db()->fetch($q);
	}

	public function getCategoryByDocId($doc_id) {

		return singleton('getCategoryByDocId'.$doc_id, function() use ($doc_id){

			return $this->_getCategoryByDocId($doc_id);
		});
	}

	public function getAllCatsWithLinks() {

		$q = str_replace(['{t_relations}', '{t_items}'], [
			$this->t_relations,
			$this->t_items,
		], 'SELECT `r`.`product_code`, `t`.`id` as `cid`, `t`.`document_id`
				FROM {t_relations} r
				INNER JOIN {t_items} t ON r.category_id = t.id');

		return db()->fetch($q)->assoc('product_code');
	}

	public function getCategoryBrands($root_ids) {

		is_array($root_ids) AND $root_ids = implode(',', $root_ids);

		$q = str_replace(['{t_relations}', '{t_products}', '{t_brands}', '{root_ids}'], [
			$this->t_relations,
			$this->t_products,
			$this->t_brands,
			$root_ids,
		], 'SELECT b.brand_id, b.brand_name
				FROM {t_relations} r
				INNER JOIN {t_products} p ON r.product_code = p.code
				INNER JOIN {t_brands} b ON p.brand_id = b.brand_id
				WHERE r.category_id IN ({root_ids})
					AND b.status = 1
					GROUP BY b.brand_id
                    ORDER BY b.brand_name');

		return $this->fetch_assoc_or_false($q);
	}

	public function getBrandName($brand_id) {

		$q = str_replace(['{t_brands}', '{b_id}'], [
			$this->t_brands,
			(int)$brand_id,
		], 'SELECT brand_name FROM {t_brands} WHERE brand_id = {b_id}');

		$q = db()->fetch($q);

		if($q->count()){
			return $q->row()['brand_name'];
		}
		return '';
	}

	public function getCountryName($country_id) {

		$q = str_replace(['{t_countries}', '{c_id}'], [
			$this->t_countries,
			(int)$country_id,
		], 'SELECT country_name FROM {t_countries} WHERE country_id = {c_id}');

		$q = db()->fetch($q);

		if($q->count()){
			return $q->row()['country_name'];
		}
		return '';
	}

	public function getManufacturerName($manufacturer_id) {

		$q = str_replace(['{t_manufacturers}', '{m_id}'], [
			$this->t_manufacturers,
			(int)$manufacturer_id,
		], 'SELECT name FROM {t_manufacturers} WHERE id = {m_id}');

		$q = db()->fetch($q);

		if($q->count()){
			return $q->row()['name'];
		}
		return '';
	}

	public function getCategoryProducts($root_ids, $limit = 25, $offset = 0, $conditions = '', $orderby = '') {

		is_array($root_ids) AND $root_ids = implode(', ', $root_ids);

		$q = str_replace(['{t_relations}', '{t_products}', '{root_ids}', '{t_documents}', '{limit}', '{offset}', '{f_image_id}', '{conditions}', '{orderby}'], [
			$this->t_relations,
			$this->t_products,
			$root_ids,
			$this->t_documents,
			(int)$limit,
			(int)$offset,
			$this->field_image_id,
			$conditions,
			(!empty($orderby)) ? 'ORDER BY '.$orderby : '',
		], 'SELECT DISTINCT(p.product_doc_id) as doc_id, 
					p.*,
					d.document_alias,
					d.document_title,
					d.rubric_id,
					df.rubric_field_id as f_image_id,
					df.field_value as f_image_value
				FROM {t_relations} r
				INNER JOIN {t_products} p ON r.product_code = p.code
				INNER JOIN {t_documents} d ON p.product_doc_id = d.Id
				LEFT JOIN tfp_document_fields df ON d.Id = df.document_id AND df.rubric_field_id = {f_image_id}
				WHERE r.category_id IN ({root_ids})
					{conditions}
					AND p.status = 1
					{orderby}
				LIMIT {limit} OFFSET {offset}');

		return $this->fetch_assoc_or_false($q, 'doc_id');
	}

	public function getCategoryProductsCount($root_ids, $conditions = '') {

		is_array($root_ids) AND $root_ids = implode(', ', $root_ids);

		$q = str_replace(['{t_relations}', '{t_products}', '{root_ids}', '{t_documents}', '{conditions}'], [
			$this->t_relations,
			$this->t_products,
			$root_ids,
			$this->t_documents,
			$conditions
		], 'SELECT COUNT(DISTINCT(p.product_doc_id)) as total
				FROM {t_relations} r
				INNER JOIN {t_products} p ON r.product_code = p.code
				INNER JOIN {t_documents} d ON p.product_doc_id = d.Id
				WHERE r.category_id IN ({root_ids})
					{conditions}
					AND p.status = 1');

		return (int)db()->fetch($q)->row()['total'];
	}

	/**
	 * Возвращает топ товаров глобально или по указанным списком категориям
	 * @param type $limit
	 * @param string $cats
	 * @return array
	 */
	public function getProductsTop($limit = 1, $cats = false) {

		$q = str_replace(['{t_relations}', '{t_products}','{t_documents}', '{f_image_id}', '{limit}', '{cats}' ], [
			$this->t_relations,
			$this->t_products,
			$this->t_documents,
			$this->field_image_id,
			(int)$limit,
			!empty($cats) ? $cats = 'r.category_id IN ('.$cats.') AND ' : '',
		], 'SELECT DISTINCT(p.product_doc_id) as doc_id,
					p.*,
					d.document_alias,
					d.document_title,
					d.rubric_id,
					df.rubric_field_id as f_image_id,
					df.field_value as f_image_value
				FROM {t_relations} r
				INNER JOIN {t_products} p ON r.product_code = p.code
				INNER JOIN {t_documents} d ON p.product_doc_id = d.Id
				LEFT JOIN tfp_document_fields df ON d.Id = df.document_id AND df.rubric_field_id = {f_image_id}
				WHERE {cats} p.status = 1 AND d.document_status = 1
				ORDER BY d.document_count_view DESC
				LIMIT {limit}');

		return $this->fetch_assoc_or_false($q, 'doc_id');
	}

	/**
	 * Возвращает активные товары по id документов
	 */
	public function getProducts($doc_ids, $limit = 1) {

		if(empty($doc_ids)){
			return false;
		}

		$q = str_replace(['{t_products}','{t_documents}', '{f_image_id}', '{doc_ids}', '{limit}' ], [
			$this->t_products,
			$this->t_documents,
			$this->field_image_id,
			is_array($doc_ids) ? implode(', ', array_map('intval', $doc_ids)) : (int)$doc_ids,
			(int)$limit
		], 'SELECT DISTINCT(p.product_doc_id) as doc_id,
					p.*,
					d.document_alias,
					d.document_title,
					d.rubric_id,
					df.rubric_field_id as f_image_id,
					df.field_value as f_image_value
				FROM {t_products} p
				INNER JOIN {t_documents} d ON p.product_doc_id = d.Id
				LEFT JOIN tfp_document_fields df ON d.Id = df.document_id AND df.rubric_field_id = {f_image_id}
				WHERE p.product_doc_id IN ({doc_ids}) AND d.document_status = 1
				ORDER BY d.document_count_view DESC
				LIMIT {limit}');

		return $this->fetch_assoc_or_false($q, 'doc_id');
	}

	/**
	 * Вернет минимальную и максимальную цену всех продуктов в категории
	 * @param type $root_ids
	 * @return array [min, max]
	 */
	public function getCatalogPrices($root_ids) {

		is_array($root_ids) AND $root_ids = implode(',', $root_ids);

		$q = str_replace(['{t_relations}', '{t_products}', '{root_ids}'], [
			$this->t_relations,
			$this->t_products,
			$root_ids,
		], 'SELECT MIN(p.price) as min_price, MAX(p.price) as max_price
				FROM {t_relations} r
				INNER JOIN {t_products} p ON r.product_code = p.code
				WHERE r.category_id IN ({root_ids})
					AND p.status = 1');

		return $this->fetch_row_or_false($q);
	}

	public function getCategoryAges($root_ids) {

		is_array($root_ids) AND $root_ids = implode(',', $root_ids);

		$q = str_replace(['{t_relations}', '{t_products}', '{root_ids}'], [
			$this->t_relations,
			$this->t_products,
			$root_ids,
		], 'SELECT MIN(p.age) as min_age, MAX(p.age) as max_age
				FROM {t_relations} r
				INNER JOIN {t_products} p ON r.product_code = p.code
				WHERE r.category_id IN ({root_ids})
					AND p.status = 1');

		return $this->fetch_row_or_false($q);
	}

	private function _getCategoryByDocId($doc_id) {

		$q = str_replace(['{t_items}', '{t_documents}', '{doc_id}'], [
			$this->t_items,
			$this->t_documents,
			(int)$doc_id,
		], 'SELECT ti.* FROM `{t_items}` ti
			INNER JOIN `{t_documents}` td ON ti.document_id = td.Id
			WHERE td.Id = {doc_id}
				AND (td.document_status = 1 AND td.document_deleted = 0)
			LIMIT 1');

		return db()->fetch($q)->row();
	}

	private function _getSubcategories($root_id) {

		$q = str_replace(['{t_items}', '{root_id}'], [
			$this->t_items,
			(int)$root_id,
		], 'SELECT t1.`id`, GROUP_CONCAT(t2.`id`) as subcats
				FROM {t_items} t1
				LEFT JOIN {t_items} t2 ON  t2.`parent_id` = t1.`id`
				WHERE t1.`parent_id` = {root_id}
				GROUP BY t1.id');

		$subcats = db()->fetch($q)->assoc();

		$result = [];
		foreach($subcats as $row){
			if(!empty($row['subcats'])){
				$result = array_merge($result, explode(',', $row['id'].','.$row['subcats']));
			} else {
				$result[] = $row['id'];
			}
		}

		return array_map('intval', $result);
	}
}

/* END CLASS: SnippetCatalog */