<?php

namespace Installer\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Installer\Installer;

class InstallController extends Controller
{
    /**
     * 安装步骤 0：显示安装界面
     *
     * @return \Illuminate\View\View
     */
    public function home()
    {
        return view('installer::install', [
            'requirements' => Installer::checkRequirements(),
        ]);
    }

    /**
     * 安装步骤 1：更新 .env 文件
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function install(Request $request)
    {
        Installer::prepareDatabase($request->input('db_database'));
        Installer::updateEnv($request->all());
        return response('');
    }

    /**
     * 安装步骤 2：执行数据库迁移
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function migrate(Request $request)
    {
        Installer::migrate();
        return response('');
    }
}
