<template>
  <div
    class="v-talisman"
    :style="`width: ${$props.size}px; height: ${$props.size}px;`"
  >
    <template v-if="$props.slotType == 3">
      <span
        class="mhrssi-talisman-three-slot"
        :style="`font-size: ${$props.size}px;`"
      ><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span></span>
    </template>
    <template v-else-if="$props.slotType == 2">
      <span
        class="mhrssi-talisman-two-slot"
        :style="`font-size: ${$props.size}px`"
      ><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></span>
    </template>
    <template v-else-if="$props.slotType == 1">
      <span
        class="mhrssi-talisman-one-slot"
        :style="`font-size: ${$props.size}px`"
      ><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
    </template>
    <template v-else>
      <v-icon
        class="grey--text text--darken-1"
        :size="$props.size"
      >mdi-minus</v-icon>
    </template>
  </div>
</template>

<script>
export default {
  name: 'Talisman',

  props: {
    slotType: {
      Type: Number,
      default: 0
    },
    attached: {
      Type: Number,
      default: null
    },
    size: {
      Type: Number,
      default: 28
    },
  },

  data: () => ({
  }),

}
</script>