<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\KanbanController;
use App\Http\Controllers\SubtaskController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/masterdata/employee', [EmployeeController::class, 'index']);
    Route::get('/masterdata/employee-data', [EmployeeController::class, 'datatable'])->name('employee.datatable');
    Route::get('/masterdata/employee/{id}', [EmployeeController::class, 'show'])->name('employee.show');
    Route::get('/masterdata/employee/{id}/edit', [EmployeeController::class, 'edit'])->name('employee.edit');
    Route::put('/masterdata/employee/{id}', [EmployeeController::class, 'update'])->name('employee.update');

    Route::get('/masterdata/role', [RoleController::class, 'index']);
    Route::get('/masterdata/role-data', [RoleController::class, 'datatable'])->name('role.datatable');
    Route::get('/masterdata/role/create', [RoleController::class, 'create'])->name('role.create');
    Route::post('/masterdata/role/store', [RoleController::class, 'store'])->name('role.store');
    Route::get('/masterdata/role/{id}', [RoleController::class, 'show'])->name('role.show');
    Route::get('/masterdata/role/{id}/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::put('/masterdata/role/{id}', [RoleController::class, 'update'])->name('role.update');
    Route::delete('/masterdata/role/{id}/delete', [RoleController::class, 'destroy'])->name('role.destroy');

    Route::get('/log/audittrail', [LogController::class, 'index']);
    Route::get('/logs/audit-trail-data', [LogController::class, 'datatable'])->name('logs.datatable');

    Route::get('/menu/structure', [MenuController::class, 'getMenuStructure'])->name('menu.structure');
    Route::get('/project-mgt/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/project-mgt/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('project-icon/{filename}', [ProjectController::class, 'displayIcon'])->name('project.icon');
    Route::get('/projects/{id}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    Route::get('/projects/search', [ProjectController::class, 'search'])->name('projects.search'); // Route baru untuk live search
    Route::post('/projects/{id}/versions', [ProjectController::class, 'addVersion'])->name('projects.addVersion');
    Route::put('/projects/{projectId}/versions/{versionId}', [ProjectController::class, 'editVersion'])->name('projects.editVersion');
    Route::delete('/projects/{projectId}/versions/{versionId}', [ProjectController::class, 'deleteVersion'])->name('projects.deleteVersion');
    Route::post('/projects/{projectId}/versions/{versionId}/activate', [ProjectController::class, 'setActiveVersion'])->name('projects.setActiveVersion');
    Route::get('/projects/search', [ProjectController::class, 'search'])->name('projects.search');
    Route::resource('projects', ProjectController::class);
    Route::post('/projects/{project}/timeline', [TimelineController::class, 'store'])->name('timeline.store');
    Route::put('/projects/{project}/timeline/{timeline}', [TimelineController::class, 'update'])->name('timeline.update');
    Route::delete('/projects/{project}/timeline/{timeline}', [TimelineController::class, 'destroy'])->name('timeline.destroy');
    Route::get('/projects/{project}/timeline/gantt-data', [TimelineController::class, 'getGanttData'])->name('timeline.gantt-data');
});
// Route Kanban - PERBAIKI INI
// ROUTE KANBAN
Route::prefix('projects/{project}/kanban')->group(function () {

    Route::get('/', [KanbanController::class, 'index'])->name('kanban.index');
    Route::post('/', [KanbanController::class, 'store'])->name('kanban.store');
    Route::put('/{kanban}', [KanbanController::class, 'update'])->name('kanban.update');
    Route::post('/status', [KanbanController::class, 'updateStatus'])->name('kanban.updateStatus');
    Route::delete('/{kanban}', [KanbanController::class, 'delete'])->name('kanban.delete');

    // SUBTASK ROUTES — FIXED!!!
    Route::get('/{kanban}/subtasks', [SubtaskController::class, 'index'])->name('subtask.list');

    Route::post('/{kanban}/subtasks', [SubtaskController::class, 'store'])->name('subtask.store');

    Route::put('/{kanban}/subtasks/{subtask}', [SubtaskController::class, 'update'])->name('subtask.update');

    Route::delete('/{kanban}/subtasks/{subtask}', [SubtaskController::class, 'delete'])->name('subtask.delete');

    Route::post('/{kanban}/subtasks/{subtask}/toggle-status', [SubtaskController::class, 'toggleStatus'])
        ->name('subtask.toggleStatus');
});



Route::get('/auth/login', [AuthController::class, 'index'])->name('login');
Route::post('/auth/login', [AuthController::class, 'doLogin'])->name('doLogin');
Route::get('/auth/logout', [AuthController::class, 'logout'])->name('doLogout');
