<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Settings\SettingsManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
        if ($group = SettingsManager::resolve($group)) {
            return $group->view();
        }
        abort(404);
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
        if ($group = SettingsManager::resolve($group)) {
            $group->save($request->all());
            return response('');
        }
        abort(404);
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
}
