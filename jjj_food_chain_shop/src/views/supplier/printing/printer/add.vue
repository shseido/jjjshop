<template>
    <el-dialog class="product-add" @close="handleClose" v-model="dialogVisible" :close-on-click-modal="false"
        :close-on-press-escape="false" :title="$t('添加打印机')">
        <!--form表单-->
        <el-form size="small" ref="form" :model="form" label-position="top">
            <!--添加门店-->
            <el-form-item :label="$t('打印机名称')" prop="printer_name" :rules="[{ required: true, message: ' ' }]">
                <el-input v-model="form.printer_name" :maxlength="50" :placeholder="$t('请输入打印机名称')"></el-input>
            </el-form-item>
            <el-form-item :label="$t('打印机类型')" prop="printer_type" :rules="[{ required: true, message: ' ' }]">
                <el-select v-model="form.printer_type" :placeholder="$t('请选择类型')" style="width: 100%;">
                    <el-option v-for="(item, index) in type" :key="index" :label="item" :value="index">
                    </el-option>
                </el-select>
            </el-form-item>
            

            <!-- 飞鹅打印机 -->
            <div v-if="form.printer_type == 'FEI_E_YUN'">
                <el-form-item label="USER" prop="FEI_E_YUN.USER" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.FEI_E_YUN.USER"></el-input>
                    <div class="tips">{{ $t('飞鹅云后台注册用户名') }}</div>
                </el-form-item>

                <el-form-item label="UKEY" prop="FEI_E_YUN.UKEY" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.FEI_E_YUN.UKEY"></el-input>
                    <div class="tips">{{ $t('飞鹅云后台登录生成的UKEY') }}</div>
                </el-form-item>

                <el-form-item :label="$t('打印机编号')" prop="FEI_E_YUN.SN" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.FEI_E_YUN.SN"></el-input>
                    <div class="tips">{{ $t('打印机编号为9位数字，查看飞鹅打印机底部贴纸上面的编号') }}</div>
                </el-form-item>
            </div>

            <!-- 飞鹅打印机 -->
            <div v-if="form.printer_type == 'FEI_E_YUN_TAG'">
                <el-form-item label="USER" prop="FEI_E_YUN_TAG.USER" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.FEI_E_YUN_TAG.USER"></el-input>
                    <div class="tips">{{ $t('飞鹅云后台注册用户名') }}</div>
                </el-form-item>

                <el-form-item label="UKEY" prop="FEI_E_YUN_TAG.UKEY" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.FEI_E_YUN_TAG.UKEY"></el-input>
                    <div class="tips">{{ $t('飞鹅云后台登录生成的UKEY') }}</div>
                </el-form-item>

                <el-form-item :label="$t('打印机编号')" prop="FEI_E_YUN_TAG.SN" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.FEI_E_YUN_TAG.SN"></el-input>
                    <div class="tips">{{ $t('打印机编号为9位数字，查看飞鹅打印机底部贴纸上面的编号') }}</div>
                </el-form-item>
            </div>

            <!-- 365云打印 -->
            <div v-if="form.printer_type == 'PRINT_CENTER'">
                <el-form-item :label="$t('打印机编号')" prop="PRINT_CENTER.deviceNo" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.PRINT_CENTER.deviceNo"></el-input>
                </el-form-item>

                <el-form-item :label="$t('打印机秘钥')" prop="PRINT_CENTER.key" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.PRINT_CENTER.key"></el-input>
                </el-form-item>
            </div>

            <!-- 商米打印 -->
            <div v-if="form.printer_type == 'SUNMI_LAN'">
                <el-form-item :label="$t('打印机IP')" prop="SUNMI_LAN.IP" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.SUNMI_LAN.IP"></el-input>
                </el-form-item>

                <el-form-item :label="$t('打印机SN')" prop="SUNMI_LAN.SN" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.SUNMI_LAN.SN"></el-input>
                </el-form-item>
            </div>

            <!-- 芯烨打印 -->
            <div v-if="form.printer_type == 'XPRINTER_LAN'">
                <el-form-item :label="$t('打印机IP')" prop="XPRINTER_LAN.IP" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.XPRINTER_LAN.IP"></el-input>
                </el-form-item>

                <el-form-item :label="$t('打印机PORT')" prop="XPRINTER_LAN.PORT" :rules="[{ required: true, message: ' ' }]">
                    <el-input v-model="form.XPRINTER_LAN.PORT"></el-input>
                </el-form-item>
            </div>

            <el-form-item :label="$t('打印联数')" prop="print_times" :rules="[{ required: true, message: $t('请输入打印联数') }]">
                <el-input-number :controls="false" :min="0" :max="10" :placeholder="$t('请输入打印联数')" v-model.number="form.print_times" autocomplete="off"></el-input-number>
                <div class="tips">{{ $t('同一订单，打印的次数') }}</div>
            </el-form-item>

            <el-form-item :label="$t('排序')" prop="sort" :rules="[{ required: true, message: $t('接近0，排序等级越高') }]">
                <el-input-number :controls="false" :min="0" :max="999" :placeholder="$t('接近0，排序等级越高')" v-model.number="form.sort" autocomplete="off"></el-input-number>
               
            </el-form-item>

            <!--提交-->




        </el-form>
        <template #footer>
            <span class="dialog-footer">
                <el-button @click="handleClose">{{ $t('取消') }}</el-button>
                <el-button type="primary" @click="onSubmit" :loading="loading">{{ $t('确定') }}</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script>
import SettingApi from '@/api/setting.js';

export default {
    props: ['open_add'],
    created() {
        this.dialogVisible = this.open_add
        this.getData();
    },
    data() {
        return {
            /*切换菜单*/
            // activeIndex: '1',
            /*form表单数据*/
            form: {
                printer_name: '',
                printer_type: '',
                sort: null,
                print_times: 1,
                FEI_E_YUN: {
                    USER: '',
                    UKEY: '',
                    SN: '',
                },
                FEI_E_YUN_TAG: {
                    USER: '',
                    UKEY: '',
                    SN: '',
                },
                PRINT_CENTER: {
                    deviceNo: '',
                    key: ''
                },
                SUNMI_LAN:{
                    IP:'',
                    SN:'',
                },
                XPRINTER_LAN:{
                    IP:'',
                    PORT:9100,
                },
            },
            loading: false,

            type: [],
            dialogVisible: false,
        };
    },


    methods: {
        getData() {
            SettingApi.printerType({}, true)
                .then(data => {
                    this.type = data.data.printerType;

                })
                .catch(error => {

                });
        },
        //提交表单
        onSubmit() {
            let self = this;
            let form = self.form;
            self.$refs.form.validate((valid) => {
                if (valid) {
                    self.loading = true;
                    SettingApi.addPrinter(form, true)
                        .then(data => {
                            self.loading = false;
                            this.$ElMessage({
                                message: $t('添加成功'),
                                type: 'success'
                            });
                            this.$emit('close', 1)

                        }).catch(error => {
                            self.loading = false;
                        });
                }
            });
        },

        handleClose() {
            this.$emit('close')
        },
    }

};
</script>

<style scoped>
.tips {
    color: #ccc;
}
</style>
