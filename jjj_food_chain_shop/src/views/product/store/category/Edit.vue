<template>
    <el-dialog :title="$t('编辑普通分类')" v-model="dialogVisible" @close="dialogFormVisible" :close-on-click-modal="false"
        :close-on-press-escape="false">
        <el-form size="small" :model="form" label-position="top" :rules="formRules" ref="form">
            <el-form-item :label="$t('分类级别')" prop="parent">
                <el-radio-group v-model="parent" @change="radioChange">
                    <el-radio :label="1">{{ $t('一级分类') }}</el-radio>
                    <el-radio :label="0">{{ $t('二级分类') }}</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item v-if="parent == 0" :label="$t('上级分类')" prop="parent_id"
                :rules="[{ required: true, message: $t('请选择上级分类') }]">
                <el-select v-model="form.parent_id" :placeholder="$t('请选择上级分类')">
                    <template v-for="cat in category" :key="cat.category_id">
                        <el-option :value="cat.category_id" :label="cat.name_text"></el-option>
                    </template>
                </el-select>
            </el-form-item>
            <template v-for="(item, index) in languageList" :key="index">
                <el-form-item :label="$t('分类名称') + `(${item.value})`" :prop="`name.${item.key}`"
                    :rules="[{ required: true, message: $t('请输入分类名称') }]">
                    <el-input v-model="form.name[item.key]" :placeholder="$t('请输入分类名称')" :maxlength="50" autocomplete="off"></el-input>
                </el-form-item>
            </template>
            <!-- <el-form-item :label="$t('分类图片')" prop="image_id">
                <el-row>
                    <el-button type="primary" @click="openUpload">{{ $t('选择图片') }}</el-button>
                    <div v-if="form.image_id != ''" class="img">
                        <img :src="file_path" width="100" height="100" />
                    </div>
                </el-row>
            </el-form-item> -->

            <el-form-item :label="$t('分类排序')" prop="sort">
                <el-input-number :controls="false" :placeholder="$t('接近0，排序等级越高')" :min="0" :max="999" v-model.number="form.sort" autocomplete="off"></el-input-number>
            </el-form-item>
        </el-form>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogFormVisible">{{ $t('取消') }}</el-button>
                <el-button type="primary" @click="addUser" :loading="loading">{{ $t('确定') }}</el-button>
            </div>
        </template>
        <!--上传图片组件-->
        <Upload v-if="isupload" :isupload="isupload" :type="type" @returnImgs="returnImgsFunc">上传图片</Upload>
    </el-dialog>
</template>

<script>
import PorductApi from '@/api/product.js';
import Upload from '@/components/file/Upload.vue';
import { languageStore } from '@/store/model/language.js';
const languageData = JSON.stringify(languageStore().languageData);
const languageList = languageStore().languageList;
export default {
    components: {
        Upload
    },
    data() {
        return {
            languageList: languageList,
            category: [],
            parent: 1,
            form: {
                parent_id: 0,
                category_id: 0,
                name: JSON.parse(languageData),
                image_id: '',
                sort: ''
            },
            file_path: '',
            formRules: {

                image_id: [{
                    required: true,
                    message: $t('请上传分类图片'),
                    trigger: 'blur'
                }],
                sort: [{
                    required: true,
                    message: $t('分类排序不能为空')
                }, {
                    type: 'number',
                    message: $t('分类排序必须为数字')
                }]
            },
            /*左边长度*/
            formLabelWidth: '120px',
            /*是否显示*/
            dialogVisible: false,
            loading: false,
            /*是否上传图片*/
            isupload: false,
        };
    },
    props: ['open_edit', 'editform'],
    created() {
        /*获取父级分类*/
        this.getParentCategory();
        this.dialogVisible = this.open_edit;
        this.form.category_id = this.editform.model.category_id;
        this.form.parent_id = this.editform.model.parent_id;
        this.form.name = JSON.parse(this.editform.model.name);
        this.form.sort = this.editform.model.sort;
        this.form.image_id = this.editform.model.image_id;
        if (this.editform.model.parent_id != 0) {
            this.parent = 0;
        }
    },
    methods: {
        /*获取父级分类*/
        getParentCategory: function () {
            let self = this;
            PorductApi.storeCatParentList({}, true)
                .then(res => {
                    self.loading = false;
                    // console.log(res.data);
                    // Object.assign(self.category, res.data.list);
                    // console.log(self.category)
                    this.category = res.data.list;
                })
                .catch(error => {
                    self.loading = false;
                });
        },

        radioChange(e) {
            this.form.parent_id = '';
        },
        /*修改用户*/
        addUser() {
            let self = this;
            let params = JSON.parse(JSON.stringify(self.form));
            params.name = JSON.stringify(params.name)
            self.$refs.form.validate((valid) => {
                if (valid) {
                    self.loading = true;
                    PorductApi.storeCatEdit(params, true).then(data => {
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
        /*关闭弹窗*/
        dialogFormVisible(e) {
            if (e) {
                this.$emit('closeDialog', {
                    type: 'success',
                    openDialog: false
                })
            } else {
                this.$emit('closeDialog', {
                    type: 'error',
                    openDialog: false
                })
            }
        },
        /*上传*/
        openUpload(e) {
            this.type = e;
            this.isupload = true;
        },
        /*获取图片*/
        returnImgsFunc(e) {
            if (e != null && e.length > 0) {
                this.file_path = e[0].file_path;
                this.form.image_id = e[0].file_id;
            }
            this.isupload = false;
        },

    }
};
</script>

<style scoped>
.img {
    margin-top: 10px;
}
</style>
