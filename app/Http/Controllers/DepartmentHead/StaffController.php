<?php

namespace App\Http\Controllers\DepartmentHead;

use App\Http\Controllers\Controller;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $departmentId = $user->department_id;

        $staff = User::where('department_id', $departmentId)
            ->with('department')
            ->orderBy('name')
            ->get();

        return view('department-head.staff.index', compact('staff'));
    }

    public function show(User $user)
    {
        $departmentHead = auth()->user();

        if ($user->department_id !== $departmentHead->department_id) {
            abort(403);
        }

        $user->load('department', 'leaveBalances.leaveType', 'leaveRequests.leaveType');

        return view('department-head.staff.show', compact('user'));
    }
}
