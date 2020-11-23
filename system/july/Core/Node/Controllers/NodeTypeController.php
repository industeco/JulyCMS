<?php

namespace July\Core\Node\Controllers;

use App\Http\Controllers\Controller;
use July\Core\Node\Node;
use July\Core\Node\NodeField;
use July\Core\Node\NodeType;
use Illuminate\Http\Request;
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
        // 统计每个类型下有多少个节点
        $referenced = NodeType::countReference();

        $nodeTypes = [];
        foreach (NodeType::all() as $nodeType) {
            $id = $nodeType->getKey();
            $nodeTypes[$id] = array_merge(
                $nodeType->attributesToArray(),
                ['nodes_total' => $referenced[$id] ?? 0]
            );
        }

        // dd($nodeTypes);

        return view_with_langcode('backend::node_type.index', [
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
        $allFields = NodeField::takeFieldsInfo()->groupBy('preset_type');

        $currentFields = $allFields->get(NodeField::PRESET_TYPE['preset'])
            ->sortBy('delta')->pluck('id')->all();

        $availableFields = $allFields->get(NodeField::PRESET_TYPE['preset'])
            ->merge($allFields->get(NodeField::PRESET_TYPE['normal']))
            ->sortBy('delta')
            ->keyBy('id')
            ->all();

        $data = [
            'id' => null,
            'label' => null,
            'description' => null,
            'currentFields' => $currentFields,
            'availableFields' => $availableFields,
        ];

        // dd($data);

        return view_with_langcode('backend::node_type.create_edit', $data);
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
     * @param  \July\Core\Node\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function show(NodeType $nodeType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \July\Core\Node\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function edit(NodeType $nodeType)
    {
        $allFields = NodeField::takeFieldsInfo()->groupBy('preset_type');

        $availableFields = $allFields->get(NodeField::PRESET_TYPE['preset'])
            ->merge($allFields->get(NodeField::PRESET_TYPE['normal']))
            ->sortBy('id')
            ->keyBy('id')
            ->all();

        $data = [
            'id' => $nodeType->id,
            'label' => $nodeType->label,
            'description' => $nodeType->description,
            'currentFields' => $nodeType->fields->sortBy('delta')->pluck('id')->all(),
            'availableFields' => $availableFields,
        ];

        return view_with_langcode('backend::node_type.create_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Core\Node\NodeType  $nodeType
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
     * @param  \July\Core\Node\NodeType  $nodeType
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
    public function isExist($id)
    {
        return response([
            'is_exist' => !empty(NodeType::find($id)),
        ]);
    }
}
