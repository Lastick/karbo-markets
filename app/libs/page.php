<?php

class page {

  private $name = '';
  private $ver = '';
  private $path = '';
  private $db = NULL;  

  function __construct($name, $ver, $db, $path){
    $this->name = $name;
    $this->ver = $ver;
    $this->path = $path;
    $this->db = $db;
  }

  private function stat($tickers){
    $result = '';
    $stat_array = array();
    $stat_array['name'] = $this->name;
    $stat_array['ver'] = $this->ver;
    $stat_array['status'] = false;
    $stat_array['size_markets'] = array();
    $stat_array['tickers'] = $tickers;
    $this->db->connect();
    $SizeMarkets = $this->db->getSizeMarkets();
    $this->db->close();
    if ($SizeMarkets['status']){
      $stat_array['status'] = true;
      $stat_array['size_markets']['first'] = $SizeMarkets['first'] * 1000;
      $stat_array['size_markets']['last'] = $SizeMarkets['last'] * 1000;
    }
    return json_encode($stat_array);
  }

  public function show($data){
    $result = array();
    $tickers = array();
    if ($data['status']){
      $tickers = $data['tickers'];
      $tickers['status'] = true;
      } else {
      $tickers['status'] = false;
    }
    $result['content_type'] = 'Content-Type: text/plain; charset=utf-8';
    $result['data'] = $this->stat($tickers);
    return $result;
  }

}

?>
