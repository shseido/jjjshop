<template>
    <!--
    	作者：luoyiming
    	时间：2019-10-26
    	描述：商品管理-商品编辑-高级设置
    -->
    <div class="buy-set-content">
        <!-- <div class="common-form">积分设置</div>
    <el-form-item label="是否开启积分赠送：">
      <el-radio-group v-model="form.model.is_points_gift">
        <el-radio :label="1">开启</el-radio>
        <el-radio :label="0">关闭</el-radio>
      </el-radio-group>
    </el-form-item> -->
        <!--其他设置-->
        <div class="common-form mt50">{{ $t('其他设置') }}</div>
        <el-form-item v-if="form.model.product_status != 40" :label="$t('商品状态：')"
            :rules="[{ required: true, message: $t('选择商品状态') }]" prop="model.product_status">
            <el-radio-group v-model="form.model.product_status">
                <el-radio :label="10">{{ $t('上架') }}</el-radio>
                <el-radio :label="20">{{ $t('下架') }}</el-radio>
            </el-radio-group>
        </el-form-item>

        <el-form-item  :label="$t('显示在平板端：')"
            :rules="[{ required: true, message: $t('选择是否显示') }]" prop="model.product_status">
            <el-radio-group v-model="form.model.is_show_tablet">
                <el-radio :label="1">{{ $t('显示') }}</el-radio>
                <el-radio :label="2">{{ $t('不显示') }}</el-radio>
            </el-radio-group>
        </el-form-item>


        <el-form-item  :label="$t('需要送厨：')"
            :rules="[{ required: true, message: $t(' ') }]" prop="model.product_status">
            <el-radio-group v-model="form.model.is_show_kitchen">
                <el-radio :label="1">{{ $t('是') }}</el-radio>
                <el-radio :label="2">{{ $t('否') }}</el-radio>
            </el-radio-group>
        </el-form-item>

        <el-form-item :label="$t('商品排序：')" :rules="[{ required: true, message: $t('接近0，排序等级越高') }]" prop="model.product_sort">
            <el-input-number :controls="false" :min="0" :max="999" :placeholder="$t('接近0，排序等级越高')"
                v-model="form.model.product_sort" class="max-w460"></el-input-number>
        </el-form-item>
        <el-form-item :label="$t('限购数量：')" :rules="[{ required: true, message: $t('请输入限购数量') }]" prop="model.limit_num">
            <el-input-number :controls="false" :min="0" :max="999" v-model="form.model.limit_num" class="max-w460"></el-input-number>
            <div class="gray9">{{ $t('每单/每桌购买的最大数量，0为不限购') }}</div>
        </el-form-item>
        <el-form-item :label="$t('打印标签：')" prop="model.label_id" >
            <el-select v-model="form.model.label_id" clearable class="max-w460" size="default">
                <el-option :value="0" :label="$t('无')"></el-option>
                <template v-for="cat in form.labelList" :key="cat.label_id">
                    <el-option :value="cat.label_id" :label="cat.label_name_text"></el-option>
                </template>
            </el-select>
        </el-form-item>
        <!--会员折扣设置-->
        <div class="common-form mt50">{{ $t('会员折扣设置') }}</div>
        <el-form-item :label="$t('是否开启会员折扣：')">
            <el-radio-group v-model="form.model.is_enable_grade">
                <el-radio :label="1">{{ $t('开启') }}</el-radio>
                <el-radio :label="0">{{ $t('关闭') }}</el-radio>
            </el-radio-group>
        </el-form-item>
        <el-form-item :label="$t('会员折扣设置：')" v-if="form.model.is_enable_grade == 1">
            <el-radio-group v-model="form.model.is_alone_grade">
                <el-radio :label="0">{{ $t('默认折扣') }}</el-radio>
                <!-- <el-radio :label="1">{{ $t('仅需支付') }}</el-radio> -->
            </el-radio-group>
            <div class="gray9" v-if="form.model.is_alone_grade == 0">{{ $t('默认折扣：默认为用户所属会员等级的折扣率') }}</div>
            <div class="gray9" v-if="form.model.is_alone_grade == 1">{{ $t('仅需支付：用户购买此商品仅需支付的金额或比例') }}</div>
        </el-form-item>
        <el-form-item :label="$t('折扣佣金类型：')" v-if="form.model.is_alone_grade == 1 && form.model.is_enable_grade == 1">
            <el-radio-group v-model="form.model.alone_grade_type" @change="changeGradeType">
                <el-radio :label="10">{{ $t('百分比') }}</el-radio>
                <el-radio :label="20">{{ $t('固定金额') }}</el-radio>
            </el-radio-group>
        </el-form-item>

        <el-form-item label="" v-if="form.model.is_alone_grade == 1 && form.model.is_enable_grade == 1">
            <div class="percent-w50">
                <el-table :data="form.gradeList" border size="">
                    <el-table-column prop="name" :label="$t('会员等级')">
                    </el-table-column>
                    <el-table-column prop="name" :label="$t('折扣')">
                        <template #default="scope">
                            <div class="d-s-c">
                                <el-form-item class="product-equity" :rules="[{
                                    validator: () => {
                                        return scope.row.product_equity ? true : false;
                                    },
                                    message: $t('请输入折扣')
                                }]" prop="model.image">
                                    <el-input-number v-model="scope.row.product_equity"
                                        :min="form.model.alone_grade_type == 10 ? 1 : 0"
                                        :max="form.model.alone_grade_type == 10 ? 100 : minPrice" :controls="false"
                                        :placeholder="$t('请输入折扣')"></el-input-number>
                                    <span class="ml10">{{ form.model.alone_grade_type == 10 ? grade_unit : currency.unit
                                    }}</span>
                                </el-form-item>
                            </div>
                        </template>
                    </el-table-column>
                </el-table>
            </div>
        </el-form-item>

        <!--分销设置-->
        <div class="common-form mt50" v-if="form.basicSetting.is_open == 1">分销设置</div>
        <el-form-item label="是否开启分销：" v-if="form.basicSetting.is_open == 1">
            <el-radio-group v-model="form.model.is_agent">
                <el-radio :label="1">开启</el-radio>
                <el-radio :label="0">关闭</el-radio>
            </el-radio-group>
        </el-form-item>
        <template v-if="form.model.is_agent === 1">
            <el-form-item label="分销规则：" v-if="form.basicSetting.is_open == 1">
                <el-radio-group v-model="form.model.is_ind_agent">
                    <el-radio :label="0">平台规则</el-radio>
                    <el-radio :label="1">单独规则</el-radio>
                </el-radio-group>
                <div class="gray9">平台规则：层级({{ form.basicSetting.level }}级)
                    <span v-if="form.basicSetting.level >= 1" style="padding-left: 10px;">1级佣金({{
                        form.agentSetting.first_money }}%)</span>
                    <span v-if="form.basicSetting.level >= 2" style="padding-left: 10px;">2级佣金({{
                        form.agentSetting.second_money }}%)</span>
                    <span v-if="form.basicSetting.level >= 3" style="padding-left: 10px;">3级佣金({{
                        form.agentSetting.third_money }}%)</span>
                </div>
            </el-form-item>
            <template v-if="form.model.is_ind_agent === 1 && form.basicSetting.is_open == 1">
                <el-form-item label="分销佣金类型：">
                    <el-radio-group v-model="form.model.agent_money_type" @change="changeMoneyType">
                        <el-radio :label="10">百分比</el-radio>
                        <el-radio :label="20">固定金额</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="单独分销设置：">
                    <el-input type="number" min="0" v-model="form.model.first_money" class="max-w460">
                        <template #prepend>
                            一级佣金：
                        </template>
                        <template #append>
                            {{ unit }}
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item v-if="form.basicSetting.level >= 2">
                    <el-input type="number" min="0" v-model="form.model.second_money" class="max-w460">
                        <template #prepend>
                            二级佣金：
                        </template>
                        <template #append>
                            {{ unit }}
                        </template>
                    </el-input>
                </el-form-item>
                <el-form-item v-if="form.basicSetting.level >= 3">
                    <el-input type="number" min="0" v-model="form.model.third_money" class="max-w460">
                        <template #prepend>
                            三级佣金：
                        </template>
                        <template #append>
                            {{ unit }}
                        </template>
                    </el-input>
                </el-form-item>
            </template>
        </template>

    </div>
</template>

<script>
import { useUserStore } from '@/store';
const { currency } = useUserStore();
export default {
    data() {
        return {
            unit: '%',
            grade_unit: '%',
            currency: currency,
            minPrice: 0,
        };
    },
    created() {
        if (this.form.model.alone_grade_type == '20') {
            this.grade_unit = '元';
        }
        if (this.form.model.agent_money_type == '20') {
            this.unit = '元';
        }
    },
    inject: ['form'],
    watch: {
        'form': {
            handler(val) {
                let price = []
                val.model.sku.map((item) => {
                    price.push(item.product_price)
                })
                this.minPrice = Math.min(...price);
            },
            immediate: true,
            deep: true,
        },
    },
    methods: {
        /*换算单位*/
        changeMoneyType: function (val) {
            if (val == '10') {
                this.unit = '%';
            } else {
                this.unit = '元';
            }
        },
        /*换算单位*/
        changeGradeType: function (val) {

            this.form.gradeList.map((item, index) => {
                this.form.gradeList[index].product_equity = null
            })
            if (val == '10') {
                this.grade_unit = '%';
            } else {
                this.grade_unit = '元';
            }
        }
    }
};
</script>
<style lang="scss" scoped>
:deep(.el-input__wrapper) {
    padding-left: 7px !important;
    padding-right: 7px !important;
}

.product-equity {
    display: flex;
    align-items: center;
    width: 100%;
    margin-top: 16px;

    :deep(.el-form-item__content) {
        flex-wrap: nowrap;
    }
}
</style>

