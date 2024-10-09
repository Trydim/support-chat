<template>
  <div>
    <v-button @click="open" />

    <Dialog v-model:visible="visible" ref="dialog" :style="dialogStyle"
            maximizable draggable
            :position="position.toLowerCase()"
            :modal="true" :draggable="false"
            :breakpoints="{'1199px': '75vw', '575px': '90vw'}"
            @maximize="maximize" @unmaximize="unMaximize"
    >
      <template #header>
        <dialog-header/>
      </template>
      <template #closeicon>
        <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M7.93933 7.00045L13.466 1.47378C13.5752 1.34625 13.6323 1.1822 13.6258 1.01441C13.6193 0.84663 13.5498 0.68747 13.431 0.568741C13.3123 0.450011 13.1532 0.380456 12.9854 0.373975C12.8176 0.367494 12.6535 0.424565 12.526 0.533783L6.99933 6.06045L1.47266 0.527116C1.34713 0.40158 1.17687 0.331055 0.999331 0.331055C0.821797 0.331055 0.651534 0.40158 0.525998 0.527116C0.400462 0.652652 0.329937 0.822915 0.329937 1.00045C0.329937 1.17798 0.400462 1.34825 0.525998 1.47378L6.05933 7.00045L0.525998 12.5271C0.45621 12.5869 0.39953 12.6604 0.359514 12.7431C0.319499 12.8258 0.297012 12.9159 0.293466 13.0077C0.289919 13.0996 0.30539 13.1911 0.338906 13.2767C0.372422 13.3622 0.423261 13.4399 0.488231 13.5049C0.553201 13.5699 0.630899 13.6207 0.716449 13.6542C0.801999 13.6877 0.893554 13.7032 0.985367 13.6996C1.07718 13.6961 1.16727 13.6736 1.24998 13.6336C1.33269 13.5936 1.40623 13.5369 1.466 13.4671L6.99933 7.94045L12.526 13.4671C12.6535 13.5763 12.8176 13.6334 12.9854 13.6269C13.1532 13.6204 13.3123 13.5509 13.431 13.4322C13.5498 13.3134 13.6193 13.1543 13.6258 12.9865C13.6323 12.8187 13.5752 12.6547 13.466 12.5271L7.93933 7.00045Z" fill="#686868"/>
        </svg>
      </template>

      <div class="chat-content">
        <resize-border :position="position" :node="$refs.dialog" @drag="dragSize" />
        <chat-messages :sended="true" :content="content" />
      </div>

      <template #footer>
        <DialogFooter v-model="sendData" @send="send"/>
      </template>
    </Dialog>
  </div>
</template>

<script>

import {DEBUG, POSITION, SUPPORT_KEY, SYNC_DELAY, SYNC_INTERVAL} from "./const";
import query from "./libs/query";

import Dialog from 'primevue/dialog';

import DialogHeader from "./components/header";
import DialogFooter from "./components/footer";
import ResizeBorder from "./components/resizeBorder";
import ChatMessages from "./components/chatMessages";
import VButton from "./components/button";

const getISODate = () => {
  const d = new Date(),
        m = d.getMonth() + 1;

  return `${d.getFullYear()}-${m < 10 ? '0' + m : m}-${d.getDate()} ${d.toTimeString().slice(0, 8)}`;
}

export default {
  name: 'support-app',
  components: {
    VButton,
    ChatMessages,
    DialogHeader,
    ResizeBorder,
    DialogFooter,
    Dialog,
  },
  data: () => ({
    dialogStyle: {width: '340px', minWidth: '340px', maxHeight: '60vh'},
    visible: false,
    position: POSITION[2],

    userKey: undefined,
    from   : undefined, // От кого сообщение
    lastDate: undefined,

    syncInterval: undefined,
    content: [],
    sendData: '',
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

      !this.lastDate && this.loadMessages();
    },

    addContent(data) {
      data.forEach(item => {
        this.content.push({
          author: item.userKey === this.userKey,
          date  : item.date,
          type  : item.type,
          content: item.content,
        });
      });

      data.length && (this.lastDate = data.pop().date);
    },

    loadMessages(date) {
      query.Post({data: {action: 'loadMessages', date}}).then(d => {
        if (d['status']) {
          this.userKey = d[SUPPORT_KEY];
          if (this.userKey !== window.localStorage.getItem(SUPPORT_KEY)) window.localStorage.setItem(SUPPORT_KEY, this.userKey);

          this.addContent(d['data']);
        }
      })
    },

    startSync() {
      this.syncInterval = setInterval(() => this.loadMessages(this.lastDate), SYNC_INTERVAL);

      setTimeout(() => this.stopSync(), SYNC_DELAY);
    },
    stopSync() { clearInterval(this.syncInterval) },
    restartSync() {
      this.stopSync();
      this.startSync();
    },

    maximize() { this.dialogStyle.maxHeight = 'initial' },
    unMaximize() { this.dialogStyle.maxHeight = '60vh' },
    dragSize(v) { this.dialogStyle.width = v + 'px'; },

    send(finish) {
      const type = this.sendData instanceof Blob ? 'file' : 'text';

      this.restartSync();

      query.Post({
        data: {
          action: 'addMessage',
          from: this.from,
          type, content: this.sendData,
        }
      }).then(d => {
        if (d['status']) this.addContent([{
          userKey: this.userKey,
          date: getISODate(),
          type,
          content: type === 'file' ? URL.createObjectURL(this.sendData) : this.sendData,
        }]);
      }).finally(finish);
    }
  },
  created() {
    let from = this.$globalSettings.from;
    if (from) from = typeof from === 'string' ? from : from(this);
    this.from = from || location.host + location.pathname;

    if (this.$globalSettings.position) this.position = this.$globalSettings.position;
  },
  mounted() {
    this.open();
  },
}
</script>
