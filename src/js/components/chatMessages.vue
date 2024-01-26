<template>
  <div class="chat-messages">
    <div v-for="(item, index) of content" :key="index"
         class="message-box-holder" :class="{'message-right': item.author}">
      <div v-if="item.type === 'text'" class="message-box">{{ item.content }}</div>

      <VImage v-else class="message-img" :src="item.content" alt="Image" preview></VImage>
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
      today: new Date().toLocaleString().slice(0, 10),
    };
  },
  methods: {
    getChatDate(date) {
      let r;

      date = new Date(Date.parse(date.replace(',', ''))).toLocaleString();
      r = date.slice(0, 10);

      return this.today === r ? date.slice(12) : date;
    }
  },
}

</script>
