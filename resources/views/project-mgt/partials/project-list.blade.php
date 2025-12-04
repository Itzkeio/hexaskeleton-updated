{{-- Partial view untuk project buttons dan project data --}}

@if($projects->count() > 0)
{{-- Project Buttons --}}
<div id="projectBar" class="d-flex flex-nowrap overflow-auto gap-2 py-2">
    @foreach($projects as $index => $project)
    <button class="btn btn-{{ $index === 0 ? 'primary' : 'outline-secondary' }} rounded-pill px-4 py-2 flex-shrink-0 project-btn"
        data-project-index="{{ $index }}"
        data-project-id="{{ $project->id }}">
        @if($project->icon)
        <img src="{{ asset('storage/icons/' . $project->icon) }}"
            alt="icon" class="me-2 rounded-circle"
            style="width: 20px; height: 20px; object-fit: cover;"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='inline';">
        <i class="ti ti-folder me-1 d-none"></i>
        @else
        <i class="ti ti-folder me-2"></i>
        @endif
        <span>{{ $project->name }}</span>
    </button>
    @endforeach
</div>
@else
<div class="alert alert-warning mb-0">
    <i class="ti ti-alert-circle me-2"></i>
    Tidak ada project yang ditemukan.
</div>
@endif

{{-- Project Data Content --}}
@if($projects->count() > 0)
@foreach($projects as $index => $project)
<div class="project-data"
    data-index="{{ $index }}"
    style="display: {{ $index === 0 ? 'block' : 'none' }}">

    {{-- ✨ PERBAIKAN: Tabs dengan ID yang unik per project --}}
    <ul class="nav nav-tabs mb-3 bg-white px-3 pt-3 rounded-top mt-4" id="projectTabs-{{ $index }}" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active"
                id="overview-tab-{{ $index }}"
                data-bs-toggle="tab"
                data-bs-target="#overview-{{ $index }}"
                type="button"
                role="tab"
                aria-controls="overview-{{ $index }}"
                aria-selected="true">
                <i class="ti ti-info-circle me-1"></i>Details
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link"
                id="versions-tab-{{ $index }}"
                data-bs-toggle="tab"
                data-bs-target="#versions-{{ $index }}"
                type="button"
                role="tab"
                aria-controls="versions-{{ $index }}"
                aria-selected="false">
                <i class="ti ti-versions me-1"></i>Versions
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link"
                id="timeline-tab-{{ $index }}"
                data-bs-toggle="tab"
                data-bs-target="#timeline-{{ $index }}"
                type="button"
                role="tab"
                aria-controls="timeline-{{ $index }}"
                aria-selected="false">
                <i class="ti ti-timeline me-1"></i>Timeline
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link"
                id="kanban-tab-{{ $index }}"
                data-bs-toggle="tab"
                data-bs-target="#kanban-{{ $index }}"
                type="button"
                role="tab"
                aria-controls="Kanban-{{ $index }}"
                aria-selected="false">
                <i class=" me-1"></i>Kanban
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link"
                id="pic-tab-{{ $index }}"
                data-bs-toggle="tab"
                data-bs-target="#pic-{{ $index }}"
                type="button"
                role="tab"
                aria-controls="pic-{{ $index }}"
                aria-selected="false">
                <i class="ti ti-users me-1"></i>PIC
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link"
                id="impact-tab-{{ $index }}"
                data-bs-toggle="tab"
                data-bs-target="#impact-{{ $index }}"
                type="button"
                role="tab"
                aria-controls="impact-{{ $index }}"
                aria-selected="false">
                <i class="ti ti-chart-line me-1"></i>Dampak
            </button>
        </li>
    </ul>

    <div class="tab-content bg-white rounded-bottom shadow-sm" id="projectTabsContent-{{ $index }}">

        {{-- TAB DETAILS --}}
        <div class="tab-pane fade show active"
            id="overview-{{ $index }}"
            role="tabpanel"
            aria-labelledby="overview-tab-{{ $index }}">
            <div class="card border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-4 pb-4 border-bottom">
                        <div class="me-3">
                            @if($project->icon)
                            <img src="{{ asset('storage/icons/' . $project->icon) }}"
                                alt="icon" class="rounded"
                                style="width: 64px; height: 64px; object-fit: cover;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="bg-light rounded d-none align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="ti ti-folder fs-1 text-secondary"></i>
                            </div>
                            @else
                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                                <i class="ti ti-folder fs-1 text-secondary"></i>
                            </div>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <h4 class="mb-2 fw-bold">{{ $project->name }}</h4>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge bg-primary">
                                    <i class="ti ti-versions me-1"></i>
                                    v{{ $project->version ? $project->version->version : '1.0.0' }}
                                </span>

                                @if($project->version)
                                @if($project->version->status)
                                <span class="badge bg-success">
                                    <i class="ti ti-circle-check me-1"></i>Active
                                </span>
                                @else
                                <span class="badge bg-secondary">
                                    <i class="ti ti-circle-x me-1"></i>Inactive
                                </span>
                                @endif
                                @endif

                                @if($project->finishedAt)
                                <span class="badge bg-success">
                                    <i class="ti ti-check me-1"></i>Selesai
                                </span>
                                @else
                                <span class="badge bg-warning text-dark">
                                    <i class="ti ti-clock me-1"></i>Sedang Berjalan
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="ti ti-file-description me-2 text-primary"></i>Deskripsi Project
                        </h6>
                        <p class="text-muted">
                            {{ $project->description ?? 'Tidak ada deskripsi untuk project ini.' }}
                        </p>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold mb-3">
                            <i class="ti ti-info-circle me-2 text-primary"></i>Informasi Version
                        </h6>
                        <div class="border rounded p-3 bg-light">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1">Version Number</small>
                                    <strong class="d-block">{{ $project->version ? $project->version->version : '-' }}</strong>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted d-block mb-1">Status Version</small>
                                    <strong class="d-block">
                                        @if($project->version)
                                        @if($project->version->status)
                                        <span class="text-success">
                                            <i class="ti ti-circle-check me-1"></i>Active
                                        </span>
                                        @else
                                        <span class="text-secondary">
                                            <i class="ti ti-circle-x me-1"></i>Inactive
                                        </span>
                                        @endif
                                        @else
                                        -
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted d-block mb-2">
                                    <i class="ti ti-calendar-event me-1"></i>Tanggal Dibuat
                                </small>
                                <strong class="fs-6">
                                    {{ $project->createdAt ? \Carbon\Carbon::parse($project->createdAt)->format('d M Y') : '-' }}
                                </strong>
                            </div>
                        </div>

                        @if($project->finishedAt)
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100 border-success">
                                <small class="text-muted d-block mb-2">
                                    <i class="ti ti-calendar-event me-1"></i>Tanggal Selesai
                                </small>
                                <strong class="fs-6 text-success">
                                    {{ \Carbon\Carbon::parse($project->finishedAt)->format('d M Y') }}
                                </strong>
                            </div>
                        </div>
                        @endif

                        @if($project->createdAt)
                        <div class="col-md-4">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted d-block mb-2">
                                    <i class="ti ti-hourglass me-1"></i>Durasi Project
                                </small>
                                @if($project->finishedAt)
                                @php
                                $start = \Carbon\Carbon::parse($project->createdAt);
                                $end = \Carbon\Carbon::parse($project->finishedAt);
                                $duration = $start->diffInDays($end);
                                @endphp
                                <strong class="fs-6 text-success">{{ $duration }} hari</strong>
                                @else
                                @php
                                $start = \Carbon\Carbon::parse($project->createdAt);
                                $now = \Carbon\Carbon::now();
                                $duration = $start->diffInDays($now);
                                @endphp
                                <strong class="fs-6 text-warning">{{ $duration }} hari</strong>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB VERSIONS --}}
        <div class="tab-pane fade"
            id="versions-{{ $index }}"
            role="tabpanel"
            aria-labelledby="versions-tab-{{ $index }}">
            <div class="card border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">
                            <i class="ti ti-versions me-2 text-primary"></i>Version History
                        </h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addVersionModal{{ $project->id }}">
                            <i class="ti ti-plus me-1"></i>Add New Version
                        </button>
                    </div>

                    @if($project->versions && $project->versions->count() > 0)
                    <div class="timeline">
                        @foreach($project->versions as $versionItem)
                        <div class="mb-3 border rounded p-3 {{ $versionItem->status ? 'border-success bg-light' : '' }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <h6 class="mb-0 fw-bold">Version {{ $versionItem->version }}</h6>
                                        @if($versionItem->status)
                                        <span class="badge bg-success">
                                            <i class="ti ti-circle-check me-1"></i>Active
                                        </span>
                                        @else
                                        <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </div>

                                    @if($versionItem->description)
                                    <p class="text-muted mb-2 small">{{ $versionItem->description }}</p>
                                    @endif

                                    <div class="d-flex gap-3 small text-muted">
                                        <span>
                                            <i class="ti ti-calendar me-1"></i>
                                            Released: {{ $versionItem->created_at ? \Carbon\Carbon::parse($versionItem->created_at)->format('d M Y H:i') : '-' }}
                                        </span>
                                        <span>
                                            <i class="ti ti-clock me-1"></i>
                                            {{ $versionItem->created_at ? \Carbon\Carbon::parse($versionItem->created_at)->diffForHumans() : '-' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    {{-- Tombol Edit --}}
                                    <button class="btn btn-sm btn-outline-primary edit-version-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editVersionModal{{ $project->id }}_{{ $versionItem->id }}"
                                        title="Edit Version">
                                        <i class="ti ti-edit"></i>
                                    </button>

                                    {{-- Tombol Activate (hanya tampil jika inactive) --}}
                                    @if(!$versionItem->status)
                                    <button class="btn btn-sm btn-outline-success activate-version-btn"
                                        data-project-id="{{ $project->id }}"
                                        data-version-id="{{ $versionItem->id }}"
                                        data-version-name="{{ $versionItem->version }}"
                                        title="Activate Version">
                                        <i class="ti ti-check"></i>
                                    </button>
                                    @endif

                                    {{-- Tombol Delete (tidak bisa delete jika hanya ada 1 version) --}}
                                    @if($project->versions->count() > 1)
                                    <button class="btn btn-sm btn-outline-danger delete-version-btn"
                                        data-project-id="{{ $project->id }}"
                                        data-version-id="{{ $versionItem->id }}"
                                        data-version-name="{{ $versionItem->version }}"
                                        title="Delete Version">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Modal Edit Version untuk setiap version --}}
                        <div class="modal fade" id="editVersionModal{{ $project->id }}_{{ $versionItem->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">
                                            <i class="ti ti-edit me-2"></i>Edit Version {{ $versionItem->version }}
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="editVersionForm{{ $project->id }}_{{ $versionItem->id }}">
                                            @csrf
                                            @method('PUT')
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    Version Number <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="version" class="form-control"
                                                    value="{{ $versionItem->version }}"
                                                    placeholder="e.g., 2.0.0" required>
                                                <small class="text-muted">Format: major.minor.patch (e.g., 1.0.0, 2.1.3)</small>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Description</label>
                                                <textarea name="description" class="form-control" rows="3"
                                                    placeholder="What's new in this version?">{{ $versionItem->description }}</textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-bold">
                                                    Status <span class="text-danger">*</span>
                                                </label>
                                                <select name="status" class="form-select" required>
                                                    <option value="1" {{ $versionItem->status ? 'selected' : '' }}>Active</option>
                                                    <option value="0" {{ !$versionItem->status ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                                <small class="text-muted">Setting as active will deactivate all other versions.</small>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="ti ti-x me-1"></i>Cancel
                                        </button>
                                        <button type="button" class="btn btn-primary update-version-btn"
                                            data-project-id="{{ $project->id }}"
                                            data-version-id="{{ $versionItem->id }}">
                                            <i class="ti ti-device-floppy me-1"></i>Update Version
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Belum ada history version untuk project ini.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- TAB TIMELINE - Replace existing timeline tab content --}}
        <div class="tab-pane fade"
            id="timeline-{{ $index }}"
            role="tabpanel"
            aria-labelledby="timeline-tab-{{ $index }}">
            <div class="card border-0">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold mb-0">
                            <i class="ti ti-timeline me-2 text-primary"></i>Timeline & Gantt Chart
                        </h5>
                        <button class="btn btn-sm btn-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#addTimelineModal{{ $project->id }}">
                            <i class="ti ti-plus me-1"></i>Add Timeline
                        </button>
                    </div>

                    {{-- Overall Progress --}}
                    <div class="alert alert-info mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>
                                <i class="ti ti-progress me-2"></i>Overall Progress
                            </strong>
                            <span class="badge bg-primary">{{ $project->getOverallProgress() }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar" role="progressbar"
                                style="width: {{ $project->getOverallProgress() }}%"
                                aria-valuenow="{{ $project->getOverallProgress() }}"
                                aria-valuemin="0"
                                aria-valuemax="100">
                            </div>
                        </div>
                    </div>

                    {{-- Gantt Chart Container --}}
                    <div class="mb-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="ti ti-chart-gantt me-2"></i>Gantt Chart
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <div id="ganttChart{{ $project->id }}" style="min-height: 400px;">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="text-muted mt-2">Loading Gantt Chart...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Timeline List --}}
                    <h6 class="fw-bold mb-3">
                        <i class="ti ti-list me-2"></i>Timeline Details
                    </h6>

                    {{-- Plan Awal --}}
                    <div class="mb-3 border rounded p-3 bg-light">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h6 class="mb-0 fw-bold">
                                        <i class="ti ti-flag text-info me-1"></i>
                                        Plan Awal: {{ $project->name }}
                                    </h6>
                                    <span class="badge bg-info">Plan</span>
                                </div>
                                <div class="d-flex gap-3 small text-muted">
                                    <span>
                                        <i class="ti ti-calendar me-1"></i>
                                        Start: {{ $project->createdAt ? \Carbon\Carbon::parse($project->createdAt)->format('d M Y') : '-' }}
                                    </span>
                                    <span>
                                        <i class="ti ti-calendar-check me-1"></i>
                                        End: {{ $project->finishedAt ? \Carbon\Carbon::parse($project->finishedAt)->format('d M Y') : 'Ongoing' }}
                                    </span>
                                    @if($project->createdAt && $project->finishedAt)
                                    <span>
                                        <i class="ti ti-hourglass me-1"></i>
                                        Duration: {{ \Carbon\Carbon::parse($project->createdAt)->diffInDays(\Carbon\Carbon::parse($project->finishedAt)) }} days
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actual Timelines --}}
                    @if($project->actualTimelines && $project->actualTimelines->count() > 0)
                    @foreach($project->actualTimelines as $timeline)
                    <div class="mb-3 border rounded p-3 position-relative">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <h6 class="mb-0 fw-bold">{{ $timeline->title }}</h6>
                                    <span class="badge bg-{{ $timeline->getStatusColor() }}">
                                        {{ $timeline->progress }}%
                                    </span>
                                    @if($timeline->isOverdue())
                                    <span class="badge bg-danger">
                                        <i class="ti ti-alert-triangle me-1"></i>Overdue
                                    </span>
                                    @endif
                                </div>

                                @if($timeline->description)
                                <p class="text-muted mb-2 small">{{ $timeline->description }}</p>
                                @endif

                                <div class="d-flex gap-3 small text-muted mb-2">
                                    <span>
                                        <i class="ti ti-calendar me-1"></i>
                                        {{ optional($timeline->start_date)->format('d M Y') ?? '-' }}
                                    </span>
                                    <span>
                                        <i class="ti ti-calendar-check me-1"></i>
                                        {{ optional($timeline->end_date)->format('d M Y') ?? '-' }}
                                    </span>
                                    <span>
                                        <i class="ti ti-hourglass me-1"></i>
                                        {{ $timeline->getDurationInDays() }} days
                                    </span>
                                </div>

                                {{-- Progress Bar --}}
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-{{ $timeline->getStatusColor() }}"
                                        role="progressbar"
                                        style="width: {{ $timeline->progress }}%"
                                        aria-valuenow="{{ $timeline->progress }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 ms-3">
                                <button class="btn btn-sm btn-outline-primary edit-timeline-btn"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editTimelineModal{{ $project->id }}_{{ $timeline->id }}"
                                    title="Edit Timeline">
                                    <i class="ti ti-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-timeline-btn"
                                    data-project-id="{{ $project->id }}"
                                    data-timeline-id="{{ $timeline->id }}"
                                    data-timeline-title="{{ $timeline->title }}"
                                    title="Delete Timeline">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Edit Timeline --}}
                    <div class="modal fade" id="editTimelineModal{{ $project->id }}_{{ $timeline->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title">
                                        <i class="ti ti-edit me-2"></i>Edit Timeline
                                    </h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="editTimelineForm{{ $project->id }}_{{ $timeline->id }}">
                                        @csrf
                                        @method('PUT')
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Title <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="title" class="form-control"
                                                value="{{ $timeline->title }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Description</label>
                                            <textarea name="description" class="form-control" rows="3">{{ $timeline->description }}</textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">
                                                    Start Date <span class="text-danger">*</span>
                                                </label>
                                                <input type="date"
                                                    name="start_date"
                                                    class="form-control"
                                                    value="{{ optional($timeline->start_date)->format('Y-m-d') }}"
                                                    required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label fw-bold">
                                                    End Date <span class="text-danger">*</span>
                                                </label>
                                                <input type="date"
                                                    name="end_date"
                                                    class="form-control"
                                                    value="{{ optional($timeline->end_date)->format('Y-m-d') }}"
                                                    required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                Progress <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" name="progress" class="form-control"
                                                    value="{{ $timeline->progress }}" min="0" max="100" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                            <input type="range" class="form-range mt-2" min="0" max="100"
                                                value="{{ $timeline->progress }}"
                                                id="progressRange{{ $timeline->id }}"
                                                oninput="document.querySelector('#editTimelineForm{{ $project->id }}_{{ $timeline->id }} input[name=progress]').value = this.value">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="ti ti-x me-1"></i>Cancel
                                    </button>
                                    <button type="button" class="btn btn-primary update-timeline-btn"
                                        data-project-id="{{ $project->id }}"
                                        data-timeline-id="{{ $timeline->id }}">
                                        <i class="ti ti-device-floppy me-1"></i>Update
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @else
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Belum ada timeline actual. Klik tombol "Add Timeline" untuk menambahkan.
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Modal Add Timeline --}}
        <div class="modal fade" id="addTimelineModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="ti ti-plus me-2"></i>Add New Timeline
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addTimelineForm{{ $project->id }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Title <span class="text-danger">*</span>
                                </label>
                                <input type="hidden" name="projectId" value="{{ $project->id }}">
                                <input type="text" name="title" class="form-control"
                                    placeholder="e.g., Development Phase 1" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Description</label>
                                <textarea name="description" class="form-control" rows="3"
                                    placeholder="Brief description of this timeline phase"></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">
                                        Start Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="start_date" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">
                                        End Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="end_date" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Progress <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <input type="number" name="progress" class="form-control"
                                        value="0" min="0" max="100" required id="progressInput{{ $project->id }}">
                                    <span class="input-group-text">%</span>
                                </div>
                                <input type="range" class="form-range mt-2" min="0" max="100" value="0"
                                    oninput="document.getElementById('progressInput{{ $project->id }}').value = this.value">
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="ti ti-x me-1"></i>Cancel
                        </button>
                        <button type="button" class="btn btn-primary submit-timeline-btn" data-project-id="{{ $project->id }}">
                            <i class="ti ti-device-floppy me-1"></i>Save Timeline
                        </button>
                    </div>
                </div>
            </div>
        </div>
        {{-- TAB KANBAN --}}
        <div class="tab-pane fade"
            id="kanban-{{ $index }}"
            role="tabpanel"
            aria-labelledby="kanban-tab-{{ $index }}">

            @include('project-mgt.kanban.index', ['project' => $project])

        </div>
        {{-- TAB PIC --}}
        <div class="tab-pane fade"
            id="pic-{{ $index }}"
            role="tabpanel"
            aria-labelledby="pic-tab-{{ $index }}">
            <div class="card border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="ti ti-users me-2 text-primary"></i>Person In Charge (PIC)
                    </h5>

                    @if($project->picType === 'individual')
                    @php
                    $picUser = \App\Models\User::find($project->picId);
                    @endphp
                    @if($picUser)
                    <div class="alert alert-light border mb-3">
                        <small class="text-muted">Tipe PIC: Individual</small>
                    </div>
                    <div class="d-flex align-items-center p-3 border rounded bg-light">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                            style="width: 50px; height: 50px; font-size: 20px;">
                            <strong>{{ strtoupper(substr($picUser->name, 0, 1)) }}</strong>
                        </div>
                        <div>
                            <strong class="d-block">{{ $picUser->name }}</strong>
                            <small class="text-muted d-block">
                                <i class="ti ti-mail me-1"></i>{{ $picUser->email }}
                            </small>
                            @if($picUser->jobTtlName)
                            <small class="text-muted d-block">
                                <i class="ti ti-briefcase me-1"></i>{{ $picUser->jobTtlName }}
                            </small>
                            @endif
                        </div>
                    </div>
                    @else
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>Data PIC tidak ditemukan.
                    </div>
                    @endif
                    @else
                    @php
                    $picGroup = \App\Models\Groups::find($project->picId);
                    $members = DB::table('group_members')
                    ->join('users', 'group_members.user_id', '=', 'users.id')
                    ->where('group_members.group_id', $project->picId)
                    ->select('users.*')
                    ->get();
                    @endphp
                    @if($picGroup)
                    <div class="alert alert-light border mb-4">
                        <small class="text-muted d-block mb-1">Tipe PIC: Group</small>
                        <h6 class="mb-0">
                            <i class="ti ti-users-group me-2 text-primary"></i>
                            {{ $picGroup->name }}
                        </h6>
                    </div>

                    @if($members->count() > 0)
                    <h6 class="mb-3">Anggota Group ({{ $members->count() }} orang):</h6>
                    <div class="row g-3">
                        @foreach($members as $member)
                        <div class="col-md-6">
                            <div class="d-flex align-items-center p-3 border rounded">
                                <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                                    style="width: 45px; height: 45px;">
                                    <strong>{{ strtoupper(substr($member->name, 0, 1)) }}</strong>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <strong class="d-block text-truncate">{{ $member->name }}</strong>
                                    <small class="text-muted d-block text-truncate">
                                        <i class="ti ti-mail me-1"></i>{{ $member->email }}
                                    </small>
                                    @if($member->jobTtlName)
                                    <small class="text-muted d-block text-truncate">
                                        <i class="ti ti-briefcase me-1"></i>{{ $member->jobTtlName }}
                                    </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>Belum ada anggota dalam group ini.
                    </div>
                    @endif
                    @else
                    <div class="alert alert-warning">
                        <i class="ti ti-alert-triangle me-2"></i>Data group tidak ditemukan.
                    </div>
                    @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- TAB DAMPAK --}}
        <div class="tab-pane fade"
            id="impact-{{ $index }}"
            role="tabpanel"
            aria-labelledby="impact-tab-{{ $index }}">
            <div class="card border-0">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="ti ti-chart-line me-2 text-primary"></i>Dampak Project
                    </h5>

                    @if($project->dampak)
                    <div class="bg-light rounded p-4">
                        <p class="mb-0" style="white-space: pre-line;">{{ $project->dampak }}</p>
                    </div>
                    @else
                    <div class="alert alert-info">
                        <i class="ti ti-info-circle me-2"></i>
                        Belum ada informasi dampak untuk project ini.
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>

{{-- Modal Add New Version --}}
<div class="modal fade" id="addVersionModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="ti ti-plus me-2"></i>Add New Version
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addVersionForm{{ $project->id }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Version Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="version" class="form-control"
                            placeholder="e.g., 2.0.0" required>
                        <small class="text-muted">Format: major.minor.patch (e.g., 1.0.0, 2.1.3)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="3"
                            placeholder="What's new in this version?"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Status <span class="text-danger">*</span>
                        </label>
                        <select name="status" class="form-select" required>
                            <option value="1">Active (will deactivate other versions)</option>
                            <option value="0">Inactive</option>
                        </select>
                        <small class="text-muted">Setting a version as active will automatically deactivate all other versions.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary submit-version-btn" data-project-id="{{ $project->id }}">
                    <i class="ti ti-device-floppy me-1"></i>Save Version
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endif