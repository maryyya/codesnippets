<template>
<div class="l_main">
  <div class="l_wrap">
    <div class="l_contents"> <!-- InstanceBeginEditable name="contents" -->
      <h1 class="c_h1">アラート管理　一覧</h1>
      <div class="l_block">
        <div class="l_block u_ALcenter">
          <el-row>
            <div v-for="alert in alertlist" :key="alert.ID">
              <el-col :span="12">
                <a :href="'/alert/detail?id='+ alert.ID">
                <el-button>{{alert.ALERTNAME }}</el-button></a>
              </el-col>
            </div>
          </el-row>
        </div>

      </div>
      <!-- block end -->
    </div>
    <!-- contents end -->
  </div>
  <!-- wrap end -->
</div>
<!-- main end -->
</template>

<script>
export default {
  data () {
    return {
      // アラート一覧
      alertlist: [],
    }
  },

  created() {
    console.log('[created] alertindex');
    var me = this;

    // アラート一覧を取得
    axios.get('/alert/getAlertData').then(function(res) {
      me.alertlist = res.data.data;
    });
  }
}
</script>

<style scoped>
.el-button {
  margin-bottom: 5px;
  width: 500px;
}
</style>