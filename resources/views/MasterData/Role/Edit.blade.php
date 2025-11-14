@extends('layout.app')

@section('content')
<!-- [ breadcrumb ] start -->
<div class="page-header px-1">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item"><a href="{{ url('/masterdata/role') }}">Role</a></li>
                    <li class="breadcrumb-item" aria-current="page">Edit</li>
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
                <h5>Management Role</h5>
                <small>Update role data in WorkSpace</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <form class="form-custom" id="role-form">
                            <div class="mb-3">
                                <label class="form-label">Site <sup>*</sup></label>
                                <select class="form-control form-control-sm" id="comp-code" disabled>
                                    <option data-comp-code="{{ $role->compCode }}" data-comp-name="{{ $role->compName }}">
                                        {{ $role->compName }}
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name <sup>*</sup></label>
                                <input type="text" class="form-control form-control-sm" id="name" placeholder="Role name" value="{{ old('name', $role->name) }}">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" id="description">{{ old('description', $role->description) }}</textarea>
                            </div>
                            <div class="mb-3 d-none d-sm-block">
                                <a href="{{ url('/masterdata/role') }}" class="btn btn-danger btn-sm rounded-2">Cancel</a>
                                <button type="button" class="btn btn-primary btn-sm rounded-2 save-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                    <div class="col-12 col-md-8">
                        <form class="form-custom">
                            <label class="form-label">Select access</label>
                            <div id="menu-tree"></div>
                        </form>
                    </div>
                    <div class="col-12 d-block d-sm-none my-3">
                        <form class="form-custom">
                            <a href="{{ url('/masterdata/role') }}" class="btn btn-danger btn-sm rounded-2">Cancel</a>
                            <button type="button" class="btn btn-primary btn-sm rounded-2 save-btn">Submit</button>
                        </form>
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
        $('.save-btn').on('click', function(e) {
            e.preventDefault();
            $('.save-btn').prop('disabled', true);
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            var checkedKeys = [];

            var selectedNodes = $('#menu-tree').jstree("get_selected", true);
            $.each(selectedNodes, function() {
                if (this.original && this.original.key) {
                    checkedKeys.push(this.original.key);
                }
            });

            // Also include partially checked (undetermined) nodes
            $("#menu-tree").find(".jstree-undetermined").each(function() {
                var nodeId = $(this).closest('.jstree-node').attr("id");
                var node = $('#menu-tree').jstree(true).get_node(nodeId);
                if (node.original && node.original.key) {
                    checkedKeys.push(node.original.key);
                }
            });

            const roleId = '{{ $role->id }}';
            $.ajax({
                url: "{{ route('role.update', ':id') }}".replace(':id', roleId),
                type: 'PUT',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    compCode: $('#comp-code').find('option:selected').attr('data-comp-code'),
                    compName: $('#comp-code').find('option:selected').attr('data-comp-name'),
                    name: $('#name').val(),
                    description: $('#description').val(),
                    menuId: checkedKeys
                }),
                success: function(resp) {
                    if (resp.status == 200) {
                        Swal.fire({
                            title: 'Good',
                            text: resp.message,
                            icon: 'success',
                        }).then(function() {
                            window.location.href = '{{ url("/masterdata/role") }}'
                        });
                    } else {
                        Swal.fire({
                            title: 'Oops',
                            text: resp.message,
                            icon: 'error',
                        });
                    }
                    $('.save-btn').prop('disabled', false);
                },
                error: function(xhr) {
                    if (xhr.status === 422) { // Laravel validation error status
                        var errors = xhr.responseJSON.errors;
                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                var errorMessage = errors[key].join('<br>');
                                $('#' + key.toLowerCase()).addClass('is-invalid').siblings('.invalid-feedback').html(errorMessage);
                            }
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
                        $('#name').addClass('is-invalid').siblings('.invalid-feedback').html(xhr.responseText.replace(/"/g, ''));
                    }
                    $('.save-btn').prop('disabled', false);
                    console.log(xhr.status, xhr.responseText);
                }
            });
        });

        // ========== jsTree load with RBAC
        $('#menu-tree').jstree({
                'core': {
                    'data': {
                        'url': "{{ url('/menu/structure') }}",
                        'data': function(node) {
                            return {
                                'id': node.id,
                                'type': 'view',
                                'roleId': '{{ $role->id }}'
                            };
                        }
                    },
                },
                'types': {
                    'default': {
                        'icon': 'fa fa-folder fa-fw'
                    },
                    'f-open': {
                        'icon': 'fa fa-folder-open fa-fw'
                    },
                    'f-closed': {
                        'icon': 'fa fa-folder fa-fw'
                    }
                },
                'plugins': ['checkbox', 'types'],
                'checkbox': {
                    three_state: true,
                    whole_node: true,
                    tie_selection: true
                }
            })
            .on('ready.jstree', function() {
                $(".save-btn").removeAttr('disabled');
            })
            .on('open_node.jstree', function(event, data) {
                data.instance.set_type(data.node, 'f-open');
            })
            .on('close_node.jstree', function(event, data) {
                data.instance.set_type(data.node, 'f-closed');
            });
    });
</script>
@endsection