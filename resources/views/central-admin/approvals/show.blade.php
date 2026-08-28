@extends('layouts.app')

@section('title', __('central_admin.pending_title'))

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="cu-card cu-card-body">
        <div class="flex justify-between items-start mb-6 gap-4">
            <div>
                <h2 class="cu-page-title">{{ __('central_admin.review_applications') }}</h2>
                <p class="cu-muted mt-1">{{ __('common.application_summary') }}</p>
            </div>
        @if(auth()->user()->isAdmin() && ! auth()->user()->isSuperAdmin())
            <div class="border-t border-slate-100 pt-6">
                <h3 class="cu-section-title mb-4">{{ __('central_admin.assignment_info') }}</h3>
                <p class="text-sm text-slate-600">{{ __('central_admin.assignment_description') }}</p>
            </div>
        @endif

        @if($leaveRequest->current_approval_level === 2)
                <span class="cu-badge-warning">{{ __('common.pending') }}</span>
            @elseif($leaveRequest->status === 'approved')
                <span class="cu-badge-success">{{ __('common.approved') }}</span>
            @endif
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            <div>
                <p class="cu-muted">{{ __('common.staff') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ app()->getLocale() == 'my' ? ($leaveRequest->user->name_mm ?? $leaveRequest->user->name) : $leaveRequest->user->name }}
                </p>
                <p class="text-sm text-slate-500">{{ my_number($leaveRequest->user->staff_id) }}</p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.leave_type') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ app()->getLocale() == 'my' ? ($leaveRequest->leaveType->name_mm ?? $leaveRequest->leaveType->name) : $leaveRequest->leaveType->name }}
                </p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.total_days') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ $leaveRequest->leaveType->is_not_limited ? '-' : my_number($leaveRequest->total_days) . ' ' . __('common.days') }}
                </p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.start_date') }}</p>
                <p class="text-base font-semibold text-slate-900">{{\App\Support\MyanmarDateFormatter::format($leaveRequest->start_date, 'l, F d, Y')}}</p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.end_date') }}</p>
                <p class="text-base font-semibold text-slate-900">
                    {{ $leaveRequest->end_date ? \App\Support\MyanmarDateFormatter::format($leaveRequest->end_date, 'l, F d, Y') : __('common.unlimited') }}
                </p>
            </div>
            <div>
                <p class="cu-muted">{{ __('common.submitted') }}</p>
                <p class="text-base font-semibold text-slate-900">{{\App\Support\MyanmarDateFormatter::diffForHumans($leaveRequest->created_at)}}</p>
            </div>
        </div>

        <div class="mb-6 rounded-xl bg-slate-50 border border-slate-100 p-4">
            <p class="cu-muted">{{ __('common.reason') }}</p>
            <p class="text-base text-slate-800 mt-1">{{ $leaveRequest->reason }}</p>
        </div>

        @if($leaveRequest->attachment_path)
            @php $attachments = json_decode($leaveRequest->attachment_path, true) ?: [$leaveRequest->attachment_path]; @endphp
            <div class="mb-6">
                <p class="cu-muted">{{ __('common.attachment') }}</p>
                <div class="mt-1 space-y-1">
                    @foreach($attachments as $path)
                        <a href="{{ Storage::url($path) }}" target="_blank" class="cu-link inline-flex items-center">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                            </svg>
                            {{ __('common.view_document') }} {{ $loop->iteration }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        @if($leaveRequest->duty_exchange_user_id && $leaveRequest->dutyExchangeUser)
            <div class="border-t border-slate-100 pt-6">
                <h3 class="cu-section-title mb-4">{{ __('common.duty_exchange') }}</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="cu-muted">{{ __('common.staff') }}</p>
                        <p class="text-base font-semibold text-slate-900">
                            {{ app()->getLocale() == 'my' ? ($leaveRequest->dutyExchangeUser->name_mm ?? $leaveRequest->dutyExchangeUser->name) : $leaveRequest->dutyExchangeUser->name }}
                        </p>
                    </div>
                    <div>
                        <p class="cu-muted">{{ __('common.position') }}</p>
                        <p class="text-base font-semibold text-slate-900">{{ $leaveRequest->dutyExchangeUser->position ?? '-' }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="border-t border-slate-100 pt-6">
            <h3 class="cu-section-title mb-4">{{ __('common.review_details') }}</h3>
            <div class="space-y-4">
                @if($leaveRequest->reviewer)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="cu-muted">{{ __('common.reviewed_by') }} ({{ __('common.department_head') }})</p>
                            <p class="text-base font-semibold text-slate-900">
                                {{ app()->getLocale() == 'my' ? ($leaveRequest->reviewer->name_mm ?? $leaveRequest->reviewer->name) : $leaveRequest->reviewer->name }}
                            </p>
                        </div>
                        <div>
                            <p class="cu-muted">{{ __('common.reviewed_date') }}</p>
                            <p class="text-base font-semibold text-slate-900">
                                    {{ \App\Support\MyanmarDateFormatter::format($leaveRequest->reviewed_at, 'F d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                @endif
                @if($leaveRequest->hr)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="cu-muted">{{ __('common.reviewed_by') }} (HR / Central Admin)</p>
                            <p class="text-base font-semibold text-slate-900">
                                {{ app()->getLocale() == 'my' ? ($leaveRequest->hr->name_mm ?? $leaveRequest->hr->name) : $leaveRequest->hr->name }}
                            </p>
                        </div>
                        <div>
                            <p class="cu-muted">{{ __('common.reviewed_date') }}</p>
                            <p class="text-base font-semibold text-slate-900">
                                    {{ \App\Support\MyanmarDateFormatter::format($leaveRequest->reviewed_at, 'F d, Y H:i') }}
                            </p>
                        </div>
                    </div>
                @endif
                @if($leaveRequest->review_remarks)
                    <div class="mt-4">
                        <p class="cu-muted">{{ __('common.remarks') }}</p>
                        <p class="text-base text-slate-800 mt-1">{{ $leaveRequest->review_remarks }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($leaveRequest->current_approval_level === 2)
            <div class="border-t border-slate-100 pt-6">
                <h3 class="cu-section-title mb-4">{{ __('common.actions') }}</h3>
                <div class="flex flex-col sm:flex-row gap-4">
                    <form action="{{ route('central-admin.approvals.approve', $leaveRequest) }}" method="POST" class="flex-1">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('common.approve') }}</label>
                            <input type="text" name="remarks"
                                   placeholder="{{ __('common.approval_remarks') }}"
                                   class="cu-input">
                            @error('remarks')
                                <p class="cu-form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="cu-btn-success w-full">{{ __('common.approve') }}</button>
                    </form>

                    <form action="{{ route('central-admin.approvals.reject', $leaveRequest) }}" method="POST" class="flex-1">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('common.reject') }}</label>
                            <input type="text" name="remarks"
                                   placeholder="{{ __('common.rejection_reason') }}"
                                   required
                                   class="cu-input">
                            @error('remarks')
                                <p class="cu-form-error">{{ $message }}</p>
                            @enderror
                        </div>
                        <button type="submit" class="cu-btn-danger w-full">{{ __('common.reject') }}</button>
                    </form>
                </div>
            </div>
        @elseif($leaveRequest->status === 'approved')
            <div class="border-t border-slate-100 pt-6">
                <h3 class="cu-section-title mb-4">{{ __('common.actions') }}</h3>
                <form action="{{ route('central-admin.approvals.revoke', $leaveRequest) }}" method="POST" class="max-w-md">
                    @csrf
                    <div class="mb-2">
                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('common.revoke') }}</label>
                        <input type="text" name="reason"
                               placeholder="{{ __('common.revocation_reason') }}"
                               required
                               class="cu-input">
                        @error('reason')
                            <p class="cu-form-error">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="cu-btn-danger">{{ __('common.revoke') }}</button>
                </form>
            </div>
        @endif

        <div class="border-t border-slate-100 pt-6 mt-6">
            <a href="{{ route('central-admin.approvals.pending') }}" class="cu-link">
                {{ __('common.back') }}
            </a>
        </div>
    </div>
</div>
@endsection
