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
        left: 0,
        top: 0,
        visible: false,
      };
    },

    methods: {
      show(event, el) {
        event.preventDefault();
        this.visible = true;

        const rect = el.getClientRects()[0];
        if (rect) {
          this.top = Math.min(event.clientY, window.innerHeight-rect.height-20);
          this.left = Math.min(event.clientX, window.innerWidth-rect.width-20);
        } else {
          this.top = event.clientY;
          this.left = event.clientX;
        }

        const _m = this;
        function hideContextMenu(e) {
          _m.visible = false;
          document.removeEventListener('click', hideContextMenu);
        }
        document.addEventListener('click', hideContextMenu, true);
      },
    },
  }
</script>
