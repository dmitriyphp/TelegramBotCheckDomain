<?php

/**
 * For clients
 * Bot uses Bitexbit system (internal transfers)
 */

define('BOT_NAME', 'bot4');

define('BOT_TOKEN', '5170734919:AAH0nM5duM19DNrAVno8V0rgxMrF6EZN_2M');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');

define('MERCHANT_ID', isset($_GET['merchant_id']) ? $_GET['merchant_id'] : '8eeb4e4194b730f5ac8c0f5ed739c263');
define('CURRENCY', 'USD');

define('KEYBOARDS', array(
    'keyboard' => array(array('Introduction', 'Check fee')),
    'one_time_keyboard' => true,
    'resize_keyboard' => true));

function apiRequestWebhook($method, $parameters) {
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $parameters["method"] = $method;

    $payload = json_encode($parameters);
    header('Content-Type: application/json');
    header('Content-Length: '.strlen($payload));
    echo $payload;

    return true;
}

function exec_curl_request($handle) {
    $response = curl_exec($handle);

    if ($response === false) {
        $errno = curl_errno($handle);
        $error = curl_error($handle);
        error_log("Curl returned error $errno: $error\n");
        curl_close($handle);
        return false;
    }

    $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
    curl_close($handle);

    if ($http_code >= 500) {
        // do not wat to DDOS server if something goes wrong
        sleep(10);
        return false;
    } else if ($http_code != 200) {
        $response = json_decode($response, true);
        error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
        if ($http_code == 401) {
            throw new Exception('Invalid access token provided');
        }
        return false;
    } else {
        $response = json_decode($response, true);
        if (isset($response['description'])) {
            error_log("Request was successful: {$response['description']}\n");
        }
        $response = $response['result'];
    }

    return $response;
}

function apiRequest($method, $parameters) {
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    foreach ($parameters as $key => &$val) {
        // encoding to JSON array parameters, for example reply_markup
        if (!is_numeric($val) && !is_string($val)) {
            $val = json_encode($val);
        }
    }
    $url = API_URL.$method.'?'.http_build_query($parameters);

    $handle = curl_init($url);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);

    return exec_curl_request($handle);
}

function apiRequestJson($method, $parameters) {
    if (!is_string($method)) {
        error_log("Method name must be a string\n");
        return false;
    }

    if (!$parameters) {
        $parameters = array();
    } else if (!is_array($parameters)) {
        error_log("Parameters must be an array\n");
        return false;
    }

    $parameters["method"] = $method;

    $handle = curl_init(API_URL);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

    return exec_curl_request($handle);
}
function processMessage($message) {
    // process incoming message
    //$message_id = $message['message_id'];
    $chat_id = $message['chat']['id'];
    if (isset($message['text'])) {
        // incoming text message
        $text = $message['text'];

        if (strpos($text, "/start") === 0) {
            apiRequestJson("sendMessage", array('chat_id' => $chat_id, "text" => 'Hello', 'reply_markup' => KEYBOARDS));
        } else if ($text === "Hello" || $text === "Hi") {
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Nice to meet you', 'reply_markup' => KEYBOARDS));
        } elseif ($text === 'Introduction') {
            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Coming soon...', 'reply_markup' => KEYBOARDS));
        } else {
            $amount = floatval($text);
            $amount_o = $amount;
            if ($amount <= 0) {
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'Enter amount in ' . CURRENCY . ' you want to get'));
                die;
            }

            $merchant_id = DB::get("SELECT `id` FROM `merchants` WHERE `hash_id` = '%1' LIMIT 1", array(
                MERCHANT_ID
            ));
            $merchant_id = $merchant_id[0]['id'];
            $data = DB::get("SELECT `rate` FROM `merchants__currency_rates` WHERE `merchant_id` = '%1' AND `base_currency` = 'USDT_TRC20' LIMIT 1", array(
                $merchant_id
            ));
            $rate = isset($data[0]['rate']) ? $data[0]['rate'] : false;
            if ($rate === false) {
                apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'There is an error. Could not get rate. Please try again later. Thanks.', 'reply_markup' => KEYBOARDS));
                die;
            }

            $amount /= $rate;
            if ($amount - round($amount) < 0.001) {
                $amount = round($amount);
            } else {
                $amount = ceil($amount);
            }

            apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'You should send ' . $amount . ' USDT to get $' . $amount_o, 'reply_markup' => KEYBOARDS));
        }
    } else {
        apiRequest("sendMessage", array('chat_id' => $chat_id, "text" => 'I understand only text messages'));
    }
}


define('WEBHOOK_URL', 'https://bot.coinsxo.com/call.php');

if (php_sapi_name() == 'cli') {
    // if run from console, set or delete webhook
    apiRequest('setWebhook', array('url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL));
    exit;
}


$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    // receive wrong update, must not happen
    exit;
}

if (isset($update["message"])) {
    processMessage($update["message"]);
}
