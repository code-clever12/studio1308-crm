<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrowseController extends Controller
{
    public function index(Request $request): View
    {
        $services = Service::query()
            ->with('category')
            ->where('is_active', true)
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('price', '<=', $request->float('max_price')))
            ->orderBy('display_order')
            ->get();

        $staff = Staff::query()
            ->with('user')
            ->where('status', 'active')
            ->withAvg('reviews', 'rating')
            ->get();

        return view('customer.browse', compact('services', 'staff'));
    }
}
