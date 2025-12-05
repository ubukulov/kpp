<script>
export default {
    data() {
        return {
            orders: [],
            order_code: '',
            searchInput: '',
            count: 1
        }
    },
    methods: {
        getOrders(){
            axios.get('/cabinet/samsung/barcode/get-orders')
                .then(res => {
                    this.orders = res.data;
                    console.log(res.data)
                })
                .catch(err => {
                    console.log(err)
                })
        },
        commandPrint(){
            let formData = new FormData();
            formData.append('count', this.count);
            formData.append('order_code', this.order_code);
            formData.append('_token', $('input[name="_token"]').val());
            console.log(this.order_code);
            const found = this.orders.find(element => element.ord_Code === this.order_code);
            console.log(found);
            formData.append('ord_CustomerOrderCode', found.ord_CustomerOrderCode);
            formData.append('KASPI_DO', found.KASPI_DO);
            formData.append('KASPI_URL', found.KASPI_URL);
            let address = this.orders.filter(item => item.ord_Code === this.order_code);
            let direction = '';
            if(address.length > 0) {
                direction = address[0].address;
            }

            formData.append('direction', direction);

            if (this.order_code !== '') {
                axios.post('/cabinet/samsung/barcode/command-print', formData)
                    .then(res => {
                        console.log(res);
                        if(found.KASPI_DO != null) {
                            window.open(found.KASPI_URL, '_blank');
                        }
                    })
                    .catch(err => {
                        console.log(err)
                    })
            }
        }
    },
    created(){
        this.getOrders();
    }
}
</script>

<template>
    <v-app>
        <v-main>
            <v-container>
                <v-row>
                    <v-col cols="6">
                        <!-- general form elements -->
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Распечатать этикетки</h3>
                            </div>
                            <!-- /.card-header -->
                            <!-- form start -->
                            <div class="card-body">
                                <label>Выберите заказ</label>
                                <div class="form-group">
                                    <v-autocomplete
                                        :search-input.sync="searchInput"
                                        :items="orders"
                                        item-value="ord_Code"
                                        v-model="order_code"
                                        item-text="ord_Code"
                                    ></v-autocomplete>
                                </div>

                                <div class="form-group">
                                    <label>Выберите количество упаковок(этикеток)</label>
                                    <select class="form-control" v-model="count">
                                        <option v-for="i in 60" :key="i" :value="i">{{ i }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button @click="commandPrint()" type="button" class="btn btn-success">Печать</button>
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>

</style>
