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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

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
                return Arr::only($node->gather(), ['id','node_type_id','updated_at','created_at','title','url','tags','templates']);
            })->keyBy('id')->all();

        return view_with_langcode('backend::node.index', [
                'nodes' => $nodes,
                'node_types' => NodeType::all()->pluck('label', 'id')->all(),
                // 'catalogs' => Catalog::pluck('label', 'id')->all(),
                'catalogs' => ['main' => '默认目录'],
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
        $data = [
            'node' => [
                'id' => 0,
                'tags' => [],
                'catalog_positions' => [],
            ],
            'node_type' => [
                'id' => $nodeType->id,
                'label' => $nodeType->label,
            ],
            'fields' => [
                'selected' => $nodeType->takeFieldMaterials(),
                'global' => NodeField::takeGlobalFieldMaterials(),
            ],
            'context' => [
                // 'tags' => Tag::allTags($langcode),
                // 'tags' => Tag::all()->groupBy('langcode')->get($langcode)->pluck('name')->all(),
                'tags' => [],
                'nodes' => $this->simpleNodes(),
                'templates' => $this->getTwigTemplates(),
                'catalog_nodes' => Catalog::allPositions(),
                'editor_config' => Config::getEditorConfig(),
                'edit_mode' => '新建',
            ],
        ];

        // dd($data);

        return view_with_langcode('backend::node.create_edit', $data);
    }

    protected function simpleNodes(string $langcode = null)
    {
        return Node::all()->map(function(Node $node) use($langcode) {
            if ($langcode) {
                $node->translateTo($langcode);
            }
            return [
                'id' => $node->getKey(),
                'title' => $node->getAttribute('title'),
            ];
        })->keyBy('id')->all();
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
     * @param  string|null  $langcode
     * @return \Illuminate\Http\Response
     */
    public function edit(Node $node, string $langcode = null)
    {
        if ($langcode) {
            config(['request.langcode.content' => $langcode]);
        }

        $values = $node->gather($langcode);

        // 已选字段
        $selectedFields = [];

        // 全局字段
        $globalFields = [];

        foreach ($node->takeEntityFieldMaterials($langcode) as $fieldId => $materials) {
            $materials['value'] = $values[$fieldId] ?? null;
            if ($materials['data']['preset_type'] === NodeField::GLOBAL_FIELD) {
                $globalFields[$fieldId] = $materials;
            } else {
                $selectedFields[$fieldId] = $materials;
            }
        }

        $data = [
            'node' => [
                'id' => $node->id,
                'tags' => $values['tags'] ?? [],
                'catalog_positions' => $node->positions(),
            ],
            'node_type' => [
                'id' => $node->nodeType->id,
                'label' => $node->nodeType->label,
            ],
            'fields' => [
                'selected' => $selectedFields,
                'global' => $globalFields,
            ],
            'context' => [
                // 'tags' => Tag::allTags($langcode),
                // 'tags' => Tag::all()->groupBy('langcode')->get($langcode)->pluck('name')->all(),
                'tags' => [],
                'nodes' => $this->simpleNodes($langcode),
                'templates' => $this->getTwigTemplates(),
                'catalog_nodes' => Catalog::allPositions(),
                'editor_config' => Config::getEditorConfig(),
                'edit_mode' => $langcode ? '翻译' : '编辑',
            ],
        ];

        // dd($data);

        return view_with_langcode('backend::node.create_edit', $data);

        // $data = [
        //     'id' => $node->id,
        //     'node_type_id' => $values['node_type'],
        //     'node_type_label' => $node->nodeType->getAttribute('label'),
        //     'fields' => $selectedFields,
        //     'globalFields' => $globalFields,
        //     'tags' => $values['tags'],
        //     'positions' => $node->positions(),
        //     'all_tags' => Tag::allTags($langcode),
        //     'all_nodes' => $this->simpleNodes($langcode),
        //     'all_templates' => $this->getTwigTemplates(),
        //     'catalog_nodes' => Catalog::allPositions(),
        //     'editorConfig' => Config::getEditorConfig(),
        //     'editMode' => '编辑',
        // ];

        // if ($langcode) {
        //     $data['editMode'] = '翻译';
        // }
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

        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Core\Node\Node  $content
     * @return \Illuminate\Http\Response
     */
    public function destroy(Node $content)
    {
        $content->delete();
    }

    /**
     * 选择语言
     *
     * @param  \July\Core\Node\Node  $content
     * @return \Illuminate\Http\Response
     */
    public function chooseLanguage(Node $content)
    {
        if (!config('jc.language.multiple')) {
            abort(404);
        }

        return view_with_langcode('backend::languages', [
            'original_langcode' => $content->getAttribute('langcode'),
            'languages' => lang()->getTranslatableLanguages(),
            'entityKey' => $content->getKey(),
            'routePrefix' => 'contents',
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
        $contents = Node::fetchAll();
        $ids = $request->input('contents');
        if (! empty($ids)) {
            $contents = Node::fetchMany($ids);
        }

        $twig = twig('template', true);

        // 多语言生成
        if (config('jc.language.multiple')) {
            $langs = $request->input('langcode') ?: lang()->getAccessibleLangcodes();
        } else {
            $langs = [langcode('page')];
        }

        $success = [];
        foreach ($contents as $content) {
            $result = [];
            foreach ($langs as $langcode) {
                if ($content->render($twig, $langcode)) {
                    $result[$langcode] = true;
                } else {
                    $result[$langcode] = false;
                }
            }
            $success[$content->id] = $result;
        }

        return response($success);
    }

    protected function getTwigTemplates()
    {
        $templates = NodeField::find('template')->getRecords()->pluck('template_value');
        return $templates->sort()->unique()->all();
    }
}
