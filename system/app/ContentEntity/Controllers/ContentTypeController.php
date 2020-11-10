<?php

namespace App\ContentEntity\Controllers;

use App\ContentEntity\Models\Content;
use App\ContentEntity\Models\ContentField;
use App\ContentEntity\Models\ContentType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ContentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $contentCount = Content::countByType();
        $contentTypes = ContentType::all()->map(function($contentType) {
            return $contentType->attributesToArray();
        })->all();
        foreach ($contentTypes as &$contentType) {
            $contentType['contents'] = $contentCount[$contentType['truename']] ?? 0;
        }
        unset($contentType);

        return view_with_langcode('backend::content_types.index', [
            'contentTypes' => $contentTypes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $optionalFields = ContentField::localFields()
                            ->map(function($field) {
                                return $field->gather();
                            })
                            ->keyBy('truename')
                            ->all();

        return view_with_langcode('backend::content_types.create_edit', [
            'truename' => null,
            'label' => null,
            'description' => null,
            'fields' => ContentField::presetLocalFields()->pluck('truename')->all(),
            'availableFields' => $optionalFields,
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
        $contentType = ContentType::make($request->all());
        $contentType->save();
        $contentType->updateFields($request->input('fields', []));
        return response('');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function show(ContentType $contentType)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function edit(ContentType $contentType)
    {
        if ('default' === $contentType->getKey()) {
            abort(404);
        }
        $fields = collect($contentType->cacheGetFields())->keyBy('truename');

        $data = $contentType->gather();
        $data['fields'] = $fields->keys()->all();
        $data['availableFields'] = ContentField::localFields()
                                    ->map(function($field) {
                                        return $field->gather();
                                    })
                                    ->keyBy('truename')
                                    ->replace($fields)
                                    ->all();

        return view_with_langcode('backend::content_types.create_edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ContentType $contentType)
    {
        $contentType->update($request->all());
        $contentType->updateFields($request->input('fields', []));
        return response('');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ContentType  $contentType
     * @return \Illuminate\Http\Response
     */
    public function destroy(ContentType $contentType)
    {
        $contentType->fields()->detach();
        $contentType->delete();
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
            'exists' => !empty(ContentType::find($id)),
        ]);
    }
}
