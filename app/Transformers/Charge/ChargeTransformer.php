<?php

namespace App\Transformers\Charge;

use App\Entities\Charge;
use App\Transformers\Product\ProductTransformer;
use App\Transformers\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class ChargeTransformer.
 */
class ChargeTransformer extends TransformerAbstract
{

    /**
     * @param Account $model
     * @return array
     */
    public function transform(Charge $model)
    {

        $deleted_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }

        return [
        
            'id' => $model->id,
            'charge_cd' => $model->charge_cd,
            'description' => $model->description,
            'amount' => $model->amount,
            'percent' => $model->percent,
            'currency' => $model->currency()->first(['id', 'name']),
            'income_gl_acccount_no' => $model->income_gl_acccount_no,
            'status' => $model->status()->first(['id', 'name']),           
            'creator' => $model->creator()->first(['id', 'first_name', 'last_name']),
            'updater' => $model->updater()->first(['id', 'first_name', 'last_name']),
            'deleter' => $model->deleter()->first(['id', 'first_name', 'last_name']),
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
            'deleted_at' => $deleted_at

        ];

    }

}
