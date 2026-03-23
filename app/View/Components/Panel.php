<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Panel extends Component
{
    public $layout;
    public $routePrefix;
    public $contentView;
    public $data;

    public function __construct($layout, $routePrefix, $contentView, $data = [])
    {
        $this->layout = $layout;
        $this->routePrefix = $routePrefix;
        $this->contentView = $contentView;
        $this->data = $data;
    }

    public function render()
    {
        return view($this->contentView, array_merge(
            ['layout' => $this->layout, 'routePrefix' => $this->routePrefix],
            $this->data
        ));
    }
}
