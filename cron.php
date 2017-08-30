<?php

header('Content-type: text/plain; charset=utf-8');

require_once('app/core.php');

$app = new markets();
$app->cron();

?>
