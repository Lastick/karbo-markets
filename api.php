<?php

ini_set('memory_limit', '256M');

require_once('app/core.php');

$app = new markets();
$app->api();

?>
