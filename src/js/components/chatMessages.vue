<template>
  <div ref="msgContent" class="chat-messages" @scroll="autoScroll = false">
    <div v-for="(item, index) of content" :key="index"
         class="message-box-holder" :class="{'message-right': item.author}">
      <div v-if="item.type === 'text'" class="message-box" v-html="getMsgContent(item.content)"></div>

      <template v-else>
        <VImage v-if="isImage(item.content)" class="message-img" :src="item.content" alt="Image" preview></VImage>
        <div v-else class="message-box">{{ getOriginalName(item.content) }}</div>
      </template>
      <div class="message-info">{{ item.author ? '' : 'Специалист,' }} {{ getChatDate(item.date) }}</div>
    </div>
  </div>
</template>

<script>

import VImage from 'primevue/image';

export default {
  name: 'chat-messages',
  props: {
    content: Array,
  },
  components: {VImage},
  data() {
    return {
      autoScroll: true,
      today: new Date().toLocaleString().slice(0, 10),
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
    isImage(str) { return /(.png|.svg|.jpg)$/i.test(str) },
    getOriginalName(content) { return content.replace(/.+\/upload\//, '') },

    getChatDate(date) {
      let r;

      date = new Date(Date.parse(date.replace(',', ''))).toLocaleString();
      r = date.slice(0, 10);

      return this.today === r ? date.slice(12) : date;
    }
  },
}

</script>
