<?php

namespace App\Http\Controllers\Web\States;

use App\Entities\Country;
use App\Entities\State;
use App\Entities\Status;
use App\User;
use App\Http\Controllers\Controller;
use App\Services\State\StateIndex;
use Carbon\Carbon;
use Dingo\Api\Exception\StoreResourceFailedException;
use Dingo\Api\Http\Response\paginator;
use Dingo\Api\Routing\Helpers;
use Illuminate\Database\Eloquent\paginate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Jenssegers\Agent\Agent;
use Propaganistas\LaravelPhone\PhoneNumber;
use libphonenumber\PhoneNumberFormat;

/**
 * Class StatesController.
 *
 */
class StatesController extends Controller
{

    /**
     * @var State
     */
    protected $model;

    /**
     * CountriesController constructor.
     *
     * @param State $model
     */
    public function __construct(State $model)
    {
        $this->model = $model;
    }

    /**
     * Returns the Users resource with the roles relation.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request, StateIndex $stateIndex)
    {

        //get the data
        $data = $stateIndex->getStates($request);

        //are we in report mode? return get results
        if ($request->report) {

            $data = $data->get();

        }

        $countries = Country::all();

        return view('admin.manage.states.index', [
            'countries' => $countries,
            'states' => $data->appends(Input::except('page'))
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
        return view('admin.manage.states.create', compact('statuses', 'countries', 'logged_user'));

    }

    public function edit($id)
    {

        $user = auth()->user();

        //if user is superadmin, proceed, else, abort
        if ($user->hasRole('superadministrator')){

            $state = $this->model->find($id);
            $countries = Country::all();
            $statuses = Status::all();

            return view('admin.manage.states.edit', compact('state', 'countries', 'statuses'));

        } else {

            abort(404);

        }

    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {

        //get details for this item
        $state = $this->model->find($id);
        
        return view('admin.manage.states.show', compact('state'));

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    { 

        $rules = [
            'name' => 'required',
            'country_id' => 'required',
            'status_id' => 'required',
        ];

        $payload = app('request')->only('name', 'country_id', 'status_id');

        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        //create item
        $state = $this->model->create($request->all());

        Session::flash('success', 'State successfully created');

        return redirect()->route('states.show', $state->id);
        
    }

    /**
     * @param Request $request
     * @param $uuid
     * @return mixed
     */
    public function update(Request $request, $id)
    {
        
        $user = auth()->user();

        //if user is superadmin, proceed, else, abort
        if ($user->hasRole('superadministrator')){

            $state = $this->model->find($id);

            $rules = [
                'name' => 'required',
                'country_id' => 'required',
                'status_id' => 'required',
            ];

            $payload = app('request')->only('name', 'country_id', 'status_id');

            $validator = app('validator')->make($payload, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            // update fields
            $this->model->updatedata($id, $request->all());

            Session::flash('success', 'Successfully updated state - ' . $state->name);
            //show updated record list
            //return redirect()->route('states.index');
            return redirect()->route('states.show', $state->id);

        } else {

            abort(404);

        }

    }

    /**
     * @param Request $request
     * @param $uuid
     * @return mixed
     */
    public function destroy(Request $request, $uuid)
    {
        $user = $this->model->byUuid($uuid)->firstOrFail();
        $user->delete();

        return $this->response->noContent();
    }
}
