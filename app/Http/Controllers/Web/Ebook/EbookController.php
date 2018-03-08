<?php

namespace App\Http\Controllers\Web\Ebook;

use App\Entities\Country;
use App\Entities\Group;
use App\Entities\Ebook;
use App\Entities\Status;
use App\Http\Controllers\Controller;
use App\Services\Ebook\EbookIndex;
use App\User;
use Carbon\Carbon;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;
use Session;

class EbookController extends Controller
{
    
    protected $model;

    /**
     *  constructor.
     *
     * @param Ebook $model
     */
    public function __construct(Ebook $model)
    {
        $this->model = $model;

    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, EbookIndex $ebookIndex)
    {

        //get the data
        $data = $ebookIndex->getEbooks($request);

        //are we in report mode? return get results
        if ($request->report) {

            $data = $data->get();

        }

        return view('admin.manage.ebooks.index', [
            'ebooks' => $data->appends(Input::except('page'))
        ]);

    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $logged_user = auth()->user();
        $statuses = Status::all();
        return view('admin.manage.ebooks.create', compact('statuses', 'logged_user'));
 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { 

        $rules = [
            'title' => 'required',
            'uploadfile' => 'required|mimes:pdf',
        ];

        $payload = app('request')->only('title', 'uploadfile');

        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator->errors());
        }

        //upload pdf
        $extension = $request->file('uploadfile')->getClientOriginalExtension();
        $filename = uniqid();
        //$filename .= $request->file('uploadfile')->getClientOriginalName();
        $filename = str_slug($filename) . '.' . $extension;

        $request->file('uploadfile')->move(
            base_path() . '/files/ebooks/', $filename
        );
        //end upload file

        //get full pdf path
        $fullPath = 'files/ebooks/' . $filename;

        //merge src to request
        $request->merge([
            'src' => $fullPath
        ]);

        //create item
        $ebook = $this->model->create($request->only('title', 'description', 'src'));

        Session::flash('success', 'Ebook successfully created');

        return redirect()->route('ebooks.index');
        
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

            $ebook = $this->model->find($id);

            if ($ebook->phone) {
                $phone = getLocalisedPhoneNumber($ebook->phone, $ebook->phone_country);
                $ebook->phone = $phone;
            }

            //dd($ebook);
            $statuses = Status::all();
            $countries = Country::all();

            return view('admin.manage.ebooks.edit', compact('ebook', 'countries', 'statuses'));

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
        
        //dd($request);

        $user = auth()->user();

        //if user is superadmin, proceed, else, abort
        if ($user->hasRole('superadministrator')){

            $ebook = $this->model->find($id);

            $rules = [
                'title' => 'required',
            ];

            $payload = app('request')->only('title');

            $validator = app('validator')->make($payload, $rules);

            if ($validator->fails()) {
                return redirect()->back()->withInput()->withErrors($validator->errors());
            }

            if ($request->uploadfile)
            {
                //upload pdf
                $extension = $request->file('uploadfile')->getClientOriginalExtension();
                $filename = uniqid();
                //$filename .= $request->file('uploadfile')->getClientOriginalName();
                $filename = str_slug($filename) . '.' . $extension;

                //delete current ebook pdf
                unlink(base_path() . '/public/' . $ebook->src);
                //dd($filename, $ebook->src);

                $request->file('uploadfile')->move(
                    base_path() . '/public/files/ebooks/', $filename
                );
                //end upload file

                //get full pdf path
                $fullPath = 'files/ebooks/' . $filename;

                //merge src to request
                $request->merge([
                    'src' => $fullPath
                ]);
            }

            // update fields
            $this->model->updatedata($id, $request->all());

            Session::flash('success', 'Successfully updated Ebook - ' . $ebook->id);
            //show updated record
            return redirect()->route('ebooks.show', $ebook->id);

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
        $ebook = $this->model->find($id);

        if ($ebook->phone) {
            $phone = getDatabasePhoneNumber($ebook->phone, $ebook->phone_country);
            $ebook->phone = $phone;
        }
        
        return view('admin.manage.ebooks.show', compact('ebook'));

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

        return redirect()->route('ebooks.index');
    }

}
