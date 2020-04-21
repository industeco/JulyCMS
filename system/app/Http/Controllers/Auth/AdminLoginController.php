<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AdminLoginController extends Controller
{
    //

    /**
     * 后端首页
     */
    public function home()
    {
        return view('admin::home');
    }

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function showLoginForm()
    {
        if ($this->guard()->check()) {
            return redirect($this->adminHome());
        }

        return view('admin::login');
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function login(Request $request)
    {
        $this->loginValidator($request->all())->validate();

        $credentials = $request->only([$this->username(), 'password']);
        $remember = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            'login' => '用户名或密码错误',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect($this->adminHome());
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username()   => 'required',
            'password'          => 'required',
        ]);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        return redirect()->intended($this->adminHome());
    }

    protected function adminHome()
    {
        return route('admin.home');
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'truename';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('admin');
    }
}
