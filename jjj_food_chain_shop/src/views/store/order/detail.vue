<template>
    <!--
      作者：luoyiming
      时间：2019-10-25
      描述：订单详情
  -->
    <div class="pb50" v-loading="loading">
        <div class="product-content">
            <!--基本信息-->
            <div class="common-form">{{ $t('基本信息') }}</div>
            <div class="table-wrap">
                <el-row>
                    <el-col :span="6">
                        <div class="pb16">
                            <span class="gray9">{{ $t('订单类型：') }}</span>
                            {{ detail.order_source_text }}
                        </div>
                    </el-col>
                    <el-col :span="6">
                        <div class="pb16">
                            <span class="gray9">{{ $t('订单号：') }}</span>
                            {{ detail.order_no }}
                        </div>
                    </el-col>
                    <el-col :span="6" v-if="detail.user">
                        <div class="pb16">
                            <span class="gray9">{{ $t('买家：') }}</span>
                            {{ detail.user?.nickName || '' }}
                            <span>{{ $t('用户ID：') }}({{ detail.user?.user_id }})</span>
                        </div>
                    </el-col>
                    <el-col :span="6">
                        <div class="pb16">
                            <span class="gray9">{{ $t('订单金额：') }}</span>
                            {{ currency.unit }}{{ detail.order_price }}
                            <template v-if="currency.is_open == 1">{{ currency.vices?.vice_unit }}{{
                                (Number(detail.order_price) * Number(currency.vices?.unit_rate)).toFixed(2) }}</template>
                        </div>
                    </el-col>
                    <el-col :span="6" v-if="detail.express_price > 0">
                        <div class="pb16">
                            <span class="gray9">{{ $t('配送费：') }}</span>
                            {{ detail.express_price }}
                        </div>
                    </el-col>
                    <el-col :span="6" v-if="detail.bag_price > 0">
                        <div class="pb16">
                            <span class="gray9">{{ $t('包装费：') }}</span>
                            {{ detail.bag_price }}
                        </div>
                    </el-col>
                    <!-- <el-col :span="6" v-if="detail.service_money > 0">
						<div class="pb16">
							<span class="gray9">{{ $t('服务费：') }}</span>
							{{ detail.service_money }}
						</div>
					</el-col> -->
                    <!-- <el-col :span="6" v-if="detail.coupon_money > 0">
						<div class="pb16" >
							<span class="gray9">{{ $t('优惠券抵扣：') }}</span>
							{{ detail.coupon_money }}
						</div>
					</el-col> -->
                    <!-- <el-col :span="6" v-if="detail.points_money > 0">
						<div class="pb16" >
							<span class="gray9">{{ $t('积分抵扣：') }}</span>
							{{ detail.points_money }}
						</div>
					</el-col>
					<el-col :span="6"  v-if="detail.fullreduce_money > 0">
						<div class="pb16">
							<span class="gray9">{{ $t('满减金额：') }}</span>
							{{ detail.fullreduce_money }}
						</div>
					</el-col> -->
                    <!-- <el-col :span="6" v-if="detail.discount_money > 0">
						<div class="pb16" >
							<span class="gray9">{{ $t('优惠金额：') }}</span>
							{{ detail.discount_money }}
						</div>
					</el-col> -->
                    <el-col :span="6" v-if="detail.order_status.value == 30">
                        <div class="pb16">
                            <span class="gray9">{{ $t('实付款金额：') }}</span>
                            {{ currency.unit }}{{ detail.pay_price }}
                            <span v-if="detail.refund_money > 0">({{ $t('已退款：') }}
                                {{ currency.unit }}{{ detail.refund_money }})
                            </span>
                        </div>
                    </el-col>
                    <el-col :span="6" v-if="detail.order_status.value == 30">
                        <div class="pb16">
                            <span class="gray9">{{ $t('支付方式：') }}</span>
                            {{ detail.pay_type.text }}
                        </div>
                    </el-col>
                    <el-col :span="6">
                        <div class="pb16">
                            <span class="gray9">{{ $t('用餐方式：') }}</span>
                            {{ detail.delivery_type.text }}
                        </div>
                    </el-col>
                    <!-- <el-col :span="6" v-if="detail.mealtime">
						<div class="pb16">
							<span class="gray9">{{ $t('取餐时间：') }}</span>
							{{ detail.mealtime}}
						</div>
					</el-col> -->
                    <el-col :span="6" v-if="detail.table_no">
                        <div class="pb16">
                            <span class="gray9">{{ $t('桌号：') }}</span>
                            {{ detail.table_no }}
                        </div>
                    </el-col>
                    <el-col :span="6">
                        <div class="pb16">
                            <span class="gray9">{{ $t('交易状态：') }}</span>
                            {{
                                detail.order_status.value == 10 ? $t('待付款') :
                                detail.order_status.value == 20 ? $t('已取消') : $t('已完成')
                            }}
                        </div>
                    </el-col>
                    <el-col :span="6" v-if="detail.cashier">
                        <div class="pb16">
                            <span class="gray9">{{ $t('收银员：') }}</span>
                            {{ detail.cashier?.real_name }}
                        </div>
                    </el-col>
                    <el-col :span="6" v-if="detail.is_buffet == 1">
                        <div class="pb16">
                            <span class="gray9">{{ $t('自助餐：') }}</span>
                            <template v-if="detail?.buffet?.length > 0">{{ buffet }}</template>
                            <template v-else>-</template>
                        </div>
                    </el-col>
                </el-row>
            </div>

            <div class="common-form mt16">{{ $t('商品信息') }}</div>
            <div class="table-wrap">
                <el-table size="small" :data="tableData" border style="width: 100%">
                    <el-table-column prop="name_text" :label="$t('商品')" width="400">
                        <template #default="scope">
                            <div class="product-info">
                                <div class="pic">
                                    <img v-if="scope.row.image.file_path=='buffet'" src="../../../assets/img/buffet.png" />
                                    <img v-else-if="scope.row.image.file_path=='clock'" src="../../../assets/img/clock.png" />
                                    <img v-else v-img-url="scope.row.image.file_path" />
                                </div>
                                <div class="info">
                                    <div class="name">{{ scope.row.name_text }}</div>
                                    <div class="gray9" v-if="scope.row.product_attr != ''">{{ scope.row.product_attr }}
                                    </div>
                                    <div class="orange" v-if="scope.row.refund">
                                        {{ scope.row.refund.type.text }}-{{ scope.row.refund.status.text }}
                                    </div>
                                    <div class="price">
                                        <span class="ml10">
                                            <span v-if="scope.row.discount_type">-</span>{{ currency.unit }}{{ scope.row.product_price }}
                                            <template v-if="currency.is_open == 1">
                                                <span v-if="scope.row.discount_type">-</span>{{currency.vices?.vice_unit }}{{
                                                (Number(scope.row.product_price) *
                                                    Number(currency.vices?.unit_rate)).toFixed(2) }}</template>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </el-table-column>
                    <el-table-column prop="total_num" :label="$t('购买数量')">
                        <template #default="scope">
                            <p> {{ scope.row.total_num }}</p>
                        </template>
                    </el-table-column>
                    <el-table-column prop="product_price" :label="$t('商品总价')">
                        <template #default="scope">
                            <p v-if="scope.row.discount_type"> <span v-if="scope.row.discount_type">-</span>{{ currency.unit }}
                                {{Number(scope.row.total_price).toFixed(2) }}
                                <span v-if="currency.is_open == 1"> <span v-if="scope.row.discount_type">-</span>{{ currency.vices?.vice_unit }}{{
                                    (Number(scope.row.total_price)  *  Number(currency.vices?.unit_rate)).toFixed(2) }}</span>
                            </p>
                            <p v-else>{{ currency.unit }}{{
                                (Number(scope.row.product_price) * Number(scope.row.total_num)).toFixed(2) }}
                                <span v-if="currency.is_open == 1">{{ currency.vices?.vice_unit }}{{
                                    (Number(scope.row.product_price) * Number(scope.row.total_num) *
                                        Number(currency.vices?.unit_rate)).toFixed(2) }}</span>
                            </p>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
            <div class="table-wrap" v-if="detail.order_status.value == 20 && detail.cancel_remark != ''">
                <div class="common-form mt16">{{ $t('取消信息') }}</div>
                <div class="table-wrap">
                    <el-row>
                        <el-col :span="6">
                            <div class="pb16">
                                <span class="gray9">{{ $t('商家备注') }}:</span>
                                {{ detail.cancel_remark }}
                            </div>
                        </el-col>
                    </el-row>
                </div>
            </div>
        </div>
        <div class="common-button-wrapper">
            <el-button size="small" @click="cancelFunc">{{ $t('返回') }}</el-button>
            <el-button v-if="detail.order_status.value == 30" @click="refundClick(detail)" type="danger" size="small"
                v-auth="'/store/operate/refund'">{{ $t('退款')
                }}</el-button>
            <el-button v-if="detail.order_status.value == 10" @click="cancelClick(detail)" type="danger" size="small"
                v-auth="'/store/operate/order_cancel'">{{ $t('取消') }}
            </el-button>
            <el-button v-if="detail.order_status.value == 20" @click="delClick(detail)" type="danger" size="small"
                v-auth="'/store/order/delete'">{{ $t('删除') }}
            </el-button>
        </div>
        <!--处理-->
        <Cancel v-if="open_edit" :open_edit="open_edit" :order_no="order_no" :order_id="order_id"
            @closeDialog="closeDialogFunc($event, 'edit')">
        </Cancel>
        <!--处理-->
        <refund v-if="open_refund" :open_edit="open_refund" :order_no="order_no" :order_id="order_id" :pay_price="pay_price"
            @closeDialog="closerefundDialogFunc($event, 'edit')">
        </refund>
    </div>
</template>

<script>
import OrderApi from '@/api/order.js';
import Cancel from './dialog/cancel.vue';
import refund from './dialog/refund.vue';
import { useUserStore } from '@/store';
const { currency } = useUserStore();
export default {
    components: {
        Cancel,
        refund,
    },
    data() {
        return {
            currency: currency,
            active: 0,
            /*是否加载完成*/
            loading: true,
            /*订单数据*/
            detail: {
                order_id: 0,
                pay_status: [],
                pay_type: [],
                delivery_type: [],
                user: {},
                address: [],
                product: [],
                order_status: [],
                extract: [],
                delivery_status: [],
                supplier: {
                    name: ''
                }
            },
            /*是否打开编辑弹窗*/
            open_edit: false,
            open_refund: false,
            /*当前编辑的对象*/
            order_no: 0,
            order_id: 0,
            pay_price: 0,
            buffet: [],
            tableData: [],
        };
    },
    created() {
        /*获取列表*/
        this.getParams();
    },

    methods: {
        next() {
            if (this.active++ > 4) this.active = 0;
        },
        /*获取参数*/
        getParams() {
            let self = this;
            // 取到路由带过来的参数
            const params = this.$route.query.order_id;
            OrderApi.storeOrderdetail({
                order_id: params
            }, true)
                .then(data => {
                    self.loading = false;
                    self.detail = data.data.detail;
                    self.detail.buffet.map(item => {
                        self.buffet.push(item.name_text)
                        self.tableData.push({
                            name_text: item.name_text,
                            image: { file_path:'buffet' },
                            product_attr: '',
                            refund: '',
                            product_price: item.price,
                            total_num: self.detail.meal_num,
                        })
                    })
                    self.buffet = self.buffet.join('+')

                    self.detail.buffetDiscount.map(item => {
                        self.tableData.push({
                            name_text: item.name_text,
                            image: { file_path:'buffet' },
                            product_attr: '',
                            refund: '',
                            product_price: item.price,
                            total_num: item.num,
                            discount_type:item.discount_type,
                            total_price:item.total_price,
                        })
                    })

                    self.detail.delay.map(item => {
                        self.tableData.push({
                            name_text: item.name_text,
                            image: { file_path:'clock' },
                            product_attr: '',
                            refund: '',
                            product_price: item.price,
                            total_num: self.detail.meal_num,
                        })
                    })


                    self.detail.product.map(item => {
                        self.tableData.push({
                            name_text: item.product_name_text,
                            image: item.image,
                            product_attr: item.product_attr,
                            refund: item.refund,
                            product_price: item.product_price,
                            total_num: item.total_num,
                        })
                    })

                })
                .catch(error => {
                    self.loading = false;
                });
        },
        /*取消*/
        cancelFunc() {
            this.$router.back(-1);
        },


        /*打开取消*/
        cancelClick(item) {
            this.order_no = item.order_no;
            this.order_id = item.order_id;
            this.open_edit = true;
        },
        refundClick(item) {
            this.order_no = item.order_no;
            this.order_id = item.order_id;
            this.pay_price = (Number(item.pay_price) - Number(item.refund_money)).toFixed(2);
            this.open_refund = true;
        },

        /*关闭弹窗*/
        closeDialogFunc(e, f) {
            if (f == 'edit') {
                this.open_edit = e.openDialog;
                if (e.type == 'success') {
                    this.getParams();
                }
            }
        },
        /*关闭弹窗*/
        closerefundDialogFunc(e, f) {
            if (f == 'edit') {
                this.open_refund = e.openDialog;
                if (e.type == 'success') {
                    this.getParams();
                }
            }
        },

        delClick(item) {
            let self = this;
            ElMessageBox.confirm($t('删除后不可恢复，确认删除吗?'), $t('提示'), {
                type: 'warning'
            })
                .then(() => {
                    OrderApi.storedelete({
                        order_id: item.order_id
                    }).then(data => {
                        this.$ElMessage({
                            message: $t('删除成功'),
                            type: 'success'
                        });
                        this.$router.back(-1);
                    });
                });
        },
    }
};
</script>
<style scoped lang="scss">.common-button-wrapper {
    justify-content: flex-end;
}</style>
