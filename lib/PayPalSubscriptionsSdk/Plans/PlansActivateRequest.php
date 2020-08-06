<?php

namespace PayPalSubscriptionsSdk\Plans;

use PayPalSubscriptionsSdk\Core\HttpRequest;



class PlansActivateRequest extends HttpRequest
{
    function __construct($planId=null)
    {
        parent::__construct("/v1/billing/plans/{plan_id}/activate", "POST");
        $this->path = str_replace("{plan_id}", urlencode($planId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

}

