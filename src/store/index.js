import Vue from "vue"
import Vuex from "vuex"

Vue.use(Vuex)

const getInitialState = () => {
  return {
    weapons: [],
    armors: [],
    talismans: [],
    decorations: [],
    skills: [],
    skill_evaluation: [],
    weapon_meta: [],
    ammo: [],
    weapon: {
      data: {},
      slots: {},
    },
    head: {
      data: {},
      level: 1,
      slots: {},
    },
    chest: {
      data: {},
      level: 1,
      slots: {},
    },
    arms: {
      data: {},
      level: 1,
      slots: {},
    },
    waist: {
      data: {},
      level: 1,
      slots: {},
    },
    legs: {
      data: {},
      level: 1,
      slots: {},
    },
    talisman: {
      data: {},
      slots: {},
    },
    aggs: {},
    player: {
      gender: 'female',
      items: [],
    },
  }
}

const state = () => getInitialState()

const getters = {
  // Get all weapons of the specified weapon type
  // (: 指定武器種の全武器を取得
  weaponsKindOf: (state) => (kind) => {
    return state.weapons.filter(weapon => weapon.type == kind)
  },
  // Get all armor in the specified part
  // (: 指定部位の全防具を取得
  armorsKindOf: (state) => (part) => {
    return state.armors.filter(armor => armor.part == part)
  },
  // Get singular data with specified ID for each data table
  // (: 各データテーブル単位で指定IDの単一データを取得
  itemsById: (state) => (type, id) => {
    return state[type].find(item => item.id === id)
  },
  // Whether or not the item is currently equipped
  // (: 現在装備中のアイテムがあるかどうか
  equipmentExists: (state) => (kind=null) => {
    let isExists = false,
        parts = kind == null ? ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman']: [kind]
    //console.log('equipmentExists:', parts)
    parts.forEach(part => {
      //console.log(state[part])
      if (state[part] && Object.prototype.hasOwnProperty.call(state[part], 'data')) {
        for (let key in state[part].data) {
          if (key === 'id' && state[part].data[key] != 0) {
            isExists = true
          }
        }
      }
    })
    return isExists
  },
  // Get the currently equipped item data by specifying the type and key
  // (: 現在装備中の装備データを種別・キー名指定で取得
  equipmentKindOf: (state) => (kind, key=null) => {
    return !key ? state[kind].data: state[kind].data[key]
  },
  // Get the level of the currently equipped armor
  // (: 現在装備中の防具のレベルを取得
  armorLevelKindOf: (state) => (part) => {
    if (Number.isFinite(part)) {
      part = ['head', 'chest', 'arms', 'waist', 'legs'][part]
    }
    return state[part].level
  },
  // Get the slot status of the currently equipped item
  // (: 現在装備中の装備のスロット状況を取得
  currentSlotsKindOf: (state) => (kind) => {
    return state[kind].slots
  },
  // Get the skills granted by decoration in slots
  // (: スロットの装飾品によって付与されているスキルを取得
  currentSkillsInSlots: (state) => (kind) => {
    let skills = []
    for (let [, decoId] of Object.entries(state[kind].slots)) {
      let decoration = state.decorations.find(item => item.id === decoId)
      skills = skills.concat(Object.keys(decoration.skills))
    }
    return skills
  },
  // Get the player's specified data
  // (: プレイヤーの指定データを取得
  playerDataOf: (state) => (target) => {
    return state.player[target]
  },
}

const mutations = {
  // Usage: $store.commit('setItems', {property: 'weapons', data: response.data})
  setItems: (state, payload) => {
    state[payload.property] = payload.data
  },
  // Usage: $store.commit('addItem', {property: 'talismans', data: newTalisman})
  addItem: (state, payload) => {
    //console.log('$store.commit::addItem:', payload)
    state[payload.property].push(payload.data)
  },
  // Usage: $store.commit('updateEquipItem', {property: 'head', data: {...}, slots: []})
  updateEquipItem: (state, payload) => {
    if (Object.prototype.hasOwnProperty.call(payload, 'data')) {
      state[payload.property].data = payload.data
    }
    if (Object.prototype.hasOwnProperty.call(payload, 'level')) {
      state[payload.property].level = payload.level
    }
    if (Object.prototype.hasOwnProperty.call(payload, 'slots')) {
      state[payload.property].slots = payload.slots
    }
  },
  // Usage: $store.commit('updateArmorLevel', {part: 'head', level: n})
  updateArmorLevel: (state, payload) => {
    state[payload.part].level = payload.level
  },
  // Usage: $store.commit('updateAggSkills', {aggrigation: {}})
  updateAggSkills: (state, payload) => {
    state.aggs = payload.aggrigation
  },
  // Usage: $store.commit('clearEquipments', {kind: 'all'})
  clearEquipments: (state, payload) => {
    if (payload.kind === 'all') {
      ['weapon', 'head', 'chest', 'arms', 'waist', 'legs', 'talisman'].forEach(key => {
        //console.log(state[key], getInitialState()[key])
        state[key] = Object.assign(state[key], getInitialState()[key])
      })
    } else {
      Object.assign(state[payload.kind], getInitialState()[payload.kind])
    }
  },
  // Usage: $store.commit('updatePlayerData', {property: 'gender', value: 'female'})
  updatePlayerData: (state, payload) => {
    state.player[payload.property] = payload.value
  },
  // Usage: $store.commit('resetState')
  resetState: (state) => {
    Object.assign(state, getInitialState())
  },
  // Usage: $store.commit('resetMasterData', {property: 'talismans'})
  resetMasterData: (state, payload) => {
    Object.assign(state[payload.property], getInitialState()[payload.property])
  },
}

const actions = {
  initData: ({ commit }, payload) => {
    commit('setItems', payload)
  },
  addData: ({ commit }, payload) => {
    commit('addItem', payload)
  },
  setEquipment: ({ commit }, payload) => {
    commit('updateEquipItem', payload)
  },
  setArmorLevel: ({ commit }, payload) => {
    commit('updateArmorLevel', payload)
  },
  setAggSkills: ({ commit }, payload) => {
    commit('updateAggSkills', payload)
  },
  removeEquipment: ({ commit }, payload) => {
    commit('clearEquipments', payload)
  },
  setPlayerData: ({ commit }, payload) => {
    commit('updatePlayerData', payload)
  },
  resetState: ({ commit }) => {
    commit('resetState')
  },
  resetMasterData: ({ commit }, payload) => {
    commit('resetMasterData', payload)
  },
}

const store = new Vuex.Store({
  state,
  getters,
  mutations,
  actions,
})

export default store