<template>
    <!--
      作者 luoyiming
      时间：2020-06-09
      描述：会员-积分管理-积分明细
  -->
    <div class="user">
        <!--搜索表单-->
        <div class="common-seach-wrap">
            <el-form size="small" :inline="true" :model="formInline" class="demo-form-inline">
                <el-form-item :label="$t('变更场景')">
                    <el-select v-model="formInline.scene" :placeholder="$t('请选择')">
                        <el-option :label="$t('全部')" value="0"></el-option>
                        <el-option v-for="(item, index) in Scene" :key="index" :label="item.name"
                            :value="item.value"></el-option>
                    </el-select>
                </el-form-item>

                <el-form-item :label="$t('昵称/手机号/会员ID')">
                    <el-input v-model="formInline.keyword" :placeholder="$t('昵称/手机号/会员ID')"></el-input>
                </el-form-item>
                <el-form-item :label="$t('变动时间')">
                    <div class="block">
                        <span class="demonstration"></span>
                        <el-date-picker v-model="formInline.date" type="daterange" value-format="YYYY-MM-DD"
                        range-separator="~" :start-placeholder="$t('开始日期')" :end-placeholder="$t('结束日期')">
                        </el-date-picker>
                    </div>
                </el-form-item>
                <el-form-item>
                    <el-button class="search-button" type="primary" icon="Search" @click="onSubmit">{{ $t('查询') }}</el-button>
                </el-form-item>
            </el-form>
        </div>
        <!--内容-->
        <div class="product-content">
            <div class="table-wrap">
                <el-table size="small" :data="tableData" border style="width: 100%" v-loading="loading">
                    <el-table-column prop="log_id" label="ID" width="60"></el-table-column>
                    <el-table-column prop="" :label="$t('昵称')">
                        <template #default="scope">
                            <span>{{ scope.row.user.nickName }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="user.mobile" :label="$t('手机号')" width="160">
                    </el-table-column>
                    <el-table-column prop="user_id" :label="$t('会员ID')" width="80">
                    </el-table-column>

                    <el-table-column prop="value" :label="$t('变动数量')">
                        <template #default="scope">
                            <p v-if="scope.row.value > 0">+{{ scope.row.value }}</p>
                            <p v-else>{{ scope.row.value }}</p>
                            </template>

           
                    </el-table-column>
                    <el-table-column prop="scene.text" :label="$t('变动场景')">
                        <template #default="scope">
                            <span v-if="!scope.row.scene" >-</span>
                            <span v-if="scope.row.scene == 10" style="color: #409EFF">{{ scope.row.scene.text }}</span>
                            <span v-if="scope.row.scene == 20" style="color: #67C23A">{{ scope.row.scene.text }}</span>
                            <span v-if="scope.row.scene == 30" style="color: #F56C6C">{{ scope.row.scene.text }}</span>
                            <span v-if="scope.row.scene == 40" style="color: #E6A23C">{{ scope.row.scene.text }}</span>
                            <span v-if="scope.row.scene == 50" style="color: #E63C81">{{ scope.row.scene.text }}</span>
                        </template>
                    </el-table-column>
                    <el-table-column prop="describe" :label="$t('描述/说明')"></el-table-column>
                    <el-table-column prop="create_time" :label="$t('变动时间')"></el-table-column>
                </el-table>
            </div>

            <!--分页-->
            <div class="pagination">
                <el-pagination @size-change="handleSizeChange" @current-change="handleCurrentChange" background
                    :current-page="curPage" :page-size="pageSize" layout="total, prev, pager, next, jumper"
                    :total="totalDataNumber">
                </el-pagination>
            </div>
        </div>

    </div>
</template>

<script>
import PointsApi from '@/api/points.js';
export default {
    components: {},
    data() {
        return {
            /*是否加载完成*/
            loading: true,
            /*列表数据*/
            tableData: [],
            /*一页多少条*/
            pageSize: 10,
            /*一共多少条数据*/
            totalDataNumber: 0,
            /*当前是第几页*/
            curPage: 1,
            /*横向表单数据模型*/
            formInline: {
                keyword: '',
                date: '',
                map: '',
                scene:'',
            },
            /*场景*/
            Scene: [],
        };
    },
    created() {
        /*获取列表*/
        this.getTableList();

    },
    methods: {
        /*选择第几页*/
        handleCurrentChange(val) {
            let self = this;
            self.curPage = val;
            self.loading = true;
            self.getTableList();
        },

        /*每页多少条*/
        handleSizeChange(val) {
            this.curPage = 1;
            this.pageSize = val;
            this.getTableList();
        },

        /*获取列表*/
        getTableList() {
            let self = this;
            let Params = self.formInline;
            Params.page = self.curPage;
            Params.list_rows = self.pageSize;
            PointsApi.GetUserList(Params, true).then(data => {
                self.loading = false;
                self.tableData = data.data.list.data;
                self.totalDataNumber = data.data.list.total;
                self.Scene = data.data.attributes.scene;
            }).catch(error => {

            });
        },

        /*搜索查询*/
        onSubmit() {
            let self = this;
            self.loading = true;
            self.getTableList();
        },
    }
};
</script>

