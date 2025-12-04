<div
    class="card mb-2 shadow-sm border kanban-item position-relative {{ $priorityClass[$task->priority] ?? '' }}"
    data-id="{{ $task->id }}"
    data-title="{{ $task->title }}"
    data-description="{{ $task->description }}"
    data-notes="{{ $task->notes }}"
    data-priority="{{ $task->priority }}"
    data-date_start="{{ $task->date_start }}"
    data-date_end="{{ $task->date_end }}"
    data-duration="{{ $task->duration }}"
    data-status="{{ $task->status }}">

    <div class="p-2 pb-0">
        {{-- BADGE STATUS --}}
        <div class="position-absolute top-0 end-0 m-1">
            @if($task->status == 'todo')
                <span class="badge bg-secondary">To Do</span>
            @elseif($task->status == 'inprogress')
                <span class="badge bg-primary">In Progress</span>
            @else
                <span class="badge bg-success">Finished</span>
            @endif
        </div>

        {{-- CARD CONTENT --}}
        <div class="d-flex justify-content-between">
            <div class="flex-grow-1">
                <span class="fw-semibold">{{ $task->title }}</span>
                <div class="small text-muted">{{ ucfirst($task->priority) }}</div>

                @if($task->date_start && $task->date_end)
                    <div class="small text-muted mt-1">
                        <i class="ti ti-calendar-event"></i>
                        {{ \Carbon\Carbon::parse($task->date_start)->format('d M') }} -
                        {{ \Carbon\Carbon::parse($task->date_end)->format('d M Y') }}
                    </div>
                @endif

                @if($task->duration)
                    <div class="small mt-1">
                        <span class="badge bg-info-subtle text-info border border-info">
                            <i class="ti ti-clock"></i> {{ $task->duration }} hari
                        </span>
                    </div>
                @endif

                @if($task->notes)
                    <div class="small text-muted mt-1">
                        <i class="ti ti-note"></i>
                        {{ \Illuminate\Support\Str::limit($task->notes, 80) }}
                    </div>
                @endif
            </div>

            {{-- BUTTONS --}}
            <div class="d-flex gap-1">
                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary edit-btn"
                    data-task-id="{{ $task->id }}">
                    <i class="ti ti-edit" style="pointer-events:none;"></i>
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-danger delete-task-btn"
                    data-task-id="{{ $task->id }}"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteKanbanModal-{{ $task->id }}">
                    <i class="ti ti-trash" style="pointer-events:none;"></i>
                </button>
            </div>
        </div>

        {{-- TASK FILES (di card) --}}
        @include('project-mgt.kanban.partials.task-files', ['task' => $task])
    </div>

    {{-- SUBTASKS DROPDOWN + FILES --}}
    @include('project-mgt.kanban.partials.subtasks-list', ['task' => $task])

    {{-- MODALS UNTUK TASK INI --}}
    @include('project-mgt.kanban.modals.delete-task-modal', ['task' => $task])
    @include('project-mgt.kanban.modals.edit-task-modal', ['task' => $task, 'project' => $project])
    @include('project-mgt.kanban.modals.add-subtask-modal', ['task' => $task])
    @include('project-mgt.kanban.modals.edit-subtask-modal', ['task' => $task])
</div>
