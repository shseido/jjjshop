<template>
    <!--
        作者：luoyiming
        时间：2019-10-25
        描述：权限-角色管理-添加角色
    -->
    <div v-loading="loading" class="add-box-role">
        <!--form表单-->
        <el-form size="small" ref="form" :model="form" label-position="top" label-width="180px">
            <!--添加门店-->
            <div class="common-form">{{ $t('添加角色') }}</div>

            <el-form-item :label="$t('角色名称：')" prop="role_name" :rules="[{ required: true, message: ' ' }]">
                <el-input v-model="form.role_name" :placeholder="$t('请输入角色名称')" :maxlength="50" class="max-w460"></el-input>
            </el-form-item>

            <el-form-item class="role-list" :label="$t('权限列表：')" v-model="form.access_id">
                <el-tree :data="data" show-checkbox node-key="access_id" :default-expand-all="true"
                    :default-checked-keys="[]" :props="defaultProps" @check="handleCheckChange"></el-tree>
            </el-form-item>

            <!-- <el-form-item :label="$t('排序：')"><el-input type="number" v-model="form.sort" placeholder="$t('接近0，排序等级越高')"
                    class="max-w460"></el-input></el-form-item> -->

            <!--提交-->
            <div class="common-button-wrapper">
                <el-button size="small"  @click="cancelFunc">{{$t('取消')}}</el-button>
                <el-button type="primary" size="small" @click="onSubmit" :loading="loading">{{$t('确定')}}</el-button>
            </div>
        </el-form>
    </div>
</template>

<script>
import AuthApi from '@/api/auth.js';

export default {
    data() {
        return {
            /*是否正在加载*/
            loading: true,
            /*表单数据对象*/
            form: {
                access_id: [],
                sort: 1
            },
            data: [],
            roleList: [],
            defaultProps: {
                children: 'children',
                label: 'name'
            }
        };
    },
    created() {
        /*获取列表*/
        this.getData();
    },
    methods: {
        /*添加角色*/
        onSubmit() {
            let self = this;
            let form = self.form;
            self.$refs.form.validate(valid => {
                if (valid) {
                    self.loading = true;
                    AuthApi.roleAdd({
                        params: JSON.stringify(form)
                    }, true)
                        .then(data => {
                            self.loading = false;
                            this.$ElMessage({
                                message: '添加成功',
                                type: 'success'
                            });
                            self.$router.push('/auth/role/index');
                        })
                        .catch(error => {
                            self.loading = false;
                        });
                } else {
                    const divElement = document.querySelector('.main-div');
                    divElement.scrollTop = 0;
                }
            });
        },

        /*获取数据*/
        getData() {
            let self = this;
            AuthApi.roleAddInfo()
                .then(data => {
                    self.data = data.data.menu;
                    data.data.menu.map((item,index)=>{
                        self.data[index].name = $t(item.name)
                        item.children.map((items,indexs)=>{
                            self.data[index].children[indexs].name  =  $t(items.name);
                            items.children.map((itemThree,indexThree)=>{
                                self.data[index].children[indexs].children[indexThree].name = $t(itemThree.name);
                                itemThree.children.map((itemFour,indexFour)=>{
                                    self.data[index].children[indexs].children[indexThree].children[indexFour].name = $t(itemFour.name)
                                })
                            })
                        })
                    })
                    self.roleList = data.data.roleList;
                    self.loading = false;
                })
                .catch(error => {
                    self.loading = false;
                });
        },

        //监听选中
        handleCheckChange(data, checked) {
            this.form.access_id = checked.checkedKeys.concat(checked.halfCheckedKeys);
        },

        /*取消*/
        cancelFunc() {
            this.$router.back(-1);
        }
    }
};
</script>

<style lang="scss" scoped>
.img {
    margin-top: 10px;
}
.add-box-role{
    height: calc(100% - 14px) ;
    overflow: hidden;
    .el-form{
        display: flex;
        flex-direction: column;
        height: 100%;
        .role-list{
            flex: 1 1 auto;
            overflow-y: auto;
        }
    }
}
</style>
