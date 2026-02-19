<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrgStructureController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // User Management (CEO Only)
    Route::middleware('can:ceo-access')->group(function () {
        Route::resource('users', UserController::class);
    });

    // Organization Structure
    Route::middleware('can:view-org')->group(function () {
        Route::get('/organization', [OrgStructureController::class, 'index'])->name('organization.index');
    });

    Route::middleware('can:manage-hierarchy')->group(function () {
        Route::put('/organization/users/{user}/hierarchy', [OrgStructureController::class, 'updateUserHierarchy'])->name('organization.users.hierarchy');
    });

    Route::middleware('can:manage-departments')->group(function () {
        Route::post('/organization/departments', [OrgStructureController::class, 'storeDepartment'])->name('organization.departments.store');
        Route::put('/organization/departments/{department}', [OrgStructureController::class, 'updateDepartment'])->name('organization.departments.update');
        Route::delete('/organization/departments/{department}', [OrgStructureController::class, 'destroyDepartment'])->name('organization.departments.destroy');
    });

    // Our Company Section
    Route::get('/company', [\App\Http\Controllers\CompanyProfileController::class, 'index'])->name('company.index');
    
    Route::middleware('can:ceo-access')->group(function () {
        Route::post('/company/vision', [\App\Http\Controllers\CompanyProfileController::class, 'updateVision'])->name('company.vision.update');
        
        Route::post('/company/missions', [\App\Http\Controllers\CompanyProfileController::class, 'storeMission'])->name('company.missions.store');
        Route::put('/company/missions/{mission}', [\App\Http\Controllers\CompanyProfileController::class, 'updateMission'])->name('company.missions.update');
        Route::delete('/company/missions/{mission}', [\App\Http\Controllers\CompanyProfileController::class, 'destroyMission'])->name('company.missions.destroy');
        
        Route::post('/company/core-values', [\App\Http\Controllers\CompanyProfileController::class, 'storeCoreValue'])->name('company.core-values.store');
        Route::put('/company/core-values/{coreValue}', [\App\Http\Controllers\CompanyProfileController::class, 'updateCoreValue'])->name('company.core-values.update');
        Route::delete('/company/core-values/{coreValue}', [\App\Http\Controllers\CompanyProfileController::class, 'destroyCoreValue'])->name('company.core-values.destroy');
    });

    // Business Model Canvas
    Route::get('/bmc', [\App\Http\Controllers\BmcController::class, 'index'])->name('bmc.index');
    Route::post('/bmc', [\App\Http\Controllers\BmcController::class, 'store'])->name('bmc.store');
    Route::put('/bmc/{bmcItem}', [\App\Http\Controllers\BmcController::class, 'update'])->name('bmc.update');
    Route::delete('/bmc/{bmcItem}', [\App\Http\Controllers\BmcController::class, 'destroy'])->name('bmc.destroy');

    // Goals & OKR
    Route::get('/okr', [\App\Http\Controllers\OkrController::class, 'index'])->name('okr.index');
    
    // CEO Only
    Route::post('/okr/goals', [\App\Http\Controllers\OkrController::class, 'storeGoal'])->name('okr.goals.store');
    Route::put('/okr/goals/{goal}', [\App\Http\Controllers\OkrController::class, 'updateGoal'])->name('okr.goals.update');
    Route::delete('/okr/goals/{goal}', [\App\Http\Controllers\OkrController::class, 'destroyGoal'])->name('okr.goals.destroy');
    
    // Director/CEO
    Route::post('/okr/objectives', [\App\Http\Controllers\OkrController::class, 'storeObjective'])->name('okr.objectives.store');
    Route::put('/okr/objectives/{objective}', [\App\Http\Controllers\OkrController::class, 'updateObjective'])->name('okr.objectives.update');
    Route::delete('/okr/objectives/{objective}', [\App\Http\Controllers\OkrController::class, 'destroyObjective'])->name('okr.objectives.destroy');
    
    Route::post('/okr/key-results', [\App\Http\Controllers\OkrController::class, 'storeKeyResult'])->name('okr.key-results.store');
    Route::put('/okr/key-results/{keyResult}', [\App\Http\Controllers\OkrController::class, 'updateKeyResult'])->name('okr.key-results.update');
    Route::delete('/okr/key-results/{keyResult}', [\App\Http\Controllers\OkrController::class, 'destroyKeyResult'])->name('okr.key-results.destroy');
    
    Route::post('/okr/action-plans', [\App\Http\Controllers\OkrController::class, 'storeActionPlan'])->name('okr.action-plans.store');
    Route::put('/okr/action-plans/{actionPlan}', [\App\Http\Controllers\OkrController::class, 'updateActionPlan'])->name('okr.action-plans.update');
    Route::delete('/okr/action-plans/{actionPlan}', [\App\Http\Controllers\OkrController::class, 'destroyActionPlan'])->name('okr.action-plans.destroy');

    // Task Management
    Route::prefix('task-management')->name('tasks.')->group(function () {
        // Overview
        Route::get('/overview', [\App\Http\Controllers\TaskManagementController::class, 'index'])->name('overview');
        
        // Projects
        Route::get('/projects', [\App\Http\Controllers\TaskManagementController::class, 'projects'])->name('projects.index');
        Route::post('/projects', [\App\Http\Controllers\TaskManagementController::class, 'storeProject'])->name('projects.store');
        Route::get('/projects/{project}', [\App\Http\Controllers\TaskManagementController::class, 'showProject'])->name('projects.show');
        Route::put('/projects/{project}', [\App\Http\Controllers\TaskManagementController::class, 'updateProject'])->name('projects.update');
        Route::delete('/projects/{project}', [\App\Http\Controllers\TaskManagementController::class, 'destroyProject'])->name('projects.destroy');

        // Project Labels
        Route::post('/projects/{project}/labels', [\App\Http\Controllers\TaskManagementController::class, 'storeLabel'])->name('projects.labels.store');
        Route::delete('/projects/{project}/labels', [\App\Http\Controllers\TaskManagementController::class, 'destroyLabel'])->name('projects.labels.destroy');

        // Tasks (Nested in Projects theoretically, but keeping flat for simplicity where possible, or passed via ID)
        Route::post('/projects/{project}/tasks', [\App\Http\Controllers\TaskManagementController::class, 'storeTask'])->name('tasks.store');
        Route::put('/tasks/{task}', [\App\Http\Controllers\TaskManagementController::class, 'updateTask'])->name('tasks.update');
        Route::delete('/tasks/{task}', [\App\Http\Controllers\TaskManagementController::class, 'destroyTask'])->name('tasks.destroy');
    });
});

require __DIR__.'/auth.php';
