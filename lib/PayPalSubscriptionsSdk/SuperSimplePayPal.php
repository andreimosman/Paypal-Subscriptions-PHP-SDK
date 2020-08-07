<?php

namespace PayPalSubscriptionsSdk;

use PayPalHttp\HttpRequest;

use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\PayPalEnvironment;   // Production
use PayPalCheckoutSdk\Core\SandboxEnvironment;  // Sandbox

use PayPalSubscriptionsSdk\Core\HttpUpdateRequest;

use PayPalSubscriptionsSdk\Catalog\ProductsGetRequest;
use PayPalSubscriptionsSdk\Catalog\ProductsCreateRequest;
use PayPalSubscriptionsSdk\Catalog\ProductsUpdateRequest;

use PayPalSubscriptionsSdk\Plans\PlansGetRequest;
use PayPalSubscriptionsSdk\Plans\PlansCreateRequest;
use PayPalSubscriptionsSdk\Plans\PlansUpdateRequest;
use PayPalSubscriptionsSdk\Plans\PlansActivateRequest;
use PayPalSubscriptionsSdk\Plans\PlansDeactivateRequest;
use PayPalSubscriptionsSdk\Plans\PlansUpdatePriceRequest;

use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsGetRequest;
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsCreateRequest;
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsUpdateRequest;
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsActivateRequest;
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsCancelRequest;
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsCaptureRequest;
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsUpdateQuantityRequest;
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsSuspendRequest;
use PayPalSubscriptionsSdk\Subscriptions\SubscriptionsListTransactionsRequest;


class SuperSimplePayPal {

    protected $client;

    function __construct($clientId,$clientSecret,$environment='sandbox') {
        $environmentClass = "\\PayPalCheckoutSdk\\Core\\".($environment == 'production' ? 'PayPalEnvironment' : 'SandboxEnvironment');
        $this->client = new PayPalHttpClient(new $environmentClass($clientId,$clientSecret));
    }

    public function getResult(HttpRequest $request,$resultKey=null) {
        $result = $this->client->execute($request);
        if( !$resultKey ) return($result->result);
        return($result->result->$resultKey);
    }

    public function create(HttpRequest $request,$data) {
        if( $request instanceof PayPalSubscriptionsSdk\Core\HttpRequest ) {
            $request->setData($data);
        } else {
            // it's PayPalHttp\HttpRequest
            $request->body = $data;
        }
        $result = $this->client->execute($request);
        return($result->result);
    }

    public function update(HttpUpdateRequest $request, $data, $originalRecord=null) {
        if( $originalRecord ) {
            // To see which fields exists on the original record
            $request->setFieldsOnCurrentRecord($request->getFieldListFromObject($originalRecord));
        }

        $request->setData($data);

        $result = $this->client->execute($request);
        return($result);
    }

    public function createProduct($name,$type,$category,$description=null,$image_url=null,$home_url=null) {
        $data = [
            "name" => $name,
            "type" => $type,
            "category" => $category,
            
        ];

        if( $description ) $data["description"] = $description;
        if( $image_url ) $data["image_url"] = $image_url;
        if( $home_url ) $data["home_url"] = $home_url;

        return($this->create(new ProductsCreateRequest(),$data));

    }

    public function updateProduct($id, $category=null,$description=null,$image_url=null,$home_url=null) {
        $data = [ ];

        if( $description ) $data["description"] = $description;
        if( $category ) $data["category"] = $category;
        if( $image_url ) $data["image_url"] = $image_url;
        if( $home_url ) $data["home_url"] = $home_url;

        return($this->update(new ProductsUpdateRequest($id),$data,$this->getProduct($id)));

    }

    public function listProducts() {
        return($this->getResult(new ProductsGetRequest(),"products"));
    }

    public function getProduct($id) {
        return($this->getResult(new ProductsGetRequest($id)));
    }

    // https://developer.paypal.com/docs/api/subscriptions/v1/#plans_create
    public function createPlanWithDetailedBillingCyclesAndPreferences($productId,$name,$description,$billingCycles,$status='ACTIVE',$paymentPreferences=null,$taxes=null,$quantitySupported=false) {

        $data = [
            "product_id" => $productId,
            "name" => $name,
            "description" => $description,
            "status" => $status,
            "billing_cycles" => $billingCycles
        ];

        if( $paymentPreferences ) $data["payment_preferences"] = $paymentPreferences;
        if( $taxes ) $data["taxes"] = $taxes;
        if( $quantitySupported ) $data["quantity_supported"] = $quantitySupported;

        return($this->create(new PlansCreateRequest(),$data));


    }
    // Create a plan with fixed price and by default with status 'ACTIVE' and billing once per month
    // I did this because it's the most usual options for me
    public function createPlan($productId,$name,$description,$fixedPriceValue,$currencyCode='USD',$totalCycles=null,$status='ACTIVE',$frequencyIntervalUnit='MONTH',$frequencyIntervalCount=1,$tenureType='REGULAR') {

        $billingCycles = [
            [
                "frequency" => [
                    "interval_unit" => $frequencyIntervalUnit,
                    "interval_count" => $frequencyIntervalCount,
                ],
                "tenure_type" => "REGULAR",
                "sequence" => 1,
                "pricing_scheme" => [
                    "fixed_price" => [
                        "value" => $fixedPriceValue,
                        "currency_code" => $currencyCode
                    ]
                ]
            ]
        ];

        $paymentPreferences = [
            "auto_bill_outstanding" => true,
        ];

        if( $totalCycles ) $billingCycles["total_cycles"] = $totalCycles;

        return($this->createPlanWithDetailedBillingCyclesAndPreferences($productId,$name,$description,$billingCycles,$status,$paymentPreferences));

    }

    public function updatePlan($id,$description=null,$autoBillOutstanding=null,$paymentFailureTreshold=null,$setupFeeFailureAction=null,$taxesPercentage=null) {
        $data = [];
        if( $description ) $data["description"] = $description;
        if( !is_null($autoBillOutstanding) || $paymentFailureTreshold || $setupFee || $setupFeeFailureAction ) $data["payment_preferences"] = [];

        if( !is_null($autoBillOutstanding) ) $data["payment_preferences"]["auto_bill_outstanding"] = $autoBillOutstanding;
        if( $paymentFailureTreshold ) $data["payment_preferences"]["payment_failure_threshold"] = $paymentFailureTreshold;
        if( $setupFeeFailureAction ) $data["payment_preferences"]["setup_fee_failure_action"] = $setupFeeFailureAction;

        if( $taxesPercentage ) $data["taxes"] = ["percentage"=>$taxesPercentage];

        return($this->update(new PlansUpdateRequest($id),$data));
    }

    public function updatePricingSchemes($id,$pricingSchemes) {
        $data = ["pricing_schemes" => $pricingSchemes ];
        return $this->update(new PlansUpdatePriceRequest($id),$data);
    }

    // That simple, just to match my needs. If someone need something different just tell me. =)
    public function updatePrice($id,$fixedPriceValue,$billingCycleSequence=1) {
        $data = [
            [
                "billing_cycle_sequence" => $billingCycleSequence,
                "pricing_scheme" => [
                    "fixed_price" => [
                        "value" => $fixedPriceValue,
                        "currency_code" => "USD"
                    ]
                ]
            ]
        ];

        return($this->updatePricingSchemes($id,$data));

    }

    public function listPlans() {
        return($this->getResult(new PlansGetRequest(),"plans"));
    }

    public function getPlan($id) {
        return($this->getResult(new PlansGetRequest($id)));
    }

    public function activatePlan($id) {
        return($this->getResult(new PlansActivateRequest($id)));
    }

    public function deactivatePlan($id) {
        return($this->getResult(new PlansDeactivateRequest($id)));
    }


    public function createSubscription($planId,$applicationContext,$quantity=1,$subscriber = null,$shippingAmount=null) {
        $data = [
            "plan_id" => $planId,
            "quantity" => $quantity,
            "application_context" => $applicationContext,
        ];

        if( $shippingAmount ) $data["shipping_amount"] = $shippingAmount;
        if( $subscriber ) $data["subscriber"] = $subscriber;

        return($this->create(new SubscriptionsCreateRequest(),$data));

    }

    public function updateSubscription($id,$data) {
        return $this->update(new SubscriptionsUpdateRequest($id),$data);
    }

    public function updateQuantitiesInSubscription($id,$planId=null,$applicationContext=null,$shippingAmount=null,$shippingAddress=null) {
        $data = [];
        if( $planId ) $data["plan_id"] = $planId;
        if( $applicationContext ) $data["application_context"] = $applicationContext;
        if( $shippingAmount ) $data["shipping_amount"] = $shippingAmount;
        if( $shippingAddress ) $data["shipping_address"] = $shippingAddress;
        return $this->update(new SubscriptionsUpdateQuantityRequest($id),$data);
    }

    /**
     * It keeps returning "NOT AUTHORIZED" if someone succeed in making this work lemme know.
    public function listSubscriptions() {
        return($this->getResult(new SubscriptionsGetRequest(),"subscriptions"));
    }
     */

    public function getSubscription($id) {
        return($this->getResult(new SubscriptionsGetRequest($id)));
    }

    public function captureAuthorizedPaymentOnSubscription() {

    }

    public function activateSubscription($id) {
        return($this->getResult(new SubscriptionsActivateRequest($id)));
    }

    public function cancelSubscription($id) {
        return($this->getResult(new SubscriptionsCancelRequest($id)));
    }

    public function suspendSubscription($id) {
        return($this->getResult(new SubscriptionsSuspendRequest($id)));
    }


}
