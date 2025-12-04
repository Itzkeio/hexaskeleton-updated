@if($subtask->files && $subtask->files->count() > 0)
    <div class="mt-1 small">
        @foreach($subtask->files as $file)
            <div class="d-flex justify-content-between align-items-center mb-1">
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
                    data-bs-target="#deleteFileModalSubtask-{{ $file->id }}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>

            @include('project-mgt.kanban.modals.delete-file-subtask-modal', [
                'file'    => $file,
                'subtask' => $subtask,
            ])
        @endforeach
    </div>
@endif
