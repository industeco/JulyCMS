<?php

namespace App\Http\Controllers;

use App\Models\Node;
use App\Models\NodeType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Models\NodeField;
use Illuminate\Support\Facades\Log;

class NodeTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $nodeCount = NodeType::usedByNodes();
        $nodeTypes = NodeType::all()->map(function($nodeType) {
            return $nodeType->attributesToArray();
        })->all();
        foreach ($nodeTypes as &$nodeType) {
            $nodeType['nodes'] = $nodeCount[$nodeType['truename']] ?? 0;
        }
        unset($nodeType);

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
        $optionalFields = NodeField::optionalFields()
                            ->map(function($field) {
                                return $field->gather();
                            })
                            ->keyBy('truename')
                            ->all();

        return view_with_langcode('admin::node_types.create_edit', [
            'truename' => null,
            'label' => null,
            'description' => null,
            'fields' => NodeField::optionalPresetFields()->pluck('truename')->all(),
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
        $nodeType->updateFields($request->input('fields', []));
        return response('');
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
        if ('default' === $nodeType->getKey()) {
            abort(404);
        }
        $fields = collect($nodeType->cacheGetFields())->keyBy('truename');

        $data = $nodeType->gather();
        $data['fields'] = $fields->keys()->all();
        $data['availableFields'] = NodeField::optionalFields()
                                    ->map(function($field) {
                                        return $field->gather();
                                    })
                                    ->keyBy('truename')
                                    ->replace($fields)
                                    ->all();

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
        $nodeType->updateFields($request->input('fields', []));
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
