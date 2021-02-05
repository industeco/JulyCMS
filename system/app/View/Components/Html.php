<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Html extends Component
{
    public $field;

    public $value;

    public $model;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(array $field, $value = null, string $model = 'model')
    {
        $this->field = $field;
        $this->value = $value;
        $this->model = $model;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.html');
    }
}
