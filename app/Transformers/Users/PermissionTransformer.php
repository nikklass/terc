<?php

namespace App\Transformers\Users;

use App\Permission;
use League\Fractal\TransformerAbstract;

/**
 * Class PermissionTransformer
 * @package App\Transformers\Users
 */
class PermissionTransformer extends TransformerAbstract
{

    /**
     * @param Permission $model
     * @return array
     */
    public function transform(Permission $model)
    {
        return [
            'id' => $model->uuid,
            'name' => $model->name,
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
        ];
    }
}
