<?php

namespace Specs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Specs\Record;
use Specs\Spec;

class RecordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function index(Spec $spec)
    {
        $records = DB::table($spec->getRecordsTable())
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

        return view('specs::records.index', $data);
    }

    /**
     * 新建或更新规格数据
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function upsert(Request $request, Spec $spec)
    {
        $records = $spec->upsertRecords(array_reverse($request->input('records')));

        return response($records);
    }

    /**
     * 删除或清空规格数据
     *
     * @param  \Specs\Spec  $spec
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Spec $spec)
    {
        if (!empty($records = $request->input('records'))) {
            DB::table($spec->getRecordsTable())->whereIn('id', $records)->delete();
        }
        // DB::table($spec->getRecordsTable())->delete();

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
     * @param  string|int  $recordId
     * @return \Illuminate\Http\Response
     */
    public function show(Spec $spec, $recordId)
    {
        // $nodes = NodeSet::fetchAll()->keyBy('id');

        // $results = NodeIndex::search($request->input('keywords'));
        // $results['title'] = 'Search';
        // $results['meta_title'] = 'Search Result';
        // $results['meta_keywords'] = 'Search';
        // $results['meta_description'] = 'Search Result';

        // foreach ($results['results'] as &$result) {
        //     $result['node'] = $nodes->get($result['node_id']);
        // }

        // return app('twig')->render('search.twig', $results);

        return app('twig')->render('specs/record.twig', [
            'spec' => $spec,
            'record' => $spec->getRecord($recordId),
        ]);
    }

    /**
     * 检索规格数据
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $results = Record::search($request->input('keywords'));

        return app('twig')->render('specs/search.twig', $results);
    }

    /**
     * 获取规格数据
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request)
    {
        $spec_id = $request->input('spec_id') ?: $request->input('category');
        if ($spec_id && $spec = Spec::find($spec_id)) {
            $results = $spec->search(null);
        } else {
            $results = Record::search();
        }

        return response($results);
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
