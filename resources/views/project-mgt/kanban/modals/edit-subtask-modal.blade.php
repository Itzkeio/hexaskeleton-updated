<div class="modal fade" id="editSubtaskModal-{{ $task->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form
            id="editSubtaskForm-{{ $task->id }}"
            method="POST"
            class="modal-content"
            enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Edit Subtask</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="subtask_id" id="edit_subtask_id-{{ $task->id }}">
                <input
                    type="hidden"
                    name="kanban_id"
                    id="edit_subtask_kanban_id-{{ $task->id }}"
                    value="{{ $task->id }}">

                <div class="mb-2">
                    <label class="form-label">Title</label>
                    <input
                        name="title"
                        id="edit_subtask_title-{{ $task->id }}"
                        class="form-control"
                        required>
                </div>
                <div class="mb-2">
                    <label class="form-label">Description</label>
                    <textarea
                        name="description"
                        id="edit_subtask_description-{{ $task->id }}"
                        class="form-control"
                        rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Notes (optional)</label>
                    <textarea
                        name="notes"
                        id="edit_subtask_notes-{{ $task->id }}"
                        class="form-control"
                        rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">Date Start</label>
                    <input
                        type="date"
                        name="date_start"
                        id="edit_subtask_date_start-{{ $task->id }}"
                        class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Date End</label>
                    <input
                        type="date"
                        name="date_end"
                        id="edit_subtask_date_end-{{ $task->id }}"
                        class="form-control">
                </div>
                <div class="mb-2">
                    <label class="form-label">Priority</label>
                    <select
                        name="priority"
                        id="edit_subtask_priority-{{ $task->id }}"
                        class="form-select">
                        <option value="low">Low</option>
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label">Status</label>
                    <select
                        name="status"
                        id="edit_subtask_status-{{ $task->id }}"
                        class="form-select">
                        @foreach ($project->statuses as $status)
                        <option value="{{ $status->key }}">
                            {{ $status->label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label">Upload File (optional)</label>
                    <input type="file" name="files[]" class="form-control" multiple>
                    <small class="text-muted">File baru akan ditambahkan, tidak menghapus file lama.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Subtask</button>
            </div>
        </form>
    </div>
</div>
