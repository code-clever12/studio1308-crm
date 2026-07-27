<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FormResource;
use App\Models\Form;
use Illuminate\Http\JsonResponse;

class FormController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => FormResource::collection(Form::orderBy('name')->get()),
        ]);
    }
}
