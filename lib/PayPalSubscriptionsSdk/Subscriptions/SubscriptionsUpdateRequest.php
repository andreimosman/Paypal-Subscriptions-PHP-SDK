<?php

namespace PayPalSubscriptionsSdk\Subscriptions;

use PayPalSubscriptionsSdk\Core\HttpUpdateRequest;

class SubscriptionsUpdateRequest extends HttpUpdateRequest
{
    protected $fieldsThatCanBeUpdated = [ 
    ];

    function __construct($subscriptionId)
    {
        parent::__construct("/v1/billing/subscriptions/{subscription_id}", "PATCH");
        $this->path = str_replace("{subscription_id}", urlencode($subscriptionId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

}

