import axios from "axios";
import { defineStore } from "pinia";

export const global_store = defineStore("global_store", {
  state: () => ({
    unseen_vouchers: [],
  }),
  getters: {
    get_auth_info: function () {
      return this.auth_info;
    },
  },
  actions: {
    get_all_pending_expense: async function () {
      let response = await axios.get("account-expenses?is_seen=0&get_all=1");
      if (response.status == 200) {
        this.unseen_vouchers = response.data.data;
      }
    },
  },
});
