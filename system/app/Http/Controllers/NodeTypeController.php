<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\NodeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\NodeField;

class NodeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $nodeCount = Node::countByNodeType();
        $nodeTypes = NodeType::columns()->all();
        foreach ($nodeTypes as &$nodeType) {
            $nodeType['nodes'] = $nodeCount[$nodeType['truename']] ?? 0;
        }

        return view_with_langcode('admin::node_types.index', [
            'nodeTypes' => $nodeTypes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $requiredFiels = [
            NodeField::find('title')->gather(),
        ];

        $optionalFields = NodeField::where([
            'is_preset' => false,
            'is_global' => false,
        ])->get()->map(function($field) {
            return $field->gather();
        })->all();

        return view_with_langcode('admin::node_types.create_edit', [
            'truename' => '',
            'name' => '',
            'description' => '',
            'fields' => $requiredFiels,
            'availableFields' => $optionalFields,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $nodeType = NodeType::make($request->all());
        $nodeType->save();
        $nodeType->updateFields($request);
        return Response::make($nodeType);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function show(NodeType $nodeType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function edit(NodeType $nodeType)
    {
        $data = $nodeType->toArray();
        $data['fields'] = $nodeType->cacheGetFields();

        $data['availableFields'] = NodeField::where([
            'is_preset' => false,
            'is_global' => false,
        ])->get()->map(function($field) {
            return $field->gather();
        })->all();

        return view_with_langcode('admin::node_types.create_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NodeType $nodeType)
    {
        $nodeType->update($request->all());
        $nodeType->updateFields($request);
        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function destroy(NodeType $nodeType)
    {
        $nodeType->fields()->detach();
        $nodeType->delete();
        return response('');
    }

    /**
     * 检查主键是否重复
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function unique($id)
    {
        return response([
            'exists' => !empty(NodeType::find($id)),
        ]);
    }
}
