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
                    <li class="breadcrumb-item" aria-current="page">View</li>
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
                <small>View employee data in workspace.</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- First column -->
                    <div class="col-md-4">
                        <form class="form-custom">
                            <div class="mb-3">
                                <label class="form-label">Site</label>
                                <p><b>{{ $employee->compName }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">NIK</label>
                                <p><b>{{ $employee->nik }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <p><b>{{ $employee->name }}</b></p>
                            </div>
                            <div class="mb-3 d-none d-sm-block">
                                <a href="{{ url('/masterdata/employee') }}" class="btn btn-info btn-sm rounded-2">Back</a>
                            </div>
                        </form>
                    </div>

                    <!-- Second column -->
                    <div class="col-md-4">
                        <form class="form-custom">
                            <div class="mb-3">
                                <label class="form-label">UPN</label>
                                <p><b>{{ $employee->userPrincipalName }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Div</label>
                                <p><b>{{ $employee->divName }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Job Level</label>
                                <p><b>{{ $employee->jobLvlName }}</b></p>
                            </div>
                        </form>
                    </div>

                    <!-- Third column -->
                    <div class="col-md-4">
                        <form class="form-custom">
                            <div class="mb-3">
                                <label class="form-label">Job Title</label>
                                <p><b>{{ $employee->jobTtlName }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <p>
                                    @if($employee->roles && $employee->roles->count() > 0)
                                    @foreach($employee->roles as $role)
                                    <span class="badge bg-primary py-1 me-1">{{ $role->name }}</span>
                                    @endforeach
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </p>
                            </div>
                        </form>
                    </div>

                    <!-- Back button for smaller screens -->
                    <div class="col-12">
                        <form class="form-custom">
                            <div class="mb-3 d-block d-sm-none">
                                <a href="{{ url('/masterdata/employee') }}" class="btn btn-info btn-sm rounded-2">Back</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection