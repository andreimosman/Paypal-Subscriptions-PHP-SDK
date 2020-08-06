<?php

namespace PayPalSubscriptionsSdk\Core;

abstract class HttpUpdateRequest extends HttpRequest
{
    protected $fieldsThatCanBeUpdated; // It's not considered when it's empty

    public function setData($data) {
        $this->body = self::convertToUpdateInstructions($data);
        return($this->body);
    }

    protected function getUpdateInstruction($key,$value) {
        return (!$this->fieldsThatCanBeUpdated || in_array($key,$this->fieldsThatCanBeUpdated)) ? [
            "op" => "replace",
            "path" => "/" . $key,
            "value" => $value
        ] : null;
    }

    public function convertToUpdateInstructions($data,$prefix="") {
        $data = (array) $data; // I prefer to address it as array
        $updateInstructions = [];
        foreach($data as $key => $value) {
            if( is_array($value) ) {
                $newInstructions = $this->convertToUpdateInstructions($value,$key);
                $updateInstructions = array_merge($updateInstructions, $newInstructions);
                continue;
            } 

            if( $prefix ) $key = $prefix . "/" . $key;
            if($instruction=$this->getUpdateInstruction($key,$value)) $updateInstructions[] = $instruction;
        }
        return($updateInstructions);
    }
    

}
