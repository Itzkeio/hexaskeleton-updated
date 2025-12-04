@php
$priorityClass = [
    'urgent' => 'bg-danger-subtle border-danger',
    'high'   => 'bg-warning-subtle border-warning',
    'normal' => 'bg-primary-subtle border-primary',
    'low'    => 'bg-secondary-subtle border-secondary',
];
@endphp

<div class="container-fluid px-2 py-3">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold">Kanban — {{ $project->name }}</h5>

        <div class="d-flex gap-2">
            <a href="{{ route('kanban.logs.index', ['project' => $project->id]) }}"
               class="btn btn-light btn-sm border">
                <i class="ti ti-history"></i> Logs
            </a>

            <button
                class="btn btn-primary btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#createKanbanModal-{{ $project->id }}">
                <i class="ti ti-plus me-1"></i>Tambah Progress
            </button>
        </div>
    </div>

    {{-- SUCCESS/ERROR MESSAGE --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- KANBAN BOARD --}}
    <div class="row g-3" id="kanban-board">
        {{-- ROOT UNTUK JS --}}
        <div id="kanban-root" data-project-id="{{ $project->id }}" style="display:none;"></div>

        {{-- LOOP COLUMNS --}}
        @foreach (['todo' => 'To Do', 'inprogress' => 'In Progress', 'finished' => 'Finished'] as $statusKey => $label)
            <div class="col-md-4">
                <div class="card shadow-sm">
                    {{-- HEADER --}}
                    <div class="card-header fw-bold
                        @if($statusKey === 'todo') bg-secondary-subtle
                        @elseif($statusKey === 'inprogress') bg-primary-subtle
                        @else bg-success-subtle @endif">
                        {{ $label }}
                    </div>

                    {{-- BODY --}}
                    <div class="card-body" id="{{ $statusKey }}-{{ $project->id }}">
                        @foreach($project->kanban->where('status', $statusKey) as $task)
                            @include('project-mgt.kanban.partials.task-card', [
                                'task'          => $task,
                                'project'       => $project,
                                'priorityClass' => $priorityClass,
                            ])
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div> {{-- /#kanban-board --}}
</div> {{-- /.container-fluid --}}

{{-- CREATE KANBAN (project-level) --}}
@include('project-mgt.kanban.modals.create-task-modal', ['project' => $project])

<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script src="{{ asset('js/kanban.js') }}"></script>
<link rel="stylesheet" href="{{ asset('css/kanban.css') }}">
