<?php

namespace PayPalSubscriptionsSdk\Subscriptions;

use PayPalSubscriptionsSdk\Core\HttpRequest;



class SubscriptionsGetRequest extends HttpRequest
{
    function __construct($subscriptionId=null)
    {
        parent::__construct("/v1/billing/subscriptions/{subscription_id}?", "GET");
        $this->path = str_replace("{subscription_id}", urlencode($subscriptionId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

}

