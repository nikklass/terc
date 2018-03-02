<?php

namespace App\Entities;

use App\Entities\Company;
use App\Entities\LoanApplication;
use App\Entities\Product;
use App\Entities\Status;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class DepositAccount extends Model
{

    use SoftDeletes;

    protected $dates = ['deleted_at', 'opened_at', 'available_at', 'closed_at'];

    /**
     * The attributes that are mass assignable
    **/
    protected $fillable = [
         'id', 'account_id', 'account_name', 'account_no', 'currency_id', 'risk_factor', 'opened_at', 'status_id', 'available_at', 'closed_at', 'company_id', 'primary_user_id', 'created_at', 'created_by', 'updated_at', 'updated_by', 'deleted_at', 'deleted_by' 
    ];


    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function account() 
    {
        return $this->belongsTo(Account::class);
    }

    public function primaryuser()
    {
        return $this->belongsTo(User::class, 'primary_user_id', 'id');
    }

    public function loanapplications()
    {
        return $this->hasMany(LoanApplication::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
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

        if (auth()->user()) {
            $user_id = auth()->user()->id;

            $attributes['created_by'] = $user_id;
            $attributes['updated_by'] = $user_id;
        }

        $model = static::query()->create($attributes);

        return $model;

    }

    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function updatedata($id, array $attributes = [])
    {

        if (auth()->user()) {
            $user_id = auth()->user()->id;

            $attributes['updated_by'] = $user_id;
        }

        //item data
        $item = static::query()->findOrFail($id);

        //do any extra processing here

        $model = $item->update($attributes);

        return $model;

    }


}
