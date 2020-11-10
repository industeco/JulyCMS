<?php

namespace July\Core\Config\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Core\Config\PathAlias;

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
     * @param  \July\Core\Config\PathAlias  $url
     * @return \Illuminate\Http\Response
     */
    public function show(PathAlias $url)
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
     * @param  \July\Core\Config\PathAlias  $url
     * @return \Illuminate\Http\Response
     */
    public function destroy(PathAlias $url)
    {
        //
    }

    /**
     * 检查 url 是否已存在
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function isExist(Request $request)
    {
        $entity = PathAlias::findEntityByAlias($request->input('url'));
        return response([
            'is_exist' => $entity && $entity->getEntityPath() !== $request->input('path'),
        ]);
    }
}
