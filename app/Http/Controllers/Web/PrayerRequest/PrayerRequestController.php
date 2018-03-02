<?php

namespace App\Http\Controllers\Web\PrayerRequest;

use App\Entities\Group;
use App\Entities\PrayerRequest;
use App\Entities\Status;
use App\Http\Controllers\Controller;
use App\Services\PrayerRequest\PrayerRequestIndex;
use App\User;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Session;

class PrayerRequestController extends Controller
{
    
    protected $model;

    /**
     *  constructor.
     *
     * @param PrayerRequest $model
     */
    public function __construct(PrayerRequest $model)
    {
        $this->model = $model;

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, PrayerRequestIndex $prayerRequestIndex)
    {

        //get the data
        $data = $prayerRequestIndex->getPrayerRequests($request);

        //are we in report mode? return get results
        if ($request->report) {

            $data = $data->get();

        }

        return view('admin.prayer-center.prayer-requests.index', [
            'prayerRequests' => $data->appends(Input::except('page'))
        ]);

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $logged_user = auth()->user();
        $statuses = Status::all();
        return view('admin.prayer-center.prayer-requests.create', compact('statuses', 'logged_user'));

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 

        $rules = [
            'title' => 'required',
            'description' => 'required',
        ];

        $payload = app('request')->only('title', 'description');

        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        //create item
        $prayerRequest = $this->model->create($request->all());

        Session::flash('success', 'Prayer Request successfully created');

        return redirect()->route('prayer-requests.index');
        
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

            $prayerRequest = $this->model->find($id);
            $statuses = Status::all();

            return view('admin.prayer-center.prayer-requests.edit', compact('prayerRequest', 'statuses'));

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

            $prayerRequest = $this->model->find($id);

            $rules = [
                'full_names' => 'required',
                'title' => 'required',
                'description' => 'required',
                'status_id' => 'required',
            ];

            $payload = app('request')->only('full_names', 'title', 'description', 'status_id');

            $validator = app('validator')->make($payload, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            // update fields
            $this->model->updatedata($id, $request->all());

            Session::flash('success', 'Successfully updated prayer request - ' . $prayerRequest->id);
            //show updated record
            return redirect()->route('prayer-requests.show', $prayerRequest->id);

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
        $prayerRequest = $this->model->find($id);
        
        return view('admin.prayer-center.prayer-requests.show', compact('prayerRequest'));

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
