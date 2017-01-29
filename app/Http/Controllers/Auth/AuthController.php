<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Nova\NovaController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends NovaController
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * When a user is not successfully authenticated, they will be redirected to the /auth/login.
     *
     * @var string
     */
    protected $loginPath = '/auth/login';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware($this->guestMiddleware(), ['except' => ['logout','getLogout']]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|max:255',
            'lastname' => 'required',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8|confirmed',
            'role'    => 'required'
        ]);
    }

    public function authenticated(Request $request, $user){
        $user=\Auth::guard('web')->user();
        \Auth::guard($this->getGuard())->logout();
        $this->novaMessage->setRoute('/');
        $this->novaMessage->addSuccesMessage('authentication','User was logged successfully');
        $this->novaMessage->setData(['token'=>$user->api_token]);
        return $this->returnJSONMessage();
    }

    /**
     * Get the failed login response instance.
     *
     * @param \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function sendFailedLoginResponse(Request $request)
    {
        $this->novaMessage->setRoute('/auth/login');
        $this->novaMessage->addErrorMessage($this->loginUsername(),$this->getFailedLoginMessage());
        return $this->returnJSONMessage(401);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return string|null
     */
    protected function getGuard()
    {
        //return property_exists($this, 'guard') ? $this->guard : null;
        return 'web';
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        \Auth::guard($this->getGuard())->logout();
        $this->novaMessage->setRoute('/auth/login');
        return $this->returnJSONMessage(200);
    }

}
