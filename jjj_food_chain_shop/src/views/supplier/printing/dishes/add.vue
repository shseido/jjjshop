<template>
    <el-dialog class="product-add" @close="handleClose" v-model="dialogVisible" :close-on-click-modal="false" :close-on-press-escape="false" :title="$t('添加商品打印')">
        <!--form表单-->
        <el-form size="small" ref="form" :model="form" label-position="top">
            <!--添加门店-->
            <el-form-item :label="$t('名称')" prop="name" :rules="[{ required: true, message: $t('请输入名称') }]"><el-input v-model="form.name" :placeholder="$t('请输入名称')"
                    :maxlength="50"></el-input></el-form-item>
            <el-form-item :label="$t('是否开启')" prop="is_open" :rules="[{ required: true, message: '' }]">
                <div>
                    <el-radio v-model="form.is_open" :label="1">{{ $t('开启') }}</el-radio>
                    <el-radio v-model="form.is_open" :label="0">{{ $t('关闭') }}</el-radio>
                </div>
            </el-form-item>

            <el-form-item :label="$t('打印模式')" prop="print_type" :rules="[{ required: true, message: '' }]">
                <div>
                    <el-radio v-model="form.print_type" :label="10">{{ $t('付款打印') }}</el-radio>
                    <el-radio v-model="form.print_type" :label="30">{{ $t('送厨打印') }}</el-radio>

                </div>
            </el-form-item>

            <el-form-item :label="$t('按区域打印')">
                <el-select v-model="form.area_id" multiple :placeholder="$t('全部区域')">
                    <el-option v-for="(item, index) in areaData" :key="index" :label="item.area_name" :value="item.area_id"></el-option>
                </el-select>
            </el-form-item>

            <el-form-item :label="$t('打印方式')" prop="print_method" :rules="[{ required: true, message: '' }]">
                <div>
                    <el-radio-group v-model="form.print_method" @change="() => { form.is_open_one_food = 0 }">
                        <el-radio :label="10">{{ $t('整单打印') }}</el-radio>
                        <el-radio :label="20">{{ $t('按商品分类打印') }}</el-radio>
                        <el-radio :label="30">{{ $t('按标签打印') }}</el-radio>
                        <el-radio :label="40">{{ $t('按一菜一单打印') }}</el-radio>
                    </el-radio-group>
                </div>
            </el-form-item>

            <el-form-item v-if="form.type == 10" :label="$t('打印机')" prop="printer_id" :rules="[{ required: true, message: $t('请选择打印机') }]">
                <el-select v-model="form.printer_id" :placeholder="$t('请选择')">
                    <el-option v-for="(item, index) in type" :key="index" :label="item.printer_name" :value="item.printer_id"></el-option>
                </el-select>
            </el-form-item>

            <el-form-item v-if="form.type == 20" :label="$t('打印机')" prop="printer_id" :rules="[{ required: true, message: $t('请选择打印机') }]">
                <el-select v-model="form.printer_id" :placeholder="$t('请选择')">
                    <el-option v-for="(item, index) in typeTag" :key="index" :label="item.printer_name" :value="item.printer_id"></el-option>
                </el-select>
            </el-form-item>

            <el-form-item v-if="form.product_type == 0 && form.print_method == 20" :label="$t('商品分类')" prop="category_id" :rules="[{ required: true, message: '请选择商品分类' }]">
                <el-select v-model="form.category_id" multiple :placeholder="$t('请选择')">
                    <el-option v-for="item in storeList" :key="item.category_id" :label="item.name_text" :value="item.category_id + ''"></el-option>
                </el-select>
            </el-form-item>

            <el-form-item v-if="form.product_type == 1 && form.print_method == 20" :label="$t('商品分类')" prop="category_id" :rules="[{ required: true, message: $t('请选择商品分类') }]">
                <el-select v-model="form.category_id" multiple :placeholder="$t('请选择')">
                    <el-option v-for="item in storeList" :key="item.category_id" :label="item.name_text" :value="item.category_id + ''"></el-option>
                </el-select>
            </el-form-item>

            <el-form-item v-if="form.print_method == 30" :label="$t('打印标签')" prop="label_id">
                <el-select v-model="form.label_id" multiple :placeholder="$t('请选择')">
                    <el-option v-for="item in labelList" :key="item.label_id" :label="item.label_name_text" :value="item.label_id + ''"></el-option>
                </el-select>
                <div class="tips">{{ $t('不选择打印全部') }}</div>
            </el-form-item>

            <el-form-item v-if="form.print_method == 20 || form.print_method == 30" :label="$t('按一菜一单打印')" prop="is_open_one_food" :rules="[{ required: true, message: '' }]">
                <div>
                    <el-radio v-model="form.is_open_one_food" :label="0">{{ $t('关闭') }}</el-radio>
                    <el-radio v-model="form.is_open_one_food" :label="1">{{ $t('开启') }}</el-radio>
                </div>
            </el-form-item>
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
import SupplierApi from '@/api/supplier.js';
import StoreApi from '@/api/store.js';
export default {
    data() {
        return {
            /*切换菜单*/
            // activeIndex: '1',
            /*form表单数据*/
            form: {
                name: '',
                is_open: 1,
                printer_id: '',
                product_type: 0,
                print_type: 10,
                category_id: [],
                area_id: '',
                type: 10,
                print_method: 10,
                label_id: [],
                is_open_one_food: 0,
            },
            loading: false,
            dialogVisible: false,
            type: [],
            typeTag: [],
            storeList: [],
            takeList: [],
            labelList: [],
            // 
            options: [],
            categoryIds: [],
            areaData: []
        };
    },
    props: ['open_add'],
    created() {
        this.dialogVisible = this.open_add
        this.getAreaData();

    },
    // watch: {
    //     'categoryIds': {
    //         handler(val) {
    //             this.form.category_id = [];
    //             this.categoryIds.map(h=>{
    //                 if (h[1]) {
    //                     this.form.category_id.push(h[1])
    //                 }
    //             })
    //             // 
    //             this.$refs?.form?.validate(_=>{})
    //         },
    //         deep: true,
    //         immediate: true,
    //     }
    // },
    methods: {
        getData() {
            SupplierApi.getPrinting({}, true)
                .then(data => {
                    this.storeList = data.data.storeList;
                    this.takeList = data.data.takeList;
                    this.type = data.data.printerList;
                    this.typeTag = data.data.printerTagList;
                    this.labelList = data.data.labelList;
                    // 
                    this.options = [];
                    this.storeList?.map((item) => {
                        if (item.parent_id == 0) {
                            let children = [];
                            this.storeList?.map((val) => {
                                if (item.category_id == val.parent_id) {
                                    children.push({
                                        value: val.category_id,
                                        label: val.name_text,
                                        children: [],
                                    })
                                }
                            })
                            this.options.push({
                                value: item.category_id,
                                label: item.name_text,
                                children: children,
                            })
                        }
                    })
                })
                .catch(error => { });
        },

        /*获取列表*/
        getAreaData() {
            let self = this;
            self.loading = true;
            StoreApi.arealist({}, true)
                .then(data => {
                    self.loading = false;
                    self.areaData = data.data.list.data.map(item=>{
                        return{
                            area_id: item.area_id.toString(),
                            area_name: item.area_name
                        }
                    });
                    self.areaData.unshift({
                        area_id: '0',
                        area_name: this.$t('无区域 (收银无桌台)')
                    })
                    this.getData();
                })
                .catch(error => {
                    self.loading = false;
                });
        },

        //提交表单
        onSubmit() {
            let self = this;
            let form = JSON.parse(JSON.stringify(self.form));
            // 
            if (!form.print_method == 20) {
                form.category_id = [];
            }
            form.area_id = (form.area_id || []).filter(id=>id)
            form.area_id = (form.area_id || []).length > 0 ? form.area_id : '';
            self.$refs.form.validate(valid => {
                if (valid) {
                    self.loading = true;
                    SupplierApi.addPrinting(form, true).then(data => {
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
