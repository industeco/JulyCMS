<?php

namespace App\Http\Controllers;

class Dashboard extends Controller
{
    /**
     * 返回首页
     *
     * @return View
     */
    public function __invoke()
    {
        return view('admin::home');
    }
}
