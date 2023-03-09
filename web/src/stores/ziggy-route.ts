import { defineStore } from "pinia";
import { Config } from "ziggy-js";

export interface ZiggyState {
  ziggyValue: Config
}

export const useZiggyRouteStore = defineStore("ziggyRoute", {
  state: (): ZiggyState => ({
    ziggyValue: {
        url: 'localhost',
        port: 8000,
        defaults: {},
        routes: {
            'api.get.db.module.profile.read': {
                uri: 'api/get/dashboard/module/profile/read',
                methods: ['GET', 'HEAD']
            },
            'api.get.db.core.user.menu': {
                uri: 'api/get/dashboard/core/user/menu',
                methods: ['GET', 'HEAD']
            },
            'api.get.db.core.user.api': {
                uri: 'api/get/dashboard/core/user/api',
                methods: ['GET', 'HEAD']
            }
        }
    }
  }),
  getters: {
    getZiggy(state): Config {
      return state.ziggyValue;
    },
  },
  actions: {
    setZiggy(ziggy: Config) {
      this.ziggyValue = ziggy;
    },
  },
});
