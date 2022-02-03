<template>
  <!-- Header -->
  <div class="relative bg-emerald-600 md:pt-32 pb-32 pt-12">
    <div class="px-4 md:px-10 mx-auto w-full">
      <div>
        <!-- Card stats -->
        <div class="flex flex-wrap">
          <div class="w-full lg:w-6/12 xl:w-3/12 px-4">
            <card-stats
                @click="moveToTables()"
              statSubtitle="WEBSITES"
              :statTitle="websites"
              statArrow="up"
              statPercent="3.48"
              statPercentColor="text-emerald-500"
              statDescripiron="Since last month"
              statIconName="fas fa-globe"
              statIconColor="bg-red-500"
            />
          </div>
          <div class="w-full lg:w-6/12 xl:w-3/12 px-4">
            <card-stats
              statSubtitle="WEBSITE USERS"
              :statTitle="website_user"
              statArrow="down"
              statPercent="3.48"
              statPercentColor="text-red-500"
              statDescripiron="Since last week"
              statIconName="fas fa-users"
              statIconColor="bg-orange-500"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import CardStats from "../Cards/CardStats.vue";

export default {
  components: {
    CardStats,
  },
    mounted() {

        this.emitter.on("reload_navbar", (e) => {
            if (e){
                this.getStats()
            }
        })
      this.getStats()
    },
    data(){
      return {
          isLoading: false,
          website_user: 0,
          websites: 0
      }
    },
    methods:{
        moveToTables(){
          this.$router.push("/admin/websites")
        },
      getStats(){
          this.isLoading = true
          axios.get("/api/get-stats")
                .then(res => {
                    this.websites = res.data.websites
                    this.website_user = res.data.users
                })
                .catch(err => {
                    console.log(err)
                })
                .finally(() => {
                    this.isLoading = false
                })
      }
    }
};
</script>
