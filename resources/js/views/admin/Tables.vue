<template>
  <div v-if="!isLoading" class="flex flex-wrap mt-4">
      <div class="w-full mb-12 px-4">
        <div class="relative flex w-full flex-wrap items-stretch">
          <span
              class="z-10 h-full leading-snug font-normal absolute text-center text-blueGray-300 absolute bg-transparent rounded text-base items-center justify-center w-8 pl-3 py-3"
          >
            <i class="fas fa-search"></i>
          </span>
            <input
                type="text"
                v-model="search"
                placeholder="Search here..."
                class="border-0 px-3 py-3 placeholder-blueGray-300 text-blueGray-600 relative bg-white bg-white rounded text-sm shadow outline-none focus:outline-none focus:ring w-full pl-10"
            />
        </div>
    </div>
    <div class="w-full mb-12 px-4">
      <card-table :list="websites" :headers="headers" @delete="performDelete"/>
    </div>

  </div>
</template>
<script>
import _ from 'lodash'
import {debounce} from "lodash";
import CardTable from "../../components/Cards/CardTable.vue";

export default {
  components: {
    CardTable,
  },
    data(){
      return {
          isLoading: false,
          websites: [],
          meta: {},
          search: "",
          headers: ["website name", "unique ID", "users", "action"]
      }
    },
    watch: {
        search: debounce(function (e) {
            return this.loadWebsites(e)
        }, 500)
    },
    mounted(){
      this.loadWebsites()
    },
    methods:{
         performDelete(item){
            this.isLoading = true
            axios.delete("/api/websites/"+item.unique_id)
                    .then(res => {
                        this.loadWebsites()
                    })
                    .catch(err => {
                        console.log(err)
                    })
                    .finally(() => {
                        this.isLoading = false
                        this.emitter.emit("reload_navbar", true);
                    })
        },
      loadWebsites(search = null){
          this.isLoading = true
          let url = "/api/load-websites"
          if (search) {
              url = url+"?keyword="+search
          }
          axios.get(url)
                .then(res => {
                    console.log(res.data.meta)
                    this.websites = res.data.data
                    this.meta = res.data.meta
                })
                .catch(err => {
                    console.log(err)
                })
                .finally(() =>{
                    this.isLoading = false
                })
      }
    }
};
</script>
