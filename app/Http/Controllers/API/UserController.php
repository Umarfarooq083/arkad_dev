<?php

namespace App\Http\Controllers\API;


use App\Mail\ForgotPasswordRequest;
use App\Mail\SendPasswordChanged;
use App\Mail\UserEmails;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Roles;
use App\Models\SecurityQuestion;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\UserSeeder;
use Illuminate\Auth\Authenticatable;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use Spatie\Permission\Guard;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class UserController extends BaseController
{
    use Authenticatable;


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     * Author Omer Asif
     * Sanctum package is used for API.
     */
    public function Login(Request $request)

    {
        $request->validate([
            'user_name' => 'required',
            'password' => 'required',
//            'secrate_question_id'=>'required',
//            'secrate_question_answer'=>'required'
        ]);
        $remember_me = $request->has('remember_me') && $request->remember_me == true ? true : false;
        $credentials = $request->only('user_name', 'password');
        $credentials['status'] = 1;
        try {
            $auth = Auth::attempt($credentials, $remember_me);
            if ($auth) {
                $user = Auth::user();
                if (count($user->tokens)) {
                    $user->tokens->each(function ($token, $key) {
                        $token->delete();
                    });
                }
                $success['token'] = $user->createToken($user->user_name)->plainTextToken;
                $success['name'] = $user->user_name;
                return $this->sendResponse($success, 'User login successfully.');
            } else {
                return $this->sendError('Invalid.', ['error' => 'Invalid user name or password']);
            }
        } catch (\Exception $ex) {
            return $this->sendError('server_error', ['error' => $ex->getMessage()]);
        }

    }

    /**
     * @param Request $request
     * @return \Exception|\Illuminate\Http\JsonResponse
     * Autho: Omer Asif
     * This function can register/create user and send verification email to new user for conformation.
     *
     */
    public
    function Register(Request $request)
    {

        $details = array(
            'email' => $request->email,
            'view' => 'email.welcome_user',
            'data' => $request,
        );
        $null_role = [null];
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'password' => Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
//                ->uncompromised()
            ,
            'user_name' => 'required|unique:users',
            'email' => 'required|unique:users',
//            'role_id.*' => "required",
            'designation_id' => 'required',
            'department_id' => 'required',
            'dob' => 'required',
            'city' => 'required',
            'country' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'employee_code' => 'required',
//            'secrat_question_answer' => 'required'
        ]);


        $current_user = $request->user();
        $created_by = $current_user->id;

        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->password = Hash::make($request->password);
        $user->user_name = $request->user_name;
        $user->employee_code = $request->employee_code;
        $user->email = $request->email;
        $user->designation_id = $request->designation_id;
        $user->department_id = $request->department_id;
        $user->dob = Carbon::createFromFormat('Y-m-d', $request->dob);
        $user->secrat_question_id = isset($request->secrat_question_id) ? $request->secrat_question_id : 0;
        $user->secrate_question_answer = isset($request->secrat_question_answer) ? $request->secrat_question_id : "";
        $user->city = $request->city;
        $user->country = $request->country;
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->created_by = $created_by;
        $user->updated_by = $created_by;
        $user->status = 1;
        $user->save();
//        if ($user->id) {
//                $user->syncRoles($request->role_name);
//            $timestamp = $timestamp = strtotime(date('Y-m-d H:i:s')) + 60 * 60;
//            $email_data = $request->all();
//            $email_data['token'] = Crypt::encrypt($user->id . "->" . $timestamp);
//
//            try {
//                Mail::to($request->email)->send(new UserEmails('email.welcome_user', $email_data));
//            } catch (\Exception $ex) {
//                return $ex;
//            }
//        }
        $roles = Roles::whereIn('name', (array)$request->role_name)->pluck('id');
        $user->roles()->sync($roles);

        return $this->sendResponse($user, 'User created successfully check your email to verify');
    }

    /**
     * @param Request $request
     * @return \Exception|\Illuminate\Http\JsonResponse
     * Autho: Omer Asif
     * This function can register user and send verification email to new user for conformation.
     *
     */
    public
    function Edit(Request $request)
    {

        $request->validate([
            'id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'role_id.*' => "required",
            'designation_id' => 'required',
            'department_id' => 'required',
            'dob' => 'required',
            'city' => 'required',
            'country' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'employee_code' => 'required',
//            'secrat_question_answer' => 'required'
        ]);


        $current_user = $request->user();
        $updated_by = $current_user->id;

        $user = User::with('roles')
            ->with('designation')
            ->with('department')
            ->firstWhere('id', $request['id']);
        if (empty($user)) {
            return $this->sendResponse([], 'No data found');
        }
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->designation_id = $request->designation_id;
        $user->department_id = $request->department_id;
        $user->dob = Carbon::createFromFormat('Y-m-d', $request->dob);
        $user->secrat_question_id = isset($request->secrat_question_id) ? $request->secrat_question_id : 0;
        $user->secrate_question_answer = isset($request->secrat_question_answer) ? $request->secrat_question_id : "";
        $user->city = $request->city;
        $user->country = $request->country;
        $user->address = $request->address;
        $user->employee_code = $request->employee_code;
        $user->phone = $request->phone;
        $user->updated_by = $updated_by;
        $user->status = 1;
        $user->save();
        $roles = Roles::whereIn('name', (array)$request->role_name)->pluck('id');
        $user->roles()->sync($roles);
        return $this->sendResponse($user, 'User data Updated successfully');
    }

    public
    function VerifyUser(Request $request)
    {
        if (isset($request->token) && !empty($request->token)) {
            $user_data = Crypt::decrypt($request->token);
            $token_data = explode('->', $user_data);
            if (strtotime(date('Y-m-d H:i:s')) <= $token_data[1]) {
                $user = User::where('id', '=', $token_data[0])->first();
                $user->email_verified_at = Carbon::now()->format('Y-m-d H:i:s');
                $user->save();
                dd($user);
                return $this->sendResponse($user, 'Verified successfully');
            }
        }
    }

    public
    function LogOut(Request $request)
    {

    }

    public
    function GetUsers(Request $request)
    {
        $request_data = $request->all();
        $per_page = isset($request->per_page) ? $request->per_page : 20;
        $user_list = User::query();
        $user_list->with(['designation' => function ($query) {
            $query->select('id', 'designation_name');
        }]);
        $user_list->with('roles');
        $user_list->with(['department' => function ($query) {
            $query->select('id', 'name');
        }]);

        if (isset($request->s) && !empty($request->s)) {
//            $user_list->where('first_name', 'like', '%' . $request->s . '%');
//            $user_list->orWhere('last_name', 'like', '%' . $request->s . '%');
//            $user_list->orWhere('user_name', 'like', '%' . $request->s . '%');
            $user_list->orWhere('email', 'like', '%' . $request->s . '%');
        }
        if (isset($request->status)) {
            $user_list->where('status', '=', $request->status);
        }
        if (isset($request->role_id)) {
            $user_list->where('designation_id', '=', $request->role_id);
        }
//        if (isset($request->designation_id)) {
//            $user_list->where('designation_id', '=', $request->designation_id);
//        }
        if (isset($request->department_id)) {
            $user_list->where('department_id', '=', $request->department_id);
        }
        $user_list->orderBy('id', 'desc');
        return response()->json($user_list->paginate((int)$per_page,), 200);
    }

    public
    function ChangePasswordSubmit(Request $request)
    {
        $request->validate([
            'old_password' => 'current_password',
//            'secrete_question_id' => 'required',
//            'secrete_question_answer' => 'required',
            'new_password' => ['required', 'confirmed', 'different:password', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
            ]
        ]);
        $user = Auth::user();
        $user_data = User::where('id', '=', $user->id)->first();
        if (Hash::check($request->new_password, $user_data->password)) {
            return $this->sendError(
                'The given data is invalid'
                , array('New password should be different then the old'), 422);
        }
//        if (
//            ($request->secrate_question_id != $user_data->secrat_question_id) &&
//            ($request->secrete_question_answer != $user_data->secrate_question_answer)
//        ) {
//            return $this->sendError(
//                'The given data is invalid'
//                , array('Invalid secrete question data'), 422);
//        }
        $user_data->password = Hash::make($request->new_password);
        $user_data->save();
//        Mail::to($user->email)->send(new SendPasswordChanged($user));
        return $this->sendResponse($user, 'Password updated successfully');
    }


    public
    function GetFilterData(Request $request)
    {
        $data = array();
        $data['designation'] = Designation::select('id', 'designation_name')->where('status', '=', 1)->get();
        return $data;
    }

    public
    function GetRegisteration(Request $request)
    {
        try {
            $data = array();
            $data['roles'] = Roles::select('id', 'name')->get();
            $data['departments'] = Department::select('id', 'name')->where('status', '=', 1)->get();
            $data['designation'] = Designation::select('id', 'designation_name')->where('status', '=', 1)->get();
            $data['secrate_questions'] = SecurityQuestion::select('id', 'question')->where('status', '=', 1)->get();
//            $data['countries']= Country::select('id','name')->get();
            return $data;
        } catch (\Exception $ex) {
            return $ex;
        }

    }

    public
    function ForgotPasswordRequest(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'secrate_question_id' => 'required|numeric',
            'secrate_question_answer' => 'required'
        ]);
        $user = User::where('email', '=', $request->email)->first();
        if (
            ($request->secrate_question_id == $user->secrat_question_id)
            &&
            ($request->secrate_question_answer == $user->secrate_question_answer)
        ) {
            $timestamp = strtotime(date('Y-m-d H:i:s')) + 60 * 60;
            $toke_string = $user->id . ':' . $user->user_name . ':' . $timestamp;
            $token = Crypt::encryptString($toke_string);

            $url = "https://arkad.viltco.com/admin/reset-password/?token=$token";
            $data = array('user_data' => $user, 'url' => $url);
            try {
                Mail::to($user->email)->send(new ForgotPasswordRequest($data));
            } catch (\Exception $ex) {

            }
            return $this->sendResponse(array(), 'A reset password links is sent to you email please open the link and rest you password');
        } else {
            return $this->sendError('Invalid user', [], 401);
        }

    }

    public
    function ChangeForgotPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols()
            ],
            'password_confirm' => [Password::min(8)]
        ]);
        $token_data = explode(':', Crypt::decryptString($request->token));

        $issue_time = $token_data[2];
        $valid_till = $token_data[2] + 600;
        if ($valid_till >= $issue_time) {
            $user = User::where('id', '=', $token_data[0])->first();
            $user->password = Hash::make($request->password);
            return $this->sendResponse($user, 'Your password changed successfully');
        } else {
            return $this->sendError('Token expire', [], 401);
        }
    }

    public
    function GetProfile()
    {
        $user_id = Auth::user()->getAuthIdentifier();
        $profile_data = User::where('id', '=', $user_id)
            ->with(['roles' => function ($query) {
                $query->select('roles.id', 'roles.name');
            }])
            ->with('designation')
            ->with('department')
            ->first();
        return $this->sendResponse($profile_data, 'Your profile');
    }

    public function GetUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required | numeric'
        ]);

        $profile_data = User::where('id', '=', $request->user_id)
            ->with('roles')
            ->with('designation')
            ->with('department')
            ->first();
        if ($profile_data) {
            return $this->sendResponse($profile_data, 'User Data');
        } else {
            return $this->sendError('User not found', [], 404);
        }
    }

    public function AddRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles'
        ]);
        $role = Role::create(['name' => $request->name, 'guard_name' => 'api']);
        return $this->sendResponse($role, 'Role created');
    }

    public function ChangeStatus(Request $request)
    {
        $request->validate([
            'status' => 'required',
            'user_id' => 'required'
        ]);

        $user = User::find($request->user_id);
        $user->status = $request->status;
        $user->save();
        return $user->where('id', '=', $request->user_id)->first();
    }

    public
    function GetAllPermissions(Request $request)
    {
        return Permission::all();
    }

    public
    function GivePermissionToRole(Request $request)
    {
        $request->validate([
            'role_name' => 'required',
            'permissions.*' => 'required'

        ]);

        $role = Role::findByName($request->role_name, 'api');
        $role->syncPermissions($request->permissions);
        return $role->getPermissionNames();
    }

    public
    function AddSecurityQuestion(Request $request)
    {
//        return $request;
        $request->validate([
            'secrat_question_id' => 'required | numeric',
            'secrate_question_answer' => 'required'
        ]);
        $data = $request->all();
        $user = Auth::user()->getAuthIdentifier();
        DB::connection()->enableQueryLog();
        $user = User::where('id', '=', 10)->first();
        $user->secrat_question_id = $data['secrat_question_id'];
        $user->secrate_question_answer = $data['secrate_question_answer'];
        $user->save();

        return $this->sendResponse($user, 'Your security added successfully');

    }

    function GetSecurityQuestions()
    {
        return SecurityQuestion::all();
    }
}
