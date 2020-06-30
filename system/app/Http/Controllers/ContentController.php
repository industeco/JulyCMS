<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\App;
use App\Models\Content;
use App\Models\Catalog;
use App\Models\Config;
use App\Models\ContentField;
use App\Models\ContentType;
use App\Models\Tag;
use App\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contents = Content::all()->map(function($content) {
            $data = Arr::only($content->gather(), ['id','content_type','updated_at','created_at','title','url','tags']);
            $data['templates'] = $content->suggestedTemplates();
            return $data;
        })->keyBy('id')->all();

        return view_with_langcode('admin::contents.index', [
            'contents' => $contents,
            'contentTypes' => ContentType::pluck('label', 'truename')->all(),
            'catalogs' => Catalog::pluck('label', 'truename')->all(),
            'all_tags' => Tag::allTags(),
            'languages' => lang()->getTranslatableLanguageList(),
        ]);
    }

    /**
     * 选择类型
     *
     * @return \Illuminate\Http\Response
     */
    public function chooseNodetype()
    {
        return view_with_langcode('admin::contents.contenttypes', [
            'contentTypes' => ContentType::all()->all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function create(ContentType $contentType)
    {
        return view_with_langcode('admin::contents.create_edit', [
            'id' => 0,
            'content_type' => $contentType->getKey(),
            'contentTypeLabel' => $contentType->getAttribute('label'),
            'fields' => $contentType->cacheGetFieldJigsaws(),
            'globalFields' => ContentField::cacheGetGlobalFieldJigsaws(),
            'tags' => [],
            'positions' => [],
            'all_tags' => Tag::allTags(),
            'all_contents' => $this->simpleNodes(),
            'all_templates' => $this->getTwigTemplates(),
            'catalog_contents' => Catalog::allPositions(),
            'editorConfig' => Config::getEditorConfig(),
            'editMode' => '新建',
        ]);
    }

    protected function simpleNodes($langcode = null)
    {
        return Content::all()->map(function($content) use($langcode) {
            return [
                'id' => $content->getKey(),
                'title' => $content->gather($langcode)['title'],
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

        $content = Content::make($data);
        $content->save();

        $content->saveValues($data);

        if ($tags = $request->input('tags')) {
            $content->saveTags($tags);
        }

        $positions = (array) $request->input('changed_positions');
        if ($positions) {
            $content->savePositions($positions);
        }

        return Response::make([
            'content_id' => $content->getKey(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\Response
     */
    public function show(Content $content)
    {
        //
    }

    /**
     * 展示编辑或翻译界面
     *
     * @param  \App\Models\Content  $content
     * @param  string  $langcode
     * @return \Illuminate\Http\Response
     */
    public function edit(Content $content, $langcode = null)
    {
        if ($langcode) {
            config()->set('request.langcode.content', $langcode);
        }

        $attributes = $content->gather($langcode);

        //
        $fields = $content->contentType->cacheGetFieldJigsaws($langcode);
        foreach ($fields as $fieldName => &$field) {
            $field['value'] = $attributes[$fieldName] ?? null;
        }
        unset($field);

        // 全局字段
        $globalFields = ContentField::cacheGetGlobalFieldJigsaws($langcode);
        foreach ($globalFields as $fieldName => &$field) {
            $field['value'] = $attributes[$fieldName] ?? null;
        }
        unset($field);

        $data = [
            'id' => $content->id,
            'content_type' => $attributes['content_type'],
            'contentTypeLabel' => $content->contentType->getAttribute('label'),
            'fields' => $fields,
            'globalFields' => $globalFields,
            'tags' => $attributes['tags'],
            'positions' => $content->positions(),
            'all_tags' => Tag::allTags($langcode),
            'all_contents' => $this->simpleNodes($langcode),
            'all_templates' => $this->getTwigTemplates(),
            'catalog_contents' => Catalog::allPositions(),
            'editorConfig' => Config::getEditorConfig(),
            'editMode' => '编辑',
        ];

        if ($langcode) {
            $data['editMode'] = '翻译';
        }

        return view_with_langcode('admin::contents.create_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Content $content)
    {
        // Log::info('Recieved update data:');
        // Log::info($request->all());

        $changed = (array) $request->input('changed_values');

        if (!empty($changed)) {
            // Log::info($changed);
            // $content->update($content->prepareUpdate($request));
            $content->touch();
            $content->saveValues($request->all(), true);

            if (in_array('tags', $changed)) {
                $content->saveTags($request->input('tags'));
            }
        }

        $positions = (array) $request->input('changed_positions');
        if ($positions) {
            $content->savePositions($positions, true);
        }

        return Response::make();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\Response
     */
    public function destroy(Content $content)
    {
        $content->delete();
    }

    /**
     * 选择语言
     *
     * @param  \App\Models\Content  $content
     * @return \Illuminate\Http\Response
     */
    public function chooseLanguage(Content $content)
    {
        if (!config('jc.multi_language')) {
            abort(404);
        }

        return view_with_langcode('admin::languages', [
            'original_langcode' => $content->getAttribute('langcode'),
            'languages' => lang()->getTranslatableLanguageList(),
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
        $contents = Content::fetchAll();
        $ids = $request->input('contents');
        if (! empty($ids)) {
            $contents = Content::fetchMany($ids);
        }

        $twig = twig('template', true);

        // 多语言生成
        if (config('jc.multi_language')) {
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
        $templates = ContentField::find('template')->getRecords()->pluck('template_value');
        return $templates->sort()->unique()->all();
    }
}
