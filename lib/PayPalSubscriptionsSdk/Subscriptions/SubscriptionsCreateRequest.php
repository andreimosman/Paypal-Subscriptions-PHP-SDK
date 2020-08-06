<?php

namespace PayPalSubscriptionsSdk\Subscriptions;

use PayPalSubscriptionsSdk\Core\HttpRequest;


class SubscriptionsCreateRequest extends HttpRequest
{
    function __construct()
    {
        parent::__construct("/v1/billing/subscriptions", "POST");
        $this->headers["Content-Type"] = "application/json";
    }

}

