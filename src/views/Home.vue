<template>
  <div
    class="d-flex flex-wrap justify-space-between align-stretch"
  >
    <div
      class="align-self-start order-first mr-0 mb-3 pa-1 col-12 col-sm-6 col-md-3"
    >
      <Equipment />
    </div>
    <div
      class="align-self-start order-last order-md-2 mr-0 mb-3 pa-1 col-12 col-sm-12 col-md-6"
    >
      <SkillBreakdown />
    </div>
    <div
      class="align-self-start order-2 order-md-last mr-0 mb-3 pa-1 col-12 col-sm-6 col-md-3"
    >
      <Status />
    </div>
  </div>
</template>

<script>
import Equipment from '@/components/Equipment'
import SkillBreakdown from '@/components/SkillBreakdown'
import Status from '@/components/Status'

export default {
  name: 'Home',

  components: {
    Equipment,
    SkillBreakdown,
    Status,
  },

  data: () => ({
    //
  }),

  created() {
    //console.log(this.$route)
  },
}
</script>
