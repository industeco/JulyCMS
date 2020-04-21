<?php

namespace App\Traits;

use Illuminate\Support\Facades\Response;

trait CheckUnique
{
    /**
     * 检查 id 是否已存在
     *
     * @param  string|int  $id
     * @return \Illuminate\Http\Response
     */
    public function unique($id)
    {
        return Response::make([
            'exists' => $this->model && !empty($this->model::find($id)),
        ]);
    }
}
