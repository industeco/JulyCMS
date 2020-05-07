<?php

namespace App\Http\Controllers;

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
        return view_with_langcode('admin::node_types.index', [
            'nodeTypes' => mix_config(NodeType::all()),
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
            NodeField::find('title')->mixConfig(),
        ];

        $optionalFields = NodeField::where('is_preset', false)->get();

        return view_with_langcode('admin::node_types.create_edit', [
            'truename' => '',
            'name' => '',
            'description' => '',
            'fields' => $requiredFiels,
            'availableFields' => mix_config($optionalFields),
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
        $data = $nodeType->mixConfig();
        $data['fields'] = $nodeType->retrieveFields();

        $optionalFields = NodeField::where('is_preset', false)->get();
        $data['availableFields'] = mix_config($optionalFields);

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
        $config = $nodeType->buildConfig($request->all());
        $nodeType->update([
            'config' => array_replace_recursive($nodeType->config, $config)
        ]);
        $nodeType->updateFields($request);
        return Response::make();
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
        return Response::make();
    }

    /**
     * 检查主键是否重复
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function unique($id)
    {
        return Response::make([
            'exists' => !empty(NodeType::find($id)),
        ]);
    }
}
