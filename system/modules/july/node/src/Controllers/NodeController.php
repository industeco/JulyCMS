<?php

namespace July\Node\Controllers;

use App\Http\Controllers\Controller;
use App\Utils\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Node\Catalog;
use July\Node\Node;
use July\Node\NodeType;
use July\Node\NodeField;
use July\Node\TwigExtensions\NodeQueryExtension;
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
        $data = [
            'models' => Node::indexWithFields(['url']),
            'context' => [
                'molds' => NodeType::query()->pluck('label', 'id')->all(),
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
     * @param  \July\Node\Node  $content
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
                // 'catalog_nodes' => Catalog::allPositions(),
                // 'tags' => Tag::all()->groupBy('langcode')->get($langcode)->pluck('name')->all(),
            ],
        ];

        return view('node::node.create-edit', $data);
    }

    /**
     * 展示编辑或翻译界面
     *
     * @param  \July\Node\Node  $node
     * @param  string|null  $langcode
     * @return \Illuminate\Http\Response
     */
    public function edit(Node $node, string $langcode = null)
    {
        if ($langcode) {
            $node->translateTo($langcode);
        }

        // 字段集，按是否全局字段分组
        $fields = $node->getFields()->groupBy(function(NodeField $field) {
            return $field->is_global ? 'global' : 'local';
        });

        $data = [
            'model' => $node->gather(),
            'context' => [
                'mold' => $node->mold,
                'global_fields' => $fields->get('global'),
                'local_fields' => $fields->get('local'),
                'mode' => $node->isTranslated() ? 'translate' : 'edit',
                // 'catalog_nodes' => Catalog::allPositions(),
                // 'tags' => Tag::all()->groupBy('langcode')->get($langcode)->pluck('name')->all(),
            ],
        ];

        // dd($data);

        return view('node::node.create-edit', $data);
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
     * @param  \July\Node\Node  $node
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
     * @param  \July\Node\Node  $node
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
        if (!config('lang.multiple')) {
            abort(404);
        }

        return view('node::languages', [
            'original_langcode' => $node->getOriginalLangcode(),
            'languages' => Lang::getTranslatableLangnames(),
            'entity_id' => $node->getKey(),
            'route_prefix' => 'nodes',
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
        // $contents = Node::carryAll();
        if ($ids = $request->input('nodes')) {
            $nodes = Node::find($ids);
        } else {
            $nodes = Node::all();
        }

        // 多语言生成
        if (config('lang.multiple')) {
            $langs = $request->input('langcode') ?: Lang::getAccessibleLangcodes();
        } else {
            $langs = [langcode('frontend')];
        }

        /** @var \Twig\Environment */
        $twig = app('twig');
        $twig->addExtension(new NodeQueryExtension);

        $success = [];
        foreach ($nodes as $node) {
            $result = [];
            foreach ($langs as $langcode) {
                try {
                    $node->translateTo($langcode)->render($twig);
                    $result[$langcode] = true;
                } catch (\Throwable $th) {
                    $result[$langcode] = false;
                    Log::error($th->getMessage());
                }
            }
            $success[$node->id] = $result;
        }

        return response($success);
    }
}
