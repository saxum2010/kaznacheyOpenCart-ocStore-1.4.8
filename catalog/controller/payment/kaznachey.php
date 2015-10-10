<?php
class ControllerPaymentkaznachey extends Controller {
  private $error = array();
	public	$urlGetMerchantInfo = 'http://payment.kaznachey.net/api/PaymentInterface/CreatePayment';
	public	$urlGetClientMerchantInfo = 'http://payment.kaznachey.net/api/PaymentInterface/GetMerchatInformation';

  protected function index() {
    $this->data['button_confirm'] = $this->language->get('button_confirm');
    $this->data['button_back'] = $this->language->get('button_back');

    $this->data['action'] = '/index.php?route=payment/kaznachey/pay';

    $this->load->model('checkout/order');
	
	$this->id = 'payment';
	
    $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
	$this->data['order_id'] = $this->session->data['order_id'];
	
	$cc_types = $this->GetMerchnatInfo();
	if($cc_types)
	{
		$this->data['cc_type'] = '<select name="cc_type" id="cc_type">';
		foreach ($cc_types["PaySystems"] as $paysystem)
		{
			$this->data['cc_type'] .= '<option value="'.$paysystem['Id'].'">'.$paysystem['PaySystemName'].'</option>';
		}
		$this->data['cc_type'] .= '</select>';
		
		$term_url = $this->GetTermToUse();
		$this->data['cc_agreed'] = " <input type='checkbox' class='form-checkbox' name='cc_agreed' id='cc_agreed' checked><label for='edit-panes-payment-details-cc-agreed'><a href='$term_url'  target='_blank' >Согласен с условиями использования</a></label>";
	}
	
	$cur_code = (isset($order_info['currency_code']))?$order_info['currency_code']:'UAH';
	
    if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/kaznachey.tpl')) {
      $this->template = $this->config->get('config_template') . '/template/payment/kaznachey.tpl';
    } else {
      $this->template = 'default/template/payment/kaznachey.tpl';
    }

    $this->render(); 
  }
  
  public function fail() {
    $this->redirect(HTTPS_SERVER . 'index.php?route=checkout/checkout');
  }

   public function success() {
	if(isset($_GET['Result']))
	{
		if ($_GET['Result'] == 'success')
		{
			$this->redirect(HTTPS_SERVER . 'index.php?route=checkout/success');
		}		
		
		if ($_GET['Result'] == 'deferred')
		{
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/kaznachey_deferred.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/kaznachey.tpl';
			} else {
				$this->template = 'default/template/payment/kaznachey_deferred.tpl';
			}

			//$this->render(); 
			$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
		}
	}
  }
  
  public function callback() 
  { 
	$HTTP_RAW_POST_DATA = @$HTTP_RAW_POST_DATA ? $HTTP_RAW_POST_DATA : file_get_contents('php://input');
    
	$hrpd = json_decode($HTTP_RAW_POST_DATA);
	
	$this->load->model('checkout/order');

    $merchantGuid = $this->config->get('kaznachey_merchant_id'); 
    $merchnatSecretKey = $this->config->get('kaznachey_secret_key');
	
	if(@$hrpd->MerchantInternalPaymentId)
	{
		$order_id = intval($hrpd->MerchantInternalPaymentId);
		$order_info = $this->model_checkout_order->getOrder($order_id);
		if($order_info)
		{
			$signature_u = md5(md5(
				$merchantGuid.
				$merchnatSecretKey.
				"$order_info[total]".
				$order_id
			));
		}

	if($hrpd->ErrorCode == 0)
	{
		if($hrpd->CustomMerchantInfo == $signature_u)
		{
			if($order_info['order_status_id'] == 0) {
				$this->model_checkout_order->confirm($order_id, $this->config->get('kaznachey_order_status_id'), 'kaznachey');
			}
			if($order_info['order_status_id'] != $this->config->get('kaznachey_order_status_id')) {
				$this->model_checkout_order->update($order_id, $this->config->get('kaznachey_order_status_id'),'kaznachey',TRUE);
			}
		}else{
			
			$this->model_checkout_order->update($order_id, 9,'kaznachey Error .The signature is not valid',FALSE);
			$this->log->write("kaznachey order № $order_id The signature is not valid");
		}
	}else{

		$this->model_checkout_order->update($order_id, 9,'kaznachey Error. Transaction failed',FALSE);
		$this->log->write("kaznachey order # $order_id  Transaction failed");
	}
	
	}else{
		$this->log->write("kaznachey order id id failed");
	}
  }
  
	public function getmerchinfo() 
	{
		$this->load->model('checkout/order');
		
		$json = array();
		
		$cc_types = $this->GetMerchnatInfo();
		if($cc_types)
		{
			$json['cc_type'] = '<tr class="cc_type_tr">
				<td colspan="2"> <select name="cc_type" id="cc_type">';
			foreach ($cc_types["PaySystems"] as $paysystem)
			{
				$json['cc_type'] .= '<option value="'.$paysystem['Id'].'">'.$paysystem['PaySystemName'].'</option>';
			}
				$json['cc_type'] .= '</select></td></tr>';
		}
		
		$this->response->setOutput(json_encode($json));
  }
  
  public function pay() 
  {
    if (isset($this->request->post['order_id'])) {
		$order_id = $this->request->post['order_id'];
	}elseif(isset($this->session->data['order_id']))
	{
		$order_id = $this->session->data['order_id'];
	}else {
		$order_id = 0;
	}

	$this->load->model('checkout/order');

	$order_info = $this->model_checkout_order->getOrder($order_id);

	$currency_code = (isset($order_info['currency_code'])) ? $order_info['currency_code'] : 'UAH';
		
   	if (!isset($this->request->post['cc_agreed'])) {
		$this->redirect($this->url->link('checkout/cart'));
	} 	
	
	if (isset($this->request->post['cc_type'])) {
		$selectedPaySystemId = $this->request->post['cc_type'];
	} else {
		$this->redirect($this->url->link('checkout/cart'));
	}

	$urlGetMerchantInfo = $this->urlGetMerchantInfo;
    $merchantGuid = $this->config->get('kaznachey_merchant_id'); 
    $merchnatSecretKey = $this->config->get('kaznachey_secret_key');
    
	$product_count = 0;
	$amount2 = 0;
	
	if (!$this->cart->hasProducts() || $this->cart->hasProducts() == 0) {
		$this->redirect($this->url->link('checkout/cart'));
	}
	
	$getProducts = $this->cart->getProducts();
	$i = 0;
	foreach ($getProducts as $key=>$product) { 

		$products[$i]['ProductItemsNum'] = number_format($product['quantity'], 2, '.', '');
		$products[$i]['ProductName'] = $product['name'];
		$products[$i]['ProductPrice'] = number_format($this->currency->format($product['price'], $currency_code, false, false), 2, '.', '');
		$products[$i]['ProductId'] = $product['model'];
		$product_count += $product['quantity'];
		$amount2 += $product['price'] * $product['quantity'];
		$products[$i]['ImageUrl'] = (isset($product['image']))?'http://'.$_SERVER['HTTP_HOST'] .'/image/'. $product['image']:'';
		$i++;
	}
	
	$amount = number_format($order_info['total'], 2, '.', '');
	$amount2  = number_format($amount2, 2, '.', '');

	if($amount != $amount2)
	{
		$tt = $amount - $amount2; 
		$products[$i]['ProductItemsNum'] = '1.00';
		$products[$i]['ProductName'] = 'Доставка или скидка';
		$products[$i]['ProductPrice'] = number_format($this->currency->format($tt, $currency_code, false, false), 2, '.', '');
		$products[$i]['ProductId'] = '00001'; 
		$pr_c = '1.00';
		$amount2  = number_format($amount2 + $tt, 2, '.', '');
	}
	
	$order_id = $order_info['order_id'];

	$signature_u = md5(md5(
		$merchantGuid.
		$merchnatSecretKey.
		"$order_info[total]".
		$order_id
	));

	$copy_result_url = 'http://'.$_SERVER['HTTP_HOST']  . '/index.php?route=payment/kaznachey/callback';
	$copy_success_url = 'http://'.$_SERVER['HTTP_HOST']  . '/index.php?route=payment/kaznachey/success';
	
    $paymentDetails = Array(
       "MerchantInternalPaymentId"=>"$order_id",
       "MerchantInternalUserId"=>"$order_info[customer_id]",
       "EMail"=>"$order_info[email]",
       "PhoneNumber"=>"$order_info[telephone]",
       "CustomMerchantInfo"=>"$signature_u",
       "StatusUrl"=>"$copy_result_url",
       "ReturnUrl"=>"$copy_success_url",
       "BuyerCountry"=>"$order_info[payment_country]",
       "BuyerFirstname"=>"$order_info[payment_firstname]",
       "BuyerPatronymic"=>"1",
       "BuyerLastname"=>"$order_info[payment_lastname]",
       "BuyerStreet"=>"$order_info[payment_address_1]",
       "BuyerZone"=>"$order_info[payment_zone]",
       "BuyerZip"=>"$order_info[payment_zone_id]",
       "BuyerCity"=>"$order_info[payment_city]",

       "DeliveryFirstname"=>"$order_info[shipping_firstname]",
       "DeliveryLastname"=>"$order_info[shipping_lastname]",
       "DeliveryZip"=>"$order_info[shipping_zone_id]",
       "DeliveryCountry"=>"$order_info[shipping_country]",
       "DeliveryPatronymic"=>"1",
       "DeliveryStreet"=>"$order_info[shipping_address_1]",
       "DeliveryCity"=>"$order_info[shipping_city]",
       "DeliveryZone"=>"$order_info[shipping_zone]",
    );
	
	$product_count = (isset($pr_c)) ? $product_count + $pr_c : $product_count;
	$product_count = number_format($product_count, 2, '.', '');	

	$amount2 = number_format($this->currency->format($amount2, $currency_code, false, false), 2, '.', '');

	$signature = md5(
		$merchantGuid.
		"$amount2".
		"$product_count".
		$paymentDetails["MerchantInternalUserId"].
		$paymentDetails["MerchantInternalPaymentId"].
		$selectedPaySystemId.
		$merchnatSecretKey
	);	
	
    $request = Array(
        "SelectedPaySystemId"=>$selectedPaySystemId,
        "Products"=>$products,
        "PaymentDetails"=>$paymentDetails,
        "Signature"=>$signature,
        "MerchantGuid"=>$merchantGuid,
		"Currency"=>$currency_code
    );

		$res = $this->sendRequestKaznachey($urlGetMerchantInfo, json_encode($request));
		$result = json_decode($res,true);

		if($result['ErrorCode'] != 0)
		{
			$this->redirect(HTTPS_SERVER . 'index.php?route=checkout/checkout');
		}
		
		echo base64_decode($result["ExternalForm"]);
	
	}
  
    public function sendRequestKaznachey($url,$data)
    {
		//header('Content-Type: text/html; charset=utf-8');
        $curl =curl_init();
        if (!$curl)
            return false;

        curl_setopt($curl, CURLOPT_URL,$url );
        curl_setopt($curl, CURLOPT_POST,true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, 
                array("Expect: ","Content-Type: application/json; charset=UTF-8",'Content-Length: ' 
                    . strlen($data)));
        curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER,True);
        $res =  curl_exec($curl);
        curl_close($curl);
        return $res;
    }
	
	function GetMerchnatInfo($id = false)
	{
		$urlGetClientMerchantInfo = $this->urlGetClientMerchantInfo; 
		$merchantGuid = $this->config->get('kaznachey_merchant_id'); 
		$merchnatSecretKey  = $this->config->get('kaznachey_secret_key');

		$requestMerchantInfo = Array(
			"MerchantGuid"=>$merchantGuid,
			"Signature"=>md5($merchantGuid.$merchnatSecretKey)
		);

		$resMerchantInfo = json_decode($this->sendRequestKaznachey($urlGetClientMerchantInfo , json_encode($requestMerchantInfo)),true); 
		
		if($id)
		{
			foreach ($resMerchantInfo["PaySystems"] as $key=>$paysystem)
			{
				if($paysystem['Id'] == $id)
				{
					return $paysystem;
				}
			}
		}else{
			return $resMerchantInfo;
		}
	}	
	
	function GetTermToUse()
	{
		$urlGetClientMerchantInfo = $this->urlGetClientMerchantInfo; 
		$merchantGuid = $this->config->get('kaznachey_merchant_id'); 
		$merchnatSecretKey  = $this->config->get('kaznachey_secret_key');

		$requestMerchantInfo = Array(
			"MerchantGuid"=>$merchantGuid,
			"Signature"=>md5($merchantGuid.$merchnatSecretKey)
		);

		$resMerchantInfo = json_decode($this->sendRequestKaznachey($urlGetClientMerchantInfo , json_encode($requestMerchantInfo)),true); 
		
		return $resMerchantInfo["TermToUse"];
	}
	
	
	public function validate() {
		//$this->language->load('checkout/checkout');
		
		$json = array();
		
		$this->load->model('account/address');
		if ($this->customer->isLogged()) {
			$this->request->post['email'] = $this->customer->getEmail();
			$this->request->post['telephone'] = $this->customer->getTelephone();
			$json['customer']['customer_id'] = $this->customer->getId();
		}
		$this->response->setOutput(json_encode($json));
	}
  
}
?>