require('./bootstrap');

window.Vue = require('vue');

let tableComponent = require("./components/TableComponent").default;

const table = new Vue({
    el: '#tableComponent',
    components: {
        tableComponent: tableComponent
    }
});
