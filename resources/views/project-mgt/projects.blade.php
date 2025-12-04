@extends('layout.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Header Section --}}
    <div class="border bg-white rounded shadow-sm px-3 py-3 mb-4 position-relative">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">

            {{-- Kiri: Judul + Tombol Tambah --}}
            <div class="d-flex align-items-center gap-2">
                <h5 class="mb-0 fw-bold">
                    <i class="ti ti-briefcase me-2 text-primary"></i>Project Management
                </h5>
                <a href="{{ route('projects.create') }}">
                    <button class="btn btn-sm btn-primary">
                        <i class="ti ti-plus"></i> Tambah Project
                    </button>
                </a>
            </div>

            {{-- Kanan: Search Bar --}}
            <div class="d-flex" style="max-width: 300px;">
                <div class="input-group input-group-sm">
                    <input type="search" id="searchProject" class="form-control"
                        placeholder="Cari project..." aria-label="Search" autocomplete="off" />
                    <button id="btnSearchProject" class="input-group-text bg-white border-start">
                        <i class="ti ti-search"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Container untuk project list (update via AJAX) --}}
        <div id="projectListContainer">
            @include('project-mgt.partials.project-list', ['projects' => $projects])
        </div>

        {{-- Context Menu --}}
        <div id="contextMenu" class="card shadow position-absolute d-none border" style="width: 160px; z-index: 2000;">
            <ul class="list-group list-group-flush">
                <li class="list-group-item list-group-item-action d-flex align-items-center py-2" id="editProject" role="button">
                    <i class="ti ti-edit me-2 text-primary"></i>
                    <span>Edit Project</span>
                </li>
                <li class="list-group-item list-group-item-action d-flex align-items-center py-2 text-danger" id="deleteProject" role="button">
                    <i class="ti ti-trash me-2"></i>
                    <span>Hapus Project</span>
                </li>
            </ul>
        </div>
    </div>
</div>

{{-- jQuery Script --}}
<script>
    $(document).ready(function() {
        const $kanbanContainer = $("#kanban-board");
          const loadedKanbanBoards = {};

        // ======================================
        // 🔹 Reset Tabs ke Default (Details Tab)
        // ======================================
        function resetTabsToDefault(projectIndex) {
            $(`#projectTabs-${projectIndex} .nav-link`).removeClass('active');
            $(`#projectTabsContent-${projectIndex} .tab-pane`).removeClass('show active');

            $(`#overview-tab-${projectIndex}`).addClass('active');
            $(`#overview-${projectIndex}`).addClass('show active');
        }

        // ======================================
        // 🔹 Initialize Tombol Project
        // ======================================
        function initProjectButtons() {
            $('.project-btn').off('click').on('click', function() {
                const index = $(this).data('project-index');
                const projectId = $(this).data('project-id');
                const $selectedData = $(`.project-data[data-index="${index}"]`);
                const $localKanbanRoot = $selectedData.find('#kanban-root');

                if ($localKanbanRoot.length) {
                    $localKanbanRoot.attr('data-project-id', projectId.toString());
                }

                $('#kanban-root').attr('data-project-id', projectId.toString());

                $('.project-btn').removeClass('btn-primary').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('btn-primary');

                $('.project-data').hide();
                $(`.project-data[data-index="${index}"]`).show();

                resetTabsToDefault(index);

                // Clear gantt chart untuk project yang baru dipilih
                $(`#ganttChart${projectId}`).html(`
                <div class="text-center py-5">
                    <p class="text-muted">Klik tab Timeline untuk melihat Gantt Chart</p>
                </div>
            `);
            // If we have a kanban container inside this project block, try loading it (AJAX)
                if ($kanbanContainer.length) {
                    // if already loaded, simply re-init kanban (in case elements were detached)
                    if (loadedKanbanBoards[projectId]) {
                        // ensure kanban-root inside container has the correct projectId
                        $kanbanContainer.find('#kanban-root').attr('data-project-id', projectId.toString());
                        // call init from kanban.js (if available)
                        if (typeof initKanbanBoard === 'function') {
                            try { initKanbanBoard(); } catch (err) { console.warn('initKanbanBoard error', err); }
                        } else {
                            console.warn('initKanbanBoard() not found — make sure kanban.js is loaded.');
                        }
                        return;
                    }

                    // show small loading placeholder
                    const prevHtml = $kanbanContainer.html();
                    $kanbanContainer.data('prev-html', prevHtml);
                    $kanbanContainer.html(`
                        <div class="text-center py-4 kanban-loading-placeholder">
                            <div class="spinner-border text-primary" role="status" style="width:2rem;height:2rem">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="text-muted mt-2">Loading Kanban...</div>
                        </div>
                    `);

                    // fetch kanban partial (AJAX)
                    $.ajax({
                        url: `/projects/${projectId}/kanban`,
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            // server should return { success: true, html: '...' }
                            if (response && response.html) {
                                $kanbanContainer.html(response.html);

                                // set data-project-id on the injected kanban-root (important)
                                $kanbanContainer.find('#kanban-root').attr('data-project-id', projectId.toString());
                                // also set global root (in case other code reads it)
                                $('#kanban-root').attr('data-project-id', projectId.toString());

                                // mark loaded to avoid repeated requests
                                loadedKanbanBoards[projectId] = true;

                                // re-init kanban behaviors (drag/drop, modals, etc)
                                if (typeof initKanbanBoard === 'function') {
                                    try { initKanbanBoard(); } catch (err) { console.error('initKanbanBoard error', err); }
                                } else {
                                    console.warn('initKanbanBoard() not found — ensure js/kanban.js is included.');
                                }

                                console.log(`✅ Kanban loaded for project ${projectId}`);
                            } else {
                                console.warn('Unexpected response from /kanban AJAX', response);
                                $kanbanContainer.html(`<div class="alert alert-warning">No kanban content returned.</div>`);
                            }
                        },
                        error: function(xhr) {
                            console.error('Failed to load kanban board:', xhr);
                            const errHtml = `
                                <div class="alert alert-danger">
                                    <i class="ti ti-alert-circle me-2"></i>
                                    Failed to load Kanban Board. Please refresh or try again.
                                </div>`;
                            $kanbanContainer.html(errHtml);
                        }
                    });
                } else {
                    // fallback: no kanban-container found in this project block (maybe page structure different)
                    console.warn(`No .kanban-container inside project-data index=${index}`);
                }
            });

        }

        // Inisialisasi awal
        initProjectButtons();

        // ======================================
        // 🔹 SEARCH BAR (manual trigger)
        // ======================================
        function performSearch() {
            const searchValue = $('#searchProject').val().trim();

            $.ajax({
                url: "{{ route('projects.search') }}",
                method: 'GET',
                data: {
                    search: searchValue
                },
                success: function(response) {
                    $('#projectListContainer').html(response);
                    initProjectButtons();
                    if ($('.project-data').length > 0) resetTabsToDefault(0);
                },
                error: function(xhr) {
                    console.error('Search error:', xhr);
                }
            });
        }

        // Tekan Enter untuk search
        $('#searchProject').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                performSearch();
            }
        });

        // Klik tombol search untuk search
        $('#btnSearchProject').on('click', function() {
            performSearch();
        });

        // ======================================
        // 🔹 Context Menu (Klik Kanan)
        // ======================================
        let selectedProjectId = null;
        const $contextMenu = $('#contextMenu');

        if ($contextMenu.parent()[0].tagName !== 'BODY') {
            $('body').append($contextMenu);
        }

        $(document).on('contextmenu', '.project-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            selectedProjectId = $(this).data('project-id');

            const menuWidth = $contextMenu.outerWidth() || 160;
            const menuHeight = $contextMenu.outerHeight() || 90;
            let posX = e.clientX;
            let posY = e.clientY;

            const maxX = window.innerWidth - menuWidth - 10;
            const maxY = window.innerHeight - menuHeight - 10;
            posX = Math.min(posX, maxX);
            posY = Math.min(posY, maxY);

            $contextMenu
                .css({
                    position: 'fixed',
                    top: posY + 'px',
                    left: posX + 'px',
                    display: 'block',
                    zIndex: 9999
                })
                .removeClass('d-none')
                .hide()
                .fadeIn(100);
        });

        // Klik kiri di luar menu → tutup
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#contextMenu, .project-btn').length) {
                $contextMenu.fadeOut(100);
            }
        });

        // Scroll → tutup menu
        $(window).on('scroll', function() {
            $contextMenu.fadeOut(100);
        });

        // Disable klik kanan di menu
        $contextMenu.on('contextmenu', function(e) {
            e.preventDefault();
        });

        // Tombol Edit Project
        $('#editProject').on('click', function() {
            if (selectedProjectId) {
                window.location.href = `/projects/${selectedProjectId}/edit`;
            }
            $contextMenu.fadeOut(100);
        });

        // Tombol Hapus Project
        $('#deleteProject').on('click', function() {
            if (selectedProjectId) {
                $contextMenu.fadeOut(100);
                Swal.fire({
                    title: 'Hapus Project?',
                    text: 'Project dan semua data terkait akan dihapus permanen!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = $('<form>', {
                            method: 'POST',
                            action: `/projects/${selectedProjectId}`
                        }).append(`
                        <input type="hidden" name="_token" value="${$('meta[name="csrf-token"]').attr('content')}">
                        <input type="hidden" name="_method" value="DELETE">
                    `);
                        $('body').append(form);
                        form.submit();
                    }
                });
            }
        });

        // ======================================
        // 🔹 CRUD Versi Project
        // ======================================
        $(document).on('click', '.submit-version-btn', function() {
            const projectId = $(this).data('project-id');
            const form = $(`#addVersionForm${projectId}`);
            const formData = form.serialize();

            $.ajax({
                url: `/projects/${projectId}/versions`,
                method: 'POST',
                data: formData,
                beforeSend: function() {
                    $('.submit-version-btn').prop('disabled', true)
                        .html('<i class="ti ti-loader ti-spin me-1"></i>Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        $(`#addVersionModal${projectId}`).modal('hide');
                        form[0].reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 2000);
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Failed to add version';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: msg
                    });
                },
                complete: function() {
                    $('.submit-version-btn').prop('disabled', false)
                        .html('<i class="ti ti-device-floppy me-1"></i>Save Version');
                }
            });
        });

        // Update versi
        $(document).on('click', '.update-version-btn', function() {
            const projectId = $(this).data('project-id');
            const versionId = $(this).data('version-id');
            const form = $(`#editVersionForm${projectId}_${versionId}`);
            const formData = form.serialize();

            $.ajax({
                url: `/projects/${projectId}/versions/${versionId}`,
                method: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('.update-version-btn').prop('disabled', true)
                        .html('<i class="ti ti-loader ti-spin me-1"></i>Updating...');
                },
                success: function(response) {
                    if (response.success) {
                        $(`#editVersionModal${projectId}_${versionId}`).modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 2000);
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Failed to update version';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: msg
                    });
                },
                complete: function() {
                    $('.update-version-btn').prop('disabled', false)
                        .html('<i class="ti ti-device-floppy me-1"></i>Update Version');
                }
            });
        });

        // Hapus versi
        $(document).on('click', '.delete-version-btn', function() {
            const projectId = $(this).data('project-id');
            const versionId = $(this).data('version-id');
            const versionName = $(this).data('version-name');

            Swal.fire({
                title: 'Delete Version?',
                html: `Yakin ingin hapus <strong>Version ${versionName}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/projects/${projectId}/versions/${versionId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 2000);
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Failed to delete version';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: msg
                            });
                        }
                    });
                }
            });
        });

        // Aktifkan versi
        $(document).on('click', '.activate-version-btn', function() {
            const projectId = $(this).data('project-id');
            const versionId = $(this).data('version-id');
            const versionName = $(this).data('version-name');

            Swal.fire({
                title: 'Activate Version?',
                html: `Aktifkan <strong>Version ${versionName}</strong>? Versi lain akan nonaktif.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Aktifkan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/projects/${projectId}/versions/${versionId}/activate`,
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Activated!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 2000);
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Failed to activate version';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: msg
                            });
                        }
                    });
                }
            });
        });

        // ======================================
        // 🔹 TIMELINE & GANTT CHART FUNCTIONS
        // ======================================

        // 🔥 Object untuk menyimpan status loading per project
        const ganttChartLoaded = {};

        // Helper function to format date
        function formatDate(dateStr) {
            const date = new Date(dateStr);
            const options = {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            };
            return date.toLocaleDateString('id-ID', options);
        }

        // Function to load Gantt Chart
        function loadGanttChart(projectId) {
            // 🔥 Cek apakah sudah pernah di-load
            if (ganttChartLoaded[projectId]) {
                console.log(`Gantt Chart for project ${projectId} already loaded`);
                return;
            }

            const container = $(`#ganttChart${projectId}`);

            // 🔥 Cek apakah container ada
            if (container.length === 0) {
                console.error(`Gantt Chart container for project ${projectId} not found`);
                return;
            }

            container.html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-2">Loading Gantt Chart...</p>
            </div>
        `);

            $.ajax({
                url: `/projects/${projectId}/timeline/gantt-data`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderGanttChart(projectId, response.data);
                        // 🔥 Mark sebagai sudah di-load
                        ganttChartLoaded[projectId] = true;
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load gantt data:', xhr);
                    container.html(`
                    <div class="alert alert-danger m-3">
                        <i class="ti ti-alert-circle me-2"></i>
                        Failed to load Gantt Chart. Please try again.
                    </div>
                `);
                }
            });
        }

        // Function to render Gantt Chart
        function renderGanttChart(projectId, data) {
            const container = $(`#ganttChart${projectId}`);

            if (data.length === 0) {
                container.html(`
                <div class="alert alert-info m-3">
                    <i class="ti ti-info-circle me-2"></i>
                    No timeline data available. Add timeline to see the Gantt Chart.
                </div>
            `);
                return;
            }

            // Calculate date range
            let minDate = new Date(data[0].start);
            let maxDate = new Date(data[0].end);

            data.forEach(item => {
                const start = new Date(item.start);
                const end = new Date(item.end);
                if (start < minDate) minDate = start;
                if (end > maxDate) maxDate = end;
            });

            // Add padding to date range
            minDate.setDate(minDate.getDate() - 7);
            maxDate.setDate(maxDate.getDate() + 7);

            const totalDays = Math.ceil((maxDate - minDate) / (1000 * 60 * 60 * 24));
            const dayWidth = Math.max(800 / totalDays, 3); // Minimum 3px per day

            // Build HTML
            let html = '<div class="gantt-container" style="overflow-x: auto; padding: 20px;">';
            html += '<div class="gantt-chart" style="min-width: 800px;">';

            // Header with dates
            html += '<div class="gantt-header d-flex mb-2 pb-2 border-bottom">';
            html += '<div style="width: 200px; font-weight: bold; flex-shrink: 0;">Task</div>';
            html += '<div style="flex: 1; position: relative; height: 30px; min-width: 600px;">';

            // Month markers
            let currentMonth = '';
            const monthPositions = [];
            for (let d = new Date(minDate); d <= maxDate; d.setDate(d.getDate() + 1)) {
                const monthName = d.toLocaleDateString('id-ID', {
                    month: 'short',
                    year: 'numeric'
                });
                if (monthName !== currentMonth) {
                    const leftPos = ((d - minDate) / (1000 * 60 * 60 * 24)) * dayWidth;
                    monthPositions.push({
                        pos: leftPos,
                        name: monthName
                    });
                    currentMonth = monthName;
                }
            }

            // Render month markers
            monthPositions.forEach((month, index) => {
                const nextPos = monthPositions[index + 1]?.pos || (totalDays * dayWidth);
                const width = nextPos - month.pos;
                html += `
                <div style="position: absolute; left: ${month.pos}px; width: ${width}px; border-right: 1px solid #dee2e6;">
                    <div style="font-size: 11px; color: #666; padding: 2px 4px;">${month.name}</div>
                </div>
            `;
            });

            html += '</div></div>';

            // Render each timeline item
            data.forEach((item, index) => {
                const start = new Date(item.start);
                const end = new Date(item.end);
                const duration = Math.ceil((end - start) / (1000 * 60 * 60 * 24));
                const startOffset = Math.ceil((start - minDate) / (1000 * 60 * 60 * 24));

                const leftPos = startOffset * dayWidth;
                const width = Math.max(duration * dayWidth, 30); // Minimum 30px width

                const bgColor = item.type === 'plan' ? '#e3f2fd' : item.color;
                const borderColor = item.type === 'plan' ? '#90caf9' : item.color;
                const isDark = item.type !== 'plan';

                html += `
                <div class="gantt-row d-flex align-items-center mb-3 position-relative" style="min-height: 50px;">
                    <div style="width: 200px; font-size: 13px; padding-right: 10px; flex-shrink: 0;">
                        <div class="fw-bold text-truncate" title="${item.name}">${item.name}</div>
                        ${item.type === 'actual' ? `<small class="text-muted">${item.progress}% complete</small>` : '<small class="text-primary">Initial Plan</small>'}
                    </div>
                    <div style="flex: 1; position: relative; height: 40px; min-width: 600px;">
                        <div class="gantt-bar" 
                            style="position: absolute; 
                                   left: ${leftPos}px; 
                                   width: ${width}px; 
                                   height: 32px;
                                   background: ${bgColor};
                                   border: 2px solid ${borderColor};
                                   border-radius: 6px;
                                   display: flex;
                                   align-items: center;
                                   padding: 0 10px;
                                   cursor: pointer;
                                   transition: all 0.2s;
                                   box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)';"
                            onmouseout="this.style.transform=''; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';"
                            title="${item.name}
Start: ${formatDate(item.start)}
End: ${formatDate(item.end)}
Progress: ${item.progress}%${item.description ? '\n' + item.description : ''}">
                            ${item.type === 'actual' ? `
                                <div style="position: relative; width: 100%; height: 6px; background: rgba(255,255,255,0.3); border-radius: 3px; overflow: hidden;">
                                    <div style="position: absolute; left: 0; top: 0; height: 100%; width: ${item.progress}%; background: #fff; border-radius: 3px; transition: width 0.3s;"></div>
                                </div>
                            ` : `
                                <span style="color: #1976d2; font-size: 11px; font-weight: 700; letter-spacing: 0.5px;">PLAN AWAL</span>
                            `}
                        </div>
                    </div>
                </div>
            `;
            });

            html += '</div></div>';

            // Add legend
            html += `
            <div class="gantt-legend mt-3 pt-3 border-top">
                <small class="text-muted fw-bold me-3">Legend:</small>
                <span class="badge" style="background: #e3f2fd; color: #1976d2; border: 1px solid #90caf9;">Plan Awal</span>
                <span class="badge bg-success ms-2">75-100%</span>
                <span class="badge bg-warning ms-2">50-74%</span>
                <span class="badge bg-orange ms-2" style="background: #ff9800;">25-49%</span>
                <span class="badge bg-danger ms-2">0-24%</span>
            </div>
        `;

            container.html(html);
        }

        // 🔥 Load Gantt Chart when timeline tab is clicked
        $(document).on('shown.bs.tab', 'button[id^="timeline-tab-"]', function(e) {
            const tabId = $(e.target).attr('id');
            const index = tabId.replace('timeline-tab-', '');
            const projectBtn = $(`.project-btn[data-project-index="${index}"]`);
            const projectId = projectBtn.data('project-id');

            console.log(`Timeline tab clicked for project ${projectId}, index ${index}`);

            if (projectId) {
                loadGanttChart(projectId);
            }
        });

        // 🔥 NEW: Pre-load Gantt Chart untuk semua project yang visible
        function preloadVisibleGanttCharts() {
            // Cari semua project data yang sedang ditampilkan
            $('.project-data:visible').each(function() {
                const projectIndex = $(this).data('index');
                const projectBtn = $(`.project-btn[data-project-index="${projectIndex}"]`);
                const projectId = projectBtn.data('project-id');

                if (projectId) {
                    // Cek apakah tab timeline sedang aktif
                    const timelineTab = $(`#timeline-tab-${projectIndex}`);
                    if (timelineTab.hasClass('active')) {
                        console.log(`Pre-loading Gantt Chart for visible project ${projectId}`);
                        loadGanttChart(projectId);
                    }
                }
            });
        }

        // 🔥 Panggil preload saat dokumen ready dan setelah project button di-klik
        setTimeout(function() {
            preloadVisibleGanttCharts();
        }, 500);

        // 🔥 Override initProjectButtons untuk include preload
        const originalInitProjectButtons = initProjectButtons;
        initProjectButtons = function() {
            originalInitProjectButtons();

            // Tambahkan listener untuk preload setelah klik project
            $('.project-btn').on('click', function() {
                setTimeout(preloadVisibleGanttCharts, 300);
            });
        };

        // ======================================
        // 🔹 CRUD TIMELINE
        // ======================================

        // Add Timeline
        $(document).on('click', '.submit-timeline-btn', function() {
            const projectId = $(this).data('project-id');
            const form = $(`#addTimelineForm${projectId}`);
            const formData = form.serialize();

            // Validasi dates
            const startDate = form.find('input[name="start_date"]').val();
            const endDate = form.find('input[name="end_date"]').val();

            if (new Date(startDate) > new Date(endDate)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date!',
                    text: 'End date must be after start date.'
                });
                return;
            }

            $.ajax({
                url: `/projects/${projectId}/timeline`,
                method: 'POST',
                data: formData,
                beforeSend: function() {
                    $('.submit-timeline-btn').prop('disabled', true)
                        .html('<i class="ti ti-loader ti-spin me-1"></i>Saving...');
                },
                success: function(response) {
                    if (response.success) {
                        $(`#addTimelineModal${projectId}`).modal('hide');
                        form[0].reset();

                        // 🔥 Reset loaded status agar bisa reload
                        ganttChartLoaded[projectId] = false;

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 2000);
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Failed to add timeline';
                    if (xhr.responseJSON?.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        msg = errors.join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: msg
                    });
                },
                complete: function() {
                    $('.submit-timeline-btn').prop('disabled', false)
                        .html('<i class="ti ti-device-floppy me-1"></i>Save Timeline');
                }
            });
        });

        // Update Timeline
        $(document).on('click', '.update-timeline-btn', function() {
            const projectId = $(this).data('project-id');
            const timelineId = $(this).data('timeline-id');
            const form = $(`#editTimelineForm${projectId}_${timelineId}`);
            const formData = form.serialize();

            // Validasi dates
            const startDate = form.find('input[name="start_date"]').val();
            const endDate = form.find('input[name="end_date"]').val();

            if (new Date(startDate) > new Date(endDate)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date!',
                    text: 'End date must be after start date.'
                });
                return;
            }

            $.ajax({
                url: `/projects/${projectId}/timeline/${timelineId}`,
                method: 'PUT',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('.update-timeline-btn').prop('disabled', true)
                        .html('<i class="ti ti-loader ti-spin me-1"></i>Updating...');
                },
                success: function(response) {
                    if (response.success) {
                        $(`#editTimelineModal${projectId}_${timelineId}`).modal('hide');

                        // 🔥 Reset loaded status agar bisa reload
                        ganttChartLoaded[projectId] = false;

                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 2000);
                    }
                },
                error: function(xhr) {
                    let msg = xhr.responseJSON?.message || 'Failed to update timeline';
                    if (xhr.responseJSON?.errors) {
                        const errors = Object.values(xhr.responseJSON.errors).flat();
                        msg = errors.join('<br>');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        html: msg
                    });
                },
                complete: function() {
                    $('.update-timeline-btn').prop('disabled', false)
                        .html('<i class="ti ti-device-floppy me-1"></i>Update');
                }
            });
        });

        // Delete Timeline
        $(document).on('click', '.delete-timeline-btn', function() {
            const projectId = $(this).data('project-id');
            const timelineId = $(this).data('timeline-id');
            const timelineTitle = $(this).data('timeline-title');

            Swal.fire({
                title: 'Delete Timeline?',
                html: `Are you sure you want to delete <strong>${timelineTitle}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/projects/${projectId}/timeline/${timelineId}`,
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            // 🔥 Reset loaded status agar bisa reload
                            ganttChartLoaded[projectId] = false;

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            setTimeout(() => location.reload(), 2000);
                        },
                        error: function(xhr) {
                            let msg = xhr.responseJSON?.message || 'Failed to delete timeline';
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: msg
                            });
                        }
                    });
                }
            });
        });

        // Sync range slider with number input
        $(document).on('input', 'input[type="range"]', function() {
            const value = $(this).val();
            $(this).prev('.input-group').find('input[name="progress"]').val(value);
        });

        $(document).on('input', 'input[name="progress"]', function() {
            const value = $(this).val();
            $(this).closest('.mb-3').find('input[type="range"]').val(value);
        });

        // Reset form setiap modal ditutup
        $('.modal').on('hidden.bs.modal', function() {
            const form = $(this).find('form');
            if (form.length > 0) {
                form[0].reset();
                // Reset range slider
                form.find('input[type="range"]').val(0);
            }
        });

        // 🔥 Debug: Log semua project yang ada
        console.log('=== Available Projects ===');
        $('.project-btn').each(function() {
            const index = $(this).data('project-index');
            const id = $(this).data('project-id');
            console.log(`Project Index: ${index}, ID: ${id}`);
        });
        console.log('==========================');
    });
</script>

@endsection