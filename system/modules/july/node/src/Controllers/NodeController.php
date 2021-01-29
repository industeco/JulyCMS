<?php

namespace July\Node\Controllers;

use App\EntityField\EntityView;
use App\Http\Controllers\Controller;
use App\Utils\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Node\Catalog;
use July\Node\Node;
use July\Node\NodeType;
use July\Node\NodeField;
use July\Taxonomy\Tag;

class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $keys = ['id','mold_id','is_red','is_green','is_blue','updated_at','created_at','title','url','suggested_views',];
        $data = [
            'models' => Node::index($keys),
            'context' => [
                'node_types' => NodeType::query()->pluck('label', 'id')->all(),
                'catalogs' => Catalog::query()->pluck('label', 'id')->all(),
                'languages' => Lang::getTranslatableLangnames(),
                // 'catalogs' => ['main' => '默认目录'],
                // 'tags' => Tag::allTags(),
            ],
        ];

        return view('node::node.index', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Core\Node\Node  $content
     * @return \Illuminate\Http\Response
     */
    public function show(Node $content)
    {
        //
    }

    /**
     * 选择类型
     *
     * @return \Illuminate\Http\Response
     */
    public function chooseMold()
    {
        $data = [
            'models' => NodeType::all(),
        ];

        return view('node::node.choose_mold', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \July\Node\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function create(NodeType $nodeType)
    {
        // 节点模板数据
        $model = array_merge(Node::template(), $nodeType->getFieldValues());
        $model['langcode'] = langcode('content');
        $model['mold_id'] = $nodeType->getKey();

        // 字段集，按是否全局字段分组
        $fields = $nodeType->getFields()->groupBy(function(NodeField $field) {
            return $field->is_global ? 'global' : 'local';
        });

        $data = [
            'model' => $model,
            'context' => [
                'mold' => $nodeType,
                'global_fields' => $fields->get('global'),
                'local_fields' => $fields->get('local'),
                'mode' => 'create',
            ],
            // 'context' => [
            //     'tags' => Tag::allTags($langcode),
            //     'tags' => Tag::all()->groupBy('langcode')->get($langcode)->pluck('name')->all(),
            //     'nodes' => $nodes,
            //     'catalog_nodes' => Catalog::allPositions(),
            //     'editor_config' => Config::getEditorConfig(),
            // ],
        ];

        return view('node::node.create-edit', $data);
    }

    /**
     * 展示编辑或翻译界面
     *
     * @param  \July\Node\Node  $node
     * @param  string|null  $translateTo
     * @return \Illuminate\Http\Response
     */
    public function edit(Node $node, string $translateTo = null)
    {
        if ($translateTo) {
            config(['request.langcode.content' => $translateTo]);
            $node->translateTo($translateTo);
        }
        $langcode = $node->getLangcode();

        $data = [
            'node' => array_merge($node->gather(), ['path' => $node->getEntityPath()]),
            'node_type' => [
                'id' => $node->nodeType->id,
                'label' => $node->nodeType->label,
            ],
            'fields' => $node->retrieveFieldMaterials(),
            'context' => [
                // 'tags' => Tag::allTags($langcode),
                // 'tags' => Tag::all()->groupBy('langcode')->get($langcode)->pluck('name')->all(),
                // 'nodes' => $this->simpleNodes($langcode),
                'templates' => $this->getTwigTemplates($langcode),
                // 'catalog_nodes' => Catalog::allPositions(),
                // 'editor_config' => Config::getEditorConfig(),
            ],
            'langcode' => $langcode,
            'mode' => ($translateTo && $translateTo !== $langcode) ? 'translate' : 'edit',
        ];

        // dd($data);

        return view_with_langcode('backend::node.create_edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $node = Node::create($request->all());
        return response([
            'node_id' => $node->getKey(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Core\Node\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Node $node)
    {
        $changed = (array) $request->input('_changed');

        if (!empty($changed)) {
            // Log::info($changed);
            $node->update($request->only($changed));
        }

        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Core\Node\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function destroy(Node $node)
    {
        // Log::info($node->id);
        $node->delete();

        return response('');
    }

    /**
     * 选择语言
     *
     * @param  \July\Node\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function translateTo(Node $node)
    {
        if (!config('language.multiple')) {
            abort(404);
        }

        return view_with_langcode('backend::languages', [
            'original_langcode' => $node->getAttribute('langcode'),
            'languages' => Lang::getTranslatableLangnames(),
            'entityKey' => $node->getKey(),
            'routePrefix' => 'nodes',
        ]);
    }

    protected function simpleNodes(string $langcode = null)
    {
        return Node::all()->map(function (Node $node) use ($langcode) {
            if ($langcode) {
                $node->translateTo($langcode);
            }
            return [
                'id' => $node->getKey(),
                'title' => $node->getAttributeValue('title'),
            ];
        })->keyBy('id')->all();
    }

    /**
     * 渲染内容
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render(Request $request)
    {
        // $contents = Node::carryAll();
        if ($ids = $request->input('selected_nodes')) {
            $nodes = Node::find($ids);
        } else {
            $nodes = Node::all();
        }

        // 多语言生成
        if (config('language.multiple')) {
            $langs = $request->input('langcode') ?: lang()->getAccessibleLangcodes();
        } else {
            $langs = [langcode('frontend')];
        }

        $success = [];
        foreach ($nodes as $node) {
            $result = [];
            foreach ($langs as $langcode) {
                try {
                    $node->translateTo($langcode)->render();
                    $result[$langcode] = true;
                } catch (\Throwable $th) {
                    $result[$langcode] = false;
                }
            }
            $success[$node->id] = $result;
        }

        return response($success);
    }
}
