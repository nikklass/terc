<?php

namespace App\Transformers\GlobalAltar;

use App\Entities\GlobalAltar;
use League\Fractal\TransformerAbstract;

/**
 * Class GlobalAltarTransformer.
 */
class GlobalAltarTransformer extends TransformerAbstract
{

    /**
     * @param GlobalAltar $model
     * @return array
     */
    public function transform(GlobalAltar $model)
    {

        $deleted_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }

        return [

            'id' => $model->id,
            'name' => $model->name,
            'description' => $model->description,
            'status_id' => $model->status_id,
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
