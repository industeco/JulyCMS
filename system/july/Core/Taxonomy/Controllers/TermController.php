<?php

namespace July\Core\Taxonomy\Controllers;

use App\Http\Controllers\Controller;
use July\Core\Taxonomy\Term;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TermController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (config('jc.language.multiple')) {
            $tags = Term::all();
        } else {
            $tags = Term::query()->where('langcode', langcode('content'))->get();
        }

        return view_with_langcode('backend::tags.index', [
            'tags' => $tags->keyBy('tag')->toArray(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \July\Core\Taxonomy\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function show(Term $term)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \July\Core\Taxonomy\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function edit(Term $term)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        Term::saveChange($request->input('changed'));
        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \July\Core\Taxonomy\Term  $term
     * @return \Illuminate\Http\Response
     */
    public function destroy(Term $term)
    {
        //
    }
}
