<?php

class charts {

  private $name = '';
  private $ver = '';
  private $path = '';
  private $pairs = array();
  private $db = NULL;  

  function __construct($name, $ver, $db, $path, $pairs){
    $this->name = $name;
    $this->ver = $ver;
    $this->path = $path;
    $this->pairs = $pairs;
    $this->db = $db;
  }

  private function createHighchartsJSON($orders){
    $result_array = '';
    $buy_active = array();
    $buy_effective = array();
    $sell_active = array();
    $sell_effective = array();
    if ($orders['status']){
      foreach ($orders['orders'] as $key=>$order){
        $date = $order['time'] * 1000;
        $buy_active['data'][$key] = array($date, $order['buy_active']);
        $buy_effective['data'][$key] = array($date, $order['buy_effective']);
        $sell_active['data'][$key] = array($date, $order['sell_active']);
        $sell_effective['data'][$key] = array($date, $order['sell_effective']);
      }
    }
    $buy_active['type'] = 'area';
    $buy_effective['type'] = 'area';
    $sell_active['type'] = 'area';
    $sell_effective['type'] = 'area';
    $buy_active['name'] = 'Buy active';
    $buy_effective['name'] = 'Buy effective';
    $sell_active['name'] = 'Sell active';
    $sell_effective['name'] = 'Sell effective';
    $result_array[] = $buy_active;
    $result_array[] = $buy_effective;
    $result_array[] = $sell_active;
    $result_array[] = $sell_effective;
    return json_encode($result_array);
  }

  public function show(){
    $result = array();
    $this->db->connect();
    $orders = $this->db->getMarkets(time() - 86400, time(), 0, 3);
    $this->db->close();
    $result['content_type'] = 'Content-Type: text/json; charset=utf-8';
    $result['data'] = $this->createHighchartsJSON($orders);
    return $result;
  }

}

?>
