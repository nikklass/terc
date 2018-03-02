<?php

namespace App\Http\Controllers\Web\PrayerPoint;

use App\Entities\Group;
use App\Entities\PrayerPoint;
use App\Entities\Status;
use App\Http\Controllers\Controller;
use App\Services\PrayerPoint\PrayerPointIndex;
use App\User;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Session;

class PrayerPointController extends Controller
{
    
    protected $model;

    /**
     *  constructor.
     *
     * @param PrayerPoint $model
     */
    public function __construct(PrayerPoint $model)
    {
        $this->model = $model;

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PrayerPointIndex $prayerPointIndex)
    {

        //get the data
        $data = $prayerPointIndex->getPrayerPoints($request);

        //are we in report mode? return get results
        if ($request->report) {

            $data = $data->get();

        }

        return view('admin.prayer-center.prayer-points.index', [
            'prayerPoints' => $data->appends(Input::except('page'))
        ]);

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $logged_user = auth()->user();
        $statuses = Status::all();
        return view('admin.prayer-center.prayer-points.create', compact('statuses', 'logged_user'));

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
        $prayerPoint = $this->model->create($request->all());

        Session::flash('success', 'Prayer point successfully created');

        return redirect()->route('prayer-points.index');
        
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

            $prayerPoint = $this->model->find($id);
            $statuses = Status::all();

            return view('admin.prayer-center.prayer-points.edit', compact('prayerPoint', 'statuses'));

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

            $prayerPoint = $this->model->find($id);

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

            Session::flash('success', 'Successfully updated prayer point - ' . $prayerPoint->id);
            //show updated record
            return redirect()->route('prayer-points.show', $prayerPoint->id);

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
        $prayerPoint = $this->model->find($id);
        
        return view('admin.prayer-center.prayer-points.show', compact('prayerPoint'));

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

        return redirect()->route('prayer-points.index');
    }

}
