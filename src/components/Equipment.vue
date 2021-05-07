<template>
  <v-card
    outlined
    tile
    class="pa-1"
  >
    <h3
      class="headline--text text-center mb-2"
    >{{ labels.title }}</h3>
    <EquipmentItem type="weapon"   id="1" />
    <EquipmentItem type="armor"    id="1" />
    <EquipmentItem type="armor"    id="2" />
    <EquipmentItem type="armor"    id="3" />
    <EquipmentItem type="armor"    id="4" />
    <EquipmentItem type="armor"    id="5" />
    <EquipmentItem type="talisman" id="1" />
    <v-divider />
    <v-card-actions>
      <v-btn
        class="btn-default"
        elevation="0"
        to="talisman"
      >{{ labels.edit }}</v-btn>
      <v-spacer />
      <v-btn
        class="btn-primary"
        elevation="0"
      >{{ labels.save }}</v-btn>
    </v-card-actions>
    <template>
      <SlotEditor />
      <EquipChanger />
    </template>
  </v-card>
</template>

<script>
import EquipmentItem from '@/components/EquipmentItem'
import SlotEditor from '@/components/SlotEditor'
import EquipChanger from '@/components/EquipChanger'

export default {
  name: 'Equipment',

  components: {
    EquipmentItem,
    SlotEditor,
    EquipChanger,
  },

  props: {
    //
  },

  data: () => ({
    labels: {
      title: '装備・装飾品',
      edit: '護石管理',
      save: 'マイセット登録',
    },
  }),

  created() {
    //
  },

  mounted() {
    //
  },

  computed: {
    //
  },

  methods: {
    //
  },

}
</script>