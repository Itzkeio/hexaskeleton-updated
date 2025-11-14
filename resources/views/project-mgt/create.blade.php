@extends('layout.app')

@section('content')
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Tambah Project Baru</h4>
            <a href="{{ route('projects.index') }}" class="btn btn-light btn-sm"> <- Kembali</a>
        </div>
        <div class="card-body">

            @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Project Name</label>
                    <input type="text" class="form-control" name="name" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="2"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Created At</label>
                        <input type="date" class="form-control" name="createdAt" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Finished At (optional)</label>
                        <input type="date" class="form-control" name="finishedAt">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Dampak</label>
                    <textarea class="form-control" name="dampak" rows="2"></textarea>
                </div>

                {{-- Version --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">
                        Version <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="version" class="form-control"
                        value="{{ old('version', '1.0.0') }}"
                        placeholder="e.g., 1.0.0" required>
                </div>

                {{-- Status Checkbox --}}
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="status_active" name="status_active" value="1"
                        {{ old('status_active', true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="status_active">Status Aktif</label>
                    <small class="d-block text-muted">Centang jika project sedang aktif</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">PIC Type</label>
                    <select class="form-select" id="picType" name="picType" required>
                        <option value="">-- Pilih Tipe PIC --</option>
                        <option value="individual" {{ old('picType')=='individual'?'selected':'' }}>Individual</option>
                        <option value="group" {{ old('picType')=='group'?'selected':'' }}>Group</option>
                    </select>
                </div>

                {{-- Individual --}}
                <div class="mb-3" id="individualBox" style="display:none;">
                    <label class="form-label">Pilih User</label>
                    <select class="form-select" name="picUser">
                        <option value="">-- Pilih User --</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('picUser')==$user->id?'selected':'' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Group --}}
                <div id="groupBox" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label">Nama Group</label>
                        <input type="text" class="form-control" name="groupName" value="{{ old('groupName') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Anggota Group</label>
                        <div class="border rounded p-2">
                            @foreach($users as $user)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="groupMembers[]" value="{{ $user->id }}" id="user{{ $user->id }}"
                                    {{ is_array(old('groupMembers')) && in_array($user->id, old('groupMembers')) ? 'checked' : '' }}>
                                <label class="form-check-label" for="user{{ $user->id }}">{{ $user->name }}</label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Icon Project(Logo)</label>
                    <input type="file" class="form-control" name="icon" accept="image/*">
                </div>

                <button type="submit" class="btn btn-success mt-3">Simpan Project</button>
            </form>
        </div>
    </div>
</div>

<script>
    const picTypeSelect = document.getElementById('picType');
    const individualBox = document.getElementById('individualBox');
    const groupBox = document.getElementById('groupBox');

    function togglePicBox() {
        if (picTypeSelect.value === 'individual') {
            individualBox.style.display = 'block';
            groupBox.style.display = 'none';
        } else if (picTypeSelect.value === 'group') {
            individualBox.style.display = 'none';
            groupBox.style.display = 'block';
        } else {
            individualBox.style.display = 'none';
            groupBox.style.display = 'none';
        }
    }

    // Jalankan saat load page
    togglePicBox();
    picTypeSelect.addEventListener('change', togglePicBox);
</script>
@endsection