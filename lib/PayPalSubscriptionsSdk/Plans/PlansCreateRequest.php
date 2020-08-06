<?php

namespace PayPalSubscriptionsSdk\Plans;

use PayPalSubscriptionsSdk\Core\HttpRequest;


class PlansCreateRequest extends HttpRequest
{
    function __construct()
    {
        parent::__construct("/v1/billing/plans", "POST");
        $this->headers["Content-Type"] = "application/json";
    }

}

