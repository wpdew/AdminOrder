<?php
session_start();
date_default_timezone_set('Europe/Kiev');

// Завантажуємо налаштування з БД
require_once __DIR__ . '/order-settings-loader.php';

// Завантажуємо заблоковані IP з БД
require_once __DIR__ . '/blocked-ips-loader.php';

use App\Models\OrderRecord;

// Змінні $tgtoken, $tgchatid, $crm_lp_token і т.д. вже завантажені з order-settings-loader.php
// Змінна $spamip завантажена з blocked-ips-loader.php

$name = isset($_POST['name']) ? $_POST['name'] : "";
$phone = isset($_POST['phone']) ? $_POST['phone'] : "";
$phone = preg_replace('/[^0-9]/', '', $phone);

$fbp = isset($_POST['fbp']) ? $_POST['fbp'] : "";
$comment = isset($_POST['comment']) ? $_POST['comment'] : "";
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : "5";
$product_price = isset($_POST['product_price']) ? $_POST['product_price'] : "699";
$product_title = isset($_POST['product']) ? $_POST['product'] : "";
$count = isset($_POST['count']) ? $_POST['count'] : "1";
$type_form = isset($_POST['type']) ? $_POST['type'] : "";
$payment = isset($_POST['payment']) ? $_POST['payment'] : "";
$delivery = isset($_POST['delivery']) ? $_POST['delivery'] : "";
$delivery_adress = isset($_POST['delivery_adress']) ? $_POST['delivery_adress'] : "";
$additional_1 = isset($_POST['additional_1']) ? $_POST['additional_1'] : "";
$additional_2 = isset($_POST['additional_2']) ? $_POST['additional_2'] : "";
$additional_3 = isset($_POST['additional_3']) ? $_POST['additional_3'] : "";
$additional_4 = isset($_POST['additional_4']) ? $_POST['additional_4'] : "";
$utm_source = isset($_SESSION['utms']['utm_source']) ? $_SESSION['utms']['utm_source'] : '';
$utm_medium = isset($_SESSION['utms']['utm_medium']) ? $_SESSION['utms']['utm_medium'] : '';
$utm_term = isset($_SESSION['utms']['utm_term']) ? $_SESSION['utms']['utm_term'] : '';
$utm_content = isset($_SESSION['utms']['utm_content']) ? $_SESSION['utms']['utm_content'] : '';
$utm_campaign = isset($_SESSION['utms']['utm_campaign']) ? $_SESSION['utms']['utm_campaign'] : '';
$utm = 'utm_source: '.$utm_source.'; utm_medium: '.$utm_medium.'; utm_term: '.$utm_term.'; utm_content: '.$utm_content.'; utm_campaign: '.$utm_campaign;
/*
[{&quot;id&quot;:&quot;31&quot;,&quot;price&quot;:399,&quot;name&quot;:&quot;Українські хіти&quot;},{&quot;id&quot;:&quot;34&quot;,&quot;price&quot;:399,&quot;name&quot;:&quot;Хіти 80х 90х&quot;}]
*/

/*
раом купують апсели
*/
$upsells = isset($_POST['upsells']) ? $_POST['upsells'] : '';

// Обробка апселів для Telegram
$upsells_array = [];
$total_sum = $product_price * $count;
$product_names = $product_title . ' ' . $comment;
$upsells_text = '';

if (!empty($upsells)) {
	$upsells_decoded = stripslashes($upsells);
	$upsells_array = json_decode($upsells_decoded, true);
	
	if (is_array($upsells_array) && count($upsells_array) > 0) {
		$upsell_lines = [];
		foreach ($upsells_array as $upsell) {
			if (isset($upsell['name']) && isset($upsell['price'])) {
				$upsell_count = isset($upsell['count']) ? (int)$upsell['count'] : 1;
				$upsell_lines[] = '  ✓ ' . $upsell['name'] . ' - ' . $upsell['price'] . ' грн' . ($upsell_count > 1 ? ' x' . $upsell_count : '');
				$total_sum += $upsell['price'] * $upsell_count;
			}
		}
		if (!empty($upsell_lines)) {
			$upsells_text = implode("%0A", $upsell_lines);
		}
	}
}


$ip = isset($_SERVER['HTTP_CLIENT_IP']) 
	? $_SERVER['HTTP_CLIENT_IP'] 
	: (isset($_SERVER['HTTP_X_FORWARDED_FOR']) 
	  ? $_SERVER['HTTP_X_FORWARDED_FOR'] 
	  : $_SERVER['REMOTE_ADDR']);

$umob = isset($_POST['umob']) ? $_POST['umob'] : "0";
if ($umob == "1") {
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $is_mobile = preg_match('/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|Tablet|Phone|nokia|samsung|htc|sony|huawei|xiaomi|miui|lg|motorola/i', $agent);
    $usemobile = $is_mobile ? '1' : '0';
} else {
    $usemobile = '1';
}

$dataarray = array(
	'name' => $name,
	'phone' => $phone,
	'product_id' => $product_id,
	'product_title' => $product_title,
	'product_price' => $product_price,
	'count' => $count,
	'comment' => $comment,
	'payment' => $payment,
	'delivery' => $delivery,
	'delivery_adress' => $delivery_adress,
	'additional_1' => $additional_1,
	'additional_2' => $additional_2,
	'additional_3' => $additional_3,
	'additional_4' => $additional_4,
	'type' => $type_form,
	'email' => $email,
	'website' => $_SERVER['SERVER_NAME']. dirname($_SERVER['SCRIPT_NAME']),
);

$arrTg = array(
	'💁‍♂️ Ім`я: ' => $name,
	'📱 Телефон: ' => $phone,
	'📍 ID продукта: ' => $product_id,
	'📦 Товар: ' => $product_title . ' ' . $count . ' шт',
	'💸 Ціна: ' => $product_price.' грн',
	'📍 Кількість: ' => $count.' шт.',
	'💸 Базова сума: ' => ($product_price * $count).' грн',
);
// Добавляем допродажі, если есть
if (!empty($upsells_text)) {
	$arrTg['🎁 Допродажі:'] = "%0A" . $upsells_text;
}
// Добавляем общую сумму
$arrTg['💸 ЗАГАЛЬНА СУМА: '] = $total_sum.' грн';
$arrTg['📅 Дата: '] = date("Y-m-d H:i:s");
$arrTg['📌 IP-замовлення: '] = $ip;
$arrTg['🌐 Сайт: '] = $_SERVER['SERVER_NAME']. dirname($_SERVER['SCRIPT_NAME']);
$arrTg['🖥️ Тип замовлення: '] = $type_form;
//if $umob = 1 Добавляем информацию о мобильном устройстве
if ($usemobile === '1') {
	$arrTg['📱 Пристрій: '] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'Невідомий пристрій';
}if ($umob === 1 || $usemobile === '0') {
	$arrTg['📱 Пристрій: '] = 'Не мобільний пристрій чи спам';
}
// Добавляем UTM-метки, если не пустые
if (!empty($utm_source))   $arrTg['🔖 UTM Source: ']   = $utm_source;
if (!empty($utm_medium))   $arrTg['🔖 UTM Medium: ']   = $utm_medium;
if (!empty($utm_term))     $arrTg['🔖 UTM Term: ']     = $utm_term;
if (!empty($utm_content))  $arrTg['🔖 UTM Content: ']  = $utm_content;
if (!empty($utm_campaign)) $arrTg['🔖 UTM Campaign: '] = $utm_campaign;


class Order{

		//send sendToGoogleSheets function
		public function sendToGoogleSheets($googleURL, $arrData) {
			$params = '';
			$arrData = array_merge(
				array('Timestamp' => date("d.m.Y H:i")),
				$arrData
			);

			foreach ($arrData as $key => $value) {
				$params .= urlencode($key) . '=' . urlencode($value) . '&';
			}
			$sendToSheets = fopen("{$googleURL}?{$params}", "r");
		}

		//send sendToTelegram function
		public function sendToTelegram($tgtoken, $tgchatid, $arrTg) {
			$txt = '';

			foreach ($arrTg as $key => $value) {
				$key = htmlspecialchars((string)$key, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
				$value = htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
				$txt .= "<b>{$key}</b> {$value}\n";
			}

			$ch = curl_init("https://api.telegram.org/bot{$tgtoken}/sendMessage");
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => [
					'chat_id' => $tgchatid,
					'parse_mode' => 'HTML',
					'text' => $txt,
				],
				CURLOPT_TIMEOUT => 15,
			]);

			$response = curl_exec($ch);
			$error = curl_error($ch);
			curl_close($ch);

			return true;
		}

		//send sentdToLpCrm function
		public function sentdToLpCrm($crm_lp_token, $crm_lp_adress, $crm_lp_office, $dataarray){

			$products_list = array(
				0 => array(
					'product_id' => $dataarray['product_id'],
					'price'      => $dataarray['product_price'],
					'count'      => $dataarray['count'],
				),
			);

			// Если есть апселлы
			if (!empty($_POST['upsells'])) {
				$upsells = json_decode(stripslashes($_POST['upsells']), true);
				if (is_array($upsells)) {
					$i = 1; 
					foreach ($upsells as $upsell) {
						$products_list[$i] = array(
							'product_id' => $upsell['id'],
							'price'      => $upsell['price'],
							'count'      => 1, 
						);
						$i++;
					}
				}
			}

			$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME']. dirname($_SERVER['SCRIPT_NAME']);
			$products = urlencode(serialize($products_list));
			$sender = urlencode(serialize($_SERVER));
			$data = array (
				'key'             => $crm_lp_token,
				'order_id'        => number_format(round(microtime(true) * 10), 0, '.', ''),
				'country'         => 'UA',
				'office'          => $crm_lp_office,
				'products'        => $products,
				'bayer_name'      => $dataarray['name'],
				'phone'           => $dataarray['phone'],
				'comment'         => $dataarray['product_title'].' '.$dataarray['comment'],
				'payment'         => $dataarray['payment'],
				'delivery'        => $dataarray['delivery'],
				'delivery_adress' => $dataarray['delivery_adress'],
				'sender'          => $sender,                        
				'utm_source'      => $_SESSION['utms']['utm_source'],
				'utm_medium'      => $_SESSION['utms']['utm_medium'],
				'utm_term'        => $_SESSION['utms']['utm_term'],
				'utm_content'     => $_SESSION['utms']['utm_content'],
				'utm_campaign'    => $_SESSION['utms']['utm_campaign'],
				'additional_1'    => $dataarray['additional_1'],
				'additional_2'    => $dataarray['additional_2'],
				'additional_3'    => $dataarray['additional_3'],
				'additional_4'    => $dataarray['additional_4'] 
			);
		
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $crm_lp_adress . '/api/addNewOrder.html');
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
			$out = curl_exec($curl);
			curl_close($curl);

		}

		//send sendEmail function
		public function sendEmail($email, $arrTg){
			$subject = "Заказ товара ";
		
			$message;
			$message .= "<b>Заказ товара</b><br/><hr/><br/>";
			foreach ($arrTg as $key => $value) {
				$message .= "<b>" . $key . "</b> " . $value . "<br/>";
			};
			$message .= "<hr/><br/>";
			$message .= "<b>Дата: </b> " . date("Y-m-d H:i:s") . "<br/>";
			$message .= "Разработка конфигуратора  <a href='https://t.me/WpDews'>@WpDews</a><br/>";
			
			
			$sendMail = mail($email, $subject, $message, "Content-type:text/html; charset=UTF-8\r\n");

		}

		//send sentdToSalesDrive function
		public function sentdToSalesDrive($crm_salesdrive_token, $crm_salesdrive_sources, $dataarray){
			$crm_salesdrive_token = $crm_salesdrive_token;
			$crm_salesdrive_sources = $crm_salesdrive_sources;
			$dataarray = $dataarray;
	
			$products = [];
				
			$products[0]["id"] = $dataarray['product_id']; // id товару
			$products[0]["name"] = $dataarray['product_title']; // назва товару
			$products[0]["costPerItem"] = $dataarray['product_price']; // ціна
			$products[0]["amount"] = $dataarray['count']; // кількість
			$products[0]["description"] = ""; // опис товарної позиції в заявці
			$products[0]["discount"] = ""; // знижка, задається в % або в абсолютній величині
			$products[0]["sku"] = ""; // артикул (SKU) товару
										
			$_salesdrive_url = $crm_salesdrive_sources;
			$_salesdrive_values = [
				"form" => $crm_salesdrive_token,
				"getResultData" => "1", // Отримувати дані створеної заявки (0 - не отримувати, 1 - отримувати)
				"products"=>$products, //Товари/Послуги
				"comment"=>$dataarray['comment'], // Коментар
				"fName"=>$dataarray['name'], // Ім'я
				"lName"=>"", // Прізвище
				"mName"=>"", // По батькові
				"phone"=>$dataarray['phone'], // Телефон
				"email"=>"", // E-mail
				"con_comment"=>$dataarray['comment'], // Коментар
				"shipping_method"=>"", // Спосіб доставки
				"payment_method"=>"", // Спосіб оплати
				"shipping_address"=>"", // Адреса доставки
				"novaposhta"=> [
					"ServiceType" => "", // можливі значення: DoorsDoors, DoorsWarehouse, WarehouseWarehouse, WarehouseDoors
					"payer" => "", // можливі значення: "sender", "recipient"
					"area" => "", // область російською або українською мовою, або Ref області в системі Нової пошти
					"region" => "", // район російською або українською мовою (використовується тільки якщо cityNameFormat=settlement)
					"city" => "", // назва міста російською або українською мовою, або Ref міста в системі Нової пошти
					"cityNameFormat" => "", // можливі значення: full (за замовчуванням), short, settlement (населений пункт із нової адресної системи: ref або назва)
					"WarehouseNumber" => "", // відділення Нової Пошти в одному з форматів: номер, опис, Ref
					"Street" => "", // назва і тип вулиці, або Ref вулиці в системі Нової пошти
					"BuildingNumber" => "", // номер будинку
					"Flat" => "", // номер квартири
				],
				"ukrposhta"=> [
					"ServiceType" => "", // можливі значення: DoorsDoors, DoorsWarehouse, WarehouseWarehouse, WarehouseDoors
					"payer" => "", // можливі значення: "sender", "recipient"
					"type" => "", // можливі значення: express, standard
					"city" => "", // місто російською або українською мовою, або CITY_ID Укрпошти
					"WarehouseNumber" => "", // номер відділення Укрпошти
					"Street" => "", // STREET_ID Укрпошти
					"BuildingNumber" => "", // номер будинку
					"Flat" => "" // номер квартири
				],
				"sajt"=>"", // Сайт
				"organizationId"=>"", // id організації
				"shipping_costs"=>"", // Витрати на доставку
				"stockId"=>"", // id складу
				"utmSourceFull"=> $utm_source,
				"utmSource"=> $utm_source,
				"utmMedium"=> $utm_medium,
				"utmCampaign"=> $utm_campaign,
				"utmContent"=> $utm_content,
				"utmTerm"=> $utm_term,
				"utmPage"=>isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:"",
			];
			
			$_salesdrive_ch = curl_init();
			curl_setopt($_salesdrive_ch, CURLOPT_URL, $_salesdrive_url);
			curl_setopt($_salesdrive_ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($_salesdrive_ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($_salesdrive_ch, CURLOPT_SAFE_UPLOAD, true);
			curl_setopt($_salesdrive_ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($_salesdrive_ch, CURLOPT_POST, 1);
			curl_setopt($_salesdrive_ch, CURLOPT_POSTFIELDS, json_encode($_salesdrive_values));
			curl_setopt($_salesdrive_ch, CURLOPT_TIMEOUT, 15);
			
			$_salesdrive_res = curl_exec($_salesdrive_ch); 
	
		}

		//send sentdToKeyCrm function
		public function sentdToKeyCrm($crm_key_token, $crm_key_sources, $dataarray){
			$crm_key_token = $crm_key_token;
			$crm_key_sources = $crm_key_sources;
			$dataarray = $dataarray;
	
				$data = [
					"source_id" => $crm_key_sources, // в какой источник в KeyCRM добавлять заказы
					"buyer" => [
						"full_name"=> $dataarray['name'], // ФИО покупателя
						"email"=> $dataarray['email'], // email покупателя
						"phone"=> $dataarray['phone'] // номер телефона покупателя
					],
					"shipping" => [
						"shipping_address_city"=> $_POST['address_city'], // город покупателя
						"shipping_receive_point"=> $_POST['address_street'], // улица, номер дома или отделение Новой Почты
						"shipping_address_country"=> $_POST['address_country'], // страна
						"shipping_address_region"=> $_POST['address_region'], // область/штат/регион
						"shipping_address_zip"=> $_POST['address_zip'] // индекс
					],
					"marketing" => [
						"utm_source" => $_SESSION['utms']['utm_term'],
						"utm_medium" => $_SESSION['utms']['utm_medium'],
						"utm_campaign" => $_SESSION['utms']['utm_campaign'],
						"utm_term" => $_SESSION['utms']['utm_term'],
						"utm_content" => $_SESSION['utms']['utm_content'],
					],
					"products"=> [
						[
							"price"=> $dataarray['product_price'], // цена продажи
							"quantity"=> $dataarray['count'], // количество проданного товара
							"name"=> $dataarray['product_title'], // название товара
							"picture"=> $_POST['product_url'], // картинка товара
							"comment"=> $dataarray['comment'],
							"properties"=>[
							[
								"name"=> $dataarray['properties_name'],
								"value"=> $dataarray['properties_value']
							]
							]
						]
					]
				];
		
				//  "упаковываем данные"
				$data_string = json_encode($data);
				
				// Ваш уникальный API ключ KeyCRM
				$token = $crm_key_token;
				
				// отправляем на сервер
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, "https://openapi.keycrm.app/v1/order");
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						"Content-type: application/json",
						"Accept: application/json",
						"Cache-Control: no-cache",
						"Pragma: no-cache",
						'Authorization:  Bearer ' . $token)
				);
				$result = curl_exec($ch);
				curl_close($ch);
	
		}


		//send sentdToKeyCrm function
		public function sentdToKeyCrmLead($crm_key_token, $crm_key_sources, $crm_key_voronka, $dataarray){
			$crm_key_token = $crm_key_token;
			$crm_key_sources = $crm_key_sources;
			$dataarray = $dataarray;

			$data = [
				"title" => "Нове замовлення",
				"source_id" => $crm_key_sources, // в какой источник в KeyCRM добавлять заказы
				"pipeline_id" => $crm_key_voronka, // воронка
				"contact" => [
					"full_name"=> $dataarray['name'], // ФИО покупателя
					"email"=> $dataarray['email'], // email покупателя
					"phone"=> $dataarray['phone'] // номер телефона покупателя
				],
				"utm_source" => $_SESSION['utms']['utm_term'],
				"utm_medium" => $_SESSION['utms']['utm_medium'],
				"utm_campaign" => $_SESSION['utms']['utm_campaign'],
				"utm_term" => $_SESSION['utms']['utm_term'],
				"utm_content" => $_SESSION['utms']['utm_content'],
				
				"products"=> [
					[
						"price"=> $dataarray['product_price'], // цена продажи
						"quantity"=> $dataarray['count'], // количество проданного товара
						"name"=> $dataarray['product_title'], // название товара
						"picture"=> $_POST['product_url'], // картинка товара
					]
				]
			];
	
			//  "упаковываем данные"
			$data_string = json_encode($data);
			
			// Ваш уникальный API ключ KeyCRM
			$token = $crm_key_token;
			
			// отправляем на сервер
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://openapi.keycrm.app/v1/pipelines/cards");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					"Content-type: application/json",
					"Accept: application/json",
					"Cache-Control: no-cache",
					"Pragma: no-cache",
					'Authorization:  Bearer ' . $token)
			);
			$result = curl_exec($ch);
			curl_close($ch);

		}

		public function sentdEbashCrm($crm_ebash_token, $crm_ebash_adress, $crm_ebash_ofise, $dataarray){

			$products_list = array(
				0 => array(
					'product_id' => $dataarray['product_id'],
					'price'      => $dataarray['product_price'],
					'count'      => $dataarray['count'],
				),
			);
			$_SERVER['SERVER_NAME'] = $_SERVER['SERVER_NAME'];
			$products = urlencode(serialize($products_list));
			$sender = urlencode(serialize($_SERVER));
			$order_id = number_format(round(microtime(true)*10),0,'.','') . rand(10000, 99999);

			$data = array (
				'key'             => $crm_ebash_token,
				'order_id'        => $order_id,
				'country'         => 'UA',
				'office'          => $crm_ebash_ofise,
				'products'        => $products,
				'bayer_name'      => $dataarray['name'],
				'phone'           => $dataarray['phone'],
				'email'           => $dataarray['email'],
				'comment'         => $dataarray['comment'],
				'delivery'        => '1',
				'delivery_adress' => 'Київ, вул. Хрещатик 1',
				'payment'         => '1',
				'site_url'        => $dataarray['website'],
				// UTM мітки для аналітики
				'utm_source'      => $dataarray['utm_source'],
				'utm_medium'      => $dataarray['utm_medium'],
				'utm_campaign'    => $dataarray['utm_campaign'],
				'utm_term'        => $dataarray['utm_term'],
				'utm_content'     => $dataarray['utm_content'],
				// Додаткові поля (опціонально)
				'additional_1'    => $dataarray['additional_1'],
				'additional_2'    => $dataarray['additional_2'],
				'additional_3'    => $dataarray['additional_3'],
				'additional_4'    => $dataarray['additional_4'],
			);
	
				$curl = curl_init();
				curl_setopt_array($curl, array(
				CURLOPT_URL => $crm_ebash_adress. '/api/addNewOrder.html',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => $data
			));
	
			$response = curl_exec($curl);
			curl_close($curl);
	
		}

		public function sentdMagnetstore($token_magnetstore, $tenant_magnetstore, $dataarray)
		{
			$ip_address = $_SERVER['REMOTE_ADDR'];

			$data = [
				"office" => 1,
				"country_id" => 250,
				"products" => [
					[
						"id"     => (int) $dataarray['product_id'],
						"amount" => (int) $dataarray['count'],
						"price"  => (float) $dataarray['product_price']
					]
				],
				"fio" => $dataarray['name'] ?? '',
				"phone" => $dataarray['phone'] ?? '',
				"comment" => $dataarray['comment'] ?? '',
				"payment" => 1,
				"additional_field_1" => "",
				"additional_field_2" => "",
				"additional_field_3" => "",
				"additional_field_4" => "",
				"utm_source"    => $_SESSION['utms']['utm_source'] ?? '',
				"utm_medium"    => $_SESSION['utms']['utm_medium'] ?? '',
				"utm_campaign"  => $_SESSION['utms']['utm_campaign'] ?? '',
				"utm_term"      => $_SESSION['utms']['utm_term'] ?? '',
				"utm_content"   => $_SESSION['utms']['utm_content'] ?? '',
				"order_website" => (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://{$_SERVER['HTTP_HOST']}",
				"user_ip" => $ip_address,
			];

			$apiUrl = "https://{$tenant_magnetstore}.go.profi-crm.com/open-api/order-store?token={$token_magnetstore}";

			
			$curl = curl_init($apiUrl);
			curl_setopt_array($curl, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($data, JSON_UNESCAPED_UNICODE),
				CURLOPT_HTTPHEADER => [
					'Content-Type: application/json',
					'Accept: application/json',
				]
			]);

			$response = curl_exec($curl);
			/*
				if (curl_errno($curl)) {
					echo '❌ CURL Error: ' . curl_error($curl);
				} else {
					echo '✅ Response: <pre>' . htmlspecialchars($response) . '</pre>';
				}
			*/
			curl_close($curl);
		}

		public function sendToMagnetstore(string $token, string $tenant, array $data): array
		{
			$ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

			$payload = [
				"office" => 1,
				"country_id" => 250,
				"products" => [[
					"id" => (int) $data['product_id'],
					"amount" => (int) $data['count'],
					"price" => (float) $data['product_price']
				]],
				"fio" => $data['name'] ?? '',
				"phone" => $data['phone'] ?? '',
				"comment" => $data['comment'] ?? '',
				"payment" => 1,
				"additional_field_1" => $data['additional_1'] ?? '',
				"additional_field_2" => $data['additional_2'] ?? '',
				"additional_field_3" => $data['additional_3'] ?? '',
				"additional_field_4" => $data['additional_4'] ?? '',
				"utm_source" => $_SESSION['utms']['utm_source'] ?? '',
				"utm_medium" => $_SESSION['utms']['utm_medium'] ?? '',
				"utm_campaign" => $_SESSION['utms']['utm_campaign'] ?? '',
				"utm_term" => $_SESSION['utms']['utm_term'] ?? '',
				"utm_content" => $_SESSION['utms']['utm_content'] ?? '',
				"order_website" => (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://{$_SERVER['HTTP_HOST']}",
				"user_ip" => $ip_address,
			];

			$url = "https://{$tenant}.go.profi-crm.com/open-api/order-store?token={$token}";

			$ch = curl_init($url);
			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
				CURLOPT_HTTPHEADER => [
					'Content-Type: application/json',
					'Accept: application/json',
				]
			]);

			$response = curl_exec($ch);
			$error = curl_error($ch);
			curl_close($ch);

			if ($response === false || !empty($error)) {
				return [
					'status' => 'error',
					'message' => 'Magnetstore request failed: ' . $error
				];
			}

			$result = json_decode($response);
			if (!isset($response)) {
				return [
					'status' => 'error',
					'message' => htmlspecialchars($response) ?? 'Unknown error from Magnetstore API'
				];
			}

			return [
				'status' => 'success',
				'message' => 'Order sent successfully to Magnetstore'
			];
		}

		function getCaptcha($SectretKey){
			$SiteKey = "6LfoAYkjAAAAAJGXXB8wSco-P8RSFZNN8cT7xHfY";
			$Response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$SiteKey}&response={$SectretKey}");
			$Return = json_decode($Response);
			return $Return;
		}


}
$order = new Order;

if($type_form == 'offer'){

	//add recaptcha
	//$Return = $order->getCaptcha($_POST['g-recaptcha-response']);
	//if($Return->success == true && $Return->score > 0.5){
 
	if(in_array($ip, $spamip) || $usemobile === '0'){
		$spam = 'spam';
		if($tgchatforspamid != ''){
			$response = $order->sendToTelegram($tgtoken, $tgchatforspamid, $arrTg);
		}  
		sleep(2);
	}else{

		if($googleURL != '') {
			$order->sendToGoogleSheets($googleURL, $dataarray);
		}

		if($tgchatid != ''){
			$order->sendToTelegram($tgtoken, $tgchatid, $arrTg); 
		}  
		if($email != ''){
			$order->sendEmail($email, $arrTg);
		}
		if($crm_lp_token != ''){
			$order->sentdToLpCrm($crm_lp_token, $crm_lp_adress, $crm_lp_office, $dataarray);
		}
		if($crm_salesdrive_token != ''){
			$order->sentdToSalesDrive($crm_salesdrive_token, $crm_salesdrive_sources, $dataarray);
		}
		if($crm_key_token != ''){
			if($crm_key_voronka != ''){
				$order->sentdToKeyCrmLead($crm_key_token, $crm_key_sources, $crm_key_voronka, $dataarray);
			}else{
				$order->sentdToKeyCrm($crm_key_token, $crm_key_sources, $dataarray);
			}
		}
		if($crm_ebash_token != ''){
			$order->sentdEbashCrm($crm_ebash_token, $crm_ebash_adress, $crm_ebash_ofise, $dataarray);
		}
		if($api_token_keep_crm != ''){
			$order->sendToKeepinCRM($api_token_keep_crm, $dataarray);
		}
		if($token_magnetstore != ''){
			$order->sentdMagnetstore($token_magnetstore, $tenant_magnetstore, $dataarray);
		}
		
		$spam = 'nospam';
	}

	// Зберігаємо замовлення в локальну БД, якщо це дозволено в інтеграціях
	if (!isset($use_internal_orders_db) || $use_internal_orders_db === true) {
		try {
			$orderRecord = new OrderRecord();
			$orderRecord->create([
				'customer_name' => $name,
				'phone' => $phone,
				'product_id' => $product_id,
				'product_title' => $product_title,
				'product_price' => $product_price,
				'quantity' => $count,
				'total_sum' => $total_sum,
				'comment' => $comment,
				'payment' => $payment,
				'delivery' => $delivery,
				'delivery_address' => $delivery_adress,
				'additional_1' => $additional_1,
				'additional_2' => $additional_2,
				'additional_3' => $additional_3,
				'additional_4' => $additional_4,
				'type_form' => $type_form,
				'status' => $spam === 'spam' ? 'spam' : 'new',
				'is_spam' => $spam === 'spam' ? 1 : 0,
				'client_ip' => $ip,
				'utm_source' => $utm_source,
				'utm_medium' => $utm_medium,
				'utm_term' => $utm_term,
				'utm_content' => $utm_content,
				'utm_campaign' => $utm_campaign,
				'upsells_json' => is_array($upsells_array) ? json_encode($upsells_array, JSON_UNESCAPED_UNICODE) : '',
			]);
		} catch (\Throwable $e) {
			error_log('Order save error: ' . $e->getMessage());
		}
	}

	//add recaptcha end
	//}else{
	// header("Location: {$_SERVER['HTTP_REFERER']}");
	//}
		
}

?>

<html lang="uk">

<head>
	<meta charset="UTF-8">
	<title><?php if (isset($phone) && !empty($phone)) { echo 'Дякуємо за замовлення!'; } else { echo 'Якась проблема...'; } ?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&amp;display=swap" rel="stylesheet">

<?php if (isset($phone) && !empty($phone) && $spam == 'nospam') { ?>
<!-- Meta Pixel Code -->
	<script>
	! function(f, b, e, v, n, t, s) {
		if (f.fbq) return;
		n = f.fbq = function() {
			n.callMethod ?
				n.callMethod.apply(n, arguments) : n.queue.push(arguments)
		};
		if (!f._fbq) f._fbq = n;
		n.push = n;
		n.loaded = !0;
		n.version = '2.0';
		n.queue = [];
		t = b.createElement(e);
		t.async = !0;
		t.src = v;
		s = b.getElementsByTagName(e)[0];
		s.parentNode.insertBefore(t, s)
	}(window, document, 'script',
		'https://connect.facebook.net/en_US/fbevents.js');
	fbq('init', '<?= $fbp; ?>');
	fbq('track', 'PageView');
	fbq('track', 'Lead');
	//fbq('track', 'Purchase', {currency: "UAH", value: <?= $product_price; ?>});
	</script>
	<noscript><img height="1" width="1" style="display:none" src="/o__www.facebook.com/tr?id=<?= $fbp; ?>&ev=PageView&noscript=1" /></noscript>
<!-- End Meta Pixel Code -->

<!-- TikTok Pixel Code Start -->
<?php if(isset($_SESSION['tiktok']) && $_SESSION['tiktok'] !== ''){ ?>
<script>
!function (w, d, t) {
  w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(
var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=document.createElement("script")
;n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=document.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};


  ttq.load('<?= $_SESSION['tiktok']; ?>');
  ttq.page();
  ttq.track('PlaceAnOrder', {
	content_type: 'product',
	content_id: '<?= $product_id; ?>',
	currency: 'UAH',
	value: '<?= $product_price; ?>'
	});
}(window, document, 'ttq');
</script>
<?php } ?>
<!-- TikTok Pixel Code End -->
<?php } ?>
</head>

<body>
	<!-- Декор (конфетті) -->
	<div class="confetti" aria-hidden="true">
		<span></span><span></span><span></span><span></span><span></span><span></span>
		<span></span><span></span><span></span><span></span><span></span><span></span>
	</div>

	<main class="wrap">
		<section class="card">
			<!-- Ліва частина -->
			<div class="content">
				<?php if (isset($phone) && !empty($phone)) { ?>
				<div class="badge">
					<span class="dot"></span>
					Замовлення успішно створено
				</div>

				<h1 class="title">
					Вітаємо! <span class="grad">Ваше замовлення прийнято</span> ✅
				</h1>

				<p class="subtitle">
					Найближчим часом з вами зв’яжеться оператор для підтвердження.
					Перевірте правильність даних нижче.
				</p>

				<!-- Блок з даними -->
				<div class="info">
					<div class="infoHead">
						<div class="check">
							<!-- SVG галочка -->
							<svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
								<path d="M20 7L10 17L5 12" stroke="currentColor" stroke-width="2.5"
									stroke-linecap="round" stroke-linejoin="round"></path>
							</svg>
						</div>
						<div>
							<div class="infoTitle">Ваші дані</div>
							<div class="infoHint">Якщо помилка — поверніться та заповніть ще раз</div>
						</div>
					</div>

					<div class="infoGrid">
						<div class="field">
							<div class="label">П.І.Б.</div>
							<div class="value" id="client"> <?= htmlspecialchars($name); ?> </div>
						</div>

						<div class="field">
							<div class="label">Телефон</div>
							<div class="value mono" id="tel"> <?= htmlspecialchars($phone); ?> </div>
						</div>
					</div>

					<div class="note">
						<span class="noteIcon">⏰</span>
						Якщо замовлення зроблено після 21:00, обробка буде в першій половині наступного дня.
					</div>
				</div>
				<?php } else { ?>

				<div class="badge error">
					<span class="dot"></span>
					Сталася помилка
				</div>

				<h1 class="title">
					<span class="grad">Щось пішло не так...</span> 😕
				</h1>

				<p class="subtitle">
					На жаль, ми не змогли обробити ваше замовлення. Спробуйте ще раз.
				</p>
				<?php } ?>

				<!-- Кнопки -->
				<div class="actions">
					<a class="btn primary" href="javascript:history.back();">
						Заповнити ще раз
						<span class="arrow">↩</span>
					</a>

					<!--
					<a class="btn ghost" href="/" rel="nofollow">
						На головну
					</a>
				-->

					<!-- Опціонально: кнопка дзвінка (якщо хочеш) -->
					<?php if($my_telegram ) { ?>
					 <a class="btn ghost" href="<?= $my_telegram; ?>" rel="nofollow" target="_blank">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" style="margin-right: 8px;">
							<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm4.64 6.8c-.15 1.58-.8 5.42-1.13 7.19-.14.75-.42 1-.68 1.03-.58.05-1.02-.38-1.58-.75-.88-.58-1.38-.94-2.23-1.5-.99-.65-.35-1.01.22-1.59.15-.15 2.71-2.48 2.76-2.69a.2.2 0 00-.05-.18c-.06-.05-.14-.03-.21-.02-.09.02-1.49.95-4.22 2.79-.4.27-.76.41-1.08.4-.36-.01-1.04-.2-1.55-.37-.63-.2-1.13-.31-1.08-.66.02-.18.27-.36.74-.55 2.92-1.27 4.86-2.11 5.83-2.51 2.78-1.16 3.35-1.36 3.73-1.36.08 0 .27.02.39.12.1.08.13.19.14.27-.01.06.01.24 0 .38z"/>
						</svg>
						Ми в Telegram
					 </a>
					 <?php } ?>


				</div>

				<div class="trust">
					<div class="trustItem">🔒 Дані захищені</div>
					<div class="trustItem">⚡ Швидка обробка</div>
					<div class="trustItem">📞 Підтвердження оператором</div>
				</div>
			</div>

			<!-- Права частина (дівчина + бульбашка) -->
			<aside class="side">
				<div class="sideGlow" aria-hidden="true"></div>

				<div class="operatorWrap">
					<div class="bubble">
						<div class="bubbleTitle">Дякуємо, що обираєте нас! 💛</div>
						<div class="bubbleText">Ми вже готуємо ваше замовлення. Очікуйте дзвінок.</div>
					</div>

					<img class="operator" src="data:image/png;base64,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UmVhZHlxyWU8AAADJmlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNS42LWMwNjcgNzkuMTU3NzQ3LCAyMDE1LzAzLzMwLTIzOjQwOjQyICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgQ0MgMjAxNSAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6NjVFM0IyM0UzMDY1MTFFOUEzQzVDQjYwNzZGRDc4MTUiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6NjVFM0IyM0YzMDY1MTFFOUEzQzVDQjYwNzZGRDc4MTUiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo2NUUzQjIzQzMwNjUxMUU5QTNDNUNCNjA3NkZENzgxNSIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo2NUUzQjIzRDMwNjUxMUU5QTNDNUNCNjA3NkZENzgxNSIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PqEc5QYAATRGSURBVHja7L0HgFxndTb83Jm509vuzvai3rvkgmXjBtjBxhgDDgkEvpBAaAFCSAKGkA9CMXwkBPiBYIoN2Ka5d9yw3OSi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" alt="Оператор">
				</div>

				<div class="miniCard">
					<div class="miniTitle">Порада</div>
					<div class="miniText">Будь ласка, тримайте телефон поруч — оператор може зателефонувати найближчим
						часом.</div>
				</div>
			</aside>
		</section>

		<footer class="footer">
			<span>©</span> Дякуємо за довіру. Гарного дня!
		</footer>
	</main>



	<style>
		:root {
			--bg1: #0b1020;
			--bg2: #0d1b2a;
			--card: rgba(255, 255, 255, .08);
			--stroke: rgba(255, 255, 255, .14);
			--text: rgba(255, 255, 255, .92);
			--muted: rgba(255, 255, 255, .70);
			--shadow: 0 18px 60px rgba(0, 0, 0, .45);

			--a: #7c3aed;
			/* фіолет */
			--b: #22c55e;
			/* зел */
			--c: #f59e0b;
			/* жовт */
			--d: #60a5fa;
			/* блак */
		}

		* {
			box-sizing: border-box
		}

		html,
		body {
			height: 100%
		}

		body {
			margin: 0;
			font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
			color: var(--text);
			background:
				radial-gradient(1100px 700px at 15% 10%, rgba(124, 58, 237, .35), transparent 55%),
				radial-gradient(900px 600px at 85% 25%, rgba(34, 197, 94, .25), transparent 55%),
				radial-gradient(900px 600px at 60% 95%, rgba(96, 165, 250, .22), transparent 60%),
				linear-gradient(180deg, var(--bg1), var(--bg2));
			overflow-x: hidden;
		}

		/* легкий “патерн” */
		body::before {
			content: "";
			position: fixed;
			inset: 0;
			pointer-events: none;
			background:
				radial-gradient(circle at 1px 1px, rgba(255, 255, 255, .06) 1px, transparent 1px);
			background-size: 26px 26px;
			opacity: .35;
		}

		.wrap {
			min-height: 100%;
			display: flex;
			flex-direction: column;
			justify-content: center;
			padding: 28px 16px 22px;
		}

		.card {
			width: min(1100px, 100%);
			margin: 0 auto;
			background: var(--card);
			border: 1px solid var(--stroke);
			border-radius: 24px;
			box-shadow: var(--shadow);
			backdrop-filter: blur(14px);
			overflow: hidden;
			display: grid;
			grid-template-columns: 1.2fr .85fr;
		}

		.content {
			padding: 30px 28px;
		}

		.side {
			position: relative;
			border-left: 1px solid var(--stroke);
			padding: 22px;
			display: flex;
			flex-direction: column;
			gap: 14px;
			justify-content: center;
			background:
				radial-gradient(800px 500px at 50% 20%, rgba(255, 255, 255, .06), transparent 60%);
		}

		.sideGlow {
			position: absolute;
			inset: -120px -120px auto auto;
			width: 340px;
			height: 340px;
			background: radial-gradient(circle, rgba(245, 158, 11, .22), transparent 60%);
			filter: blur(6px);
			pointer-events: none;
		}

		.badge {
			display: inline-flex;
			align-items: center;
			gap: 10px;
			padding: 10px 12px;
			border-radius: 999px;
			border: 1px solid var(--stroke);
			background: rgba(0, 0, 0, .18);
			color: var(--muted);
			font-weight: 600;
			font-size: 13px;
		}

		.badge .dot {
			width: 9px;
			height: 9px;
			border-radius: 50%;
			background: linear-gradient(135deg, var(--b), var(--d));
			box-shadow: 0 0 0 4px rgba(34, 197, 94, .12);
		}

		.title {
			margin: 14px 0 8px;
			font-size: clamp(26px, 3.1vw, 40px);
			line-height: 1.08;
			letter-spacing: -0.02em;
		}

		.grad {
			background: linear-gradient(90deg, #a78bfa, #34d399, #60a5fa);
			-webkit-background-clip: text;
			background-clip: text;
			color: transparent;
		}

		.subtitle {
			margin: 0 0 18px;
			color: var(--muted);
			max-width: 60ch;
			font-size: 15px;
			line-height: 1.5;
		}

		.info {
			border: 1px solid var(--stroke);
			border-radius: 18px;
			background: rgba(0, 0, 0, .22);
			padding: 16px;
		}

		.infoHead {
			display: flex;
			gap: 12px;
			align-items: flex-start;
			margin-bottom: 12px;
		}

		.check {
			width: 38px;
			height: 38px;
			border-radius: 12px;
			display: grid;
			place-items: center;
			background: linear-gradient(135deg, rgba(34, 197, 94, .22), rgba(96, 165, 250, .18));
			border: 1px solid rgba(255, 255, 255, .14);
			color: #bbf7d0;
		}

		.infoTitle {
			font-weight: 800;
			letter-spacing: -.01em
		}

		.infoHint {
			color: var(--muted);
			font-size: 13px;
			margin-top: 2px
		}

		.infoGrid {
			display: grid;
			grid-template-columns: 1fr 1fr;
			gap: 12px;
		}

		.field {
			padding: 12px 12px;
			border-radius: 14px;
			border: 1px solid rgba(255, 255, 255, .12);
			background: rgba(255, 255, 255, .05);
		}

		.label {
			color: var(--muted);
			font-size: 12px;
			margin-bottom: 6px;
		}

		.value {
			font-weight: 800;
			letter-spacing: -.01em;
			font-size: 15px;
			word-break: break-word;
		}

		.mono {
			font-variant-numeric: tabular-nums;
			letter-spacing: .02em;
		}

		.note {
			margin-top: 12px;
			padding: 12px 12px;
			border-radius: 14px;
			border: 1px dashed rgba(255, 255, 255, .18);
			background: rgba(245, 158, 11, .06);
			color: rgba(255, 255, 255, .88);
			display: flex;
			gap: 10px;
			align-items: flex-start;
			line-height: 1.35;
			font-size: 13.5px;
		}

		.noteIcon {
			width: 28px;
			height: 28px;
			border-radius: 10px;
			display: grid;
			place-items: center;
			background: rgba(245, 158, 11, .12);
			border: 1px solid rgba(255, 255, 255, .14);
		}

		.actions {
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
			margin-top: 16px;
		}

		.btn {
			appearance: none;
			border: 1px solid transparent;
			border-radius: 14px;
			padding: 12px 14px;
			text-decoration: none;
			color: var(--text);
			font-weight: 800;
			letter-spacing: -.01em;
			display: inline-flex;
			align-items: center;
			gap: 10px;
			transition: transform .14s ease, background .14s ease, border-color .14s ease, box-shadow .14s ease;
			user-select: none;
		}

		.btn:active {
			transform: translateY(1px)
		}

		.btn.primary {
			background: linear-gradient(135deg, rgba(124, 58, 237, .95), rgba(96, 165, 250, .75));
			box-shadow: 0 10px 28px rgba(124, 58, 237, .22);
		}

		.btn.primary:hover {
			box-shadow: 0 14px 34px rgba(124, 58, 237, .30);
			transform: translateY(-1px);
		}

		/* Стиль "Ghost" спеціально для Telegram */
		.btn.ghost {
			background-color: transparent;
			color: #24A1DE; /* Фірмовий колір Telegram */
			border: 2px solid #24A1DE;
		}

		/* Ефект при наведенні */
		.btn.ghost:hover {
			background-color: #24A1DE;
			color: #fff;
			box-shadow: 0 4px 15px rgba(36, 161, 222, 0.4); /* М'яке світіння */
			transform: translateY(-2px); /* Легке підняття вгору */
		}

		/* Ефект при кліку */
		.btn.ghost:active {
			transform: translateY(0);
		}

		.btn.soft {
			background: rgba(34, 197, 94, .10);
			border-color: rgba(34, 197, 94, .22);
		}

		.btn.soft:hover {
			background: rgba(34, 197, 94, .14);
			transform: translateY(-1px);
		}

		.arrow {
			opacity: .9;
			font-weight: 900;
		}

		.trust {
			margin-top: 14px;
			display: flex;
			flex-wrap: wrap;
			gap: 10px;
		}

		.trustItem {
			font-size: 12.5px;
			color: var(--muted);
			padding: 8px 10px;
			border-radius: 999px;
			border: 1px solid rgba(255, 255, 255, .12);
			background: rgba(0, 0, 0, .18);
		}

		/* Права сторона */
		.operatorWrap {
			position: relative;
			border: 1px solid rgba(255, 255, 255, .12);
			background: rgba(255, 255, 255, .05);
			border-radius: 20px;
			padding: 16px 16px 10px;
			overflow: hidden;
		}

		.bubble {
			border-radius: 16px;
			padding: 12px 12px;
			border: 1px solid rgba(255, 255, 255, .14);
			background: rgba(0, 0, 0, .20);
			margin-bottom: 10px;
		}

		.bubbleTitle {
			font-weight: 900;
			letter-spacing: -.01em;
		}

		.bubbleText {
			margin-top: 6px;
			color: var(--muted);
			font-size: 13px;
			line-height: 1.35;
		}

		.operator {
			width: 100%;
			max-width: 280px;
			display: block;
			margin: 6px auto 0;
			filter: drop-shadow(0 18px 24px rgba(0, 0, 0, .30));
			transform: translateY(2px);
		}

		.miniCard {
			border-radius: 18px;
			border: 1px solid rgba(255, 255, 255, .12);
			background: rgba(0, 0, 0, .18);
			padding: 14px;
		}

		.miniTitle {
			font-weight: 900;
		}

		.miniText {
			margin-top: 6px;
			color: var(--muted);
			font-size: 13px;
			line-height: 1.45;
		}

		.footer {
			width: min(1100px, 100%);
			margin: 14px auto 0;
			text-align: center;
			color: rgba(255, 255, 255, .55);
			font-size: 12.5px;
		}

		/* Конфетті (CSS-only) */
		.confetti {
			position: fixed;
			inset: 0;
			pointer-events: none;
			overflow: hidden;
		}

		.confetti span {
			position: absolute;
			width: 10px;
			height: 18px;
			border-radius: 6px;
			top: -10%;
			left: 10%;
			opacity: .85;
			transform: rotate(10deg);
			animation: fall 6.5s linear infinite;
			filter: drop-shadow(0 8px 8px rgba(0, 0, 0, .25));
		}

		.confetti span:nth-child(1) {
			left: 8%;
			background: rgba(124, 58, 237, .9);
			animation-duration: 6.8s;
			animation-delay: .1s;
		}

		.confetti span:nth-child(2) {
			left: 18%;
			background: rgba(34, 197, 94, .85);
			animation-duration: 7.6s;
			animation-delay: .6s;
		}

		.confetti span:nth-child(3) {
			left: 28%;
			background: rgba(96, 165, 250, .9);
			animation-duration: 6.2s;
			animation-delay: .2s;
		}

		.confetti span:nth-child(4) {
			left: 38%;
			background: rgba(245, 158, 11, .9);
			animation-duration: 7.2s;
			animation-delay: 1.1s;
		}

		.confetti span:nth-child(5) {
			left: 48%;
			background: rgba(236, 72, 153, .85);
			animation-duration: 6.9s;
			animation-delay: .9s;
		}

		.confetti span:nth-child(6) {
			left: 58%;
			background: rgba(34, 197, 94, .85);
			animation-duration: 7.9s;
			animation-delay: 1.4s;
		}

		.confetti span:nth-child(7) {
			left: 68%;
			background: rgba(96, 165, 250, .9);
			animation-duration: 6.7s;
			animation-delay: .4s;
		}

		.confetti span:nth-child(8) {
			left: 78%;
			background: rgba(245, 158, 11, .9);
			animation-duration: 7.4s;
			animation-delay: 1.2s;
		}

		.confetti span:nth-child(9) {
			left: 88%;
			background: rgba(124, 58, 237, .9);
			animation-duration: 6.6s;
			animation-delay: .8s;
		}

		.confetti span:nth-child(10) {
			left: 14%;
			background: rgba(236, 72, 153, .85);
			animation-duration: 8.0s;
			animation-delay: 1.7s;
		}

		.confetti span:nth-child(11) {
			left: 44%;
			background: rgba(124, 58, 237, .9);
			animation-duration: 7.1s;
			animation-delay: 2.0s;
		}

		.confetti span:nth-child(12) {
			left: 74%;
			background: rgba(34, 197, 94, .85);
			animation-duration: 7.7s;
			animation-delay: 2.3s;
		}

		@keyframes fall {
			0% {
				transform: translateY(-20vh) rotate(0deg);
			}

			100% {
				transform: translateY(120vh) rotate(340deg);
			}
		}

		/* Адаптація */
		@media (max-width: 920px) {
			.card {
				grid-template-columns: 1fr;
			}

			.side {
				border-left: 0;
				border-top: 1px solid var(--stroke);
			}

			.operator {
				max-width: 250px;
			}
		}

		@media (max-width: 520px) {
			.content {
				padding: 22px 16px;
			}

			.side {
				padding: 16px;
			}

			.infoGrid {
				grid-template-columns: 1fr;
			}

			.btn {
				width: 100%;
				justify-content: center;
			}

			.trust {
				gap: 8px;
			}

			.trustItem {
				width: 100%;
				text-align: center;
			}
		}
	</style>
</body>

</html>