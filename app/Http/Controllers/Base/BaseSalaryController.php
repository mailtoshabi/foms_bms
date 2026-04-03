<?php

namespace App\Http\Controllers\Base;

use Illuminate\Http\Request;
use App\Http\Controllers\Base\BaseServiceController;

class BaseSalaryController extends BaseServiceController
{

    protected $viewPrefix;
    protected $routePrefix;

    public function store($id)
    {

        $this->salaryService->processTeacherSalary(decrypt($id));

        return redirect()->route($this->routePrefix.'.index')
            ->with('success','Class created successfully');
    }

}
