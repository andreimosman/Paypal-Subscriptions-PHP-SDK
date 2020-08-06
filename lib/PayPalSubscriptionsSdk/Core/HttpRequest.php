<?php

namespace PayPalSubscriptionsSdk\Core;

class HttpRequest extends \PayPalHttp\HttpRequest {

    public function setData($data) {
        $this->body = $data;
    }

}
