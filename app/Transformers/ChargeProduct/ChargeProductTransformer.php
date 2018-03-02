<?php

namespace App\Transformers\Charge;

use App\Entities\ChargeProduct;
use App\Transformers\Charge\ChargeTransformer;
use App\Transformers\Product\ProductTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class ChargeProductTransformer.
 */
class ChargeProductTransformer extends TransformerAbstract
{

    /**
     * @var array
     */
    protected $defaultIncludes = ['charges', 'products'];


    /**
     * @param ChargeProduct $model
     * @return array
     */
    public function transform(ChargeProduct $model)
    {

        $deleted_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }

        return [
        
            'id' => $model->id,
            'charge_id' => $model->charge_id,
            'product_id' => $model->product_id,
            'status' => $model->status()->first(['id', 'name']),           
            'creator' => $model->creator()->first(['id', 'first_name', 'last_name']),
            'updater' => $model->updater()->first(['id', 'first_name', 'last_name']),
            'deleter' => $model->deleter()->first(['id', 'first_name', 'last_name']),
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
            'deleted_at' => $deleted_at

        ];

    }

    public function includeProducts(ChargeProduct $model)
    {
        if ($model->products) {
            return $this->collection($model->products, new ProductTransformer());
        } else {
            return null;
        }
    }

    public function includeCharges(ChargeProduct $model)
    {
        if ($model->charges) {
            return $this->collection($model->charges, new ChargeTransformer());
        } else {
            return null;
        }
    }

}
