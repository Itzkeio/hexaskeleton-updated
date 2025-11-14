@extends('layout.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header px-1">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item"><a href="{{ url('/masterdata/employee') }}">Employee</a></li>
                    <li class="breadcrumb-item" aria-current="page">Assign Role</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Master Data</h2>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- [ breadcrumb ] end -->

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Management Employee</h5>
                <small>Update employee data in WorkSpace</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- First Column -->
                    <div class="col-md-4">
                        <div class="form-custom">
                            <input type="hidden" id="user-id" value="{{ $user->id }}" />
                            <input type="hidden" id="name" value="{{ $user->name }}" />
                            <div class="mb-3">
                                <label class="form-label">Site</label>
                                <p><b>{{ $user->compName }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">NIK</label>
                                <p><b>{{ $user->nik }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <p><b>{{ $user->name }}</b></p>
                            </div>
                        </div>
                    </div>

                    <!-- Second Column -->
                    <div class="col-md-4">
                        <div class="form-custom">
                            <div class="mb-3">
                                <label class="form-label">UPN</label>
                                <p><b>{{ $user->userPrincipalName }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Div</label>
                                <p><b>{{ $user->divName }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Job Level</label>
                                <p><b>{{ $user->jobLvlName }}</b></p>
                            </div>
                        </div>
                    </div>

                    <!-- Third Column -->
                    <div class="col-md-4">
                        <div class="form-custom">
                            <div class="mb-3">
                                <label class="form-label">Job Title</label>
                                <p><b>{{ $user->jobTtlName }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Roles</label>
                                <select class="form-control select2" name="SelectedRoleIds[]" multiple="multiple" style="width: 100%;" id="role">
                                    @foreach($availableRoles as $role)
                                    <option value="{{ $role->id }}" {{ in_array($role->id, $selectedRoleIds) ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="col-12">
                        <div class="form-custom">
                            <div class="mb-3">
                                <form class="form-custom" onsubmit="return false;">
                                    <a href="{{ url('/masterdata/employee') }}" class="btn btn-danger btn-sm rounded-2">Cancel</a>
                                    <button type="button" class="btn btn-primary btn-sm rounded-2" id="save-btn">Submit</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select one or more roles"
        });

        $('#save-btn').on('click', function() {
            $('#save-btn').prop('disabled', true);

            const selectedRoles = $('#role').val(); // array of selected role IDs
            const userId = $('#user-id').val();
            const name = $('#name').val();

            $.ajax({
                url: "{{ route('employee.update', ':id') }}".replace(':id', userId),
                type: 'PUT',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    userId: userId,
                    name: name,
                    selectedRoleIds: selectedRoles
                }),
                success: function(resp) {
                    if (resp.status == 200) {
                        Swal.fire({
                            title: 'Good',
                            text: resp.message,
                            icon: 'success',
                        }).then(function() {
                            window.location.href = '{{ url("/masterdata/employee") }}'
                        });
                    } else {
                        Swal.fire({
                            title: 'Oops',
                            text: resp.message,
                            icon: 'error',
                        });
                    }
                    $('#save-btn').prop('disabled', false);
                },
                error: function(xhr) {
                    if (xhr.status === 400) {
                        const errors = xhr.responseJSON;
                        for (let key in errors) {
                            const errorMessage = errors[key].join('<br>');
                            $('#' + key.toLowerCase()).addClass('is-invalid').siblings('.invalid-feedback').html(errorMessage);
                        }
                    } else if (xhr.status === 401) {
                        // Unauthorized
                        Swal.fire({
                            title: 'Oops',
                            text: xhr.responseJSON.message || 'You do not have permission to perform this action.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        $('#role').addClass('is-invalid').siblings('.invalid-feedback').html(xhr.responseText.replace(/"/g, ''));
                    }
                    $('#save-btn').prop('disabled', false);
                    console.log(xhr.status, xhr.responseText);
                }
            });
        });
    });
</script>
@endsection