<template>
  <div
    class="d-flex justify-center"
  >
    <SkillList />
  </div>
</template>

<script>
import SkillList from '@/components/SkillList'

export default {
  name: 'Skills',

  components: {
    SkillList,
  },

  data: () => ({
    //
  }),

  created() {
    //console.log(this.$route)
  },
}
</script>
