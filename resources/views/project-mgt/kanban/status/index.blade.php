@extends('layout.app')

@section('content')
<div class="container py-3">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold">
            Manage Status — {{ $project->name }}
        </h4>

        <a href="{{ route('projects.index') }}" class="btn btn-light border">
            <i class="ti ti-arrow-left"></i> Kembali ke Projects
        </a>
    </div>

    {{-- SUCCESS / ERROR --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- CREATE STATUS --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Tambah Status Baru</h6>

            <form action="{{ route('kanban.status.store', $project->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label class="form-label">Label</label>
                        <input type="text" name="label" class="form-control" required>
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="form-label">Background Color</label>
                        <input type="color" name="color_bg" value="#e9ecef" class="form-control form-control-color">
                    </div>

                    <div class="col-md-3 mb-2">
                        <label class="form-label">Border Color</label>
                        <input type="color" name="color_border" value="#bfbfbf" class="form-control form-control-color">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="form-label d-block">&nbsp;</label>
                        <button class="btn btn-primary w-100">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- LIST STATUS --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Daftar Status</h6>

            <ul id="status-list" class="list-group">
                @foreach ($statuses as $status)
                    <li class="list-group-item d-flex justify-content-between align-items-center"
                        data-id="{{ $status->id }}">

                        <div class="d-flex align-items-center gap-3">

                            {{-- DRAG ICON --}}
                            <i class="ti ti-grip-vertical text-muted"></i>

                            {{-- COLOR PREVIEW --}}
                            <div style="width:20px; height:20px; border-radius:4px;
                                background: {{ $status->color_bg }};
                                border: 2px solid {{ $status->color_border }};">
                            </div>

                            <span class="{{ $status->deleted_at ? 'text-danger text-decoration-line-through' : 'fw-semibold' }}">
                                {{ $status->label }}
                                @if($status->deleted_at)
                                    <small class="text-muted">(Deleted)</small>
                                @endif
                            </span>
                        </div>

                        <div class="d-flex gap-2">
                            {{-- If deleted → show restore --}}
                            @if($status->deleted_at)
                                <form action="{{ route('kanban.status.restore', [$project->id, $status->id]) }}"
                                      method="POST">
                                    @csrf
                                    <button class="btn btn-sm btn-success">
                                        <i class="ti ti-restore"></i>
                                    </button>
                                </form>
                            @else
                                {{-- EDIT --}}
                                <button class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editStatusModal-{{ $status->id }}">
                                    <i class="ti ti-edit"></i>
                                </button>

                                {{-- DELETE --}}
                                <form action="{{ route('kanban.status.delete', [$project->id, $status->id]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Hapus status ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </li>

                    {{-- EDIT MODAL --}}
                    <div class="modal fade" id="editStatusModal-{{ $status->id }}" tabindex="-1">
                        <div class="modal-dialog">
                            <form action="{{ route('kanban.status.update', [$project->id, $status->id]) }}"
                                  method="POST"
                                  class="modal-content">
                                @csrf
                                @method('PUT')

                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Status</h5>
                                    <button class="btn-close" data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    <label class="form-label">Label</label>
                                    <input type="text" name="label" class="form-control"
                                           value="{{ $status->label }}" required>

                                    <div class="row mt-3">
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Background Color</label>
                                            <input type="color" name="color_bg"
                                                   class="form-control form-control-color"
                                                   value="{{ $status->color_bg }}">
                                        </div>

                                        <div class="col-md-6 mb-2">
                                            <label class="form-label">Border Color</label>
                                            <input type="color" name="color_border"
                                                   class="form-control form-control-color"
                                                   value="{{ $status->color_border }}">
                                        </div>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button class="btn btn-primary">Update</button>
                                </div>

                            </form>
                        </div>
                    </div>

                @endforeach
            </ul>
        </div>
    </div>
</div>


{{-- Sortable JS --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
<script>
    const list = document.getElementById("status-list");

    new Sortable(list, {
        animation: 150,
        handle: ".ti-grip-vertical",
        onEnd: function () {
            const order = [];
            list.querySelectorAll("li").forEach(item => {
                order.push(item.dataset.id);
            });

            fetch("{{ route('kanban.status.order', $project->id) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ order }),
            });
        }
    });
</script>
@endsection
