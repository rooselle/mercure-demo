import './styles/app.css';
import Vue from 'vue';
import App from './js/view/App.vue';
import BootstrapVue from 'bootstrap-vue';
import Notifications from 'vue-notification';
import VueTimeago from 'vue-timeago';

import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';

Vue.use(BootstrapVue);
Vue.use(Notifications);
Vue.use(VueTimeago, {
  name: 'timeago',
  locale: 'fr',
  locales: {
    fr: require('date-fns/locale/fr')
  }
});

Vue.config.productionTip = false;

new Vue({
  el: '#app',
  render: h => h(App),
});
