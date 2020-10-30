<?php

namespace App\Traits;

trait BaseScalable {
    /**
     * @return string
     */
    public function uuid(): string {
        return property_exists($this, "uuid") ? $this->uuid : "uuid";
    }
}
