<?php

namespace App\Transformers\LoanApplication;

use App\Entities\LoanApplication;
use App\Transformers\Company\CompanyTransformer;
use App\Transformers\Groups\GroupTransformer;
use App\Transformers\Product\ProductTransformer;
use League\Fractal\TransformerAbstract;

/**
 * Class LoanApplicationTransformer.
 */
class LoanApplicationTransformer extends TransformerAbstract
{

    /**
     * @var array
     */
    protected $defaultIncludes = ['company', 'product', 'group', 'depositaccount'];

    /**
     * @param LoanApplication $model
     * @return array
     */
    public function transform(LoanApplication $model)
    {

        $deleted_at = null;
        $applied_at = null;
        $expiry_at = null;
        $approved_expiry_at = null;
        $decline_at = null;
        $validity_expiration_at = null;
        $approval_at = null;

        if ($model->deleted_at) { $deleted_at = $model->deleted_at->toIso8601String(); }
        if ($model->applied_at) { $applied_at = $model->applied_at->toIso8601String(); }
        if ($model->expiry_at) { $expiry_at = $model->expiry_at->toIso8601String(); }
        if ($model->approved_expiry_at) { $approved_expiry_at = $model->approved_expiry_at->toIso8601String(); }
        if ($model->decline_at) { $decline_at = $model->decline_at->toIso8601String(); }
        if ($model->validity_expiration_at) { $validity_expiration_at = $model->validity_expiration_at->toIso8601String(); }
        if ($model->approval_at) { $approval_at = $model->approval_at->toIso8601String(); }

        return [
        
            'id' => $model->id,
            'user_id' => $model->user_id,
            'company_id' => $model->company_id,
            'group_id' => $model->group_id,
            'deposit_account_id' => $model->deposit_account_id,
            'currency_id' => $model->currency_id,
            'loan_amt' => $model->loan_amt,
            'prime_limit_amt' => $model->prime_limit_amt,
            'approved_limit_amt' => $model->approved_limit_amt,
            'product_id' => $model->product_id,
            'comments' => $model->comments,
            'status_id' => $model->status_id,
            'term_cd' => $model->term_cd,
            'term_value' => $model->term_value,
            'approved_term_cd' => $model->approved_term_cd,
            'approved_term_value' => $model->approved_term_value,
            'creator' => $model->creator()->first(['id', 'first_name', 'last_name']),
            'updater' => $model->updater()->first(['id', 'first_name', 'last_name']),
            'deleter' => $model->deleter()->first(['id', 'first_name', 'last_name']),
            'created_at' => $model->created_at->toIso8601String(),
            'updated_at' => $model->updated_at->toIso8601String(),
            'deleted_at' => $deleted_at,
            'applied_at' => $applied_at,
            'expiry_at' => $expiry_at,
            'approved_expiry_at' => $approved_expiry_at,
            'decline_at' => $decline_at,
            'validity_expiration_at' => $validity_expiration_at,
            'approval_at' => $approval_at

        ];

    }

    /**
     * @param LoanApplication $model
     * @return \League\Fractal\Resource\Collection
     */
    public function includeCompany(LoanApplication $model)
    {
        if ($model->company) {
            return $this->item($model->company, new CompanyTransformer());
        } else {
            return null;
        }
    }

    public function includeProduct(LoanApplication $model)
    {
        if ($model->product) {
            return $this->item($model->product, new ProductTransformer());
        } else {
            return null;
        }
    }

    public function includeGroup(LoanApplication $model)
    {
        if ($model->group) {
            return $this->item($model->group, new GroupTransformer());
        } else {
            return null;
        }
    }

    public function includeDepositaccount(LoanApplication $model)
    {
        if ($model->depositaccount) {
            return $this->item($model->depositaccount, new DepositAccountTransformer());
        } else {
            return null;
        }
    }

}
