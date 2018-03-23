<?php

namespace App\Http\Controllers\Web\GlobalAltar;

use App\Entities\Country;
use App\Entities\GlobalAltar;
use App\Entities\Group;
use App\Entities\Status;
use App\Http\Controllers\Controller;
use App\Services\GlobalAltar\GlobalAltarIndex;
use App\User;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Session;

class GlobalAltarController extends Controller
{
    
    protected $model;

    /**
     *  constructor.
     *
     * @param GlobalAltar $model
     */
    public function __construct(GlobalAltar $model)
    {
        $this->model = $model;

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, GlobalAltarIndex $globalAltarIndex)
    {

        //get the data
        $data = $globalAltarIndex->getGlobalAltars($request);

        //are we in report mode? return get results
        if ($request->report) {

            $data = $data->get();

        }

        return view('admin.global-altars.global-altars.index', [
            'globalAltars' => $data->appends(Input::except('page'))
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
        return view('admin.global-altars.global-altars.create', compact('statuses', 'logged_user', 'countries'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 

        $rules = [
            'name' => 'required',
            'status_id' => 'required',
        ];

        $payload = app('request')->only('name', 'status_id');

        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        //create item
        $globalAltar = $this->model->create($request->all());

        Session::flash('success', 'Global altar successfully created');

        return redirect()->route('global-altars.index');
        
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

            $globalAltar = $this->model->find($id);
            $statuses = Status::all();

            return view('admin.global-altars.global-altars.edit', compact('globalAltar', 'statuses'));

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

            $globalAltar = $this->model->find($id);

            $rules = [
                'name' => 'required',
                'status_id' => 'required',
            ];

            $payload = app('request')->only('name', 'status_id');

            $validator = app('validator')->make($payload, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            // update fields
            $this->model->updatedata($id, $request->all());

            Session::flash('success', 'Successfully updated global altar - ' . $globalAltar->id);
            //show updated record
            return redirect()->route('global-altars.show', $globalAltar->id);

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
        $globalAltar = $this->model->find($id);
        
        return view('admin.global-altars.global-altars.show', compact('globalAltar'));

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

        return redirect()->route('global-altars.index');
    }

}
