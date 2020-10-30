<?php

namespace App\Traits;

trait UnixEpochAttributes {
    protected $blnIsPivot = false;

    public function getTimeStampsAttributes() {
        $this->blnIsPivot = property_exists($this, "isPivot") ? $this->isPivot : false;
        $timestmpsAttributes = [];
        if ($this->blnIsPivot) {
            $timestmpsAttributes = ["stamp_created_by", "stamp_updated_by"];

        } else {

            $timestmpsAttributes = ["stamp_created", "stamp_updated"];

        }
        return $timestmpsAttributes;
    }

    public function setAttribute($key, $value) {
        if (in_array($key, $this->getTimeStmpsAttributes())) {
            $this->attributes[$key] = $value;
        } else {
            foreach ($this->getTimeStmpsAttributes() as $attribute) {
                parent::setAttribute($attribute, time());
            }
        }
    }
}
