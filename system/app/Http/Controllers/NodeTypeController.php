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
            'types' => describe(NodeType::all()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $lang = langcode();

        $availableFields = NodeField::where('is_preset', false)->get();
        return view_with_langcode('admin::node_types.create_edit', [
            'truename' => '',
            'name' => '',
            'description' => '',
            'fields' => [describe(NodeField::find('title'), $lang)],
            'availableFields' => describe($availableFields, $lang),
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
        $nodeType = NodeType::create(NodeType::prepareRequest($request));
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
        $lang = langcode();

        $data = describe($nodeType, $lang);
        $data['fields'] = describe($nodeType->retrieveFields(), $lang);

        $fields = NodeField::where('is_preset', false)->get();
        $data['availableFields'] = describe($fields, $lang);

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
        $nodeType->update(NodeType::prepareRequest($request, $nodeType));
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
