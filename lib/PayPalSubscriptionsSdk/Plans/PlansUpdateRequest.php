<?php

namespace PayPalSubscriptionsSdk\Plans;

use PayPalSubscriptionsSdk\Core\HttpUpdateRequest;

class PlansUpdateRequest extends HttpUpdateRequest
{
    protected $fieldsThatCanBeUpdated = [ 
        "description", 
        "payment_preferences/auto_bill_outstanding", 
        "taxes/percentage", 
        "payment_preferences/payment_failure_threshold",
        "payment_preferences/setup_fee",
        "payment_preferences/setup_fee_failure_action"
    ];

    function __construct($planId)
    {
        parent::__construct("/v1/billing/plans/{plan_id}", "PATCH");
        $this->path = str_replace("{plan_id}", urlencode($planId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }

}

