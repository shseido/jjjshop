<template>
    <!--
      	描述：商品-店内商品
      -->
    <div class="common-seach-wrap">
        <!--商品管理-->
        <productindex v-if="activeName == 'productindex'"></productindex>
        <!--分类管理-->
        <categoryindex v-if="activeName == 'categoryindex'"></categoryindex>
        <!-- 特色管理 -->
        <featureindex v-if="activeName == 'featureindex'"></featureindex>
    </div>
</template>
<script>
import { reactive, toRefs, defineComponent } from 'vue';
import { useUserStore } from "@/store";
import categoryindex from './category/index.vue';
import productindex from './product/index.vue';
import featureindex from './feature/index.vue'
export default defineComponent({
    components: {
        categoryindex,
        productindex,
        featureindex,
    },
    setup() {

        const { bus_emit, bus_off, bus_on } = useUserStore();
        const state = reactive({
            bus_emit,
            bus_off,
            bus_on,
            /*是否加载完成*/
            loading: true,
            form: {},
            /*参数*/
            param: {},
            /*当前选中*/
            activeName: 'productindex',
            /*切换数组原始数据*/
            sourceList: [{
                key: 'productindex',
                value: $t('商品管理'),
                path: '/product/store/product/index'
            },
            {
                key: 'categoryindex',
                value: $t('普通分类'),
                path: '/product/store/category/index'
            },
            {
                key: 'featureindex',
                value: $t('特色分类'),
                path: '/product/store/feature/index'
            },
            ],
            /*权限筛选后的数据*/
            tabList: [],

        })
        return {
            ...toRefs(state),

        };

    },
    mounted() {
        this.tabList = this.authFilter();
        if (this.tabList.length > 0) {
            this.activeName = this.tabList[0].key;
        }
        if (this.$route.query.type != null) {
            this.activeName = this.$route.query.type;
        }

        /*监听传插件的值*/
        this.bus_on('activeValue', res => {
            this.activeName = res;
        });

        //发送类别切换
        let params = {
            active: this.activeName,
            list: this.tabList,
            tab_type: 'storeproduct',
        }
        this.bus_emit('tabData', params);

    },
    beforeUnmount() {
        //发送类别切换
        this.bus_emit('tabData', { active: null, tab_type: 'storeproduct', list: [] });
        this.bus_off('activeValue');
    },
    methods: {
        /*权限过滤*/
        authFilter() {
            let list = [];
            for (let i = 0; i < this.sourceList.length; i++) {
                let item = this.sourceList[i];
                if (this.$filter.isAuth(item.path)) {
                    list.push(item);
                }
            }
            return list;
        },

    }
});
</script>

<style scoped>
.operation-wrap {
    height: 124px;
    border-radius: 8px;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    padding: 30px 30px;
    -webkit-box-orient: vertical;
    -webkit-box-direction: normal;
    -ms-flex-direction: column;
    flex-direction: column;
    overflow: hidden;
    background: #909399;
    background-size: 100% 100%;
    color: #fff;
}
</style>
