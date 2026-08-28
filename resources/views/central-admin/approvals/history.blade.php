@extends('layouts.app')

@section('title', __('central_admin.history_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('central_admin.history_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('central_admin.processed_applications') }}</p>
        </div>
    </div>

    <div class="cu-table-wrap overflow-x-auto">
        <table class="cu-table">
                <thead>
                    <tr>
                        <th>{{ __('common.number') }}</th>
                        <th>{{ __('common.name') }}</th>
                        <th>{{ __('common.leave_type') }}</th>
                        <th>{{ __('common.position') }}</th>
                        <th>{{ __('common.department') }}</th>
                        <th>{{ __('common.start_date') }}</th>
                        <th>{{ __('common.end_date') }}</th>
                        <th>{{ __('common.total_days') }}</th>
                        <th>{{ __('common.duty_exchange') }}</th>
                        <th>{{ __('common.attachment') }}</th>
                        <th>{{ __('common.status') }}</th>
                        <th>{{ __('department_head.reviewed_date') }}</th>
                        <th>{{ __('common.reviewed_by') }}</th>
                        <th>{{ __('common.actions') }}</th>
                    </tr>
                </thead>
            <tbody>
                @foreach($processedRequests as $request)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="primary">
                            <div class="flex items-center gap-2">
                                @if($request->user->profile_image)
                                    <img src="{{ asset('storage/' . $request->user->profile_image) }}" alt="{{ $request->user->name }}" class="w-8 h-8 rounded-full object-cover">
                                @else
                                    <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                @endif
                                {{ app()->getLocale() == 'my' ? $request->user->name_mm ?? $request->user->name : $request->user->name }}
                            </div>
                        </td>
                        <td>
                            {{ app()->getLocale() == 'my' ? ($request->leaveType->name_mm ?? $request->leaveType->name) : $request->leaveType->name }}
                        </td>
                        <td>
                            {{ app()->getLocale() == 'my' ? ($request->user->position_mm ?? $request->user->position) : $request->user->position }}
                        </td>
                        <td>
                            {{ $request->user->department ? (app()->getLocale() == 'my' ? ($request->user->department->name_mm ?? $request->user->department->name) : $request->user->department->name) : __('common.n_a') }}
                        </td>
                        <td>
                            {{\App\Support\MyanmarDateFormatter::format($request->start_date, 'F d, Y')}}
                        </td>
                        <td>
                            {{ $request->end_date ? \App\Support\MyanmarDateFormatter::format($request->end_date, 'F d, Y') : __('common.unlimited') }}
                        </td>
                        <td>{{ $request->leaveType->is_not_limited ? '-' : my_number($request->total_days) }}</td>
                        <td>
                            @if($request->duty_exchange_user_id && $request->dutyExchangeUser)
                                {{ app()->getLocale() == 'my' ? ($request->dutyExchangeUser->name_mm ?? $request->dutyExchangeUser->name) : $request->dutyExchangeUser->name }}
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td>
                            @if($request->attachment_path)
                                <span class="cu-badge-success">{{ __('common.yes') }}</span>
                            @else
                                <span class="cu-badge-neutral">{{ __('common.no') }}</span>
                            @endif
                        </td>
                        <td>
                            <span @class([
                                'cu-badge-success' => $request->status === 'approved',
                                'cu-badge-danger' => in_array($request->status, ['rejected', 'revoked']),
                                'cu-badge-neutral' => ! in_array($request->status, ['approved', 'rejected', 'revoked']),
                            ])>
                                {{ __('common.' . $request->status) }}
                            </span>
                        </td>
                        <td>{{ $request->reviewed_at ? \App\Support\MyanmarDateFormatter::format($request->reviewed_at, 'F d, Y') : __('common.n_a') }}</td>
                        <td>{{ app()->getLocale() == 'my' ? $request->hr->name_mm ?? $request->hr->name : $request->hr->name ?? __('common.n_a') }}</td>
                        <td>
                            <a href="{{ route('central-admin.approvals.show', $request) }}" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors inline-flex" title="{{ __('common.view') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $processedRequests->links() }}
    </div>
</div>
@endsection
