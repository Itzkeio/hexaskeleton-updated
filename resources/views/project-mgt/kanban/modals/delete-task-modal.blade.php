<div class="modal fade" id="deleteKanbanModal-{{ $task->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Progress</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Apakah Anda yakin ingin menghapus <b>{{ $task->title }}</b>?
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button
                    type="button"
                    class="btn btn-danger confirm-delete-task"
                    data-task-id="{{ $task->id }}">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>
