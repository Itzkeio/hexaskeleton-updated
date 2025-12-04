@extends('layout.app')

@section('title', 'Kanban Logs')

@section('content')
<div class="page-header px-1">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Log</li>
                    <li class="breadcrumb-item active" aria-current="page">Kanban Activity</li>
                </ul>
            </div>
            <div class="col-md-12">
                <div class="page-header-title">
                    <h2 class="mb-0">Kanban Logs</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Kanban Activity Log</h5>
                <small>List of Kanban actions performed in this workspace.</small>
            </div>
            <div class="card-body">
                <table class="table log-tbl table-striped table-bordered w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Description</th>
                            <th>Time</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><input type="text" placeholder="User" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Action" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Entity" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Description" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Time" class="form-control-sm column-search datepicker form-control" style="width: 100%;" /></th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- DataTables AJAX --}}
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
            scrollX: true,
            sort: false,
            order: [],
            dom: 'Bfrtip',
            buttons: [{
                text: '<span class="fa fa-list"></span> Show',
                className: 'btn btn-sm btn-dt poppins-medium',
                extend: 'pageLength',
            }],
            ajax: {
                url: "{{ route('kanban.logs.datatable', $project->id) }}",
                type: "GET",
                dataSrc: function(json) {
                    return json.data;
                }
            },

            columns: [{
                    data: null,
                    render: function(data, type, row, meta) {
                        return meta.row + 1;
                    },
                    orderable: false,
                },
                {
                    data: "username",
                    name: "username"
                },
                {
                    data: "action",
                    name: "action"
                },
                {
                    data: "entity_type",
                    name: "entity_type"
                },
                {
                    data: "description",
                    name: "description"
                },
                {
                    data: "created_at",
                    name: "created_at",
                    render: function(data) {
                        return moment(data).format('DD MMM YYYY HH:mm:ss');
                    }
                }
            ]
        });

        // Column Search Enter Key
        $('.log-tbl thead').on('keyup', '.column-search', function(event) {
            if (event.keyCode === 13) {
                let keyword = this.value;
                let colIndex = $(this).parent().index();

                datatable
                    .column(colIndex)
                    .search(keyword)
                    .draw();
            }
        });

        // Datepicker Search
        $('.log-tbl thead').on('changeDate', '.column-search.datepicker', function() {
            let keyword = this.value;
            let colIndex = $(this).parent().index();

            if (moment(keyword, 'DD MMMM YYYY', true).isValid()) {
                keyword = moment(keyword, 'DD MMMM YYYY').format('YYYY-MM-DD');
            }

            datatable
                .column(colIndex)
                .search(keyword)
                .draw();
        });

        // Reset search when all empty
        $('.log-tbl thead').on('keyup', '.column-search', function() {
            let empty = true;

            $("input.column-search").each(function() {
                if ($(this).val().trim() !== '') {
                    empty = false;
                    return false;
                }
            });

            if (empty) {
                $("input.column-search").val('');
                datatable.columns().search('').draw();
            }
        });

        resizeHandler($('.log-tbl'));
    });
</script>
@endsection