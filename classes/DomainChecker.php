<?php

class DomainChecker {

    /**
     * @param $domain
     */
    public function check($domain) {

        $data = json_decode(file_get_contents(DOMAIN_API_URL . $domain));
        if ($data && isset($data->status)) {
            return $data->status;
        }

        return false;
    }

}