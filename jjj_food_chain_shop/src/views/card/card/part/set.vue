<template>
  <!--
    	作者：luoyiming
    	时间：2019-10-26
    	描述：商品管理-商品编辑-高级设置
    -->
  <div class="basic-setting-content pl16 pr16">
    <div class="common-form">{{$t('领取设置')}}</div>
    <el-form-item :label="$t('有效期：')" :rules="[{ required: true, message:  $t('请输入有效期') }]" prop="model.expire">
      <el-input-number :controls="false" :min="0" :max="999" class="max-w460" :placeholder="$t('请输入有效期')" v-model.number="form.model.expire"></el-input-number>
      <div class="gray9">{{ $t('有效期限（月），0为永久有效') }}</div>
    </el-form-item>
    <el-form-item :label="$t('价格：')" :rules="[{ required: true, message:  $t('请输入价格') }]" prop="model.money">
      <el-input-number :controls="false" :min="0" :max="100000000"  class="max-w460" :placeholder="$t('请输入价格')" v-model.number="form.model.money"></el-input-number>
      <div class="gray9">{{ $t('用户购买会员卡需要支付的金额，0为用户免费领取') }}</div>
    </el-form-item>
<!--    <el-form-item label="库存：" :rules="[{ required: true, message: '请输入库存' }]" prop="model.stock">
      <el-input v-model="form.model.stock" class="max-w460" type="number"></el-input>
      <div class="gray9">会员卡剩余可领取的数量</div>
    </el-form-item> -->
    <el-form-item :label="$t('状态：')">
      <el-radio-group v-model="form.model.status">
        <el-radio :label="0">{{ $t('启用') }}</el-radio>
        <el-radio :label="1">{{ $t('关闭') }}</el-radio>
      </el-radio-group>
    </el-form-item>
    <el-form-item :label="$t('使用须知：')" :rules="[{ required: true, message: $t('请输入使用须知') }]" prop="model.content">
      <el-input type="textarea" :maxlength="200" show-word-limit v-model="form.model.content" class="max-w460" rows="8"></el-input>
    </el-form-item>
  </div>
</template>

<script>
  export default {
    data() {
      return {};
    },
    created() {

    },
    inject: ['form'],
    methods: {

    }
  };
</script>


