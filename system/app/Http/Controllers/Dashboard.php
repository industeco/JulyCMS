<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Dashboard extends Controller
{
    /**
     * 返回首页
     *
     * @return View
     */
    public function __invoke()
    {
        return view('home');
    }
}
