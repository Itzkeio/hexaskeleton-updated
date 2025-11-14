@extends('layout.app')

@section('title', 'Audit Trail')

@section('content')
<div class="page-header px-1">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Log</li>
                    <li class="breadcrumb-item active" aria-current="page">Audit Trail</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Log</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Managament Log</h5>
                <small>List of logs in this workspace.</small>
            </div>
            <div class="card-body">
                <table class="table log-tbl table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Comp Name</th>
                            <th>User</th>
                            <th>Activity</th>
                            <th>Description</th>
                            <th>Time</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><input type="text" placeholder="Comp Name" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="User" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Activity" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Description" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" class="form-control-sm column-search datepicker form-control" placeholder="Time" style="width: 100%;" /></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Data akan diisi oleh datatable AJAX --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        var datatable = $('.log-tbl').DataTable({
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
                url: "{{ route('logs.datatable') }}",
                type: "GET",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1; // This will render the auto-increment index
                    },
                    orderable: false,
                },
                {
                    data: "compName",
                    name: "compName"
                },
                {
                    data: "username",
                    name: "username"
                },
                {
                    data: "activity",
                    name: "activity"
                },
                {
                    data: "description",
                    name: "description"
                },
                {
                    data: "createdAt",
                    name: "createdAt",
                    render: function(data, type, row) {
                        return moment(data).format('DD MMM YYYY HH:mm:ss');
                    }
                }
            ],
        });

        // Column search
        $('.log-tbl thead').on('keyup', '.column-search', function(event) {
            event.preventDefault();

            // Number 13 is the "Enter" key on the keyboard
            if (event.keyCode === 13) {
                var keyword = this.value;
                var columnIndex = $(this).parent().index(); // Get the column index

                //if (this.placeholder === 'Search Published') {
                //    keyword = keyword.toUpperCase();
                //    if (keyword === 'TRUE' || keyword === 'YA' || keyword === 'YES' || keyword === 'Y' || keyword === '1') {
                //        keyword = 1;
                //    } else {
                //        keyword = 0;
                //    }
                //}

                datatable
                    .column(columnIndex)
                    .search(keyword)
                    .draw();
            }
        });

        $('.log-tbl thead').on('changeDate', '.column-search.datepicker', function(event) {
            event.preventDefault();

            var keyword = this.value;
            var columnIndex = $(this).parent().index();

            // Convert to Y-m-d if moment is available
            if (moment(keyword, 'DD MMMM YYYY', true).isValid()) {
                keyword = moment(keyword, 'DD MMMM YYYY').format('YYYY-MM-DD');
            }

            datatable
                .column(columnIndex)
                .search(keyword)
                .draw();
        });


        $('.log-tbl thead').on('keyup', '.column-search', function() {
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

        resizeHandler($('.log-tbl'));
    });
</script>
@endsection