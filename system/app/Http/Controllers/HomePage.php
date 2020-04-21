<?php

namespace App\Http\Controllers;

class HomePage extends Controller
{
    /**
     * 返回首页
     *
     * @return View
     */
    public function __invoke()
    {
        return view('welcome');
    }
}
