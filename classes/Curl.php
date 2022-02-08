<?php

class Curl
{
    /**
     * Makes CURL request
     * @param $url
     * @return false|mixed
     */
    public function execute($url)
    {
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, CURLOPT_CONNECTTIMEOUT);
        curl_setopt($handle, CURLOPT_TIMEOUT, CURLOPT_TIMEOUT);

        $response = curl_exec($handle);
        if ($response === false) {
            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);

        if ($http_code === 200) {
            $response = json_decode($response, true);
            $response = $response['result'];
        }

        return $response;
    }
}