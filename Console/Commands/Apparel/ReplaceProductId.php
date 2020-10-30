<?php

namespace App\Console\Commands\Apparel;

use App\Models\Apparel\Product;
use Illuminate\Console\Command;

class ReplaceProductId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apparel:replace-id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    /**
     * @var Product
     */
    private Product $product;

    /**
     * Create a new command instance.
     *
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        parent::__construct();
        $this->product = $product;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $products = $this->product->all();

        foreach ($products as $product) {
            preg_match("~( - [0-9A-Z]+)$~", $product->product_name, $strId);

            if (empty($strId)) {
                continue;
            }

            $product->product_name = preg_replace("~( - [0-9A-Z]+)$~", "", $product->product_name);
            $product->ascolour_id  = str_replace([" ", "-"], "", $strId[0]);
            $product->save();
        }

        $this->info("Success.");
    }
}
