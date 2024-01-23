'use strict';

import btnStyle from '../css/btn.scss';

import {DEBUG, MAIN_URL, POSITION} from "./const";

class SupportBtn {
  loading = false;
  scriptLoaded = false;

  constructor() {
    this.createBtn();
    this.onEvent();
  }

  createBtn(position = POSITION[3]) {
    const wrap  = document.createElement('div'),
          style = document.createElement('style'),
          btn   = document.createElement('div');

    style.innerHTML = btnStyle['toString']();

    btn.id = 'supportApp';
    btn.classList.add('vis-support-btn', position);
    btn.innerHTML = 'Помощь';

    wrap.append(style);
    wrap.append(btn);
    document.body.append(wrap);

    this.btn = btn;
  }

  loadScript() {
    const script = document.createElement('script');
    script.src = MAIN_URL + 'supportApp.js';

    document.body.append(script);
    this.scriptLoaded = true;
  }

  onEvent() {
    this.btn.addEventListener('click', async () => {
      if (!this.scriptLoaded) this.loadScript(); // добавить скрипты

      // открыть окно чата, с загрузкой
      // инициализировать синхронизацию setInterval

      // можно писать сообщение пока идет инициализация..
    });

    DEBUG && this.btn.click();
  }
}

document.addEventListener("DOMContentLoaded", () => {
  new SupportBtn();
});

