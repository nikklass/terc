<?php

namespace App\Entities;

use App\Entities\Company;
use App\Entities\Status;
use App\Entities\UssdPayment;
use App\Entities\UssdRegistration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UssdEvent extends Model
{

    
    /**
     * The attributes that are mass assignable
    **/
    protected $fillable = [
        'name', 'company_id', 'description', 'amount', 'status', 'start_at', 'end_at'
    ];
    

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
    */
    protected $dates = ['start_at', 'end_at'];


    /*relationships*/
    public function company() {
        return $this->belongsTo(Company::class);
    }
    

    public function ussdpayments() {
        return $this->hasMany(UssdPayment::class);
    }


    public function eventstatus() {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }


    public function ussdregistrations() {
        return $this->hasMany(UssdRegistration::class);
    }


    

    /**
     * @param array $attributes
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function create(array $attributes = [])
    {

        $start_at = $attributes['start_at'];
        $end_at = $attributes['end_at'];

        if (array_key_exists('start_at', $attributes)) {
            $start_at = Carbon::parse($start_at);
            $attributes['start_at'] = $start_at;
        }

        if (array_key_exists('end_at', $attributes)) {
            $end_at = Carbon::parse($end_at);
            $end_at = $end_at->addDay();
            $end_at = $end_at->subMinute();
            $attributes['end_at'] = $end_at;
        }

        $model = static::query()->create($attributes);

        return $model;

    }


    //start convert dates to local dates
    /*public function setStartAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function setEndAtAttribute($value)
    {
        return Carbon::parse($value)->addDay()->subMinute();
    }*/


    public function getStartAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getEndAtAttribute($value)
    {
        return Carbon::parse($value);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone(getLocalTimezone());
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timezone(getLocalTimezone());
    }
    //end convert dates to local dates
    

}
