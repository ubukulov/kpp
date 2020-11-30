@extends('layouts.kpp')
@section('content')
    <v-app>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 left_content">
                    <div><h3>Разрешение на въезд</h3></div>
                    <hr>
                    <form method="POST" action="{{route('store.img')}}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <h4 style="font-style: italic;">Данные о машине</h4>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Гос.номер(без пробелов, на анг)</label>
                                            <input onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="gov_number" tabindex="1" type="text" placeholder="888BBZ05" required name="gov_number" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="padding-top: 52px;">
                                        <button @click="checkCar()" type="button" class="btn btn-warning">Проверить</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>№тех.паспорта</label>
                                    <input style="text-transform: uppercase;" v-model="tex_number" tabindex="2" type="text" name="tex_number" required class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Марка авто</label>
                                    <input v-model="mark_car" type="text" tabindex="3" name="mark_car" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label>Номер прицепа</label>
                                    <input v-model="pr_number" tabindex="4" type="text" name="pr_number" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Дата заезда</label>
                                    <input tabindex="5" type="text" id="date_in" required value="<?= date('d.m.Y H:i')?>" name="date_in" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h4 style="font-style: italic;">Данные о водителе</h4>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>№вод.удостоверение</label>
                                            <input placeholder="BN203526" onkeyup="return no_cirilic(this);" style="text-transform: uppercase;" v-model="ud_number" tabindex="6" type="text" name="ud_number" required class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-4" style="padding-top: 52px;">
                                        <button @click="checkDriver()" style="color: #fff;" type="button" class="btn btn-dark">Проверить</button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>ФИО</label>
                                    <input style="text-transform: uppercase;" v-model="last_name" tabindex="7" type="text" name="last_name" required class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Телефон(без пробелов с 8-ки)</label>
                                    <input v-model="phone" tabindex="8" type="text" placeholder="87778882255" name="phone" required class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Компания</label>
                                    <input v-model="company" tabindex="9" type="text" name="company" required class="form-control">
                                </div>

                                <div class="form-group">
                                    <label>Вид операции</label>
                                    <select tabindex="10" name="operation_type" id="type" class="form-control">
                                        <option value="1">Погрузка</option>
                                        <option value="2">Разгрузка</option>
                                        <option value="3">Другие действие</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label style="font-size: 14px;">Копия документов*</label>
                                    <div class="row justify-content-center">
                                        <div class="col-md-12 text-center">
                                            <div id="camera" style="max-width: 100%; margin-bottom: 10px;"></div>
                                            <div>
                                                <button style="margin-right: 20px;" id="image-fire" type=button class="btn btn-warning">Фото (лицевая)</button>
                                                <button style="color: #fff;" id="tex_btn" type=button class="btn btn-dark">Фото (обратная)</button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="path_docs_fac" class="image-tag">
                                        <input type="hidden" id="path_docs_back" name="path_docs_back" class="image-tag">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    <button style="padding: 10px 50px; font-size: 20px;" type="submit" class="btn btn-success">Отправить</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="col-md-12 right_content">
                    <div>
                        <h3>Список добавленных пропусков</h3>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="text" placeholder="Номер пропуска или гос.номер" v-model="search" class="form-control">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <button @click="searchPermit()" type="button" class="btn btn-primary">Поиск</button>
                        </div>

                        <div class="col-md-6">

                        </div>
                    </div>
                    <hr>
                    <table class="table">
                        <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">ФИО</th>
                            <th scope="col">#вод.удос.</th>
                            <th scope="col">Компания</th>
                            <th scope="col">Вид операции</th>
                            <th scope="col">Телефон</th>
                            <th scope="col">Гос.номер</th>
                            <th scope="col">Фото(лицевая)</th>
                            <th scope="col">Фото(обратная)</th>
                            <th scope="col">Печать</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-html="html" style="background: cadetblue;"></tr>
                        @foreach($permits as $permit)
                            <tr>
                                <th scope="row">{{ $permit->id }}</th>
                                <td>{{ $permit->getFullname() }}</td>
                                <td>{{ $permit->ud_number }}</td>
                                <td>{{ $permit->company }}</td>
                                <td>
                                    @if($permit->operation_type == 1)
                                        Погрузка
                                    @elseif($permit->operation_type == 2)
                                        Разгрузка
                                    @else
                                        Другие действие
                                    @endif
                                </td>
                                <td>{{ $permit->phone }}</td>
                                <td>{{ $permit->gov_number }}</td>
                                <td>
                                    @if(!empty($permit->path_docs_fac))
                                        <a href="{{ $permit->doc_fac() }}" target="_blank">ссылка</a>
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($permit->path_docs_back))
                                        <a href="{{ $permit->doc_back() }}" target="_blank">ссылка</a>
                                    @endif
                                </td>
                                <td>
                                    <v-icon
                                        middle
                                        @click="print_r({{ $permit->id }})"
                                        {{--                                @if($driver->id == 1424)--}}
                                        {{--                                @click="print_r({{ $driver->id }})"--}}
                                        {{--                                @else--}}
                                        {{--                                @click="showUser({{ $driver->id }})"--}}
                                        {{--                                @endif--}}
                                    >
                                        mdi-printer
                                    </v-icon>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <v-dialog style="z-index: 99999; position: absolute;" v-model="dialog" persistent max-width="1000px">
            <v-card style="padding-left: 50px;">
                <v-card-title style="padding-top: 0px;">
                    <span class="headline" style="font-size: 40px !important;">РАЗРЕШЕНИЕ НА ВЪЕЗД № @{{ propusk_id }}</span>
                </v-card-title>
                <v-card-text>
                    <v-container>
                        <v-row>
                            <v-col cols="6" class="prt_col">
                                <div>
                                    <p>Дата заезда:</p>
                                    <span>@{{ date_in }}</span>
                                </div>
                                <div>
                                    <p>Марка авто:</p> <span>@{{ mark_car }}</span>
                                </div>
                                <div>
                                    <p>Гос.номер:</p> <span>@{{ gov_number }}</span>
                                </div>
                                <!--<div>
                                    <p>Номер прицепа:</p> <span>@{{ pr_number }}</span>
                                </div>-->
                                <div>
                                    <p>ФИО водителя:</p> <span>@{{ last_name }}</span>
                                </div>
                            </v-col>

                            <v-col cols="6" class="prt_col">
                                <div>
                                    <p>Дата выезда:</p> <span>_____________</span>
                                </div>
                                <div>
                                    <p>Компания:</p> <span>@{{ company }}</span>
                                </div>
                                <div>
                                    <p>Вид операции:</p> <span>@{{ operation_type }}</span>
                                </div>
                                <div>
                                    <p>Номер водителя:</p> <span>@{{ phone }}</span>
                                </div>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="blue darken-1" @click="closePrintForm()">Отменить</v-btn>
                    <v-btn color="success darken-1" onclick="window.print();">
                        <v-icon
                            middle
                        >
                            mdi-printer
                        </v-icon>
                        &nbsp;Распечатать
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-app>
@stop
