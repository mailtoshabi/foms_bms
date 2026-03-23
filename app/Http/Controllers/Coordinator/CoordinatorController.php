<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Base\BaseServiceController;
use Illuminate\Http\Request;

class CoordinatorController extends BaseServiceController
{
    public function addClass(Request $request)
    {
        $this->classService->create($request->all());

        return back()->with('success','Class created');
    }
}
