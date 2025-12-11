@php
    $statusObj = $project->statuses->firstWhere('key', $task->status);
@endphp

<div
    class="card mb-2 shadow-sm border kanban-item position-relative"
    data-id="{{ $task->id }}"
    data-title="{{ $task->title }}"
    data-description="{{ $task->description }}"
    data-notes="{{ $task->notes }}"
    data-priority="{{ $task->priority }}"
    data-date_start="{{ $task->date_start }}"
    data-date_end="{{ $task->date_end }}"
    data-duration="{{ $task->duration }}"
    data-status="{{ $task->status }}"
    style="
        background: {{ $statusObj->color_bg ?? '#ffffff' }};
        border-color: {{ $statusObj->color_border ?? '#cccccc' }};
    "
>

    <div class="p-2 pb-0">

        {{-- STATUS BADGE --}}
        <div class="position-absolute top-0 end-0 m-1">
            @if($statusObj)
                <span class="badge"
                    style="background: {{ $statusObj->color_bg }};
                           border: 1px solid {{ $statusObj->color_border }};
                           color:#000;">
                    {{ $statusObj->label }}
                </span>
            @endif
        </div>

        {{-- CONTENT --}}
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
                    {{ Str::limit($task->notes, 80) }}
                </div>
                @endif
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="d-flex gap-1">
                <button type="button"
                    class="btn btn-outline-primary btn-sm edit-btn"
                    style="width:32px;height:32px;"
                    data-task-id="{{ $task->id }}">
                    <i class="ti ti-edit"></i>
                </button>

                <button type="button"
                    class="btn btn-outline-danger btn-sm delete-task-btn"
                    style="width:32px;height:32px;"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteKanbanModal-{{ $task->id }}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>
        </div>

        {{-- FILE LIST --}}
        @include('project-mgt.kanban.partials.task-files', ['task' => $task])
    </div>

    {{-- SUBTASK LIST --}}
    @include('project-mgt.kanban.partials.subtasks-list', ['task' => $task])

</div>
