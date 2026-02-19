<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OrgStructureController extends Controller
{
    public function index()
    {
        $ceoId = auth()->user()->ceo_id;

        $departments = Department::with(['allChildren', 'users.superior'])
            ->where('ceo_id', $ceoId)
            ->whereNull('parent_id')
            ->get();

        $allDepartmentsFlat = $this->flattenDepartments($departments);

        $unassignedUsers = User::where('ceo_id', $ceoId)
            ->whereNull('department_id')
            ->get();
            
        $allUsers = User::select('id', 'name', 'role', 'superior_id', 'department_id')
            ->where('ceo_id', $ceoId)
            ->get();

        return Inertia::render('Organization/Index', [
            'departments'     => $departments,
            'allDepartments'  => $allDepartmentsFlat,
            'unassignedUsers' => $unassignedUsers,
            'allUsers'        => $allUsers,
        ]);
    }

    /**
     * Flatten the department tree into a flat list with level prefix for dropdown display.
     */
    private function flattenDepartments($departments, int $level = 0): array
    {
        $result = [];
        foreach ($departments as $dept) {
            $prefix = str_repeat('— ', $level);
            $result[] = [
                'id'        => $dept->id,
                'name'      => $prefix . $dept->name,
                'level'     => $level,
                'parent_id' => $dept->parent_id,
            ];
            if ($dept->allChildren && $dept->allChildren->count() > 0) {
                $result = array_merge($result, $this->flattenDepartments($dept->allChildren, $level + 1));
            }
        }
        return $result;
    }

    public function storeDepartment(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:departments,id',
        ]);

        Department::create([
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'ceo_id' => auth()->user()->ceo_id,
        ]);

        return back();
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $department->update($request->only('name'));

        return back();
    }

    public function updateUserHierarchy(Request $request, User $user)
    {
        $request->validate([
            'department_id' => 'nullable|exists:departments,id',
            'superior_id'   => 'nullable|exists:users,id',
        ]);

        $user->update($request->only('department_id', 'superior_id'));

        return back();
    }

    public function destroyDepartment(Department $department)
    {
        $department->delete();
        return back();
    }
}
