<?php

namespace July\Node\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use July\Node\Catalog;
use July\Node\Node;
use July\Node\NodeField;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('node::catalog.index', [
            'models' => Catalog::index(),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Catalog  $catalog
     * @return \Illuminate\Http\Response
     */
    public function show(Catalog $catalog)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('node::catalog.create-edit', [
            'model' => Catalog::template(),
            'context' => [
                'mode' => 'create',
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Catalog  $catalog
     * @return \Illuminate\Http\Response
     */
    public function edit(Catalog $catalog)
    {
        return view('node::catalog.create-edit', [
            'model' => $catalog->gather(),
            'context' => [
                'mode' => 'edit',
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Catalog::create($request->all());

        return response('');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Catalog  $catalog
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Catalog $catalog)
    {
        $catalog->update($request->all());

        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Catalog  $catalog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Catalog $catalog)
    {
        $catalog->delete();

        return response('');
    }

    /**
     * 展示目录结构
     *
     * @param  \July\Node\Catalog $catalog
     * @return \Illuminate\View\View
     */
    public function tree(Catalog $catalog)
    {
        // 获取所有 title 字段的值
        $titles = NodeField::findOrFail('title')
            ->getValueModel()
            ->newQuery()
            ->pluck('title', 'entity_id')
            ->all();

        // 获取所有节点数据，附带 title 字段值
        $nodes = Node::all()->map(function (Node $node) use ($titles) {
            return $node->attributesToArray() + ['title' => $titles[$node->getKey()] ?? null];
        })->keyBy('id')->all();

        $data = [
            'positions' => $catalog->tree()->getNodes(),
            'context' => [
                'nodes' => $nodes,
            ],
        ];

        return view('node::catalog.tree', $data);
    }

    public function sort(Request $request, Catalog $catalog)
    {
        $catalog->updatePositions($request->input('positions'));

        return response('');
    }

    /**
     * 检查目录是否存在
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function exists($id)
    {
        return response([
            'exists' => !empty(Catalog::find($id)),
        ]);
    }
}
