@extends('layout.app')

@section('content')

@php
$compCode = auth()->user()->compCode;
$compName = auth()->user()->compName;
@endphp


<!-- [ breadcrumb ] start -->
<div class="page-header px-1">
    <div class="page-block">
        <div class="row align-items-center">
            <div class="col-md-12">
                <ul class="breadcrumb">
                    <li class="breadcrumb-item">Master Data</li>
                    <li class="breadcrumb-item"><a href="{{ url('/masterdata/role') }}">Role</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create</li>
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

<div class="row bg-white">
    <div class="col-12 col-md-4">
        <form class="form-custom">
            <div class="mb-3">
                <label class="form-label">Site <sup>*</sup></label>
                <select class="form-control form-control-sm" id="comp-code" disabled>
                    <option data-comp-code="{{ $compCode }}" data-comp-name="{{ $compName }}">{{ $compName }}</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Name <sup>*</sup></label>
                <input type="text" class="form-control form-control-sm" id="name" placeholder="Role name">
                <div class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" id="description"></textarea>
            </div>
            <div class="d-none d-sm-block mb-3">
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
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.save-btn').on('click', function(e) {
            $('.save-btn').prop('disabled', true);
            $('.form-control').removeClass('is-invalid');
            $('.invalid-feedback').text('');

            e.preventDefault();

            var checkedKeys = [];

            var selectedNodes = $('#menu-tree').jstree("get_selected", true);
            $.each(selectedNodes, function() {
                if (this.original && this.original.key) {
                    checkedKeys.push(this.original.key);
                }
            });

            $("#menu-tree").find(".jstree-undetermined").each(function() {
                var nodeId = $(this).closest('.jstree-node').attr("id");
                var node = $('#menu-tree').jstree(true).get_node(nodeId);
                if (node.original && node.original.key) {
                    checkedKeys.push(node.original.key);
                }
            });

            $.ajax({
                url: "{{ route('role.store') }}",
                type: 'POST',
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
                    if (xhr.status === 400) {
                        const errors = JSON.parse(xhr.responseText);
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
                    } else if (xhr.status === 422) {
                        const res = xhr.responseJSON; // already parsed if content-type is JSON
                        const errors = res.errors;

                        for (let key in errors) {
                            const errorMessage = errors[key].join('<br>');
                            const $input = $('#' + key);

                            $input.addClass('is-invalid');
                            $input.siblings('.invalid-feedback').html(errorMessage);
                        }
                    } else {
                        $('#name').addClass('is-invalid').siblings('.invalid-feedback').html(xhr.responseText.replace(/"/g, ''));
                    }
                    $('.save-btn').prop('disabled', false);
                    console.log(xhr.status, xhr.responseText);
                }
            });
        });

        // ===================== MENU TREE
        $('#menu-tree').jstree({
            'core': {
                'data': {
                    'url': "{{ url('/menu/structure') }}",
                    'data': function(node) {
                        return {
                            'id': node.id,
                            'type': 'create',
                            'roleId': '00000000-0000-0000-0000-000000000000'
                        };
                    }
                },
            },
            "types": {
                "default": {
                    "icon": "fa fa-folder fa-fw"
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
            },
        }).bind("ready.jstree", function(e, data) {
            data.instance.open_all();
            $(".save-btn").removeAttr('disabled');
        });

        $("#menu-tree").on('open_node.jstree', function(event, data) {
            data.instance.set_type(data.node, 'f-open');
        });

        $("#menu-tree").on('close_node.jstree', function(event, data) {
            data.instance.set_type(data.node, 'f-closed');
        });
    });
</script>
@endsection