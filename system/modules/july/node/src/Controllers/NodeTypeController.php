<?php

namespace July\Node\Controllers;

use App\EntityField\FieldTypes\FieldTypeManager;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Node\NodeField;
use July\Node\NodeType;

class NodeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('node::node_type.index', [
            'models' => NodeType::index(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $fields = NodeField::classify();
        $data = [
            'model' => NodeType::template(),
            'context' => [
                'fields' => $fields['preseted'],
                'optional_fields' => $fields['optional'],
                'field_template' => NodeField::template(),
                'content_langcode' => langcode('content'),
            ],
        ];

        return view('node::node_type.create-edit', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \July\Node\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function edit(NodeType $nodeType)
    {
        $fields = NodeField::classify();
        $data = [
            'model' => $nodeType->gather(),
            'context' => [
                'fields' => $nodeType->gatherFields()->all(),
                'optional_fields' => $fields['optional'],
                'field_template' => NodeField::template(),
                'content_langcode' => langcode('content'),
            ],
        ];

        return view('node::node_type.create-edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 创建类型
        NodeType::create($request->all());

        return response('');
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Node\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function show(NodeType $nodeType)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Node\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NodeType $nodeType)
    {
        // 更新类型
        $nodeType->update($request->all());

        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Node\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function destroy(NodeType $nodeType)
    {
        $nodeType->delete();

        return response('');
    }

    /**
     * 检查主键是否重复
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function exists($id)
    {
        return response([
            'exists' => !empty(NodeType::find($id)),
        ]);
    }
}
