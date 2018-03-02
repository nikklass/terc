<?php

namespace App\Transformers\DepositAccount;

use App\Entities\DepositAccount;
use App\Transformers\Account\AccountTransformer;
use App\Transformers\Company\CompanyTransformer;
use App\Transformers\Users\UserTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class DepositAccountTransformer.
 */
class DepositAccountTransformer extends TransformerAbstract
{

    /**
     * @var array
     */
    protected $defaultIncludes = ['company', 'account', 'primaryuser'];

    /**
     * @param DepositAccount $model
     * @return array
     */
    public function transform(DepositAccount $model)
    {

        $deleted_at = null;
        $opened_at = null;
        $available_at = null;
        $closed_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }
        if ($model->opened_at) { $opened_at = $model->opened_at->toIso8601String(); }
        if ($model->available_at) { $available_at = $model->available_at->toIso8601String(); }
        if ($model->closed_at) { $closed_at = $model->closed_at->toIso8601String(); }

        return [
        
            'id' => $model->id,
            'name' => $model->account_no . ' - ' . $model->account_name,
            'account_id' => $model->account_id,
            'account_name' => $model->account_name,
            'account_no' => $model->account_no,
            'risk_factor' => $model->risk_factor,
            'currency_id' => $model->currency_id,
            'company_id' => $model->company_id,
            'primary_user_id' => $model->primary_user_id,
            'creator' => $model->creator()->first(['id', 'first_name', 'last_name']),
            'updater' => $model->updater()->first(['id', 'first_name', 'last_name']),
            'deleter' => $model->deleter()->first(['id', 'first_name', 'last_name']),
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
            'deleted_at' => $deleted_at,
            'opened_at' => $opened_at,
            'available_at' => $available_at,
            'closed_at' => $closed_at

        ];

    }

    /**
     * @param DepositAccount $model
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCompany(DepositAccount $model)
    {
        if ($model->company) {
            return $this->item($model->company, new CompanyTransformer());
        } else {
            return null;
        }
    }

    public function includeAccount(DepositAccount $model)
    {
        if ($model->account) {
            return $this->item($model->account, new AccountTransformer());
        } else {
            return null;
        }
    }

    public function includePrimaryuser(DepositAccount $model)
    {
        if ($model->primaryuser) {
            return $this->item($model->primaryuser, new UserTransformer());
        } else {
            return null;
        }
    }

}
