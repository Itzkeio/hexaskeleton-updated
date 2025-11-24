@php
$priorityClass = [
    'urgent' => 'bg-danger-subtle border-danger',
    'high' => 'bg-warning-subtle border-warning',
    'normal' => 'bg-primary-subtle border-primary',
    'low' => 'bg-secondary-subtle border-secondary',
];
@endphp

<div class="container-fluid px-2 py-3">
    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold">Kanban — {{ $project->name }}</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createKanbanModal-{{ $project->id }}">
            <i class="ti ti-plus me-1"></i>Tambah Progress
        </button>
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
    <div class="row g-3">
        {{-- LOOP COLUMNS --}}
        @foreach (['todo' => 'To Do', 'inprogress' => 'In Progress', 'finished' => 'Finished'] as $statusKey => $label)
        <div class="col-md-4">
            <div class="card shadow-sm">
                {{-- HEADER --}}
                <div class="card-header fw-bold @if($statusKey === 'todo') bg-secondary-subtle @elseif($statusKey === 'inprogress') bg-primary-subtle @else bg-success-subtle @endif">
                    {{ $label }}
                </div>
                {{-- BODY --}}
                <div class="card-body" id="{{ $statusKey }}-{{ $project->id }}">
                    @foreach($project->kanban->where('status', $statusKey) as $task)
                    <div class="card mb-2 shadow-sm border kanban-item position-relative {{ $priorityClass[$task->priority] }}"
                         data-id="{{ $task->id }}"
                         data-title="{{ $task->title }}"
                         data-description="{{ $task->description }}"
                         data-priority="{{ $task->priority }}"
                         data-date_start="{{ $task->date_start }}"
                         data-date_end="{{ $task->date_end }}"
                         data-duration="{{ $task->duration }}">
                        
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
                                        <div class="small">
                                            <span class="badge bg-info-subtle text-info border border-info">
                                                <i class="ti ti-clock"></i> {{ $task->duration }} hari
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                {{-- BUTTONS --}}
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                            data-task-id="{{ $task->id }}">
                                        <i class="ti ti-edit" style="pointer-events:none;"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-task-btn"
                                            data-task-id="{{ $task->id }}" data-bs-toggle="modal" data-bs-target="#deleteKanbanModal-{{ $task->id }}">
                                        <i class="ti ti-trash" style="pointer-events:none;"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- SUBTASKS DROPDOWN (inline display of first few) --}}
                        @if($task->subtasks && $task->subtasks->count() > 0)
                        <div class="border-top mt-2">
                            <button class="btn btn-link btn-sm w-100 text-start text-decoration-none py-1 px-2 d-flex justify-content-between align-items-center collapsed" 
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
                                            <input class="form-check-input subtask-checkbox" 
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
                                                <div class="text-muted" style="font-size: 0.75rem;">{{ $subtask->description }}</div>
                                            @endif
                                            <div class="mt-1">
                                                @if($subtask->status == 'todo')
                                                    <span class="badge bg-secondary" style="font-size: 0.65rem;">To Do</span>
                                                @elseif($subtask->status == 'inprogress')
                                                    <span class="badge bg-primary" style="font-size: 0.65rem;">In Progress</span>
                                                @else
                                                    <span class="badge bg-success" style="font-size: 0.65rem;">Finished</span>
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
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- DELETE TASK MODAL (per task) --}}
                    <div class="modal fade" id="deleteKanbanModal-{{ $task->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('kanban.delete', [$project->id, $task->id]) }}" method="POST" class="modal-content">
                                @csrf
                                @method('DELETE')
                                <div class="modal-header">
                                    <h5 class="modal-title">Hapus Progress</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin ingin menghapus <b>{{ $task->title }}</b>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- EDIT TASK MODAL (per task) --}}
                    <div class="modal fade" id="editKanbanModal-{{ $task->id }}" tabindex="-1">
                        <div class="modal-dialog modal-lg">
                            <form id="editKanbanForm-{{ $task->id }}" method="POST" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Progress</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="edit_task_id-{{ $task->id }}" name="kanban_id" value="{{ $task->id }}">

                                    {{-- TASK INFO --}}
                                    <h6 class="fw-bold mb-3">Task Information</h6>
                                    <div class="mb-2">
                                        <label class="form-label">Title</label>
                                        <input name="title" id="edit_title-{{ $task->id }}" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Date Start</label>
                                        <input type="date" name="date_start" id="edit_date_start-{{ $task->id }}" class="form-control">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Date End</label>
                                        <input type="date" name="date_end" id="edit_date_end-{{ $task->id }}" class="form-control">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Duration</label>
                                        <input type="text" id="edit_duration-{{ $task->id }}" class="form-control bg-light" disabled>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" id="edit_description-{{ $task->id }}" class="form-control"></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Priority</label>
                                        <select name="priority" id="edit_priority-{{ $task->id }}" class="form-select">
                                            <option value="low">Low</option>
                                            <option value="normal">Normal</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>

                                    <hr class="my-4">

                                    {{-- SUBTASKS SECTION --}}
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="fw-bold mb-0">Subtasks</h6>
                                        <button type="button" class="btn btn-sm btn-success open-add-subtask-btn" data-task-id="{{ $task->id }}">
                                            <i class="ti ti-plus"></i> Add Subtask
                                        </button>
                                    </div>

                                    <div id="subtasks-list-{{ $task->id }}" class="border rounded p-2" style="max-height: 300px; overflow-y: auto;">
                                        <div class="text-center text-muted py-3">
                                            <i class="ti ti-clipboard-list"></i>
                                            <p class="mb-0 small">No subtasks yet</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- ADD SUBTASK MODAL (per task) --}}
                    <div class="modal fade" id="addSubtaskModal-{{ $task->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form id="addSubtaskForm-{{ $task->id }}" method="POST" class="modal-content">
                                @csrf
                                <input type="hidden" name="kanban_id" value="{{ $task->id }}">
                                <div class="modal-header">
                                    <h5 class="modal-title">Add Subtask</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-2">
                                        <label class="form-label">Title</label>
                                        <input name="title" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Date Start</label>
                                        <input type="date" name="date_start" class="form-control">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Date End</label>
                                        <input type="date" name="date_end" class="form-control">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Priority</label>
                                        <select name="priority" class="form-select">
                                            <option value="low">Low</option>
                                            <option value="normal" selected>Normal</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Add Subtask</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- EDIT SUBTASK MODAL (per task) --}}
                    <div class="modal fade" id="editSubtaskModal-{{ $task->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form id="editSubtaskForm-{{ $task->id }}" method="POST" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Subtask</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="subtask_id" id="edit_subtask_id-{{ $task->id }}">
                                    <input type="hidden" name="kanban_id" id="edit_subtask_kanban_id-{{ $task->id }}" value="{{ $task->id }}">

                                    <div class="mb-2">
                                        <label class="form-label">Title</label>
                                        <input name="title" id="edit_subtask_title-{{ $task->id }}" class="form-control" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" id="edit_subtask_description-{{ $task->id }}" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Date Start</label>
                                        <input type="date" name="date_start" id="edit_subtask_date_start-{{ $task->id }}" class="form-control">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Date End</label>
                                        <input type="date" name="date_end" id="edit_subtask_date_end-{{ $task->id }}" class="form-control">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Priority</label>
                                        <select name="priority" id="edit_subtask_priority-{{ $task->id }}" class="form-select">
                                            <option value="low">Low</option>
                                            <option value="normal">Normal</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label">Status</label>
                                        <select name="status" id="edit_subtask_status-{{ $task->id }}" class="form-select">
                                            <option value="todo">To Do</option>
                                            <option value="inprogress">In Progress</option>
                                            <option value="finished">Finished</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update Subtask</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- CREATE KANBAN (project-level) --}}
<div class="modal fade" id="createKanbanModal-{{ $project->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('kanban.store', $project->id) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label">Title</label>
                    <input name="title" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Date Start</label>
                    <input type="date" name="date_start" id="create_date_start" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Date End</label>
                    <input type="date" name="date_end" id="create_date_end" class="form-control">
                </div>
                <div class="mb-2" id="create_duration_display" style="display: none;">
                    <label class="form-label">Duration</label>
                    <input type="text" id="create_duration_value" class="form-control bg-light" disabled>
                </div>
                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="normal" selected>Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- STYLES & SCRIPTS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<style>
    .sortable-ghost { opacity: 0.3; background: #d0d0d0 !important; }
    .sortable-drag { transform: rotate(2deg) scale(1.03); transition: 0.15s ease; }
    .kanban-item { padding-top: 28px !important; }
    .kanban-item [data-bs-toggle="collapse"] { transition: all 0.3s ease; }
    .kanban-item [data-bs-toggle="collapse"]:hover { background-color: rgba(0,0,0,0.02); }
    .kanban-item [data-bs-toggle="collapse"] .ti-chevron-down { transition: transform 0.3s ease; }
    .kanban-item [data-bs-toggle="collapse"]:not(.collapsed) .ti-chevron-down { transform: rotate(180deg); }
    .subtask-checkbox { cursor: pointer; width: 18px; height: 18px; }
    .kanban-item .bg-light:hover { background-color: #e9ecef !important; }
    @keyframes slideIn { from { transform: translateX(100%); opacity:0 } to { transform: translateX(0); opacity:1 } }
    @keyframes slideOut { from { transform: translateX(0); opacity:1 } to { transform: translateX(100%); opacity:0 } }
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const projectId = "{{ $project->id }}";
    const statuses = ["todo","inprogress","finished"];

    function sortColumnByPriority(column) {
        const priorityOrder = { urgent:0, high:1, normal:2, low:3 };
        let cards = Array.from(column.children);
        cards.sort((a,b) => (priorityOrder[a.dataset.priority] ?? 99) - (priorityOrder[b.dataset.priority] ?? 99));
        cards.forEach(c => column.appendChild(c));
    }

    // Init Sortable for each column
    statuses.forEach(status => {
        const col = document.getElementById(`${status}-${projectId}`);
        if (!col) return;
        sortColumnByPriority(col);
        new Sortable(col, {
            group: "kanban-project-" + projectId,
            animation: 200,
            ghostClass: "sortable-ghost",
            dragClass: "sortable-drag",
            onAdd: function(evt) {
                const card = evt.item;
                const id = card.dataset.id;
                const newStatus = status;
                fetch(`/projects/${projectId}/kanban/status`, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({ id, status: newStatus })
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        updateCardBadge(card, newStatus);
                        sortColumnByPriority(col);
                        showToast('Status berhasil diupdate!', 'success');
                    } else showToast('Gagal mengupdate status!', 'error');
                })
                .catch(err => { console.error(err); showToast('Error update status', 'error'); });
            }
        });
    });

    function updateCardBadge(card, status) {
        const badgeContainer = card.querySelector('.position-absolute.top-0.end-0.m-1');
        if (!badgeContainer) return;
        let html = '';
        if (status === 'todo') html = '<span class="badge bg-secondary">To Do</span>';
        else if (status === 'inprogress') html = '<span class="badge bg-primary">In Progress</span>';
        else if (status === 'finished') html = '<span class="badge bg-success">Finished</span>';
        badgeContainer.innerHTML = html;
    }

    function showToast(message, type='success') {
        const old = document.getElementById('kanban-toast');
        if (old) old.remove();
        const t = document.createElement('div');
        t.id = 'kanban-toast';
        t.className = `alert alert-${type==='success'?'success':'danger'} position-fixed top-0 end-0 m-3 shadow-lg`;
        t.style.cssText = 'z-index:9999; min-width:250px; animation: slideIn 0.25s ease-out;';
        t.innerHTML = `<div class="d-flex align-items-center"><i class="ti ti-${type==='success'?'check':'alert-circle'} me-2"></i><span>${message}</span></div>`;
        document.body.appendChild(t);
        setTimeout(()=>{ t.style.animation='slideOut 0.25s ease-out'; setTimeout(()=>t.remove(),250); },3000);
    }

    // ---------- Edit Task button (per task) ----------
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.edit-btn');
        if (!btn) return;
        const taskCard = btn.closest('.kanban-item');
        const taskId = taskCard.dataset.id;
        const modalEl = document.getElementById(`editKanbanModal-${taskId}`);
        if (!modalEl) return console.warn('No edit modal for', taskId);
        document.getElementById(`edit_task_id-${taskId}`).value = taskId;
        document.getElementById(`edit_title-${taskId}`).value = taskCard.dataset.title ?? '';
        const descEl = document.getElementById(`edit_description-${taskId}`);
        if (descEl) descEl.value = taskCard.dataset.description ?? '';
        const pri = document.getElementById(`edit_priority-${taskId}`);
        if (pri) pri.value = taskCard.dataset.priority ?? 'normal';
        const ds = document.getElementById(`edit_date_start-${taskId}`);
        const de = document.getElementById(`edit_date_end-${taskId}`);
        if (ds) ds.value = taskCard.dataset.date_start ?? '';
        if (de) de.value = taskCard.dataset.date_end ?? '';
        const dur = document.getElementById(`edit_duration-${taskId}`);
        if (dur && ds && de && ds.value && de.value) {
            const diff = Math.ceil((new Date(de.value) - new Date(ds.value)) / (1000*60*60*24));
            dur.value = diff + ' hari';
        } else if (dur) dur.value = '-';

        const editForm = document.getElementById(`editKanbanForm-${taskId}`);
        if (editForm) editForm.action = `/projects/${projectId}/kanban/${taskId}`;

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        loadSubtasks(taskId);
    });

    // ---------- Load subtasks (per task) ----------
    function loadSubtasks(kanbanId) {
        const listEl = document.getElementById(`subtasks-list-${kanbanId}`);
        if (!listEl) return console.warn('No subtasks list for', kanbanId);
        listEl.innerHTML = `<div class="text-center py-3"><i class="ti ti-loader ti-spin"></i> Loading...</div>`;
        fetch(`/projects/${projectId}/kanban/${kanbanId}/subtasks`)
            .then(r => {
                if (!r.ok) throw new Error('HTTP '+r.status);
                return r.json();
            })
            .then(data => {
                if (!data.subtasks || !Array.isArray(data.subtasks) || data.subtasks.length === 0) {
                    listEl.innerHTML = `<div class="text-center text-muted py-3"><i class="ti ti-clipboard-list"></i><p class="mb-0 small">No subtasks yet</p></div>`;
                    return;
                }
                const frag = document.createDocumentFragment();
                data.subtasks.forEach(st => {
                    const card = document.createElement('div');
                    card.className = 'card mb-2 p-2 border';
                    const row = document.createElement('div');
                    row.className = 'd-flex justify-content-between align-items-start';
                    const left = document.createElement('div'); left.className='flex-grow-1';
                    const title = document.createElement('div'); title.className='fw-semibold'; title.textContent = st.title || '';
                    left.appendChild(title);
                    if (st.description) {
                        const d = document.createElement('div'); d.className='small text-muted'; d.textContent = st.description; left.appendChild(d);
                    }
                    const meta = document.createElement('div'); meta.className='mt-1';
                    meta.innerHTML = `${getStatusBadge(st.status)} ${getPriorityBadge(st.priority)} ${st.duration ? `<span class="badge bg-info-subtle text-info"><i class="ti ti-clock"></i> ${st.duration} hari</span>` : ''}`;
                    left.appendChild(meta);

                    const right = document.createElement('div'); right.className='d-flex gap-1';
                    const btnEdit = document.createElement('button'); btnEdit.type='button'; btnEdit.className='btn btn-sm btn-outline-primary';
                    btnEdit.innerHTML = '<i class="ti ti-edit"></i>';
                    btnEdit.addEventListener('click', () => openEditSubtaskModal(st.id, kanbanId, st));
                    const btnDel = document.createElement('button'); btnDel.type='button'; btnDel.className='btn btn-sm btn-outline-danger';
                    btnDel.innerHTML = '<i class="ti ti-trash"></i>';
                    btnDel.addEventListener('click', () => openDeleteSubtask(st.id, kanbanId));
                    right.appendChild(btnEdit); right.appendChild(btnDel);

                    row.appendChild(left); row.appendChild(right); card.appendChild(row); frag.appendChild(card);
                });
                listEl.innerHTML = ''; listEl.appendChild(frag);
            })
            .catch(err => {
                console.error('loadSubtasks err', err);
                listEl.innerHTML = `<div class="alert alert-danger">Failed to load subtasks</div>`;
            });
    }

    function getStatusBadge(status) {
        const badges = { 'todo':'<span class="badge bg-secondary">To Do</span>', 'inprogress':'<span class="badge bg-primary">In Progress</span>', 'finished':'<span class="badge bg-success">Finished</span>' };
        return badges[status] || '';
    }
    function getPriorityBadge(priority) {
        const badges = { 'urgent':'<span class="badge bg-danger">Urgent</span>', 'high':'<span class="badge bg-warning">High</span>', 'normal':'<span class="badge bg-primary">Normal</span>', 'low':'<span class="badge bg-secondary">Low</span>' };
        return badges[priority] || '';
    }

    // ---------- Open Add Subtask ----------
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.open-add-subtask-btn');
        if (!btn) return;
        const taskId = btn.dataset.taskId;
        
        const editTaskModalEl = document.getElementById(`editKanbanModal-${taskId}`);
        if (editTaskModalEl) {
            const editTaskModal = bootstrap.Modal.getInstance(editTaskModalEl);
            if (editTaskModal) editTaskModal.hide();
        }
        
        const addModalEl = document.getElementById(`addSubtaskModal-${taskId}`);
        if (!addModalEl) return console.warn('No addSubtaskModal for', taskId);
        
        setTimeout(() => {
            const addModal = bootstrap.Modal.getOrCreateInstance(addModalEl);
            addModal.show();
        }, 150);
        
        addModalEl.addEventListener('hidden.bs.modal', function handler() {
            const editTaskModalEl = document.getElementById(`editKanbanModal-${taskId}`);
            if (editTaskModalEl) {
                const editTaskModal = bootstrap.Modal.getOrCreateInstance(editTaskModalEl);
                editTaskModal.show();
            }
            addModalEl.removeEventListener('hidden.bs.modal', handler);
        });
    });

    // ---------- Open Edit Subtask Modal ----------
    function openEditSubtaskModal(subtaskId, kanbanId, subtask) {
        const modalEl = document.getElementById(`editSubtaskModal-${kanbanId}`);
        if (!modalEl) return console.warn('No per-task edit subtask modal found for kanban', kanbanId);
        
        const editTaskModalEl = document.getElementById(`editKanbanModal-${kanbanId}`);
        if (editTaskModalEl) {
            const editTaskModal = bootstrap.Modal.getInstance(editTaskModalEl);
            if (editTaskModal) editTaskModal.hide();
        }
        
        const idInput = document.getElementById(`edit_subtask_id-${kanbanId}`);
        if (idInput) idInput.value = subtaskId;
        const title = document.getElementById(`edit_subtask_title-${kanbanId}`);
        if (title) title.value = subtask.title || '';
        const desc = document.getElementById(`edit_subtask_description-${kanbanId}`);
        if (desc) desc.value = subtask.description || '';
        
        const ds = document.getElementById(`edit_subtask_date_start-${kanbanId}`);
        const de = document.getElementById(`edit_subtask_date_end-${kanbanId}`);
        if (ds) {
            if (subtask.date_start) {
                const dateStart = new Date(subtask.date_start);
                if (!isNaN(dateStart.getTime())) {
                    ds.value = subtask.date_start.split(' ')[0];
                }
            } else {
                ds.value = '';
            }
        }
        if (de) {
            if (subtask.date_end) {
                const dateEnd = new Date(subtask.date_end);
                if (!isNaN(dateEnd.getTime())) {
                    de.value = subtask.date_end.split(' ')[0];
                }
            } else {
                de.value = '';
            }
        }
        
        const pri = document.getElementById(`edit_subtask_priority-${kanbanId}`);
        if (pri) pri.value = subtask.priority || 'normal';
        const st = document.getElementById(`edit_subtask_status-${kanbanId}`);
        if (st) st.value = subtask.status || 'todo';

        const form = document.getElementById(`editSubtaskForm-${kanbanId}`);
        if (form) form.action = `/projects/${projectId}/kanban/${kanbanId}/subtasks/${subtaskId}`;

        setTimeout(() => {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }, 150);
        
        modalEl.addEventListener('hidden.bs.modal', function handler() {
            const editTaskModalEl = document.getElementById(`editKanbanModal-${kanbanId}`);
            if (editTaskModalEl) {
                const editTaskModal = bootstrap.Modal.getOrCreateInstance(editTaskModalEl);
                editTaskModal.show();
            }
            modalEl.removeEventListener('hidden.bs.modal', handler);
        });
    }

    // ---------- Delete subtask ----------
    function openDeleteSubtask(subtaskId, kanbanId) {
        if (!confirm('Hapus subtask ini?')) return;
        fetch(`/projects/${projectId}/kanban/${kanbanId}/subtasks/${subtaskId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) { showToast('Subtask dihapus', 'success'); loadSubtasks(kanbanId); }
            else showToast('Gagal hapus subtask', 'error');
        })
        .catch(err => { console.error(err); showToast('Error hapus subtask', 'error'); });
    }

    // Helper function to update subtask counter
    function updateSubtaskCounter(kanbanId) {
        const kanbanCard = document.querySelector(`.kanban-item[data-id="${kanbanId}"]`);
        if (!kanbanCard) return;

        const subtasksCollapse = kanbanCard.querySelector(`#subtasks-${kanbanId}`);
        if (!subtasksCollapse) return;

        const allCheckboxes = subtasksCollapse.querySelectorAll('.subtask-checkbox');
        const checkedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;
        const totalCount = allCheckboxes.length;

        const counterBtn = kanbanCard.querySelector(`[data-bs-target="#subtasks-${kanbanId}"] span`);
        if (counterBtn) {
            counterBtn.innerHTML = `<i class="ti ti-checklist"></i> ${checkedCount}/${totalCount} subtasks`;
        }
    }

    // Helper function to update kanban card subtasks area (after add/delete subtask)
    function updateKanbanCardSubtasks(kanbanId) {
        // Fetch updated subtasks from server
        fetch(`/projects/${projectId}/kanban/${kanbanId}/subtasks`)
            .then(r => r.json())
            .then(data => {
                const kanbanCard = document.querySelector(`.kanban-item[data-id="${kanbanId}"]`);
                if (!kanbanCard) return;
                
                const subtasksArea = kanbanCard.querySelector(`#subtasks-${kanbanId}`);
                if (!subtasksArea) return;
                
                const subtaskContainer = subtasksArea.querySelector('.px-2.pb-2');
                if (!subtaskContainer) return;
                
                // Clear existing subtasks
                subtaskContainer.innerHTML = '';
                
                if (!data.subtasks || data.subtasks.length === 0) {
                    // Update counter button to show 0/0
                    const counterBtn = kanbanCard.querySelector(`[data-bs-target="#subtasks-${kanbanId}"] span`);
                    if (counterBtn) {
                        counterBtn.innerHTML = `<i class="ti ti-checklist"></i> 0/0 subtasks`;
                    }
                    return;
                }
                
                // Rebuild subtask items
                data.subtasks.forEach(subtask => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'd-flex align-items-start gap-2 mb-2 p-2 bg-light rounded small';
                    
                    // Checkbox
                    const checkDiv = document.createElement('div');
                    checkDiv.className = 'form-check';
                    checkDiv.style.minWidth = '20px';
                    const checkbox = document.createElement('input');
                    checkbox.className = 'form-check-input subtask-checkbox';
                    checkbox.type = 'checkbox';
                    checkbox.checked = subtask.status === 'finished';
                    checkbox.dataset.subtaskId = subtask.id;
                    checkbox.dataset.kanbanId = kanbanId;
                    checkbox.onchange = function() { toggleSubtaskStatus(this); };
                    checkDiv.appendChild(checkbox);
                    
                    // Content
                    const contentDiv = document.createElement('div');
                    contentDiv.className = 'flex-grow-1';
                    
                    const titleDiv = document.createElement('div');
                    titleDiv.className = 'fw-semibold';
                    if (subtask.status === 'finished') {
                        titleDiv.classList.add('text-decoration-line-through', 'text-muted');
                    }
                    titleDiv.textContent = subtask.title;
                    contentDiv.appendChild(titleDiv);
                    
                    if (subtask.description) {
                        const descDiv = document.createElement('div');
                        descDiv.className = 'text-muted';
                        descDiv.style.fontSize = '0.75rem';
                        descDiv.textContent = subtask.description;
                        contentDiv.appendChild(descDiv);
                    }
                    
                    // Badges
                    const badgesDiv = document.createElement('div');
                    badgesDiv.className = 'mt-1';
                    
                    // Status badge
                    let statusBadge = '<span class="badge bg-secondary" style="font-size: 0.65rem;">To Do</span>';
                    if (subtask.status === 'inprogress') {
                        statusBadge = '<span class="badge bg-primary" style="font-size: 0.65rem;">In Progress</span>';
                    } else if (subtask.status === 'finished') {
                        statusBadge = '<span class="badge bg-success" style="font-size: 0.65rem;">Finished</span>';
                    }
                    
                    // Priority badge
                    let priorityBadge = '<span class="badge bg-secondary" style="font-size: 0.65rem;">Low</span>';
                    if (subtask.priority === 'urgent') {
                        priorityBadge = '<span class="badge bg-danger" style="font-size: 0.65rem;">Urgent</span>';
                    } else if (subtask.priority === 'high') {
                        priorityBadge = '<span class="badge bg-warning" style="font-size: 0.65rem;">High</span>';
                    } else if (subtask.priority === 'normal') {
                        priorityBadge = '<span class="badge bg-primary" style="font-size: 0.65rem;">Normal</span>';
                    }
                    
                    // Duration badge
                    let durationBadge = '';
                    if (subtask.duration) {
                        durationBadge = `<span class="badge bg-info" style="font-size: 0.65rem;"><i class="ti ti-clock"></i> ${subtask.duration} hari</span>`;
                    }
                    
                    badgesDiv.innerHTML = statusBadge + ' ' + priorityBadge + ' ' + durationBadge;
                    contentDiv.appendChild(badgesDiv);
                    
                    itemDiv.appendChild(checkDiv);
                    itemDiv.appendChild(contentDiv);
                    subtaskContainer.appendChild(itemDiv);
                });
                
                // Update counter
                const checkedCount = data.subtasks.filter(s => s.status === 'finished').length;
                const totalCount = data.subtasks.length;
                const counterBtn = kanbanCard.querySelector(`[data-bs-target="#subtasks-${kanbanId}"] span`);
                if (counterBtn) {
                    counterBtn.innerHTML = `<i class="ti ti-checklist"></i> ${checkedCount}/${totalCount} subtasks`;
                }
            })
            .catch(err => {
                console.error('Failed to update kanban card subtasks:', err);
            });
    }

    // ---------- Handle ALL form submissions ----------
    let isSubmitting = false;
    
    document.addEventListener('submit', async function(e) {
        const form = e.target;
        
        // EDIT TASK UTAMA
        if (form.matches('form[id^="editKanbanForm-"]')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            if (isSubmitting) return;
            isSubmitting = true;
            
            // PERBAIKAN: Ambil full UUID dari form ID
            // Form ID format: editKanbanForm-{UUID}
            const formId = form.id; // e.g., "editKanbanForm-bc28dab2-1234-5678-9abc-def012345678"
            const taskId = formId.replace('editKanbanForm-', ''); // Ambil semua setelah prefix
            
            const fd = new FormData(form);
            
            // Tambahkan _method untuk Laravel PUT request
            if (!fd.has('_method')) {
                fd.append('_method', 'PUT');
            }
            
            // PERBAIKAN: URL harus sesuai dengan route Laravel
            const action = `/projects/${projectId}/kanban/${taskId}`;
            
            console.log('Submitting edit task:', taskId);
            console.log('Action URL:', action);
            console.log('Project ID:', projectId);
            
            try {
                const resp = await fetch(action, {
                    method: 'POST',
                    headers: { 
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: fd
                });
                
                console.log('Response status:', resp.status);
                
                // Cek apakah response OK (2xx)
                if (resp.ok) {
                    // Parse JSON response
                    const json = await resp.json();
                    console.log('Response:', json);
                    
                    // Tutup modal dengan smooth
                    const modalEl = document.getElementById(`editKanbanModal-${taskId}`);
                    if (modalEl) {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    }
                    
                    // Bersihkan backdrop setelah transisi modal selesai
                    setTimeout(() => {
                        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.removeProperty('padding-right');
                        document.body.style.removeProperty('overflow');
                    }, 300);
                    
                    showToast('Task berhasil diupdate!', 'success');
                    
                    // Reload dengan delay yang lebih smooth
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    // Coba parse JSON error
                    try {
                        const json = await resp.json();
                        console.error('Error response:', json);
                        showToast(json.message || 'Gagal update task', 'error');
                    } catch {
                        console.error('Response status:', resp.status);
                        showToast('Gagal update task (Status: ' + resp.status + ')', 'error');
                    }
                }
            } catch (err) {
                console.error('editTask error', err);
                showToast('Gagal update task: ' + err.message, 'error');
            } finally {
                isSubmitting = false;
            }
        }
        
        // ADD SUBTASK
        else if (form.matches('form[id^="addSubtaskForm-"]')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            if (isSubmitting) return;
            isSubmitting = true;
            
            const fd = new FormData(form);
            const kanbanId = fd.get('kanban_id') || form.querySelector('[name="kanban_id"]')?.value;
            const action = `/projects/${projectId}/kanban/${kanbanId}/subtasks`;
            
            try {
                const resp = await fetch(action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: fd
                });
                const json = await resp.json();
                if (json.success) {
                    // Tutup modal add subtask
                    const modalEl = document.getElementById(`addSubtaskModal-${kanbanId}`);
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    form.reset();
                    
                    showToast('Subtask ditambahkan!', 'success');
                    
                    // Update subtask list di modal edit task
                    setTimeout(() => {
                        const editTaskModalEl = document.getElementById(`editKanbanModal-${kanbanId}`);
                        if (editTaskModalEl) {
                            const editTaskModal = bootstrap.Modal.getOrCreateInstance(editTaskModalEl);
                            editTaskModal.show();
                            // Reload subtasks list
                            loadSubtasks(kanbanId);
                        }
                    }, 150);
                    
                    // Update subtask counter dan collapse area di kanban card
                    updateKanbanCardSubtasks(kanbanId);
                } else {
                    showToast('Gagal menambah subtask', 'error');
                }
            } catch (err) {
                console.error('addSubtask error', err);
                showToast('Gagal menambah subtask', 'error');
            } finally {
                isSubmitting = false;
            }
        }
        
        // EDIT SUBTASK
        else if (form.matches('form[id^="editSubtaskForm-"]')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            
            if (isSubmitting) return;
            isSubmitting = true;
            
            const fd = new FormData(form);
            const kanbanId = fd.get('kanban_id') || form.querySelector('[name="kanban_id"]')?.value;
            const subtaskId = fd.get('subtask_id') || form.querySelector('[name="subtask_id"]')?.value;
            const action = `/projects/${projectId}/kanban/${kanbanId}/subtasks/${subtaskId}`;
            
            try {
                const resp = await fetch(action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: fd
                });
                const json = await resp.json();
                if (json.success) {
                    // Tutup modal edit subtask
                    const editSubtaskModal = document.getElementById(`editSubtaskModal-${kanbanId}`);
                    if (editSubtaskModal) {
                        const modal = bootstrap.Modal.getInstance(editSubtaskModal);
                        if (modal) modal.hide();
                    }
                    
                    // Reset form
                    form.reset();
                    
                    showToast('Subtask berhasil diupdate!', 'success');
                    
                    // Update subtask list di modal edit task
                    setTimeout(() => {
                        const editTaskModalEl = document.getElementById(`editKanbanModal-${kanbanId}`);
                        if (editTaskModalEl) {
                            const editTaskModal = bootstrap.Modal.getOrCreateInstance(editTaskModalEl);
                            editTaskModal.show();
                            // Reload subtasks list
                            loadSubtasks(kanbanId);
                        }
                    }, 150);
                    
                    // Update subtask counter dan collapse area di kanban card (tanpa reload page)
                    updateKanbanCardSubtasks(kanbanId);
                } else {
                    showToast('Gagal update subtask', 'error');
                }
            } catch (err) {
                console.error('editSubtask err', err);
                showToast('Gagal update subtask', 'error');
            } finally {
                isSubmitting = false;
            }
        }
    }, { capture: true });

    // ---------- Toggle subtask status (checkbox) ----------
    window.toggleSubtaskStatus = function(checkbox) {
        const subtaskId = checkbox.dataset.subtaskId;
        const kanbanId = checkbox.dataset.kanbanId;
        const newStatus = checkbox.checked ? 'finished' : 'todo';
        
        // Optimistic UI - update title styling
        const container = checkbox.closest('.d-flex.align-items-start');
        const titleEl = container?.querySelector('.fw-semibold');
        if (checkbox.checked) {
            titleEl?.classList.add('text-decoration-line-through','text-muted');
        } else {
            titleEl?.classList.remove('text-decoration-line-through','text-muted');
        }

        // Optimistic UI - update badge status in the kanban card collapse area
        const badgeContainer = container?.querySelector('.mt-1');
        if (badgeContainer) {
            const badges = badgeContainer.querySelectorAll('.badge');
            if (badges.length > 0) {
                const statusBadge = badges[0];
                if (newStatus === 'finished') {
                    statusBadge.className = 'badge bg-success';
                    statusBadge.style.fontSize = '0.65rem';
                    statusBadge.textContent = 'Finished';
                } else if (newStatus === 'inprogress') {
                    statusBadge.className = 'badge bg-primary';
                    statusBadge.style.fontSize = '0.65rem';
                    statusBadge.textContent = 'In Progress';
                } else {
                    statusBadge.className = 'badge bg-secondary';
                    statusBadge.style.fontSize = '0.65rem';
                    statusBadge.textContent = 'To Do';
                }
            }
        }

        // Update subtask counter in main kanban card
        updateSubtaskCounter(kanbanId);

        fetch(`/projects/${projectId}/kanban/${kanbanId}/subtasks/${subtaskId}/toggle-status`, {
            method: 'POST',
            headers: { 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json' },
            body: JSON.stringify({ status: newStatus })
        })
        .then(r=>r.json())
        .then(res=>{
            if (res.success) { 
                showToast('Status berhasil diupdate!', 'success');
            } else {
                // Revert on failure
                checkbox.checked = !checkbox.checked;
                if (checkbox.checked) {
                    titleEl?.classList.add('text-decoration-line-through','text-muted');
                } else {
                    titleEl?.classList.remove('text-decoration-line-through','text-muted');
                }
                // Revert badge as well
                if (badgeContainer) {
                    const badges = badgeContainer.querySelectorAll('.badge');
                    if (badges.length > 0) {
                        const statusBadge = badges[0];
                        const revertStatus = checkbox.checked ? 'finished' : 'todo';
                        if (revertStatus === 'finished') {
                            statusBadge.className = 'badge bg-success';
                            statusBadge.textContent = 'Finished';
                        } else {
                            statusBadge.className = 'badge bg-secondary';
                            statusBadge.textContent = 'To Do';
                        }
                        statusBadge.style.fontSize = '0.65rem';
                    }
                }
                showToast('Gagal update status', 'error');
            }
        })
        .catch(err => {
            console.error(err);
            // Revert on error
            checkbox.checked = !checkbox.checked;
            if (checkbox.checked) {
                titleEl?.classList.add('text-decoration-line-through','text-muted');
            } else {
                titleEl?.classList.remove('text-decoration-line-through','text-muted');
            }
            // Revert badge as well
            if (badgeContainer) {
                const badges = badgeContainer.querySelectorAll('.badge');
                if (badges.length > 0) {
                    const statusBadge = badges[0];
                    const revertStatus = checkbox.checked ? 'finished' : 'todo';
                    if (revertStatus === 'finished') {
                        statusBadge.className = 'badge bg-success';
                        statusBadge.textContent = 'Finished';
                    } else {
                        statusBadge.className = 'badge bg-secondary';
                        statusBadge.textContent = 'To Do';
                    }
                    statusBadge.style.fontSize = '0.65rem';
                }
            }
            showToast('Error update status', 'error');
        });
    };

    // Helper function to update subtask counter
    function updateSubtaskCounter(kanbanId) {
        const kanbanCard = document.querySelector(`.kanban-item[data-id="${kanbanId}"]`);
        if (!kanbanCard) return;

        const subtasksCollapse = kanbanCard.querySelector(`#subtasks-${kanbanId}`);
        if (!subtasksCollapse) return;

        const allCheckboxes = subtasksCollapse.querySelectorAll('.subtask-checkbox');
        const checkedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;
        const totalCount = allCheckboxes.length;

        const counterBtn = kanbanCard.querySelector(`[data-bs-target="#subtasks-${kanbanId}"] span`);
        if (counterBtn) {
            counterBtn.innerHTML = `<i class="ti ti-checklist"></i> ${checkedCount}/${totalCount} subtasks`;
        }
    }

    // Helper function to update kanban card subtasks area (after add/delete subtask)
    function updateKanbanCardSubtasks(kanbanId) {
        // Fetch updated subtasks from server
        fetch(`/projects/${projectId}/kanban/${kanbanId}/subtasks`)
            .then(r => r.json())
            .then(data => {
                const kanbanCard = document.querySelector(`.kanban-item[data-id="${kanbanId}"]`);
                if (!kanbanCard) return;
                
                const subtasksArea = kanbanCard.querySelector(`#subtasks-${kanbanId}`);
                if (!subtasksArea) return;
                
                const subtaskContainer = subtasksArea.querySelector('.px-2.pb-2');
                if (!subtaskContainer) return;
                
                // Clear existing subtasks
                subtaskContainer.innerHTML = '';
                
                if (!data.subtasks || data.subtasks.length === 0) {
                    // Update counter button to show 0/0
                    const counterBtn = kanbanCard.querySelector(`[data-bs-target="#subtasks-${kanbanId}"] span`);
                    if (counterBtn) {
                        counterBtn.innerHTML = `<i class="ti ti-checklist"></i> 0/0 subtasks`;
                    }
                    return;
                }
                
                // Rebuild subtask items
                data.subtasks.forEach(subtask => {
                    const itemDiv = document.createElement('div');
                    itemDiv.className = 'd-flex align-items-start gap-2 mb-2 p-2 bg-light rounded small';
                    
                    // Checkbox
                    const checkDiv = document.createElement('div');
                    checkDiv.className = 'form-check';
                    checkDiv.style.minWidth = '20px';
                    const checkbox = document.createElement('input');
                    checkbox.className = 'form-check-input subtask-checkbox';
                    checkbox.type = 'checkbox';
                    checkbox.checked = subtask.status === 'finished';
                    checkbox.dataset.subtaskId = subtask.id;
                    checkbox.dataset.kanbanId = kanbanId;
                    checkbox.onchange = function() { toggleSubtaskStatus(this); };
                    checkDiv.appendChild(checkbox);
                    
                    // Content
                    const contentDiv = document.createElement('div');
                    contentDiv.className = 'flex-grow-1';
                    
                    const titleDiv = document.createElement('div');
                    titleDiv.className = 'fw-semibold';
                    if (subtask.status === 'finished') {
                        titleDiv.classList.add('text-decoration-line-through', 'text-muted');
                    }
                    titleDiv.textContent = subtask.title;
                    contentDiv.appendChild(titleDiv);
                    
                    if (subtask.description) {
                        const descDiv = document.createElement('div');
                        descDiv.className = 'text-muted';
                        descDiv.style.fontSize = '0.75rem';
                        descDiv.textContent = subtask.description;
                        contentDiv.appendChild(descDiv);
                    }
                    
                    // Badges
                    const badgesDiv = document.createElement('div');
                    badgesDiv.className = 'mt-1';
                    
                    // Status badge
                    let statusBadge = '<span class="badge bg-secondary" style="font-size: 0.65rem;">To Do</span>';
                    if (subtask.status === 'inprogress') {
                        statusBadge = '<span class="badge bg-primary" style="font-size: 0.65rem;">In Progress</span>';
                    } else if (subtask.status === 'finished') {
                        statusBadge = '<span class="badge bg-success" style="font-size: 0.65rem;">Finished</span>';
                    }
                    
                    // Priority badge
                    let priorityBadge = '<span class="badge bg-secondary" style="font-size: 0.65rem;">Low</span>';
                    if (subtask.priority === 'urgent') {
                        priorityBadge = '<span class="badge bg-danger" style="font-size: 0.65rem;">Urgent</span>';
                    } else if (subtask.priority === 'high') {
                        priorityBadge = '<span class="badge bg-warning" style="font-size: 0.65rem;">High</span>';
                    } else if (subtask.priority === 'normal') {
                        priorityBadge = '<span class="badge bg-primary" style="font-size: 0.65rem;">Normal</span>';
                    }
                    
                    // Duration badge
                    let durationBadge = '';
                    if (subtask.duration) {
                        durationBadge = `<span class="badge bg-info" style="font-size: 0.65rem;"><i class="ti ti-clock"></i> ${subtask.duration} hari</span>`;
                    }
                    
                    badgesDiv.innerHTML = statusBadge + ' ' + priorityBadge + ' ' + durationBadge;
                    contentDiv.appendChild(badgesDiv);
                    
                    itemDiv.appendChild(checkDiv);
                    itemDiv.appendChild(contentDiv);
                    subtaskContainer.appendChild(itemDiv);
                });
                
                // Update counter
                const checkedCount = data.subtasks.filter(s => s.status === 'finished').length;
                const totalCount = data.subtasks.length;
                const counterBtn = kanbanCard.querySelector(`[data-bs-target="#subtasks-${kanbanId}"] span`);
                if (counterBtn) {
                    counterBtn.innerHTML = `<i class="ti ti-checklist"></i> ${checkedCount}/${totalCount} subtasks`;
                }
            })
            .catch(err => {
                console.error('Failed to update kanban card subtasks:', err);
            });
    }

    // ---------- Duration calculate for create modal ----------
    const createDateStart = document.getElementById('create_date_start');
    const createDateEnd = document.getElementById('create_date_end');
    const createDurationDisplay = document.getElementById('create_duration_display');
    const createDurationValue = document.getElementById('create_duration_value');
    function calculateCreateDuration() {
        if (!createDateStart || !createDateEnd) return;
        const s = createDateStart.value; const e = createDateEnd.value;
        if (s && e) { const diff = Math.ceil((new Date(e)-new Date(s))/(1000*60*60*24)); createDurationValue.value = diff+' hari'; createDurationDisplay.style.display='block'; }
        else createDurationDisplay.style.display='none';
    }
    if (createDateStart && createDateEnd) { createDateStart.addEventListener('change', calculateCreateDuration); createDateEnd.addEventListener('change', calculateCreateDuration); }

    // ---------- Helper: calculate duration in edit task modal ----------
    document.querySelectorAll('input[id^="edit_date_start-"], input[id^="edit_date_end-"]').forEach(inp => {
        inp.addEventListener('change', (e) => {
            const id = e.target.id.split('-').pop();
            const s = document.getElementById(`edit_date_start-${id}`)?.value;
            const eVal = document.getElementById(`edit_date_end-${id}`)?.value;
            const out = document.getElementById(`edit_duration-${id}`);
            if (s && eVal && out) { const diff = Math.ceil((new Date(eVal)-new Date(s))/(1000*60*60*24)); out.value = diff+' hari'; }
            else if (out) out.value = '-';
        });
    });

});
</script>