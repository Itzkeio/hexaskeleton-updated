@if($task->files && $task->files->count() > 0)
    <div class="border rounded p-2 bg-light mb-3">
        <label class="fw-semibold d-block mb-2">
            <i class="ti ti-paperclip"></i> File Terlampir
        </label>

        @foreach($task->files as $file)
            <div class="d-flex justify-content-between align-items-center small mb-1">
                <a
                    href="{{ asset('storage/' . $file->file_path) }}"
                    target="_blank"
                    class="text-decoration-none text-primary">
                    📎 {{ $file->filename }}
                </a>
                <button
                    type="button"
                    class="btn btn-sm btn-link text-danger p-0"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteFileModalEdit-{{ $file->id }}-{{ $task->id }}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>

            @include('project-mgt.kanban.modals.delete-file-edit-task-modal', [
                'file' => $file,
                'task' => $task,
            ])
        @endforeach
    </div>
@endif
