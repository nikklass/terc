<?php

namespace App\Http\Controllers;

use App\Entities\Company;
use App\Entities\Group;
use App\Entities\ScheduleSmsOutbox;
use App\Entities\SmsOutbox;
use App\User;
use Carbon\Carbon;
use Excel;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Http\Request;
use Session;

class SmsOutboxController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        //get logged in user
        $user = auth()->user();

        //get companies data sent via url
        $companies_request = $request['companies'];
        $companies_array = [];

        if ($companies_request) { $companies_array[] = $companies_request; }
        
        //get account companies
        if ($user->hasRole('superadministrator')){
            
            //companies used to fetch remote data
            if (!count($companies_array)) {
                //get all companies
                $companies_array = Company::pluck('id')
                        ->toArray();
            }

            //get companies accounts for showing in dropdown
            $companies = Company::all();

        } else if ($user->hasRole('administrator')){
            
            //get user company 
            $user_company_id = $user->company->id;

            //companies used to fetch remote data
            if (!count($companies_array)) {
                $companies_array = Company::where('id', $user_company_id)
                        ->pluck('id')
                        ->toArray();
            }

            //get companies for showing in dropdown
            $companies = Company::where('id', $user_company_id)
                        ->get();

        } else {

            abort(404);

        }

        $report = $request->report;
        $start_date = $request->start_date;
        if ($start_date) { $start_date = Carbon::parse($request->start_date); }
        $end_date = $request->end_date;
        if ($end_date) { $end_date = Carbon::parse($request->end_date); }

        //create new SmsOutbox object
        $smsoutboxes = new SmsOutbox();
        
        //filter results
        if (count($companies_array)) { 
            $smsoutboxes = $smsoutboxes->whereIn('company_id', $companies_array); 
        }
        if ($start_date) { 
            $smsoutboxes = $smsoutboxes->where('created_at', '>=', $start_date); 
        }
        if ($end_date) { 
            $smsoutboxes = $smsoutboxes->where('created_at', '<=', $end_date); 
        }
        
        //$smsoutboxes = $smsoutboxes->get();

        $smsoutboxes = $smsoutboxes->orderBy('id', 'desc')
                    ->paginate($request->get('limit', config('app.pagination_limit')));

        return view('smsoutbox.index', compact('smsoutboxes', 'companies'));

    }


    public function index2(Request $request)
    {
        
        //get logged in user
        $user = auth()->user();

        //if user is superadmin, show all companies, else show a user's companies
        $companies = [];
        $company_ids = [];

        if ($user->hasRole('superadministrator')){
            $companies = Company::all();
            $company_ids = $companies->pluck('id');
        } else if ($user->hasRole('administrator')) {
            if ($user->company) {
                $companies = $user->company;
                $company_ids[] = $user->company->id;
            }
        } else {
            abort(404);
        }

        //get company smsoutbox
        $smsoutboxes = [];

        //dd($company_ids, $companies);

        if ($companies) {
        
            $smsoutboxes = SmsOutbox::whereIn('company_id', $company_ids);

            //search params - for filtering records based on search criteria
            $search = $request->search;
            $start_date = $request->start_date;
            $end_date = $request->end_date;
            $company_id = $request->company_id;
            $start_at_date = Carbon::parse($start_date);
            $end_at_date = Carbon::parse($end_date);

            if ($search) {
                
                if ($start_date) {
                    $smsoutboxes = $smsoutboxes->where('created_at', '>=', $start_at_date);
                }

                if ($end_date) {
                    $smsoutboxes = $smsoutboxes->where('created_at', '<=', $start_at_date);
                }

                if ($company_id) {
                    $smsoutboxes = $smsoutboxes->where('company_id', $company_id);
                }

            }
            //end search params 

            $smsoutboxes = $smsoutboxes->orderBy('id', 'desc')
                        ->paginate($request->get('limit', config('app.pagination_limit')));

            return view('smsoutbox.index', compact('smsoutboxes', 'companies'));

        } 

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        //get logged in user
        $user = auth()->user();

        $userCompany = User::where('id', $user->id)
            ->with('company')
            ->first();

        //if user is superadmin, show all companies, else show a user's companies
        $companies = [];
        $company_ids = [];
        if ($user->hasRole('superadministrator')){
            $company_ids = Company::all()->pluck('id');
            $companies = Company::all();
        } else if ($user->hasRole('administrator')) {
            if ($user->company) {
                $company_ids[] = $user->company->id;
                $companies[] = $user->company;
            }
        }

        //get company smsoutbox
        $users = [];
        $groups = [];

        if ($company_ids) {
        
            $smsoutboxes = SmsOutbox::whereIn('company_id', $company_ids)
                    ->orderBy('id', 'desc')
                    ->with('company')
                    ->with('user') 
                    ->get();

            $groups = Group::whereIn('company_id', $company_ids)
                    ->orderBy('id', 'desc')
                    ->with('company')
                    ->get();

            $users = User::whereIn('company_id', $company_ids)
                    ->orderBy('id', 'desc')
                    ->with('company')
                    ->get();

        }



        //get bulk sms data
        $bulk_sms_data = getBulkSMSData($user->id); 
        $error = $bulk_sms_data['error'];

        //dd($bulk_sms_data);
        
        if (!$error) 
        {

            if ($bulk_sms_data['sms_balance'] > 0) {
                $default_source = $bulk_sms_data['default_source'];
                if ($default_source) {
                    $sms_balance = $bulk_sms_data['sms_balance'];
                }
            } else {
                $sms_balance = 0;
            }

            $userCompany->sms_balance = format_num($sms_balance, 0);

            return view('smsoutbox.create')
                   ->withSmsOutboxes($smsoutboxes)
                   ->withCompanies($companies)
                   ->withGroups($groups)
                   ->withUser($userCompany)
                   ->withUsers($users);

        } else {

            abort(404);

        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $auth_user = auth()->user();
        $user_id = $auth_user->id;
        $errors = [];

        $this->validate($request, [
            'sms_message' => 'required'        
        ]);

        $bulk_sms_data = getBulkSMSData($user_id);

        if (!$bulk_sms_data['error']) {

            //dd($bulk_sms_data);
            $usr = $bulk_sms_data['sms_user_name'];
            $src = $bulk_sms_data['default_source'];
            $pass = $bulk_sms_data['passwd'];

            $usersSelected = explode(',', $request->usersSelected);
            $sms_message = trim($request->sms_message);
                
            //formulate sms message if excel file was loaded
            if ($request->attachContent) {

                //read sms and check for delimiters in sms box
                $matches_regex = "/\[\[(\w+)\]\]/";
                $remove_spaces_regex = "/\s+/";

                //remove all spaces
                $regex_message = preg_replace($remove_spaces_regex, ' ', $sms_message);
                $regex_message = strtolower($regex_message);

                //get the replaceable matches
                $hits = preg_match_all($matches_regex, $regex_message, $match_results, PREG_PATTERN_ORDER);

                //dump($match_results[0]); //match full pattern
                //dd($match_results[1]); //match in brackets
                $match_results_full = $match_results[0];
                $match_results = $match_results[1];

                //read excel file if it exists
                if ($request->hasFile('import_file') && count($match_results)) {
            
                    $company_id = null;
                    if ($auth_user->company) {
                        $company_id = $auth_user->company->id;
                    }
                    //dump($company_id);
                    $path = $request->file('import_file')->getRealPath();
                    $data = Excel::load($path, function($reader) {
                    })->get();
                    
                    //get column titles/ headers
                    $line0 = $data[0];
                    $headers = $line0->keys();

                    $sent_sms_count = 0;
                    
                    //insert sms outbox data
                    foreach ($data as $key => $value) {
                        
                        //init sms_message in each loop
                        $local_sms_message = $sms_message;
                        $local_phone_number = null; 
                        
                        //get values from excel and map to sms_message
                        //loop thru headers and get values assigned in $data array
                        //generate the message
                        foreach ($headers as $key => $header) {
                            
                            $header = strtolower($header);

                            //get the phone number
                            if ($header == "phone_number") {
                                $local_phone_number = $value[$header];
                            }

                            //check if $header is in sms_message, 
                            //assign to markers present in sms
                            if (in_array($header, $match_results)) {
                                //get the items value from excel file
                                $item_value = $value[$header];
                                $item_value_regex = "/\[\[$header\]\]/i";
                                //dump($item_value_regex);
                                //replace sms_message placeholder with this value
                                $local_sms_message = preg_replace($item_value_regex, $item_value, $local_sms_message);
                            }

                        }
                        //end generate the message

                        //dump($local_sms_message);

                        // send sms
                        if ($request->sendSmsCheckBox == 'now') {

                            /*$params['usr'] = $usr;
                            $params['pass'] = $pass;
                            $params['src'] = $src;
                            $params['phone_number'] = $local_phone_number;
                            $params['sms_message'] = $local_sms_message;

                            $response = sendSms($params);*/

                            //format local phone number
                            $local_phone_number = formatPhoneNumber($local_phone_number);

                            //find user who owns phone number 
                            $local_user_id = null;
                            if ($local_phone_number) {
                                $local_user = User::where('phone_number', $local_phone_number)
                                                    ->where('company_id', $company_id)
                                                    ->first();
                                if ($local_user) {
                                    $local_user_id = $local_user->id;
                                }
                            }  

                            //start create new outbox
                            $message = $local_sms_message;
                            $phone = $local_phone_number;
                            $company_id = $request->company_id;
                            $sms_user_name = $usr;
                            createSmsOutbox($message, $phone, $company_id, $sms_user_name);
                            //end create new outbox

                        } else {
                            
                            //create new scheduled sms outbox
                            $schedulesmsoutbox = new ScheduleSmsOutbox();
                            $schedulesmsoutbox->message = $local_sms_message;
                            $schedulesmsoutbox->short_message = reducelength($local_sms_message, 45);
                            $schedulesmsoutbox->user_id = $local_user_id;
                            $schedulesmsoutbox->phone_number = $local_phone_number;
                            $schedulesmsoutbox->company_id = $request->company_id;
                            $schedulesmsoutbox->sms_user_name = $usr;
                            $schedulesmsoutbox->user_agent = getUserAgent();
                            $schedulesmsoutbox->src_ip = getIp();
                            $schedulesmsoutbox->src_host = getHost();
                            $schedulesmsoutbox->created_by = $user_id;
                            $schedulesmsoutbox->updated_by = $user_id;
                            $schedulesmsoutbox->save();

                        }

                        $sent_sms_count++;
                        
                    }

                    Session::flash('success', "<strong>$sent_sms_count</strong> SMS successfully sent/ scheduled");
                    return redirect()->back();

                } else {
                    //throw an error msg here
                    //send back
                    Session::flash('error', "Please select excel file and have [[markers]] in your sms message");
                    return redirect()->back()->withInput();
                }

            }

            //if users is selected and not attach content selected
            if ((count($usersSelected) > 0) && (!$request->attachContent)){
                
                //send message(s)
                foreach ($usersSelected as $x) {
                    
                    //get the recipient user details
                    $user = User::where('id', $x)->first();

                    if ($request->sendSmsCheckBox == 'now') {

                        /*$params['usr'] = $usr;
                        $params['pass'] = $pass;
                        $params['src'] = $src;
                        $params['phone_number'] = $user->phone_number;
                        $params['sms_message'] = $request->sms_message;*/
                        //dump($params);

                        //$response['error'] = true;
                        //$response = sendSms($params);

                        //dd($response);

                        //if (!$response['error']) {
                            
                            //create new outbox
                            /*$smsoutbox = new SmsOutbox();
                            $smsoutbox->message = $request->sms_message;
                            $smsoutbox->short_message = reducelength($request->sms_message,45);
                            $smsoutbox->user_id = $x;
                            $smsoutbox->company_id = $request->company_id;
                            $smsoutbox->sms_user_name = $usr;
                            $smsoutbox->phone_number = formatPhoneNumber($user->phone_number);
                            $smsoutbox->user_agent = getUserAgent();
                            $smsoutbox->src_ip = getIp();
                            $smsoutbox->src_host = getHost();
                            $smsoutbox->created_by = $user_id;
                            $smsoutbox->updated_by = $user_id;
                            $smsoutbox->save();*/

                            //start create new outbox
                            $message = $request->sms_message;
                            $phone = $user->phone_number;
                            $company_id = $request->company_id;
                            $sms_user_name = $usr;
                            createSmsOutbox($message, $phone, $company_id, $sms_user_name);
                            //end create new outbox

                        /*} else {

                            $errors[] = $response->message;

                        }*/

                    } else {
                        
                        //create new scheduled sms outbox
                        $schedulesmsoutbox = new ScheduleSmsOutbox();
                        $schedulesmsoutbox->message = $request->sms_message;
                        $schedulesmsoutbox->user_id = $x;
                        $schedulesmsoutbox->company_id = $request->company_id;
                        $schedulesmsoutbox->sms_user_name = $usr;
                        $schedulesmsoutbox->phone_number = formatPhoneNumber($user->phone_number);
                        $schedulesmsoutbox->user_agent = getUserAgent();
                        $schedulesmsoutbox->src_ip = getIp();
                        $schedulesmsoutbox->src_host = getHost();
                        $schedulesmsoutbox->created_by = $user_id;
                        $schedulesmsoutbox->updated_by = $user_id;
                        $schedulesmsoutbox->save();                    

                    }

                } 
                
                Session::flash('success', 'SMS successfully sent/ scheduled');
                return redirect()->back();
            
            }

        }
        //dd('hell');

        //send back
        Session::flash('error', "You dont have an active SMS account. Please contact pendomedia.");
        return redirect()->back()->withInput();

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        //get details for this smsoutbox
        $smsoutbox = SmsOutbox::where('id', $id)
                 ->with('company')
                 ->first();
        
        return view('smsoutbox.show', compact('smsoutbox'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
        $group = Group::where('id', $id)
                 ->with('company')
                 ->first();

        $user = auth()->user();
        //if user is superadmin, show all companies, else show a user's companies
        if ($user->hasRole('superadministrator')){
            $companies = Company::all();
        } else {
            $companies = $user->company;
        }
        
        return view('smsoutbox.edit')->withGroup($group)->withCompanies($companies);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $user_id = auth()->user()->id;

        $this->validate($request, [
            'name' => 'required|max:255',
            'phone_number' => 'required|max:255',
            'company_id' => 'required|max:255'
        ]);

        $group = Group::findOrFail($id);
        $group->name = $request->name;
        $group->company_id = $request->company_id;
        $group->phone_number = $request->phone_number;
        $group->email = $request->email;
        $group->physical_address = $request->physical_address;
        $group->box = $request->box;
        $group->updated_by = $user_id;
        $group->save();

        Session::flash('success', 'Successfully updated group - ' . $group->name);
        return redirect()->route('smsoutbox.show', $group->id);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
