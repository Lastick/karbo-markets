<?php

class http_client {

  const API_TIMER = 50000;
  const API_TIMEOUT = 5;
  const API_INFO = 'https://forum.karbowanec.com';

  private $app_name = '';
  private $app_ver = '';
  private $http_headers = array();

  function __construct($app_name, $app_ver){
    $this->app_name = $app_name;
    $this->app_ver = $app_ver;
    $this->http_headers[] = 'Content-Type: application/json; charset=utf-8';
    $this->http_headers[] = 'User-Agent: ' . ucfirst(strtolower($this->app_name)) . '/' . $this->app_ver . ' (+' . self::API_INFO . ')';
  }

  public function apiCall($url){
    $result = array();
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $this->http_headers);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, self::API_TIMEOUT);
    usleep(self::API_TIMER);
    $res = curl_exec($ch);
    $result['status'] = false;
    $result['data'] = '';
    if(curl_errno($ch) == 0){
      $result['status'] = true;
      $result['data'] = $res;
    }
    curl_close($ch);
    return $result;
  }

}

?>
