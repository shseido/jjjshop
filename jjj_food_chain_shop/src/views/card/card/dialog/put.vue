<template>
    <!--
      作者：luoyiming
      时间：2019-10-25
      描述：会员-用户列表-会员等级
  -->
    <el-dialog :title="$t('发卡')" v-model="dialogVisible" @close='dialogFormVisible' :close-on-click-modal="false"
        :close-on-press-escape="false" :modal-append-to-body="false" width='600px'>
        <el-form size="small" :model="form" label-position="top">
            <el-form-item>
                <div class="d-s-s">
                    <div class="d-b-s">
                        <div class="fb mr10"></div>
                        <div class="text item">
                            <div>{{ $t('卡名称: ') }}{{ form.card_name }}</div>
                            <div>{{ $t('卡ID:') }} {{ form.card_id }}</div>
                            <div>{{ $t('有效期:') }}
                                <span v-if="form.expire > 0">{{ form.expire }}{{ $t('月') }}</span>
                                <span v-else>{{ $t('永久有效') }}</span>
                            </div>
                            <div>{{ $t('折扣: ') }} <span v-if="form.is_discount > 0">{{ Number(form.discount) }}{{ $t('折')
                            }}</span>
                                <span v-else>{{ $t('无') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

            </el-form-item>
            <el-form-item label="" :label-width="formLabelWidth">
                <div class="d-s-s d-c w-100">
                    <el-button @click="openGetuser" icon="Plus">{{ $t('选择会员') }}</el-button>
                    <div class="select-list" v-if="select_list.length > 0">
                        <template v-for="item, index in select_list">
                            <div class="select-button">
                                {{ item.nickName }}
                                <el-icon class="select-icon" @click="deleteOne(index)">
                                    <CircleCloseFilled />
                                </el-icon>
                            </div>
                        </template>

                    </div>
                </div>

            </el-form-item>
        </el-form>
        <template #footer>
            <div class="dialog-footer">
                <el-button @click="dialogFormVisible">{{ $t('取消') }}</el-button>
                <el-button type="primary" @click="editUser" :loading="loading">{{ $t('确定') }}</el-button>
            </div>
        </template>
        <!--选择用户-->
        <GetUser :is_open="open_getuser" @close="closeGetuserFunc"></GetUser>
    </el-dialog>
</template>

<script>
import CardApi from '@/api/card.js';
import GetUser from '@/components/user/GetUser.vue';
export default {
    components: {
        GetUser,
    },
    data() {
        return {
            /*左边长度*/
            formLabelWidth: '120px',
            /*是否显示*/
            loading: false,
            dialogVisible: false,
            /*获取用户是否显示*/
            open_getuser: false,
            user_ids: '',
            /*选择的用户列表*/
            select_list: [],
        };
    },
    props: ['open_edit', 'form'],
    created() {
        this.dialogVisible = this.open_edit;
    },
    methods: {

        /*修改用户*/
        editUser() {
            let self = this;
            let params = {};
            params.card_id = self.form.card_id;
            params.user_ids = '';
            self.select_list.map((item,index) => {
                if(index < self.select_list.length - 1){
                    params.user_ids += item.user_id +','
                }else{
                    params.user_ids += item.user_id
                }
             })
            self.loading = true;
            CardApi.putcard(params, true)
                .then(data => {
                    self.loading = false;
                    if (data.code == 1) {
                        this.$ElMessage({
                            message: $t('操作成功'),
                            type: 'success'
                        });
                        self.dialogFormVisible(true);
                    }
                })
                .catch(error => {
                    self.loading = false;
                });
        },
        /*打开获取用户*/
        openGetuser() {
            this.open_getuser = true;
        },

        deleteOne(index) {
            this.select_list.splice(index, 1)
        },

        /*关闭获取用户*/
        closeGetuserFunc(e) {
            if (e && e.type != 'error') {
                this.select_list = [...e.params];
            }
            this.open_getuser = false;
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
        }

    }
};
</script>

<style scoped>
.d-c-s {
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.w-100 {
    width: 100%;
}

.select-list {
    width: 100%;
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 16px;
}

.select-button {
    border: solid 1px var(--el-color-tips);
    color: var(--el-color-tips);
    padding: 0 16px;
    border-radius: 4px;
    position: relative;

    .select-icon {
        position: absolute;
        right: -7px;
        top: -7px;
        cursor: pointer;
        color: #c80000;
    }
}
</style>
