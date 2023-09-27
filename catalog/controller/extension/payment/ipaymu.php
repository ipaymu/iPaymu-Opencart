<?php

class ControllerExtensionPaymentIpaymu extends Controller
{

    public function index()
    {
        $this->language->load('extension/payment/ipaymu');
        $data['action'] = $this->url->link('extension/payment/ipaymu/send');
        $data['button_confirm'] = $this->language->get('button_confirm');

        return $this->load->view('extension/payment/ipaymu', $data);
    }

    private function simpleXor($string, $password)
    {
        $data = array();

        for ($i = 0; $i < strlen($password); $i++) {
            $data[$i] = ord(substr($password, $i, 1));
        }

        $output = '';

        for ($i = 0; $i < strlen($string); $i++) {
            $output .= chr(ord(substr($string, $i, 1)) ^ ($data[$i % strlen($password)]));
        }

        return $output;
    }

    public function send()
    {
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $data['ap_merchant'] = $this->config->get('payment_ipaymu_merchant');
        $data['url_web'] = $this->url->link('common/home');
        $data['ap_security'] = $this->config->get('payment_ipaymu_security');
        $data['ap_paypal'] = $this->config->get('payment_ipaymu_paypal');
        $data['ap_ipaymu_rate'] = $this->config->get('payment_ipaymu_rate');
        $data['ap_inv_paypal'] = $this->config->get('payment_ipaymu_inv_paypal');
        $data['ap_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        $data['ap_currency'] = $order_info['currency_code'];
        $data['ap_purchasetype'] = 'Item';
        $data['ap_itemname'] = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];
        $data['ap_itemcode'] = $this->session->data['order_id'];

        $security_code = $data['ap_security'] . $this->session->data['order_id'];

        // $data = array();
        $data['orderid'] = $this->session->data['order_id'];
        $def_curr = $this->config->get('config_currency');
        $data['jumlah'] = $def_curr == 'IDR' ? $order_info['total'] : $this->currency->convert($order_info['total'], $order_info['currency_code'], 'IDR');


        $crypt_data = array();

        foreach ($data as $key => $value) {
            $crypt_data[] = $key . '=' . $value;
        }

        $data['crypt'] = base64_encode($this->simpleXor(utf8_decode(implode('&', $crypt_data)), $security_code));

        // $data['ap_returnurl'] = str_replace('&amp;', '&', $this->url->link('extension/payment/ipaymu/success', 'order_id=' . $this->session->data['order_id'] . '&crypt=' . $data['crypt']));
        // $data['ap_notifyurl'] = str_replace('&amp;', '&', $this->url->link('checkout/checkout'));

        $successUrl = $this->url->link('checkout/success&');
        $data['ap_returnurl'] = $successUrl;
        $data['ap_notifyurl'] = $this->url->link('extension/payment/ipaymu/payment_notification') . '&order_id=' . $this->session->data['order_id'];

        $data['ap_cancelurl'] = $this->url->link('checkout/checkout', '', 'SSL');

        $this->session->data['crypt'] = $data['crypt'];

        $url = 'https://my.ipaymu.com/payment';

        // Prepare Parameters
        $pprate = isset($data['ap_ipaymu_rate']) && !empty($data['ap_ipaymu_rate']) ? $data['ap_ipaymu_rate'] : 1;
        $params = array(
            'key' => '' . $this->config->get('payment_ipaymu_security') . '', // API Key Merchant / Penjual
            'action' => 'payment',
            'product' => 'Order #' . $data['orderid'] . '',
            'price' => '' . $data['jumlah'] . '', // Total Harga
            'quantity' => 1,
            'comments' => 'Transaksi Pembelian di ' . $_SERVER["SERVER_NAME"] . '', // Optional           
            'ureturn' => '' . $data['ap_returnurl'] . '',
            'unotify' => '' . $data['ap_notifyurl'] . '',
            'ucancel' => '' . $data['ap_cancelurl'] . '',
            'buyer_name'    => "{$this->customer->getFirstName()} {$this->customer->getLastName()}",
            'buyer_phone'   => $this->customer->getTelephone(),
            'buyer_email'   => $this->customer->getEmail(),
            'reference_id'   => '' . $data['orderid'] . '',
            'format' => 'json'
        );


        $params_string = http_build_query($params);

        //open connection_aborted(oci_internal_debug(onoff))
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($params));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        //execute post
        $request = curl_exec($ch);

        if ($request === false) {
            echo 'Curl Error: ' . curl_error($ch);
        } else {

            $result = json_decode($request, true);

            if (isset($result['url'])) {
                $message = 'Transaction via iPaymu ' . $result['url'];
                $this->load->model('checkout/order');
                $this->model_checkout_order->addOrderHistory($data['orderid'], 1, $message);
                header('location: ' . $result['url']);
            } else {
                echo $request;
            }
        }
        //close connection
        curl_close($ch);
    }

    public function success()
    {
        $data = array();
        $cryptSession = $this->session->data['crypt'];

        foreach ($_REQUEST as $key => $value) {
            $data[$key] = $value;
        }

        if (empty($data['crypt'])) {
            // $message = 'Transaction via iPaymu';
            // $this->model_checkout_order->addOrderHistory($data['order_id'], 1, $message);
            
            $this->response->redirect($this->url->link('checkout/success'));
        } else {
            
        
            $data['crypt'] = str_replace(" ", "+", $data['crypt']);
    
            if (isset($cryptSession) && isset($data['crypt']) && ($data['crypt'] == $cryptSession)) {
                unset($cryptSession);
                $this->load->model('checkout/order');
    
                if ($data['status'] == 'berhasil') {
                    $message = 'iPaymu with transaction id: ' . $data['trx_id'];
                    if ($data['ref_no']) {
                        $message .= ' ,ref. number: ' . $data['ref_no'];
                    }
                    $this->model_checkout_order->addOrderHistory($data['order_id'], 15, $message);
                } elseif ($data['status'] == 'pending') {
                    $message = 'iPaymu with transaction id: ' . $data['trx_id'];
                    $this->model_checkout_order->addOrderHistory($data['order_id'], 1, $message);
                } elseif ($data['status'] == 'gagal') {
                    $message = 'iPaymu with transaction id: ' . $data['trx_id'];
                    if ($data['ref_no']) {
                        $message .= ' ,ref. number: ' . $data['ref_no'];
                    }
                    $this->model_checkout_order->addOrderHistory($data['order_id'], 10, $message);
                }
    
                $this->response->redirect($this->url->link('checkout/success'));
            }
        }
    }

    // Response early with 200 OK status for Midtrans notification & handle HTTP GET
    public function earlyResponse(){
        if ( $_SERVER['REQUEST_METHOD'] == 'GET' ){
          die('This endpoint should not be opened using browser (HTTP GET). This endpoint is for Midtrans notification URL (HTTP POST)');
          exit();
        }
    
        ob_start();
    
        $input_source = "php://input";
        $raw_notification = json_decode(file_get_contents($input_source), true);
        echo "Notification Received: \n";
        print_r($raw_notification);
        
        header('Connection: close');
        header('Content-Length: '.ob_get_length());
        ob_end_flush();
        ob_flush();
        flush();
    }

    public function payment_notification() {
         // $this->earlyResponse();
        // if ( $_SERVER['REQUEST_METHOD'] == 'GET' ){
        //   die('This endpoint should not be opened using browser (HTTP GET). This endpoint is for Midtrans notification URL (HTTP POST)');
        //   exit();
        // }
        $this->load->model('checkout/order');
        $data = array();
        foreach ($_REQUEST as $key => $value) {
            $data[$key] = $value;
        }

        if (!empty($data['order_id'])) {
            $orderId = $data['order_id'];
        } else {
            $orderId = $data['reference_id'];
        }
        if ($data['status'] == 'berhasil') {
            $message = 'Payment Success - ' . $data['trx_id'];
            $this->model_checkout_order->addOrderHistory($orderId, 15, $message);
        } else if ($data['status'] == 'expired') {
            $message = 'Payment Expired - ' . $data['trx_id'];
            $this->model_checkout_order->addOrderHistory($orderId, 7, $message);
        } else if ($data['status'] == 'pending') {
            $message = 'Payment Pending - ' . $data['trx_id'];
            $this->model_checkout_order->addOrderHistory($orderId, 1, $message);
        } else {
            $message = 'Payment Failed - ' . $data['trx_id'];
            $this->model_checkout_order->addOrderHistory($orderId, 10, $message);
        }

        echo 'received with order ID ' . $orderId;
    }
}
