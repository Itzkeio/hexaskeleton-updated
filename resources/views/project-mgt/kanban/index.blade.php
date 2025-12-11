@php
$priorityClass = [
    'urgent' => 'bg-danger-subtle border-danger',
    'high'   => 'bg-warning-subtle border-warning',
    'normal' => 'bg-primary-subtle border-primary',
    'low'    => 'bg-secondary-subtle border-secondary',
];
@endphp

<div class="kanban-wrapper px-2 py-3">

    {{-- ROOT untuk JS --}}
    <div id="kanban-root"
         data-project-id="{{ $project->id }}"
         style="display:none;">
    </div>

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0">Kanban — {{ $project->name }}</h5>

        <div class="d-flex gap-2">
            <a href="{{ route('kanban.status.index', $project->id) }}"
               class="btn btn-secondary btn-sm border">
                <i class="ti ti-settings me-1"></i> Status
            </a>

            <a href="{{ route('kanban.logs.index', $project->id) }}"
               class="btn btn-light btn-sm border">
                <i class="ti ti-history me-1"></i> Logs
            </a>

            <button class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#createKanbanModal-{{ $project->id }}">
                <i class="ti ti-plus me-1"></i> Progress
            </button>
        </div>
    </div>

    {{-- SUCCESS / ERROR --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- BOARD --}}
    <div class="row g-3 kanban-board" id="kanban-board-{{ $project->id }}">

        @if (($project->statuses ?? collect())->count() === 0)
            <div class="col-12">
                <div class="alert alert-info border">
                    Belum ada status. Tambahkan status di menu <b>Status</b>.
                </div>
            </div>
        @endif

        {{-- STATUS COLUMNS --}}
        @foreach ($project->statuses as $status)
            <div class="col-md-4">
                <div class="card shadow-sm h-100">

                    <div class="card-header fw-bold"
                         style="background: {{ $status->color_bg }};
                                border-bottom: 2px solid {{ $status->color_border }}">
                        {{ $status->label }}
                    </div>

                    <div class="card-body kanban-column"
                         id="{{ $status->key }}-{{ $project->id }}"
                         data-status="{{ $status->key }}"
                         data-project-id="{{ $project->id }}">

                        @forelse ($project->kanban->where('status', $status->key) as $task)
                            @include('project-mgt.kanban.partials.task-card', [
                                'task' => $task,
                                'project' => $project,
                                'priorityClass' => $priorityClass
                            ])
                        @empty
                            <div class="text-muted small"></div>
                        @endforelse
                    </div>

                </div>
            </div>
        @endforeach

    </div>{{-- /kanban-board --}}
</div>{{-- /kanban-wrapper --}}


{{-- ========================================================== --}}
{{--   FIX UTAMA: SEMUA MODAL DIPINDAH KE BAWAH SEPERTI INI     --}}
{{-- ========================================================== --}}

{{-- CREATE TASK --}}
@include('project-mgt.kanban.modals.create-task-modal', ['project' => $project])

{{-- ALL TASK MODALS --}}
@foreach ($project->kanban as $task)

    {{-- DELETE TASK --}}
    @include('project-mgt.kanban.modals.delete-task-modal', ['task' => $task])

    {{-- EDIT TASK --}}
    @include('project-mgt.kanban.modals.edit-task-modal', [
        'task' => $task,
        'project' => $project
    ])

    {{-- ADD SUBTASK --}}
    @include('project-mgt.kanban.modals.add-subtask-modal', ['task' => $task])

    {{-- EDIT SUBTASK --}}
    @include('project-mgt.kanban.modals.edit-subtask-modal', ['task' => $task])

@endforeach


{{-- JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script src="{{ asset('js/kanban.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/kanban.css') }}">
