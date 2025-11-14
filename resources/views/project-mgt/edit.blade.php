@extends('layout.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="ti ti-edit me-2"></i>Edit Project
                    </h5>
                    <a href="{{ route('projects.index') }}" class="btn btn-light btn-sm"> <- Kembali</a>
                </div>
                <div class="card-body p-4">

                    @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Error!</strong>
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    @endif

                    <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        {{-- Project Name --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Nama Project <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="name" class="form-control"
                                value="{{ old('name', $project->name) }}" required>
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Deskripsi</label>
                            <textarea name="description" class="form-control" rows="4">{{ old('description', $project->description) }}</textarea>
                        </div>

                        {{-- Dates --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Tanggal Dibuat <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="createdAt" class="form-control"
                                    value="{{ old('createdAt', $project->createdAt ? \Carbon\Carbon::parse($project->createdAt)->format('Y-m-d') : '') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Tanggal Selesai</label>
                                <input type="date" name="finishedAt" class="form-control"
                                    value="{{ old('finishedAt', $project->finishedAt ? \Carbon\Carbon::parse($project->finishedAt)->format('Y-m-d') : '') }}">
                            </div>
                        </div>

                        {{-- Version & Status --}}
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">
                                    Version <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="version" class="form-control"
                                    value="{{ old('version', $project->version ? $project->version->version : '1.0.0') }}"
                                    placeholder="e.g., 1.0.0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Status Version</label>
                                @php
                                // Konversi boolean status ke integer untuk dropdown
                                $currentStatus = 0; // default inactive
                                if ($project->version && $project->version->status === true) {
                                $currentStatus = 1; // active
                                }
                                // Prioritaskan old input jika ada error validasi
                                $selectedStatus = old('status', $currentStatus);
                                @endphp
                                <select name="status" class="form-select">
                                    <option value="0" {{ $selectedStatus == '0' || $selectedStatus == 0 ? 'selected' : '' }}>
                                        Inactive
                                    </option>
                                    <option value="1" {{ $selectedStatus == '1' || $selectedStatus == 1 ? 'selected' : '' }}>
                                        Active
                                    </option>
                                </select>
                                <small class="text-muted">Status ini akan mempengaruhi tampilan di project list</small>
                            </div>
                        </div>
                        {{-- Icon Upload --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Icon Project</label>

                            @if($project->icon)
                            <div class="mb-3 d-flex align-items-center gap-3">
                                <img src="{{ asset('storage/icons/' . $project->icon) }}"
                                    alt="Current Icon" class="border rounded"
                                    style="width: 80px; height: 80px; object-fit: cover;">
                                <div>
                                    <p class="mb-2 text-muted small">Icon saat ini</p>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                            id="removeIcon" name="remove_icon" value="1">
                                        <label class="form-check-label" for="removeIcon">
                                            Hapus icon
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @endif

                            <input type="file" name="icon" class="form-control" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, SVG (Max: 2MB)</small>
                        </div>

                        {{-- PIC Type --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">
                                Tipe PIC <span class="text-danger">*</span>
                            </label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="picType" id="picIndividual"
                                    value="individual" {{ old('picType', $project->picType) == 'individual' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="picIndividual">
                                    <i class="ti ti-user me-1"></i>Individual
                                </label>

                                <input type="radio" class="btn-check" name="picType" id="picGroup"
                                    value="group" {{ old('picType', $project->picType) == 'group' ? 'checked' : '' }}>
                                <label class="btn btn-outline-primary" for="picGroup">
                                    <i class="ti ti-users me-1"></i>Group
                                </label>
                            </div>
                        </div>

                        {{-- Individual PIC --}}
                        <div id="individualPicSection" class="mb-3" style="display: {{ old('picType', $project->picType) == 'individual' ? 'block' : 'none' }};">
                            <label class="form-label fw-bold">
                                Pilih User <span class="text-danger">*</span>
                            </label>
                            <select name="picUser" class="form-select">
                                <option value="">-- Pilih User --</option>
                                @foreach($users as $user)
                                <option value="{{ $user->id }}"
                                    {{ old('picUser', $project->picType == 'individual' ? $project->picId : '') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }} ({{ $user->email }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Group PIC --}}
                        <div id="groupPicSection" style="display: {{ old('picType', $project->picType) == 'group' ? 'block' : 'none' }};">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    Nama Group <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="groupName" class="form-control"
                                    value="{{ old('groupName', $project->groupName ?? '') }}"
                                    placeholder="e.g., Tim Development">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Pilih Anggota Group</label>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    @foreach($users as $user)
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox"
                                            name="groupMembers[]" value="{{ $user->id }}"
                                            id="user{{ $user->id }}"
                                            {{ in_array($user->id, old('groupMembers', $groupMembers)) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="user{{ $user->id }}">
                                            <strong>{{ $user->name }}</strong>
                                            <small class="text-muted d-block">{{ $user->email }}</small>
                                        </label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Dampak --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold">Dampak Project</label>
                            <textarea name="dampak" class="form-control" rows="4"
                                placeholder="Jelaskan dampak dari project ini...">{{ old('dampak', $project->dampak) }}</textarea>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex gap-2 justify-content-end border-top pt-3">
                            <a href="{{ route('projects.index') }}" class="btn btn-secondary">
                                <i class="ti ti-x me-1"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="ti ti-device-floppy me-1"></i>Update Project
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const picIndividual = document.getElementById('picIndividual');
        const picGroup = document.getElementById('picGroup');
        const individualSection = document.getElementById('individualPicSection');
        const groupSection = document.getElementById('groupPicSection');

        function togglePicSections() {
            if (picIndividual.checked) {
                individualSection.style.display = 'block';
                groupSection.style.display = 'none';
            } else {
                individualSection.style.display = 'none';
                groupSection.style.display = 'block';
            }
        }

        picIndividual.addEventListener('change', togglePicSections);
        picGroup.addEventListener('change', togglePicSections);
    });
</script>
@endsection