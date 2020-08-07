<?php

namespace PayPalSubscriptionsSdk\Catalog;

use PayPalSubscriptionsSdk\Core\HttpUpdateRequest;


class ProductsUpdateRequest extends HttpUpdateRequest
{
    protected $fieldsThatCanBeUpdated = [ "description", "category", "image_url", "home_url" ];

    function __construct($productId)
    {
        parent::__construct("/v1/catalogs/products/{product_id}", "PATCH");
        $this->path = str_replace("{product_id}", urlencode($productId), $this->path);
        $this->headers["Content-Type"] = "application/json";
    }
    
}

