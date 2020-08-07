<?php

namespace PayPalSubscriptionsSdk\Core;

abstract class HttpUpdateRequest extends HttpRequest {
    protected $fieldsThatCanBeUpdated; // It's not considered when it's empty
    protected $fieldsOnCurrentRecord;

    public function setData($data) {
        $this->body = self::convertToUpdateInstructions($data);
        return($this->body);
    }

    public function getFieldListFromObject($obj,$prefix=null) {
        $obj = (array) $obj;
        $fieldList = [];
        foreach($obj as $key => $value) {
            if(is_numeric($key)) { // <-- to ignore numeric indexes
                $key = $prefix;
            } else {
                if( $prefix ) $key = $prefix . "/" . $key;
            }
            
            if( is_array($value) || is_object($value) ) {
                $list = $this->getFieldListFromObject($value,$key);
                $fieldList = array_merge($fieldList,$list);
                continue;
            }
            $fieldList[] = $key;
        }
        return($fieldList);
    }

    public function setFieldsOnCurrentRecord($fieldsOnCurrentRecord) {
        $this->fieldsOnCurrentRecord = $fieldsOnCurrentRecord;
    }

    protected function getUpdateInstruction($key,$value) {
        $op = "replace";
        if( $this->fieldsOnCurrentRecord && !in_array($key,$this->fieldsOnCurrentRecord) ) $op = "add";

        return (!$this->fieldsThatCanBeUpdated || in_array($key,$this->fieldsThatCanBeUpdated)) ? [
            "op" => $op, // <-- When you create a record without some value it must to be added in order to work
            "path" => '/'.$key,
            "value" => $value
        ] : null;
    }

    public function convertToUpdateInstructions($data,$prefix="") {
        $data = (array) $data; // I prefer to address it as array
        $updateInstructions = [];
        foreach($data as $key => $value) {
            if( $prefix ) $key = $prefix . "/" . $key;
            if( is_array($value) ) {
                $newInstructions = $this->convertToUpdateInstructions($value,$key);
                $updateInstructions = array_merge($updateInstructions, $newInstructions);
                continue;
            } 

            if($instruction=$this->getUpdateInstruction($key,$value)) $updateInstructions[] = $instruction;
        }
        return($updateInstructions);
    }
    

}
