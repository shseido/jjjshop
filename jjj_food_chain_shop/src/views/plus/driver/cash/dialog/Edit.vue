<template>
  <!--
      	作者：luoyiming
      	时间：2020-06-01
      	描述：插件中心-配送员-提现申请-弹窗
      -->
  <div v-if="status != 30">
    <el-dialog title="提现审核" v-model="dialogVisible" @close="dialogFormVisible" :close-on-click-modal="false"
      :close-on-press-escape="false">
      <el-form :model="form" label-position="top">
        <el-form-item label="审核状态" :label-width="formLabelWidth">
          <div>
            <el-radio v-model="form.apply_status" label="20">审核通过</el-radio>
            <el-radio v-model="form.apply_status" label="30">驳回</el-radio>
          </div>
        </el-form-item>
        <div v-if="form.apply_status == 30">
          <el-form-item label="驳回原因" :label-width="formLabelWidth">
            <el-input v-model="form.reject_reason" autocomplete="off"></el-input>
          </el-form-item>
        </div>
      </el-form>
      <template #footer>
      <div class="dialog-footer">
        <el-button @click="dialogFormVisible">取 消</el-button>
        <el-button type="primary" @click="editApplyStatus">确 定</el-button>
      </div>
      </template>
    </el-dialog>
  </div>

  <div v-else>
    <el-dialog title="驳回原因" v-model="dialogVisible" @close="dialogFormVisible" :close-on-click-modal="false"
      :close-on-press-escape="false">
      <p>{{ reject_reason }}</p>
    </el-dialog>
  </div>
</template>

<script>
  import DriverApi from '@/api/driver.js';
  export default {
    data() {
      return {
        status: '',
        reject_reason: '',
        /*左边长度*/
        formLabelWidth: '120px',
        /*是否显示*/
        dialogVisible: false
      };
    },
    props: ['open_edit', 'form'],
    created() {
      this.dialogVisible = this.open_edit;
      this.status = this.form.apply_status.value;
      if (this.status == 30) {
        this.reject_reason = this.form.reject_reason;
      }
    },
    methods: {

      /*修改*/
      editApplyStatus() {
        let self = this;
        let params = this.form;
        DriverApi.cashSubmit(params, true)
          .then(data => {
            this.$ElMessage({
              message: $t('保存成功'),
              type: 'success'
            });
            self.dialogFormVisible(true);
          })
          .catch(error => {});
      },

      /*关闭弹窗*/
      dialogFormVisible(e) {
        if (e) {
          this.$emit('closeDialog', {
            type: 'success',
            openDialog: false
          });
        } else {
          this.$emit('closeDialog', {
            type: 'error',
            openDialog: false
          });
        }
      }
    }
  };
</script>

