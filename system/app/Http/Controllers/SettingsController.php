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
     * @param  string $group
     * @return \Illuminate\Http\Response
     */
    public function show(string $group)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $group
     * @return \Illuminate\Http\Response
     */
    public function edit(string $group)
    {
        $settings = $this->findSettings($group);
        return view_with_langcode('backend::config.'.$group, [
            'configs' => JulyConfig::getConfigsByGroup($group),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $group)
    {
        $this->findSettings($group)->save($request->all());
        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $group)
    {
        //
    }

    /**
     * 根据配置组名称查找配置组
     *
     * @param  string $name
     * @return \App\Settings\SettingsBase|null
     */
    public function findSettings(string $name)
    {
        foreach (config('app.settings') as $class) {
            if (class_exists($class)) {
                # code...
                $settings = new $class;
                if ($settings->getName() === $name) {
                    return $settings;
                }
            }
        }

        return null;
    }
}
