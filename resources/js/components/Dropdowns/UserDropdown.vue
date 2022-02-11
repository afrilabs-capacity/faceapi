<template>
  <div v-if="userAvailable">
    <a
      class="text-blueGray-500 block"
      href="#pablo"
      ref="btnDropdownRef"
      v-on:click="toggleDropdown($event)"
    >
      <div class="items-center flex">
        <span
          class="w-12 h-12 text-sm text-white  inline-flex items-center justify-center rounded-full bg-red-500"
        >
         {{user.name.charAt(0)}}

        </span>
      </div>
    </a>
    <div
      ref="popoverDropdownRef"
      class="bg-white text-base z-50 float-left py-2 list-none text-left rounded shadow-lg min-w-48"
      v-bind:class="{
        hidden: !dropdownPopoverShow,
        block: dropdownPopoverShow,
      }"
    >

      <div class="h-0 my-2 border border-solid border-blueGray-100" />
      <button
        @click="logUserOut"
        class="text-sm py-2 px-4 font-normal block w-full whitespace-nowrap bg-transparent text-blueGray-700"
      >
        log out
      </button>
    </div>
  </div>
</template>

<script>
import { createPopper } from "@popperjs/core";

import image from "../../assets/img/team-1-800x800.jpg";

export default {
    props: ['user'],
  data() {
    return {
      dropdownPopoverShow: false,
      image: image,
    };
  },
    computed:{
        userAvailable(){
            if (this.user) {
                return Object.keys(this.user).length
            }
            return  null
        }
    },
  methods: {
    toggleDropdown: function (event) {
      event.preventDefault();
      if (this.dropdownPopoverShow) {
        this.dropdownPopoverShow = false;
      } else {
        this.dropdownPopoverShow = true;
        createPopper(this.$refs.btnDropdownRef, this.$refs.popoverDropdownRef, {
          placement: "bottom-start",
        });
      }
    },

      logUserOut(){
          axios.get("/api/logout")
              .then(res => {
                  if(res.data.logout) {
                      window.location = "/"
                  }
              })
      }
  },
};
</script>
