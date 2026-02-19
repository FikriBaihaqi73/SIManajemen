<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskManagementController extends Controller
{
    /**
     * Overview Dashboard: Chart of User Performance by Story Points
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $ceoId = $user->ceo_id ?? $user->id;

        // Filters
        $month = $request->input('month', date('m'));
        $year = $request->input('year', date('Y'));

        // Calculate Total Story Points per User for the selected period
        // Tasks must be 'done' to count towards performance
        $performances = Task::whereHas('project', function($q) use ($ceoId) {
                $q->where('ceo_id', $ceoId);
            })
            ->where('status', 'done')
            ->whereYear('updated_at', $year)
            ->whereMonth('updated_at', $month)
            ->select('assigned_to', DB::raw('SUM(story_point) as total_points'))
            ->groupBy('assigned_to')
            ->with('assignee:id,name')
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->assignee ? $item->assignee->name : 'Unassigned',
                    'points' => (int) $item->total_points
                ];
            });

        return Inertia::render('TaskManagement/Overview', [
            'performances' => $performances,
            'filters' => ['month' => $month, 'year' => $year]
        ]);
    }

    /**
     * List Projects
     */
    public function projects()
    {
        $user = Auth::user();
        
        // If CEO/Director/Manager => See All Projects in Organization
        // If Staff => See Only Projects they are a member of
        $projectsQuery = Project::query();

        if (in_array($user->role, ['ceo', 'director', 'manajer'])) {
            $projectsQuery->where('ceo_id', $user->ceo_id);
        } else {
            $projectsQuery->whereHas('members', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        return Inertia::render('TaskManagement/Projects/Index', [
            'projects' => $projectsQuery->withCount('tasks')->get(),
            'canCreate' => in_array($user->role, ['ceo', 'director', 'manajer']),
            'users' => User::where('ceo_id', $user->ceo_id)->get(['id', 'name', 'role']) // For creating new project member selection
        ]);
    }

    /**
     * Store New Project
     */
    public function storeProject(Request $request) 
    {
        $this->authorizeCreateProject();

        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'members' => 'required|array|min:1', // List of user IDs
            'members.*' => 'exists:users,id'
        ]);

        $projectId = null;

        DB::transaction(function() use ($request, &$projectId) {
            $project = Project::create([
                'ceo_id' => Auth::user()->ceo_id,
                'name' => $request->name,
                'description' => $request->description,
                'status' => 'active',
                'labels' => ['Backend', 'Frontend', 'Design', 'Mobile', 'Documentation']
            ]);

            $projectId = $project->id;

            // Add selected members
            foreach ($request->members as $userId) {
                ProjectMember::create([
                    'project_id' => $project->id,
                    'user_id' => $userId,
                    'role' => 'member'
                ]);
            }
        });

        // Redirect to the new project dashboard
        return to_route('tasks.projects.show', $projectId)->with('success', 'Project created successfully');
    }

    /**
     * Update Project
     */
    public function updateProject(Request $request, Project $project)
    {
        $this->authorizeCreateProject();

        $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'status' => 'required|in:active,completed,archived',
        ]);

        $project->update($request->only('name', 'description', 'status'));

        return back();
    }

    /**
     * Delete Project
     */
    public function destroyProject(Project $project)
    {
        $this->authorizeCreateProject();

        $project->delete();

        return back(); // Or to index if on show page, but deletion usually from index
    }

    /**
     * Show Single Project (The Spreadsheet View)
     */
    public function showProject(Project $project)
    {
        // Check Access
        $user = Auth::user();
        $isMember = $project->members()->where('users.id', $user->id)->exists();
        $isAdmin = in_array($user->role, ['ceo', 'director', 'manajer']) && $project->ceo_id === $user->ceo_id;

        if (!$isMember && !$isAdmin) {
            abort(403, 'You are not a member of this project.');
        }

        $project->load(['members', 'tasks.assignee']);

        // Group tasks by Month -> Sprint
        // Logic: specific grouping logic can be done in Frontend or Backend.
        // Let's pass raw tasks and let Frontend group them for flexible UI
        
        return Inertia::render('TaskManagement/Projects/Show', [
            'project' => $project,
            'members' => $project->members,
            'tasks' => $project->tasks
        ]);
    }

    /**
     * Store Task
     */
    public function storeTask(Request $request, Project $project)
    {
        // Permission check skipped for brevity, assume project members can add tasks
        
        $request->validate([
            'description' => 'required|string',
            'category' => 'required|string',
            'story_point' => 'required|integer|min:0',
            'sprint_group' => 'nullable|string',
            'status' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date'
        ]);

        $project->tasks()->create($request->all());

        return back();
    }

    /**
     * Update Task
     */
    public function updateTask(Request $request, Task $task)
    {
        $task->update($request->all());
        return back();

        // Note: For a true "spreadsheet" feel, we might want bulk updates or specific field updates.
        // For now, standard update is fine.
    }

     /**
     * Delete Task
     */
    public function destroyTask(Task $task)
    {
        $task->delete();
        return back();
    }

    private function authorizeCreateProject()
    {
        if (!in_array(Auth::user()->role, ['ceo', 'director', 'manajer'])) {
            abort(403, 'Unauthorized');
        }
    }

    public function storeLabel(Request $request, Project $project)
    {
        // Simple authorization check (adjust as needed)
        // Only allow members to add labels? Or just admins? Let's say members.
        if (!$project->members()->where('users.id', Auth::id())->exists() && $project->ceo_id !== Auth::user()->ceo_id) {
             abort(403);
        }

        $request->validate(['label' => 'required|string|max:50']);
        
        $labels = $project->labels ?? [];
        if (!in_array($request->label, $labels)) {
            $labels[] = $request->label;
            $project->update(['labels' => $labels]);
        }
        
        return back();
    }

    public function destroyLabel(Request $request, Project $project)
    {
         if (!$project->members()->where('users.id', Auth::id())->exists() && $project->ceo_id !== Auth::user()->ceo_id) {
             abort(403);
        }
        
        $request->validate(['label' => 'required|string']);

        $labels = $project->labels ?? [];
        $labels = array_values(array_filter($labels, fn($l) => $l !== $request->label));
        $project->update(['labels' => $labels]);

        return back();
    }
}
