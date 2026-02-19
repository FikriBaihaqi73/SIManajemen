<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $marketing = Department::create(['name' => 'Marketing']);
        $it = Department::create(['name' => 'IT & Engineering']);
        $finance = Department::create(['name' => 'Finance & HR']);

        Department::create(['name' => 'Software Dev', 'parent_id' => $it->id]);
        Department::create(['name' => 'Social Media', 'parent_id' => $marketing->id]);

        $ceo = User::where('role', 'ceo')->first();
        $director = User::where('role', 'director')->first();
        $manajer = User::where('role', 'manajer')->first();
        $supervisor = User::where('role', 'supervisor')->first();

        if ($ceo) {
            $ceo->update(['department_id' => null, 'superior_id' => null]);
        }

        if ($director && $ceo) {
            $director->update(['department_id' => $it->id, 'superior_id' => $ceo->id]);
        }

        if ($manajer && $director) {
            $manajer->update(['department_id' => $it->id, 'superior_id' => $director->id]);
        }

        if ($supervisor && $manajer) {
            $supervisor->update(['department_id' => $it->id, 'superior_id' => $manajer->id]);
        }
    }
}
