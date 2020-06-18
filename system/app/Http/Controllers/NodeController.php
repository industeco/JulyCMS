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
use App\Models\Tag;
use App\Support\Arr;
use Illuminate\Support\Facades\DB;
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
        $nodes = Node::all()->map(function($node) {
            $data = Arr::only($node->gather(), ['id','node_type','updated_at','created_at','title','url','tags']);
            $data['templates'] = $node->suggestedTemplates();
            return $data;
        })->keyBy('id')->all();

        return view_with_langcode('admin::nodes.index', [
            'nodes' => $nodes,
            'nodeTypes' => NodeType::pluck('label', 'truename')->all(),
            'catalogs' => Catalog::pluck('label', 'truename')->all(),
            'all_tags' => Tag::allTags(),
            'languages' => available_languages('translatable'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view_with_langcode('admin::nodes.choose_node_type', [
            'nodeTypes' => NodeType::all()->all(),
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
        return view_with_langcode('admin::nodes.create_edit', [
            'id' => 0,
            'node_type' => $nodeType->truename,
            'fields' => $nodeType->cacheGetFieldJigsaws(),
            'fields_aside' => NodeField::cacheGetGlobalFieldJigsaws(),
            'tags' => [],
            'positions' => [],
            'all_tags' => Tag::allTags(),
            'all_nodes' => $this->simpleNodes(),
            'all_templates' => $this->getTwigTemplates(),
            'catalog_nodes' => Catalog::allPositions(),
            'mode' => 'create',
        ]);
    }

    protected function simpleNodes($langcode = null)
    {
        $nodes = [];
        foreach (Node::allNodes($langcode) as $node) {
            $nodes[$node['id']] = [
                'id' => $node['id'],
                'title' => $node['title'],
            ];
        }
        return $nodes;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        $node = Node::make($data);
        $node->save();

        $node->saveValues($data);

        if ($tags = $request->input('tags')) {
            $node->saveTags($tags);
        }

        $positions = (array) $request->input('changed_positions');
        if ($positions) {
            $node->savePositions($positions);
        }

        return Response::make([
            'node_id' => $node->getKey(),
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
        if ($langcode) {
            config()->set('request.langcode.content', $langcode);
        }

        $values = $node->gather($langcode);

        $fields = $node->nodeType->cacheGetFieldJigsaws($langcode);
        foreach ($fields as $fieldName => &$field) {
            $field['value'] = $values[$fieldName] ?? null;
        }
        unset($field);

        $fieldsAside = NodeField::cacheGetGlobalFieldJigsaws($langcode);
        foreach ($fieldsAside as $fieldName => &$field) {
            $field['value'] = $values[$fieldName] ?? null;
        }
        unset($field);

        $data = [
            'id' => $node->id,
            'node_type' => $node->node_type,
            'fields' => $fields,
            'fields_aside' => $fieldsAside,
            'tags' => $values['tags'],
            'positions' => $node->positions(),
            'all_tags' => Tag::allTags($langcode),
            'all_nodes' => $this->simpleNodes($langcode),
            'all_templates' => $this->getTwigTemplates(),
            'catalog_nodes' => Catalog::allPositions(),
            'mode' => 'edit',
        ];

        if ($langcode) {
            $data['mode'] = 'translate';
        }

        return view_with_langcode('admin::nodes.create_edit', $data);
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

        $changed = (array) $request->input('changed_values');

        if (!empty($changed)) {
            // Log::info($changed);
            // $node->update($node->prepareUpdate($request));
            $node->touch();
            $node->saveValues($request->all(), true);

            if (in_array('tags', $changed)) {
                $node->saveTags($request->input('tags'));
            }
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
        if (!config('jc.multi_language')) {
            abort(404);
        }

        return view_with_langcode('admin::translate', [
            'original_langcode' => $node->langcode,
            'languages' => available_languages('translatable'),
            'entityKey' => $node->id,
            'routePrefix' => 'nodes',
        ]);
    }

    /**
     * 渲染内容
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render(Request $request)
    {
        $nodes = Node::fetchAll();
        $ids = $request->input('nodes');
        if (! empty($ids)) {
            $nodes = Node::fetchMany($ids);
        }

        $twig = twig('template', true);

        // 多语言生成
        if (config('jc.multi_language')) {
            $langs = $request->input('langcode') ?: available_langcodes('accessible');
        } else {
            $langs = [langcode('page')];
        }

        $success = [];
        foreach ($nodes as $node) {
            $result = [];
            foreach ($langs as $langcode) {
                if ($node->render($twig, $langcode)) {
                    $result[$langcode] = true;
                } else {
                    $result[$langcode] = false;
                }
            }
            $success[$node->id] = $result;
        }

        return response($success);
    }

    protected function getTwigTemplates()
    {
        $templates = NodeField::find('template')->records()->pluck('template_value');
        return $templates->sort()->unique()->all();
    }
}
