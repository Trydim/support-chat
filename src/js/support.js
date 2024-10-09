'use strict';

import btnStyle from '../css/btn.scss';

import {DEBUG, MAIN_URL, POSITION} from "./const";

const APP_ID = 'supportApp';

const getIcon = () => `<svg width="35" height="35" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
  <path d="M2.00001 11.9997C2.00051 9.80727 2.72149 7.67579 4.052 5.93326C5.3825 4.19074 7.24878 2.93375 9.36361 2.35574C11.4784 1.77774 13.7246 1.91076 15.7565 2.73432C17.7883 3.55789 19.4932 5.02636 20.6087 6.91374C21.7243 8.80111 22.1886 11.0028 21.9304 13.1799C21.6721 15.3571 20.7056 17.389 19.1794 18.963C17.6533 20.537 15.6521 21.5659 13.484 21.8912C11.3159 22.2166 9.10093 21.8204 7.18001 20.7637L3.29201 21.9477C3.11859 22.0005 2.93408 22.0052 2.7582 21.9612C2.58232 21.9173 2.42169 21.8264 2.29351 21.6982C2.16532 21.57 2.07439 21.4094 2.03044 21.2335C1.98649 21.0576 1.99118 20.8731 2.04401 20.6997L3.22801 16.8057C2.41992 15.3329 1.99749 13.6796 2.00001 11.9997ZM8.00001 10.9997C8.00001 11.2649 8.10537 11.5192 8.2929 11.7068C8.48044 11.8943 8.7348 11.9997 9.00001 11.9997H15C15.2652 11.9997 15.5196 11.8943 15.7071 11.7068C15.8947 11.5192 16 11.2649 16 10.9997C16 10.7345 15.8947 10.4801 15.7071 10.2926C15.5196 10.105 15.2652 9.99967 15 9.99967H9.00001C8.7348 9.99967 8.48044 10.105 8.2929 10.2926C8.10537 10.4801 8.00001 10.7345 8.00001 10.9997ZM9.00001 13.9997C8.7348 13.9997 8.48044 14.105 8.2929 14.2926C8.10537 14.4801 8.00001 14.7345 8.00001 14.9997C8.00001 15.2649 8.10537 15.5192 8.2929 15.7068C8.48044 15.8943 8.7348 15.9997 9.00001 15.9997H13C13.2652 15.9997 13.5196 15.8943 13.7071 15.7068C13.8947 15.5192 14 15.2649 14 14.9997C14 14.7345 13.8947 14.4801 13.7071 14.2926C13.5196 14.105 13.2652 13.9997 13 13.9997H9.00001Z" fill="#ffffff"/>
</svg>`;

class SupportBtn {
  loading = false;

  constructor(settings) {
    settings = settings || {};

    if (settings.hasOwnProperty('btn')) this.bindBtn(settings.btn);
    else this.createBtn(settings.position);
    this.onEvent();
  }

  bindBtn(btn) {
    if (typeof btn === 'string') btn = document.querySelector(btn);

    btn.id = APP_ID;

    this.btn = btn;
  }
  createBtn(position = POSITION[2]) {
    const wrap  = document.createElement('div'),
          style = document.createElement('style'),
          btn   = document.createElement('div');

    style.innerHTML = btnStyle['toString']();

    btn.id = APP_ID;
    btn.classList.add('vis-support-btn', position);
    btn.innerHTML = getIcon();

    wrap.append(style);
    wrap.append(btn);
    document.body.append(wrap);

    this.btn = btn;
  }

  loadScript() {
    const script = document.createElement('script');
    script.src = MAIN_URL + 'supportApp.js?ver=102';

    document.body.append(script);
    return script;
  }

  onEvent() {
    this.btn.addEventListener('click', async () => {
      // Значок загрузки на кнопку

      this.loadScript();
    }, {once: true});

    DEBUG && this.btn.click();
  }
}

document.addEventListener("DOMContentLoaded", () => {
  new SupportBtn(window['supportBotSettings']);
});

