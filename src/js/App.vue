<template>
  <div>
    <div @click="open">Помощь</div>

    <Dialog v-model:visible="visible" header="Поддержка"
            :style="{ width: '50rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" :position="position" :modal="true" :draggable="false">

      <div class="chat-messages">
        <div v-for="(item, index) of content" :key="index" class="message-box-holder">
          <div v-if="!item.author" class="message-sender">Try</div>
          <div class="message-box" :class="{'message-partner': !item.author}">{{ item.content }}</div>
        </div>
      </div>

      <!--<div class="chatbox-holder">
        <div class="chatbox">
          <div class="chatbox-top">
            <div class="chat-partner-name">
              <span class="status online"></span>
              <a target="_blank" href="https://www.facebook.com/mfreak">Mamun Khandaker</a>
            </div>
            <div class="chatbox-icons">
              <a href="javascript:void(0);"><i class="fa fa-minus"></i></a>
              <a href="javascript:void(0);"><i class="fa fa-close"></i></a>
            </div>
          </div>

          <div class="chat-input-holder">
            <textarea class="chat-input"></textarea>
            <input type="submit" value="Send" class="message-send" />
          </div>
          <div class="attachment-panel">
            <a href="#" class="fa fa-thumbs-up"></a>
            <a href="#" class="fa fa-camera"></a>
            <a href="#" class="fa fa-video-camera"></a>
            <a href="#" class="fa fa-image"></a>
            <a href="#" class="fa fa-paperclip"></a>
            <a href="#" class="fa fa-link"></a>
            <a href="#" class="fa fa-trash-o"></a>
            <a href="#" class="fa fa-search"></a>
          </div>
        </div>

        </div>
      </div>-->

      <template #footer>
        <div class="chat-input-holder">
          <textarea class="chat-input" v-model="message"></textarea>
          <input type="submit" class="message-send" value="Send" @click="send">
        </div>
      </template>
    </Dialog>
  </div>
</template>

<script>

import {DEBUG, POSITION, SYNC_DELAY, SYNC_INTERVAL} from "./const";
import query from "./libs/query";

import Dialog from 'primevue/dialog';

export default {
  name: 'support-app',
  components: {Dialog},
  data: () => ({
    userKey: undefined,
    visible: false,
    position: 'topright',

    content: [],
    message: '',
  }),
  computed: {},
  watch   : {
    visible() {
      if (this.visible) this.startSync();
      else this.stopSync();
    },
  },
  methods: {
    open() {
      this.visible = true;

      this.loadMessages();
    },

    prepareContent(data) {
      this.content = data.map(item => {
        return {
          author: item.userKey === this.userKey,
          date  : item.date,
          content: item.content,
        }
      });
    },
    loadMessages(date) {
      query.Post({
        data: {action: 'loadMessages', date},
      }).then(d => {
        if (d['status']) {
          this.userKey = d['userKey'];
          this.prepareContent(d['data']);
        }
      })
    },

    startSync() {
      this.syncInterval = setInterval(() => this.loadMessages(new Date().getTime() / 1e3 | 0), SYNC_INTERVAL);

      setTimeout(() => this.stopSync(), SYNC_DELAY);
    },
    stopSync() { clearInterval(this.syncInterval) },

    send() {
      const content = this.message;
      this.message = '';

      // блокировать кнопку отправить

      query.Post({
        data: {
          action: 'addMessage',
          content,
        }
      }).then(d => {
        if (d['status'])this.content.push({
          author: true,
          date  : new Date().toLocaleString('ru'),
          content,
        });
      })
    }
  },
  mounted() {
    DEBUG && this.open();
  },
}
</script>
