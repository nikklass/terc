<?php

namespace App\Http\Controllers\Api\GlobalAltar;

use App\Entities\Group;
use App\Entities\GlobalAltar;
use App\Http\Controllers\BaseController;
use App\Services\GlobalAltar\GlobalAltarIndex;
use App\Transformers\GlobalAltar\GlobalAltarTransformer;
use App\User;
use Carbon\Carbon;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Session;

class ApiGlobalAltarController extends BaseController
{

    /**
     * @var GlobalAltar
     */
    protected $model;

    /**
     * GlobalAltarController constructor.
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

        //are we in report mode?
        if (!$request->report) {

            $data = $this->response->paginator($data, new GlobalAltarTransformer());

        } else {

            $data = $data->get();
            $data = $this->response->collection($data, new GlobalAltarTransformer());

        }

        return $data;

    }


    /**
     * @param $id
     * @return mixed
    */
    public function show($id)
    {
        $item = $this->model->findOrFail($id);

        return $this->response->item($item, new GlobalAltarTransformer());
    }


    /**
     * @param Request $request
     * @return mixed
    */
    public function store(Request $request)
    {

        $rules = [
            'start_at' => 'required'
        ];

        $payload = app('request')->only('start_at');
        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            throw new StoreResourceFailedException($validator->errors());
        }

        //create item
        $this->model->create($request->all());

        return ['message' => 'Product created.'];

    }


    /**
     * @param Request $request
     * @param $id
     * @return mixed
    */
    public function update(Request $request, $id)
    {

        $item = $this->model->findOrFail($id);

        $rules = [
            'start_at' => 'required'
        ];

        $payload = app('request')->only('start_at');
        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            throw new StoreResourceFailedException($validator->errors());
        }
        // update data fields
        $this->model->updatedata($id, $request->all());

        return $this->response->item($item->fresh(), new GlobalAltarTransformer());

    }


    /**
     * @param Request $request
     * @param $id
     * @return mixed
    */
    public function destroy(Request $request, $id)
    {

        $user_id = auth()->user()->id;

        $item = $this->model->findOrFail($id);

        if ($item) {
            //update deleted by field
            $item->update(['deleted_by' => $user_id]);
            $result = $item->delete();
        }

        return $this->response->noContent();
    }

}
