<template >
    <div class="supplier">
        <el-form size="small" ref="form" :model="form" label-position="top" :rules="formRules">
            <el-form-item :label="$t('服务费')" prop="is_open">
                <div>
                    <el-radio v-model="form.is_open" :label="'1'">{{ $t('开启') }}</el-radio>
                    <el-radio v-model="form.is_open" :label="'0'">{{ $t('关闭') }}</el-radio>
                </div>
            </el-form-item>
            <el-form-item v-if="form.is_open == '1'" :label="$t('金额')" prop="service_charge">
                <el-input-number class="max-w460" :controls="false" :precision="2" :min="0"  :placeholder="$t('请输入')" v-model.number="form.service_charge"></el-input-number>
                    <span>{{ currency.unit }}</span>
                    <div class="tips">{{ $t('收银/桌台订单所需要增加的服务费') }}</div>
            </el-form-item>
        </el-form>
        <!--提交-->
        <div class="common-button-wrapper">
            <el-button @click="getData" :loading="loading">{{ $t('重置') }}</el-button>
            <el-button type="primary" @click="onSubmit" :loading="loading">{{ $t('保存') }}</el-button>
        </div>

    </div>
</template>
<script>
import SettingApi from '@/api/setting.js';
import { useUserStore } from '@/store';
const {  currency } = useUserStore();
export default {
    data() {
        return {
            currency:currency,
            loading: false,
            form: {
                is_open: '1',
                service_charge: null,
            },
            formRules: {

                is_open: [{
                    required: true,
                    message: $t('请输入主货币单位'),
                    trigger: 'blur'
                }],

                service_charge: [{
                    required: true,
                    message: $t('请输入金额'),
                    trigger: 'blur'
                }],
            },
        }
    },
    created() {
        this.getData();
    },
    methods: {
        /*获取列表*/
        getData() {
            let self = this;
            SettingApi.getServiceCharge({}, true)
                .then(data => {
                    self.loading = false;
                    self.form = data.data.vars.values;
                    self.form.service_charge = Number(self.form.service_charge)
                    self.form.is_open = data.data.vars.values.is_open.toString()
                    self.$refs.form.validate()
                })
                .catch(error => {

                });
        },
        onSubmit() {
            let self = this;
            let params = JSON.parse(JSON.stringify(self.form));
            self.$refs.form.validate((valid) => {
                if (valid) {
                    self.loading = true;
                    SettingApi.setServiceCharge(params, true).then(data => {
                        self.loading = false;
                        this.$ElMessage({
                            message: $t('保存成功'),
                            type: 'success'
                        });
                        self.dialogFormVisible(true);
                    }).catch(error => {
                        self.loading = false;
                    });
                }
            });


        },
    },
}
</script>
<style lang="">
    
</style>