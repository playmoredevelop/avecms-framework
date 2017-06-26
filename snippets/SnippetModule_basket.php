<?php

namespace Framework;

/**
 * Снеппет работает только с установленным модулем корзины
 */
require_once BOOTSTRAP_ROOT.'/modules/basket/class.basket.php';

class SnippetModule_basket extends \ModulBasket {
	/** @author Playmore 2017 (playmoredevelop@gmail.com) */

	private $_carts = [];
	private $t_basket = PREFIX . '_module_basket';
	private $t_documents = PREFIX.'_documents';
	private $t_doc_fields = PREFIX . '_document_fields';
	private $t_basket_delivery = PREFIX.'_module_basket_delivery';
	private $t_basket_payment = PREFIX.'_module_basket_payment';
	private $t_orders = PREFIX.'_module_basket_history';
	private $t_settings = PREFIX.'_module_basket_settings';
	private $basketId = false;

	public function __construct() {

		$this->setBasketId(session_id());
		$this->getBasketProducts();
	}

	public function setBasketId($basketId) {

		$this->basketId = $basketId;
	}

	public function doOrder($data = []) {

		$getdata = function ($name, $default = false) use ($data) {
			if(array_key_exists($name, $data)){
				return $data[$name];
			}
			return $default;
		};

		$data = [
			'first_name' => $getdata('name', 'Анонимный пользователь'),
			'last_name' => $getdata('lastname', ''),
			'city' => $getdata('city', 0),
			'cityname' => $getdata('cityname', 'Не определен'),
			'phone' => $getdata('phone', ''),
			'email' => $getdata('email', ''),
			'address' => $getdata('address', ''),
			'description' => $getdata('wishes', ''),
			'delivery_id' => $getdata('delivery', 1),
			'payment_id' => $getdata('payment', 1),
		];

		$basket = $this->getBasket();
		$settings = $this->get_basket_settings();
		$order_id = $this->get_basket_settings('last_order_id') + 1;
		$oStatus = $this->get_basket_status();
		$status = $this->get_status($oStatus, $settings->basket_dstatus);

		if(!empty($basket['products'])){

			$products = base64_encode(serialize($this->getBasketProducts()));

			$delivery_method = $this->getDeliveryMethodById($data['delivery_id']);
			$paymethod = $this->getPayMethodById($data['payment_id']);

			$insert = [
				'order_id' => $order_id,
				'order_payment_id' => $data['payment_id'],
				'order_delivery_id' => $data['delivery_id'],
				'order_name' => $data['first_name'] . ' ' . $data['last_name'],
				'order_email' => $data['email'],
				'order_phone' => $data['phone'],
				'order_address' => $data['address'],
				'order_city' => $data['cityname'],
				'order_description' => $data['description'],
				'order_published' => TIME,
				'order_total' =>  $basket['total_order'],
				'order_customer_id' => 0,
				'order_user_id' => 0,
				'order_text' => $products,
				'order_status' => $settings->basket_dstatus,
				'order_discont' => 0,
				'order_coupon' => 0,
			];

			if($orderPID = db()->insert($this->t_orders, $insert)){
				
				$this->setLastOrderId($orderPID);

				$this->clearBasket();
				
				//ob_start();
				//include BOOTSTRAP_PATH.'/widgets/mail.order.php';
				//$view = ob_get_clean();
				
				ob_start();
				include BOOTSTRAP_PATH.'/widgets/success.order.php';
				$view = ob_get_clean();

				return snippets()->response->json([
					'status' => 'ok',
					'html' => $view
				]);
			}
		}

		return snippets()->response->json([
			'status' => 'fail',
			'message' => 'Произошла ошибка. Попробуйте позже.'
		]);

	}

	public function setLastOrderId($id) {

		return db()->update($this->t_settings, ['last_order_id' => (int)$id], 'id = 1');
	}

	public function clearBasket() {

		$q = str_replace(['{t_basket}', '{basketId}'], [
			$this->t_basket,
			$this->basketId,
		], 'DELETE FROM {t_basket} WHERE basket_session_id = \'{basketId}\'');

		return db()->fetch($q);

	}

	public function getBasketProducts() {

		if(!empty($this->_carts[$this->basketId])){

			return $this->_carts[$this->basketId];
		}

		$q = str_replace(['{t_basket}', '{t_documents}', '{t_doc_fields}', '{basketId}'], [
			$this->t_basket,
			$this->t_documents,
			$this->t_doc_fields,
			$this->basketId
		], 'SELECT
				b.basket_product_id as id,
				b.basket_product_quantity AS quantity,
				b.basket_product_amount AS amount,
				d.Id as doc_id,
				d.document_alias,
				d.document_title,
				df.field_value as price,
				df2.field_value as sku
			FROM
				{t_basket} b
			INNER JOIN
				{t_documents} d ON b.basket_product_id = d.Id
			LEFT JOIN
				{t_doc_fields} df ON df.Id = b.basket_product_price_id
			LEFT JOIN
				{t_doc_fields} df2 ON df2.Id = b.basket_product_article_id
			WHERE
				b.basket_session_id = \'{basketId}\'
			ORDER BY
				b.id ASC');

		$q = db()->fetch($q);

		if($q->count()){

			$this->_carts[$this->basketId] = $q->assoc('id');

			return $this->_carts[$this->basketId];
		}

		return false;
	}

	public function getBasketCount($product_doc_id = false) {

		if($product_doc_id > 0){

			if($this->incart($product_doc_id)){

				return $this->_carts[$this->basketId][$product_doc_id]['quantity'];
			}

		} else {

			$count = 0;
			foreach($this->_carts[$this->basketId] as $item){
				$count = $count + $item['quantity'];
			}

			return $count;
		}

		return 0;
	}

	public function getBasketSumm() {

		$summ = 0;
		foreach($this->_carts[$this->basketId] as $item){
			$summ += ($item['quantity'] * $item['price']);
		}

		return $summ;
	}

	public function incart($product_doc_id) {

		return array_key_exists($product_doc_id, $this->_carts[$this->basketId]);
	}

	public function updateProductsQuantity($products) {

		if(empty($products)) return false;

		foreach ($products as $product_id => $product_quantity)	{

				if (! is_numeric($product_quantity)) {
					continue;
				}

				// если количество больше установленного в настройках, пропускаем
				if ($product_quantity > $this->get_basket_settings('basket_max_quantity')) {
					continue;
				}

				$q = str_replace(['{t_basket}', '{pid}', '{qty}', '{basketId}'], [
					$this->t_basket,
					(int)$product_id,
					(int)$product_quantity,
					$this->basketId
				], 'UPDATE
						{t_basket}
					SET
						basket_product_amount = basket_product_amount / basket_product_quantity * {qty},
						basket_product_quantity = {qty}
					WHERE
						basket_product_id = {pid} AND basket_session_id   = \'{basketId}\'');

				db()->fetch($q);
		}

		return db()->avedb()->getAffectedRows();
	}

	public function setProductsQuanitty($products) {

		foreach($products as $pid => $qty){

			if($qty < 1) {
				$this->basketProductDelete($pid);
				unset($products[$pid]);
			}
		}

		return $this->updateProductsQuantity($products);
	}

	/**
	 * Виды доставки
	 * @return boolean
	 */
	public function getDeliveryMethods() {

		$q = db()->fetch('SELECT * FROM '.$this->t_basket_delivery.' WHERE delivery_activ = 1 ORDER BY delivery_position ASC');

		if($q->count()){
			return $q->assoc('id');
		}

		return false;
	}

	public function getDeliveryMethodById($id) {

		$q = db()->fetch('SELECT delivery_title FROM '.$this->t_basket_delivery.' WHERE id = '.(int)$id.' AND delivery_activ = 1');

		if($q->count()){
			return $q->row()['delivery_title'];
		}

		return false;
	}
	
	/**
	 * Виды оплаты
	 * @return boolean
	 */
	public function getPayMethods() {

		$q = db()->fetch('SELECT * FROM '.$this->t_basket_payment.' WHERE payment_activ = 1 ORDER BY payment_position ASC');

		if($q->count()){
			return $q->assoc('id');
		}

		return 'Неизвестный';
	}

	public function getPayMethodById($id) {

		$q = db()->fetch('SELECT payment_title FROM '.$this->t_basket_payment.' WHERE id = '.(int)$id.' AND payment_activ = 1');

		if($q->count()){
			return $q->row()['payment_title'];
		}

		return 'Неизвестный';
	}
}

/* END CLASS: SnippetModules */