<?php

namespace App\Http\Controllers;

use App\Models\ActionPlan;
use App\Models\CompanyGoal;
use App\Models\KeyResult;
use App\Models\Objective;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class OkrController extends Controller
{
    public function index()
    {
        $this->authorizeView();
        
        $ceoId = Auth::user()->ceo_id;

        $goals = CompanyGoal::where('ceo_id', $ceoId)
            ->with(['objectives.keyResults.actionPlans'])
            ->get();

        return Inertia::render('Okr/Index', [
            'goals' => $goals,
            'isCeo' => Auth::user()->role === 'ceo'
        ]);
    }

    // COMPANY GOAL
    public function storeGoal(Request $request)
    {
        $this->authorizeCeo();

        $request->validate([
            'goal' => 'required|string',
            'year' => 'required|string',
        ]);

        CompanyGoal::create([
            'ceo_id' => Auth::user()->ceo_id,
            'goal' => $request->goal,
            'year' => $request->year,
        ]);

        return back();
    }

    public function updateGoal(Request $request, CompanyGoal $goal)
    {
        $this->authorizeCeo($goal->ceo_id);
        $goal->update($request->only('goal', 'year'));
        return back();
    }

    public function destroyGoal(CompanyGoal $goal)
    {
        $this->authorizeCeo($goal->ceo_id);
        $goal->delete();
        return back();
    }

    // OBJECTIVE (DIVISI)
    public function storeObjective(Request $request)
    {
        $this->authorizeEdit();

        $request->validate([
            'company_goal_id' => 'required|exists:company_goals,id',
            'division' => 'required|string',
            'objective' => 'required|string',
        ]);

        Objective::create([
            'ceo_id' => Auth::user()->ceo_id,
            'company_goal_id' => $request->company_goal_id,
            'division' => $request->division,
            'objective' => $request->objective,
        ]);

        return back();
    }

    public function updateObjective(Request $request, Objective $objective) 
    {
        $this->authorizeEdit($objective->ceo_id);
        $objective->update($request->only('division', 'objective'));
        return back();
    }

    public function destroyObjective(Objective $objective)
    {
        $this->authorizeEdit($objective->ceo_id);
        $objective->delete();
        return back();
    }

    // KEY RESULT
    public function storeKeyResult(Request $request)
    {
        $this->authorizeEdit();

        $request->validate([
            'objective_id' => 'required|exists:objectives,id',
            'key_result' => 'required|string',
            'target' => 'required|integer',
            'weight' => 'required|integer',
            'unit' => 'nullable|string'
        ]);

        KeyResult::create($request->all());

        return back();
    }

    public function updateKeyResult(Request $request, KeyResult $keyResult)
    {
        // Check authorization (logic simplified for now, ideally check objective ownership)
        $this->authorizeEdit();

        $request->validate([
            'key_result' => 'required|string',
            'target' => 'required|integer',
            'actual' => 'required|integer',
            'weight' => 'required|integer',
        ]);

        $keyResult->update($request->all());

        return back();
    }

    public function destroyKeyResult(KeyResult $keyResult)
    {
        $this->authorizeEdit();
        $keyResult->delete();
        return back();
    }

    // ACTION PLAN
    public function storeActionPlan(Request $request)
    {
        $this->authorizeEdit();

        $request->validate([
            'key_result_id' => 'required|exists:key_results,id',
            'activity' => 'required|string',
        ]);

        ActionPlan::create($request->all());

        return back();
    }

    public function updateActionPlan(Request $request, ActionPlan $actionPlan)
    {
        $this->authorizeEdit();
        $actionPlan->update($request->only('activity', 'is_completed'));
        return back();
    }

    public function destroyActionPlan(ActionPlan $actionPlan)
    {
        $this->authorizeEdit();
        $actionPlan->delete();
        return back();
    }


    private function authorizeView()
    {
        $user = Auth::user();
        if (!in_array($user->role, ['ceo', 'director'])) {
            abort(403, 'Akses ditolak.');
        }
    }

    private function authorizeCeo($ownerId = null)
    {
        if (Auth::user()->role !== 'ceo') {
            abort(403, 'Hanya CEO yang bisa mengubah data ini.');
        }
        if ($ownerId && Auth::user()->ceo_id !== $ownerId) {
            abort(403, 'Akses ditolak.');   
        }
    }

    private function authorizeEdit($ownerId = null)
    {
        $user = Auth::user();
        if (!in_array($user->role, ['ceo', 'director'])) {
            abort(403, 'Hanya CEO dan Director yang bisa mengubah data.');
        }
        if ($ownerId && $user->ceo_id !== $ownerId) {
            abort(403, 'Akses ditolak.');
        }
    }
}
