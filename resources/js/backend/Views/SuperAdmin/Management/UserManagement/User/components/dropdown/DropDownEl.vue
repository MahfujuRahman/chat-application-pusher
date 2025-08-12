<template lang="">
  <div class="col-md-6">
    <label> Assigned To {{ setup.module_name }} </label>
    <!-- {{ value }} -->
    <div class="custom_drop_down">
      <div class="selected_list  c-pointer position-relative" @click="show_list = !show_list">
        <template v-if="!validSelected.length">
          <div class="">Select {{ setup.module_name }}</div>
          <i :class="show_list ? 'fa fa-angle-up' : 'fa fa-angle-down'"  style="position: absolute; right: 10px;"></i>
        </template>

        <template v-else>
          <div v-for="item in validSelected" :key="item.id" :id="item.id" class="selected_item">
            <div class="label">
              {{ item.name || item.title  }}
            </div>
            <div @click.prevent="remove_item(item)" class="remove">
              <i class="fa fa-close"></i>
            </div>
          </div>
        </template>
      </div>
      <div class="drop_down_items" v-if="show_list">
        <div class="drop_down_data_search">
          <input @keyup="search_item($event)" class="form-control" placeholder="search.." id="table_search_box" type="search" />

          <button type="button" @click.prevent="show_list = false" class="btn btn-danger">
            <i class="fa fa-close"></i>
          </button>
        </div>
        <ul class="option_list custom_scroll">
          <li v-if="!all?.data?.length" class="option_item text-center text-muted">
            <div class="p-2">No users available</div>
          </li>
          <li class="option_item" v-for="item in all.data" :key="item.id">
            <label :for="`drop_item_${item.id}`">
              <div class="check_box">
                <input
                  @change="set_selected(item, $event)"
                  :checked="is_selected(item)"
                  type="checkbox"
                  :id="`drop_item_${item.id}`"
                  class="form-check-input ml-0"
                />
              </div>
              <div class="label">{{ item.name || item.title }}</div>
            </label>
          </li>
        </ul>
        <div class="drop_down_footer data_list" v-if="all?.links && all.links.length > 3">
          <Pagination
            :data="all"
            :method="handlePagination"
          />
        </div>
      </div>
    </div>
  </div>
  <input type="hidden" :id="name" :name="name" :value="multiple ? `[${selected_ids}]` : `${selected_ids}`" />
</template>
<script>
import { mapActions, mapState, mapWritableState } from "pinia";
import { store } from "../../store";
import debounce from "../../helpers/debounce";
import setup from "../../setup";
import Pagination from "../../../../../../../GlobalComponents/Pagination.vue";

export default {
  components: {
    Pagination,
  },
  props: {
    multiple: {
      type: Boolean,
      default: false,
    },
    name: {
      type: String,
      default: "users_" + parseInt(Math.random() * 1000),
    },
    value: {
      type: Array,
      default: [],
    },
  },
  created: function () {
    // Only fetch data if not already available to prevent unnecessary API calls
    if (!this.all?.data?.length) {
      this.get_all();
    }
    this.$watch("value", function (v) {
      this.processValue(v);
    }, { immediate: true });
    
    // Watch for when all.data becomes available and re-process the value
    this.$watch("all.data", function (newData) {
      if (newData && newData.length && this.value && Array.isArray(this.value)) {
        console.log('User DropDown - all.data loaded, re-processing value');
        // Re-trigger the value watcher
        const currentValue = this.value;
        this.$nextTick(() => {
          // Trigger value processing again now that we have data
          this.processValue(currentValue);
        });
      }
    });
  },
  data: () => ({
    selected: [],
    show_list: false,
    setup,
  }),
  methods: {
    ...mapActions(store, ["get_all", "set_paginate", "set_page"]),
    
    processValue: function(v) {
      console.log('User DropDown - processValue called with:', v);
      console.log('User DropDown - all.data:', this.all?.data?.length ? `${this.all.data.length} items` : 'no data');
      
      // Filter out undefined, null, or empty values
      const cleanValue = Array.isArray(v) ? v.filter(item => item !== null && item !== undefined && item !== '') : [];
      console.log('User DropDown - cleanValue:', cleanValue);
      
      // If cleanValue is an array of objects with valid id, set selected directly
      if (cleanValue.length && typeof cleanValue[0] === 'object' && cleanValue[0].id) {
        console.log('User DropDown - setting selected from objects:', cleanValue);
        this.selected = cleanValue.filter(item => item && item.id);
      } else if (cleanValue.length && this.all && Array.isArray(this.all.data)) {
        // fallback for array of ids, only if all.data is available
        console.log('User DropDown - looking up users by IDs in all.data');
        this.selected = this.all.data.filter(item => item && item.id && cleanValue.includes(item.id));
        console.log('User DropDown - found users:', this.selected);
      } else {
        console.log('User DropDown - no valid data, clearing selection');
        this.selected = [];
      }
      
      console.log('User DropDown - final selected:', this.selected);
    },
    
    // Wrapper method for pagination
    async handlePagination(url) {
      try {
        if (url) {
          // Extract page number from URL
          const urlParams = new URLSearchParams(url.split('?')[1]);
          const page = urlParams.get('page');
          if (page) {
            this.set_page(parseInt(page));
          }
        }
        await this.get_all();
      } catch (error) {
        console.error('Pagination error:', error);
      }
    },
    
    search_item: debounce(async function (event) {
      let value = event.target.value;
      this.search_key = value;
      // Store current selection to preserve it
      const currentSelection = [...this.selected];
      this.only_latest_data = true;
      await this.get_all();
      this.only_latest_data = false;
      // Restore selection after search
      this.selected = currentSelection;
    }, 500),
    set_selected: function (item, event) {
      if (!this.multiple) {
        this.selected = [item];
        return;
      }
      // Defensive: event may be undefined when called from watcher
      const checked = event && event.target ? event.target.checked : true;
      if (checked) {
        if (!this.selected.find((i) => i.id === item.id)) {
          this.selected.push(item);
        }
      } else {
        this.selected = this.selected.filter((i) => i.id != item.id);
      }
    },
    is_selected: function (item) {
      return this.selected.find((i) => i.id == item.id);
    },
    remove_item: function (item) {
      this.selected = this.selected.filter((i) => i.id != item.id);
    },
  },
  computed: {
    ...mapState(store, ["all"]),
    ...mapWritableState(store, ["search_key", "page", "paginate"]),
    validSelected: function () {
      if (!this.selected || !Array.isArray(this.selected)) {
        return [];
      }
      return this.selected.filter(item => item && item.id);
    },
    selected_ids: function () {
      if (!this.validSelected || this.validSelected.length === 0) {
        return "";
      }
      return this.validSelected.map((i) => i.id).join(",");
    },
  },
};
</script>
