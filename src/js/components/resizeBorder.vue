<template>
  <div :class="borderClass" @mousedown="startResize"></div>
</template>

<script>

export default {
  name: 'resize-border',
  props: {
    position: String,
    node: Object,
  },
  emits: ['drag'],
  data: () => ({
    borderClass: '',
  }),
  methods: {
    startResize(e) {
      const currentW = this.node.mask.firstElementChild.getBoundingClientRect().width,
            startX = e.clientX,
            k = this.borderClass === 'right-border' ? 1 : -1,
            mouseMove = (e) => this.$emit('drag', currentW + (e.clientX - startX) * k);

      document.addEventListener('mousemove', mouseMove);
      document.addEventListener('mouseup', () => {
        document.removeEventListener('mousemove', mouseMove);
      }, {once: true});
    },
  },
  created() {
    this.borderClass = this.position.toLowerCase().includes('left') ? 'right-border' : 'left-border';
  },
}

</script>
