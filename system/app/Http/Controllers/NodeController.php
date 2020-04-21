<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use App\Models\Node;
use App\Models\Catalog;
use App\Models\NodeField;
use App\Models\NodeType;
use App\FieldTypes\FieldType;
use Illuminate\Support\Facades\Log;

class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd(Node::allNodes());
        return view_with_lang('admin::nodes.index', [
            'nodes' => Node::allNodes(),
            'catalogs' => describe(Catalog::all()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view_with_lang('admin::nodes.choose_node_type', [
            'nodeTypes' => describe(NodeType::all()),
        ]);
    }

    /**
     * 创建类型化内容
     *
     * @param \App\Models\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function createWith(NodeType $nodeType)
    {
        $fieldJigsaws = Node::retrieveFieldJigsaws($nodeType);

        return view_with_lang('admin::nodes.create_edit', [
            'id' => 0,
            'node_type' => $nodeType->truename,
            'fields' => $fieldJigsaws['jigsaws'],
            'fields_aside' => $fieldJigsaws['jigsawsAside'],
            'positions' => [],
            'all_tags' => ['hot'],
            'all_nodes' => Node::allNodes(),
            'catalog_nodes' => Catalog::allPositions(),
            'mode' => 'create',
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
        $node = Node::create(Node::prepareRequest($request));
        $node->saveValues($request->all());

        $positions = (array) $request->input('changed_positions');
        if ($positions) {
            $node->savePositions($positions);
        }

        return Response::make([
            'node_id' => $node->id,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function show(Node $node)
    {
        //
    }

    /**
     * 展示编辑或翻译界面
     *
     * @param  \App\Models\Node  $node
     * @param  string  $langcode
     * @return \Illuminate\Http\Response
     */
    public function edit(Node $node, $langcode = null)
    {
        // $fields_aside = [];
        // foreach (NodeField::fieldsAside() as $field) {
        //     $fields_aside[$field['truename']] = FieldType::getJigsaws($field->toArray());
        // }
        $fieldJigsaws = Node::retrieveFieldJigsaws($node->nodeType, $node->getData($langcode));

        $data = [
            'id' => $node->id,
            'node_type' => $node->node_type,
            'fields' => $fieldJigsaws['jigsaws'],
            'fields_aside' => $fieldJigsaws['jigsawsAside'],
            'positions' => $node->positions(),
            'all_tags' => ['hot'],
            'all_nodes' => Node::allNodes($langcode),
            'catalog_nodes' => Catalog::allPositions(),
            'mode' => 'edit',
        ];

        if ($langcode) {
            $data['content_value_lang'] = $langcode;
            $data['mode'] = 'translate';
        }

        return view_with_lang('admin::nodes.create_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Node $node)
    {
        // Log::info('Recieved update data:');
        // Log::info($request->all());

        $changed_values = (array) $request->input('changed_values');

        if (!empty($changed_values)) {
            // $node->update($node->prepareUpdate($request));
            $node->forceUpdate();
            $node->saveValues($request->all(), true);
        }

        $positions = (array) $request->input('changed_positions');
        if ($positions) {
            $node->savePositions($positions, true);
        }

        return Response::make();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function destroy(Node $node)
    {
        $node->delete();
    }

    /**
     * 选择语言
     *
     * @param  \App\Models\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function translate(Node $node)
    {
        return view_with_lang('admin::nodes.choose_langcode', [
            'original_lang' => $node->langcode,
        ]);
    }
}
