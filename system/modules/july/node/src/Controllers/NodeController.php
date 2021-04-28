<?php

namespace July\Node\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Node\Catalog;
use July\Node\Node;
use July\Node\NodeType;
use July\Node\NodeField;
use July\Node\NodeIndex;
use July\Node\NodeSet;

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
            'models' => Node::indexWith(['url'])->all(),
            'context' => [
                'molds' => NodeType::query()->pluck('label', 'id')->all(),
                'catalogs' => Catalog::query()->pluck('label', 'id')->all(),
                'languages' => Lang::getTranslatableLangnames(),
            ],
        ];

        return view('node::node.index', $data);
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Node\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function show(Node $node)
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
            'models' => NodeType::all()->all(),
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
                'global_fields' => $fields->get('global', []),
                'local_fields' => $fields->get('local', []),
                'views' => Node::query()->pluck('view')->filter()->unique()->all(),
                'mode' => 'create',
            ],
            'langcode' => langcode('content'),
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
            'model' => array_merge($node->gather(), ['langcode' => $node->getLangcode()]),
            'context' => [
                'mold' => $node->mold,
                'global_fields' => $fields->get('global'),
                'local_fields' => $fields->get('local'),
                'views' => Node::query()->pluck('view')->filter()->unique()->all(),
                'mode' => $node->isTranslated() ? 'translate' : 'edit',
            ],
            'langcode' => $node->getLangcode(),
        ];

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
        $langcode = langcode('request') ?? $node->getOriginalLangcode();

        $node->translateTo($langcode);

        if ($node->isTranslated()) {
            $node->update($request->all());
        } else {
            $changed = (array) $request->input('_changed');
            if ($changed) {
                $node->update($request->only($changed));
            }
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
        $langcode = request('langcode') ?? langcode('content');

        // 删除节点及其翻译
        if ($langcode === $node->getOriginalLangcode()) {
            $node->translations()->delete();
            $node->delete();
        }

        // 删除节点翻译
        else {
            $node->translations()->where('langcode', $langcode)->delete();
            $node->touch();
        }

        return response('');
    }

    /**
     * 选择语言
     *
     * @param  \July\Node\Node  $node
     * @return \Illuminate\Http\Response
     */
    public function chooseLanguage(Node $node)
    {
        if (! config('lang.multiple')) {
            abort(404);
        }

        return view('languages', [
            'original_langcode' => $node->getOriginalLangcode(),
            'languages' => Lang::getTranslatableLangnames(),
            'content_id' => $node->getKey(),
            'edit_route' => 'nodes.edit',
            'translate_route' => 'nodes.translate',
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
        if ($ids = $request->input('nodes')) {
            $nodes = NodeSet::fetch($ids);
        } else {
            $nodes = NodeSet::fetchAll();
        }

        $frontendLangcode = langcode('frontend');

        // 多语言生成
        $langs = config('lang.multiple') ? Lang::getAccessibleLangcodes() : [];

        /** @var \Twig\Environment */
        $twig = app('twig');

        $success = [];
        foreach ($nodes as $node) {
            $result = [];

            try {
                $node->translateTo($frontendLangcode)->render($twig);
            } catch (\Throwable $th) {
                $result['_default'] = false;

                Log::error($th->getMessage());
            }

            if ($langs) {
                foreach ($langs as $langcode) {
                    try {
                        $node->translateTo($langcode)->render($twig, $langcode);
                        $result[$langcode] = true;
                    } catch (\Throwable $th) {
                        $result[$langcode] = false;

                        Log::error($th->getMessage());
                    }
                }
            }

            $success[$node->url] = $result;
        }

        return response($success);
    }

    /**
     * 检索关键词
     *
     * @return string
     */
    public function search(Request $request)
    {
        $nodes = NodeSet::fetchAll()->keyBy('id');

        $results = NodeIndex::search($request->input('keywords'));
        $results['title'] = 'Search';
        $results['meta_title'] = 'Search Result';
        $results['meta_keywords'] = 'Search';
        $results['meta_description'] = 'Search Result';

        foreach ($results['results'] as &$result) {
            $result['node'] = $nodes->get($result['node_id']);
        }

        return app('twig')->render('search.twig', $results);
    }

    /**
     * 查找无效链接
     *
     * @return \Illuminate\View\View
     */
    public function findInvalidLinks()
    {
        $invalidLinks = [];
        foreach (NodeSet::fetchAll() as $node) {
            $invalidLinks = array_merge($invalidLinks, $node->findInvalidLinks());
        }

        return view('node::node.invalid_links', [
            'invalidLinks' => $invalidLinks,
        ]);
    }
}
