<?php

namespace Specs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Specs\FieldType;
use Specs\FieldTypeDefinitions\DefinitionInterface;
use Specs\Spec;
use Specs\SpecField;

class SpecController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $specs = Spec::all()->map(function(Spec $spec) {
            return $spec->attributesToArray();
        })->all();

        return view('specs::index', [
            'specs' => $specs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data = [
            'spec' => Spec::defaultAttributes(),
            'fields' => [],
            'fieldTypes' => FieldType::all()->map(function(DefinitionInterface $fieldType) {
                return $fieldType->attributesToArray();
            }),
            'emptyField' => SpecField::defaultAttributes(),
        ];

        // dd($fieldTypes);

        return view('specs::create_edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Spec::create($request->all());

        return response('');
    }

    /**
     * Display the specified resource.
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function show(Spec $spec)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function edit(Spec $spec)
    {
        $data = [
            'spec' => $spec->attributesToArray(),
            'fields' => $spec->fields->toArray(),
            'fieldTypes' => FieldType::all()->map(function(DefinitionInterface $fieldType) {
                return $fieldType->attributesToArray();
            }),
            'emptyField' => SpecField::defaultAttributes(),
        ];

        return view('specs::create_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Spec $spec)
    {
        $spec->update($request->all());

        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function destroy(Spec $spec)
    {
        $spec->delete();

        return response('');
    }

    /**
     * 录入数据
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function insert(Spec $spec)
    {
        $data = [
            'specs' => $spec->getRecords(),
            'fields' => $spec->fields->toArray(),
            'template' => $spec->getTemplate(),
        ];

        return view('specs::insert', $data);
    }

    /**
     * 检查主键是否重复
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function isExist($id)
    {
        return response([
            'is_exist' => !empty(Spec::find($id)),
        ]);
    }
}
