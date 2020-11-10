<?php

namespace July\Core\Node\Controllers;

use App\Http\Controllers\Controller;
use July\Core\Node\NodeField;
use July\Core\EntityField\FieldType;
use July\Core\EntityField\FieldParameters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class NodeFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fields = NodeField::all()->map(function($field) {
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
        $field = NodeField::make($request->all());
        $parameters = FieldParameters::make([
            'parameters' => FieldType::find($field)->extractParameters($request->input('parameters')),
            'langcode' => $field->langcode,
        ]);

        DB::beginTransaction();

        $field->save();
        $field->parameters()->save($parameters);

        DB::commit();

        return response('');
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Core\Node\NodeField  $contentField
     * @return \Illuminate\Http\Response
     */
    public function show(NodeField $contentField)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \July\Core\Node\NodeField  $contentField
     * @return \Illuminate\Http\Response
     */
    public function edit(NodeField $contentField)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Core\Node\NodeField  $contentField
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NodeField $contentField)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Core\Node\NodeField  $contentField
     * @return \Illuminate\Http\Response
     */
    public function destroy(NodeField $contentField)
    {
        //
    }

    /**
     * 检查字段真名是否存在
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function isExist(string $id)
    {
        // 保留的字段名
        $reserved = [
            // Node 固有属性名
            'id', 'node_type_id', 'langcode', 'updated_at', 'created_at',

            // 关联属性名
            'tags', 'catalogs',
        ];

        return response([
            'is_exist' => in_array($id, $reserved) || !empty(NodeField::find($id)),
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
