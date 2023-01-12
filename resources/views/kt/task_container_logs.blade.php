@extends('layouts.app')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="kt">
                @include('partials.kt_header')

                <div class="row">
                    <div class="col-md-6">
                        <a class="btn btn-primary" href="{{ route('kt.kt_operator') }}">Назад</a>
                    </div>

                    <div class="col-md-6 text-right">
                        @if($container_task->status == 'open' && $container_task->trans_type != 'auto')
                        <button @if(!$container_task->allowCloseThisTask()) disabled="disabled" title="Не все позиции выполнены"  @endif style="font-size: 14px !important;" class="btn btn-success" onclick="window.location.href = '{{ route('completed.task', ['id' => $container_task->id]) }}'">Закрыть заявку</button>
                        @endif
                    </div>
                </div>

                <br>
                <div class="row">
                    <div class="col-md-12">
                        <p>
                            Заявка: <strong>{{ $container_task->getNumber() }}</strong>  | Тип заявки: <strong>{{ $container_task->getType() }}</strong>  |  Тип транспорта: <strong>{{ $container_task->getTransType() }}</strong>
                            | Создал: <strong>{{ $container_task->user->full_name }}</strong> | Статус: <strong>@lang('words.'.$container_task->status)</strong>
                        </p>
                    </div>
                </div>


                <table class="table table-bordered">
                    <thead>
                    <th>#</th>
                    <th>Контейнер</th>
                    <th>Статус</th>
                    <th>Клиент</th>
                    <th>Адрес</th>
                    <th>Дата</th>
                    <th>Действие</th>
                    </thead>
                    @foreach($import_logs as $im)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $im->container_number }}</td>
                            <td>
                                @if($container_task->task_type == 'receive')
                                    @if($im->state == 'posted')
                                        <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ __('words.posted') }}
                                    @else
                                        <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; {{ __('words.not_posted') }}
                                    @endif

                                    @if($im->position['cancel'])
                                        <br><span>Находиться в процессе отмены</span>
                                    @endif

                                    @if($im->position['edit'])
                                        <br><span>Находиться в процессе редактирование</span>
                                    @endif
                                @endif

                                @if($container_task->task_type == 'ship')
                                    @if($im->state == 'selected')
                                        <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ __('words.selected') }}
                                    @elseif($im->state == 'issued')
                                        <i style="font-size: 20px; color: green;" class="fa fa-check-circle" aria-hidden="true"></i>&nbsp; {{ __('words.issued') }}
                                    @else
                                        <i style="font-size: 20px; color: red;" class="fa fa-exclamation-circle" aria-hidden="true"></i>&nbsp; {{ __('words.not_selected') }}
                                    @endif

                                    @if($im->position['cancel'])
                                        <br><span>Находиться в процессе отмены</span>
                                    @endif
                                @endif
                            </td>
                            <td width="280">{{ $im->company }}</td>
                            <td>
                                @if($container_task->isOpen())
                                    {{ $im->getContainerAddress($container_task->id) }}
                                @elseif($container_task->task_type == 'receive' && $container_task->kind == 'common')
                                    {{ $im->getContainerAddress($container_task->id) }}
                                @elseif($container_task->task_type == 'receive' && $container_task->kind == 'automatic')
                                    {{ $im->getContainerAddress($container_task->id) }}
                                @else

                                @endif
                            </td>
                            <td>
                                {{ $im->updated_at->format('d.m.Y H:i:s') }}
                            </td>
                            <td>
                                @if((!$im->position['cancel'] && !$im->position['edit']) && ($im->state == 'not_posted' || $im->state == 'not_selected'))
                                    <button type="button" data-number="{{ $im->container_number }}" data-task="{{ $container_task->id }}" class="btn btn-danger  cancelPosition" style="font-size: 14px !important;">Отменить</button>

                                    @if($container_task->task_type == 'receive')
                                        <button type="button" data-number="{{ $im->container_number }}" data-task="{{ $container_task->id }}" class="btn btn-warning editPosition" style="font-size: 14px !important;">Изменить</button>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </table>

                @include('modals.task_position_cancel_modal')

                @include('modals.task_position_edit_modal')


            </div>
        </div>
    </div>
@stop

@push('scripts')
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <script>
        jQuery.noConflict();
        $(document).ready(function(){
            let reason = $('#message-text');
            let new_container_number = $('#new_container_number');
            let successMessageDiv = $('#successMessageDiv');
            let successMessageDiv2 = $('#successMessageDiv2');
            let taskPositionCancelModalTitle = $('#taskPositionCancelModalTitle');
            let taskPositionEditModalTitle = $('#taskPositionEditModalTitle');
            let container_number = $('#container_number');
            let edit_container_number = $('#edit_container_number');
            let container_task_id = $('#container_task_id');
            let edit_container_task_id = $('#edit_container_task_id');
            let _token = $('meta[name="csrf-token"]').attr('content');

            $('.cancelPosition').each(function(){
                $(this).click(function(){
                    container_number.val($(this).data('number'));
                    container_task_id.val($(this).data('task'));
                    $('#taskPositionCancelModal').modal({
                        backdrop: true,
                        keyboard: true,
                        toggle: true,
                    });
                    taskPositionCancelModalTitle.html('Отмена позиции - ' + container_number.val());
                });
            });

            $('.editPosition').each(function(){
                $(this).click(function(){
                    edit_container_number.val($(this).data('number'));
                    edit_container_task_id.val($(this).data('task'));
                    $('#taskPositionEditModal').modal('show');
                    taskPositionEditModalTitle.html('Изменить позиции - ' + edit_container_number.val());
                });
            });

            $('#btnTaskPositionSend').click(function(){
                if (reason.val() !== '') {
                    if (reason.hasClass('red_border')) {
                        reason.removeClass('red_border');
                    }

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo route('task.position.cancel') ?>",
                        data: {_token: _token, container_number: container_number.val(), container_task_id: container_task_id.val(), reason: reason.val()},
                        success: function(res){
                            successMessageDiv.html(res).addClass('show');
                        },
                        error: function(err){
                            console.log(err)
                        }
                    });
                } else {
                    if(!reason.hasClass('red_border')) {
                        reason.addClass('red_border');
                    }
                }
            });

            $('#btnTaskPositionEditSend').click(function(){
                if (new_container_number.val() !== '') {
                    if (new_container_number.hasClass('red_border')) {
                        new_container_number.removeClass('red_border');
                    }

                    $.ajax({
                        type: 'POST',
                        url: "<?php echo route('task.position.edit') ?>",
                        data: {_token: _token, edit_container_number: edit_container_number.val(), new_container_number: new_container_number.val(), edit_container_task_id: edit_container_task_id.val()},
                        success: function(res){
                            successMessageDiv2.html(res).addClass('show');
                        },
                        error: function(err){
                            console.log(err)
                        }
                    });
                } else {
                    if(!new_container_number.hasClass('red_border')) {
                        new_container_number.addClass('red_border');
                    }
                }
            });

            $('#btnTaskPositionCancel').click(function(){
                if (reason.hasClass('red_border')) {
                    reason.removeClass('red_border');
                }
                reason.html('');
                successMessageDiv.html('').removeClass('show');
            });

            $('#btnTaskPositionEdit').click(function(){
                if (reason.hasClass('red_border')) {
                    reason.removeClass('red_border');
                }
                reason.html('');
                successMessageDiv.html('').removeClass('show');
            });
        });
    </script>
@endpush
