document.addEventListener("DOMContentLoaded", () => {
    if (window.__KANBAN_JS_LOADED__) {
    console.log("⚠️ Kanban JS sudah dimuat sebelumnya — dilewati");
    return;
}
window.__KANBAN_JS_LOADED__ = true;
    // ================================
    //  GLOBAL HELPER & KONFIG
    // ================================
    const csrf =
        document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
        "";

        let isDragging = false; // FIX: wajib ada


     function getProjectId() {
        // 1) cari semua kanban-root (1 per tab)
        const roots = document.querySelectorAll('#kanban-root');

        for (const root of roots) {
            if (root.offsetParent !== null) {  // visible
                return root.dataset.projectId;
            }
        }

        // 2) fallback: tab active
        const activeRoot = document.querySelector('.tab-pane.active #kanban-root');
        if (activeRoot) return activeRoot.dataset.projectId;

        // 3) fallback terakhir (single view)
        return document.querySelector('#kanban-root')?.dataset.projectId || null;
    }

    /* ----------------------------------------
        TOAST
    ---------------------------------------- */
    function showToast(message, type = "success") {
        const old = document.getElementById("kanban-toast");
        if (old) old.remove();

        const el = document.createElement("div");
        el.id = "kanban-toast";
        el.className = `alert alert-${type} position-fixed top-0 end-0 m-3 shadow`;
        el.style.cssText = "z-index:9999; animation: fadeIn .25s;";
        el.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="ti ti-${type === "success" ? "check" : "alert-circle"} me-2"></i>
                <span>${message}</span>
            </div>
        `;
        document.body.appendChild(el);

        setTimeout(() => {
            el.style.animation = "fadeOut .25s";
            setTimeout(() => el.remove(), 250);
        }, 2500);
    }

    function goToKanbanTab() {
        const tab =
            document.querySelector('button[data-bs-target="#kanban_tab"]') ||
            document.querySelector('a[href="#kanban_tab"]') ||
            document.querySelector('[data-bs-target="#kanban"]');

        if (tab) tab.click();
    }

     /* ----------------------------------------
        UPDATE BADGE STATUS DI CARD
    ---------------------------------------- */
    function updateCardBadge(card, statusMeta) {
        console.log("Update badge:", card, statusMeta);

        let wrapper = card.querySelector(".position-absolute.top-0.end-0.m-1");
        if (!wrapper) return;

        wrapper.innerHTML = "";

        const badge = document.createElement("span");
        badge.className = "badge";
        badge.textContent = statusMeta.label ?? statusMeta.key ?? "";

        if (statusMeta.color_bg) {
            badge.style.background = statusMeta.color_bg;
        }
        if (statusMeta.color_border) {
            badge.style.border = `1px solid ${statusMeta.color_border}`;
        }

        badge.style.color = "#000";
        wrapper.appendChild(badge);
    }

    /* ----------------------------------------
        SORTABLE DRAG & DROP
    ---------------------------------------- */
    function initSortableColumns() {
        const cols = document.querySelectorAll(".kanban-column");
        if (!cols.length) return;

        cols.forEach(col => {
            if (col.dataset.sortableInit === "1") return;

            const newStatus = col.dataset.status;
            const projectId = col.dataset.projectId;

            if (!projectId) return;

            new Sortable(col, {
                group: "kanban-" + projectId,
                animation: 200,
                ghostClass: "sortable-ghost",
                dragClass: "sortable-drag",

                onStart() { isDragging = true; },
                onEnd() { isDragging = false; },

                onAdd(evt) {
                    const card = evt.item;
                    const taskId = card.dataset.id;

                    fetch(`/projects/${projectId}/kanban/task-status`, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": csrf,
                            Accept: "application/json",
                        },
                        body: JSON.stringify({
                            id: taskId,
                            status: newStatus
                        }),
                    })
                    .then(r => r.json())
                    .then(res => {
                        if (res.success) {
                            console.log("Update success:", res);
                            card.dataset.status = newStatus;

                            if (res.status) {
                                updateCardBadge(card, res.status);
                            }

                            showToast("Status updated!", "success");
                        } else {
                            showToast("Gagal update status", "danger");
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        showToast("Error update status!", "danger");
                    });
                }
            });

            col.dataset.sortableInit = "1";
        });
    }

    // ================================
    //        HIGHLIGHT OVERDUE
    // ================================
    function highlightOverdueTasks() {
        const today = new Date();

        document.querySelectorAll(".kanban-item").forEach((item) => {
            const dateEnd = item.dataset.date_end;
            if (!dateEnd) {
                item.classList.remove("overdue-task");
                return;
            }

            const endDate = new Date(dateEnd);

            let status =
                item.dataset.status || item.getAttribute("data-status");
            if (!status) {
                const colBody = item.closest(".card-body");
                if (colBody && colBody.id) {
                    if (colBody.id.startsWith("todo-")) status = "todo";
                    else if (colBody.id.startsWith("inprogress-"))
                        status = "inprogress";
                    else if (colBody.id.startsWith("finished-"))
                        status = "finished";
                }
            }

            if (endDate < today && status !== "finished") {
                item.classList.add("overdue-task");
            } else {
                item.classList.remove("overdue-task");
            }
        });
    }

    // ================================
    //           SUBTASK HELPERS
    // ================================
    function getStatusBadge(status) {
        const badges = {
            todo: '<span class="badge bg-secondary">To Do</span>',
            inprogress: '<span class="badge bg-primary">In Progress</span>',
            finished: '<span class="badge bg-success">Finished</span>',
        };
        return badges[status] || "";
    }

    function getPriorityBadge(priority) {
        const badges = {
            urgent: '<span class="badge bg-danger">Urgent</span>',
            high: '<span class="badge bg-warning">High</span>',
            normal: '<span class="badge bg-primary">Normal</span>',
            low: '<span class="badge bg-secondary">Low</span>',
        };
        return badges[priority] || "";
    }

    function updateSubtaskCounter(kanbanId) {
        const kanbanCard = document.querySelector(
            `.kanban-item[data-id="${kanbanId}"]`
        );
        if (!kanbanCard) return;

        const subtasksCollapse = kanbanCard.querySelector(
            `#subtasks-${kanbanId}`
        );
        if (!subtasksCollapse) return;

        const allCheckboxes =
            subtasksCollapse.querySelectorAll(".subtask-checkbox");
        const checkedCount = Array.from(allCheckboxes).filter(
            (cb) => cb.checked
        ).length;
        const totalCount = allCheckboxes.length;

        const counterBtn = kanbanCard.querySelector(
            `[data-bs-target="#subtasks-${kanbanId}"] span`
        );
        if (counterBtn) {
            counterBtn.innerHTML = `<i class="ti ti-checklist"></i> ${checkedCount}/${totalCount} subtasks`;
        }
    }

    // ================================
    //           LOAD SUBTASKS
    // ================================
    function loadSubtasks(kanbanId) {
        const projectId = getProjectId();
        const listEl = document.getElementById(`subtasks-list-${kanbanId}`);
        if (!listEl) return console.warn("No subtasks list for", kanbanId);

        listEl.innerHTML = `<div class="text-center py-3"><i class="ti ti-loader ti-spin"></i> Loading...</div>`;

        fetch(`/projects/${projectId}/kanban/${kanbanId}/subtasks`)
            .then((r) => {
                if (!r.ok) throw new Error("HTTP " + r.status);
                return r.json();
            })
            .then((data) => {
                if (
                    !data.subtasks ||
                    !Array.isArray(data.subtasks) ||
                    data.subtasks.length === 0
                ) {
                    listEl.innerHTML = `<div class="text-center text-muted py-3"><i class="ti ti-clipboard-list"></i><p class="mb-0 small">No subtasks yet</p></div>`;
                    return;
                }

                const frag = document.createDocumentFragment();
                data.subtasks.forEach((st) => {
                    const card = document.createElement("div");
                    card.className = "card mb-2 p-2 border";
                    const row = document.createElement("div");
                    row.className =
                        "d-flex justify-content-between align-items-start";
                    const left = document.createElement("div");
                    left.className = "flex-grow-1";
                    const title = document.createElement("div");
                    title.className = "fw-semibold";
                    title.textContent = st.title || "";
                    left.appendChild(title);
                    if (st.description) {
                        const d = document.createElement("div");
                        d.className = "small text-muted";
                        d.textContent = st.description;
                        left.appendChild(d);
                    }
                    const meta = document.createElement("div");
                    meta.className = "mt-1";
                    meta.innerHTML = `${getStatusBadge(
                        st.status
                    )} ${getPriorityBadge(st.priority)} ${
                        st.duration
                            ? `<span class="badge bg-info-subtle text-info"><i class="ti ti-clock"></i> ${st.duration} hari</span>`
                            : ""
                    }`;
                    left.appendChild(meta);

                    // Files
                    if (
                        st.files &&
                        Array.isArray(st.files) &&
                        st.files.length > 0
                    ) {
                        const filesWrap = document.createElement("div");
                        filesWrap.className = "mt-1 small";
                        st.files.forEach((f) => {
                            const rowFile = document.createElement("div");
                            rowFile.className =
                                "d-flex justify-content-between align-items-center mb-1";
                            const a = document.createElement("a");
                            a.href = `/storage/${f.file_path}`;
                            a.target = "_blank";
                            a.textContent = `📎 ${f.filename}`;
                            a.className = "text-decoration-none";
                            const delBtn = document.createElement("button");
                            delBtn.dataset.type = "subtask";
                            delBtn.dataset.kanbanId = kanbanId;
                            delBtn.type = "button";
                            delBtn.className =
                                "btn btn-sm btn-link text-danger p-0 delete-file-btn";
                            delBtn.dataset.fileId = f.id;
                            delBtn.innerHTML = '<i class="ti ti-trash"></i>';
                            rowFile.appendChild(a);
                            rowFile.appendChild(delBtn);
                            filesWrap.appendChild(rowFile);
                        });
                        left.appendChild(filesWrap);
                    }

                    const right = document.createElement("div");
                    right.className = "d-flex gap-1";

                    const btnEdit = document.createElement("button");
                    btnEdit.type = "button";
                    btnEdit.className = "btn btn-sm btn-outline-primary";
                    btnEdit.innerHTML = '<i class="ti ti-edit"></i>';
                    btnEdit.addEventListener("click", () =>
                        openEditSubtaskModal(st.id, kanbanId, st)
                    );

                    const btnDel = document.createElement("button");
                    btnDel.type = "button";
                    btnDel.className = "btn btn-sm btn-outline-danger";
                    btnDel.innerHTML = '<i class="ti ti-trash"></i>';
                    btnDel.addEventListener("click", () =>
                        openDeleteSubtask(st.id, kanbanId)
                    );

                    right.appendChild(btnEdit);
                    right.appendChild(btnDel);

                    row.appendChild(left);
                    row.appendChild(right);
                    card.appendChild(row);
                    frag.appendChild(card);
                });
                listEl.innerHTML = "";
                listEl.appendChild(frag);
            })
            .catch((err) => {
                console.error("loadSubtasks err", err);
                listEl.innerHTML = `<div class="alert alert-danger">Failed to load subtasks</div>`;
            });
    }

    // ================================
    //        EDIT TASK MODAL
    // ================================
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".edit-btn");
        if (!btn) return;

        const taskCard = btn.closest(".kanban-item");
        const taskId = taskCard.dataset.id;
        const modalEl = document.getElementById(`editKanbanModal-${taskId}`);
        if (!modalEl) return console.warn("No edit modal for", taskId);

        document.getElementById(`edit_task_id-${taskId}`).value = taskId;
        document.getElementById(`edit_title-${taskId}`).value =
            taskCard.dataset.title ?? "";

        const descEl = document.getElementById(`edit_description-${taskId}`);
        if (descEl) descEl.value = taskCard.dataset.description ?? "";

        const notesEl = document.getElementById(`edit_notes-${taskId}`);
        if (notesEl) notesEl.value = taskCard.dataset.notes ?? "";

        const pri = document.getElementById(`edit_priority-${taskId}`);
        if (pri) pri.value = taskCard.dataset.priority ?? "normal";

        const ds = document.getElementById(`edit_date_start-${taskId}`);
        const de = document.getElementById(`edit_date_end-${taskId}`);
        if (ds) ds.value = taskCard.dataset.date_start ?? "";
        if (de) de.value = taskCard.dataset.date_end ?? "";
        const dur = document.getElementById(`edit_duration-${taskId}`);
        if (dur && ds && de && ds.value && de.value) {
            const diff = Math.ceil(
                (new Date(de.value) - new Date(ds.value)) / (1000 * 60 * 60 * 24)
            );
            dur.value = diff + " hari";
        } else if (dur) dur.value = "-";

        const editForm = document.getElementById(`editKanbanForm-${taskId}`);
        if (editForm) {
            editForm.action = `/projects/${getProjectId()}/kanban/${taskId}`;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        loadSubtasks(taskId);
    });

    // ================================
    //          DELETE TASK
    // ================================
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".confirm-delete-task");
        if (!btn) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const taskId = btn.dataset.taskId;
        const modalEl = btn.closest(".modal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        const projectId = getProjectId();

        fetch(`/projects/${projectId}/kanban/${taskId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": csrf,
                Accept: "application/json",
            },
        })
            .then((r) => r.json())
            .then((res) => {
                if (res.success) {
                    showToast("Task berhasil dihapus!", "success");

                    if (modal) modal.hide();

                    const card = document.querySelector(
                        `.kanban-item[data-id="${taskId}"]`
                    );
                    if (card) {
                        card.style.opacity = "0";
                        setTimeout(() => card.remove(), 250);
                    }
                } else {
                    showToast("Gagal menghapus task!", "danger");
                }
            })
            .catch((err) => {
                console.error(err);
                showToast("Error menghapus task!", "danger");
            });
    });

    // ================================
    //      OPEN ADD SUBTASK MODAL
    // ================================
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".open-add-subtask-btn");
        if (!btn) return;
        const taskId = btn.dataset.taskId;

        const editTaskModalEl = document.getElementById(
            `editKanbanModal-${taskId}`
        );
        if (editTaskModalEl) {
            const editTaskModal = bootstrap.Modal.getInstance(editTaskModalEl);
            if (editTaskModal) editTaskModal.hide();
        }

        const addModalEl = document.getElementById(`addSubtaskModal-${taskId}`);
        if (!addModalEl) return console.warn("No addSubtaskModal for", taskId);

        setTimeout(() => {
            const addModal = bootstrap.Modal.getOrCreateInstance(addModalEl);
            addModal.show();
        }, 150);

        addModalEl.addEventListener("hidden.bs.modal", function handler() {
            const editTaskModalEl = document.getElementById(
                `editKanbanModal-${taskId}`
            );
            if (editTaskModalEl) {
                const editTaskModal =
                    bootstrap.Modal.getOrCreateInstance(editTaskModalEl);
                editTaskModal.show();
            }
            addModalEl.removeEventListener("hidden.bs.modal", handler);
        });
    });

    // ================================
    //      EDIT SUBTASK MODAL
    // ================================
    function openEditSubtaskModal(subtaskId, kanbanId, subtask) {
        const modalEl = document.getElementById(`editSubtaskModal-${kanbanId}`);
        if (!modalEl)
            return console.warn(
                "No per-task edit subtask modal found for kanban",
                kanbanId
            );

        const editTaskModalEl = document.getElementById(
            `editKanbanModal-${kanbanId}`
        );
        if (editTaskModalEl) {
            const editTaskModal = bootstrap.Modal.getInstance(editTaskModalEl);
            if (editTaskModal) editTaskModal.hide();
        }

        const idInput = document.getElementById(
            `edit_subtask_id-${kanbanId}`
        );
        if (idInput) idInput.value = subtaskId;
        const title = document.getElementById(`edit_subtask_title-${kanbanId}`);
        if (title) title.value = subtask.title || "";
        const desc = document.getElementById(
            `edit_subtask_description-${kanbanId}`
        );
        if (desc) desc.value = subtask.description || "";

        const notesEl = document.getElementById(
            `edit_subtask_notes-${kanbanId}`
        );
        if (notesEl) notesEl.value = subtask.notes || "";

        const ds = document.getElementById(
            `edit_subtask_date_start-${kanbanId}`
        );
        const de = document.getElementById(
            `edit_subtask_date_end-${kanbanId}`
        );
        if (ds) ds.value = subtask.date_start
            ? subtask.date_start.split(" ")[0]
            : "";
        if (de) de.value = subtask.date_end
            ? subtask.date_end.split(" ")[0]
            : "";

        const pri = document.getElementById(
            `edit_subtask_priority-${kanbanId}`
        );
        if (pri) pri.value = subtask.priority || "normal";
        const st = document.getElementById(`edit_subtask_status-${kanbanId}`);
        if (st) st.value = subtask.status || "todo";

        const form = document.getElementById(`editSubtaskForm-${kanbanId}`);
        if (form) {
            form.action = `/projects/${getProjectId()}/kanban/${kanbanId}/subtasks/${subtaskId}`;
        }

        setTimeout(() => {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }, 150);
    }

    // ================================
    //        DELETE SUBTASK
    // ================================
    function openDeleteSubtask(subtaskId, kanbanId) {
        if (!confirm("Hapus subtask ini?")) return;
        const projectId = getProjectId();

        fetch(
            `/projects/${projectId}/kanban/${kanbanId}/subtasks/${subtaskId}`,
            {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                },
            }
        )
            .then((r) => r.json())
            .then((res) => {
                if (res.success) {
                    showToast("Subtask dihapus", "success");
                    loadSubtasks(kanbanId);
                } else showToast("Gagal hapus subtask", "danger");
            })
            .catch((err) => {
                console.error(err);
                showToast("Error hapus subtask", "danger");
            });
    }

    // ================================
    //       FORM SUBMIT HANDLER
    // ================================
    let isSubmitting = false;

    document.addEventListener(
        "submit",
        async function (e) {
            const form = e.target;
            
/* ============================================================
       CREATE NEW TASK  (PERBAIKAN)
    ============================================================ */
    if (form.id === "createKanbanForm") {
        e.preventDefault();
        e.stopImmediatePropagation();
        if (isSubmitting) return;
        isSubmitting = true;

        const fd = new FormData(form);
        const projectId = getProjectId();

        try {
            const resp = await fetch(`/projects/${projectId}/kanban`, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: fd,
            });

            const json = await resp.json();

            if (json.success) {
                showToast("Task berhasil ditambahkan!", "success");

                // Tutup modal
                const modalEl = document.getElementById(`createKanbanModal-${projectId}`);
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                form.reset();

                // Bersihkan backdrop
                document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
                document.body.classList.remove("modal-open");
                document.body.style.removeProperty("overflow");
                document.body.style.removeProperty("padding-right");

                // Refresh board supaya task langsung muncul
               // Refresh board supaya task langsung muncul
                setTimeout(() => {
                    window.location.reload();
                    goToKanbanTab();
                }, 800);
            } else {
                showToast(json.message || "Gagal menambah task", "danger");
            }

        } catch (err) {
            console.error("Create task error:", err);
            showToast("Error: gagal menambah task!", "danger");
        } finally {
            isSubmitting = false;
        }

        return;
    }
            // EDIT TASK
            if (form.matches('form[id^="editKanbanForm-"]')) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (isSubmitting) return;
                isSubmitting = true;

                const formId = form.id;
                const taskId = formId.replace("editKanbanForm-", "");
                const fd = new FormData(form);
                if (!fd.has("_method")) {
                    fd.append("_method", "PUT");
                }
                const action = `/projects/${getProjectId()}/kanban/${taskId}`;

                try {
                    const resp = await fetch(action, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            Accept: "application/json",
                            "X-Requested-With": "XMLHttpRequest",
                        },
                        body: fd,
                    });

                    if (resp.ok) {
                        await resp.json();
                        const modalEl = document.getElementById(
                            `editKanbanModal-${taskId}`
                        );
                        if (modalEl) {
                            const modal =
                                bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        goToKanbanTab();
                        setTimeout(() => {
                            document
                                .querySelectorAll(".modal-backdrop")
                                .forEach((backdrop) =>
                                    backdrop.remove()
                                );
                            document.body.classList.remove("modal-open");
                            document.body.style.removeProperty(
                                "padding-right"
                            );
                            document.body.style.removeProperty("overflow");
                        }, 300);
                        showToast("Task berhasil diupdate!", "success");
                        setTimeout(() => {
                            window.location.reload();
                            goToKanbanTab();
                        }, 800);
                    } else {
                        try {
                            const json = await resp.json();
                            console.error("Error response:", json);
                            showToast(
                                json.message || "Gagal update task",
                                "danger"
                            );
                        } catch {
                            showToast(
                                "Gagal update task (Status: " + resp.status + ")",
                                "danger"
                            );
                        }
                    }
                } catch (err) {
                    console.error("editTask error", err);
                    showToast("Gagal update task: " + err.message, "danger");
                } finally {
                    isSubmitting = false;
                }
            }

            // ADD SUBTASK
            else if (form.matches('form[id^="addSubtaskForm-"]')) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (isSubmitting) return;
                isSubmitting = true;

                const fd = new FormData(form);
                const kanbanId =
                    fd.get("kanban_id") ||
                    form.querySelector('[name="kanban_id"]')?.value;
                const action = `/projects/${getProjectId()}/kanban/${kanbanId}/subtasks`;

                try {
                    const resp = await fetch(action, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            Accept: "application/json",
                        },
                        body: fd,
                    });
                    const json = await resp.json();
                    if (json.success) {
                        const modalEl = document.getElementById(
                            `addSubtaskModal-${kanbanId}`
                        );
                        const modal =
                            bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        form.reset();
                        showToast("Subtask ditambahkan!", "success");
                        goToKanbanTab();
                        setTimeout(() => {
                            const editTaskModalEl =
                                document.getElementById(
                                    `editKanbanModal-${kanbanId}`
                                );
                            if (editTaskModalEl) {
                                const editTaskModal =
                                    bootstrap.Modal.getOrCreateInstance(
                                        editTaskModalEl
                                    );
                                editTaskModal.show();
                                loadSubtasks(kanbanId);
                            }
                        }, 150);
                    } else {
                        showToast("Gagal menambah subtask", "danger");
                    }
                } catch (err) {
                    console.error("addSubtask error", err);
                    showToast("Gagal menambah subtask", "danger");
                } finally {
                    isSubmitting = false;
                }
            }

            // EDIT SUBTASK
            else if (form.matches('form[id^="editSubtaskForm-"]')) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (isSubmitting) return;
                isSubmitting = true;

                const fd = new FormData(form);
                fd.append("_method", "PUT");

                const kanbanId =
                    fd.get("kanban_id") ||
                    form.querySelector('[name="kanban_id"]')?.value;
                const subtaskId =
                    fd.get("subtask_id") ||
                    form.querySelector('[name="subtask_id"]')?.value;
                const action = `/projects/${getProjectId()}/kanban/${kanbanId}/subtasks/${subtaskId}`;

                try {
                    const resp = await fetch(action, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf,
                            Accept: "application/json",
                        },
                        body: fd,
                    });

                    const json = await resp.json();
                    if (json.success) {
                        const editSubtaskModal = document.getElementById(
                            `editSubtaskModal-${kanbanId}`
                        );
                        if (editSubtaskModal) {
                            const modal =
                                bootstrap.Modal.getInstance(
                                    editSubtaskModal
                                );
                            if (modal) modal.hide();
                        }
                        form.reset();
                        showToast("Subtask berhasil diupdate!", "success");
                        goToKanbanTab();

                        setTimeout(() => {
                            const editTaskModalEl =
                                document.getElementById(
                                    `editKanbanModal-${kanbanId}`
                                );
                            if (editTaskModalEl) {
                                const editTaskModal =
                                    bootstrap.Modal.getOrCreateInstance(
                                        editTaskModalEl
                                    );
                                editTaskModal.show();
                                loadSubtasks(kanbanId);
                            }
                        }, 150);
                    } else {
                        showToast(
                            json.message || "Gagal update subtask",
                            "danger"
                        );
                    }
                } catch (err) {
                    console.error("editSubtask err", err);
                    showToast("Gagal update subtask", "danger");
                } finally {
                    isSubmitting = false;
                }
            }
        },
        {
           
        }
    );

    // ================================
    //      TOGGLE SUBTASK STATUS
    // ================================
    window.toggleSubtaskStatus = function (checkbox) {
        const subtaskId = checkbox.dataset.subtaskId;
        const kanbanId = checkbox.dataset.kanbanId;
        const newStatus = checkbox.checked ? "finished" : "todo";
        const projectId = getProjectId();

        const container = checkbox.closest(
            ".d-flex.align-items-start"
        );
        const titleEl = container?.querySelector(".fw-semibold");
        if (checkbox.checked) {
            titleEl?.classList.add(
                "text-decoration-line-through",
                "text-muted"
            );
        } else {
            titleEl?.classList.remove(
                "text-decoration-line-through",
                "text-muted"
            );
        }

        const badgeContainer = container?.querySelector(".mt-1");
        if (badgeContainer) {
            const badges = badgeContainer.querySelectorAll(".badge");
            if (badges.length > 0) {
                const statusBadge = badges[0];
                if (newStatus === "finished") {
                    statusBadge.className = "badge bg-success";
                    statusBadge.style.fontSize = "0.65rem";
                    statusBadge.textContent = "Finished";
                } else {
                    statusBadge.className = "badge bg-secondary";
                    statusBadge.style.fontSize = "0.65rem";
                    statusBadge.textContent = "To Do";
                }
            }
        }

        updateSubtaskCounter(kanbanId);

        fetch(
            `/projects/${projectId}/kanban/${kanbanId}/subtasks/${subtaskId}/toggle-status`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrf,
                    Accept: "application/json",
                },
                body: JSON.stringify({ status: newStatus }),
            }
        )
            .then((r) => r.json())
            .then((res) => {
                if (res.success) {
                    showToast("Status berhasil diupdate!", "success");
                } else {
                    checkbox.checked = !checkbox.checked;
                    if (checkbox.checked) {
                        titleEl?.classList.add(
                            "text-decoration-line-through",
                            "text-muted"
                        );
                    } else {
                        titleEl?.classList.remove(
                            "text-decoration-line-through",
                            "text-muted"
                        );
                    }
                    showToast("Gagal update status", "danger");
                }
            })
            .catch((err) => {
                console.error(err);
                checkbox.checked = !checkbox.checked;
                if (checkbox.checked) {
                    titleEl?.classList.add(
                        "text-decoration-line-through",
                        "text-muted"
                    );
                } else {
                    titleEl?.classList.remove(
                        "text-decoration-line-through",
                        "text-muted"
                    );
                }
                showToast("Error update status", "danger");
            });
    };

    // ================================
    //   UPDATE SUBTASK AREA (COUNTER)
    // ================================
    function updateKanbanCardSubtasks(kanbanId) {
        const projectId = getProjectId();
        fetch(`/projects/${projectId}/kanban/${kanbanId}/subtasks`)
            .then((r) => r.json())
            .then((data) => {
                const kanbanCard = document.querySelector(
                    `.kanban-item[data-id="${kanbanId}"]`
                );
                if (!kanbanCard) return;

                const subtasksArea = kanbanCard.querySelector(
                    `#subtasks-${kanbanId}`
                );
                if (!subtasksArea) return;

                const subtaskContainer = subtasksArea.querySelector(
                    ".px-2.pb-2"
                );
                if (!subtaskContainer) return;

                subtaskContainer.innerHTML = "";

                if (!data.subtasks || data.subtasks.length === 0) {
                    const counterBtn = kanbanCard.querySelector(
                        `[data-bs-target="#subtasks-${kanbanId}"] span`
                    );
                    if (counterBtn) {
                        counterBtn.innerHTML =
                            '<i class="ti ti-checklist"></i> 0/0 subtasks';
                    }
                    return;
                }

                data.subtasks.forEach((subtask) => {
                    const itemDiv = document.createElement("div");
                    itemDiv.className =
                        "d-flex align-items-start gap-2 mb-2 p-2 bg-light rounded small";

                    const checkDiv = document.createElement("div");
                    checkDiv.className = "form-check";
                    checkDiv.style.minWidth = "20px";
                    const checkbox = document.createElement("input");
                    checkbox.className =
                        "form-check-input subtask-checkbox";
                    checkbox.type = "checkbox";
                    checkbox.checked = subtask.status === "finished";
                    checkbox.dataset.subtaskId = subtask.id;
                    checkbox.dataset.kanbanId = kanbanId;
                    checkbox.onchange = function () {
                        toggleSubtaskStatus(this);
                    };
                    checkDiv.appendChild(checkbox);

                    const contentDiv = document.createElement("div");
                    contentDiv.className = "flex-grow-1";

                    const titleDiv = document.createElement("div");
                    titleDiv.className = "fw-semibold";
                    if (subtask.status === "finished") {
                        titleDiv.classList.add(
                            "text-decoration-line-through",
                            "text-muted"
                        );
                    }
                    titleDiv.textContent = subtask.title;
                    contentDiv.appendChild(titleDiv);

                    if (subtask.description) {
                        const descDiv =
                            document.createElement("div");
                        descDiv.className = "text-muted";
                        descDiv.style.fontSize = "0.75rem";
                        descDiv.textContent = subtask.description;
                        contentDiv.appendChild(descDiv);
                    }

                    const badgesDiv = document.createElement("div");
                    badgesDiv.className = "mt-1";

                    let statusBadge =
                        '<span class="badge bg-secondary" style="font-size: 0.65rem;">To Do</span>';
                    if (subtask.status === "inprogress") {
                        statusBadge =
                            '<span class="badge bg-primary" style="font-size: 0.65rem;">In Progress</span>';
                    } else if (subtask.status === "finished") {
                        statusBadge =
                            '<span class="badge bg-success" style="font-size: 0.65rem;">Finished</span>';
                    }

                    let priorityBadge =
                        '<span class="badge bg-secondary" style="font-size: 0.65rem;">Low</span>';
                    if (subtask.priority === "urgent") {
                        priorityBadge =
                            '<span class="badge bg-danger" style="font-size: 0.65rem;">Urgent</span>';
                    } else if (subtask.priority === "high") {
                        priorityBadge =
                            '<span class="badge bg-warning" style="font-size: 0.65rem;">High</span>';
                    } else if (subtask.priority === "normal") {
                        priorityBadge =
                            '<span class="badge bg-primary" style="font-size: 0.65rem;">Normal</span>';
                    }

                    let durationBadge = "";
                    if (subtask.duration) {
                        durationBadge = `<span class="badge bg-info" style="font-size: 0.65rem;"><i class="ti ti-clock"></i> ${subtask.duration} hari</span>`;
                    }

                    badgesDiv.innerHTML =
                        statusBadge +
                        " " +
                        priorityBadge +
                        " " +
                        durationBadge;
                    contentDiv.appendChild(badgesDiv);

                    if (
                        subtask.files &&
                        Array.isArray(subtask.files) &&
                        subtask.files.length > 0
                    ) {
                        const filesWrap =
                            document.createElement("div");
                        filesWrap.className = "mt-1 small";
                        subtask.files.forEach((f) => {
                            const rowFile =
                                document.createElement("div");
                            rowFile.className =
                                "d-flex justify-content-between align-items-center mb-1";
                            const a = document.createElement("a");
                            a.href = `/storage/${f.file_path}`;
                            a.target = "_blank";
                            a.textContent = `📎 ${f.filename}`;
                            a.className = "text-decoration-none";
                            const delBtn =
                                document.createElement("button");
                            delBtn.type = "button";
                            delBtn.className =
                                "btn btn-sm btn-link text-danger p-0 delete-file-btn";
                            delBtn.dataset.fileId = f.id;
                            delBtn.dataset.type = "subtask";
                            delBtn.dataset.kanbanId = kanbanId;
                            delBtn.innerHTML =
                                '<i class="ti ti-trash"></i>';
                            rowFile.appendChild(a);
                            rowFile.appendChild(delBtn);
                            filesWrap.appendChild(rowFile);
                        });
                        contentDiv.appendChild(filesWrap);
                    }

                    itemDiv.appendChild(checkDiv);
                    itemDiv.appendChild(contentDiv);
                    subtaskContainer.appendChild(itemDiv);
                });

                const checkedCount = data.subtasks.filter(
                    (s) => s.status === "finished"
                ).length;
                const totalCount = data.subtasks.length;
                const counterBtn = kanbanCard.querySelector(
                    `[data-bs-target="#subtasks-${kanbanId}"] span`
                );
                if (counterBtn) {
                    counterBtn.innerHTML = `<i class="ti ti-checklist"></i> ${checkedCount}/${totalCount} subtasks`;
                }
            })
            .catch((err) => {
                console.error(
                    "Failed to update kanban card subtasks:",
                    err
                );
            });
    }

    // ================================
    //     DURATION (CREATE / EDIT)
    // ================================
    const createDateStart = document.getElementById("create_date_start");
    const createDateEnd = document.getElementById("create_date_end");
    const createDurationDisplay = document.getElementById(
        "create_duration_display"
    );
    const createDurationValue = document.getElementById(
        "create_duration_value"
    );

    function calculateCreateDuration() {
        if (!createDateStart || !createDateEnd) return;
        const s = createDateStart.value;
        const e = createDateEnd.value;
        if (s && e) {
            const diff = Math.ceil(
                (new Date(e) - new Date(s)) / (1000 * 60 * 60 * 24)
            );
            createDurationValue.value = diff + " hari";
            createDurationDisplay.style.display = "block";
        } else createDurationDisplay.style.display = "none";
    }

    if (createDateStart && createDateEnd) {
        createDateStart.addEventListener("change", calculateCreateDuration);
        createDateEnd.addEventListener("change", calculateCreateDuration);
    }

    document
        .querySelectorAll(
            'input[id^="edit_date_start-"], input[id^="edit_date_end-"]'
        )
        .forEach((inp) => {
            inp.addEventListener("change", (e) => {
                const id = e.target.id.split("-").pop();
                const s = document.getElementById(
                    `edit_date_start-${id}`
                )?.value;
                const eVal = document.getElementById(
                    `edit_date_end-${id}`
                )?.value;
                const out = document.getElementById(
                    `edit_duration-${id}`
                );
                if (s && eVal && out) {
                    const diff = Math.ceil(
                        (new Date(eVal) - new Date(s)) /
                            (1000 * 60 * 60 * 24)
                    );
                    out.value = diff + " hari";
                } else if (out) out.value = "-";
            });
        });

// ================================
    //          DELETE FILE
    // ================================
    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".confirm-delete-file");
        if (!btn) return;

        e.preventDefault();
        e.stopImmediatePropagation();

        const fileId = btn.dataset.fileId;
        const type = btn.dataset.type || "task";
        const kanbanId = btn.dataset.kanbanId || null;
        const modalEl = btn.closest(".modal");
        const modal = bootstrap.Modal.getInstance(modalEl);
        const projectId = getProjectId();

        console.log("🗑️ Deleting file:", { fileId, type, kanbanId, projectId });

        fetch(`/projects/${projectId}/kanban/file/${fileId}`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": csrf,
                Accept: "application/json",
            },
        })
            .then((r) => r.json())
            .then((res) => {
                if (res.success) {
                    showToast("File berhasil dihapus!", "success");

                    if (modal) modal.hide();

                    // Reload subtasks jika diperlukan
                    if (kanbanId && type === "subtask") {
                        setTimeout(() => {
                            if (typeof loadSubtasks === 'function') {
                                loadSubtasks(kanbanId);
                            }
                        }, 300);
                    } else {
                        // Reload page untuk refresh tampilan
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    }
                } else {
                    showToast("Gagal menghapus file!", "danger");
                }
            })
            .catch((err) => {
                console.error(err);
                showToast("Error menghapus file!", "danger");
            });
    });

    // ================================
    //      AUTO REFRESH KANBAN
    // ================================
    function initKanbanBoard() {
        initSortableColumns();
        highlightOverdueTasks();
    }

    function refreshKanbanBoard() {
        if (isDragging) {
            console.log(
                "⏸️ Auto-refresh ditunda karena user sedang drag & drop"
            );
            return;
        }

        const openModal = document.querySelector(".modal.show");
        if (openModal) {
            console.log("⏸️ Auto-refresh ditunda, ada modal terbuka");
            return;
        }

        const board =
            document.querySelector("#kanban-board") ||
            document.querySelector(".row.g-3");
        if (!board) return;

        fetch(window.location.href, {
            headers: {
                "X-Requested-With": "XMLHttpRequest",
            },
        })
            .then((r) => r.text())
            .then((html) => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, "text/html");
                let newBoard =
                    doc.querySelector("#kanban-board") ||
                    doc.querySelector(".row.g-3");

                if (newBoard && board) {
                    board.innerHTML = newBoard.innerHTML;

                    initKanbanBoard();

                    console.log(
                        "🔄 Board refreshed & Sortable reinitialized"
                    );
                }
            })
            .catch((err) =>
                console.error("Auto refresh error:", err)
            );
    }

    setInterval(refreshKanbanBoard, 30000); // 30 detik

    // ================================
    //     PIC TYPE SWITCH HANDLER
    // ================================
    document.addEventListener("change", function (e) {
        // ADD TASK
        if (e.target.id === "add_picType") {
            let v = e.target.value;
            document
                .getElementById("add_picUser_wrap")
                .classList.toggle("d-none", v !== "individual");
            document
                .getElementById("add_picGroup_wrap")
                .classList.toggle("d-none", v !== "group");
        }

        // EDIT TASK
        if (e.target.classList.contains("picTypeSelector")) {
            let id = e.target.dataset.taskId;
            let val = e.target.value;

            document
                .getElementById(`edit_picUser_wrap-${id}`)
                .classList.toggle("d-none", val !== "individual");
            document
                .getElementById(`edit_picGroup_wrap-${id}`)
                .classList.toggle("d-none", val !== "group");
        }
    });
// // =====================================================================
// // CREATE NEW TASK (AJAX) — FINAL & FIXED (NO DUPLICATE EVENT LISTENER)
// // =====================================================================
// if (!window.__KANBAN_CREATE_TASK_BOUND__) {

//     window.__KANBAN_CREATE_TASK_BOUND__ = true;   // 🔒 Anti double binding

//     document.addEventListener("submit", async (e) => {
//         const form = e.target;

//         // Pastikan form yang submit adalah form create task
//         if (form.id !== "createKanbanForm") return;

//         e.preventDefault(); // stop form default submit

//         const fd = new FormData(form);
//         const projectId = getProjectId();

//         try {
//             const resp = await fetch(`/projects/${projectId}/kanban`, {
//                 method: "POST",
//                 headers: {
//                     "X-CSRF-TOKEN": csrf,
//                     Accept: "application/json",
//                     "X-Requested-With": "XMLHttpRequest",
//                 },
//                 body: fd,
//             });

//             const json = await resp.json();

//             if (json.success) {
//                 showToast("Task berhasil ditambahkan!", "success");

//                 // Tutup modal
//                 const modalEl = document.getElementById(`createKanbanModal-${projectId}`);
//                 if (modalEl) {
//                     const modal = bootstrap.Modal.getInstance(modalEl);
//                     if (modal) modal.hide();
//                 }

//                 // Reset form setelah submit
//                 form.reset();

//                 // Hapus backdrop yang tersisa (jika ada)
//                 document.querySelectorAll(".modal-backdrop").forEach(el => el.remove());
//                 document.body.classList.remove("modal-open");
//                 document.body.style.removeProperty("overflow");
//                 document.body.style.removeProperty("padding-right");

//                 // Refresh board supaya task baru muncul
//                 setTimeout(() => {
//                     if (typeof refreshKanbanBoard === "function") {
//                         refreshKanbanBoard();
//                     } else {
//                         window.location.reload();
//                     }
//                 }, 200);

//             } else {
//                 showToast(json.message || "Gagal menambah task", "danger");
//             }

//         } catch (err) {
//             console.error("Create Task Error:", err);
//             showToast("Error: gagal menambah task!", "danger");
//         }
//     });
// }


  // ================================
    //   INIT PERTAMA KALI
    // ================================
    
    
console.log("🚀 Initializing Kanban...");

// Initialize kanban board
initKanbanBoard();

console.log("✅ Kanban initialized");
});

