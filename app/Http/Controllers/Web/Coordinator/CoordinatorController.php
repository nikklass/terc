<?php

namespace App\Http\Controllers\Web\Coordinator;

use App\Entities\Country;
use App\Entities\Group;
use App\Entities\Coordinator;
use App\Entities\Status;
use App\Http\Controllers\Controller;
use App\Services\Coordinator\CoordinatorIndex;
use App\User;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Session;

class CoordinatorController extends Controller
{
    
    protected $model;

    /**
     *  constructor.
     *
     * @param Coordinator $model
     */
    public function __construct(Coordinator $model)
    {
        $this->model = $model;

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, CoordinatorIndex $coordinatorIndex)
    {

        //get the data
        $data = $coordinatorIndex->getCoordinators($request);

        //are we in report mode? return get results
        if ($request->report) {

            $data = $data->get();

        }

        return view('admin.global-altars.coordinators.index', [
            'coordinators' => $data->appends(Input::except('page'))
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
        return view('admin.global-altars.coordinators.create', compact('statuses', 'countries', 'logged_user'));

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
        $coordinator = $this->model->create($request->all());

        Session::flash('success', 'Coordinator successfully created');

        return redirect()->route('coordinators.index');
        
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

            $coordinator = $this->model->find($id);

            if ($coordinator->phone) {
                $phone = getLocalisedPhoneNumber($coordinator->phone, $coordinator->phone_country);
                $coordinator->phone = $phone;
            }

            //dd($coordinator);
            $statuses = Status::all();
            $countries = Country::all();

            return view('admin.global-altars.coordinators.edit', compact('coordinator', 'countries', 'statuses'));

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

            $coordinator = $this->model->find($id);

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

            Session::flash('success', 'Successfully updated coordinator - ' . $coordinator->id);
            //show updated record
            return redirect()->route('coordinators.show', $coordinator->id);

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
        $coordinator = $this->model->find($id);

        if ($coordinator->phone) {
            $phone = getDatabasePhoneNumber($coordinator->phone, $coordinator->phone_country);
            $coordinator->phone = $phone;
        }
        
        return view('admin.global-altars.coordinators.show', compact('coordinator'));

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

        return redirect()->route('coordinators.index');
    }

}
