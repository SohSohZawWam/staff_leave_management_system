@component('mail::message')
# {{ $texts['subject'] }}

{{ str_replace(':name', $recipientName ?? $leaveRequest->user->name, $texts['greeting']) }}

{{ $texts['intro'] }}

@component('mail::table')
| {{ $texts['leave_type'] }} | {{ $texts['duration'] }} | {{ $texts['dates'] }} | {{ $texts['status'] }} |
|--------------|--------------|--------------|--------------|
 | {{ $leaveTypeName ?? $leaveRequest->leaveType->name }} | {{ $leaveRequest->leaveType->is_not_limited ? '-' : my_number($leaveRequest->total_days) . ' ' . __('common.days') }} | {{\App\Support\MyanmarDateFormatter::format($leaveRequest->start_date, 'F d, Y')}} — {{ $leaveRequest->end_date ? \App\Support\MyanmarDateFormatter::format($leaveRequest->end_date, 'F d, Y') : __('common.unlimited') }} | {{ __('common.' . $status) }} |
@endcomponent

@component('mail::button', ['url' => $url ?? route('staff.leave-requests.show', $leaveRequest)])
{{ $texts['action'] }}
@endcomponent

@if($leaveRequest->review_remarks && isset($texts['remarks']))
{{ $texts['remarks'] }}: {{ $leaveRequest->review_remarks }}
@endif

{{ $texts['outro'] }}<br>
{{ $texts['signature'] }}
@endcomponent
