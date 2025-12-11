<div class="modal fade" id="createKanbanModal-{{ $project->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form
            id="createKanbanForm"
            action="{{ route('kanban.store', $project->id) }}"
            method="POST"
            class="modal-content"
            enctype="multipart/form-data">
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
                    <label class="form-label">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="2"></textarea>
                </div>
                <div class="mb-2">
                    <label class="form-label">PIC (Project)</label>
                    <input
                        class="form-control bg-light"
                        value="{{ $project->pic_name }}" disabled>
                </div>
                <div class="mb-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select" required>
                        @foreach ($project->statuses as $status)
                        <option value="{{ $status->key }}">
                            {{ $status->label }}
                        </option>
                        @endforeach
                    </select>
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
                <div class="mb-2">
                    <label class="form-label">Upload File (optional)</label>
                    <input type="file" name="files[]" class="form-control" multiple>
                    <small class="text-muted">Dapat upload lebih dari satu file.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
