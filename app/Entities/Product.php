<?php

namespace App\Entities;
use App\Entities\Charge;
use App\Entities\Company;
use App\Entities\Currency;
use App\Entities\DepositAccount;
use App\Entities\LoanApplication;
use App\Entities\Status;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{

    use SoftDeletes; 

    protected $dates = ['deleted_at', 'start_at', 'end_at'];

    protected $fillable = [
          'id', 'product_cd', 'name', 'currency_id', 'status_id', 'start_at', 'end_at', 'created_by', 'updated_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
    ];

    /*one to many relationship*/
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function depositaccounts()
    {
        return $this->hasMany(DepositAccount::class);
    }

    public function loanapplications()
    {
        return $this->hasMany(LoanApplication::class);
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class);
    }

    public function charges()
    {
        return $this->belongsToMany(Charge::class);
    }

    public function status()
    {
        return $this->belongsTo(Status::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
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

    public function getStartAtAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->timezone(getLocalTimezone());
        }
    }

    public function getEndAtAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->timezone(getLocalTimezone());
        }
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

        //start if start_at or end_at exist, format date
        if (array_key_exists('start_at', $attributes) && ($attributes['start_at'] != null)) {
            $start_at = $attributes['start_at'];
            $attributes['start_at'] = Carbon::parse($start_at);
        }

        if (array_key_exists('end_at', $attributes) && ($attributes['end_at'] != null)) {
            $end_at = $attributes['end_at'];
            $attributes['end_at'] = Carbon::parse($end_at);
        }
        //end if start_at or end_at exist, format date

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

        //product data
        $product = static::query()->findOrFail($id);

        //start if start_at or end_at exist, format date
        if (array_key_exists('start_at', $attributes) && ($attributes['start_at'] != null)) {
            $start_at = $attributes['start_at'];
            $attributes['start_at'] = Carbon::parse($start_at);
        }

        if (array_key_exists('end_at', $attributes) && ($attributes['end_at'] != null)) {
            $end_at = $attributes['end_at'];
            $attributes['end_at'] = Carbon::parse($end_at);
        } else {
            $attributes['end_at'] = null;
        }
        //end if start_at or end_at exist, format date

        //dd($attributes);

        $model = $product->update($attributes);

        return $model;

    }


}
