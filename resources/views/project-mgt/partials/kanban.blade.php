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
        {{-- LOOP KOLOM --}}
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
                    <div class="card mb-2 p-2 shadow-sm border kanban-item position-relative {{ $priorityClass[$task->priority] }}"
                        data-id="{{ $task->id }}"
                        data-title="{{ $task->title }}"
                        data-description="{{ $task->description }}"
                        data-priority="{{ $task->priority }}"
                        data-date_start="{{ $task->date_start }}"
                        data-date_end="{{ $task->date_end }}"
                        data-duration="{{ $task->duration }}">
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
                        {{-- ISI CARD --}}
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
                            {{-- BUTTON --}}
                            <div class="d-flex gap-1">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editKanbanModal-{{ $project->id }}">
                                    <i class="ti ti-edit" style="pointer-events:none;"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#deleteKanbanModal-{{ $task->id }}">
                                    <i class="ti ti-trash" style="pointer-events:none;"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- MODAL DELETE --}}
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
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- MODAL CREATE --}}
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
                    <input type="date" name="date_start" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Date End</label>
                    <input type="date" name="date_end" class="form-control">
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

{{-- MODAL EDIT --}}
<div class="modal fade" id="editKanbanModal-{{ $project->id }}" tabindex="-1">
    <div class="modal-dialog">
        <form id="editKanbanForm-{{ $project->id }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit_task_id-{{ $project->id }}">
                <div class="mb-2">
                    <label class="form-label">Title</label>
                    <input name="title" id="edit_title-{{ $project->id }}" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Date Start</label>
                    <input type="date" name="date_start" id="edit_date_start-{{ $project->id }}" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Date End</label>
                    <input type="date" name="date_end" id="edit_date_end-{{ $project->id }}" class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Duration</label>
                    <input type="text" id="edit_duration-{{ $project->id }}" class="form-control" disabled>
                </div>
                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_description-{{ $project->id }}" class="form-control"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Priority</label>
                    <select name="priority" id="edit_priority-{{ $project->id }}" class="form-select">
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </form>
    </div>
</div>

{{-- SORTABLE + JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<style>
    .sortable-ghost {
        opacity: 0.3;
        background: #d0d0d0 !important;
    }

    .sortable-drag {
        transform: rotate(2deg) scale(1.03);
        transition: 0.15s ease;
    }

    .kanban-item {
        padding-top: 28px !important;
    }

    /* Toast Animation */
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
</style>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const projectId = "{{ $project->id }}";
        const statuses = ["todo", "inprogress", "finished"];

        function sortColumnByPriority(column) {
            const priorityOrder = {
                urgent: 0,
                high: 1,
                normal: 2,
                low: 3
            };
            let cards = Array.from(column.children);
            cards.sort((a, b) => {
                return priorityOrder[a.dataset.priority] - priorityOrder[b.dataset.priority];
            });
            cards.forEach(card => column.appendChild(card));
        }

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

                    const updateUrl = `/projects/${projectId}/kanban/status`;

                    console.log('=== DRAG & DROP DEBUG ===');
                    console.log('Task ID:', id);
                    console.log('New Status:', newStatus);
                    console.log('Project ID:', projectId);
                    console.log('URL:', updateUrl);
                    console.log('========================');

                    // AJAX Request
                    fetch(updateUrl, {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                id: id,
                                status: newStatus
                            })
                        })
                        .then(response => {
                            console.log('Response status:', response.status);
                            return response.json();
                        })
                        .then(result => {
                            console.log('Result:', result);

                            if (result.success) {
                                console.log('✅ Status updated successfully!');

                                // Update badge status secara realtime
                                updateCardBadge(card, newStatus);

                                // Sort kolom berdasarkan priority
                                sortColumnByPriority(col);

                                // Tampilkan notifikasi sukses
                                showToast('Status berhasil diupdate!', 'success');
                            } else {
                                console.error('❌ Failed to update status:', result);
                                showToast('Gagal mengupdate status!', 'error');
                            }
                        })
                        .catch(error => {
                            console.error('❌ Error:', error);
                            showToast('Terjadi kesalahan: ' + error.message, 'error');
                        });
                }
            });
        });

        // === FUNCTION: UPDATE BADGE STATUS REALTIME ===
        function updateCardBadge(card, status) {
            const badgeContainer = card.querySelector('.position-absolute.top-0.end-0.m-1');
            let badgeHTML = '';

            if (status === 'todo') {
                badgeHTML = '<span class="badge bg-secondary">To Do</span>';
            } else if (status === 'inprogress') {
                badgeHTML = '<span class="badge bg-primary">In Progress</span>';
            } else if (status === 'finished') {
                badgeHTML = '<span class="badge bg-success">Finished</span>';
            }

            if (badgeContainer) {
                badgeContainer.innerHTML = badgeHTML;
            }
        }

        // === FUNCTION: SHOW TOAST NOTIFICATION ===
        function showToast(message, type = 'success') {
            // Hapus toast lama jika ada
            const oldToast = document.getElementById('kanban-toast');
            if (oldToast) {
                oldToast.remove();
            }

            // Buat toast baru
            const toast = document.createElement('div');
            toast.id = 'kanban-toast';
            toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed top-0 end-0 m-3 shadow-lg`;
            toast.style.cssText = 'z-index: 9999; min-width: 250px; animation: slideIn 0.3s ease-out;';
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="ti ti-${type === 'success' ? 'check' : 'alert-circle'} me-2"></i>
                    <span>${message}</span>
                </div>
            `;

            document.body.appendChild(toast);

            // Auto hide setelah 3 detik
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // === EDIT MODAL FILLER ===
        document.addEventListener("click", function(e) {
            const btn = e.target.closest(".edit-btn");
            if (!btn) return;

            const card = btn.closest(".kanban-item");
            const id = card.dataset.id;

            console.log("Editing task:", id); // Debug

            document.getElementById(`edit_task_id-${projectId}`).value = id;
            document.getElementById(`edit_title-${projectId}`).value = card.dataset.title;
            document.getElementById(`edit_description-${projectId}`).value = card.dataset.description;
            document.getElementById(`edit_priority-${projectId}`).value = card.dataset.priority;
            document.getElementById(`edit_date_start-${projectId}`).value = card.dataset.date_start;
            document.getElementById(`edit_date_end-${projectId}`).value = card.dataset.date_end;
            document.getElementById(`edit_duration-${projectId}`).value = (card.dataset.duration ? card.dataset.duration + " days" : "-");

            const formAction = `/projects/${projectId}/kanban/${id}`;
            document.getElementById(`editKanbanForm-${projectId}`).action = formAction;

            console.log("Form action set to:", formAction); // Debug
        });
    });
</script>