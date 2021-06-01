<template>
  <v-rating
    v-model="$props.evaluation"
    :empty-icon="$props.icon"
    :background-color="starColor(false)"
    :color="starColor(true)"
    length="6"
    :size="$props.size"
    dense
  >
    <template v-slot:item="props">
      <v-icon
        :color="starColor(props.isFilled, props.index)"
      >{{ $props.icon }}</v-icon>
    </template>
  </v-rating>
</template>

<script>
export default {
  name: 'StarRating',

  props: {
    evaluation: {
      Type: Number,
      default: 0
    },
    icon: {
      Type: String,
      default: 'mdi-star'
    },
    size: {
      Type: Number,
      default: 28
    },
  },

  data: () => ({
  }),

  methods: {
    starColor: function(isFilled, index=0) {
      if (isFilled) {
        return index == 5 ? 'light-blue accent-4' : 'amber accent-4'
      } else {
        return index == 5 ? 'transparent' : (this.$vuetify.theme.isDark ? 'grey darken-2': 'grey lighten-1')
      }
    },
  },

}
</script>