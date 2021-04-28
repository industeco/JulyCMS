<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\EntityValue\EntityPathAlias;

class PathAliasController extends Controller
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
     * @param  \App\EntityValue\EntityPathAlias  $url
     * @return \Illuminate\Http\Response
     */
    public function show(EntityPathAlias $url)
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\EntityValue\EntityPathAlias  $url
     * @return \Illuminate\Http\Response
     */
    public function destroy(EntityPathAlias $url)
    {
        //
    }

    /**
     * 检查 url 是否已存在
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function exists(Request $request)
    {
        $value = $request->input('value');
        if ($except = $request->input('except')) {
            [$entityName, $entityId] = explode('/', $except);

            $first = EntityPathAlias::ofAlias($value)->where([
                ['entity_name', '!=', $entityName, 'and'],
                ['entity_id', '!=', $entityId, 'and'],
            ])->first();
        } else {
            $first = EntityPathAlias::ofAlias($value)->first();
        }

        return response([
            'exists' => !is_null($first),
        ]);
    }
}
