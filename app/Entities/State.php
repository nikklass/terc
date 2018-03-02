<?php

namespace App\Entities;

use App\Entities\City;
use App\Entities\Country;
use App\Entities\LeadershipTeam;
use App\Entities\Status;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class State.
 */
class State extends Model
{
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'name', 'country_id', 'created_by', 'updated_by'
    ];

    public function users() {
        return $this->hasMany(User::class);
    }

    public function leadershipteams() {
        return $this->hasMany(LeadershipTeam::class);
    }

    public function status() {
        return $this->belongsTo(Status::class);
    }

    public function country() {
        return $this->belongsTo(Country::class);
    }

    public function cities() {
        return $this->hasMany(City::class);
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

        //item data
        $item = static::query()->findOrFail($id);

        //do any extra processing here

        $model = $item->update($attributes);

        return $model;

    }

}
