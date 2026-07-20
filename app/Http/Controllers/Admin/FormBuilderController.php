<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConsentFormRequest;
use App\Models\ConsentForm;
use App\Models\Salon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Authorization for this whole controller is handled by the 'role:admin'
 * route middleware — ConsentForm has no dedicated Policy.
 */
class FormBuilderController extends Controller
{
    public function index(): View
    {
        return view('admin.forms.index', ['forms' => ConsentForm::orderBy('name')->get()]);
    }

    public function create(): View
    {
        return view('admin.forms.create');
    }

    public function store(ConsentFormRequest $request): RedirectResponse
    {
        ConsentForm::create([
            'salon_id' => Salon::query()->firstOrFail()->id,
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'fields_json' => $request->validated('fields'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.forms.index')->with('status', 'Consent form created.');
    }

    public function edit(ConsentForm $form): View
    {
        return view('admin.forms.edit', ['form' => $form]);
    }

    public function update(ConsentFormRequest $request, ConsentForm $form): RedirectResponse
    {
        $form->update([
            'name' => $request->validated('name'),
            'description' => $request->validated('description'),
            'fields_json' => $request->validated('fields'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.forms.index')->with('status', 'Consent form updated.');
    }

    public function destroy(ConsentForm $form): RedirectResponse
    {
        $form->update(['is_active' => false]);

        return redirect()->route('admin.forms.index')->with('status', 'Consent form deactivated.');
    }

    /**
     * View submitted responses for a given appointment's consent form.
     */
    public function responses(ConsentForm $form): View
    {
        return view('admin.forms.responses', [
            'form' => $form,
            'responses' => $form->appointmentFormResponses()->with('appointment.customer')->latest()->get(),
        ]);
    }
}
