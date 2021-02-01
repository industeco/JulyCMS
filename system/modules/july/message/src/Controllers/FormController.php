<?php

namespace July\Message\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use July\Message\FormField;
use July\Message\Form;

class FormController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('message::form.index', [
            'models' => Form::index(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('message::form.create-edit', $this->getCreationContext());
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \July\Message\Form  $form
     * @return \Illuminate\Http\Response
     */
    public function edit(Form $form)
    {
        $data = $this->getCreationContext();
        $data['context']['fields'] = collect($data['context']['fields'])
            ->merge(gather($form->fields)->keyBy('id'))
            ->sortBy('delta')
            ->all();
        $data['context']['mode'] = 'edit';

        return view('message::form.create-edit', $data);
    }

    /**
     * 获取 create 所需渲染环境
     *
     * @return array
     */
    protected function getCreationContext()
    {
        $fields = MessageField::bisect();
        return [
            'model' => Form::template(),
            'context' => [
                'fields' => gather($fields->get('preseted'))->keyBy('id')->all(),
                'optional_fields' => gather($fields->get('optional'))->keyBy('id')->all(),
                'field_template' => MessageField::template(),
                'content_langcode' => langcode('content'),
                'mode' => 'create',
            ],
        ];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 创建类型
        Form::create($request->all());

        return response('');
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Message\Form  $form
     * @return \Illuminate\Http\Response
     */
    public function show(Form $form)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \July\Message\Form  $form
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Form $form)
    {
        // 更新类型
        $form->update($request->all());

        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Message\Form  $form
     * @return \Illuminate\Http\Response
     */
    public function destroy(Form $form)
    {
        $form->delete();

        return response('');
    }

    /**
     * 检查主键是否重复
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function exists($id)
    {
        return response([
            'exists' => !empty(Form::find($id)),
        ]);
    }
}
