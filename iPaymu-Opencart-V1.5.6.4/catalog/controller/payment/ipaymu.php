<?php

class ControllerPaymentIpaymu extends Controller {

    protected function index() {
        $this->language->load('payment/ipaymu');
        $this->data['action'] = $this->url->link('payment/ipaymu/send');

        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $this->data['ap_merchant'] = $this->config->get('ipaymu_merchant');

        $this->data['url_web'] = $this->url;
        $this->data['ap_security'] = $this->config->get('ipaymu_security');
        $this->data['ap_paypal'] = $this->config->get('ipaymu_paypal');
        $this->data['ap_ipaymu_rate'] = $this->config->get('ipaymu_rate');
        $this->data['ap_inv_paypal'] = $this->config->get('ipaymu_inv_paypal');
        $this->data['ap_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        $this->data['ap_currency'] = $order_info['currency_code'];
        $this->data['ap_purchasetype'] = 'Item';
        $this->data['ap_itemname'] = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];
        $this->data['ap_itemcode'] = $this->session->data['order_id'];

        if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/ipaymu.tpl')) {
            $this->template = $this->config->get('config_template') . '/template/payment/ipaymu.tpl';
        } else {
            $this->template = 'default/template/payment/ipaymu.tpl';
        }

        if($this->currency->has('IDR')){
            $this->data['button_confirm'] = $this->language->get('button_confirm');
            if($order_info['currency_code'] != 'IDR'){
                $this->data['msg'] = $this->language->get('currency_convert');
            }
        }else{
            $this->data['msg'] =  $this->language->get('currency_support');
        }

        $this->render();
    }

    private function simpleXor($string, $password) {
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

    public function send() {
        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $this->data['ap_merchant'] = $this->config->get('ipaymu_merchant');
        $this->data['url_web'] = $this->url->link('common/home');
        $this->data['ap_security'] = $this->config->get('ipaymu_security');
        $this->data['ap_paypal'] = $this->config->get('ipaymu_paypal');
        $this->data['ap_ipaymu_rate'] = $this->config->get('ipaymu_rate');
        $this->data['ap_inv_paypal'] = $this->config->get('ipaymu_inv_paypal');
        $this->data['ap_amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        $this->data['ap_currency'] = $order_info['currency_code'];
        $this->data['ap_purchasetype'] = 'Item';
        $this->data['ap_itemname'] = $this->config->get('config_name') . ' - #' . $this->session->data['order_id'];
        $this->data['ap_itemcode'] = $this->session->data['order_id'];

        $security_code = $this->data['ap_security'] . $this->session->data['order_id'];

        $data = array();
        $data['orderid'] = $this->session->data['order_id'];
        $def_curr = $this->config->get('config_currency');
        $data['jumlah'] = $def_curr == 'IDR' ? $order_info['total'] : $this->currency->convert($order_info['total'], $order_info['currency_code'], 'IDR');


        $crypt_data = array();

        foreach ($data as $key => $value) {
            $crypt_data[] = $key . '=' . $value;
        }

        $this->data['crypt'] = base64_encode($this->simpleXor(utf8_decode(implode('&', $crypt_data)), $security_code));

        $this->data['ap_returnurl'] = str_replace('&amp;', '&', $this->url->link('payment/ipaymu/success', 'order_id=' . $this->session->data['order_id'] . '&crypt=' . $this->data['crypt']));
        $this->data['ap_notifyurl'] = str_replace('&amp;', '&', $this->url->link('checkout/checkout'));

        $this->data['ap_cancelurl'] = $this->url->link('checkout/checkout', '', 'SSL');

        $_SESSION['crypt'] = $this->data['crypt'];

        $url = 'https://my.ipaymu.com/payment.htm';

        // Prepare Parameters
        $pprate = isset($this->data['ap_ipaymu_rate']) && !empty($this->data['ap_ipaymu_rate']) ? $this->data['ap_ipaymu_rate'] : 1;
        $params = array(
            'key' => '' . $this->data['ap_security'] . '', // API Key Merchant / Penjual
            'action' => 'payment',
            'product' => 'Order #' . $this->data['ap_itemcode'] . '',
            'price' => '' . $data['jumlah'] . '', // Total Harga
            'quantity' => 1,
            'comments' => 'Transaksi Pembelian di ' . $_SERVER["SERVER_NAME"] . '', // Optional           
            'ureturn' => '' . $this->data['ap_returnurl'] . '',
            'unotify' => '' . $this->data['ap_notifyurl'] . '',
            'ucancel' => '' . $this->data['ap_cancelurl'] . '',
            /* Parameter untuk pembayaran lain menggunakan PayPal 
             * ----------------------------------------------- */
            'invoice_number' => uniqid($this->data['ap_inv_paypal']), // Optional
            'paypal_email' => $this->data['ap_paypal'],
            'paypal_price' => round($this->data['ap_amount'] / $this->data['ap_ipaymu_rate'], 2), // Total harga dalam kurs USD
            /* ----------------------------------------------- */
            'format' => 'json' // Format: xml / json. Default: xml 
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

            if (isset($result['url']))
                header('location: ' . $result['url']);
            else {
                echo "Request Error " . $result['Status'] . ": " . $result['Keterangan'];
            }
        }
        //close connection
        curl_close($ch);
    }

    public function success() {
        $datane = array();
        foreach ($_REQUEST as $key => $value) {
            $datane[$key] = $value;
        }

        if (isset($_SESSION['crypt'])) {
            if (isset($datane['crypt']) && ($datane['crypt'] == $_SESSION['crypt'])) {
                unset($_SESSION['crypt']);
                $this->load->model('checkout/order');

                $this->model_checkout_order->confirm($datane['order_id'], 1);

                if($datane['status'] == 'berhasil') {
                    $message = 'iPaymu with transaction id: '.$datane['trx_id'];
                    if($datane['ref_no']) {
                        $message .= ' ,ref. number: '.$datane['ref_no'];
                    }
                    $this->model_checkout_order->update($datane['order_id'], 15, $message);
                } elseif ($datane['status'] == 'pending'){
                    $message = 'Non Member iPaymu with transaction id: '.$datane['trx_id'];
                    $this->model_checkout_order->update($datane['order_id'], 1, $message);
                } elseif ($datane['status'] == 'gagal'){
                    $message = 'iPaymu with transaction id: '.$datane['trx_id'];
                    if($datane['ref_no']) {
                        $message .= ' ,ref. number: '.$datane['ref_no'];
                    }
                    $this->model_checkout_order->update($datane['order_id'], 10, $message);
                }

                $this->redirect($this->url->link('checkout/success'));
            }
        }
    }

}

?>