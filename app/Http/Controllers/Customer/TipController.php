<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TipController extends Controller
{
    public function index(Request $request): View
    {
        return view('customer.tips-history', [
            'tips' => $request->user()->tips()->with(['staff.user', 'appointment.service'])->latest()->get(),
        ]);
    }
}
