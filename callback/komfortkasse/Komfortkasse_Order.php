<?php

// in KK, an Order is an Array providing the following members:
// number, date, email, customer_number, payment_method, amount, currency_code, exchange_rate, language_code
// delivery_ and billing_: _firstname, _lastname, _company
// products: an Array of item numbers

/** 
 * Komfortkasse
 * Config Class
 * 
 * @version 1.0.0-xtc3
 */
class Komfortkasse_Order {
	
	// return all order numbers that are "open" and relevant for tranfer to kk
	public static function getOpenIDs() {
		
		$ret = array ();
		
		$sql = "select orders_id from " . TABLE_ORDERS . " where orders_status in (" . Komfortkasse_Config::getConfig(Komfortkasse_Config::status_open) . ") and ( ";
		$paycodes = preg_split ('/,/', Komfortkasse_Config::getConfig(Komfortkasse_Config::payment_methods));	
		for($i=0; $i<count($paycodes);$i++) {
			$sql .= " payment_method like '" . $paycodes[$i] . "' ";
			if ($i < count($paycodes)-1) {
				$sql .= " or ";
			}
		}	
		$sql .= " )";
		$orders_q=xtc_db_query($sql);
		
		while ($orders_a=xtc_db_fetch_array($orders_q)) {
			$ret [] = $orders_a ['orders_id'];
		}

		return $ret;
	}
	public static function getOrder($number) {
		require_once DIR_WS_CLASSES . 'order.php';
		
		$order = new order ( $number );
		if (empty ( $number ) || empty ( $order )) {
			return null;
		}
		
		$total_q=xtc_db_query("SELECT value FROM ".TABLE_ORDERS_TOTAL." where orders_id=" . $number . " and class='ot_total'");
		$total_a=xtc_db_fetch_array($total_q);
		$total = $total_a['value'];
		
		$lang_q=xtc_db_query("SELECT l.code FROM ".TABLE_ORDERS." o join " . TABLE_LANGUAGES . " l on l.directory=o.language where o.orders_id=" . $number );
		$lang_a=xtc_db_fetch_array($lang_q);
		$lang = $lang_a['code'];
		
		$ret = array ();
		$ret ['number'] = $number;
		$ret ['date'] = date("d.m.Y", strtotime($order->info ['date_purchased']));
		$ret ['email'] = $order->customer['email_address'];
		$ret ['customer_number'] = $order->customer ['csID'];
		$ret ['payment_method'] = $order->info ['payment_method'];
		$ret ['amount'] = $total;
		$ret ['currency_code'] = $order->info ['currency'];
		$ret ['exchange_rate'] = $order->info ['currency_value'];
		$ret ['language_code'] = $lang;
		$ret ['delivery_firstname'] = $order->delivery ['firstname'];
		$ret ['delivery_lastname'] = $order->delivery ['lastname'];
		$ret ['delivery_company'] = $order->delivery ['company'];
		$ret ['billing_firstname'] = $order->billing ['firstname'];
		$ret ['billing_lastname'] = $order->billing ['lastname'];
		$ret ['billing_company'] = $order->billing ['company'];

		$order_products = $order->products;
		foreach ( $order_products as $product ) {
			if ($product ['model']) {
				$ret['products'][] = $product ['model'] ;
			} else {
				$ret ['products'] [] = $product ['name'];
			}
		}
		
		return $ret;
	}
	
	public static function updateOrder($order, $status, $callbackid) {
		xtc_db_query("update ".TABLE_ORDERS." set orders_status = '".xtc_db_input($status)."', last_modified = now() where orders_id = '".xtc_db_input($order['number'])."'");
		xtc_db_query("insert into ".TABLE_ORDERS_STATUS_HISTORY." (orders_id, orders_status_id, date_added, customer_notified, comments) values ('".xtc_db_input($order['number'])."', '".xtc_db_input($status)."', now(), '0', 'Komfortkasse ID ".$callbackid."')");
	}
}

?>