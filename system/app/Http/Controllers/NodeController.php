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
        $nodes = [];
        foreach (Node::all() as $node) {
            $data = $node->getData();
            $data['templates'] = $node->suggestedTemplates();
            unset($data['content']);
            $nodes[$node->id] = $data;
        }

        return view_with_langcode('admin::nodes.index', [
            'nodes' => $nodes,
            'nodeTypes' => NodeType::columns(['name','truename'])->pluck('name', 'truename')->all(),
            'catalogs' => Catalog::columns(['name','truename'])->pluck('name', 'truename')->all(),
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
            'nodeTypes' => NodeType::columns()->all(),
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
        $interface_lang = langcode('admin_page');
        $content_lang = langcode('content_value');

        return view_with_langcode('admin::nodes.create_edit', [
            'id' => 0,
            'node_type' => $nodeType->truename,
            'fields' => $nodeType->retrieveFieldJigsaws($interface_lang),
            'fields_aside' => NodeField::retrieveGlobalFieldJigsaws($interface_lang),
            'tags' => [],
            'positions' => [],
            'all_tags' => Tag::allTags($content_lang),
            'all_nodes' => $this->simpleNodes($content_lang),
            'all_templates' => $this->getTwigTemplates(),
            'catalog_nodes' => Catalog::allPositions(),
            'mode' => 'create',
        ]);
    }

    protected function simpleNodes($langcode)
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
     * @param  string  $translateTo
     * @return \Illuminate\Http\Response
     */
    public function edit(Node $node, $translateTo = null)
    {
        $interface_lang = langcode('admin_page');
        $content_lang = $translateTo ?? langcode('content_value');

        $values = $node->getData($content_lang);
        $data = [
            'id' => $node->id,
            'node_type' => $node->node_type,
            'fields' => $node->nodeType->retrieveFieldJigsaws($interface_lang, $values),
            'fields_aside' => NodeField::retrieveGlobalFieldJigsaws($interface_lang, $values),
            'tags' => $values['tags'],
            'positions' => $node->positions(),
            'all_tags' => Tag::allTags($content_lang),
            'all_nodes' => $this->simpleNodes($content_lang),
            'all_templates' => $this->getTwigTemplates(),
            'catalog_nodes' => Catalog::allPositions(),
            'mode' => 'edit',
        ];

        if ($translateTo) {
            $data['content_value_langcode'] = $translateTo;
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
            $node->forceUpdate();
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
            'langs' => langcode('all'),
            'base_url' => '/admin/nodes/'.$node->id,
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

        $twig = twig('default/template', true);

        // 多语言生成
        if (config('jc.multi_language')) {
            $langs = $request->input('langcode') ?: array_keys(langcode('all'));
            if (is_string($langs)) {
                $langs = [$langs];
            }
        } else {
            $langs = [langcode('site_page')];
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

        return Response::make($success);
    }

    protected function getTwigTemplates()
    {
        return NodeField::find('template')->records()->pluck('template_value')->all();
    }
}
