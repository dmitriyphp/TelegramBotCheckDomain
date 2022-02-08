<?php

require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/classes/DomainChecker.php';
require_once dirname(__FILE__) . '/classes/Curl.php';
require_once dirname(__FILE__) . '/classes/Telegram.php';

$content = file_get_contents("php://input");
$update  = json_decode($content, true);

if ( ! $update) {
    exit;
}

if (isset($update["message"])) {
    $telegram = new Telegram();
    $telegram->accept($update["message"]);
}