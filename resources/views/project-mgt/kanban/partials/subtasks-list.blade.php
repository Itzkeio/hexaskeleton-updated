@if($task->subtasks && $task->subtasks->count() > 0)
    <div class="border-top mt-2">
        <button
            class="btn btn-link btn-sm w-100 text-start text-decoration-none py-1 px-2 d-flex justify-content-between align-items-center collapsed"
            type="button"
            data-bs-toggle="collapse"
            data-bs-target="#subtasks-{{ $task->id }}"
            aria-expanded="false">
            <span class="small">
                <i class="ti ti-checklist"></i>
                {{ $task->subtasks->where('status', 'finished')->count() }}/{{ $task->subtasks->count() }} subtasks
            </span>
            <i class="ti ti-chevron-down"></i>
        </button>

        <div class="collapse" id="subtasks-{{ $task->id }}">
            <div class="px-2 pb-2">
                @foreach($task->subtasks as $subtask)
                    <div class="d-flex align-items-start gap-2 mb-2 p-2 bg-light rounded small">
                        {{-- CHECKBOX STATUS --}}
                        <div class="form-check" style="min-width: 20px;">
                            <input
                                class="form-check-input subtask-checkbox"
                                type="checkbox"
                                {{ $subtask->status == 'finished' ? 'checked' : '' }}
                                data-subtask-id="{{ $subtask->id }}"
                                data-kanban-id="{{ $task->id }}"
                                onchange="toggleSubtaskStatus(this)">
                        </div>

                        {{-- SUBTASK INFO --}}
                        <div class="flex-grow-1">
                            <div class="fw-semibold {{ $subtask->status == 'finished' ? 'text-decoration-line-through text-muted' : '' }}">
                                {{ $subtask->title }}
                            </div>

                            @if($subtask->description)
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ $subtask->description }}
                                </div>
                            @endif

                            <div class="mt-1">
                                    @php
                                        $subtaskStatus = $project->statuses->firstWhere('key', $subtask->status);
                                    @endphp

                                    @if($subtaskStatus)
                                        <span class="badge"
                                            style="
                                                background: {{ $subtaskStatus->color_bg }};
                                                border: 1px solid {{ $subtaskStatus->color_border }};
                                                color:#000;
                                                font-size: 0.65rem;
                                            ">
                                            {{ $subtaskStatus->label }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary" style="font-size:0.65rem;">
                                            {{ ucfirst($subtask->status) }}
                                        </span>
                                    @endif
                                @if($subtask->priority == 'urgent')
                                    <span class="badge bg-danger" style="font-size: 0.65rem;">Urgent</span>
                                @elseif($subtask->priority == 'high')
                                    <span class="badge bg-warning" style="font-size: 0.65rem;">High</span>
                                @elseif($subtask->priority == 'normal')
                                    <span class="badge bg-primary" style="font-size: 0.65rem;">Normal</span>
                                @else
                                    <span class="badge bg-secondary" style="font-size: 0.65rem;">Low</span>
                                @endif

                                @if($subtask->duration)
                                    <span class="badge bg-info" style="font-size: 0.65rem;">
                                        <i class="ti ti-clock"></i> {{ $subtask->duration }} hari
                                    </span>
                                @endif
                            </div>

                            {{-- SUBTASK FILES --}}
                            @include('project-mgt.kanban.partials.subtask-files', [
                                'subtask' => $subtask,
                            ])
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif
