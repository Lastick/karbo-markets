<?php

class tickers {

  private $name = '';
  private $ver = '';
  private $path = '';
  private $pairs = array();
  

  function __construct($name, $ver, $path, $pairs){
    $this->name = $name;
    $this->ver = $ver;
    $this->path = $path;
    $this->pairs = $pairs;
  }

  public function compile($data){
    $tickers = array();
    $tickers['time'] = time();
    foreach ($this->pairs as $key=>$pair){
      $pair_id = $pair['id'];
      $pair_name = $pair['text'];
      $ticker_status = false;
      $buy_active = 0;
      $buy_effective = 0;
      $sell_active = 0;
      $sell_effective = 0;
      foreach ($data as $ticker){
        if (count($ticker) > 0){
          if ($ticker['pair_id'] == $pair_id){
            $buy_active = $ticker['buy_active'];
            $buy_effective = $ticker['buy_effective'];
            $sell_active = $ticker['sell_active'];
            $sell_effective = $ticker['sell_effective'];
            $ticker_status = true;
            break;
          }
        }
      }
      $tickers['pairs'][$key]['id'] = $pair_id;
      $tickers['pairs'][$key]['name'] = $pair_name;
      $tickers['pairs'][$key]['status'] = $ticker_status;
      $tickers['pairs'][$key]['buy_active'] = $buy_active;
      $tickers['pairs'][$key]['buy_effective'] = $buy_effective;
      $tickers['pairs'][$key]['sell_active'] = $sell_active;
      $tickers['pairs'][$key]['sell_effective'] = $sell_effective;
    }
    file_put_contents($this->path, json_encode($tickers));
  }

  public function getTickers(){
    $result = array();
    $result['status'] = false;
    $result['tickers'] = array();
    if (file_exists($this->path)){
      $result['status'] = true;
      $data = file_get_contents($this->path);
      $result['tickers'] = json_decode($data, true);
    }
    return $result;
  } 

  public function genTickers($data){
    $build = "";
    $build  = "{\n";
    $build .= "  \"name\": \"" . $this->name . "\",\n";
    $build .= "  \"version\": \"" . $this->ver . "\",\n";
    $build .= "  \"status\": true,\n";
    $build .= "  \"tickers\": {\n";
    $build .= "    \"time\": " . $data['time'] . ",\n";
    $build .= "    \"pairs\": [\n";
    foreach ($data['pairs'] as $key=>$pair){
      $sub = "";
      if ($key < count($this->pairs) - 1) $sub = ",\n";
      $build .= "      {\n";
      $build .= "        \"id\": " . $pair['id'] . ",\n";
      $build .= "        \"name\": \"" . $pair['name'] . "\",\n";
      $build .= "        \"buy_active\": " . $pair['buy_active'] . ",\n";
      $build .= "        \"buy_effective\": " . $pair['buy_effective'] . ",\n";
      $build .= "        \"sell_active\": " . $pair['sell_active'] . ",\n";
      $build .= "        \"sell_effective\": " . $pair['sell_effective'] . ",\n";
      $build .= "        \"status\": " . var_export($pair['status'], true) . "\n";
      $build .= "      }" . $sub . "\n";
    }
    $build .= "    ]\n";
    $build .= "  }\n";
    $build .= "}";
    return $build;
  }

  private function genError($text){
    $build = "";
    $build  = "{\n";
    $build .= "  \"name\": \"" . $this->name . "\",\n";
    $build .= "  \"version\": \"" . $this->ver . "\",\n";
    $build .= "  \"status\": false,\n";
    $build .= "  \"info\": \"" . $text . "\"\n";
    $build .= "}";
    return $build;
  }

  public function show(){
    $result = array();
    $tickers = $this->getTickers();
    if ($tickers['status']){
      $data = $this->genTickers($tickers['tickers']);
      } else {
      $data = $this->genError("Error: no data to display");
    }
    $result['content_type'] = 'Content-Type: text/plain; charset=utf-8';
    $result['data'] = $data;
    return $result;
  }

}

?>
