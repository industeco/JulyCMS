<?php

namespace App\Http\Controllers;

use App\FieldTypes\FieldType;
use App\Models\FieldParameters;
use App\Models\ContentField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ContentFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fields = ContentField::all()->map(function($field) {
            return $field->gather();
        })->all();
        return response($fields);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $field = ContentField::make($request->all());
        $parameters = FieldParameters::make([
            'keyname' => implode('.', ['content_field', $field->getKey(), langcode('content')]),
            'data' => FieldType::extractParameters($request->all()),
        ]);

        DB::beginTransaction();

        $parameters->save();
        $field->save();

        DB::commit();

        return response('');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ContentField  $contentField
     * @return \Illuminate\Http\Response
     */
    public function show(ContentField $contentField)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ContentField  $contentField
     * @return \Illuminate\Http\Response
     */
    public function edit(ContentField $contentField)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentField  $contentField
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContentField $contentField)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ContentField  $contentField
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContentField $contentField)
    {
        //
    }

    /**
     * 检查字段真名是否存在
     *
     * @param  string  $truename
     * @return \Illuminate\Http\Response
     */
    public function unique($truename)
    {
        // 保留的名字
        $reserved = [
            // 属性名
            'id', 'is_preset', 'content_type', 'langcode', 'updated_at', 'created_at',

            // 关联属性名
            'tags', 'catalogs',
        ];

        return response([
            'exists' => in_array($truename, $reserved) || !empty(ContentField::find($truename)),
        ]);
    }

    /**
     * 检查 url 是否已存在
     *
     * @param  string  $url
     * @return \Illuminate\Http\Response
     */
    public function uniqueUrl(Request $request)
    {
        $url = $request->input('url');

        $condition = [
            ['url_value', '=', $url],
            ['langcode', '=', langcode('content')],
        ];
        if ($id = (int) $request->input('id')) {
            $condition[] = ['content_id', '!=', $id];
        }

        $result = DB::table('content__url')->where($condition)->first();
        return response([
            'exists' => !empty($result),
        ]);
    }
}
