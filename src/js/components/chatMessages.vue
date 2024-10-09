<template>
  <div ref="msgContent" class="chat-messages" @scroll="autoScroll = false">
    <div v-for="(item, index) of content" :key="index"
         class="message-box-holder" :class="{'message-right': item.author}">
      <div v-if="item.type === 'text'" class="message-box" v-html="getMsgContent(item.content)"></div>

      <template v-else>
        <VImage v-if="isImage(item)" class="message-img" :src="item.content" alt="Image" preview />
        <video v-else-if="isVideo(item)" class="message-video" :src="item.content" controls="controls">

        </video>
        <a v-else :href="item.content" :download="getOriginalName(item.content)">Скачать файл: {{ getExtensionName(item.content) }}</a>
      </template>
      <div class="message-info">{{ item.author ? '' : 'Специалист,' }} {{ getChatDate(item.date) }}</div>
    </div>
  </div>
</template>

<script>

import VImage from 'primevue/image';

const getISODate = () => {
  const d = new Date(),
        m = d.getMonth() + 1;

  return `${d.getFullYear()}-${m < 10 ? '0' + m : m}-${d.getDate()}`;
}

export default {
  name: 'chat-messages',
  props: {
    content: Array,
  },
  components: {VImage},
  data() {
    return {
      autoScroll: true,
      today: getISODate(),
    };
  },
  watch: {
    content: {
      deep: true,
      handler() { this.autoScroll && this.scrollChat() },
    },
  },
  methods: {
    scrollChat() {

      // отключать автопрокрутку при обновлении если пользователь прокрутил вверх
      // включать автопрокрутку при отправлении сообщений
      setTimeout(() => {
        const n = this.$refs.msgContent.lastElementChild;
        n && n.scrollIntoView();
      }, 300);
    },

    getMsgContent(content) { return content.replaceAll('\n', '<br>') },
    isImage(item) { return item.type.includes('image') || /(^blob|\.(png|svg|jpg|gif)$)/i.test(item.content) },
    isVideo(item) { return item.type.includes('video') || /(\.(avi|mp4|mov|mkv|webm|flv|swf)$)/i.test(item.content) },
    getOriginalName(content) { return content.replace(/.+\/upload\//, location.host) },
    getExtensionName(content) { return content.replace(/^.+\./, '') },

    getChatDate(date) {
      return this.today === date.slice(0, 10) ? date.slice(11) : new Date(Date.parse(date)).toLocaleString();
    },
  },
}

</script>
