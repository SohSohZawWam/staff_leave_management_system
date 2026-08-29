@extends('layouts.app')

@section('title', __('admin.users_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('admin.users_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('admin.users_subtitle') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.create') }}" class="cu-btn-primary">{{ __('admin.add_user') }}</a>
            <button type="button" onclick="document.getElementById('import-modal').classList.remove('hidden')" class="cu-btn-secondary">{{ __('admin.import_users') }}</button>
        </div>
    </div>

    <div id="import-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('admin.import_users') }}</h3>
                    <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="import_file" class="cu-label">{{ __('common.file') }}</label>
                        <input type="file" name="import_file" id="import_file" accept=".xlsx,.csv" class="cu-input" required>
                        <p class="text-xs text-slate-500 mt-1">{{ __('common.xlsx_or_csv_format') }}</p>
                        @error('import_file')
                            <p class="cu-form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.users.import-template') }}" class="cu-btn-secondary text-sm">{{ __('admin.download_template') }}</a>
                        <button type="button" onclick="document.getElementById('import-modal').classList.add('hidden')" class="cu-btn-secondary">{{ __('common.cancel') }}</button>
                        <button type="submit" class="cu-btn-primary">{{ __('common.next') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.email') }}</th>
                        <th>{{ __('common.role') }}</th>
                        <th>{{ __('common.department') }}</th>
                        <th>{{ __('common.position') }}</th>
                        <th>{{ __('common.staff_id') }}</th>
                        <th>{{ __('common.phone') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="primary">
                            <div class="flex items-center gap-2">
                                @if($user->profile_image)
                                    <img src="{{ asset('storage/' . $user->profile_image) }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                {{ app()->getLocale() == 'my' ? $user->name_mm ?? $user->name : $user->name }}
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span @class([
                                'cu-badge-admin' => $user->role === 'admin',
                                'cu-badge-info' => $user->role === 'department_head',
                                'cu-badge-neutral' => $user->role === 'staff',
                            ])>
                                {{ __('common.role.' . $user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->department ? (app()->getLocale() == 'my' ? ($user->department->name_mm ?? $user->department->name) : $user->department->name) : __('common.n_a') }}</td>
                        <td>{{ app()->getLocale() == 'my' ? $user->position_mm ?? $user->position : $user->position ?? $user->position_mm ?? __('common.n_a') }}</td>
                        <td>{{ $user->staff_id ? $user->staff_id : __('common.n_a') }}</td>
                        <td>{{ $user->phone ? my_number($user->phone) : __('common.n_a') }}</td>
                        <td>
                            <span @class([
                                'cu-badge-success' => $user->is_active,
                                'cu-badge-danger' => ! $user->is_active,
                            ])>
                                {{ $user->is_active ? __('common.staff.active') : __('common.staff.inactive') }}
                            </span>
                        </td>
                        <td class="space-x-3 flex items-center justify-center">
                            <a href="{{ route('admin.users.edit', $user) }}" class="cu-link">{{ __('common.edit') }}</a>
                            <a href="{{ route('admin.staff.show', $user) }}" class="cu-link">{{ __('common.view') }}</a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="cu-link-danger"
                                            data-confirm="{{ __('admin.delete_this_user') }}">{{ __('common.delete') }}</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
@endsection
