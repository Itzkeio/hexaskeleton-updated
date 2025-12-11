<div class="modal fade" id="editKanbanModal-{{ $task->id }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"">
        <form
            id="editKanbanForm-{{ $task->id }}"
            method="POST"
            class="modal-content"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Edit Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input
                    type="hidden"
                    id="edit_task_id-{{ $task->id }}"
                    name="kanban_id"
                    value="{{ $task->id }}">

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
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" id="edit_notes-{{ $task->id }}" class="form-control" rows="2"></textarea>
                </div>

                <div class="mb-2">
                    <label class="form-label">PIC (Project)</label>
                    <input
                        class="form-control bg-light"
                        value="{{ $project->pic ? $project->pic->name : '-' }}"
                        disabled>
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

                <div class="mb-3 mt-3">
                    <label class="form-label">Upload File</label>
                    <input type="file" name="files[]" class="form-control" multiple>
                    <small class="text-muted">Dapat unggah banyak file. File lama tidak akan terhapus.</small>
                </div>

                {{-- EXISTING TASK FILES (EDIT) --}}
                @include('project-mgt.kanban.partials.task-files-edit', ['task' => $task])

                <hr class="my-4">

                {{-- SUBTASKS SECTION --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Subtasks</h6>
                    <button
                        type="button"
                        class="btn btn-sm btn-success open-add-subtask-btn"
                        data-task-id="{{ $task->id }}">
                        <i class="ti ti-plus"></i> Add Subtask
                    </button>
                </div>

                <div
                    id="subtasks-list-{{ $task->id }}"
                    class="border rounded p-2"
                    style="max-height: 300px; overflow-y: auto;">
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
