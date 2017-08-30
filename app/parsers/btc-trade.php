<?php

class btc_trade {

  private $api = '';
  private $app_name = '';
  private $app_ver = '';
  private $pair_text = '';

  function __construct($api, $pair_text, $app_name, $app_ver){
    $this->api = $api;
    $this->pair_text = $pair_text;
    $this->app_name = $app_name;
    $this->app_ver = $app_ver;
  }

  private function url_pair($pair_text){
    $url_pair = '';
    switch ($pair_text){
      case 'KRB/UAH':
        $url_pair = 'krb_uah';
        break;
      default:
        $url_pair = '';
    }
    return $url_pair;
  }

  private function loader($api){
    $result = array();
    $result['status'] = false;
    $result['data'] = array();
    $http = new http_client($this->app_name, $this->app_ver);
    $res = $http->apiCall($api);
    if ($res['status']){
      $res_array = json_decode($res['data'], true);
      if ($res_array != NULL){
        if (isset($res_array['list'])){
          $orders_array = $res_array['list'];
          if (count($orders_array) > 0){
            $result['status'] = true;
            foreach ($orders_array as $key=>$order){
              $result['data'][$key]['price'] = floatval($order['price']);
              $result['data'][$key]['volume'] = floatval($order['currency_trade']);
            }
          }
        }
      }
    }
    return $result;
  }

  public function getOrders(){
    $result = array();
    $result['status'] = false;
    $result['orders']['sell'] = array();
    $result['orders']['buy'] = array();
    $sell_orders = $this->loader($this->api . 'trades/sell/' . $this->url_pair($this->pair_text));
    $buy_orders = $this->loader($this->api . 'trades/buy/' . $this->url_pair($this->pair_text));
    if ($sell_orders['status'] and $buy_orders['status']){
      $result['status'] = true;
      $result['orders']['sell'] = $sell_orders['data'];
      $result['orders']['buy'] = $buy_orders['data'];
    }
    return $result;
  }

}
