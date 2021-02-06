<?php

namespace Specs\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
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
            })->keyBy('id')->all(),
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
            'fields' => $spec->getFields()->values()->all(),
            'fieldTypes' => FieldType::all()->map(function(DefinitionInterface $fieldType) {
                return $fieldType->attributesToArray();
            })->keyBy('id')->all(),
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
     * 浏览/编辑数据
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function records(Spec $spec)
    {
        $records = DB::table($spec->getDataTable())
            ->orderByDesc('id')->get()
            ->map(function($record) {
                return (array) $record;
            })->all();

        $data = [
            'spec_id' => $spec->getKey(),
            'records' => $records,
            'fields' => $spec->getFields()->all(),
            'template' => $spec->getRecordTemplate(),
        ];

        return view('specs::records', $data);
    }

    /**
     * 保存数据
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function upsertRecords(Request $request, Spec $spec)
    {
        $records = $spec->upsertRecords(array_reverse($request->input('records')));
        return response($records);
    }

    /**
     * 删除指定的规格数据
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function removeRecords(Request $request, Spec $spec)
    {
        DB::table($spec->getDataTable())->whereIn('id', $request->input('records'))->delete();
        return response('');
    }

    /**
     * 清空规格数据
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function clearRecords(Spec $spec)
    {
        DB::table($spec->getDataTable())->delete();

        return response('');
    }

    /**
     * 规格搜索界面
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function showSearch(Spec $spec)
    {
        return view('specs::search', [
            'fields' => $spec->getFields()->all(),
            'spec_id' => $spec->id,
        ]);
    }

    /**
     * 检索规格
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request, Spec $spec)
    {
        $results = $spec->search($request->input('keywords', ''), $request->input('fields', []));
        $results['fields'] = $spec->getFields()->all();
        return response($results);
    }

    /**
     * 检索规格
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Specs\Spec  $spec
     * @param  string  $recordId
     * @return \Illuminate\Http\Response
     */
    public function showRecord(Spec $spec, string $recordId)
    {
        return view('specs::record', [
            'record' => $spec->getRecord($recordId),
        ]);
    }

    /**
     * 获取指定规格的所有数据
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function getRecords(Spec $spec)
    {
        $data = $spec->toSearchResults($spec->getRecords());
        $data['fields'] = $spec->getFields()->all();
        return response($data);
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
