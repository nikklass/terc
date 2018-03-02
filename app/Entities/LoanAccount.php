<?php

namespace App\Entities;

use App\Entities\Company;
use App\Entities\Currency;
use App\Entities\Product;
use App\Entities\Status;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class LoanAccount extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at', 'start_at', 'maturity_at', 'final_repayment_at', 'original_maturity_at'];

    /**
     * The attributes that are mass assignable
    **/
    protected $fillable = [
         'id', 'account_name', 'account_no', 'currency_id', 'user_id', 'company_id', 'status_id', 'application_id', 'disbursement_limit', 'start_at', 'maturity_at', 'final_repayment_at', 'loan_cycle', 'original_maturity_at', 'term_cd', 'term_value', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by' 
    ];


    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
        //class, foreign key, local key
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by', 'id');
    }


    //start convert dates to local dates
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone(getLocalTimezone());
    }


    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone(getLocalTimezone());
    }
    //end convert dates to local dates


    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function create(array $attributes = [])
    {

        $user_id = auth()->user()->id;

        $attributes['created_by'] = $user_id;
        $attributes['updated_by'] = $user_id;

        $model = static::query()->create($attributes);

        return $model;

    }

    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function updatedata($id, array $attributes = [])
    {

        $user_id = auth()->user()->id;

        $attributes['updated_by'] = $user_id;

        //branch data
        $loanapplication = static::query()->findOrFail($id);

        //do any extra processing here

        $model = $loanapplication->update($attributes);

        return $model;

    }


}
