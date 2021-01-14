<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use July\Core\Config\Config as JulyConfig;

class SettingsController extends Controller
{
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
     * @param  \July\Core\Config\Config  $config
     * @return \Illuminate\Http\Response
     */
    public function show(JulyConfig $config)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $group
     * @return \Illuminate\Http\Response
     */
    public function edit($group)
    {
        // dd(Auth::guard('admin')->user());
        // dd(JulyConfig::getConfigsByGroup($group));
        return view_with_langcode('backend::config.'.$group, [
            'configs' => JulyConfig::getConfigsByGroup($group),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $changed = Arr::only($request->all(), array_values($request->input('_changed')));
        JulyConfig::updateConfigs($changed);

        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Core\Config\Config  $config
     * @return \Illuminate\Http\Response
     */
    public function destroy(JulyConfig $config)
    {
        //
    }
}
