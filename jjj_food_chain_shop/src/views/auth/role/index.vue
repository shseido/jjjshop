<template>
    <!--
        作者：luoyiming
        时间：2019-10-25
        描述：权限-角色管理
    -->
    <div class="user">
        <div class="common-level-rail">
            <el-button size="small" type="primary" icon="Plus" @click="addClick" v-auth="'/auth/role/add'">{{ $t('添加角色')}}</el-button>
        </div>

        <!--内容-->
        <div class="product-content">
            <div class="table-wrap">
                <el-table size="small" :data="tableData" border style="width: 100%" v-loading="loading">
                    <el-table-column prop="role_id" label="ID"></el-table-column>
                    <el-table-column prop="role_name_h1" :label="$t('角色名称')"></el-table-column>
                    <!-- <el-table-column prop="sort" :label="$t('排序')"></el-table-column> -->
                    <el-table-column prop="create_time" :label="$t('添加时间')"></el-table-column>
                    <el-table-column fixed="right" :label="$t('操作')" width="120">
                        <template #default="scope">
                            <el-button @click="editClick(scope.row)" type="primary" link size="small" v-auth="'/auth/role/edit'">{{ $t('编辑') }}</el-button>
                            <el-button @click="deleteClick(scope.row)" type="primary" link size="small" v-auth="'/auth/role/delete'">{{ $t('删除') }}</el-button>
                        </template>
                    </el-table-column>
                </el-table>
            </div>

            <!--分页-->
            <!-- <div class="pagination">
            <el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" background :current-page="curPage"
            :page-size="pageSize" layout="total, prev, pager, next, jumper"
            :total="totalDataNumber">
            </el-pagination>
        </div> -->
        </div>
    </div>
</template>

<script>
import AuthApi from '@/api/auth.js';
export default {
    components: {},
    inject: ['reload'],
    data() {
        return {
            /*是否加载完成*/
            loading: true,
            /*列表数据*/
            tableData: [],
            /*一页多少条*/
            // pageSize: 20,
            /*一共多少条数据*/
            // totalDataNumber: 0,
            /*当前是第几页*/
            // curPage: 1,
            /*横向表单数据模型*/
            formInline: {
                user: '',
                region: ''
            },
            /*是否打开添加弹窗*/
            open_add: false,
            /*是否打开编辑弹窗*/
            open_edit: false,
            /*当前编辑的对象*/
            userModel: {}
        };
    },
    created() {
        /*获取列表*/
        this.getTableList();
    },
    methods: {
        /*选择第几页*/
        // handleCurrentChange(val) {
        //   let self = this;
        //   self.curPage = val;
        //   self.loading = true;
        //   self.getTableList();
        // },

        /*每页多少条*/
        // handleSizeChange(val) {
        //   this.curPage = 1;
        //   this.pageSize = val;
        //   this.getTableList();
        // },

        /*获取列表*/
        getTableList() {
            let self = this;
            let Params = {};
            // Params.page = self.curPage;
            // Params.list_rows = self.pageSize;
            AuthApi.roleList(Params, true)
                .then(data => {
                    self.loading = false;
                    self.tableData = data.data.list;
                    // self.totalDataNumber = data.data.list.length;
                })
                .catch(error => {
                    self.loading = false;
                });
        },

        /*打开添加*/
        addClick() {
            this.$router.push('/auth/role/add');
        },

        /*打开编辑*/
        editClick(row) {
            let self = this;
            this.$router.push({
                path: '/auth/role/edit',
                // name: 'mallList',
                query: {
                    role_id: row.role_id
                }
            });
        },

        /*刷新心也*/
        refresh() {
            this.reload();
            // 直接使用reload方法
        },

        /*删除*/
        deleteClick(row) {
            let self = this;
            ElMessageBox.confirm($t('删除后不可恢复，确认删除吗?'), $t('提示'), {
                confirmButtonText: $t('确定'),
                cancelButtonText: $t('取消'),
                type: 'warning'
            }).then(() => {
                self.loading = true;
                AuthApi.roleDelete({role_id: row.role_id},true).then(data => {
                    self.loading = false;
                    if (data.code == 1) {
                        this.$ElMessage({
                            message:  $t('删除成功'),
                            type: 'success'
                        });
                        self.getTableList();
                    } else {
                        self.loading = false;
                    }
                }).catch(error => {
                    self.loading = false;
                });
            }).catch(() => { });
        }
    }
};
</script>


