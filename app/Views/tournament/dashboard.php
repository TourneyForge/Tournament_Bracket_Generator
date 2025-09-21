<?= $this->extend('\App\Views\layout') ?>

<?= $this->section('title') ?>Tournament Dashboard<?= $this->endSection() ?>

<?= $this->section('main') ?>


<div class="nav nav-tabs nav-underline d-flex flex-nowrap" role="tablist">
    <a class="nav-link <?= ($navActive == 'all') ? 'active' : '' ?>" id="v-pills-home-tab" href="<?= base_url('tournaments') ?>">Current Tournaments</a>
    <a class="nav-link <?= ($navActive == 'archived') ? 'active' : '' ?>" id="v-pills-profile-tab" href="<?= base_url('tournaments?filter=archived') ?>">Archived Tournaments</a>
    <a class="nav-link <?= ($navActive == 'shared') ? 'active' : '' ?>" id="v-pills-settings-tab" href="<?= base_url('tournaments?filter=shared') ?>">Shared Tournaments</a>
</div>

<div class="card shadow-sm">
    <div class="card-body p-3">
        <h5 class="card-title d-flex justify-content-center">
            <? //= lang('Auth.login') ?>Tournament Dashboard
        </h5>

        <?php if (session('error') !== null): ?>
        <div class="alert alert-danger" role="alert"><?= session('error') ?></div>
        <?php elseif (session('errors') !== null): ?>
        <div class="alert alert-danger" role="alert">
            <?php if (is_array(session('errors'))): ?>
            <?php foreach (session('errors') as $error): ?>
            <?= $error ?>
            <br>
            <?php endforeach ?>
            <?php else: ?>
            <?= session('errors') ?>
            <?php endif ?>
        </div>
        <?php endif ?>

        <?php if (session('message') !== null): ?>
        <div class="alert alert-success" role="alert"><?= session('message') ?></div>
        <?php endif ?>

        <div id="liveAlertPlaceholder"></div>

        <div id="tournamentsTableWrapper">
            <?php echo $table ?>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="deleteConfirm" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h4>Are you sure you want to delete this tournament "<span class="tournament-name"></span>"?</h4>
                <h5 class="text-danger">This action cannot be undone!</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="resetConfirm" data-bs-keyboard="false" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="resetModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Are you sure you want to reset this tournament "<span class="tournament-name"></span>"?</h5>
                <h5 class="text-danger">This action cannot be undone!</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmReset">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tournamentSettings" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Tournament Settings</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form id="tournamentForm" class="dashboard-settings needs-validation" method="POST" endtype="multipart/form-data">
                    <div class="settings-update">
                        <?= $settingsBlock ?>
                    </div>

                    <div id="audio-settings-panel">
                        <?= $audioSettingsBlock ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="submit">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shareModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">Share Tournament "<span class="tournament-name"></span>"</h1><br />

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <label class="mb-3">Select a sharing option, generate url, and click save to share the
                    tournament.</label>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="usertype" id="share-public" value="<?= SHARE_TO_PUBLIC ?>" data-target="Public">
                    <label class="form-check-label" for="share-public">
                        Public on the web
                        <div class="form-text">Anyone on the internet can find and access. (No sign-in required). <br>Note: This setting doesn't determine visibility on the Tournament Gallery.<br>If visibility setting is enabled for the tournament, the most recent generated public url will be added to the Public URL field associated with the tournament on the Gallery.</div>
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="usertype" id="share-guest" value="<?= SHARE_TO_EVERYONE ?>" data-target="Anyone">
                    <label class="form-check-label" for="share-guest">
                        Anyone with the link
                        <div class="form-text">Anyone who has the link can access. (Signin required) </div>
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="radio" name="usertype" id="share-users" value="<?= SHARE_TO_USERS ?>" data-target="Private" checked>
                    <label class="form-check-label" for="share-users">
                        Private
                        <div class="form-text">Only people explicitly granted</div>
                    </label>
                </div>
                <form id="privateUserTagsInputForm" method="post" class="form-horizontal">
                    <div class="private-users input-group row gy-2 gx-3 align-items-center mb-3">
                        <label for="userTagsInput" class="form-label col-form-label col-sm-4">Share with</label>
                        <div class="col-sm-8">
                            <input type="text" id="userTagsInput" name="private-users" class="form-control" placeholder="Enter registered username(s)" required />
                            <div class="hint form-text">Enter registered username(s) - (no need to prefix @ symbol)</div>
                        </div>
                    </div>
                </form>
                <div class="input-group row gy-2 gx-3 align-items-center mb-3">
                    <label class="form-label col-form-label col-sm-4">Access: <span class="selected-target">Private</span></label>
                    <div class="col-auto">
                        <select class="form-select" name="permission" aria-label="Access permission">
                            <option value="<?= SHARE_PERMISSION_EDIT ?>">Can edit</option>
                            <option value="<?= SHARE_PERMISSION_VIEW ?>" selected>Can view</option>
                        </select>
                    </div>
                    <div id="sharePermissionHelpBlock" class="form-text"></div>
                </div>
                <div class="share-url row mb-3 d-flex flex-row-reverse">
                    <div class="col-auto input-group">
                        <input type="text" class="form-control" id="tournamentURL" value="" aria-label="Tournament URL" aria-describedby="urlCopy" readonly>
                        <button class="btn btn-outline-secondary input-group-text" type="button" id="urlCopyBtn" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-content="Link Copied!">Copy</button>
                    </div>
                    <div class="col-auto">
                        <a href="javascript:;" onClick="generateURL()">Generate URL</a>
                    </div>
                    <div class="dropdown">
                        <a class="btn btn-primary" data-bs-target="#shareHistoryModal" data-bs-toggle="modal">
                            View Sharing(s)
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmShare">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="shareHistoryModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">Tournament "<span class="tournament-name"></span>"
                    Sharings</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="share-settings table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col" class="resizable">URL</th>
                                <th scope="col">Created</th>
                                <th scope="col">Modified</th>
                                <th scope="col">Accessiblility</th>
                                <th scope="col">Permission</th>
                                <th scope="col">Status</th>
                                <th scope="col">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="close-share-history btn btn-secondary" data-bs-target="#shareModal" data-bs-toggle="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="purgeShareConfirm" data-bs-keyboard="false" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Are you sure you want to purge this link?</h5>
                <h5 class="text-danger">This action cannot be undone!</h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="confirmPurgeShare">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewLogModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="logModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">Tournament [<span class="tournament-name"></span>] - History of Actions</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="buttons d-flex justify-content-end">
                    <a href="<?= base_url('tournaments/export-logs') ?>" class="btn btn-export-logs btn-success ms-2"><i class="fa-solid fa-file-csv"></i> Export</a>
                </div>
                <div class="table-responsive">
                    <table id="logActionsTable" class="action-history table align-middle">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">
                                    <label for="logUserFilter">User:</label>
                                    <select id="logUserFilter" class="form-select form-select-sm">
                                        <option value="">All Users</option>
                                    </select>
                                </th>
                                <th scope="col">
                                    <label for="actionTypeFilter">Action Type:</label>
                                    <select id="actionTypeFilter" class="form-select form-select-sm">
                                        <option value="">All Types</option>
                                        <option value="Mark Winner">Mark Winner</option>
                                        <option value="Unmark Winner">Unmark Winner</option>
                                        <option value="Change Participant">Change Participant</option>
                                        <option value="Add Participant">Add Participant</option>
                                        <option value="Remove Participant">Remove Participant</option>
                                        <option value="Delete Bracket">Delete Bracket</option>
                                        <option value="Reset">Reset</option>
                                        <option value="Vote">Vote</option>
                                    </select>
                                </th>
                                <th scope="col">Description</th>
                                <th scope="col">Time</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="bulkActionConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="bulkActionConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="resetModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="confirm-msg mb-3 text-center"></div>
                <div class="input-wrapper mb-3 d-flex justify-content-center"></div>
                <h5 class="mt-4 text-center">
                    Are you sure you want to proceed?<br />
                    <span class="text-danger">This action cannot be undone!</span>
                </h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dismiss</button>
                <button type="button" class="btn btn-danger" id="confirmBulkActionBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div id="processingMessage" class="overlay d-none">
    <div class="snippet p-3 .bg-light" data-title="dot-elastic">
        <p>Processing, please wait...</p>
        <div class="stage">
            <div class="dot-elastic"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('pageScripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.inputmask/5.0.8/jquery.inputmask.min.js" integrity="sha512-efAcjYoYT0sXxQRtxGY37CKYmqsFVOIwMApaEbrxJr4RwqVVGw8o+Lfh/+59TU07+suZn1BWq4fDl5fdgyCNkw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/typeahead.js/0.11.1/typeahead.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.1/js/bootstrapValidator.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.4/js/jquery.dataTables.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script> -->
<!-- Popperjs -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha256-BRqBN7dYgABqtY9Hd4ynE+1slnEw+roEPFzQ7TRRfcg=" crossorigin="anonymous"></script>
<!-- Tempus Dominus JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>

<script src="/js/tournament.js"></script>
<script src="/js/participants.js"></script>

<script type="text/javascript">
var users_json = '<?= json_encode($users) ?>';
var tournamentsTable = null;
var datatableRows;
var actionLogsTable = null;
var actionLogsTableRows;
var availability_start_changed = false;

//get data pass to json
var task = new Bloodhound({
    datumTokenizer: Bloodhound.tokenizers.obj.whitespace("username"),
    queryTokenizer: Bloodhound.tokenizers.whitespace,
    prefetch: {
        url: apiURL + '/tournaments/fetchUsersList',
        filter: function(list) {
            return $.map(list, function(username) {
                return {
                    name: username
                };
            });
        }
    },
    local: jQuery.parseJSON(users_json), //you can use json type
    // remote: {
    //     url: apiURL + '/tournaments/fetchUsersList',
    //     prepare: function(query, settings) {
    //         settings.type = 'POST';
    //         settings.contentType = 'application/json';
    //         settings.data = JSON.stringify({
    //             query: query
    //         });
    //         console.log(query);
    //         return settings;
    //     },
    //     transform: function(response) {
    //         // Process the response to fit the expected format if needed
    //         return response;
    //     }
    // }
});

task.initialize();

$(document).ready(function() {
    $('[data-bs-toggle="tooltip"]').tooltip();

    const linkedPicker1Element = document.getElementById('startAvPicker');
    const linked1 = new tempusDominus.TempusDominus(linkedPicker1Element, {
        'localization': {
            format: 'yyyy-MM-dd HH:mm'
        },
        restrictions: {
            minDate: new Date(),
        },
    });
    const linked2 = new tempusDominus.TempusDominus(document.getElementById('endAvPicker'), {
        useCurrent: false,
        'localization': {
            format: 'yyyy-MM-dd HH:mm'
        }
    });

    //using event listeners
    linkedPicker1Element.addEventListener('change.td', (e) => {
        let startDate = e.detail.date
        let endDate = new Date(document.getElementById('endAvPickerInput').value)

        if (startDate > endDate) {
            console.log("end date invalid")
        }

        linked2.updateOptions({
            restrictions: {
                minDate: e.detail.date,
            },
        });
    });

    //using subscribe method
    const subscription = linked2.subscribe('change.td', (e) => {
        linked1.updateOptions({
            restrictions: {
                maxDate: e.date,
            },
        });
    });

    $('#confirmReset').on('click', function() {
        const tournament_id = resetModal.getAttribute('data-id');
        $.ajax({
            type: "GET",
            url: apiURL + '/tournaments/' + tournament_id + '/clear',
            success: function(result) {
                $('#resetConfirm').modal('hide');
                setTimeout(() => {
                    alert("Brackets was cleared successfully.")
                }, 500);;
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    });

    <?php if ($navActive == 'shared'): ?>
    <?php if ($shareType == 'wh'): ?>
    var orderFalseColumns = [2, 3, 4, 5, 6]
    <?php else: ?>
    var orderFalseColumns = [2, 3, 4, 6]
    <?php endif ?>

    tournamentsTable = $('#tournamentTable').DataTable({
        "searching": true,
        "processing": true,
        "ajax": {
            "url": apiURL + '/tournaments/get-list' + window.location.search,
            "type": "POST",
            "dataSrc": "",
            "data": function(d) {
                d.search_tournament = $('#tournamentSearchInputBox').val();
                d.type = $('#typeFilter').val();
                d.evaluation_method = $('#evaluationFilter').val();
                d.status = $('#stautsFilter').val();
                d.accessibility = $('#accessibilityFilter').val();
                d.created_by = $('#userByFilter').val();
            }
        },
        "order": [
            [0, "asc"]
        ], // Initial sorting by the first column ascending
        "paging": true, // Enable pagination
        scrollX: true,
        "columnDefs": [{
            "orderable": false,
            "targets": orderFalseColumns
        }],
        // Add custom initComplete to initialize select all checkbox
        "initComplete": function(settings, json) {
            datatableRows = tournamentsTable.rows({
                'search': 'applied'
            }).nodes();

            initCollapseActions(datatableRows)

            $('#typeFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            $('#evaluationFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            $('#stautsFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            $('#accessibilityFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            $('#userByFilter').on('change', function() {
                tournamentsTable.ajax.reload(function() {
                    // Re-initialize tooltips after the table reloads
                    document.querySelectorAll('span.tooltip-span').forEach((element) => {
                        new bootstrap.Tooltip(element);
                    });
                });
            });

            <?php if ($navActive == 'shared' && $shareType == 'wh'): ?>
            var nameColumns = $('td[data-label="name"] span', datatableRows)
            var names = []
            nameColumns.each((i, element) => {
                if (!names[element.dataset.id]) {
                    var option = $(`<option value="${element.dataset.id}">${element.textContent}</option>`)
                    $('#userByFilter').append(option)

                    names[element.dataset.id] = element.textContent.trim()
                }
            })
            <?php endif ?>

            /** Display processing message of response is too long over 1s */
            let requestCompleted = false;

            // Set a timeout to check if the request exceeds the time limit
            const timeout = () => {
                requestCompleted = false
                setTimeout(() => {
                    if (!requestCompleted) {
                        console.warn("The request took too long!");
                        $('#beforeProcessing').removeClass('d-none')
                        // You can also abort the request here if needed
                        // xhr.abort(); // Uncomment if you implement an XMLHttpRequest
                    }
                }, 1000);
            }

            $('#tournamentTable').on('preXhr.dt', function() {
                // $('#beforeProcessing').removeClass('d-none')
                timeout();
            });

            // Hide custom loading overlay after reload
            $('#tournamentTable').on('xhr.dt', function() {
                requestCompleted = true; // Mark the request as completed
                clearTimeout(timeout); // Clear the timeout
                $('#beforeProcessing').addClass('d-none')
            });
        },
        "columns": [{
                "data": null,
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    return meta.row + 1; // Display index number
                }
            },
            {
                "data": "name",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    <?php if ($shareType == 'wh'): ?>
                    return `<a href="${window.location.pathname}/shared/${row.token}">${row.name}</a>`
                    <?php else: ?>
                    let tournament_id = (row.tournament_id) ? row.tournament_id : row.id
                    return `<a href="${window.location.pathname}/${tournament_id}/view">${row.name}</a>`
                    <?php endif; ?>
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'name');
                }
            },
            {
                "data": "type",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    var type = 'Single'
                    if (row.type == <?= TOURNAMENT_TYPE_DOUBLE ?>) {
                        type = "Double"
                    }

                    if (row.type == <?= TOURNAMENT_TYPE_KNOCKOUT ?>) {
                        type = "Knockout"
                    }

                    return type;
                }
            },
            {
                "data": "evaluation_method",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    var type = 'Manual'
                    if (row.evaluation_method == "<?= EVALUATION_METHOD_VOTING ?>") {
                        type = "Voting"
                    }

                    return type;
                }
            },
            {
                "data": "status",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    var status = 'In progress'
                    if (row.status == <?= TOURNAMENT_STATUS_NOTSTARTED ?>) {
                        status = 'Not started'
                    }

                    if (row.status == <?= TOURNAMENT_STATUS_COMPLETED ?>) {
                        status = 'Completed'
                    }

                    if (row.status == <?= TOURNAMENT_STATUS_ABANDONED ?>) {
                        status = 'Abandoned'
                    }

                    return status;
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'status');
                }
            },
            <?php if ($shareType == 'wh'): ?> {
                "data": null,
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    let bsTitle = 'You can view the tournament brackets.'
                    let btnText = 'Can View'
                    if (row.permission == '<?= SHARE_PERMISSION_EDIT ?>') {
                        bsTitle = 'You can view and execute actions on the tournament brackets. Note that actions are logged for tracking purposes.'
                        btnText = 'Can Edit'
                    }

                    return `
                    <span class="d-inline-block" data-bs-toggle="tooltip" data-bs-title="${bsTitle}">
                        <button class="btn" type="button" disabled>${btnText}</button>
                    </span>
                    `
                }
            }, {
                "data": null,
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    return `
                    <span class="d-inline-block tooltip-span" data-bs-toggle="tooltip" data-bs-title="${row.email ? row.email : row.username}" data-id="${row.user_id}">
                        ${row.username}
                    </span>
                    `
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'name');
                }
            },
            {
                "data": "access_time",
                "className": "text-center",
            }
            <?php else: ?> {
                "data": "created_at",
                "className": "text-center",
            },
            {
                "data": null,
                "render": function(data, type, row, meta) {
                    let tournament_id = (row.tournament_id) ? row.tournament_id : row.id
                    return `
                        <div class="btn-groups list-group">
                        <button class="btn text-start collapse-actions-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActions-${row.id}" aria-expanded="false" aria-controls="collapseActions-${row.id}">
                            <i class="fa-solid fa-plus"></i> View Actions
                        </button>
                        <div class="collapse" id="collapseActions-${row.id}">
                            <div class="card card-body p-3">
                                <a href="javascript:;" class="rename" data-id="${tournament_id}" onclick="renameTournament(this)"><?= lang('Button.rename') ?></a>
                                <a href="javascript:;" class="reset" data-id="${tournament_id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#resetConfirm"><?= lang('Button.reset') ?></a>
                                <a href="javascript:;" class="delete" data-id="${tournament_id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#deleteConfirm"><?= lang('Button.delete') ?></a>
                                <a href="javascript:;" class="change-status" data-id="${tournament_id}" data-status="${row.status}" onclick="changeStatus(event)"><?= lang('Button.changeStatus') ?></a>
                                <a href="javascript:;" class="change-settings" data-id="${tournament_id}" onclick="changeSettings(event)"><?= lang('Button.settings') ?></a>
                                <a href="javascript:;" class="share" data-id="${tournament_id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#shareModal"><?= lang('Button.share') ?></a>
                                <a href="javascript:;" class="view-log" data-id="${tournament_id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#viewLogModal"><?= lang('Button.viewLog') ?></a>
                            </div>
                        </div>
                    </div>

                    <a href="javascript:;" class="save visually-hidden" data-id="${tournament_id}" data-status="${row.status}" onClick="saveChange(event)">Save</a>
                    <a href="javascript:;" class="save visually-hidden" data-id="${tournament_id}" data-status="${row.status}" onClick="cancelUpdateTorunament(this)">Cancel</a>
                    `;
                }
            }
            <?php endif; ?>

        ],
        "createdRow": function(row, data, dataIndex) {
            // Add a custom attribute to the row
            let tournament_id = (data.tournament_id) ? data.tournament_id : data.id
            $(row).attr('data-id', tournament_id); // Adds a data-id attribute with the row's ID
        }
    });
    <?php else: ?>
    tournamentsTable = $('#tournamentTable').DataTable({
        "searching": true,
        "processing": true,
        "ajax": {
            "url": apiURL + '/tournaments/get-list' + window.location.search,
            "type": "POST",
            "dataSrc": "",
            "data": function(d) {
                d.user_id = <?= (auth()->user()) ? auth()->user()->id : 0 ?>; // Include the user_id parameter
                d.search_tournament = $('#tournamentSearchInputBox').val();
                d.type = $('#typeFilter').val();
                d.evaluation_method = $('#evaluationFilter').val();
                d.status = $('#stautsFilter').val();
            }
        },
        "order": [
            [1, "asc"]
        ], // Initial sorting by the first column ascending
        "paging": true, // Enable pagination
        scrollX: true,
        "columnDefs": [{
            "orderable": false,
            "targets": [0, 3, 4, 5, 9, 11]
        }],
        // Add custom initComplete to initialize select all checkbox
        "initComplete": function(settings, json) {
            datatableRows = tournamentsTable.rows({
                'search': 'applied'
            }).nodes();

            // Add a select all checkbox to the header
            $('#selectAllCheckbox').on('click', function() {
                $('input[type="checkbox"]', datatableRows).prop('checked', this.checked);
            });

            $('#typeFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            $('#evaluationFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            $('#stautsFilter').on('change', function() {
                tournamentsTable.ajax.reload()
            });

            initCollapseActions(datatableRows)

            /** Display processing message of response is too long over 1s */
            let requestCompleted = false;

            // Set a timeout to check if the request exceeds the time limit
            const timeout = () => {
                requestCompleted = false
                setTimeout(() => {
                    if (!requestCompleted) {
                        console.warn("The request took too long!");
                        $('#beforeProcessing').removeClass('d-none')
                        // You can also abort the request here if needed
                        // xhr.abort(); // Uncomment if you implement an XMLHttpRequest
                    }
                }, 1000);
            }

            $('#tournamentTable').on('preXhr.dt', function() {
                // $('#beforeProcessing').removeClass('d-none')
                timeout();
            });

            // Hide custom loading overlay after reload
            $('#tournamentTable').on('xhr.dt', function() {
                requestCompleted = true; // Mark the request as completed
                clearTimeout(timeout); // Clear the timeout
                $('#beforeProcessing').addClass('d-none')
            });
        },
        "columns": [{
                "data": null, // The 'null' data property is used because this column doesn't have a specific data field
                "className": "text-center",
                "render": function(data, type, row) {
                    // Render a checkbox with a value equal to the ID of the row
                    return '<input type="checkbox" class="item-checkbox row-checkbox" value="' + row.id + '">';
                },
                "orderable": false // Disable ordering for the checkbox column
            },
            {
                "data": null,
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    return meta.row + 1; // Display index number
                }
            },
            {
                "data": "name",
                "render": function(data, type, row, meta) {
                    return `<a href="${window.location.pathname}/${row.id}/view">${row.name}</a>`
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'name');
                }
            },
            {
                "data": "type",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    var type = 'Single'
                    if (row.type == <?= TOURNAMENT_TYPE_DOUBLE ?>) {
                        type = "Double"
                    }

                    if (row.type == <?= TOURNAMENT_TYPE_KNOCKOUT ?>) {
                        type = "Knockout"
                    }

                    return type;
                }
            },
            {
                "data": "evaluation_method",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    var type = 'Manual'
                    if (row.evaluation_method == "<?= EVALUATION_METHOD_VOTING ?>") {
                        type = "Voting"
                    }

                    return type;
                }
            },
            {
                "data": "status",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    var status = 'In progress'
                    if (row.status == <?= TOURNAMENT_STATUS_NOTSTARTED ?>) {
                        status = 'Not started'
                    }

                    if (row.status == <?= TOURNAMENT_STATUS_COMPLETED ?>) {
                        status = 'Completed'
                    }

                    if (row.status == <?= TOURNAMENT_STATUS_ABANDONED ?>) {
                        status = 'Abandoned'
                    }

                    return status;
                },
                "createdCell": function(td, cellData, rowData, row, col) {
                    $(td).attr('data-label', 'status');
                }
            },
            {
                "data": "participants_count",
                "className": "text-center",
            },
            {
                "data": "available_start",
                "className": "text-center",
            },
            {
                "data": "available_end",
                "className": "text-center",
            },
            {
                "data": "public_url",
                "className": "text-center",
                "render": function(data, type, row, meta) {
                    if (row.public_url) {
                        return `<div class="col-auto input-group">
                                    <input type="text" class="form-control" id="tournamentURL_${row.id}" value="${row.public_url}" aria-label="Tournament URL" aria-describedby="urlCopy" readonly="">
                                    <button class="btn btn-outline-secondary input-group-text btnCopy" data-copyid="tournamentURL_${row.id}" type="button" data-toggle="popover" data-trigger="focus" data-placement="top" data-content="Link Copied!" onclick="copyClipboard('tournamentURL_${row.id}')">Copy</button>
                                </div>
                                `
                    } else {
                        return ''
                    }
                }
            },
            {
                "data": "created_at",
                "className": "text-center",
            },
            {
                "data": null,
                "render": function(data, type, row, meta) {
                    let html = `<a href="javascript:;" class="archive" data-id="${row.id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#archiveConfirmModal"><?= lang('Button.archive') ?></a>`
                    if (row.archive == 1)
                        html = `<a href="javascript:;" class="restore" data-id="${row.id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#restoreConfirmModal"><?= lang('Button.restore') ?></a>`
                    return `
                        <div class="btn-groups list-group">
                        <button class="btn text-start collapse-actions-btn" type="button" data-bs-toggle="collapse" data-bs-target="#collapseActions-${row.id}" aria-expanded="false" aria-controls="collapseActions-${row.id}">
                            <i class="fa-solid fa-plus"></i> View Actions
                        </button>
                        <div class="collapse" id="collapseActions-${row.id}">
                            <div class="card card-body p-3">
                                <a href="javascript:;" class="rename" data-id="${row.id}" onclick="renameTournament(this)"><?= lang('Button.rename') ?></a>
                                <a href="javascript:;" class="reset" data-id="${row.id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#resetConfirm"><?= lang('Button.reset') ?></a>
                                ${html}
                                <a href="javascript:;" class="delete" data-id="${row.id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#deleteConfirm"><?= lang('Button.delete') ?></a>
                                <a href="javascript:;" class="change-status" data-id="${row.id}" data-status="${row.status}" onclick="changeStatus(event)"><?= lang('Button.changeStatus') ?></a>
                                <a href="javascript:;" class="change-settings" data-id="${row.id}" onclick="changeSettings(event)"><?= lang('Button.settings') ?></a>
                                <a href="javascript:;" class="share" data-id="${row.id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#shareModal"><?= lang('Button.share') ?></a>
                                <a href="javascript:;" class="view-log" data-id="${row.id}" data-name="${row.name}" data-bs-toggle="modal" data-bs-target="#viewLogModal"><?= lang('Button.viewLog') ?></a>
                            </div>
                        </div>
                    </div>
                    <a href="javascript:;" class="save visually-hidden" data-id="${row.id}" data-status="${row.status}" onClick="saveChange(event)">Save</a>
                    <a href="javascript:;" class="save visually-hidden" data-id="${row.id}" data-status="${row.status}" onClick="cancelUpdateTorunament(this)">Cancel</a>
                    `;
                }
            }
        ],
        "createdRow": function(row, data, dataIndex) {
            // Add a custom attribute to the row
            $(row).attr('data-id', data.id); // Adds a data-id attribute with the row's ID
        }
    });
    <?php endif ?>

    tournamentsTable.on('draw.dt', function() {
        document.querySelectorAll('span.tooltip-span').forEach((element, i) => {
            var tooltip = new bootstrap.Tooltip(element)
        })
    })

    actionLogsTable = $('#logActionsTable').DataTable({
        "order": [
            [0, "asc"]
        ], // Initial sorting by the first column ascending
        "paging": true, // Enable pagination
        "searching": true, // Enable search box
        scrollX: true,
        "columnDefs": [{
            "orderable": false,
            "targets": [1, 2, 3]
        }],
    });

    $('#actionTypeFilter').on('change', function() {
        var selectedType = $(this).val().toLowerCase();
        actionLogsTable.columns(2).search(selectedType).draw();
    });

    $('#logUserFilter').on('change', function() {
        var selectedType = $(this).val().toLowerCase();
        actionLogsTable.columns(1).search(selectedType).draw();
    });

    // Individual checkbox functionality
    $('.item-checkbox').change(function() {
        var checked = $('.item-checkbox:checked').length === $('.item-checkbox').length;
        $('#selectAllCheckbox').prop('checked', checked);
    });

    const tournamentSettingsModal = document.getElementById('tournamentSettings');
    if (tournamentSettingsModal) {
        tournamentSettingsModal.addEventListener('shown.bs.modal', event => {
            changeEvaluationMethod(document.getElementById('evaluationMethod'))

            tournamentSettingsModal.querySelectorAll(".read-more-container").forEach(container => {
                adjustReadMore(container)
            })
        })
    }

    const resetModal = document.getElementById('resetConfirm');
    if (resetModal) {
        resetModal.addEventListener('show.bs.modal', event => {
            resetModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));
            const modalTitle = resetModal.querySelector('.modal-body .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');
        })
    }

    const deleteModal = document.getElementById('deleteConfirm');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', event => {
            deleteModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));
            const modalTitle = deleteModal.querySelector('.modal-body .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');
        })
    }

    const shareModal = document.getElementById('shareModal');
    if (shareModal) {
        shareModal.addEventListener('show.bs.modal', event => {
            const base_url = "<?= base_url('tournaments/shared/') ?>";
            const tournament_id = event.relatedTarget.getAttribute('data-id');
            shareModal.setAttribute('data-id', tournament_id);

            const modalTitle = shareModal.querySelector('.modal-header .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');

            document.getElementById('shareHistoryModal').querySelector('.tournament-name').textContent = event.relatedTarget.getAttribute('data-name');
            document.getElementById('shareHistoryModal').querySelector('.close-share-history').dataset.name = event.relatedTarget.getAttribute('data-name');

            if (tournament_id) {
                fetchShareSettings(tournament_id);
            }

            $('#shareHistoryModal .close-share-history').attr('data-id', tournament_id);

            if (document.getElementById('share-users').checked) {
                $('.private-users').show();
                $('#userTagsInput').attr('disabled', false)
            } else {
                $('.private-users').hide();
                $('#userTagsInput').attr('disabled', true)
            }

            $('#userTagsInput').tagsinput('removeAll');

            if (shareModal.querySelector('select[name="permission"]').value == "<?= SHARE_PERMISSION_VIEW ?>") {
                shareModal.querySelector('#sharePermissionHelpBlock').textContent = "User(s) can view the tournament brackets."
            }

            if (shareModal.querySelector('select[name="permission"]').value == "<?= SHARE_PERMISSION_EDIT ?>") {
                shareModal.querySelector('#sharePermissionHelpBlock').innerHTML = 'User(s) can view and execute actions on the tournament brackets. <br/> Note that actions are logged for tracking purposes in the "View Log" feature of the tournament.'
            }

            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]')
            const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(
                popoverTriggerEl))

            $('input[name="usertype"]').on('change', event => {
                shareModal.querySelectorAll('.selected-target')[0].textContent = event.delegateTarget.dataset.target;

                if (document.getElementById('share-users').checked) {
                    $('.private-users').show();
                    $('#userTagsInput').attr('disabled', false)
                } else {
                    $('.private-users').hide();
                    $('#userTagsInput').attr('disabled', true)
                }
            })

            $('select[name="permission"]').on('change', event => {
                if (shareModal.querySelector('select[name="permission"]').value == "<?= SHARE_PERMISSION_VIEW ?>") {
                    shareModal.querySelector('#sharePermissionHelpBlock').textContent = "User(s) can view the tournament brackets."
                }

                if (shareModal.querySelector('select[name="permission"]').value == "<?= SHARE_PERMISSION_EDIT ?>") {
                    shareModal.querySelector('#sharePermissionHelpBlock').innerHTML = 'User(s) can view and execute actions on the tournament brackets. <br/> Note that actions are logged for tracking purposes in the "View Log" feature of the tournament.'
                }
            })

            $('#privateUserTagsInputForm')
                .find('[name="private-users"]')
                // Revalidate the cities field when it is changed
                .change(function(e) {
                    console.log($(e.target).val())
                    $('#privateUserTagsInputForm').bootstrapValidator('revalidateField', 'private-users');
                }).end()
                .bootstrapValidator({
                    framework: 'bootstrap',
                    excluded: ':disabled',
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        "private-users": {
                            validators: {
                                notEmpty: {
                                    message: 'Please select at least one user.'
                                }
                            }
                        },
                    }
                });
        })

    }

    const shareHistoryModal = document.getElementById('shareHistoryModal');
    if (shareHistoryModal) {
        shareHistoryModal.addEventListener('show.bs.modal', event => {

            document.getElementById('shareHistoryModal').querySelectorAll('td .path').forEach((ele, i) => {
                const tooltip = bootstrap.Tooltip.getOrCreateInstance(ele)
            })
        })

    }

    const viewLogModal = document.getElementById('viewLogModal');
    if (viewLogModal) {
        viewLogModal.addEventListener('show.bs.modal', event => {
            viewLogModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));

            drawActionHistoryTable(event.relatedTarget.getAttribute('data-id'));
        })
    }

    const bulkActionConfirmModal = document.getElementById('bulkActionConfirmModal');
    if (bulkActionConfirmModal) {
        bulkActionConfirmModal.addEventListener('show.bs.modal', event => {
            var action = event.relatedTarget.actionname; // Action defined in data-action attribute
            let title = '';
            let action_text = '';
            var modal = $(this);
            modal.find('.modal-body .input-wrapper').empty();

            if (action === 'bulkDelete') {
                title = "Confirm to delete"
                action_text = '<h5>You are about to delete the following selected tournament(s):</h5>';
                action_text += `<h6>Tournament Names: ${event.relatedTarget.names}</h6>`;
            } else if (action === 'bulkReset') {
                title = "Confirm To reset"
                action_text = '<h5>You are about to reset the following selected tournament(s):</h5>';
                action_text += `<h6>Tournament Names: ${event.relatedTarget.names}</h6>`;
            } else if (action === 'bulkStatusUpdate') {
                title = "Confirm to update the status"
                action_text = '<h6>You are about to change the status of the following selected tournament(s):</h6>';
                action_text += `<h6>Tournament Names: ${event.relatedTarget.names}</h6>`;
                modal.find('span.text-danger').remove();
            } else if (action === 'bulkArchive') {
                title = "Confirm to archive"
                action_text = '<h6>You are about to archive the following selected tournament(s):</h6>';
                action_text += `<h6>Tournament Names: ${event.relatedTarget.names}</h6>`;
                modal.find('span.text-danger').remove();
            } else if (action === 'bulkRestore') {
                title = "Confirm to restore"
                action_text = '<h6>You are about to restore the following selected tournament(s):</h6>';
                action_text += `<h6>Tournament Names: ${event.relatedTarget.names}</h6>`;
                modal.find('span.text-danger').remove();
            }

            modal.find('.modal-title').text(title);
            modal.find('.modal-body .confirm-msg').html(action_text);

            // Update confirm button action based on action
            var confirmButton = modal.find('#confirmBulkActionBtn');
            confirmButton.off('click'); // Remove any existing click handlers
            if (action === 'bulkDelete') {
                confirmButton.on('click', bulkDelete)
            } else if (action === 'bulkReset') {
                confirmButton.on('click', bulkReset)
            } else if (action === 'bulkArchive') {
                confirmButton.on('click', bulkArchive)
            } else if (action === 'bulkRestore') {
                confirmButton.on('click', bulkRestore)
            } else if (action === 'bulkStatusUpdate') {
                let status = $('.status-to').val()

                // Create label element
                var label = $('<label class="col-form-label col-auto justify-content-end">Status:</label>');
                // Create select box element
                var selectBox = $('<select class="form-control" id="statusUpdateTo">');
                selectBox.append('<option value="<?= TOURNAMENT_STATUS_NOTSTARTED ?>">Not Started</option>');
                selectBox.append('<option value="<?= TOURNAMENT_STATUS_INPROGRESS ?>">In Progress</option>');
                selectBox.append('<option value="<?= TOURNAMENT_STATUS_COMPLETED ?>">Completed</option>');
                selectBox.append('<option value="<?= TOURNAMENT_STATUS_ABANDONED ?>">Abandoned</option>');

                var selectBoxWrapper = $('<div class="col-auto"></div>')
                selectBoxWrapper.append(selectBox)

                var row = $('<div class="row"></div>')
                row.append(label).append(selectBoxWrapper)

                // Append select box to modal body
                modal.find('.modal-body .input-wrapper').append(row);
                // confirmButton.on('click', bulkStatusUpdate(status));
                confirmButton.on('click', bulkStatusUpdate)
            }
        })
    }

    $('#confirmDelete').on('click', function() {
        const tournament_id = deleteModal.getAttribute('data-id');

        $.ajax({
            type: "get",
            url: `${apiURL}/tournaments/${tournament_id}/delete`,
            success: function(result) {
                const msg = JSON.parse(result).msg;
                alert(msg);
                window.location.href = "/tournaments";
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    });

    $('#submit').on('click', function() {
        const form = document.getElementById('tournamentForm');

        let isValid = true;

        if (document.getElementById('enableAvailability').checked) {
            const currentDate = new Date()
            const startDate = new Date(document.getElementById('startAvPickerInput').value)
            const endDate = new Date(document.getElementById('endAvPickerInput').value)

            let startDateInput = document.getElementById('startAvPickerInput')
            let endDateInput = document.getElementById('endAvPickerInput')
            if (!startDateInput.value.trim()) {
                // Trigger validation error for empty readonly field
                document.getElementById('startAvPicker').classList.add("is-invalid");
                startDateInput.addEventListener('change', () => {
                    availability_start_changed = true;
                    if (startDateInput.value.trim()) {
                        document.getElementById('startAvPicker').classList.remove("is-invalid");
                    }
                })
                startDateInput.reportValidity(); // Shows default browser error message
                event.preventDefault(); // Prevent form submission

                isValid = false
            } else {
                startDateInput.addEventListener('change', () => {
                    availability_start_changed = true;
                })
            }

            if (!endDateInput.value.trim()) {
                // Trigger validation error for empty readonly field
                document.getElementById('endAvPicker').classList.add("is-invalid");
                endDateInput.addEventListener('change', () => {
                    if (endDateInput.value.trim()) {
                        document.getElementById('endAvPicker').classList.remove("is-invalid");
                    }
                })
                endDateInput.reportValidity(); // Shows default browser error message
                event.preventDefault(); // Prevent form submission

                isValid = false
            }

            if (startDate > endDate) {
                isValid = false
                document.getElementById('availability-end-date-error').previousElementSibling.classList.add('is-invalid')
                document.getElementById('availability-end-date-error').textContent = "Stop date must be greater than start date."
                document.getElementById('availability-end-date-error').classList.remove('d-none')
            }

            if (availability_start_changed) {
                if (startDate < currentDate) {
                    isValid = false
                    document.getElementById('availability-start-date-error').previousElementSibling.classList.add('is-invalid')
                    document.getElementById('availability-start-date-error').textContent = "You cannot select a past date/time!"
                    document.getElementById('availability-start-date-error').classList.remove('d-none')
                } else {
                    document.getElementById('availability-start-date-error').classList.add('d-none')
                }
            }

            if (endDate < currentDate) {
                isValid = false
                document.getElementById('availability-end-date-error').previousElementSibling.classList.add('is-invalid')
                document.getElementById('availability-end-date-error').textContent = "You cannot select a past date/time!"
                document.getElementById('availability-end-date-error').classList.remove('d-none')
            } else {
                document.getElementById('availability-end-date-error').classList.add('d-none')
            }
        }

        changeEvaluationMethod(document.getElementById('evaluationMethod'))

        $('.audio-setting').each((i, settingBox) => {
            const startTime0 = document.getElementsByName('start[' + i + ']')[0].value;
            const stopTime0 = document.getElementsByName('stop[' + i + ']')[0].value;

            if (parseInt(stopTime0) <= parseInt(startTime0)) {
                document.getElementById('start-time-error-' + i + '').previousElementSibling.classList.add('is-invalid')
                document.getElementById('start-time-error-' + i + '').classList.remove('d-none');
                document.getElementById('stop-time-error-' + i + '').previousElementSibling.classList.add('is-invalid')
                document.getElementById('stop-time-error-' + i + '').classList.remove('d-none');
                isValid = false;
            } else {
                document.getElementById('start-time-error-' + i + '').previousElementSibling.classList.remove('is-invalid')
                document.getElementById('start-time-error-' + i + '').classList.add('d-none');
                document.getElementById('stop-time-error-' + i + '').previousElementSibling.classList.remove('is-invalid')
                document.getElementById('stop-time-error-' + i + '').classList.add('d-none');
            }
        })

        if (!isValid || !form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
            form.classList.add('was-validated');
            return false;
        }

        const values = $('#tournamentForm').serializeArray();
        $('#tournamentForm input[type="checkbox"]:not(:checked)').each(function(i, e) {
            values.push({
                name: e.getAttribute("name"),
                value: false
            });
        });

        const data = Object.fromEntries(values.map(({
            name,
            value
        }) => [name, value]));

        $.ajax({
            url: apiURL + '/tournaments/' + $('#tournamentForm').data('id') + '/update',
            type: "POST",
            data: data,
            beforeSend: function() {
                $('#beforeProcessing').removeClass('d-none')
            },
            success: function(result) {
                $('#beforeProcessing').addClass('d-none')

                var result = JSON.parse(result);
                if (result.error) {
                    // invalid file format.
                    $("#err").html("Invalid File !").fadeIn();
                } else {
                    $('#tournamentSettings').modal('hide');
                    window.location.reload()
                }
            },
            error: function(e) {
                $("#err").html(e).fadeIn();
            }
        });
    });

    $('#urlCopyBtn').on('click', function() {
        copyClipboard("tournamentURL");
    });

    $('#confirmShare').on('click', function() {
        var validator = $("#privateUserTagsInputForm").data("bootstrapValidator");
        validator.validate();

        if (!validator.isValid()) {
            return;
        }

        const tournament_id = shareModal.dataset.id;
        const url = new URL($('#tournamentURL').val());
        var path = url.pathname.split("/");

        $.ajax({
            url: apiURL + '/tournaments/' + tournament_id + '/share',
            type: "POST",
            data: {
                'tournament_id': tournament_id,
                'target': $('input[name="usertype"]:checked').val(),
                'users': $('#userTagsInput').val(),
                'permission': $('select[name="permission"]').val(),
                'token': path[3]
            },
            beforeSend: function() {
                //$("#preview").fadeOut();
                $("#err").fadeOut();
                $('#beforeProcessing').removeClass('d-none');
            },
            success: function(result) {
                $('#shareModal').modal('hide');
                $('#beforeProcessing').addClass('d-none');
                window.location.reload()
            },
            error: function(e) {
                $("#err").html(e).fadeIn();
            }
        });
    });

    $('#confirmPurgeShare').on('click', function() {
        const shareSettingId = $('#purgeShareConfirm').attr('data-id');
        purgeShare(shareSettingId);
    })

    $('input[name="share-type"]').on('change', function(ele) {
        if ($(this).val() == 'wh') {
            window.location = "<?= base_url('tournaments?filter=shared&type=wh') ?>"
        } else {
            window.location = "<?= base_url('tournaments?filter=shared') ?>"
        }
    })

    const archiveModal = document.getElementById('archiveConfirmModal');
    if (archiveModal) {
        archiveModal.addEventListener('show.bs.modal', event => {
            archiveModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));
            const modalTitle = archiveModal.querySelector('.modal-body .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');
        })
    }

    $('#archiveConfirmBtn').on('click', function() {
        const tournament_id = archiveModal.getAttribute('data-id');
        let data = {
            'archive': 1
        }

        $.ajax({
            type: "post",
            url: `${apiURL}/tournaments/${tournament_id}/update`,
            data: data,
            success: function(result) {
                const msg = JSON.parse(result).msg;
                alert(msg);
                window.location.href = "/tournaments";
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    });

    const restoreModal = document.getElementById('restoreConfirmModal');
    if (restoreModal) {
        restoreModal.addEventListener('show.bs.modal', event => {
            restoreModal.setAttribute('data-id', event.relatedTarget.getAttribute('data-id'));
            const modalTitle = restoreModal.querySelector('.modal-body .tournament-name');
            modalTitle.textContent = event.relatedTarget.getAttribute('data-name');
        })
    }

    $('#restoreConfirmBtn').on('click', function() {
        const tournament_id = restoreModal.getAttribute('data-id');
        let data = {
            'archive': 0
        }

        $.ajax({
            type: "post",
            url: `${apiURL}/tournaments/${tournament_id}/update`,
            data: data,
            success: function(result) {
                const msg = JSON.parse(result).msg;
                alert(msg);
                window.location.href = "/tournaments";
            },
            error: function(error) {
                console.log(error);
            }
        }).done(() => {
            setTimeout(function() {
                $("#overlay").fadeOut(300);
            }, 500);
        });
    });

    var elt = $("#userTagsInput");
    elt.tagsinput({
        itemValue: "id",
        itemText: "username",
        typeaheadjs: {
            name: "task",
            displayKey: "username",
            source: task.ttAdapter()
        }
    });
});

const initCollapseActions = (rows) => {
    let myCollapsible = $('.collapse', rows)
    myCollapsible.each((i, item) => {
        item.addEventListener('hide.bs.collapse', event => {
            item.previousElementSibling.innerHTML = `<i class="fa-solid fa-plus"></i> View Actions`
        })
        item.addEventListener('show.bs.collapse', event => {
            myCollapsible.each((ii, e) => {
                if (ii != i) {
                    e.previousElementSibling.innerHTML = `<i class="fa-solid fa-plus"></i> View Actions`
                }
                $(e).collapse('hide')
            })

            item.previousElementSibling.innerHTML = `<i class="fa-solid fa-minus"></i> Hide Actions`
        })
    })
}

const renameTournament = (element) => {
    const nameBox = document.createElement('input');
    const name = $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('td a').eq(0).html();
    nameBox.classList.add('name', 'form-control');
    nameBox.value = name;
    nameBox.setAttribute('data-name-label', name)

    $(`tr[data-id="${element.getAttribute('data-id')}"]`).find('td[data-label="name"]').html(nameBox);
    $(`tr[data-id="${element.getAttribute('data-id')}"]`).find('.btn-groups').addClass('visually-hidden');
    $(`tr[data-id="${element.getAttribute('data-id')}"]`).find('.save').removeClass('visually-hidden');
}

const changeStatus = (event) => {
    const statusBox = document.createElement('select');
    statusBox.classList.add('status', 'form-control');
    const currentStatus = event.target.getAttribute('data-status');
    const currentStatusLabel = $(event.target).parents('tr').find('td[data-label="status"]').text()
    statusBox.setAttribute('data-status-label', currentStatusLabel)

    const statusOptions = {
        '<?= TOURNAMENT_STATUS_NOTSTARTED ?>': 'Not started',
        '<?= TOURNAMENT_STATUS_INPROGRESS ?>': 'In progress',
        '<?= TOURNAMENT_STATUS_COMPLETED ?>': 'Completed',
        '<?= TOURNAMENT_STATUS_ABANDONED ?>': 'Abandoned'
    }
    for (const [key, value] of Object.entries(statusOptions)) {
        let el = document.createElement("option");
        el.textContent = value;
        el.value = key;
        if (key == currentStatus) {
            el.selected = true;
        }
        statusBox.appendChild(el);
    }

    $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find(
        'td[data-label="status"]').html(statusBox);
    $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('.btn-groups')
        .addClass('visually-hidden');
    $(`tr[data-id="${event.target.getAttribute('data-id')}"]`).find('.save')
        .removeClass('visually-hidden');
}

const changeSettings = (event) => {
    const tournament_id = event.target.dataset.id

    $.ajax({
        type: "GET",
        url: apiURL + '/tournaments/' + tournament_id + '/fetch-settings',
        success: function(result) {
            $("#staticBackdropLabel").text(result.tournamentSettings.name + ' Tournament Settings');
            $('#audio-settings-panel').html(result.html);
            $('#audio-settings-panel').html(result.html).promise().done(function() {
                // Once the HTML is loaded, apply the inputmask
                $('#audio-settings-panel').find('input.startAt[type="text"], input.stopAt[type="text"]').each((i, element) => {
                    $(element).inputmask(
                        "99:59:59", {
                            placeholder: "00:00:00",
                            insertMode: false,
                            showMaskOnHover: false,
                            definitions: {
                                '5': {
                                    validator: "[0-5]",
                                    cardinality: 1
                                }
                            }
                        });
                });
            });
            $('#tournamentForm').data('id', tournament_id);

            if (result.tournamentSettings) {
                $('#tournamentTitle').val(result.tournamentSettings.name)
                $('#eliminationType').val(result.tournamentSettings.type)
                if (result.tournamentSettings.type == '<?= TOURNAMENT_TYPE_SINGLE ?>') {
                    $('.single-type-hint').removeClass('d-none')
                    $('.double-type-hint').addClass('d-none')
                } else {
                    $('.double-type-hint').removeClass('d-none')
                    $('.single-type-hint').addClass('d-none')
                }
                changeEliminationType($('#eliminationType'))

                if (result.tournamentSettings.theme) {
                    $('#tournamentTheme').val(result.tournamentSettings.theme)
                }

                $('#description').summernote('destroy');
                $('#description').text(result.tournamentSettings.description)

                $("textarea#description").summernote({
                    callbacks: {
                        onMediaDelete: function(target) {
                            // Handle media deletion if needed
                        },
                        onVideoInsert: function(target) {
                            $(target).wrap('<div class="responsive-video"></div>');
                        },
                        onVideoUpload: function(files) {
                            uploadVideo(files[0]);
                        }

                    }
                });

                if (result.tournamentSettings.visibility == 1) {
                    $('#enableVisibility').attr('checked', true)
                } else {
                    $('#enableVisibility').attr('checked', false)
                }
                if (result.tournamentSettings.availability == 1) {
                    $('#enableAvailability').attr('checked', true);

                    if (result.tournamentSettings.available_start && result.tournamentSettings.available_start != '0000-00-00 00:00:00') {
                        const [startDate, startTime] = result.tournamentSettings.available_start.split(' ');
                        const [startHour, startMinute] = startTime.split(':');
                        $('#startAvPickerInput').val(`${startDate} ${startHour}:${startMinute}`);
                    }

                    if (result.tournamentSettings.available_end && result.tournamentSettings.available_end != '0000-00-00 00:00:00') {
                        const [endDate, endTime] = result.tournamentSettings.available_end.split(' ');
                        const [endHour, endMinute] = endTime.split(':');
                        $('#endAvPickerInput').val(`${endDate} ${endHour}:${endMinute}`);
                    }

                    let startDateInput = document.getElementById('startAvPickerInput')
                    let endDateInput = document.getElementById('endAvPickerInput')
                    if (!startDateInput.value.trim()) {
                        // Trigger validation error for empty readonly field
                        document.getElementById('startAvPicker').classList.add("is-invalid");
                        startDateInput.addEventListener('change', () => {
                            if (startDateInput.value.trim()) {
                                document.getElementById('startAvPicker').classList.remove("is-invalid");
                            }
                        })
                        startDateInput.reportValidity(); // Shows default browser error message
                        event.preventDefault(); // Prevent form submission
                    }

                    if (!endDateInput.value.trim()) {
                        // Trigger validation error for empty readonly field
                        document.getElementById('endAvPicker').classList.add("is-invalid");
                        endDateInput.addEventListener('change', () => {
                            if (endDateInput.value.trim()) {
                                document.getElementById('endAvPicker').classList.remove("is-invalid");
                            }
                        })
                        endDateInput.reportValidity(); // Shows default browser error message
                        event.preventDefault(); // Prevent form submission
                    }
                } else {
                    $('#enableAvailability').attr('checked', false);
                    $('#startAvPickerInput').val('');
                    $('#endAvPickerInput').val('');
                }
                toggleVisibility(document.getElementById('enableVisibility'))
                toggleAvailability(document.getElementById('enableAvailability'))

                if (result.tournamentSettings.score_enabled == 1) {
                    $('#enableScoreOption').attr('checked', true)
                    $('#scorePerBracket').val(result.tournamentSettings.score_bracket)
                } else {
                    $('#enableScoreOption').removeAttr('checked')
                }

                if (result.tournamentSettings.increment_score_enabled == 1) {
                    $('#enableIncrementScore').attr('checked', true)
                    $('#incrementScore').removeAttr('disabled')
                    $('#incrementScore').val(result.tournamentSettings.increment_score)
                } else {
                    $('#enableIncrementScore').removeAttr('checked')
                    $('#incrementScore').attr('disabled', true)
                    $('#incrementScore').val(result.tournamentSettings.increment_score)
                }

                if (result.tournamentSettings.increment_score_type == '<?= TOURNAMENT_SCORE_INCREMENT_PLUS ?>') {
                    $('#scoreOptions #incrementPlus').prop('checked', true)
                    $('#scoreOptions #incrementMultiply').prop('checked', false)
                    $('.enable-increamentscoreoption-hint .plus').removeClass('d-none')
                    $('.enable-increamentscoreoption-hint .multiply').addClass('d-none')
                } else {
                    $('#scoreOptions #incrementPlus').prop('checked', false)
                    $('#scoreOptions #incrementMultiply').prop('checked', true)
                    $('.enable-increamentscoreoption-hint .plus').addClass('d-none')
                    $('.enable-increamentscoreoption-hint .multiply').removeClass('d-none')
                }
                toggleIncrementScore(document.getElementById('enableIncrementScore'))

                toggleScoreOption(document.getElementById('enableScoreOption'))

                if (result.tournamentSettings.shuffle_enabled == 1) {
                    $('#enableShuffle').prop('checked', true)
                } else {
                    $('#enableShuffle').prop('checked', false)
                }
                toggleShuffleParticipants(document.getElementById('enableShuffle'))

                /** Initialize the settings for Evaluation Method */
                if (!result.tournamentSettings.evaluation_method) {
                    result.tournamentSettings.evaluation_method = '<?= EVALUATION_METHOD_MANUAL ?>'
                }
                $('#evaluationMethod').val(result.tournamentSettings.evaluation_method)
                changeEvaluationMethod(document.getElementById('evaluationMethod'))
                if (!result.tournamentSettings.voting_accessibility) {
                    result.tournamentSettings.voting_accessibility = '<?= EVALUATION_VOTING_UNRESTRICTED ?>'
                }
                $('#votingAccessbility').val(result.tournamentSettings.voting_accessibility)
                changeVotingAccessbility(document.getElementById('votingAccessbility'))
                if (result.tournamentSettings.voting_mechanism == undefined) {
                    result.tournamentSettings.voting_mechanism = '<?= EVALUATION_VOTING_MECHANISM_ROUND ?>'
                }
                $('#votingMechanism').val(result.tournamentSettings.voting_mechanism)
                if (result.tournamentSettings.evaluation_method != '<?= EVALUATION_METHOD_MANUAL ?>') {
                    changeVotingMechanism(document.getElementById('votingMechanism'))
                }
                $('#maxVotes').val(result.tournamentSettings.max_vote_value)

                if (result.tournamentSettings.vote_displaying == '<?= VOTE_DISPLAYING_IN_POINT ?>') {
                    $('#voting-settings-panel #voteDisplayPoint').prop('checked', true)
                    $('#voting-settings-panel #voteDisplayPercent').prop('checked', false)
                } else {
                    $('#voting-settings-panel #voteDisplayPoint').prop('checked', false)
                    $('#voting-settings-panel #voteDisplayPercent').prop('checked', true)
                }

                if (result.tournamentSettings.voting_retain == 1) {
                    $('#retainVotesCheckbox').prop('checked', true)
                } else {
                    $('#retainVotesCheckbox').prop('checked', false)
                }

                if (result.tournamentSettings.allow_host_override == 1) {
                    $('#allowHostOverride').prop('checked', true)
                } else {
                    $('#allowHostOverride').prop('checked', false)
                }

                if (result.tournamentSettings.round_duration_combine == 1) {
                    $('#roundDurationCheckbox').prop('checked', true)
                } else {
                    $('#roundDurationCheckbox').prop('checked', false)
                }

                if (result.tournamentSettings.pt_image_update_enabled == 1) {
                    $('#ptImageUpdatePermission').prop('checked', true)
                } else {
                    $('#ptImageUpdatePermission').prop('checked', false)
                }
            }

            if (result.audioSettings) {
                for (const [i, item] of Object.entries(result.audioSettings)) {
                    if (item.type == <?= AUDIO_TYPE_BRACKET_GENERATION ?>) {
                        $('.toggle-audio-settings').eq(<?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>).prop('disabled', true)
                    }
                    if (item.type == <?= AUDIO_TYPE_BRACKET_GENERATION_VIDEO ?>) {
                        $('.toggle-audio-settings').eq(<?= AUDIO_TYPE_BRACKET_GENERATION ?>).prop('disabled', true)
                    }

                    let panel = $('.audio-setting').eq(item.type);
                    panel.find("#toggle-audio-settings-" + item.type).prop(
                        'checked', true);
                    panel.find('.setting').removeClass('visually-hidden');
                    panel.find('input[type="radio"][value="' + item.source +
                        '"]').prop('checked', true);

                    if (item.source == 'f') {
                        panel.find('input[data-source="file"]').attr('disabled', false);

                        if (item.path != '') {
                            panel.find('input[data-source="file"]').attr('required', false);
                            panel.find('.player').removeClass('d-none')
                        }

                        panel.find('input[name="file-path[' + item.type + ']"]').val(item.path);
                        panel.find('.playerSource').attr('src', '/uploads/' + item.path);
                        panel.find('.fileupload-hint').removeClass('d-none');
                        panel.find('.urlupload-hint').addClass('d-none');

                    }
                    if (item.source == 'y') {
                        panel.find('input[data-source="url"]').val(item.url).attr('disabled', false);
                        panel.find('.playerSource').attr('src', '/uploads/' + item.path);
                        panel.find('.fileupload-hint').addClass('d-none');
                        panel.find('.urlupload-hint').removeClass('d-none');
                    }

                    panel.find('.player').load();

                    panel.find('.preview input').attr('disabled', false);

                    let date = new Date(null);
                    date.setSeconds(item.start); // specify value for SECONDS here
                    panel.find('input.startAt[type="text"]').val(date.toISOString().slice(11, 19));
                    panel.find('input.startAt[type="hidden"]').val(item.start);

                    date = new Date(null);
                    date.setSeconds(item.end);
                    panel.find('input.stopAt').val(date.toISOString().slice(11, 19));
                    panel.find('input.stopAt[type="hidden"]').val(item.end);

                    panel.find('input.duration').val(item.duration);
                }

                if (result.tournamentSettings.winner_audio_everyone == 1) {
                    $('#winnerAudioForEveryone').attr('checked', true)
                } else {
                    $('#winnerAudioForEveryone').attr('checked', false)
                }
            }

            $('#tournamentSettings').modal('show');
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

const cancelUpdateTorunament = (element) => {
    const tournament_id = event.target.getAttribute('data-id');
    if ($(event.target).parents('tr').find('.name').length) {
        const name = $(event.target).parents('tr').find('.name').data('name-label');
        const nameElement = document.createElement('a');
        nameElement.href = '<?= base_url('tournaments') ?>/' + tournament_id + '/view';
        nameElement.textContent = name
        $(`tr[data-id="${tournament_id}"]`).find('td[data-label="name"]').html(nameElement);
    }

    if ($(event.target).parents('tr').find('.status').length) {
        const status = $(event.target).parents('tr').find('.status').data('status-label');
        $(`tr[data-id="${tournament_id}"]`).find('td[data-label="status"]').html(status);
    }

    $(`tr[data-id="${tournament_id}"]`).find('.btn-groups').removeClass('visually-hidden');
    $(`tr[data-id="${tournament_id}"]`).find('.save').addClass('visually-hidden');
}

function saveChange() {
    let data = {};
    const tournament_id = event.target.getAttribute('data-id');
    if ($(event.target).parents('tr').find('.name').length > 0)
        data['title'] = $(event.target).parents('tr').find('.name').val();
    if ($(event.target).parents('tr').find('.status').length > 0)
        data['status'] = $(event.target).parents('tr').find('.status').val();

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/${tournament_id}/update`,
        data: data,
        beforeSend: function() {
            $('#beforeProcessing').removeClass('d-none')
        },
        success: function(result) {
            $('#beforeProcessing').addClass('d-none')

            const data = JSON.parse(result).data;

            if (data.title != undefined && data.title != '') {
                const nameElement = document.createElement('a');
                nameElement.href = '<?= base_url('tournaments') ?>/' + tournament_id + '/view';
                nameElement.textContent = data.title
                $(`tr[data-id="${tournament_id}"]`).find('td[data-label="name"]').html(nameElement);
            }

            if (data.status != undefined && data.status != '') {
                let statusLabel = '<?= TOURNAMENT_STATUS_LABELS[TOURNAMENT_STATUS_INPROGRESS] ?>';
                if (data.status == '<?= TOURNAMENT_STATUS_NOTSTARTED ?>')
                    statusLabel = '<?= TOURNAMENT_STATUS_LABELS[TOURNAMENT_STATUS_NOTSTARTED] ?>';
                if (data.status == '<?= TOURNAMENT_STATUS_COMPLETED ?>')
                    statusLabel = '<?= TOURNAMENT_STATUS_LABELS[TOURNAMENT_STATUS_COMPLETED] ?>';
                if (data.status == '<?= TOURNAMENT_STATUS_ABANDONED ?>')
                    statusLabel = '<?= TOURNAMENT_STATUS_LABELS[TOURNAMENT_STATUS_ABANDONED] ?>';

                $(`tr[data-id="${tournament_id}"]`).find('td[data-label="status"]').html(statusLabel);
            }


            $(`tr[data-id="${tournament_id}"]`).find('.btn-groups').removeClass('visually-hidden');
            $(`tr[data-id="${tournament_id}"]`).find('.save').addClass('visually-hidden');
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

function drawActionHistoryTable(tournament_id) {
    $.ajax({
        type: "get",
        url: `${apiURL}/tournaments/${tournament_id}/getActionHistory`,
        success: function(result) {
            result = JSON.parse(result);
            viewLogModal.querySelector('.tournament-name').innerHTML = result.tournament.name

            let tbody = $('.action-history tbody');
            let rows = '<td colspan="4">History was not found.</td>';
            let names = []

            if (result.history) {
                rows = [];
                result.history.forEach((record, i) => {
                    if (!record.name) {
                        if (!record.system_log) {
                            record.name = 'Guest'
                        } else {
                            record.name = 'System - Round Duration'
                        }
                    }

                    rows.push([(i + 1), record.name, record.type, record.description, record.time])

                    if (!names.includes(record.name)) {
                        let option = document.createElement('option')
                        option.textContent = record.name
                        option.value = record.name
                        $('#logUserFilter').append(option)
                        names.push(record.name)
                    }
                })
            }

            actionLogsTable.clear()
            actionLogsTable.rows.add(rows).draw()
            // tbody.html(rows);
            $('#viewLogModal .btn-export-logs').attr('href', '<?= base_url("tournaments/export-logs?tid=") ?>' + result.tournament.id)
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

function fetchShareSettings(tournament_id) {
    $.ajax({
        url: apiURL + '/tournaments/' + tournament_id + '/share',
        type: "GET",
        beforeSend: function() {
            //$("#preview").fadeOut();
            $("#err").fadeOut();
        },
        success: function(result) {
            result = JSON.parse(result);

            $('#shareModal #tournamentURL').val("<?= base_url('/tournaments/shared/') ?>" + result.token);
            if (result.settings.length) {
                let tbody = '';
                result.settings.forEach((item, i) => {
                    let permission = 'View';
                    if (item.permission == "<?= SHARE_PERMISSION_EDIT ?>") permission = 'Edit';

                    let target = 'Private';
                    if (item.target == "<?= SHARE_TO_EVERYONE ?>") target = 'Anyone';
                    if (item.target == "<?= SHARE_TO_PUBLIC ?>") target = 'Public';
                    if (item.target == "<?= SHARE_TO_USERS ?>") target += `<br/>Share with: ${item.private_users}`;

                    tbody += `<tr data-id="${item.id}" data-tournament-id="${item.tournament_id}">
                        <td>${i + 1}</td>
                        <td><div class="input-group"><input class="path" data-bs-toggle="tooltip" data-bs-title="<?= base_url('/tournaments/shared/') ?>${item.token}" id="shareUrl${i}" value="<?= base_url('/tournaments/shared/') ?>${item.token}" readonly/><button data-bs-toggle="tooltip" class="btn btn-outline-secondary copyUrl" data-fid="shareUrl${i}" data-bs-title="Link Copy"><i class="fa fa-copy"></i></button></div></td>
                        <td><span class="date">${item.created_at}</span></td>
                        <td><span class="date modified">${item.created_at == item.updated_at ? '' : item.updated_at}</span></td>
                        <td class="target">${target}</td>
                        <td class="permission">${permission}</td>
                        <td>${item.deleted_at ? 'Purged' : 'Active'}</td>
                        <td class="actions">
                            <div class="btns">
                                <a href="javascript:;" onClick="resetShare(this)">Reset</a><br/>
                                <a href="javascript:;" data-id="${item.id}" onclick="purgeShareConfirm(${item.id})">Purge</a>
                            </div>
                        </td>
                    </tr>`;
                });

                $('table.share-settings tbody').html(tbody);
                $('.copyUrl').click(function() {
                    // Get the text field
                    var shareID = $(this).data('fid');
                    var copyText = document.getElementById(shareID);

                    // Select the text field
                    copyText.select();
                    copyText.setSelectionRange(0, 99999); // For mobile devices

                    // Copy the text inside the text field
                    if (navigator.clipboard) {
                        navigator.clipboard.writeText(copyText.value);
                    } else {
                        document.execCommand('copy');
                    }
                    $(this).attr('data-bs-title', 'Copied!');
                })
                $('.close-share-history').data('id', tournament_id);

            } else {
                $('table.share-settings tbody').html(
                    '<tr><td colspan="8">No share settings found.</td></tr>');
            }
        },
        error: function(e) {
            $("#err").html(e).fadeIn();
        }
    });
}

function purgeShareConfirm(item_id) {
    $('#purgeShareConfirm').attr('data-id', item_id);
    $('#purgeShareConfirm').modal('show')
}

function purgeShare(id) {
    $.ajax({
        type: "GET",
        url: `${apiURL}/tournaments/purge-share/${id}`,
        beforeSend: function() {
            $('#beforeProcessing').removeClass('d-none')
        },
        success: function(result) {
            result = JSON.parse(result);

            $(`tr[data-id="${id}"]`).remove();

            if (!result.shares || result.shares.length < 1) {
                if ($('table.shared-by-me').length) {
                    $(`table.shared-by-me tr[data-id="${result.tournament_id}"]`).remove();
                    $('#shareHistoryModal').modal('hide');
                }
            }

            $('#purgeShareConfirm').modal('hide');
            $('#beforeProcessing').addClass('d-none')
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

function resetShare(ele) {
    let row = $(ele).parents('tr');
    let url = row.find('span.path').html();
    const id = row.data('id');
    const tournament_id = row.data('tournament-id');

    $.ajax({
        type: "GET",
        url: `${apiURL}/tournaments/fetchShareSetting/${id}`,
        success: function(result) {
            const share = JSON.parse(result).share;

            const table = row.parents('table')
            row.find('td.actions .btns').hide()
            table.find('tr.editable').remove()

            let targetHtml = `<form id="privateUserUpdateForm" class="row">
                <div class="col-md-4 col-sm-4">
                    <select class="target form-select" aria-label="Default select example" onchange="changeShareUpdate(this)">
                        <option value="<?= SHARE_TO_PUBLIC ?>" ${share.target == "<?= SHARE_TO_PUBLIC ?>" ? "selected" : ""}>Public</option>
                        <option value="<?= SHARE_TO_EVERYONE ?>" ${share.target == "<?= SHARE_TO_EVERYONE ?>" ? "selected" : ""}>Everyone</option>
                        <option value="<?= SHARE_TO_USERS ?>" ${share.target == "<?= SHARE_TO_USERS ?>" ? "selected" : ""}>Private</option>
                    </select>
                </div>
                <div class="shareEditUsersWrapper col-md-8 col-sm-8" ${share.target == "<?= SHARE_TO_USERS ?>" ? "" : 'style="display: none"'}><input type="text" id="userTagsInputUpdate" name="private-users" class="form-control" placeholder="Enter registered username(s)" required /></div>
                </form>`;

            let permissionHtml = `<select class="permission form-select" aria-label="Default select example" onchange="changeShareUpdate(this)">
                <option value="<?= SHARE_PERMISSION_VIEW ?>" ${share.permission == "<?= SHARE_PERMISSION_VIEW ?>" ? "selected" : ""}>View</option>
                <option value="<?= SHARE_PERMISSION_EDIT ?>" ${share.permission == "<?= SHARE_PERMISSION_EDIT ?>" ? "selected" : ""}>Edit</option>
                </select>`;

            let html = `<tr class="editable" data-id="${id}" data-tournament-id="${tournament_id}">
                <td></td>
                <td><span class="path" data-bs-toggle="tooltip" data-bs-title="<?= base_url('/tournaments/shared/') ?>${share.token}"><?= base_url('/tournaments/shared/') ?>${share.token}</span></td>
                <td colspan="3">${targetHtml}</td>
                <td>${permissionHtml}</td>
                <td colspan="2"><a href="javascript:;" onclick="updateShareSetting(this)">Save</a> <a href="javascript:;" onclick="cancelUpdateSharing(this)">Cancel</a></td>
                </tr>`

            $(html).insertAfter(row);


            $("#userTagsInputUpdate").tagsinput({
                itemValue: "id",
                itemText: "username",
                typeaheadjs: {
                    name: "task",
                    displayKey: "username",
                    source: task.ttAdapter()
                }
            });

            if (share.target == "<?= SHARE_TO_USERS ?>") {
                share.private_users.forEach((user) => {
                    $("#userTagsInputUpdate").tagsinput('add', {
                        id: user.id,
                        username: user.username
                    });
                })
            }

            $('#privateUserUpdateForm')
                .find('[name="private-users"]')
                // Revalidate the cities field when it is changed
                .change(function(e) {
                    $('#privateUserUpdateForm').bootstrapValidator('revalidateField', 'private-users');
                }).end()
                .bootstrapValidator({
                    framework: 'bootstrap',
                    excluded: ':disabled',
                    icon: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    fields: {
                        "private-users": {
                            validators: {
                                notEmpty: {
                                    message: 'Please select at least one user.'
                                }
                            }
                        },
                    }
                });
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
}

function changeShareUpdate(ele) {
    let row = $(ele).parents('tr')
    if (row.find('select.target').val() == "<?= SHARE_TO_USERS ?>") {
        $('.shareEditUsersWrapper').show()
        $('#userTagsInputUpdate').attr('disabled', false)
    } else {
        $('.shareEditUsersWrapper').hide()
        $('#userTagsInputUpdate').attr('disabled', true)
    }
}

function cancelUpdateSharing(ele) {
    const row = $(ele).parents('tr');
    const id = row.data('id')
    row.parents('table').find('tr[data-id="' + id + '"] .actions .btns').show()
    row.remove()
}

function updateShareSetting(ele) {
    var validator = $("#privateUserUpdateForm").data("bootstrapValidator");
    validator.validate();

    if (!validator.isValid()) {
        return;
    }

    let row = $(ele).parents('tr');
    const tournament_id = row.data('tournament-id');
    const share_id = row.data('id');
    const url = new URL(row.find('span.path').html());
    var path = url.pathname.split("/");

    $.ajax({
        url: apiURL + '/tournaments/' + tournament_id + '/share',
        type: "POST",
        data: {
            'tournament_id': tournament_id,
            'target': row.find('td select.target').val(),
            'permission': row.find('td select.permission').val(),
            'token': path[3],
            'users': $('#userTagsInputUpdate').val()
        },
        beforeSend: function() {
            //$("#preview").fadeOut();
            $("#err").fadeOut();
        },
        success: function(result) {
            result = JSON.parse(result);
            row = row.parents('tbody').find('tr[data-id="' + result.share.id + '"]').first()

            if (result.share) {
                let permission = 'View';
                if (result.share.permission == "<?= SHARE_PERMISSION_EDIT ?>") permission = 'Edit';

                let targetHtml = 'Private';
                if (result.share.target == "<?= SHARE_TO_PUBLIC ?>") targetHtml = 'Public';
                if (result.share.target == "<?= SHARE_TO_EVERYONE ?>") targetHtml = 'Everyone';
                if (result.share.target == "<?= SHARE_TO_USERS ?>") targetHtml += `<br/>Share with: ${result.share.private_users}`;
                row.find('td.target').html(targetHtml);

                let permissionHtml = '';
                if (result.share.permission == "<?= SHARE_PERMISSION_VIEW ?>") permissionHtml = 'View';
                if (result.share.permission == "<?= SHARE_PERMISSION_EDIT ?>") permissionHtml = 'Edit';
                row.find('td.permission').html(permissionHtml);

                row.find('td span.modified').html(result.share.updated_at);
            }

            cancelUpdateSharing(ele)
        },
        error: function(e) {
            $("#err").html(e).fadeIn();
        }
    });
}

function generateURL() {
    $.ajax({
        url: apiURL + '/tournaments/' + shareModal.dataset.id + '/generateShareUrl',
        type: "GET",
        beforeSend: function() {
            $("#err").fadeOut();
        },
        success: function(result) {
            if (result.status == 'success') {
                $('#shareModal #tournamentURL').val("<?= base_url('/tournaments/shared/') ?>" + result.token);
            }
        },
        error: function(e) {
            $("#err").html(e).fadeIn();
        }
    });


}

function handleKeyPress(event) {
    tournamentsTable.ajax.reload()
}

function confirmBulkAction() {
    var selectedIds = [];
    var names = '';
    if ($('#selectAllCheckbox').is(":checked")) {
        $(bulkActionConfirmModal).modal('show', {
            'actionname': $(event.currentTarget).data('actionname'),
            'names': "All Tournaments"
        })
    } else {
        $('.item-checkbox:checked').each(function(i, item) {
            selectedIds.push($(this).closest('tr').data('id'));
            names += $(this).closest('tr').find('td a').eq(0).html();

            if (i < ($('.item-checkbox:checked').length - 1)) {
                names += ', '
            }
        });

        if (selectedIds.length) {
            $(bulkActionConfirmModal).modal('show', {
                'actionname': $(event.currentTarget).data('actionname'),
                'names': names
            })
        } else {
            alert('Please select the tournaments.')
        }
    }

}

// handling bulk action (e.g., delete)
function bulkDelete() {
    var selectedIds = [];
    var rows = tournamentsTable.rows({
        'search': 'applied'
    }).nodes();
    $('.item-checkbox:checked', rows).each(function() {
        selectedIds.push($(this).closest('tr').data('id'));
    });

    // Perform your bulk action (e.g., AJAX call to delete items)
    if (!selectedIds) {
        return false
    }

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/bulkDelete`,
        data: {
            id: selectedIds
        },
        success: function(result) {
            result = JSON.parse(result)
            $('.item-checkbox').prop('checked', false);
            appendAlert(result.msg, result.status);
            location.reload();
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
};

function bulkReset() {
    var selectedIds = [];
    var rows = tournamentsTable.rows({
        'search': 'applied'
    }).nodes();
    $('.item-checkbox:checked', rows).each(function() {
        selectedIds.push($(this).closest('tr').data('id'));
    });

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/bulkReset`,
        data: {
            id: selectedIds
        },
        success: function(result) {
            result = JSON.parse(result)
            $('.item-checkbox').prop('checked', false);
            appendAlert(result.msg, result.status);
            location.reload();
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
};

function bulkStatusUpdate() {
    var selectedIds = [];
    var rows = tournamentsTable.rows({
        'search': 'applied'
    }).nodes();
    $('.item-checkbox:checked', rows).each(function() {
        selectedIds.push($(this).closest('tr').data('id'));
    });

    // Perform your bulk action (e.g., AJAX call to delete items)
    if (!selectedIds) {
        return false
    }

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/bulkUpdate`,
        data: {
            id: selectedIds,
            status: $('#statusUpdateTo').val()
        },
        success: function(result) {
            result = JSON.parse(result)
            $('.item-checkbox').prop('checked', false);
            appendAlert(result.msg, result.status);
            location.reload();
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
};

function bulkArchive() {
    var selectedIds = [];
    var rows = tournamentsTable.rows({
        'search': 'applied'
    }).nodes();
    $('.item-checkbox:checked', rows).each(function() {
        selectedIds.push($(this).closest('tr').data('id'));
    });

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/bulkUpdate`,
        data: {
            id: selectedIds,
            archive: true
        },
        success: function(result) {
            result = JSON.parse(result)
            $('.item-checkbox').prop('checked', false);
            appendAlert(result.msg, result.status);
            location.reload();
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
};

function bulkRestore() {
    var selectedIds = [];
    var rows = tournamentsTable.rows({
        'search': 'applied'
    }).nodes();
    $('.item-checkbox:checked', rows).each(function() {
        selectedIds.push($(this).closest('tr').data('id'));
    });

    $.ajax({
        type: "POST",
        url: `${apiURL}/tournaments/bulkUpdate`,
        data: {
            id: selectedIds,
            restore: true
        },
        success: function(result) {
            result = JSON.parse(result)
            $('.item-checkbox').prop('checked', false);
            appendAlert(result.msg, result.status);
            location.reload();
        },
        error: function(error) {
            console.log(error);
        }
    }).done(() => {
        setTimeout(function() {
            $("#overlay").fadeOut(300);
        }, 500);
    });
};
</script>
<?= $this->endSection() ?>

<?= $this->section('pageStyles') ?>
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.5/css/bootstrap-theme.min.css"> -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-tagsinput/0.8.0/bootstrap-tagsinput.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.4/css/jquery.dataTables.css">
<!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.css"> -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
.resizable {
    position: relative;
}

.resizable:after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 5px;
    height: 100%;
    cursor: col-resize;
}

.path {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 200px;
    /* Adjust the width as needed */
    display: inline-block;
}
</style>

<?= $this->endSection() ?>