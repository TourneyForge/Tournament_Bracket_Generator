<div class="container justify-content-center mb-3">
    <div class="input-group mb-3">
        <input type="text" class="form-control" id="tournamentSearchInputBox" value="<?= $searchString ?>" placeholder="Search for a tournament name or find out which tournaments a participant is competing in" onkeyup="handleKeyPress(event)">
        <button class="btn btn-primary" onclick="javascript:;"><i class="fa fa-search"></i> Search</button>
    </div>
</div>

<div class="buttons d-flex justify-content-end">
    <?php if ($navActive == 'shared'): ?>
    <input type="radio" class="btn-check" name="share-type" id="shared-by" value="by" autocomplete="off" <?= ($shareType != 'wh') ? 'checked' : '' ?>>
    <label class="btn" for="shared-by">Shared by me</label>

    <input type="radio" class="btn-check" name="share-type" id="shared-with" value="wh" autocomplete="off" <?= ($shareType == 'wh') ? 'checked' : '' ?>>
    <label class="btn" for="shared-with">Shared with me</label>
    <?php else: ?>
    <?php if ($navActive == 'all') : ?>
    <a class="btn btn-success" href="<?php echo base_url('/tournaments/create') ?>"><i class="fa-sharp fa-solid fa-plus"></i> Create</a>
    <?php endif; ?>

    <div class="dropdown ms-2">
        <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-list-check"></i> Bulk Actions
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkDelete"><?= lang('Button.bulkDelete') ?></a></li>
            <?php if ($navActive == 'archived') : ?>
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkRestore"><?= lang('Button.bulkRestore') ?></a></li>
            <?php else: ?>
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkArchive"><?= lang('Button.bulkArchive') ?></a></li>
            <?php endif; ?>
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkReset"><?= lang('Button.bulkReset') ?></a></li>
            <li><a class="dropdown-item" onclick="confirmBulkAction(this)" data-actionname="bulkStatusUpdate"><?= lang('Button.bulkStatusUpdate') ?></a></li>
        </ul>
    </div>
    <?php endif ?>

    <?php if ($shareType && $shareType == 'wh'): ?>
    <a href="<?= base_url('tournaments/export?filter=' . $navActive) ?>&type=wh" class="btn btn-success ms-2"><i class="fa-solid fa-file-csv"></i> Export</a>
    <?php else: ?>
    <a href="<?= base_url('tournaments/export?filter=' . $navActive) ?>" class="btn btn-success ms-2"><i class="fa-solid fa-file-csv"></i> Export</a>
    <?php endif; ?>
</div>
<div class="table-responsive">
    <?php if ($navActive == 'shared'): ?>
    <?php if ($shareType == 'wh'): ?>
    <table id="tournamentTable" class="table stripe align-middle">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Tournament Name</th>
                <th scope="col">
                    <label for="typeFilter">Type:</label>
                    <select id="typeFilter" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="<?= TOURNAMENT_TYPE_SINGLE ?>">Single</option>
                        <option value="<?= TOURNAMENT_TYPE_DOUBLE ?>">Double</option>
                        <option value="<?= TOURNAMENT_TYPE_KNOCKOUT ?>">Knockout</option>
                    </select>
                </th>
                <th scope="col">
                    <label for="evaluationFilter">Evaluation Method:</label>
                    <select id="evaluationFilter" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="<?= EVALUATION_METHOD_MANUAL ?>">Manual</option>
                        <option value="<?= EVALUATION_METHOD_VOTING ?>">Voting</option>
                    </select>
                </th>
                <th scope="col">
                    <label for="statusFilter">Status:</label>
                    <select id="stautsFilter" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="<?= TOURNAMENT_STATUS_NOTSTARTED ?>">Not started</option>
                        <option value="<?= TOURNAMENT_STATUS_INPROGRESS ?>">In progress</option>
                        <option value="<?= TOURNAMENT_STATUS_COMPLETED ?>">Completed</option>
                        <option value="<?= TOURNAMENT_STATUS_ABANDONED ?>">Abandoned</option>
                    </select>
                </th>
                <th scope="col">
                    <label for="accessibilityFilter">Accessibility:</label>
                    <select id="accessibilityFilter" class="form-select form-select-sm">
                        <option value="">All Accessibility</option>
                        <option value="<?= SHARE_PERMISSION_EDIT ?>">Can Edit</option>
                        <option value="<?= SHARE_PERMISSION_VIEW ?>">Can View</option>
                    </select>
                </th>
                <th scope="col">
                    <label for="userByFilter">Shared By:</label>
                    <select id="userByFilter" class="form-select form-select-sm">
                        <option value="">All Users</option>
                    </select>
                </th>
                <th scope="col">Shared Time</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <?php else: ?>
    <table id="tournamentTable" class="shared-by-me table stripe align-middle">
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Tournament Name</th>
                <th scope="col">
                    <label for="typeFilter">Type:</label>
                    <select id="typeFilter" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="<?= TOURNAMENT_TYPE_SINGLE ?>">Single</option>
                        <option value="<?= TOURNAMENT_TYPE_DOUBLE ?>">Double</option>
                        <option value="<?= TOURNAMENT_TYPE_KNOCKOUT ?>">Knockout</option>
                    </select>
                </th>
                <th scope="col">
                    <label for="evaluationFilter">Evaluation Method:</label>
                    <select id="evaluationFilter" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="<?= EVALUATION_METHOD_MANUAL ?>">Manual</option>
                        <option value="<?= EVALUATION_METHOD_VOTING ?>">Voting</option>
                    </select>
                </th>
                <th scope="col">
                    <label for="statusFilter">Status:</label>
                    <select id="stautsFilter" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="<?= TOURNAMENT_STATUS_NOTSTARTED ?>">Not started</option>
                        <option value="<?= TOURNAMENT_STATUS_INPROGRESS ?>">In progress</option>
                        <option value="<?= TOURNAMENT_STATUS_COMPLETED ?>">Completed</option>
                        <option value="<?= TOURNAMENT_STATUS_ABANDONED ?>">Abandoned</option>
                    </select>
                </th>
                <th scope="col">Created Time</th>
                <th scope="col">Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <?php endif; ?>
    <?php else: ?>
    <table id="tournamentTable" class="table stripe align-middle">
        <thead>
            <tr>
                <th scope="col" width="20px">
                    <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                </th>
                <th scope="col">#<br />&nbsp;</th>
                <th scope="col">Tournament Name<br />&nbsp;</th>
                <th scope="col">
                    <label for="typeFilter">Type:</label>
                    <select id="typeFilter" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="<?= TOURNAMENT_TYPE_SINGLE ?>">Single</option>
                        <option value="<?= TOURNAMENT_TYPE_DOUBLE ?>">Double</option>
                        <option value="<?= TOURNAMENT_TYPE_KNOCKOUT ?>">Knockout</option>
                    </select>
                </th>
                <th scope="col">
                    <label for="evaluationFilter">Evaluation Method:</label>
                    <select id="evaluationFilter" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="<?= EVALUATION_METHOD_MANUAL ?>">Manual</option>
                        <option value="<?= EVALUATION_METHOD_VOTING ?>">Voting</option>
                    </select>
                </th>
                <th scope="col">
                    <label for="statusFilter">Status:</label>
                    <select id="stautsFilter" class="form-select form-select-sm">
                        <option value="">All Status</option>
                        <option value="<?= TOURNAMENT_STATUS_NOTSTARTED ?>">Not started</option>
                        <option value="<?= TOURNAMENT_STATUS_INPROGRESS ?>">In progress</option>
                        <option value="<?= TOURNAMENT_STATUS_COMPLETED ?>">Completed</option>
                        <option value="<?= TOURNAMENT_STATUS_ABANDONED ?>">Abandoned</option>
                    </select>
                </th>
                <th scope="col"># Participants<br />&nbsp;</th>
                <th scope="col">Availability Start<br />&nbsp;</th>
                <th scope="col">Availability End<br />&nbsp;</th>
                <th scope="col">Public URL<br />&nbsp;</th>
                <!-- <th scope="col">                            
                    <label for="userByFilter">Created By:</label>
                    <select id="userByFilter" class="form-select form-select-sm">
                        <option value="">All Users</option>
                    </select>
                </th> -->
                <th scope="col">Created Time<br />&nbsp;</th>
                <th scope="col">Actions<br />&nbsp;</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
    <?php endif; ?>

</div>
<!-- Modal -->
<div class="modal fade" id="archiveConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="archiveConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">Archive this tournament</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Are you sure you want to archive this tournament "<span class="tournament-name"></span>"?</h1>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="archiveConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="restoreConfirmModal" data-bs-keyboard="false" tabindex="-1" aria-labelledby="restoreConfirmModal" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel">Restore this tournament</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h5>Are you sure you want to restore this tournament "<span class="tournament-name"></span>"?</h1>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="restoreConfirmBtn">Confirm</button>
            </div>
        </div>
    </div>
</div>