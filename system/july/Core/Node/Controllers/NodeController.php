<?php

namespace July\Core\Node\Controllers;

use App\Http\Controllers\Controller;
use App\Utils\Arr;
use App\Utils\Lang;
use July\Core\Config\Config;
use July\Core\Node\Catalog;
use July\Core\Node\Node;
use July\Core\Node\NodeType;
use July\Core\Node\NodeField;
use July\Core\Taxonomy\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use July\Core\Config\PartialView;

class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $keys = array_merge(
            Node::make()->getColumnKeys(),
            ['title', 'url', 'template']
        );

        $nodes = Node::all()->map(function (Node $node) use ($keys) {
                return Arr::only($node->gather(), $keys);
            })->keyBy('id')->all();

        return view_with_langcode('backend::node.index', [
                'nodes' => $nodes,
                'node_types' => NodeType::all()->pluck('label', 'id')->all(),
                'catalogs' => Catalog::all()->pluck('label', 'id')->all(),
                // 'catalogs' => ['main' => '默认目录'],
                // 'tags' => Tag::allTags(),
                'tags' => [],
                'languages' => Lang::getTranslatableLanguages(),
            ]);
    }

    /**
     * 选择类型
     *
     * @return \Illuminate\Http\Response
     */
    public function chooseNodeType()
    {
        return view_with_langcode('backend::node.choose_node_type', [
                'node_types' => NodeType::all()->all(),
            ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param  \July\Core\Node\NodeType  $nodeType
     * @return \Illuminate\Http\Response
     */
    public function create(NodeType $nodeType)
    {
        // $nodes = Node::all()->map(function(Node $node) {
        //     return [
        //         'id' => $node->id,
        //         'title' => $node->title,
        //     ];
        // })->keyBy('id')->all();

        $langcode = langcode('content');
        $node = new Node([
                'node_type_id' => $nodeType->getKey(),
                'langcode' => $langcode,
            ]);

        $data = [
            'node' => array_merge($node->gather(), ['path' => '']),
            'node_type' => [
                'id' => $nodeType->getKey(),
                'label' => $nodeType->label,
            ],
            'fields' => $node->retrieveFormMaterials(),
            'context' => [
                // 'tags' => Tag::allTags($langcode),
                // 'tags' => Tag::all()->groupBy('langcode')->get($langcode)->pluck('name')->all(),
                // 'nodes' => $nodes,
                'templates' => $this->getTwigTemplates($langcode),
                // 'catalog_nodes' => Catalog::allPositions(),
                'editor_config' => Config::getEditorConfig(),
            ],
            'langcode' => $langcode,
            'mode' => 'create',
        ];

        return view('backend::node.create_edit', $data);
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

        // $node->saveValues($data);

        // if ($tags = $request->input('tags')) {
        //     $node->saveTags($tags);
        // }

        // $positions = (array) $request->input('changed_positions');
        // if ($positions) {
        //     $node->savePositions($positions);
        // }

        return response([
            'node_id' => $node->getKey(),
        ]);
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
     * 展示编辑或翻译界面
     *
     * @param  \July\Core\Node\Node  $node
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
                'editor_config' => Config::getEditorConfig(),
            ],
            'langcode' => $langcode,
            'mode' => ($translateTo && $translateTo !== $langcode) ? 'translate' : 'edit',
        ];

        // dd($data);

        return view_with_langcode('backend::node.create_edit', $data);
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
        Log::info($node->id);
        $node->delete();

        return response('');
    }

    /**
     * 选择语言
     *
     * @param  \July\Core\Node\Node  $content
     * @return \Illuminate\Http\Response
     */
    public function chooseLanguage(Node $node)
    {
        if (!config('jc.language.multiple')) {
            abort(404);
        }

        return view_with_langcode('backend::languages', [
            'original_langcode' => $node->getAttribute('langcode'),
            'languages' => lang()->getTranslatableLanguages(),
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
        // $contents = Node::fetchAll();
        if ($ids = $request->input('selected_nodes')) {
            $nodes = Node::find($ids);
        } else {
            $nodes = Node::all();
        }

        $twig = twig('template', true);

        // 多语言生成
        if (config('jc.language.multiple')) {
            $langs = $request->input('langcode') ?: lang()->getAccessibleLangcodes();
        } else {
            $langs = [langcode('frontend')];
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

    /**
     * @return array
     */
    protected function getTwigTemplates(string $langcode)
    {
        $templates = PartialView::query()->where('langcode', $langcode)->get()->pluck('view');

        return array_values($templates->sort()->unique()->all());
    }
}
