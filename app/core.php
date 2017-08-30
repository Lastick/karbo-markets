<?php

define('PARSERS', 'app/parsers');
define('LOGOS', 'app/images/services');
define('CACHE', 'cache');
define('CONFIG', 'config.json');

require_once('libs/db.php');
require_once('libs/http_client.php');
require_once('libs/tickers.php');


class markets {

  private $config = NULL;
  private $db = NULL;
  private $tickers = NULL;

  function __construct(){
    $this->config = json_decode(file_get_contents(CONFIG), true);
    $this->db = new db($this->config['db']['host'], $this->config['db']['name'], $this->config['db']['user'], $this->config['db']['pass']);
    $this->tickers = new tickers($this->config['name'], $this->config['ver'], CACHE . '/tickers.json', $this->config['pairs']);
  }

  private function getExchangeById($id){
    $result['status'] = false;
    $result['param']['name'] = '';
    $result['param']['url'] = '';
    $result['param']['logo'] = '';
    $result['param']['api'] = '';
    $result['param']['pairs'] = array();
    $result['param']['parser'] = '';
    $result['param']['id'] = '';
    $result['param']['active'] = false;
    $exchanges = $this->config['exchanges'];
    foreach ($exchanges as $exchange){
      if ($exchange['id'] == $id){
        $result['status'] = true;
        $result['param']['name'] = $exchange['name'];
        $result['param']['url'] = $exchange['url'];
        $result['param']['logo'] = $exchange['logo'];
        $result['param']['api'] = $exchange['api'];
        $result['param']['pairs'] = $exchange['pairs'];
        $result['param']['parser'] = $exchange['parser'];
        $result['param']['id'] = $exchange['id'];
        $result['param']['active'] = $exchange['active'];
        break;
      }
    }
    return $result;
  }

  private function getPairsId(){
    $pairs = $this->config['pairs'];
    foreach ($pairs as $pair){
      $pairs_id[] = $pair['id'];
    }
    return $pairs_id;
  }

  private function getPairById($id){
    $result['status'] = false;
    $result['param']['text'] = '';
    $result['param']['id'] = '';
    $result['param']['default'] = '';
    $pairs = $this->config['pairs'];
    foreach ($pairs as $pair){
      if ($pair['id'] == $id){
        $result['status'] = true;
        $result['param']['text'] = $pair['text'];
        $result['param']['id'] = $pair['id'];
        $result['param']['default'] = $pair['default'];
        break;
      }
    }
    return $result;
  }

  private function SortOrders($orders){
    $sell_orders = $orders['orders']['sell'];
    $buy_orders = $orders['orders']['buy'];
    foreach ($sell_orders as $sell_order){
      $sell_array[] = $sell_order['price'];
    }
    sort($sell_array, SORT_NUMERIC);
    foreach ($sell_array as $key=>$sell){
      foreach ($sell_orders as $sell_order){
        if ($sell == $sell_order['price']){
          $sell_orders_res[$key]['price'] = $sell;
          $sell_orders_res[$key]['volume'] = $sell_order['volume'];
          break;
        }
      }
    }
    foreach ($buy_orders as $buy_order){
      $buy_array[] = $buy_order['price'];
    }
    rsort($buy_array, SORT_NUMERIC);
    foreach ($buy_array as $key=>$buy){
      foreach ($buy_orders as $buy_order){
        if ($buy == $buy_order['price']){
          $buy_orders_res[$key]['price'] = $buy;
          $buy_orders_res[$key]['volume'] = $buy_order['volume'];
          break;
        }
      }
    }
    $orders_res['status'] = true;
    $orders_res['orders']['sell'] = $sell_orders_res;
    $orders_res['orders']['buy'] = $buy_orders_res;
    return $orders_res; 
  }

  private function EffectiveCalc($orders, $mode){
    $active_price = $orders[0]['price'];
    $active_volume = $orders[0]['volume'];
    $effective_offset = $active_price / 100 * $this->config['orders']['effective'];
    $volume_sum = 0;
    $price_sum = 0;
    if ($mode){
      foreach ($orders as $order){
        if ($order['price'] > $active_price - $effective_offset){
          $volume_sum = $volume_sum + $order['volume'];
          $price_sum = $price_sum + $order['price'];
        }
      }
      $effective_price = $active_price - $effective_offset + $effective_offset * $active_volume / $active_price / $volume_sum * $price_sum;
      } else {
      foreach ($orders as $order){
        if ($order['price'] < $active_price + $effective_offset){
          $volume_sum = $volume_sum + $order['volume'];
          $price_sum = $price_sum + $order['price'];
        }
      }
      $effective_price = $active_price + $effective_offset - $effective_offset * $active_volume / $active_price / $volume_sum * $price_sum;
    }
    //echo $effective_price . "\n";
    return $effective_price;
  }

  private function getOrders($service_id, $pair_id){
    $exchange = $this->getExchangeById($service_id);
    $pair = $this->getPairById($pair_id);
    require_once(PARSERS . '/' . $exchange['param']['parser'] . '.php');
    $ParserClassName = str_replace('-', '_', $exchange['param']['parser']);
    $PairText = $pair['param']['text'];
    $parser = new $ParserClassName($exchange['param']['api'], $PairText, $this->config['name'], $this->config['ver']);
    $orders = $parser->getOrders();
    return $orders;
  }

  private function update(){
    $exchanges = $this->config['exchanges'];
    foreach ($exchanges as $exchange){
      $exchange_id = $exchange['id'];
      $pairs = $exchange['pairs'];
      foreach ($pairs as $pair_id){
        $orders = $this->getOrders($exchange_id, $pair_id);
        if ($orders['status']){
          $sort_orders = $this->SortOrders($orders);
          $data['service_id'] = $exchange_id + 1;
          $data['pair_id'] = $pair_id;
          $data['buy_active'] = $sort_orders['orders']['buy'][0]['price'];
          $data['buy_effective'] = $this->EffectiveCalc($sort_orders['orders']['buy'], true);
          $data['sell_active'] = $sort_orders['orders']['sell'][0]['price'];
          $data['sell_effective'] = $this->EffectiveCalc($sort_orders['orders']['sell'], false);
          $this->db->insert($data);
        }
      }
    }
  }

  private function MarketsUpdate(){
    $pairs_id = $this->getPairsId();
    $orders = $this->db->getMarkets(time() - $this->config['orders']['interval'], time());
    if ($orders['status']){
      $data_compile = array();
      foreach ($pairs_id as $pair_id){
        $buy_active_array = array();
        $buy_effective_array = array();
        $sell_active_array = array();
        $sell_effective_array = array();
        $buy_active = 0;
        $buy_effective = 0;
        $sell_active = 0;
        $sell_effective = 0;
        $n = 0;
        foreach ($orders['orders'] as $order){
          if ($order['pair_id'] == $pair_id and $order['service_id'] != 0){
            $buy_active_array[] = $order['buy_active'];
            $buy_effective_array[] = $order['buy_effective'];
            $sell_active_array[] = $order['sell_active'];
            $sell_effective_array[] = $order['sell_effective'];
            $n++;
          }
        }
        $data_ticker = array();
        if ($n > 0){
          $buy_active = array_sum($buy_active_array) / count($buy_active_array);
          $buy_effective = array_sum($buy_effective_array) / count($buy_effective_array);
          $sell_active = array_sum($sell_active_array) / count($sell_active_array);
          $sell_effective = array_sum($sell_effective_array) / count($sell_effective_array);
          $data['service_id'] = 0;
          $data['pair_id'] = $pair_id;
          $data['buy_active'] = $buy_active;
          $data['buy_effective'] = $buy_effective;
          $data['sell_active'] = $sell_active;
          $data['sell_effective'] = $sell_effective;
          $this->db->insert($data);
          $data_ticker['pair_id'] = $pair_id;
          $data_ticker['buy_active'] = $buy_active;
          $data_ticker['buy_effective'] = $buy_effective;
          $data_ticker['sell_active'] = $sell_active;
          $data_ticker['sell_effective'] = $sell_effective;
        }
        $data_compile[] = $data_ticker;
      }
      $this->tickers->compile($data_compile);
    }
  }

  public function cron(){
    sleep(5);
    $this->db->connect();
    $this->update();
    $this->MarketsUpdate();
    //$this->db->install();
    $this->db->close();
  }

  public function api(){
    $action = '';
    $data = array();
    $data['content_type'] = 'Content-Type: text/plain; charset=utf-8';
    $data['result'] = 'The action is not defined. Request execution failed.';
    if (isset($_GET['action'])) $action = $_GET['action'];
    switch ($action){
      case '':
      case 'tickers':
      $action_result = $this->tickers->show();
      $data['content_type'] = $action_result['content_type'];
      $data['result'] = $action_result['data'];;
      break;
    case 'FLY':
      //echo "FLY";
      break;
  }
    //$this->db->connect();
    //$this->db->close();
    header('Access-Control-Allow-Origin: *');
    header($data['content_type']);
    echo $data['result'];
  }
}


?>
