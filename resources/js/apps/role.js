const roleApp = Vue.createApp({
    props: {
        test: String
    },
    data() {
        return {
            test: 'aaaa'
        }
    },
    mounted() {
        console.log('test');
    },
    computed: {
        test: function() {
            return this.test;
        }
    }
}).mount('#roleVue');
