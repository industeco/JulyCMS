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

        return view('specs::specs.index', [
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
            })->keyBy('id')->all(),
            'emptyField' => SpecField::defaultAttributes(),
        ];

        // dd($fieldTypes);

        return view('specs::specs.create-edit', $data);
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
            'fields' => $spec->getFields()->values()->all(),
            'fieldTypes' => FieldType::all()->map(function(DefinitionInterface $fieldType) {
                return $fieldType->attributesToArray();
            })->keyBy('id')->all(),
            'emptyField' => SpecField::defaultAttributes(),
        ];

        return view('specs::specs.create-edit', $data);
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
     * 检查主键是否重复
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function exists($id)
    {
        return response([
            'exists' => !empty(Spec::find($id)),
        ]);
    }
}
