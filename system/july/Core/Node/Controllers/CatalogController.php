<?php

namespace July\Core\Node\Controllers;

use App\Http\Controllers\Controller;
use App\Utils\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use July\Core\Node\Catalog;
use July\Core\Node\Node;
use July\Core\Node\NodeField;

class CatalogController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $catalogs = Catalog::all()->map(function($catalog) {
            return $catalog->gather();
        })->all();

        return view_with_langcode('backend::catalogs.index', [
            'catalogs' => $catalogs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view_with_langcode('backend::catalogs.create_edit', [
            'id' => null,
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
        $catalog = Catalog::make($request->all());
        $catalog->save();
        return Response::make($catalog);
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
        return view_with_langcode('backend::catalogs.create_edit', $catalog->gather());
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
        $catalog->contents()->detach();
        $catalog->delete();
        return response('');
    }

    public function sort(Catalog $catalog)
    {
        $data = $catalog->gather();

        $data['catalog_contents'] = $catalog->positions();

        // 非预设字段
        $exceptAttributes = NodeField::optionalFields()->pluck('id')->all();

        // 获取所有节点信息，排除信息中的非预设字段
        $data['all_contents'] = Node::all()->map(function($content) use($exceptAttributes) {
            return Arr::except($content->gather(), $exceptAttributes);
        })->keyBy('id')->all();

        return view_with_langcode('backend::catalogs.sort', $data);
    }

    public function updateOrders(Request $request, Catalog $catalog)
    {
        $catalog->updatePositions($request->input('catalog_contents'));
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
