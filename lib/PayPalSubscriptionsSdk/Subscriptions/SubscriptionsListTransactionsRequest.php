<?php

namespace PayPalSubscriptionsSdk\Subscriptions;
use PayPalSubscriptionsSdk\Core\HttpRequest;

class SubscriptionsListTransactionsRequest extends HttpRequest
{
    function __construct($subscriptionId,$startTime,$endTime)
    {
        parent::__construct("/v1/billing/subscriptions/{subscription_id}/transactions?start_time={start_time}&end_time={end_time}", "GET");
        $this->path = str_replace("{subscription_id}", urlencode($subscriptionId), $this->path);
        $this->path = str_replace("{start_time}", urlencode($startTime), $this->path);
        $this->path = str_replace("{end_time}", urlencode($endTime), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

}

