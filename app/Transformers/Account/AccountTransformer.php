<?php

namespace App\Transformers\Account;

use App\Entities\Account;
use App\Transformers\Product\ProductTransformer;
use App\Transformers\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class AccountTransformer.
 */
class AccountTransformer extends TransformerAbstract
{

    /**
     * @var array
     */
    protected $defaultIncludes = ['product', 'company'];

    /**
     * @param Account $model
     * @return array
     */
    public function transform(Account $model)
    {

        $deleted_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }

        return [
        
            'id' => $model->id,
            'account_name' => $model->account_name,
            'account_no' => $model->account_no,
            'product_id' => $model->product_id,
            'currency' => $model->currency()->first(['id', 'name']),
            'company_id' => $model->company_id,
            'status' => $model->status()->first(['id', 'name']),
            'user_id' => $model->user_id,
            'stmnt_freq_cd' => $model->stmnt_freq_cd,
            'stmnt_freq_value' => $model->stmnt_freq_value,            
            'creator' => $model->creator()->first(['id', 'first_name', 'last_name']),
            'updater' => $model->updater()->first(['id', 'first_name', 'last_name']),
            'deleter' => $model->deleter()->first(['id', 'first_name', 'last_name']),
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
            'deleted_at' => $deleted_at

        ];

    }

    /**
     * @param Account $model
     * @return \League\Fractal\Resource\Collection
     */
    public function includeProduct(Account $model)
    {
        if ($model->product) {
            return $this->item($model->product, new ProductTransformer());
        } else {
            return null;
        }
    }

    public function includeCompany(Account $model)
    {
        if ($model->account) {
            return $this->item($model->account, new AccountTransformer());
        } else {
            return null;
        }
    }

}
