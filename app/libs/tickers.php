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
    $build = "";
    $build  = "{\n";
    $build .= "  \"name\": \"" . $this->name . "\",\n";
    $build .= "  \"version\": \"" . $this->ver . "\",\n";
    $build .= "  \"status\": true,\n";
    $build .= "  \"tickers\": {\n";
    $build .= "    \"time\": " . time() . ",\n";
    $build .= "    \"pairs\": [\n";
    foreach ($this->pairs as $key=>$pair){
      $pair_id = $pair['id'];
      $pair_name = $pair['text'];
      $ticker_status = false;
      $buy_active = 0;
      $buy_effective = 0;
      $sell_active = 0;
      $sell_effective = 0;
      $sub = "";
      if ($key < count($this->pairs) - 1) $sub = ",\n";
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
      $build .= "      {\n";
      $build .= "        \"id\": " . $pair_id . ",\n";
      $build .= "        \"name\": \"" . $pair_name . "\",\n";
      $build .= "        \"buy_active\": " . $buy_active . ",\n";
      $build .= "        \"buy_effective\": " . $buy_effective . ",\n";
      $build .= "        \"sell_active\": " . $sell_active . ",\n";
      $build .= "        \"sell_effective\": " . $sell_effective . ",\n";
      $build .= "        \"status\": " . var_export($ticker_status, true) . "\n";
      $build .= "      }" . $sub . "\n";
    }
    $build .= "    ]\n";
    $build .= "  }\n";
    $build .= "}";
    file_put_contents($this->path, $build);
  }

  private function err_build($text){
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
    if (file_exists($this->path)){
      $data = file_get_contents($this->path);
      } else {
      $data = $this->err_build("Error: no data to display");
    }
    $result['content_type'] = 'Content-Type: text/plain; charset=utf-8';
    $result['data'] = $data;
    return $result;
  }

}

?>
