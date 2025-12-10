@extends('layout.app')

@section('content')
<div class="container py-4">

    <div class="card shadow-sm">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold mb-0">📅 Project Calendar</h4>

                <!-- PIC FILTER -->
                <div class="d-flex align-items-center gap-2">
                    <label class="fw-semibold">PIC:</label>
                    <select id="picFilter" class="form-select form-select-sm w-auto">
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr>

            <!-- CALENDAR -->
            <div id="projectCalendar" class="border rounded p-2 bg-light"></div>

        </div>
    </div>

</div>


<!-- =============================== -->
<!--  MODAL DETAIL PROJECT           -->
<!-- =============================== -->
<div class="modal fade" id="projectModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow">

            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="modalTitle">Project Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="fw-semibold">PIC:</label>
                    <div id="modalPIC">-</div>
                </div>

                <div class="mb-3">
                    <label class="fw-semibold">Description:</label>
                    <div id="modalDescription">-</div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <label class="fw-semibold">Start:</label>
                        <div id="modalStart">-</div>
                    </div>
                    <div class="col">
                        <label class="fw-semibold">Finish:</label>
                        <div id="modalEnd">-</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-semibold">Version:</label>
                    <div id="modalVersion">-</div>
                </div>

                <div class="mb-3">
                    <label class="fw-semibold mb-1">Progress:</label>
                    <div class="progress">
                        <div id="modalProgressBar" class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="fw-semibold">Dampak:</label>
                    <div id="modalDampak">-</div>
                </div>

                <div class="mb-3">
                    <label class="fw-semibold">Kanban Statuses:</label>
                    <ul id="modalStatuses"></ul>
                </div>

            </div>

            <div class="modal-footer">
                <a href="#" id="modalViewButton" class="btn btn-primary">Open Project</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

@endsection



@section('scripts')
<!-- FullCalendar -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<style>
    .fc .fc-toolbar-title {
        font-size: 1.4rem;
        font-weight: 600;
    }

    .fc .fc-button {
        border-radius: 6px !important;
        padding: 6px 12px !important;
    }

    .fc-daygrid-event {
        font-size: 0.80rem;
        padding: 2px 6px;
        border-radius: 4px;
    }

    .fc-day-today {
        background: rgba(13, 110, 253, 0.12) !important;
    }
</style>

<script>
    let calendar;
    document.addEventListener('DOMContentLoaded', function() {

        let calendarEl = document.getElementById('projectCalendar');

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: 700,

            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listMonth'
            },

            buttonText: {
                today: 'Today',
                month: 'Month',
                week: 'Week',
                list: 'List'
            },

            events: [],

            eventClick: function(info) {
                const projectId = info.event.id;

                fetch(`/calendar/project/${projectId}`)
                    .then(res => res.json())
                    .then(data => {

                        document.getElementById('modalTitle').innerText = data.name;
                        document.getElementById('modalPIC').innerText = data.pic;
                        document.getElementById('modalDescription').innerText = data.description ?? '-';

                        document.getElementById('modalStart').innerText = data.start;
                        document.getElementById('modalEnd').innerText = data.end;

                        document.getElementById('modalVersion').innerText = data.version;
                        document.getElementById('modalDampak').innerText = data.dampak ?? '-';

                        // Progress bar
                        const bar = document.getElementById('modalProgressBar');
                        bar.style.width = data.progress + '%';
                        bar.innerText = data.progress + '%';

                        // Kanban statuses
                        const ul = document.getElementById('modalStatuses');
                        ul.innerHTML = "";
                        document.getElementById('modalStatuses').innerHTML = 
                        `<li>${data.status}</li>`;
                        document.getElementById('modalViewButton').href = `/project-mgt/projects`;

                        const modal = new bootstrap.Modal(document.getElementById('projectModal'));
                        modal.show();
                    });
            }
        });

        
        calendar.render();

        // load first PIC
        loadProjects(document.getElementById('picFilter').value);
    });

    document.getElementById('picFilter').addEventListener('change', function() {
        loadProjects(this.value);
    });

    function loadProjects(picId) {
        calendar.removeAllEvents();
        calendar.addEventSource(`/calendar/pic/${picId}`);
    }
</script>
@endsection

