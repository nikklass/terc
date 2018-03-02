<?php

namespace App\Transformers\GlAccount;

use App\Entities\GlAccount;
use App\Transformers\Product\ProductTransformer;
use App\Transformers\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class GlAccountTransformer.
 */
class GlAccountTransformer extends TransformerAbstract
{

    /**
     * @var array
     */
    protected $defaultIncludes = ['product', 'company'];

    /**
     * @param GlAccount $model
     * @return array
     */
    public function transform(GlAccount $model)
    {

        $deleted_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }

        return [
        
            'id' => $model->id,
            'gl_account_no' => $model->gl_account_no,
            'description' => $model->description,
            'ledger_no' => $model->ledger_no,
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
