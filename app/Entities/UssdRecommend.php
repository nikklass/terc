<?php

namespace App\Entities;

use App\Entities\Company;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UssdRecommend extends Model
{

    
    /**
     * The attributes that are mass assignable
    **/
    protected $fillable = [
        'phone', 'full_name', 'rec_name', 'rec_phone', 'company_id', 'rec_date'
    ];


    /*relationships*/
    public function company() {
        return $this->belongsTo(Company::class);
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


}
