<?php

namespace App\Http\Controllers;

use App\Models\Catalog;
use App\Models\Node;
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
        return view_with_langcode('admin::catalogs.index', [
            'catalogs' => mix_config(Catalog::all()),
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
        return view_with_langcode('admin::catalogs.create_edit', $catalog->mixConfig());
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
        return Response::make();
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
        return Response::make();
    }

    public function reorder(Catalog $catalog)
    {
        return view_with_langcode('admin::catalogs.reorder', [
            'truename' => $catalog->truename,
            'catalog_nodes' => $catalog->positions(),
            'all_nodes' => Node::allNodes(),
        ]);
    }

    public function sort(Request $request, Catalog $catalog)
    {
        // return Response::make($request->input('catalog_nodes'));
        $catalog->updatePositions($request->input('catalog_nodes'));
        return Response::make();
    }

    /**
     * 检查主键是否重复
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function unique($id)
    {
        return Response::make([
            'exists' => !empty(Catalog::find($id)),
        ]);
    }
}
