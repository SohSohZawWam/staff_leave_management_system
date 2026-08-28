@extends('layouts.app')

@section('title', __('department_head.pending_title'))

@section('content')
<div class="space-y-6">
    <div class="cu-card-header">
        <div>
            <h2 class="cu-page-title">{{ __('department_head.pending_title') }}</h2>
            <p class="cu-muted mt-1">{{ __('department_head.review_applications') }}</p>
        </div>
    </div>

    <div class="cu-card cu-card-body">
        <form method="GET" action="{{ route('department-head.approvals.pending') }}" class="mb-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('common.search') }}..." class="cu-input">
                </div>
                <div>
                    <select name="leave_type_id" class="cu-select">
                        <option value="">{{ __('common.all_leave_types') }}</option>
                        @foreach($leaveTypes as $type)
                            <option value="{{ $type->id }}" {{ request('leave_type_id') == $type->id ? 'selected' : '' }}>
                                {{ app()->getLocale() == 'my' ? $type->name_mm ?? $type->name : $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="cu-btn-secondary w-full">{{ __('common.filter') }}</button>
                </div>
            </div>
        </form>
    </div>

    @if($pendingRequests->isEmpty())
        <div class="cu-card cu-card-body text-center">
            <p class="text-slate-500">{{ __('department_head.no_pending_requests') }}</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($pendingRequests as $request)
                <div class="cu-card cu-card-body">
                    <div class="flex justify-between items-start gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ app()->getLocale() == 'my' ? ($request->user->name_mm ?? $request->user->name) : $request->user->name }}
                            </h3>
                            <p class="text-sm text-slate-500">{{ my_number($request->user->staff_id) }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="cu-badge-warning">{{ __('common.pending') }}</span>
                            <a href="{{ route('department-head.approvals.show', $request) }}" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition-colors" title="{{ __('common.view_details') }}">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="cu-muted">{{ __('common.leave_type') }}</p>
                            <p class="text-sm font-semibold text-slate-800">
                                {{ app()->getLocale() == 'my' ? ($request->leaveType->name_mm ?? $request->leaveType->name) : $request->leaveType->name }}
                            </p>
                        </div>
                        <div>
                            <p class="cu-muted">{{ __('common.duration') }}</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $request->leaveType->is_not_limited ? '-' : my_number($request->total_days) . ' ' . __('common.days') }}</p>
                        </div>
                        <div>
                            <p class="cu-muted">{{ __('common.dates') }}</p>
                            <p class="text-sm font-semibold text-slate-800">
                                {{\App\Support\MyanmarDateFormatter::format($request->start_date, 'F d, Y')}} – {{ $request->end_date ? \App\Support\MyanmarDateFormatter::format($request->end_date, 'F d, Y') : __('common.unlimited') }}
                            </p>
                        </div>
                        @if($request->duty_exchange_user_id && $request->dutyExchangeUser)
                            <div>
                                <p class="cu-muted">{{ __('common.duty_exchange') }}</p>
                                <p class="text-sm font-semibold text-slate-800">
                                    {{ app()->getLocale() == 'my' ? ($request->dutyExchangeUser->name_mm ?? $request->dutyExchangeUser->name) : $request->dutyExchangeUser->name }}
                                </p>
                            </div>
                        @endif
                        <div>
                            <p class="cu-muted">{{ __('common.submitted') }}</p>
                            <p class="text-sm font-semibold text-slate-800">{{\App\Support\MyanmarDateFormatter::diffForHumans($request->created_at)}}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $pendingRequests->links() }}
        </div>
    @endif
</div>
@endsection
