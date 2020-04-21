<?php

namespace App\Http\Controllers;

use App\Models\NodeField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class NodeFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Response::make(describe(NodeField::all()));
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
        $field = NodeField::create(NodeField::prepareRequest($request));
        return Response::make($field);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NodeField  $nodeField
     * @return \Illuminate\Http\Response
     */
    public function show(NodeField $nodeField)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NodeField  $nodeField
     * @return \Illuminate\Http\Response
     */
    public function edit(NodeField $nodeField)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NodeField  $nodeField
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NodeField $nodeField)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NodeField  $nodeField
     * @return \Illuminate\Http\Response
     */
    public function destroy(NodeField $nodeField)
    {
        //
    }

    /**
     * 检查 truename 是否已存在
     *
     * @param  string  $truename
     * @return \Illuminate\Http\Response
     */
    public function unique($truename)
    {
        $reserved = [
            'id', 'is_preset', 'is_searchable', 'node_type', 'langcode',
            'node_id', 'delta', 'updated_at', 'created_at',
            'truename', 'title', 'tags', 'catalogs',
            'url', 'template', 'meta_title', 'meta_description', 'meta_keywords',
        ];
        return Response::make([
            'exists' => in_array($truename, $reserved) || !empty(NodeField::find($truename)),
        ]);
    }

    /**
     * 检查 url 是否已存在
     *
     * @param  string  $url
     * @return \Illuminate\Http\Response
     */
    public function uniqueUrl(Request $request)
    {
        $langcode = $request->input('content_value_lang');
        $url = $request->input('url');

        $condition = [
            ['url_value', '=', $url],
            ['langcode', '=', $langcode],
        ];
        if ($id = (int) $request->input('id')) {
            $condition[] = ['node_id', '!=', $id];
        }

        $result = DB::table('node__url')->where($condition)->first();
        return Response::make([
            'exists' => !empty($result),
        ]);
    }
}
