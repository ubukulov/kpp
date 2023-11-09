<!-- Task Position Cancel Modal -->
<div style="overflow-y: auto !important;" class="modal fade" id="taskPositionCancelModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="taskPositionCancelModalTitle"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="message-text" class="col-form-label">Причина:</label>
                    <textarea class="form-control" id="message-text"></textarea>
                </div>

                <div id="successMessageDiv" class="alert alert-success alert-dismissible fade" role="alert">

                </div>
            </div>
            <input type="hidden" id="container_number" value="">
            <input type="hidden" id="container_task_id" value="">
            <div class="modal-footer">
                <button id="btnTaskPositionCancel" type="button" style="font-size: 18px !important;" class="btn btn-secondary" data-dismiss="modal">Отменить</button>
                <button id="btnTaskPositionSend" type="button" style="font-size: 18px !important;" class="btn btn-primary">Отправить заявку</button>
            </div>
        </div>
    </div>
</div>
