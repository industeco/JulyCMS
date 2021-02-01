<?php

namespace July\Message\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Message\Message;
use July\Message\MessageField;

class MessageFieldController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response(MessageField::index());
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
        MessageField::create($request->all());

        return response('');
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Message\MessageField  $field
     * @return \Illuminate\Http\Response
     */
    public function show(MessageField $field)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \July\Message\MessageField  $field
     * @return \Illuminate\Http\Response
     */
    public function edit(MessageField $field)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Message\MessageField  $field
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MessageField $field)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Message\MessageField  $field
     * @return \Illuminate\Http\Response
     */
    public function destroy(MessageField $field)
    {
        //
    }

    /**
     * 检查字段真名是否存在
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function exists(string $id)
    {
        // 保留的字段名
        $reserved = array_merge(
            // 实体的固有属性
            Message::getModelFillable(),
            ['id', 'updated_at', 'created_at'],

            // 实体的动态属性（关联）
            ['fields', 'mold'],

            // 动态表中用到的，或可能会用到的
            ['entity_id', 'entity_name']
        );

        return response([
            'exists' => in_array($id, $reserved) || !empty(MessageField::find($id)),
        ]);
    }
}
