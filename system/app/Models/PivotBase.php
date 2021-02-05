<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;

abstract class PivotBase extends ModelBase
{
    use AsPivot;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
