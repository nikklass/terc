<?php

namespace App\Transformers\Product;

use App\Entities\Product;
use League\Fractal\TransformerAbstract;

/**
 * Class ProductTransformer
 * @package App\Transformers
 */
class ProductTransformer extends TransformerAbstract
{

    /**
     * @param Product $model
     * @return array
     */
    public function transform(Product $model)
    {

        $start_at = null;
        $end_at = null;
        $deleted_at = null;

        if ($model->start_at) { $start_at = $model->start_at->toIso8601String(); }
        if ($model->end_at) { $end_at = $model->end_at->toIso8601String(); }
        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }

        return [ 

          'id' => (int)$model->id,
          'product_cd' => $model->product_cd,
          'description' => $model->description,
          'currency_id' => $model->currency_id,
          'status_id' => $model->status_id,
          'currency' => $model->currency()->first(['id', 'name', 'initials']),
          'status' => $model->status()->first(['id', 'name']),
          'creator' => $model->creator()->first(['id', 'first_name', 'last_name']),
          'updater' => $model->updater()->first(['id', 'first_name', 'last_name']),
          'deleter' => $model->deleter()->first(['id', 'first_name', 'last_name']),
          'created_at' => $model->created_at->toIso8601String(),
          'updated_at' => $model->updated_at->toIso8601String(),
          'deleted_at' => $deleted_at,
          'start_at' => $start_at,
          'end_at' => $end_at

        ];
    }


}
