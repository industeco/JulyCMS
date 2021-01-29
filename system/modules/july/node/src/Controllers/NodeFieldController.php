<?php

namespace July\Node\Controllers;

use App\Http\Controllers\Controller;
use July\Node\NodeField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Node\Node;

class NodeFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response(NodeField::index());
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
        NodeField::create($request->all());

        return response('');
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Node\NodeField  $field
     * @return \Illuminate\Http\Response
     */
    public function show(NodeField $field)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \July\Node\NodeField  $field
     * @return \Illuminate\Http\Response
     */
    public function edit(NodeField $field)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Node\NodeField  $field
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NodeField $field)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Node\NodeField  $field
     * @return \Illuminate\Http\Response
     */
    public function destroy(NodeField $field)
    {
        //
    }

    /**
     * 检查字段真名是否存在
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function exists(string $id)
    {
        // 保留的字段名
        $reserved = array_merge(
            // 实体的固有属性
            Node::getModelFillable(),
            ['id', 'updated_at', 'created_at'],

            // 实体的动态属性（关联）
            ['fields', 'mold'],

            // 动态表中用到的，或可能会用到的
            ['entity_id', 'entity_name']
        );

        return response([
            'exists' => in_array($id, $reserved) || !empty(NodeField::find($id)),
        ]);
    }
}
