<?php

namespace App\Http\Controllers\Web\LeadershipTeam;

use App\Entities\Country;
use App\Entities\Group;
use App\Entities\LeadershipTeam;
use App\Entities\Status;
use App\Http\Controllers\Controller;
use App\Services\LeadershipTeam\LeadershipTeamIndex;
use App\User;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Session;

class LeadershipTeamController extends Controller
{
    
    protected $model;

    /**
     *  constructor.
     *
     * @param LeadershipTeam $model
     */
    public function __construct(LeadershipTeam $model)
    {
        $this->model = $model;

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, LeadershipTeamIndex $leadershipTeamIndex)
    {

        //get the data
        $data = $leadershipTeamIndex->getLeadershipTeams($request);

        //are we in report mode? return get results
        if ($request->report) {

            $data = $data->get();

        }

        return view('admin.manage.leadership-teams.index', [
            'leadershipTeams' => $data->appends(Input::except('page'))
        ]);

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $logged_user = auth()->user();
        $statuses = Status::all();
        $countries = Country::all();
        return view('admin.manage.leadership-teams.create', compact('statuses', 'countries', 'logged_user'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 

        $rules = [
            'title' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_country' => 'required_with:phone',
            'phone' => 'required|phone',
            'country_id' => 'required',
        ];

        $payload = app('request')->only('title', 'first_name', 'last_name', 'phone', 'phone_country', 'country_id');

        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        //create item
        $leadershipTeam = $this->model->create($request->all());

        Session::flash('success', 'Team member successfully created');

        return redirect()->route('leadership-teams.index');
        
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $user = auth()->user();

        //if user is superadmin, proceed, else, abort
        if ($user->hasRole('superadministrator')){

            $leadershipTeam = $this->model->find($id);

            if ($leadershipTeam->phone) {
                $phone = getLocalisedPhoneNumber($leadershipTeam->phone, $leadershipTeam->phone_country);
                $leadershipTeam->phone = $phone;
            }

            //dd($leadershipTeam);
            $statuses = Status::all();
            $countries = Country::all();

            return view('admin.manage.leadership-teams.edit', compact('leadershipTeam', 'countries', 'statuses'));

        } else {

            abort(404);

        }

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
        
        $user = auth()->user();

        //if user is superadmin, proceed, else, abort
        if ($user->hasRole('superadministrator')){

            $leadershipTeam = $this->model->find($id);

            $rules = [
                'title' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'phone_country' => 'required_with:phone',
                'phone' => 'required|phone',
                'country_id' => 'required',
            ];

            $payload = app('request')->only('title', 'first_name', 'last_name', 'phone', 'phone_country', 'country_id');

            $validator = app('validator')->make($payload, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            // update fields
            $this->model->updatedata($id, $request->all());

            Session::flash('success', 'Successfully updated team member - ' . $leadershipTeam->id);
            //show updated record
            return redirect()->route('leadership-teams.show', $leadershipTeam->id);

        } else {

            abort(404);

        }


        

    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        //get details for this item
        $leadershipTeam = $this->model->find($id);

        if ($leadershipTeam->phone) {
            $phone = getDatabasePhoneNumber($leadershipTeam->phone, $leadershipTeam->phone_country);
            $leadershipTeam->phone = $phone;
        }
        
        return view('admin.manage.leadership-teams.show', compact('leadershipTeam'));

    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user_id = auth()->user()->id;

        $item = $this->model->findOrFail($id);
        
        if ($item) {
            //update deleted by field
            $item->update(['deleted_by' => $user_id]);
            $result = $item->delete();
        }

        return redirect()->route('loan-applications.index');
    }

}
