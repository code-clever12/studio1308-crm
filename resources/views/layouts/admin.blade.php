<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ __('Admin') }} - {{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/admin.css', 'resources/js/admin.js'])
    </head>
    <body class="font-sans antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-100">
            <!-- Sidebar -->
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed inset-y-0 left-0 z-40 w-64 bg-gray-900 text-gray-100 transform transition-transform duration-150 ease-in-out lg:translate-x-0"
            >
                <div class="h-16 flex items-center px-6 border-b border-gray-800">
                    <a href="{{ route('admin.dashboard') }}" class="text-lg font-semibold tracking-tight">
                        {{ config('app.name', 'Laravel') }} <span class="text-gray-400 font-normal">Admin</span>
                    </a>
                </div>

                <nav class="px-3 py-4 space-y-1">
                    <x-admin-nav-link route="admin.dashboard">{{ __('Dashboard') }}</x-admin-nav-link>
                    <x-admin-nav-link route="admin.appointments.index" active="admin.appointments.*">{{ __('Appointments') }}</x-admin-nav-link>
                    <x-admin-nav-link route="admin.staff.index" active="admin.staff.*">{{ __('Staff') }}</x-admin-nav-link>
                    <x-admin-nav-link route="admin.services.index" active="admin.services.*">{{ __('Services') }}</x-admin-nav-link>
                    <x-admin-nav-link route="admin.customers.index" active="admin.customers.*">{{ __('Customers') }}</x-admin-nav-link>
                    <x-admin-nav-link route="admin.forms.index" active="admin.forms.*">{{ __('Consent Forms') }}</x-admin-nav-link>
                    <x-admin-nav-link route="admin.form-submissions.index" active="admin.form-submissions.*">{{ __('Form Submissions') }}</x-admin-nav-link>

                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Reports') }}</p>
                    <x-admin-nav-link route="admin.reports.commission">{{ __('Commission') }}</x-admin-nav-link>
                    <x-admin-nav-link route="admin.reports.tips">{{ __('Tips') }}</x-admin-nav-link>
                    <x-admin-nav-link route="admin.reports.tax">{{ __('Sales Tax') }}</x-admin-nav-link>

                    <p class="px-3 pt-4 pb-1 text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Business') }}</p>
                    <x-admin-nav-link route="admin.payouts.index">{{ __('Payouts') }}</x-admin-nav-link>
                    <x-admin-nav-link route="admin.settings.edit">{{ __('Settings') }}</x-admin-nav-link>
                </nav>
            </aside>

            <!-- Mobile overlay -->
            <div
                x-show="sidebarOpen"
                x-cloak
                @click="sidebarOpen = false"
                class="fixed inset-0 z-30 bg-black/50 lg:hidden"
            ></div>

            <div class="lg:pl-64">
                <!-- Topbar -->
                <header class="h-16 bg-white shadow-sm flex items-center justify-between px-4 sm:px-6 lg:px-8">
                    <button @click="sidebarOpen = ! sidebarOpen" class="lg:hidden text-gray-500 hover:text-gray-700">
                        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    @isset($header)
                        <div class="flex-1">{{ $header }}</div>
                    @else
                        <div class="flex-1"></div>
                    @endisset

                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                {{ __('Log Out') }}
                            </button>
                        </form>
                    </div>
                </header>

                <!-- Page Content -->
                <main class="p-4 sm:p-6 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
