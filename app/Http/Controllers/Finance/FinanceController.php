<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\FeeService;

class FinanceController extends Controller
{

    protected $feeService;

    public function __construct(FeeService $feeService)
    {
        $this->feeService = $feeService;
    }

    public function index()
    {
        return view('finance.dashboard');
    }

    public function store()
    {
        $this->feeService->createFee(request()->all());

        return back()->with('success','Fee created');
    }

    public function dues()
    {
        //
    }
}

