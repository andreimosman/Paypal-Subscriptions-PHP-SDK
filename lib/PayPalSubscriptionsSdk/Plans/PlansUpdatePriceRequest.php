<?php

namespace PayPalSubscriptionsSdk\Plans;

use PayPalSubscriptionsSdk\Core\HttpUpdateRequest;

class PlansUpdatePriceRequest extends HttpUpdateRequest
{
    function __construct($planId)
    {
        parent::__construct("/v1/billing/plans/{plan_id}/update-pricing-schemes", "POST");
        $this->path = str_replace("{plan_id}", urlencode($planId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

    public function setData($data) {
        $this->body = $data;
    }

}

