<!-- Task Position Edit Modal -->
<div style="overflow-y: auto !important;" class="modal fade" id="taskPositionEditModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskPositionEditModalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="new_container_number" class="col-form-label">Номер контейнера:</label>
                    <input class="form-control" id="new_container_number" maxlength="12" minlength="12">
                </div>

                <div id="successMessageDiv2" class="alert alert-success alert-dismissible fade" role="alert">

                </div>
            </div>
            <input type="hidden" id="edit_container_number" value="">
            <input type="hidden" id="edit_container_task_id" value="">
            <div class="modal-footer">
                <button id="btnTaskPositionEdit" type="button" style="font-size: 18px !important;" class="btn btn-secondary" data-dismiss="modal">Отменить</button>
                <button id="btnTaskPositionEditSend" type="button" style="font-size: 18px !important;" class="btn btn-primary">Отправить заявку</button>
            </div>
        </div>
    </div>
</div>
