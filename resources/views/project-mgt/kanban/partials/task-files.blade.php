@if($task->files && $task->files->count() > 0)
    <div class="mt-2 border-top pt-2 small">
        <span class="fw-semibold">
            <i class="ti ti-paperclip"></i> Files:
        </span>

        @foreach($task->files as $file)
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
                    data-bs-target="#deleteFileModal-{{ $file->id }}">
                    <i class="ti ti-trash"></i>
                </button>
            </div>

            {{-- MODAL DELETE FILE (TASK) --}}
            @include('project-mgt.kanban.modals.delete-file-modal', [
                'file' => $file,
                'task' => $task,
            ])
        @endforeach
    </div>
@endif
