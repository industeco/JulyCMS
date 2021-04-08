<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
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
        if ($group = app()->make('settings.'.$group)) {
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
        if ($group = app()->make('settings.'.$group)) {
            $group->save($request->all());
            return response('');
        }

        abort(404);
    }
}
