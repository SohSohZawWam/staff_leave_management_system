<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Carbon\Carbon;

class LeaveWorkflowService
{
    public function __construct(
        private LeaveBalanceService $leaveBalanceService,
    ) {}

    public function processApproval(LeaveRequest $leaveRequest, User $reviewer, string $status, ?string $remarks = null, ?int $nextApprovalLevel = null): LeaveRequest
    {
        if ($status === 'rejected') {
            $leaveRequest->status = 'rejected';
            $leaveRequest->reviewer_id = $reviewer->id;
            $leaveRequest->review_remarks = $remarks;
            $leaveRequest->reviewed_at = Carbon::now();

            $leaveRequest->save();

            return $leaveRequest;
        }

        $leaveRequest->reviewer_id = $reviewer->id;
        $leaveRequest->review_remarks = $remarks;
        $leaveRequest->reviewed_at = Carbon::now();

        if ($nextApprovalLevel !== null) {
            $leaveRequest->current_approval_level = $nextApprovalLevel;
        } elseif ($leaveRequest->current_approval_level === 1 && $status === 'approved') {
            $leaveRequest->current_approval_level = 2;
        } elseif ($leaveRequest->current_approval_level === 2 && $status === 'approved') {
            $leaveRequest->status = 'approved';
            $this->leaveBalanceService->updateUsedDays(
                $leaveRequest->user,
                $leaveRequest->leaveType,
                $leaveRequest->total_days
            );
        }

        $leaveRequest->save();

        return $leaveRequest;
    }

    public function finalizeApproval(LeaveRequest $leaveRequest, User $hrUser, string $status, ?string $remarks = null): LeaveRequest
    {
        $leaveRequest->status = $status;
        $leaveRequest->hr_id = $hrUser->id;
        $leaveRequest->review_remarks = $remarks;
        $leaveRequest->reviewed_at = Carbon::now();

        if ($status === 'approved') {
            $this->leaveBalanceService->updateUsedDays(
                $leaveRequest->user,
                $leaveRequest->leaveType,
                $leaveRequest->total_days
            );
        }

        $leaveRequest->save();

        return $leaveRequest;
    }

    public function approveDirectly(LeaveRequest $leaveRequest, User $approver, ?string $remarks = null): LeaveRequest
    {
        $leaveRequest->status = 'approved';
        $leaveRequest->reviewer_id = $approver->id;
        $leaveRequest->review_remarks = $remarks;
        $leaveRequest->reviewed_at = Carbon::now();

        $this->leaveBalanceService->updateUsedDays(
            $leaveRequest->user,
            $leaveRequest->leaveType,
            $leaveRequest->total_days
        );

        $leaveRequest->save();

        return $leaveRequest;
    }

    public function revokeApproval(LeaveRequest $leaveRequest, User $cancelledBy, ?string $reason = null): LeaveRequest
    {
        $leaveRequest->status = 'revoked';
        $leaveRequest->cancelled_by_id = $cancelledBy->id;
        $leaveRequest->cancelled_at = Carbon::now();
        $leaveRequest->cancellation_reason = $reason;

        if ($leaveRequest->isApproved()) {
            $this->leaveBalanceService->updateUsedDays(
                $leaveRequest->user,
                $leaveRequest->leaveType,
                -$leaveRequest->total_days
            );
        }

        $leaveRequest->save();

        return $leaveRequest;
    }

    public function calculateTotalDays(string $startDate, string $endDate, bool $halfDay = false): int|float
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        $holidays = Holiday::whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($holiday) => $holiday->date->toDateString());

        $defaultWeekendHolidays = Holiday::where('is_default', true)->get()->keyBy('id');

        $workingDays = 0;
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dayOfWeek = $date->dayOfWeek;
            $dateString = $date->toDateString();
            $isWeekend = $dayOfWeek === Carbon::SATURDAY || $dayOfWeek === Carbon::SUNDAY;
            $isReplacementWorkingDay = false;

            if ($isWeekend) {
                $holiday = $holidays->get($dateString);

                if ($holiday && $holiday->replaced_holiday_id && isset($defaultWeekendHolidays[$holiday->replaced_holiday_id])) {
                    $isReplacementWorkingDay = true;
                }

                if (! $isReplacementWorkingDay) {
                    continue;
                }
            }

            if ($holidays->has($dateString) && ! $isReplacementWorkingDay) {
                continue;
            }

            $workingDays++;
        }

        if ($halfDay) {
            return (int) ceil($workingDays / 2);
        }

        return $workingDays;
    }

    public function validateLeaveBalance(User $user, int $leaveTypeId, int $requestedDays): bool
    {
        $leaveType = LeaveType::findOrFail($leaveTypeId);
        $remainingDays = $this->leaveBalanceService->getRemainingDays($user, $leaveType, now()->year);

        return $remainingDays >= $requestedDays;
    }

    public function validateNoOverlap(User $user, string $startDate, string $endDate, ?int $excludeId = null): bool
    {
        $query = $user->leaveRequests()
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->doesntExist();
    }
}
