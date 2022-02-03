<template>
  <div v-if="!isLoading" class="flex flex-wrap mt-4">
    <div class="w-full mb-12 px-4">
      <card-table :list="websites" :headers="headers" />
    </div>
    <div class="w-full mb-12 px-4">
    </div>
  </div>
</template>
<script>
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
          headers: ["website name", "unique ID", "users", "action"]
      }
    },
    mounted(){
      this.loadWebsites()
    },
    methods:{
      loadWebsites(){
          this.isLoading = true
          axios.get("/api/load-websites")
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
