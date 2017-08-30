<?php

class db {

  private $host = '';
  private $name = '';
  private $user = '';
  private $pass = '';
  private $link = NULL;

  function __construct($host, $name, $user, $pass){
    $this->host = $host;
    $this->name = $name;
    $this->user = $user;
    $this->pass = $pass;
  }

  private function num_format($num){
    $res = number_format(round($num, 6), 6, '.', '');
    return $res;
  }

  public function connect(){
    $this->link = mysqli_connect($this->host, $this->user, $this->pass, $this->name);
    if ($this->link === false){
      echo 'Error: Unable to connect to MySQL!';
      exit;
    }
  }

  public function install(){
    $query = NULL;
    $sql_q = "";
    $sql_q  = "CREATE TABLE `markets` (";
    $sql_q .= " `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,";
    $sql_q .= " `service_id` TINYINT(2) UNSIGNED NOT NULL,";
    $sql_q .= " `pair_id` TINYINT(2) UNSIGNED NOT NULL,";
    $sql_q .= " `buy_active` DECIMAL(12, 6) UNSIGNED NOT NULL,";
    $sql_q .= " `buy_effective` DECIMAL(12, 6) UNSIGNED NOT NULL,";
    $sql_q .= " `sell_active` DECIMAL(12, 6) UNSIGNED NOT NULL,";
    $sql_q .= " `sell_effective` DECIMAL(12, 6) UNSIGNED NOT NULL,";
    $sql_q .= " `time` TIMESTAMP(14) NOT NULL";
    $sql_q .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
    mysqli_query($this->link, $sql_q);
  }

  public function insert($data){
    $query = NULL;
    $sql_q = "";
    $sql_q  = "INSERT INTO `markets` (`service_id`, `pair_id`, `buy_active`, `buy_effective`, `sell_active`, `sell_effective`)";
    $sql_q .= " VALUES";
    $sql_q .= " (" . $data['service_id'] . ", " . $data['pair_id'] . ", ";
    $sql_q .= $this->num_format($data['buy_active']) . ", " . $this->num_format($data['buy_effective']) . ", ";
    $sql_q .= $this->num_format($data['sell_active']) . ", " . $this->num_format($data['sell_effective']) . ")";
    mysqli_query($this->link, $sql_q);
  }

  public function getMarkets($time_min, $time_max, $service_id = NULL, $pair_id = NULL){
    $query = NULL;
    $sql_q = "";
    $sql_q  = "SELECT * FROM `markets` WHERE";
    $sql_q .= " (`time` > '" . date('Y-m-d H:i:s', $time_min) . "' AND `time` < '" . date('Y-m-d H:i:s', $time_max) . "')";
    $query = mysqli_query($this->link, $sql_q);
    $result = array();
    $result['status'] = false;
    $result['orders'] = array();
    while (true){
      $row = mysqli_fetch_assoc($query);
      if (is_array($row)){
        if (isset($row['buy_active'])) $row['buy_active'] = floatval($row['buy_active']);
        if (isset($row['buy_effective'])) $row['buy_effective'] = floatval($row['buy_effective']);
        if (isset($row['buy_active'])) $row['sell_active'] = floatval($row['sell_active']);
        if (isset($row['buy_active'])) $row['sell_effective'] = floatval($row['sell_effective']);
        if (isset($row['time'])) $row['time'] = strtotime($row['time']);
        $result['orders'][] = $row;
        } else {
        break;
      }
    }
    mysqli_free_result($query);
    if (count($result['orders']) > 0) $result['status'] = true;
    return $result;
  }

  public function close(){
    mysqli_close($this->link);
  }

}

?>
