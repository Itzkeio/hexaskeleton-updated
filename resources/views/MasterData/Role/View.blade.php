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
                    <li class="breadcrumb-item active" aria-current="page">View</li>
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
                <small>View role data in WorkSpace</small>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-4">
                        <form class="form-custom">
                            <div class="mb-3">
                                <label class="form-label">Site</label>
                                <p><b>{{ $role->compName }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <p><b>{{ $role->name }}</b></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <p><b>{{ $role->description ?: '-' }}</b></p>
                            </div>
                            <div class="mb-3 d-none d-sm-block">
                                <a href="{{ url('/masterdata/role') }}" class="btn btn-info btn-sm rounded-2">Back</a>
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
                            <a href="{{ url('/masterdata/role') }}" class="btn btn-info btn-sm rounded-2">Back</a>
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
    $(document).ready(function () {
        $('#menu-tree').jstree({
            'core': {
                'data': {
                    'url': "{{ url('/menu/structure') }}",
                    'data': function (node) {
                        return {
                            'id': node.id,
                            'type': 'view',
                            'roleId': "{{ $role->id }}"
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
            'plugins': [
                'checkbox',
                'types'
            ],
            'checkbox': {
                three_state: true,
                whole_node: true,
                tie_selection: true
            },
        }).bind("ready.jstree", function (e, data) {
            $('#menu-tree').find('.jstree-checkbox, .jstree-anchor').css('pointer-events', 'none');
        })
        .on('open_node.jstree', function (event, data) {
            data.instance.set_type(data.node, 'f-open');
            setTimeout(function () {
                $('#menu-tree').find('.jstree-checkbox, .jstree-anchor').css('pointer-events', 'none');
            }, 0);
        })
        .on('close_node.jstree', function (event, data) {
            data.instance.set_type(data.node, 'f-closed');
            setTimeout(function () {
                $('#menu-tree').find('.jstree-checkbox, .jstree-anchor').css('pointer-events', 'none');
            }, 0);
        })
        .on('changed.jstree', function (event, data) {
            setTimeout(function () {
                $('#menu-tree').find('.jstree-checkbox, .jstree-anchor').css('pointer-events', 'none');
            }, 0);
        });
    });
</script>
@endsection
