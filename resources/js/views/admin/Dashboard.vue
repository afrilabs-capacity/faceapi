<template>
  <div>

    <div class="flex flex-wrap">
      <div class="w-full xl:w-8/12 mb-12 xl:mb-0 px-4">
        <card-create-website />
      </div>
      <div class="w-full xl:w-4/12 px-4">
      </div>
    </div>
  </div>
</template>
<script>
import CardLineChart from "../../components/Cards/CardLineChart.vue";
import CardBarChart from "../../components/Cards/CardBarChart.vue";
import CardPageVisits from "../../components/Cards/CardPageVisits.vue";
import CardSocialTraffic from "../../components/Cards/CardSocialTraffic.vue";
import CardCreateWebsite from "../../components/Cards/CardCreateWebsite";
export default {
  name: "dashboard-page",
  components: {
      CardCreateWebsite,
    CardLineChart,
    CardBarChart,
    CardPageVisits,
    CardSocialTraffic,
  },
};
</script>
