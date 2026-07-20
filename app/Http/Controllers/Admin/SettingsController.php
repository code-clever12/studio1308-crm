<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SettingsRequest;
use App\Models\Salon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Authorization is handled by the 'role:admin' route middleware.
 */
class SettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', ['salon' => Salon::query()->firstOrFail()]);
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        Salon::query()->firstOrFail()->update($request->validated());

        return redirect()->route('admin.settings.edit')->with('status', 'Settings updated.');
    }
}
