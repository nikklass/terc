<?php

namespace App\Http\Controllers\Web\Loans;

use App\Entities\Company;
use App\Entities\DepositAccount;
use App\Entities\Group;
use App\Entities\LoanApplication;
use App\Entities\Product;
use App\Entities\Term;
use App\Http\Controllers\Controller;
use App\Services\LoanApplication\LoanApplicationIndex;
use App\User;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Session;

class LoanApplicationController extends Controller
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

        //are we in report mode? return get results
        if ($request->report) {

            $data = $data->get();

        }

        //return cached data or cache if cached data not exists
       // return Cache::remember($fullUrl, $minutes, function () use ($data) {
            return view('loans.loan-applications.index', [
                'loanapplications' => $data->appends(Input::except('page'))
            ]);
       // });

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        //get logged in user
        $user = auth()->user();


        //if user is superadmin, show all companies, else show a user's companies
        $companies = [];

        if ($user->hasRole('superadministrator')) {
            $companies = Company::all();
        } else if ($user->hasRole('administrator')) {
            if ($user->company) {
                $companies[] = $user->company;
            }
        } else {
            //get user companies i.e. where user has deposit accounts
            $company_ids = DepositAccount::where('primary_user_id', $user->id)->pluck('company_id');
            //dd($company_ids);
            $companies = Company::whereIn('id', $company_ids)->get();
            //dd($companies);
        }

        return view('loans.loan-applications.create', [
                'companies' => $companies
            ]);

    }


    /**
     * Show the form for creating a new resource - step 2.
     */
    public function create_step2(Request $request)
    {

        //dd($request);
        //get more data
        $rules = [
            'company_id' => 'required',
        ];
        $payload = app('request')->only('company_id');
        $validator = app('validator')->make($payload, $rules);
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        //get logged in user
        $user = auth()->user();

        //if user is superadmin, show all companies, else show a user's companies
        $company = Company::find($request->company_id);
        $products = Product::where('product_cat_ty', 'LN')
                    ->where('status_id', '1')
                    ->get();
        $terms = Term::where('status_id', '1')->get();
        
        $deposit_accounts = [];

        if (($user->hasRole('superadministrator')) || ($user->hasRole('administrator'))) {
            //get user deposit accounts
            $deposit_accounts = DepositAccount::where('company_id', $company->id)->get();
        } else {
            //fetch current user accounts
            $deposit_accounts = DepositAccount::where('company_id', $company->id)
                                ->where('primary_user_id', $user->id)
                                ->get();
        }

        return view('loans.loan-applications.create2', [
                'company' => $company,
                'terms' => $terms,
                'products' => $products,
                'deposit_accounts' => $deposit_accounts
            ]);

    }


    /**
     * Show the form for approving a new loan application
     */
    public function create_approve($id)
    {

        //get logged in user
        $user = auth()->user();
        $loanapplication = "";

        if ($user->hasRole('superadministrator')) {
            //if user is superadmin, show 
            $loanapplication = $this->model->find($id);
        } else if ($user->hasRole('administrator')){
            //if user is not superadmin, show only from user company
            $loanapplication = $this->model->where('company_id', $user->company->id)
                                ->where('id', $id)
                                ->first();
        }

        if ($loanapplication) {
            $products = Product::where('product_cat_ty', 'LN')
                        ->where('status_id', '1')
                        ->get();
            $terms = Term::where('status_id', '1')->get();

            return view('loans.loan-applications.approve', [
                    'loanapplication' => $loanapplication,
                    'terms' => $terms,
                    'products' => $products
                ]);
        } else {
            abort(404);
        }

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 

        //dd($request);

        //get loan application user
        $deposit_account_id = $request->deposit_account_id;
        $deposit_account = DepositAccount::find($deposit_account_id);
        $request->merge([
            'user_id' => $deposit_account->primary_user_id
        ]);

        //get more data
        $rules = [
            'user_id' => 'required',
            'loan_amt' => 'required',
            'company_id' => 'required',
            'prime_limit_amt' => 'required'
        ];

        $payload = app('request')->only('user_id', 'loan_amt', 'company_id', 'prime_limit_amt');

        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        //create item
        $loanapplication = $this->model->create($request->all());

        Session::flash('success', 'Loan application successfully created');
        
        return redirect()->route('loan-applications.index');

    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $loanapplication = $this->model->where('id', $id)->first();

        $user = auth()->user();
        //if user is superadmin, show all companies, else show a user's companies
        if ($user->hasRole('superadministrator')){
            $companies = Company::all();
        } else {
            $companies = $user->company;
        }

        return view('loans.loan-applications.edit', compact('loanapplication', 'companies'));

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
        
        $rules = [
            'user_id' => 'required',
            'loan_amt' => 'required',
            'company_id' => 'required',
            'prime_limit_amt' => 'required'
        ];

        $payload = app('request')->only('user_id', 'loan_amt', 'company_id', 'prime_limit_amt');

        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        // update fields
        $this->model->updatedata($id, $request->all());

        Session::flash('success', 'Successfully updated loan application - ' . $id);
        //show updated record
        return redirect()->route('loan-applications.show', $id);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update_approve(Request $request, $id)
    {

        $rules = [
            'approved_loan_amt' => 'required',
            'approved_term_id' => 'required',
            'approved_term_value' => 'required'
        ];

        $payload = app('request')->only('approved_loan_amt', 'approved_term_id', 'approved_term_value');

        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        // update fields
        $this->model->updatedata($id, $request->all());
        //flash a session message
        Session::flash('success', 'Successfully updated loan application - ' . $id);
        //show updated record
        return redirect()->route('loan-applications.show', $id);

    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {

        //get details for this ussd reg
        $loanapplication = $this->model->find($id);
        
        return view('loans.loan-applications.show', compact('loanapplication'));

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
