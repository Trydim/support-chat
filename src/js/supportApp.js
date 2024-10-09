'use strict';

import appStyle from '../css/styleApp.scss';

import { createApp } from 'vue';
import PrimeVue from 'primevue/config';

import App from './App.vue';

class Application {
  constructor(settings) {
    const style = document.createElement('style');

    style.innerHTML = appStyle['toString']();

    document.head.append(style);

    const app = createApp(App);
    app.use(PrimeVue);
    app.config.globalProperties.$globalSettings = settings || {};

    app.mount('#supportApp');
  }
}

new Application(window['supportBotSettings']);
