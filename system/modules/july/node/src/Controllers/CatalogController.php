<?php

namespace July\Node\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use July\Node\Catalog;
use July\Node\Node;

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
            'models' => Catalog::index()->all(),
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
        $data = [
            'model' => $catalog,
            'context' => [
                'positions' => $catalog->tree()->getNodes(),
                'nodes' => Node::index()->all(),
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
