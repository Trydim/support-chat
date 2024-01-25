<template>
  <div class="left-border" @mousedown="startResize"></div>
</template>

<script>

export default {
  name: 'left-border',
  props: {
    node: Object,
  },
  emits: ['drag'],
  data: () => ({
    size: undefined,
    startX: undefined,
  }),
  methods: {
    startResize(e) {
      const startX = e.clientX;
      const mouseMove = (e) => {
        this.$emit('drag', startX > e.clientX ? 1 : -1);
      }

      document.addEventListener('mousemove', mouseMove);
      document.addEventListener('mouseup', () => {
        document.removeEventListener('mousemove', mouseMove);
      }, {once: true});
    },
  },
  mounted() {
    this.$nextTick(() => this.size = this.node.mask.firstElementChild.getBoundingClientRect());
  }
}

</script>
