<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    /**
     * 获取所有实体类型，作为引用型字段的引用范围
     *
     * @return
     */
    public function getEntityTypes()
    {
        $results = [];
        foreach (config('app.entities') as $class) {
            $value = $class::getEntityName();
            $label = Str::studly($value);
            $children = [];
            foreach ($class::getMoldClass()::all() as $mold) {
                $children[] = [
                    'value' => $mold->id,
                    'label' => $mold->label,
                ];
            }
            $results[] = compact('value', 'label', 'children');
        }

        return response($results);
    }
}
