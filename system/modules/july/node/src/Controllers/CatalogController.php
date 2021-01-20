<?php

namespace July\Node\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
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
        return view('backend::catalog.index', [
            'catalogs' => Catalog::all()->map(function($catalog) {
                return $catalog->gather();
            })->all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend::catalog.create_edit', [
            'id' => null,
            'langcode' => langcode('backend'),
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
        return response(Catalog::make($request->all())->save());
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Catalog  $catalog
     * @return \Illuminate\Http\Response
     */
    public function edit(Catalog $catalog)
    {
        return view('backend::catalog.create_edit', array_merge($catalog->gather(), ['langcode' => langcode('backend')]));
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
        return response($catalog->update($request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Catalog  $catalog
     * @return \Illuminate\Http\Response
     */
    public function destroy(Catalog $catalog)
    {
        $catalog->nodes()->detach();
        $catalog->delete();

        return response('');
    }

    public function sort(Catalog $catalog)
    {
        $data = [
            'catalog' => $catalog->gather(),
            'positions' => $catalog->positions(),
        ];

        // 非预设字段
        $keys = NodeField::normalFields()->pluck('id')->all();

        // 获取所有节点数据，并排除数据中的常规字段
        $data['nodes'] = Node::all()->map(function (Node $node) use ($keys) {
            return Arr::except($node->gather(), $keys);
        })->keyBy('id')->all();

        return view('backend::catalog.sort', $data);
    }

    public function updateOrders(Request $request, Catalog $catalog)
    {
        $catalog->updatePositions($request->input('positions'));

        return response('');
    }

    /**
     * 检查目录是否存在
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function isExist(string $id)
    {
        return response([
            'is_exist' => !empty(Catalog::find($id)),
        ]);
    }
}
