<?php

namespace App\Transformers\Channel;

use App\Entities\Channel;
use App\Transformers\Product\ProductTransformer;
use App\Transformers\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class ChannelTransformer.
 */
class ChannelTransformer extends TransformerAbstract
{

    /**
     * @param Channel $model
     * @return array
     */
    public function transform(Channel $model)
    {

        $deleted_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }

        return [
        
            'id' => $model->id,
            'channel_cd' => $model->channel_cd,
            'description' => $model->description,
            'gl_dr_account' => $model->gl_dr_account,
            'gl_cr_account' => $model->gl_cr_account,
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
