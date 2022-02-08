<?php

class Telegram
{
    private $chat_id;

    /**
     * @var Curl
     */
    private $curl;

    /**
     * @var DomainChecker
     */
    private $domain;

    public function __construct()
    {
        $this->curl = new Curl();
        $this->domain = new DomainChecker();
    }

    /**
     * Accept message from Telegram bot
     * @param $message
     */
    public function accept($message)
    {
        $this->chat_id = $message['chat']['id'];

        if (isset($message['text'])) {
            $text = $message['text'];

            if (strpos($text, "/start") === 0) {
                $this->answer(HELLO_TEXT);
            } else {
                $is_domain_exists = $this->domain->check($text);
                $result_message = ERROR_TEXT;
                switch ($is_domain_exists) {
                    case 'success':
                        $result_message = DOMAIN_EXISTS_TEXT;
                        break;
                    case 'fail':
                        $result_message = DOMAIN_NOT_EXISTS_TEXT;
                        break;
                }
                $this->answer($result_message);
            }
        } else {
            $this->answer(ERROR_TEXT);
        }
    }

    /**
     * Answer to client
     * @param $message
     */
    public function answer($message)
    {
        $parameters = array(
            'chat_id' => json_encode($this->chat_id),
            'text' => $message
        );

        $url = API_URL.API_METHOD.'?'.http_build_query($parameters);

        return $this->curl->execute($url);
    }
}