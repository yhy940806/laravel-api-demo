<?php

namespace App\Http\Transformers;

use League\Fractal\TransformerAbstract;

class BaseTransformer extends TransformerAbstract
{

    protected $resType = "";

    protected $cacheTTL;

    public $availableIncludes = [

    ];

    protected $defaultIncludes = [

    ];

    public function __construct(array $arrIncludes = null, $resType = "soundblock")
    {
        $this->cacheTTL = config("constant.cache_ttl");

        if ($arrIncludes)
        {
            foreach($arrIncludes as $item)
            {
                $item = strtolower($item);
                $this->availableIncludes []= $item;
                $this->defaultIncludes []= $item;
            }
        }

    }
}
