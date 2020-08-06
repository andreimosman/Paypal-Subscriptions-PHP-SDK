<?php

namespace PayPalSubscriptionsSdk\Subscriptions;

use PayPalSubscriptionsSdk\Core\HttpRequest;



class SubscriptionsActivateRequest extends HttpRequest
{
    function __construct($subscriptionId)
    {
        parent::__construct("/v1/billing/subscriptions/{subscription_id}/activate", "POST");
        $this->path = str_replace("{subscription_id}", urlencode($subscriptionId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

}

