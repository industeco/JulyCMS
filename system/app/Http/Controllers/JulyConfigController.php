<?php

namespace App\Http\Controllers;

use App\Models\JulyConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JulyConfigController extends Controller
{
    /**
     * 编辑网站基本设置
     *
     * @return \Illuminate\Http\Response
     */
    public function editBasicSettings()
    {
        return view_with_langcode('admin::config.edit_basic', [
            'settings' => JulyConfig::getBasicSettings(),
        ]);
    }

    /**
     * 更新网站基本设置
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateBasicSettings(Request $request)
    {
        Log::info($request->all());

        $changed = $request->only($request->input('_changed'));
        JulyConfig::updateConfiguration($changed);

        return response('');
    }

    /**
     * 编辑语言设置
     *
     * @return \Illuminate\Http\Response
     */
    public function editLanguageSettings()
    {
        return view_with_langcode('admin::config.edit_language', [
            'settings' => JulyConfig::getLanguageSettings(),
        ]);
    }

    /**
     * 更新语言设置
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateLanguageSettings(Request $request)
    {
        Log::info($request->all());

        $changed = $request->only($request->input('_changed'));
        JulyConfig::updateConfiguration($changed);

        return response('');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Config  $config
     * @return \Illuminate\Http\Response
     */
    public function show(JulyConfig $config)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Config  $config
     * @return \Illuminate\Http\Response
     */
    public function edit(JulyConfig $config)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Config  $config
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, JulyConfig $config)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Config  $config
     * @return \Illuminate\Http\Response
     */
    public function destroy(JulyConfig $config)
    {
        //
    }
}
