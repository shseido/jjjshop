<template>
  <!--
          作者：luoyiming
          时间：2019-10-24
          描述：统计-销售统计-商品统计
      -->
  <div class="sales-product-box mt30">
    <div class="common-form">商品统计</div>
    <div class="d-s-stretch bd-box">
      <div class="d-s-c d-c left-box">
        <!--汇总-->
        <Total></Total>

        <!--已付款商品-->
        <LineChart></LineChart>
      </div>
      <!--商品排行榜-->
      <Ranking></Ranking>
    </div>
  </div>
</template>

<script>
  import Total from './product/Total.vue';
  import LineChart from './product/LineChart.vue';
  import Ranking from './product/Ranking.vue'
  export default{
    components: {
      Total,
      LineChart,
      Ranking
    }
  }
</script>

<style scoped>
  .sales-product-box .bd-box{ border-top: 1px solid #EEEEEE;}
  .sales-product-box .left-box{ width: 69%; border-right: 1px solid #EEEEEE;}
</style>
