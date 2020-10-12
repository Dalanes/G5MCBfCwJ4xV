<template>
    <div class="row">
        <div class="col-sm-3 bg-light pt-3">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <div class="form-group">
                        <label for="sort">Сортировка</label>
                        <select name="sort" class = "form-control" id="sort" ref="sort"
                            v-on:change="changeSort"
                        >
                            <option value="name">    По Наименованию</option>
                            <option value="quantity">По Количеству</option>
                            <option value="distance">По Расстоянию</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="filter">Фильтр</label>
                        <select name="nameFilter" class = "form-control" id="filter" ref="nameFilter"
                            v-on:change = "setNameFilter"
                        >
                            <option value="name">    По Наименованию</option>
                            <option value="quantity">По Количеству</option>
                            <option value="distance">По Расстоянию</option>
                        </select>
                        <div class="form-group mt-2">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary btn-sm"
                                       :class="{disabled: nameFilter === 'name'}">
                                    <input type="radio" name="condition" value="less"
                                           autocomplete="off" checked
                                        v-on:click = "setConditionFilter('less')"
                                    >
                                    Меньше
                                </label>
                                <label class="btn btn-secondary btn-sm"
                                       :class="{disabled: nameFilter === 'name'}">
                                    <input type="radio" name="condition" value="equally"
                                           autocomplete="off"
                                           v-on:click = "setConditionFilter('equally')"
                                    >
                                    Равно
                                </label>
                                <label class="btn btn-secondary btn-sm"
                                       :class="{disabled: nameFilter === 'name'}">
                                    <input type="radio" name="condition" value = "more"
                                           autocomplete="off"
                                           v-on:click = "setConditionFilter('more')"
                                    >
                                    Больше
                                </label>
                                <label class="btn btn-secondary btn-sm active" :class="{disabled: nameFilter !== 'name'}">
                                    <input type="radio" name="condition" value = "contains"
                                           autocomplete="off"
                                           v-on:click = "setConditionFilter('contains')"
                                    >
                                    Содержит
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="value" ref="valueFilter" v-model = "valueFilter">
                            <small v-if = "errors.length" class = "small text-warning">
                                <ul>
                                    <li v-for="error in errors" class="">{{ error }}</li>
                                </ul>
                            </small>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
        <div class="col-sm-9 bg-light pt-3">
            <nav aria-label="Page navigation example">
                <ul class="pagination">
                    <li class="page-item" :class="{disabled: currentPage === 1}">
                        <a class="page-link" href="#" v-on:click = "--currentPage">
                            Предыдущая
                        </a>
                    </li>

                    <template v-for="(n, index) in this.$props.quantityPages">
                        <li class="page-item" v-bind:class = "{active: currentPage === index + 1}"
                            v-on:click = "currentPage = index + 1"
                        >
                            <a class="page-link" href="#">
                                {{index + 1}}
                            </a>
                        </li>
                    </template>

                    <li class="page-item" :class="{disabled: currentPage === quantityPages}">
                        <a class="page-link" href="#"
                           v-on:click="++currentPage">
                            Следующая
                        </a>
                    </li>
                </ul>
            </nav>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">Наименование</th>
                        <th scope="col">Количество</th>
                        <th scope="col">Расстояние</th>
                        <th scope="col">Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="flight in this.flights">
                        <tr>
                            <th scope="row">{{flight.name}}</th>
                            <td>{{flight.quantity}}</td>
                            <td>{{flight.distance}}</td>
                            <td>{{flight.date}}</td>
                        </tr>

                    </template>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
export default {
    /**
     * @property flights - первоначальная загрузка всех рейсов. Поля: name, quantity, distance, date.
     * @property routeToGetPage - роут, отправляя на адрес которого, получаем рейсы,
     *                  исходя из настроек сортировки и фильтров
     * @property quantityPages  - количество страниц с рейсами
     * @property csrfToken      - csrf токен
     */
    props: {
        flights: String,
        routeToGetPage: String,
        quantityPages: String,
        csrfToken: String
    },

    /**
     * @var array flights           - записи рейсов
     * @var int quantityPages       - количество страниц с рейсами
     * @var int currentPage         - страница, которую на данный момент просматривает пользователь
     *
     * @var string sort             - поле, по которому происходит сортировка
     *
     * @var string nameFilter       - наименование поля, по которому производится поиск
     *                                необходимых рейсов
     * @var string conditionFilter  - условие, по которому производится поиск(фильтрация).
     *                            Возможные значения: 'less', 'equally', 'more', 'contains'.
     * @var string valueFilter      - значение фильтра
     *
     * @var array errors            - список ошибок
     *
     */
    data: function (){
        return {
            flights: [],
            quantityPages: 0,
            currentPage: 1,

            sort: "name",

            nameFilter: "",
            conditionFilter: "",
            valueFilter: "",

            errors: []
        }
    },
    /**
     * отслеживание изменений данных
     */
    watch: {

        /**
         * при изменении номера страницы, идёт загрузка той,
         * на которую перешёл пользователь
         */

        currentPage: function (){
            let settings = this.getDataSettings();
            axios.post(this.routeToGetPage, settings)
                .then((newFlights)=> {
                    this.setDataFromServer(newFlights);
                }).catch((error)=> {
                    console.log(error);
                });
        },

        /**
         * при изенении значения фильтра (valueFilter), происходит отправка
         * настроек(сортировки и фильтров):
         *  @var string sort - поле, по которому необходимо отсортировать данные,
         *  @var int nextPage - страница, которую желает просмотреть пользователь,
         *  @var object filter: {         - объект, содержащий в себе:
                            nameFilter,         наименование поля, по которому поизойдет фильтрация,
                            condition,          условие фильтрации,
                            value               значение.
                        },
         * Получаем эти^ данные путём вызова метода .getDataSettings()
         *
         * От сервера получаем:
         *      newFlights [
         *          flightsReceived = newFlights.data[0];   - рейсы, которые мы получили, применив настройки
         *          quantityPages   = newFlights.data[1];   - количество страниц с рейсами
         *      ]
         *
         *      устанавливаем  ^данные^ значения при помощи метода .setDataFromServer();
         */

        valueFilter: function () {
            if (this.valueFilter.length > 0) {

                let hasSpecSymb = this.validValueFilter();

                let settings = this.getDataSettings();

                if (this.currentPage !== 1) {
                    this.currentPage = 1;
                }
                if (hasSpecSymb === false) {
                    axios.post(this.$props.routeToGetPage, settings)
                        .then((newFlights) => {
                            this.setDataFromServer(newFlights);
                        }).catch((err) => {
                        console.log(err);
                    });
                }
            } else {
                this.changeSort();
            }
        },
        /**
         * меняем условие фильтра т.к. одно условие недоступно при наличии другого
         * и очищаем значение для фильтрации
         */
        nameFilter: function() {
            this.valueFilter = "";
            if (this.nameFilter === "name") {
                this.conditionFilter = "contains"
            } else {
                this.conditionFilter = "less"
            }
        }
    },
    methods: {
        /**
         * Отправка настроек на сервер при изменении поля для сортировки
         * Отправка и получение данных описаны со строки 187
         */
        changeSort: function() {
            this.sort = this.$refs.sort.value;

            let settings = this.getDataSettings();

            axios.post(this.$props.routeToGetPage, settings)
                .then((newFlights) => {

                    this.setDataFromServer(newFlights);

                }).catch((err)=>{
                    console.log(err);
                });
        },

        /**
         * Устанавливаем значение условия при его изменении
         * @param condition
         */

        setConditionFilter: function(condition) {
            this.conditionFilter = condition;
            this.valueFilter = "";
        },

        /**
         * Устанавливаем значение nameFilter при его изменении
         * */

        setNameFilter: function () {
            this.nameFilter = this.$refs.nameFilter.value;

            console.log(this.nameFilter);
        },

        //вспомогательные методы

        /**
         *  описание данного компонента на строке 187
        */
        getDataSettings: function() {
            let settings = {
                sort: this.sort,
                nextPage: this.currentPage,
                filter: {
                    nameFilter: this.$refs.nameFilter.value,
                    condition: this.conditionFilter,
                    value: this.valueFilter
                },
                _token: this.$props.csrfToken
            }

            return settings;
        },

        /**
         * описание данного метода на строке 194
         * @param newFlights
         */

        setDataFromServer: function(newFlights) {

            if (newFlights.data.length === 2) {

                let flightsReceived = newFlights.data[0];
                let quantityPages   = newFlights.data[1];

                if (this.flights !== flightsReceived) {
                    this.flights = flightsReceived;
                }
                this.quantityPages = quantityPages;

            } else {
                this.flights = newFlights.data
            }
        },

        /**
         * проверка пользовательского ввода на наличие специальных символов
         */
        validValueFilter: function() {
            this.errors = [];

            let regExpValue = /[\/\.\{\}\[\]\`\'\"\+\=\&\^\:\;\$\#\(\)\*]/;
            let hasSpecSymb  = regExpValue.test(this.valueFilter);

            if (hasSpecSymb) {
                let lengthValFilter = this.valueFilter;

                this.valueFilter = this.valueFilter.slice(0, -1);

                this.errors.push("Использование специальных символов (*, \", {, }, & и др.) запрещено.");
            }

            return hasSpecSymb;
        }
    },

    mounted() {
        this.flights              = JSON.parse(this.$props.flights);
        this.quantityPages        = JSON.parse(this.$props.quantityPages);
        this.nameFilter           = this.$refs.nameFilter.value;

    }
}
</script>
