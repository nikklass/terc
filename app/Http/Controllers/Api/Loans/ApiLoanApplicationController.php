<?php

namespace App\Http\Controllers\Api\Loans;

use App\Entities\Company;
use App\Entities\Group;
use App\Entities\LoanApplication;
use App\Http\Controllers\BaseController;
use App\Services\LoanApplication\LoanApplicationIndex;
use App\Transformers\LoanApplication\LoanApplicationTransformer;
use App\User;
use Carbon\Carbon;
use Dingo\Api\Exception\StoreResourceFailedException;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Session;

class ApiLoanApplicationController extends BaseController
{
    
    /**
     * @var state
     */
    protected $model;

    /**
     * LoanApplicationController constructor.
     *
     * @param LoanApplication $model
     */
    public function __construct(LoanApplication $model)
    {
        $this->model = $model;

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, LoanApplicationIndex $loanApplicationIndex)
    {

        /*start cache settings*/
        $url = request()->url();
        $params = request()->query();
        $fullUrl = getFullCacheUrl($url, $params);
        $minutes = getCacheDuration('low'); 
        /*end cache settings*/

        //get the data
        $data = $loanApplicationIndex->getLoanApplications($request);

        //are we in report mode?
        if (!$request->report) {

            $data = $this->response->paginator($data, new LoanApplicationTransformer());

        } else {

            $data = $data->get();
            $data = $this->response->collection($data, new LoanApplicationTransformer());

        }

        //return api data
        return Cache::remember($fullUrl, $minutes, function () use ($data) {
            return $data;
        });

    }


    /**
     * @param $id
     * @return mixed
    */
    public function show($id)
    {
        $item = $this->model->findOrFail($id);

        return $this->response->item($item, new LoanApplicationTransformer());
    }


    /**
     * @param Request $request
     * @return mixed
    */
    public function store(Request $request)
    {

        $rules = [
            'loan_amt' => 'required',
            'company_id' => 'required',
            'prime_limit_amt' => 'required'
        ];

        $payload = app('request')->only('loan_amt', 'company_id', 'prime_limit_amt');
        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            throw new StoreResourceFailedException($validator->errors());
        }

        //create item
        $this->model->create($request->all());

        return ['message' => 'Loan application created.'];

    }


    /**
     * @param Request $request
     * @param $id
     * @return mixed
    */
    public function update(Request $request, $id)
    {
        
        $user_id = auth()->user()->id;
        $request->merge([
            'updated_by' => $user_id
        ]);

        $item = $this->model->findOrFail($id);

        $rules = [
            'loan_amt' => 'required',
            'company_id' => 'required',
            'prime_limit_amt' => 'required'
        ];

        $payload = app('request')->only('loan_amt', 'company_id', 'prime_limit_amt');
        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            throw new StoreResourceFailedException($validator->errors());
        }
        // update data fields
        $this->model->updatedata($id, $request->all());

        return $this->response->item($item->fresh(), new LoanApplicationTransformer());

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
