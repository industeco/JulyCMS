<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Content;
use App\Models\ContentField;
use App\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

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

        return view_with_langcode('admin::catalogs.index', [
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
        return view_with_langcode('admin::catalogs.create_edit', [
            'truename' => null,
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
        return view_with_langcode('admin::catalogs.create_edit', $catalog->gather());
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
        $exceptAttributes = ContentField::commonFields()->pluck('truename')->all();

        // 获取所有节点信息，排除信息中的非预设字段
        $data['all_contents'] = Content::all()->map(function($content) use($exceptAttributes) {
            return Arr::except($content->gather(), $exceptAttributes);
        })->keyBy('id')->all();

        return view_with_langcode('admin::catalogs.sort', $data);
    }

    public function updateOrders(Request $request, Catalog $catalog)
    {
        $catalog->updatePositions($request->input('catalog_contents'));
        return response('');
    }

    /**
     * 检查主键是否重复
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function unique($id)
    {
        return response([
            'exists' => !empty(Catalog::find($id)),
        ]);
    }
}
