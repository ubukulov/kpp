<template>
    <div>
        <div class="scango-phone">
            <span>Нужна помощь?!<br> Позвоните: 2051</span><br>или: 2112, 2212
        </div>

        <div class="scango-barcode" align="center">
            <img src="/img/bar-code.jpg" alt="Штрих-Код накладной" align="center">
        </div>

        <div class="scango-form" align="center">
            <label>Отсканируйте штрих-код</label>
            <div><input autofocus v-on:keyup.enter="scanGO()" type="text" v-model="barcode"></div>
        </div>

        <div v-html="response_html">

        </div>

        <div v-if="progress_circule" class="text-center">
            <v-progress-circular
                style="width: 100px; height: 100px;"
                indeterminate
                color="primary"
            ></v-progress-circular>
        </div>

        <hr>

        <div class="row">
            <h4>Последние сканированные документы</h4>
            <div class="col-md-12">
                <table class="table table-bordered">
                    <thead>
                        <th>ID</th>
                        <th>Дата сканирование</th>
                        <th>Код клиента</th>
                        <th>Номер документа</th>
                        <th>Дата отгрузки</th>
                        <th>Штрих-код</th>
                        <th>Описание</th>
                        <th>Сканировал</th>
                    </thead>
                    <tbody>
                        <tr v-for="(log, i) in logs" :key="i">
                            <td>{{log.ID}}</td>
                            <td>{{log.DATE_SCAN}}</td>
                            <td>{{log.DEPOSITOR_CODE}}</td>
                            <td>{{log.ORDER_CODE}}</td>
                            <td>{{log.LOG_DOCSHIPDATE}}</td>
                            <td>{{log.SCAN}}</td>
                            <td>{{log.ERROR_DESCRIPTION}}</td>
                            <td>{{log.USER}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios'
    export default {
        data(){
            return {
                barcode: '',
                response_html: '',
                progress_circule: false,
                logs: []
            }
        },
        methods: {
            scanGO(){
                this.progress_circule = true;
                let formData = new FormData();
                formData.append('barcode', this.barcode);
                axios.post('/scan-go-checking', formData)
                .then(res => {
                    console.log(res);
                    this.response_html = res.data.data
                    this.barcode = '';
                    this.progress_circule = false;
                    this.getLast5Logs();
                    setTimeout(() => this.closeInfoHtml(), 5000);
                })
                .catch(err => {
                    console.log(err)
                })
            },
            getLast5Logs(){
                axios.get('/get-last-5-logs-from-sql-server')
                .then(res => {
                    this.logs = res.data;
                })
                .catch(err => {
                    console.log(err)
                })
            },
            closeInfoHtml(){
                this.response_html = '';
            }
        },
        created(){
            this.getLast5Logs();
        }
    }
</script>

<style scoped>
.scango-phone {
    position: absolute;
    top: 100px;
    left: 100px;
}
.scango-phone > span {
    color: red;
    font-size: 20px;
}
.scango-barcode {
    background: orange;
}
.scango-barcode > img {
    width: 500px;
    height: 260px;
    border: dotted;
}
label {
    font-size: 40px;
    font-weight: 700;
}
input[type="text"] {
    padding: 5px;
}
input {
    width: 600px;
    height: 40px;
    font-size: 30px;
    font-family: Arial Black;
    color: blue;
    border-color: #212529;
    border: 2px solid #212529;
    border-radius: 4px;
}
.v-progress-circular {
    margin: 1rem;
}
</style>
