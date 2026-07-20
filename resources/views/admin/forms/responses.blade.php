<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __(':name — Responses', ['name' => $form->name]) }}
        </h2>
    </x-slot>

    @if ($responses->isEmpty())
        <p class="text-sm text-gray-500">{{ __('No responses submitted yet.') }}</p>
    @else
        <div class="space-y-4">
            @foreach ($responses as $response)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-gray-900">{{ $response->appointment->customer->name }}</p>
                        <p class="text-xs text-gray-500">{{ $response->created_at->toFormattedDateString() }}</p>
                    </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                        @foreach ($form->fields_json ?? [] as $field)
                            <div>
                                <dt class="text-gray-500">{{ $field['label'] }}</dt>
                                <dd class="text-gray-900">
                                    @php $answer = $response->form_data_json[$field['id']] ?? null; @endphp
                                    @if (is_bool($answer))
                                        {{ $answer ? __('Yes') : __('No') }}
                                    @else
                                        {{ $answer ?? '—' }}
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endforeach
        </div>
    @endif
</x-admin-layout>
