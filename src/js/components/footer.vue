<template>
  <div class="chat-input-holder">
    <img v-if="showImage" :src="file.path" alt="">
    <textarea v-else class="chat-input" :disabled="disabled" v-model="message" @focus="onEnterEvent" @blur="removeEnterEvent"></textarea>



    <input type="submit" class="message-send" value="Send" @click="send">
  </div>
</template>

<script>

const pasteFile = function (e) {
  const items = (e.clipboardData || e.originalEvent.clipboardData).items;

  for (const index in items) {
    const item = items[index];

    if (item.kind === 'file') {
      const blob = item.getAsFile();

      this.file.blob = blob;
      this.file.type = blob.type;
      this.file.size = blob.size;
      this.file.path = URL.createObjectURL(blob);
    }
  }
}

export default {
  name: 'dialog-footer',
  props: {
    modelValue: {
      required: true,
    },
  },
  events: ['update:modelValue'],
  data: () => ({
    disabled: false,

    file: {
      blob: null,
      type: '',
      size: 0,
      path: ''
    }
  }),
  computed: {
    message: {
      get() { return this.modelValue },
      set(v) { this.$emit('update:modelValue', v) }
    },

    showImage() { return this.file.type.includes('image') }
  },
  watch: {
    'file.blob'() {
      this.message = this.file.blob;
    },
  },
  methods: {
    onEnterEvent() {
      document.addEventListener('keydown', this.onKeyDown);
    },
    removeEnterEvent() {
      document.removeEventListener('keydown', this.onKeyDown);
    },

    send() {
      this.disabled = true;
      this.$emit('send', () => {
        this.disabled = false;
        this.message = '';
      });
    },
  },
  mounted() {
    this.onKeyDown = e => {
      if (e.ctrlKey && e.key === 'Enter') this.send();
    }
    this.onPaste = pasteFile.bind(this);

    document.addEventListener('paste', this.onPaste);
  },
  unmounted() {
    document.removeEventListener('paste', this.onPaste);
  }
}

</script>
