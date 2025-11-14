@extends('layout.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header px-1">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item" aria-current="page">Employee</li>
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
                <h5>Managament Employee</h5>
                <small>List of employees in this workspace.</small>
            </div>
            <div class="card-body">
                <table class="table emp-tbl table-bordered table-stripped hover w-100 poppins-regular custom-tbl emp-tbl" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-end">Action</th>
                            <th class="text-end">Site</th>
                            <th class="text-end">NIK</th>
                            <th class="text-end">Name</th>
                            <th class="text-end">Div</th>
                            <th class="text-end">Job Level</th>
                            <th class="text-end">Job Title</th>
                            <th class="text-end">Role</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><input type="text" placeholder="Site" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="NIK" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Name" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Div" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Job Level" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Job Title" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Role" class="form-control form-control-sm column-search" /></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        var datatable = $('.emp-tbl').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            filter: true,
            orderMulti: false,
            orderCellsTop: true,
            sort: false,
            scrollX: true,
            order: [],
            search: {
                return: true
            },
            dom: 'Bfrtip',
            buttons: [{
                text: '<span class="fa fa-list"></span> Show',
                className: 'btn btn-sm btn-dt poppins-medium',
                extend: 'pageLength',
            }],
            language: {
                processing: `
            <div class="d-flex justify-content-center">
                <div class="spinner-border spinner-border-sm m-1" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>`,
                emptyTable: "No data available in table"
            },
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'All']
            ],
            initComplete: function(settings, json) {
                var api = this.api();
                api.columns().search('').draw();
            },
            ajax: {
                url: "{{ route('employee.datatable') }}",
                type: "GET",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [{
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-end',
                    render: function(data) {
                        return data; // render HTML dropdown menu from controller
                    }
                },
                {
                    data: "compName",
                    name: "compName",
                    className: "text-end"
                },
                {
                    data: "nik",
                    name: "nik",
                    className: "text-end"
                },
                {
                    data: "name",
                    name: "name",
                    className: "text-end"
                },
                {
                    data: "divName",
                    name: "divName",
                    className: "text-end"
                },
                {
                    data: "jobLvlName",
                    name: "jobLvlName",
                    className: "text-end"
                },
                {
                    data: "jobTtlName",
                    name: "jobTtlName",
                    className: "text-end"
                },
                {
                    data: "roleName",
                    name: "roleName",
                    className: "text-end"
                }
            ]
        });
        // Column search
        $('.emp-tbl thead').on('keyup', '.column-search', function(event) {
            event.preventDefault();

            // Number 13 is the "Enter" key on the keyboard
            if (event.keyCode === 13) {
                var keyword = this.value;
                var columnIndex = $(this).parent().index(); // Get the column index


                datatable
                    .column(columnIndex)
                    .search(keyword)
                    .draw();
            }
        });

        $('.emp-tbl thead').on('keyup', '.column-search', function() {
            var allInputsEmpty = true;

            // Check if all column search inputs are empty
            $('input.column-search').each(function() {
                if ($(this).val().trim() !== '') {
                    allInputsEmpty = false;
                    return false; // Exit the loop early
                }
            });

            if (allInputsEmpty) {
                $('input.column-search').val(''); // Clear all input values
                datatable.columns().search('').draw(); // Clear all column searches and redraw
            }
        });

        resizeHandler($('.emp-tbl'));

    });
</script>
@endsection