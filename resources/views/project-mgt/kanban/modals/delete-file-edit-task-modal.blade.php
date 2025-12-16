<div class="modal fade" 
     id="deleteFileModalEdit-{{ $file->id }}-{{ $task->id }}" 
     tabindex="-1"
     data-bs-backdrop="static"
     data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Hapus File</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <p class="mb-2">
                    Apakah Anda yakin ingin menghapus file <strong>{{ $file->filename }}</strong>?
                </p>
                <small class="text-muted">File yang sudah dihapus tidak dapat dikembalikan.</small>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button
                    type="button"
                    class="btn btn-danger confirm-delete-file"
                    data-file-id="{{ $file->id }}"
                    data-type="task"
                    data-kanban-id="{{ $task->id }}"
                    data-parent-modal="editKanbanModal-{{ $task->id }}">
                    <i class="ti ti-trash"></i> Hapus
                </button>
            </div>

        </div>
    </div>
</div>