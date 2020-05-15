<template>
  <ul ref="contextmenu" class="jc-contextmenu md-list md-elevation-7 md-theme-default"
    v-show="visible"
    :style="{position:'fixed','z-index':200,left:left+'px',top:top+'px'}">
    <slot></slot>
  </ul>
</template>

<script>
  export default {
    data() {
      return {
        visible: false,
        left: 0,
        top: 0,
      };
    },

    methods: {
      show(event) {
        event.preventDefault()
        this.top = event.clientY;
        this.left = event.clientX;
        this.visible = true;

        const _m = this;
        function hideContextMenu(e) {
          // if (! _m.$refs.contextmenu.contains(e.target)) {
          //   _m.visible = false;
          //   document.removeEventListener('click', hideContextMenu);
          // }
          _m.visible = false;
          document.removeEventListener('click', hideContextMenu);
        }
        document.addEventListener('click', hideContextMenu, true);
      },
    },
  }
</script>
