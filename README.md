# REST API SDK for PHP V2 extension to support full Subscription functions

### AS DESCRIBED AT https://developer.paypal.com/docs/api/subscriptions/v1/

## Prerequisites

PHP 5.6 and above
https://github.com/paypal/Checkout-PHP-SDK 

An environment which supports TLS 1.2 (see the TLS-update site for more information)

# Using Composer

composer require andreimosman/paypal-subscriptions-sdk

## The SuperSimplePaypalAbstraction

Currently it only abstracts products, plans and subscriptions functions. Sample code:

```php
require __DIR__ . '/vendor/autoload.php';

use PayPalSubscriptionsSdk\SuperSimplePayPal;

$clientId = "<<PAYPAL-CLIENT-ID>>";
$clientSecret = "<<PAYPAL-CLIENT-SECRET>>";

$environment = 'sandbox'; // Or production

$payPal = new SuperSimplePayPal($clientId,$clientSecret, $environment);

//
// Sample codes:
//

$product = $payPal->createProduct("Another Jedi Product","DIGITAL","ONLINE_GAMING","Simple description");
print_r($product);

$productId = $product->id;

$payPal->updateProduct($productId,"ONLINE_GAMING","What a nice product!","http://mysite.com/logo.png");

$product = $payPal->getProduct($productId); // With full details
print_r($product);

$products = $paypal->listProducts(); // With less details
print_r($products);


// Create a monthly plan
$plan = $payPal->createPlan($productId,"Jedi Master Plan","The best jedi plan on the market","9.98");
print_r($plan);

$planId = $plan->id;

$payPal->updatePlan($planId,
    "Another Description", // Description
    true,   // Auto Bill Outstanding
    false,  // Payment Failure Treshold
);

$plan = $payPal->getPlan($planId);
print_r($plan);

$payPal->updatePrice($planId,"11.9");

$plans = $payPal->listPlans();
print_r($plans);

// also ...
$payPal->deactivatePlan($planId);
$payPal->activatePlan($planId);





$applicationContext = [
    "brand_name" => "My Super Company",
    "return_url" => "http://mysite.com/itworked",
    "cancel_url" => "http://mysql.com/cancel",
];
$subscription = $payPal->createSubscription($planId,$applicationContext);
print_r($subscription);

$subscriptionId = $subscriptionId;


$applicationContext = [
    "brand_name" => "Watta Brand",
    "locale" => "en-US",
    "shipping_preference" => "SET_PROVIDED_ADDRESS",
    "payment_method" => [
        "payer_selected" => "PAYPAL",
        "payee_preferred" => "IMMEDIATE_PAYMENT_REQUIRED"
    ],
    "return_url" => "https://example.com/returnUrl",
    "cancel_url" => "https://example.com/cancelUrl"
];

$shippingAmount = [
    "currency_code" => "USD",
    "value" => "10.00"
];

$shippingAddress = [
    "name" => [
        "full_name" => "John Doe"
    ],
    "address" => [
        "address_line_1" => "2211 N First Street",
        "address_line_2" => "Building 17",
        "admin_area_2" => "San Jose",
        "admin_area_1" => "CA",
        "postal_code" => "95131",
        "country_code" => "US"
    ]
];

$payPal->updateQuantitiesInSubscription($subscriptionId,$planId,$applicationContext,$shippingAmount,$shippingAddress);

$subscription = $payPal->getSubscription($subscriptionId);
print_r($subscription);

// ... and also ...
$payPal->captureAuthorizedPaymentOnSubscription();
$payPal->activateSubscription($subscriptionId);
$payPal->cancelSubscription($subscriptionId);
$payPal->suspendSubscription($subscriptionId);


```


## Using the same way as PayPalCheckoutSdk
Use PayPalCheckoutSdk as you normally do.


```php
require __DIR__ . '/vendor/autoload.php';
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;



// Creating an environment
$clientId = "<<PAYPAL-CLIENT-ID>>";
$clientSecret = "<<PAYPAL-CLIENT-SECRET>>";

$environment = new SandboxEnvironment($clientId, $clientSecret);
$client = new PayPalHttpClient($environment);
```

### Creating products

```php
use PayPalSubscriptionsSdk\Catalog\ProductsCreateRequest;

$request = new ProductsCreateRequest();
$request->setData([
    "name" => "Test Course 01",
    "description" => "Product created just for testing purposes",
    "type" => "DIGITAL",
    "category" => "EDUCATIONAL_AND_TEXTBOOKS",
    "image_url" => "https://yoursite.com/logo.png",
    "home_url" => "https://yoursite.com"
]);

try {
    // Call API with your client and get a response for your call
    $response = $client->execute($request);
    
    // If call returns body in response, you can get the deserialized version from the result attribute of the response
    print_r($response);
}catch (HttpException $ex) {
    echo $ex->statusCode;
    print_r($ex->getMessage());
}
```

### Creating a Plan

```php
use PayPalSubscriptionsSdk\Plans\PlansCreateRequest;

$productId = "PROD-9999999999999999L"; // <-- you get this on creation

$request = new PlansCreateRequest();
$request->setData([
    "product_id" => $productId,
    "name" => "Super Nice Plan",
    "description" => "My 1st attempt to create a plan",
    "status" => "Active",
    "billing_cycles" => [
        [
            "frequency" => [
                "interval_unit" => "MONTH",
                "interval_count" => 1
            ],
            "tenure_type" => "REGULAR",
            "sequence" => 1,
            "total_cycles" => 24,
            "pricing_scheme" => [
                "fixed_price" => [
                    "value" => "39",
                    "currency_code" => "USD"
                ]
            ],
        ]
    ],
    "payment_preferences" => [
        "auto_bill_outstanding" => true,
    ]
]);

try {
    // Call API with your client and get a response for your call
    $response = $client->execute($request);
    
    // If call returns body in response, you can get the deserialized version from the result attribute of the response
    print_r($response);
}catch (HttpException $ex) {
    echo $ex->statusCode;
    print_r($ex->getMessage());
}
```

### Finally the Subscription

```php
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsCreateRequest;

$request = new SubscriptionsCreateRequest();
$request->setData([
    "plan_id" => $planId,

]);

// Same as everything
try {
    // Call API with your client and get a response for your call
    $response = $client->execute($request);
    
    // If call returns body in response, you can get the deserialized version from the result attribute of the response
    print_r($response);
}catch (HttpException $ex) {
    echo $ex->statusCode;
    print_r($ex->getMessage());
}

```

## What else?

```php
//
// Get Products from Catalog
//

use PayPalSubscriptionsSdk\Catalog\ProductsGetRequest;

$request = new ProductsGetRequest(); // <-- To list all products
$request = new ProductsGetRequest($productId); // <-- Single product full data

//
// Update Product
//

use PayPalSubscriptionsSdk\Catalog\ProductsUpdateRequest;

$productId = "PROD-9999999999999999L";

$newData = [
    "name" => "Attempt to change name", // Don't work. Read paypal documentation
    "description" => "Product created just for testing purposes - testing =)",
    "category" => "ANIMATION",
    "image_url" => "https://othersite.com/images/otherimage.png",
    "home_url" => "https://othersite.com/#"
];

$request = new ProductsUpdateRequest($productId);
$request->setData($newData);

//
// Get Plans
//

use PayPalSubscriptionsSdk\Plans\PlansGetRequest;

$request = new PlansGetRequest();
$request = new PlansGetRequest($planId);    // You got the idea


//
// Update the Plan
//

use PayPalSubscriptionsSdk\Plans\PlansUpdateRequest;

$request = new PlansUpdateRequest($planId);
$request->setData($planData);


//
// Activate and Deactivate
//

use PayPalSubscriptionsSdk\Plans\PlansActivateRequest;
use PayPalSubscriptionsSdk\Plans\PlansDeactivateRequest;

$request = new PlansActivateRequest($planId);
$request = new PlansDeactivateRequest($planId);


//
// Update Price of the Plan
//

use PayPalSubscriptionsSdk\Plans\PlansUpdatePriceRequest;

$priceData = [
    "pricing_schemes" => [
        [
            "billing_cycle_sequence" => 1,
            "pricing_scheme" => [
                "fixed_price" => [
                    "value" => "50",
                    "currency_code" => "USD"
                ]
            ]
        ]
    ]
];

$request = new PlansUpdatePriceRequest($planId);
$request->setData($priceData);

//
// Subscriptions - Get
//

use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsGetRequest;

$request = new SubscriptionsGetRequest();
$request = new SubscriptionsGetRequest($subscriptionId);


//
// Subscriptions - Update
//

use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsUpdateRequest;

//
// Subscriptions - Activate/Cancel
//

use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsActivateRequest;
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsCancelRequest;


$request = new SubscriptionsActivateRequest($subscriptionId);

$request = new SubscriptionsCancelRequest($subscriptionId);
$request->setData(["reason"=>"I want to cancel"]); // <-- Must to specify the reason

//
// Subscriptions - Capture Authorized payment on subscription
//

use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsCaptureRequest;

$extraCharge = [
    "note"=> "Charging as the balance reached the limit",
    "capture_type" => "OUTSTANDING_BALANCE",
    "amount" => [
        "currency_code" => "USD",
        "value" => "100"
    ]
];

$request = new SubscriptionsCaptureRequest($subscriptionId);
$request->setData($extraCharge);


//
// Update quantity of product or service in subscription
//
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsUpdateQuantityRequest;

$subscriptionNewQuantities = [
    "plan_id" => "P-5ML4271244454362WXNWU5NQ",
    "shipping_amount" => [
        "currency_code" => "USD",
        "value" => "10.00"
    ],
    "shipping_address" => [
        "name" => [
            "full_name" => "John Doe"
        ],
        "address" => [
            "address_line_1" => "2211 N First Street",
            "address_line_2" => "Building 17",
            "admin_area_2" => "San Jose",
            "admin_area_1" => "CA",
            "postal_code" => "95131",
            "country_code" => "US"
        ]
    ],
    "application_context" => [
    "brand_name" => "walmart",
    "locale" => "en-US",
    "shipping_preference" => "SET_PROVIDED_ADDRESS",
    "payment_method" => [
        "payer_selected" => "PAYPAL",
        "payee_preferred" => "IMMEDIATE_PAYMENT_REQUIRED"
    ],
    "return_url" => "https://example.com/returnUrl",
    "cancel_url" => "https://example.com/cancelUrl"
    ]
];

$request = new SubscriptionsUpdateQuantityRequest($subscriptionId);
$request->setData($subscriptionNewQuantities);

//
// Suspend Subscription
//

use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsSuspendRequest;

$request = new SubscriptionsSuspendRequest($subscriptionId);
$request->setData(["reason"=>"I want to suspend"]); // <-- you got it


//
// List transactions for subscription 
//
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsListTransactionsRequest


$startTime = "2018-01-21T07:50:20.940Z";
$endTime = "2018-08-21T07:50:20.940Z";

$request = new SubscriptionsListTransactionsRequest($subscriptionId,$startTime,$endTime);


//
// DON'T FORGET TO EXECUTE
//

try {
    $response = $client->execute($request);
    print_r($response);
}catch (HttpException $ex) {
    echo $ex->statusCode;
    print_r($ex->getMessage());
}


```

## TODO
Add order and payment abstraction to SuperSimplePaypal.
Create a better doc

