<template>
  <!--
      作者 luoyiming
      时间：2020-08-30
      描述：设置-满额免运费
  -->
  <div class="product-add">
    <!--form表单-->
    <el-form size="small" ref="form" :model="form" label-position="top" label-width="200px">
      <div class="common-form">满额免配送费设置</div>

      <el-form-item label="是否开启满额免配送费">
        <div>
          <el-radio v-model="form.is_open" label="1">开启</el-radio>
          <el-radio v-model="form.is_open" label="0">关闭</el-radio>
        </div>
      </el-form-item>
      <el-form-item label="单笔订单满" prop="money" :rules="[{required: true,message: ' '}]">
        <el-input v-model="form.money" type="number" style="width: 200px;"></el-input>元
        <div class="tips">
          如果开启满额免配送费，设置0为全场免配送费
        </div>
      </el-form-item>
      <!--提交-->
      <div class="common-button-wrapper">
        <el-button type="primary" @click="onSubmit" :loading="loading">提交</el-button>
      </div>
    </el-form>
  </div>

</template>

<script>
  import SettingApi from '@/api/setting.js';

  export default {
    data() {
      return {
        form: {
          is_open: '',
          money: '',
        },
        loading: false,
      };
    },
    created() {
      this.getData()
    },

    methods: {
      getData() {
        let self = this;
        SettingApi.fullfreeDetail({}, true)
          .then(data => {
            let vars = data.data.vars.values;
            self.form.is_open = vars.is_open;
            self.form.money = vars.money;
          })
          .catch(error => {});
      },
      //提交表单
      onSubmit() {
        let self = this;
        let params = this.form;
        self.$refs.form.validate((valid) => {
          if (valid) {
            self.loading = true;
            SettingApi.editFullfree(params, true)
              .then(data => {
                self.loading = false;
                this.$ElMessage({
                  message: '恭喜你，设置成功',
                  type: 'success'
                });
              })
              .catch(error => {
                self.loading = false;
              });
          }
        });
      },
    }

  };
</script>

<style scoped>
  .tips {
    color: #ccc;
  }
</style>
