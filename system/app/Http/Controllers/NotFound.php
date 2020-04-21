<?php

namespace App\Http\Controllers;

class NotFound extends Controller
{
    /**
     * 返回首页
     *
     * @return View
     */
    public function __invoke()
    {
        abort(404);
    }
}
