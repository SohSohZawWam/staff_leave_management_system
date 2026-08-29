<?php

namespace App\Services;

use App\Models\Department;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    private function localizedName(string $name, ?string $nameMm): string
    {
        return app()->getLocale() === 'my' && ! empty($nameMm) ? $nameMm : $name;
    }

    public function getDashboardStatistics(?User $user = null): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $pendingLevel = $user?->isSuperAdmin() ? 3 : 2;

        return [
            'total_staff' => User::where('role', 'staff')->count(),
            'total_departments' => Department::count(),
            'pending_requests' => LeaveRequest::where('status', 'pending')
                ->where('current_approval_level', $pendingLevel)
                ->count(),
            'approved_today' => LeaveRequest::where('status', 'approved')
                ->whereDate('reviewed_at', $today)
                ->count(),
            'rejected_today' => LeaveRequest::where('status', 'rejected')
                ->whereDate('reviewed_at', $today)
                ->count(),
            'approved_this_month' => LeaveRequest::where('status', 'approved')
                ->whereDate('reviewed_at', '>=', $monthStart)
                ->count(),
            'rejected_this_month' => LeaveRequest::where('status', 'rejected')
                ->whereDate('reviewed_at', '>=', $monthStart)
                ->count(),
        ];
    }

    public function getLeaveStatisticsByType(array $filters = []): array
    {
        $query = LeaveRequest::query()
            ->with(['leaveType'])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters) {
                $query->whereDate('start_date', '>=', $filters['start_date']);
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters) {
                $query->whereDate('end_date', '<=', $filters['end_date']);
            })
            ->when(! empty($filters['year']), function ($query) use ($filters) {
                $query->whereYear('start_date', $filters['year']);
            })
            ->where('status', 'approved')
            ->groupBy('leave_type_id');

        return $query->select('leave_type_id', DB::raw('SUM(total_days) as total_days'))
            ->with('leaveType')
            ->get()
            ->map(function ($item) {
                return [
                    'leave_type' => $this->localizedName($item->leaveType->name, $item->leaveType->name_mm),
                    'total_days' => (float) $item->total_days,
                    'is_not_limited' => $item->leaveType->is_not_limited,
                ];
            })
            ->values()
            ->toArray();
    }

    public function getDepartmentLeaveStatistics(array $filters = []): array
    {
        $query = LeaveRequest::query()
            ->with(['user.department', 'leaveType'])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters) {
                $query->whereDate('start_date', '>=', $filters['start_date']);
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters) {
                $query->whereDate('end_date', '<=', $filters['end_date']);
            })
            ->when(! empty($filters['year']), function ($query) use ($filters) {
                $query->whereYear('start_date', $filters['year']);
            })
            ->where('status', 'approved');

        $grouped = $query->get()->groupBy('user.department.name')->map(function ($items) {
            $total = 0;
            foreach ($items as $item) {
                $total += $item->leaveType->is_not_limited ? 0 : $item->total_days;
            }

            return $total;
        });

        return $grouped->map(function ($total, $department) {
            $deptName = $department;
            if (app()->getLocale() === 'my') {
                $deptModel = Department::where('name', $department)->first();
                $deptName = $deptModel ? ($deptModel->name_mm ?? $deptModel->name) : $department;
            }

            return [
                'department' => $deptName,
                'total_days' => (float) $total,
                'is_not_limited' => false,
            ];
        })->values()->toArray();
    }

    public function getLeaveSummary(array $filters = []): array
    {
        $query = LeaveRequest::query()
            ->with(['user.department', 'leaveType', 'reviewer'])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['start_date']), function ($query) use ($filters) {
                $query->whereDate('start_date', '>=', $filters['start_date']);
            })
            ->when(! empty($filters['end_date']), function ($query) use ($filters) {
                $query->whereDate('end_date', '<=', $filters['end_date']);
            })
            ->when(! empty($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->orderByDesc('start_date');

        return $query->get()->map(function ($item) {
            return [
                'staff_name' => $this->localizedName($item->user->name, $item->user->name_mm),
                'staff_id' => $item->user->staff_id ?? '—',
                'department' => $item->user->department ? $this->localizedName($item->user->department->name, $item->user->department->name_mm) : '—',
                'leave_type' => $this->localizedName($item->leaveType->name, $item->leaveType->name_mm),
                'start_date' => $item->start_date->format('Y-m-d'),
                'end_date' => $item->end_date?->format('Y-m-d') ?? '—',
                'total_days' => $item->total_days,
                'is_not_limited' => $item->leaveType->is_not_limited,
                'status' => $item->status,
                'reviewer' => $item->reviewer ? $this->localizedName($item->reviewer->name, $item->reviewer->name_mm) : '—',
                'reviewed_at' => $item->reviewed_at ? $item->reviewed_at->format('Y-m-d') : '—',
                'profile_image' => $item->user->profile_image,
            ];
        })->values()->toArray();
    }

    public function getLeaveBalances(array $filters = []): array
    {
        $year = $filters['year'] ?? now()->year;

        $query = LeaveBalance::query()
            ->with(['user.department', 'leaveType'])
            ->where('year', $year)
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('department_id', $filters['department_id']);
                });
            })
            ->when(! empty($filters['staff_name']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where(function ($sub) use ($filters) {
                        $sub->where('name', 'like', '%'.$filters['staff_name'].'%')
                            ->when(app()->getLocale() === 'my', function ($q2) use ($filters) {
                                $q2->orWhere('name_mm', 'like', '%'.$filters['staff_name'].'%');
                            });
                    });
                });
            })
            ->when(! empty($filters['leave_type_id']), function ($query) use ($filters) {
                $query->where('leave_type_id', $filters['leave_type_id']);
            })
            ->orderBy('user_id');

        return $query->get()->map(function ($item) {
            return [
                'staff_name' => $this->localizedName($item->user->name, $item->user->name_mm),
                'staff_id' => $item->user->staff_id ?? '—',
                'department' => $item->user->department ? $this->localizedName($item->user->department->name, $item->user->department->name_mm) : '—',
                'leave_type' => $this->localizedName($item->leaveType->name, $item->leaveType->name_mm),
                'allocated_days' => $item->allocated_days,
                'used_days' => $item->used_days,
                'remaining_days' => $item->remaining_days,
                'is_not_limited' => $item->leaveType->is_not_limited,
                'profile_image' => $item->user->profile_image,
            ];
        })->values()->toArray();
    }

    public function getDepartmentAnalytics(array $filters = []): array
    {
        $year = $filters['year'] ?? now()->year;

        $query = Department::query()
            ->withCount(['users as staff_count' => function ($query) {
                $query->where('is_active', true);
            }])
            ->when(! empty($filters['department_id']), function ($query) use ($filters) {
                $query->where('id', $filters['department_id']);
            })
            ->addSelect(['total_leave_days' => LeaveRequest::selectRaw('SUM(total_days)')
                ->join('users', 'leave_requests.user_id', '=', 'users.id')
                ->whereColumn('users.department_id', 'departments.id')
                ->where('leave_requests.status', 'approved')
                ->whereYear('leave_requests.start_date', $year)
                ->when(! empty($filters['month']), function ($q) use ($filters) {
                    $q->whereMonth('leave_requests.start_date', $filters['month']);
                }),
            ]);

        return $query->get()->map(function ($department) {
            $deptName = app()->getLocale() === 'my' ? ($department->name_mm ?? $department->name) : $department->name;

            return [
                'department' => $deptName,
                'staff_count' => $department->staff_count,
                'total_leave_days' => $department->total_leave_days ?? 0,
            ];
        })->values()->toArray();
    }
}
