<?php

namespace July\Node\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Node\Node;
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
            'models' => NodeType::index()->all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = $this->getCreationContext();

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
        $data = $this->getCreationContext();
        $data['model'] = $nodeType->gather();

        $fields = $nodeType->fields->map(function($field) {
            return $field->getMeta();
        });
        $data['context']['fields'] = collect($data['context']['fields'])
            ->merge($fields->keyBy('id'))
            ->sortBy('delta')
            ->all();
        $data['context']['mode'] = 'edit';

        return view('node::node_type.create-edit', $data);
    }

    /**
     * 获取 create 所需渲染环境
     *
     * @return array
     */
    protected function getCreationContext()
    {
        $fields = NodeField::bisect();
        return [
            'model' => NodeType::template(),
            'context' => [
                'entity_name' => Node::getEntityName(),
                'fields' => $fields->get('preseted')->keyBy('id')->all(),
                'optional_fields' => $fields->get('optional')->keyBy('id')->all(),
                'field_template' => NodeField::template(),
                'langcode' => langcode('content'),
                'mode' => 'create',
            ],
        ];
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
