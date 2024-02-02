<template>
  <div class="chat-footer">
    <input id="upload" type="file" hidden @change="uploadFile">
    <label for="upload" class="chat-clip">
      <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
        <g clip-path="url(#clip0_1071_2345)">
          <path d="M3.70885 21.6479C2.77625 21.6479 1.88085 21.262 1.16723 20.5476C-0.415056 18.9602 -0.415056 16.3783 1.16686 14.7919L13.5047 1.69023C15.4297 -0.237867 18.3784 -0.0646174 20.5303 2.09001C21.4945 3.05594 22.0356 4.44848 22.0153 5.91216C21.995 7.3604 21.4292 8.74638 20.4622 9.71507L11.1377 19.6419C10.8781 19.92 10.4429 19.9327 10.1659 19.6718C9.88952 19.4106 9.87611 18.9747 10.1367 18.6976L19.4753 8.75566C20.2058 8.02382 20.6248 6.98567 20.6403 5.89289C20.6557 4.79945 20.2608 3.76852 19.5582 3.06417C18.2382 1.74142 16.089 1.04877 14.4913 2.6503L2.15372 15.752C1.09291 16.8155 1.09325 18.5236 2.13997 19.5731C2.63083 20.0643 3.21348 20.3036 3.83395 20.2661C4.44789 20.2286 5.07901 19.9137 5.61114 19.3802L15.4279 8.9316C15.7837 8.57513 16.4987 7.70132 15.771 6.97188C15.3588 6.55904 15.0694 6.58447 14.9742 6.59238C14.7023 6.61644 14.3847 6.80447 14.055 7.13516L6.66611 14.9936C6.4052 15.271 5.96967 15.2844 5.69431 15.0228C5.41759 14.7623 5.40487 14.3257 5.66509 14.0493L13.0674 6.1761C13.6494 5.59138 14.2444 5.27444 14.8501 5.22013C15.3228 5.17817 16.0258 5.27891 16.7428 5.99804C17.8071 7.06433 17.6747 8.62841 16.4145 9.89135L6.59772 20.3393C5.81397 21.1258 4.86761 21.5826 3.91817 21.641C3.84839 21.6458 3.77861 21.6479 3.70883 21.6479L3.70885 21.6479Z" fill="#686868"/>
        </g>
      </svg>
    </label>

    <div v-if="showImage" class="chat-input-file-wrap" :class="{'document': !fileIsImage}">
      <img v-if="fileIsImage" class="chat-input-img" alt="" :src="file.path">
      <div v-else class="chat-input-file">{{ file.name }}</div>
      <i class="chat-input-file-remove" @click="removeFile">
        <svg width="15" height="15" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M7.93933 7.00045L13.466 1.47378C13.5752 1.34625 13.6323 1.1822 13.6258 1.01441C13.6193 0.84663 13.5498 0.68747 13.431 0.568741C13.3123 0.450011 13.1532 0.380456 12.9854 0.373975C12.8176 0.367494 12.6535 0.424565 12.526 0.533783L6.99933 6.06045L1.47266 0.527116C1.34713 0.40158 1.17687 0.331055 0.999331 0.331055C0.821797 0.331055 0.651534 0.40158 0.525998 0.527116C0.400462 0.652652 0.329937 0.822915 0.329937 1.00045C0.329937 1.17798 0.400462 1.34825 0.525998 1.47378L6.05933 7.00045L0.525998 12.5271C0.45621 12.5869 0.39953 12.6604 0.359514 12.7431C0.319499 12.8258 0.297012 12.9159 0.293466 13.0077C0.289919 13.0996 0.30539 13.1911 0.338906 13.2767C0.372422 13.3622 0.423261 13.4399 0.488231 13.5049C0.553201 13.5699 0.630899 13.6207 0.716449 13.6542C0.801999 13.6877 0.893554 13.7032 0.985367 13.6996C1.07718 13.6961 1.16727 13.6736 1.24998 13.6336C1.33269 13.5936 1.40623 13.5369 1.466 13.4671L6.99933 7.94045L12.526 13.4671C12.6535 13.5763 12.8176 13.6334 12.9854 13.6269C13.1532 13.6204 13.3123 13.5509 13.431 13.4322C13.5498 13.3134 13.6193 13.1543 13.6258 12.9865C13.6323 12.8187 13.5752 12.6547 13.466 12.5271L7.93933 7.00045Z" fill="#686868"></path>
        </svg>
      </i>
    </div>

    <textarea v-else ref="textarea" class="chat-input" rows="1"
              :disabled="disabled" v-model="message"
              @focus="onEnterEvent" @input="changeTextarea" @blur="removeEnterEvent"
    ></textarea>

    <button type="button" class="message-send" @click="send">
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M12.8141 12.1969L5.2821 13.4519C5.19551 13.4664 5.11425 13.5034 5.04649 13.5592C4.97873 13.615 4.92686 13.6877 4.8961 13.7699L2.2991 20.7279C2.0511 21.3679 2.7201 21.9779 3.3341 21.6699L21.3341 12.6699C21.4585 12.6076 21.5631 12.5118 21.6362 12.3934C21.7093 12.275 21.7481 12.1386 21.7481 11.9994C21.7481 11.8603 21.7093 11.7238 21.6362 11.6054C21.5631 11.487 21.4585 11.3913 21.3341 11.3289L3.3341 2.32893C2.7201 2.02193 2.0511 2.63193 2.2991 3.27093L4.8971 10.2289C4.92786 10.3112 4.97973 10.3838 5.04749 10.4397C5.11525 10.4955 5.19651 10.5325 5.2831 10.5469L12.8151 11.8019C12.8621 11.8094 12.9049 11.8334 12.9357 11.8696C12.9666 11.9058 12.9836 11.9518 12.9836 11.9994C12.9836 12.047 12.9666 12.093 12.9357 12.1292C12.9049 12.1654 12.8621 12.1894 12.8151 12.1969" fill="#599CFF"/>
      </svg>
    </button>
  </div>
</template>

<script>

const pasteFile = function (e) {
  const items = (e.clipboardData || e.originalEvent.clipboardData).items;

  for (const index in items) {
    const item = items[index];

    if (item.kind === 'file') {
      this.setFileParam(item.getAsFile())
      break;
    }
  }
}

const clearInput = (node) => {
  let input = document.createElement('input');
  input.type = 'file';
  node.files = input.files;
}

export default {
  name: 'dialog-footer',
  props: {
    modelValue: {},
  },
  events: ['update:modelValue'],
  data: () => ({
    disabled: false,

    file: {
      blob: null,
      type: '',
      name: '',
      size: 0,
      path: ''
    }
  }),
  computed: {
    message: {
      get() { return this.modelValue },
      set(v) { this.$emit('update:modelValue', v) }
    },

    showImage() { return this.file.size },
    fileIsImage() { return this.file.type.includes('image') },
  },
  watch: {
    'file.blob'() {
      this.message = this.file.blob;
    },
  },
  methods: {
    onEnterEvent() { document.addEventListener('keydown', this.onKeyDown) },
    removeEnterEvent() { document.removeEventListener('keydown', this.onKeyDown) },

    changeTextarea() {
      const t = this.$refs.textarea;

      t.style.height = '40px';
      t.style.height = t.scrollHeight + 'px';
    },

    setFileParam(blob) {
      this.file.blob = blob;
      this.file.type = blob.type;
      this.file.name = blob.name;
      this.file.size = blob.size;
      this.file.path = URL.createObjectURL(blob);
    },
    uploadFile(e) {
      const blob = e.target.files[0];

      if (blob) {
        this.setFileParam(blob);
        clearInput(e.target);
      }
    },
    removeFile() { this.file = {blob: null, type: '', name: '', size: 0, path: ''} },

    send() {
      if (!this.message) return;
      this.disabled = true;

      this.$emit('send', () => {
        this.disabled = false;
        this.message = '';
        this.removeFile();
        this.$nextTick(() => {
          this.changeTextarea();
          this.$refs.textarea.focus();
        });
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
