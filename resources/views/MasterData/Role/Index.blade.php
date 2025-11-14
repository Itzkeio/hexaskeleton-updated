@extends('layout.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header px-1">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a>Master Data</a></li>
                    <li class="breadcrumb-item" aria-current="page">Role</li>
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
                <h5>Managament Role</h5>
                <small>List of roles in this workspace.</small>
            </div>
            <div class="card-body">
                <table class="table table-bordered hover w-100 custom-tbl role-tbl" cellspacing="0">
                    <thead>
                        <tr>
                            <th class="text-end">Action</th>
                            <th class="text-end">Site</th>
                            <th class="text-end">Name</th>
                            <th class="text-end">Description</th>
                        </tr>
                        <tr>
                            <th></th>
                            <th><input type="text" placeholder="Site" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Name" class="form-control form-control-sm column-search" /></th>
                            <th><input type="text" placeholder="Description" class="form-control form-control-sm column-search" /></th>
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
        var datatable = $('.role-tbl').DataTable({
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
                text: '<span class="fa fa-plus"></span> Create',
                className: 'btn btn-sm btn-dt poppins-medium',
                action: function(e, dt, node, config) {
                    window.location.href = '/masterdata/role/create';
                }
            }, {
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
                url: "{{ route('role.datatable') }}",
                type: "GET",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                dataSrc: function(json) {
                    return json.data;
                }
            },
            columns: [{
                    data: "action",
                    orderable: false,
                    searchable: false
                },
                {
                    data: "compName",
                    name: "compName"
                },
                {
                    data: "name",
                    name: "name"
                },
                {
                    data: "description",
                    name: "description"
                }
            ],
        });

        // Column search
        $('.role-tbl thead').on('keyup', '.column-search', function(event) {
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

        $('.role-tbl thead').on('keyup', '.column-search', function() {
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

        resizeHandler($('.role-tbl'));

        // Handle delete button click
        $('.role-tbl').on('click', '.delete-btn', function() {
            var roleId = $(this).data('id');
            // Call the delete action using AJAX
            Swal.fire({
                title: 'Are you sure?',
                icon: 'warning',
                text: 'You won\'t be able to revert this!',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('role.destroy', ':id') }}".replace(':id', roleId),
                        type: 'DELETE',
                        dataType: 'json',
                        success: function(resp) {
                            // Handle success, such as displaying a success message
                            if (resp.status == 200) {
                                Swal.fire({
                                    title: 'Good!',
                                    icon: 'success',
                                    text: resp.message,
                                    showCloseButton: true,
                                    showCancelButton: false,
                                    focusConfirm: true,
                                }).then(function() {
                                    $('.role-tbl').DataTable().ajax.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Oops!',
                                    icon: 'error',
                                    text: `${resp.message}`,
                                    showCloseButton: true,
                                    showCancelButton: false,
                                    focusConfirm: true,
                                });
                                console.log(resp.status, resp.message);
                            }
                        },
                        error: function(xhr, textStatus, errorThrown) {
                            // Handle error response with status code 500
                            if (xhr.status === 401) {
                                Swal.fire({
                                    title: 'Oops!',
                                    icon: 'error',
                                    text: xhr.responseJSON.message || 'You do not have permission to perform this action.',
                                    showCloseButton: true,
                                    showCancelButton: false,
                                    focusConfirm: true,
                                }).then(function() {
                                    location.reload();
                                });
                            } else {
                                // Handle error response with status code 500
                                Swal.fire({
                                    title: 'Oops!',
                                    icon: 'error',
                                    text: 'Failed to get respond.',
                                    showCloseButton: true,
                                    showCancelButton: false,
                                    focusConfirm: true,
                                });
                            }
                            console.log(xhr.status, xhr.responseText);
                        }
                    });
                }
            })
        });
    });
</script>
@endsection