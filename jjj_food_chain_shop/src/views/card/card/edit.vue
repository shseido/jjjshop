<template>
    <div class="product-add" v-loading="loading">

        <!--form表单-->
        <el-form size="small" ref="form" class="product-form" :model="form" label-position="top" label-width="180px"
            v-if="!loading">
            <div class="product-form-flex" ref="formContainer">
                <!--基础信息-->
                <Basic></Basic>
                <!--高级设置-->
                <Set></Set>
            </div>

            <!--提交-->
            <div class="common-button-wrapper">
                <el-button size="small" @click="cancelFunc">{{ $t('取消') }}</el-button>
                <el-button size="small" type="primary" @click="onSubmit" :disabled="save_loading">{{ $t('确定') }}</el-button>
            </div>
        </el-form>
    </div>
</template>

<script>
import CardApi from '@/api/card.js';
import Basic from './part/Basic.vue';
import Set from './part/set.vue';
import {
    formatModel
} from '@/utils/base.js';
export default {
    components: {
        /*基础信息*/
        Basic,
        /*高级设置*/
        Set
    },
    data() {
        return {
            activeName: 'basic',
            /*会员卡ID*/
            card_id: 0,
            /*是否正在加载*/
            loading: true,
            /*是否正在提交保存*/
            save_loading: false,
            /*form表单数据*/
            form: {
                model: {
                    card_name: '',
                    type_id: '',
                    card_style: '',
                    sort: null,
                    is_discount: 0,
                    discount: 0,
                    open_points: 0,
                    open_points_num: 0,
                    open_coupon: 0,
                    open_coupons: null,
                    month_points: 0,
                    month_points_num: 0,
                    month_coupon: 0,
                    month_coupons: null,
                    expire: 0,
                    money: 0,
                    stock: '',
                    status: 0,
                    content: '',
                    sub_card: 0,
                    sub_num: 0,
                    default_style: '',
                    is_default: 0
                },
                /*会员卡类型*/
                typeList: [],
                /*优惠券*/
                couponList: [],
                /*副卡*/
                subList: [],
                image: [],

            },
        };
    },
    provide: function () {
        return {
            form: this.form
        };
    },
    created() {
        /*获取列表*/
        this.card_id = this.$route.query.card_id;
        this.getData();

    },
    methods: {
        /**
         * 获取基础数据
         */
        getData: function () {
            let self = this;
            CardApi.getEditData({
                card_id: self.card_id,
            },
                true
            )
                .then(res => {
                    self.loading = false;
                    Object.assign(self.form, res.data);
                    this.form.month_points = this.form.month_points == 1 ? true : false;
                    this.form.month_coupon = this.form.month_coupon == 1 ? true : false;
                })
                .catch(error => {
                    self.loading = false;
                });
        },

        /*提交*/
        onSubmit: function () {
            let self = this;
            self.$refs.form.validate(valid => {
                if (valid) {
                    let params = self.form.model;
                    params.card_id = self.card_id;
                    self.save_loading = true;
                    CardApi.editcard({
                        card_id: self.card_id,
                        params: JSON.stringify(params)
                    }, true)
                        .then(data => {
                            self.save_loading = false;
                            this.$ElMessage({
                                message: $t('保存成功'),
                                type: 'success'
                            });
                            self.cancelFunc();
                        })
                        .catch(error => {
                            self.save_loading = false;
                        });
                }
            });
        },

        /*取消*/
        cancelFunc() {
            this.$router.back(-1);
        }
    }
};
</script>

<style lang="scss" scoped>
.basic-setting-content {}

.product-add {
    height: calc(100% - 14px);
    overflow: hidden;
}

.product-form {
    height: 100%;
    overflow: hidden;
    display: flex;
    flex-direction: column;

    .product-form-flex {
        flex: 1 1 auto;
        overflow-y: auto;
    }

    .common-button-wrapper {
        flex: 0 0 auto;
        flex-shrink: 0;
    }

}
</style>
