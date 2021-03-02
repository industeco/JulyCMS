<?php

namespace Specs\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use July\Node\Node;
use July\Node\NodeField;
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
        $images = [];
        $records = [];
        foreach (DB::table($spec->getRecordsTable())->orderByDesc('id')->get() as $record) {
            $record = (array) $record;
            $record['image_invalid'] = true;
            if ($image = $record['image'] ?? '') {
                $record['image_invalid'] = $images[$image] ?? $images[$image] = !is_file(public_path(ltrim($image, '\\/')));
            }
            $records[] = $record;
        }

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

        return response('');
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
        $relatedSpec = NodeField::find('related_spec')->getValueModel()
            ->newQuery()->where('related_spec', $spec->getKey())->first();

        return html_compress(app('twig')->render('specs/record.twig', [
            'relatedNode' => Node::find($relatedSpec->entity_id),
            'spec' => $spec,
            'record' => $spec->getRecord($recordId),
        ]));
    }

    /**
     * 检索规格数据
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        $results = Record::search($request->input('limit', 0), urldecode($request->input('keywords')));

        $results['keywords'] = $request->input('keywords');
        $results['title'] = 'Search';
        $results['meta_title'] = 'Search Result';
        $results['meta_keywords'] = 'Search';
        $results['meta_description'] = 'Search Result';

        return html_compress(app('twig')->render('specs/search.twig', $results));
    }

    /**
     * 获取规格数据
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function fetch(Request $request)
    {
        $limit = $request->input('limit', 0);

        $spec_id = $request->input('spec_id') ?: $request->input('category');
        if ($spec_id && $spec = Spec::find($spec_id)) {
            $results = $spec->search($limit);
        } else {
            $results = Record::search($limit);
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
