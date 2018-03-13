<?php

namespace App\Http\Controllers\Api\Users;

use App\Entities\ConfirmCode;
use App\Http\Controllers\BaseController;
use App\Services\User\UserIndex;
use App\Services\User\UserStore;
use App\Transformers\Users\UserTransformer;
use App\User;
use Dingo\Api\Exception\StoreResourceFailedException;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class ApiUsersController
 * @package App\Http\Controllers\Users
 */
class ApiUsersController extends BaseController
{

    /**
     * @var User
     */
    protected $model;

    /**
     * UsersController constructor.
     * @param User $model
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Returns the Users resource with the roles relation
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request, UserIndex $userIndex)
    {
        
        //get the data
        $data = $userIndex->getUsers($request);

        //are we in report mode?
        if (!$request->report) {

            $data = $this->response->paginator($data, new UserTransformer());

        } else {

            $data = $data->get();
            $data = $this->response->collection($data, new UserTransformer());

        }

        return $data;

    }


    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $user = $this->model->with('roles.permissions')->byUuid($id)->firstOrFail();
        return $this->response->item($user, new UserTransformer());
    }

    /**
     * get logged in user
     */
    public function loggeduser()
    {
        $user = auth()->user();
        return $this->response->item($user, new UserTransformer());
    }

    /**
     * confirm an account
     * @param Request $request
     * @return mixed
     */
    public function accountconfirm(Request $request)
    {

        $rules = [
            'phone_country' => 'required_with:phone',
            'phone' => 'required',
            //'test'=> 'sometimes|nullable|min:6'
            'confirm_code' => 'required',
        ];

        $payload = app('request')->only('confirm_code', 'phone', 'phone_country');

        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            throw new StoreResourceFailedException($error_messages);
        }

        //set variables
        $status_active = config('constants.status.active');
        $status_disabled = config('constants.status.disabled');

        $phone_country = $request->phone_country;
        $phone = $request->phone;
        $confirm_code = $request->confirm_code;
        $email = '';
        $local_phone = '';
        
        //DB::enableQueryLog();

        //check whether entry is an email or not
        // check login field
        $login_type = filter_var( $phone, FILTER_VALIDATE_EMAIL ) ? 'email' : 'phone';
        //dump($login_type);

        if ($login_type == 'email') {
            $email = $phone;
        } else {
            //get user account if active, join to confirm_codes table where status_id = 1
            $local_phone = getLocalisedPhoneNumber($phone, $phone_country);
            $full_phone = getDatabasePhoneNumber($phone, $phone_country);
        }
        
        $user = DB::table('users')
                        ->when($local_phone, function ($query) use ($local_phone, $phone_country) {
                            $query->where('users.phone', $local_phone)
                                  ->where('users.phone_country', $phone_country);
                        }, function ($query) use ($email) {
                            $query->where('users.email', $email);
                        })
                        ->first();

        if (!$user) {

            $error_message['user'] = 'User account does not exist';
            $error_message = json_encode($error_message);

            throw new StoreResourceFailedException($error_message);

        } else {

            if ($user->active == 1) {

                $error_message['user'] = 'User account is already active';
                $error_message = json_encode($error_message);

                throw new StoreResourceFailedException($error_message);

            } 

            //check if supplied code is active
            $code_data = ConfirmCode::where('confirm_code', '=', $confirm_code)
                            ->where('status_id', '=', $status_active)
                            ->when($local_phone, function ($query) use ($full_phone, $phone_country) {
                                $query->where('phone', $full_phone)
                                      ->where('phone_country', $phone_country);
                            }, function ($query) use ($email) {
                                $query->where('email', $email);
                            })
                            ->first();

            dd($email, $local_phone, $code_data);

            if (!$code_data) {

                $error_message['user'] = 'Invalid confirmation code';
                $error_message = json_encode($error_message);

                throw new StoreResourceFailedException($error_message);

            } 
            //end check if supplied code is active

            //update the user record
            DB::table('users')
                ->when($local_phone, function ($query) use ($local_phone, $phone_country) {
                    $query->where('phone', $local_phone)
                          ->where('phone_country', $phone_country);
                }, function ($query) use ($email) {
                    $query->where('email', $email);
                })
                ->update(['status_id' => $status_active, 'active' => '1']);

            //update the confirm codes record, set to disabled
            $update_confirm_code = DB::table('confirm_codes')
                ->where('confirm_code', '=', $confirm_code)
                ->when($local_phone, function ($query) use ($full_phone, $phone_country) {
                    $query->where('phone', $full_phone)
                          ->where('phone_country', $phone_country);
                }, function ($query) use ($email) {
                    $query->where('email', $email);
                })
                ->update(['status_id' => $status_disabled]);

            //print_r(DB::getQueryLog());

            //$message = 'Welcome. Your account successfully confirmed.';

            //return ['message' => $message];
            $user = $this->model->where('phone', $local_phone)
                                ->where('phone_country', $phone_country)
                                ->first();
                                
            return $this->response->item($user, new UserTransformer)->setStatusCode(200);

        }

    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request, UserStore $userStore)
    {

        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone_country' => 'required_with:phone',
            'phone' => 'required|phone:mobile|unique:users,phone',
            'email' => 'sometimes|nullable|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            //'company_id' => 'required|integer',
        ];

        $payload = app('request')->only('first_name', 'last_name', 'phone', 'phone_country', 'email', 'password', 'password_confirmation');
        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            throw new StoreResourceFailedException($validator->errors());
        }

        //$user = $this->model->create($request->all());
        $user = $userStore->createItem($request->all());
        if ($request->has('roles')) {
            $user->syncRoles($request['roles']);
        }
        //return $this->response->created();
        return ['message' => 'User created.'];
    }


    /**
     * @param Request $request
     * @param $uuid
     * @return mixed
     */
    public function update(Request $request, $uuid)
    {
        
        $user = $this->model->byUuid($uuid)->firstOrFail();
        
        $rules = [
            'first_name' => 'required',
            'last_name' => 'required',
            'phone' => 'required|phone:KE,mobile',
            'email' => 'sometimes|nullable|email|unique:users,email,'.$user->id,
        ];

        $payload = app('request')->only('first_name', 'last_name', 'phone', 'email');
        $validator = app('validator')->make($payload, $rules);

        if ($validator->fails()) {
            throw new StoreResourceFailedException($validator->errors());
        }

        // Except password as we don't want to let the users change a password from this endpoint
        $user->update($request->except('_token', 'password'));
        if ($request->has('roles')) {
            $user->syncRoles($request['roles']);
        }
        return $this->response->item($user->fresh(), new UserTransformer());

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
