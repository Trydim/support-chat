'use strict';

//import appStyle from 'primevue/resources/themes/bootstrap4-light-blue/theme.css';
import styleApp from '../css/styleApp.scss';
import DialogStyle from 'primevue/dialog/style/dialogstyle.esm';

import { createApp } from 'vue';
import PrimeVue from 'primevue/config';

import App from './App.vue';
import btnStyle from "../css/btn.scss";

class Application {
  constructor(settings) {
    const style = document.createElement('style');

    style.innerHTML = styleApp['toString']();
    style.innerHTML += DialogStyle.css;

    document.head.append(style);

    const app = createApp(App);
    app.use(PrimeVue);
    app.config.globalProperties.$globalSettings = settings || {};

    app.mount('#supportApp');
  }
}

new Application(window['supportBotSettings']);
