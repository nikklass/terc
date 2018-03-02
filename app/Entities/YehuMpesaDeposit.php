<?php

namespace App\Entities;

use App\Entities\Company;
use App\User;
use App\Entities\UssdEvent;
use App\Entities\UssdPayment;
use App\Entities\UssdRegistrationArchive;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class YehuMpesaDeposit extends Model
{
    
    /**
     * The attributes that are mass assignable
    **/
    protected $fillable = [

        'id', 'amount', 'bu_id', 'acct_no', 'acct_name', 'paybill_number', 'phone_number', 'full_name', 'trans_id', 'src_ip', 'trans_time', 'date_stamp', 'processed', 'processed_at', 'failed', 'failed_at', 'fail_times', 'fail_reason', 'created_at', 'updated_at'

    ]; 

}
