<template>
    <el-config-provider :locale="locale">
        <router-view />
    </el-config-provider>
</template>

<script setup>
import {
    ref,
    reactive,
    computed,
    onMounted,
    onUnmounted,
} from 'vue';
import {
    ElConfigProvider
} from 'element-plus';
import { useRoute, useRouter } from 'vue-router';
// import { useLockscreenStore } from "../src/store/model/lockscreen"
import { languageStore } from './store/model/language';
import { useUserStore } from '@/store';
import zhCn from "element-plus/es/locale/lang/zh-cn";
import zhTw from "element-plus/es/locale/lang/zh-tw";
import en from "element-plus/es/locale/lang/en";
import th from "element-plus/es/locale/lang/th";
import IndexApi from '@/api/index.js';
import {
    getSessionStorage,
    setSessionStorage
} from '@/utils/base.js'
import configObj from "@/config";
import {
    getStorage
} from '@/utils/storageData';
import {
    createdAuth
} from '@/utils/createdAuth.js'

const { menu } = configObj;
const { afterLogin, userInfo, token, currency } = useUserStore();
// const useLockscreen = useLockscreenStore();
// const isLock = computed(() => useLockscreen.isLock);
// const lockTime = computed(() => useLockscreen.lockTime);
const language = languageStore().language


const route = useRoute();
const router = useRouter();
const locale = ref(zhCn);
if (language == 'zh') {
    locale.value = zhCn;
}
if (language == 'zhtw') {
    locale.value = zhTw;
}
if (language == 'en') {
    locale.value = en;
}
if (language == 'th') {
    locale.value = th;
}
const state = reactive({});
// let timer;
// const timekeeping = () => {
//     clearInterval(timer);
//     if (route.name == 'login' || isLock.value) return;
//     // 设置不锁屏
//     useLockscreen.setLock(false);
//     // 重置锁屏时间
//     useLockscreen.setLockTime();
//     timer = setInterval(() => {
//         // 锁屏倒计时递减
//         useLockscreen.setLockTime(lockTime.value - 1);
//         if (lockTime.value <= 0) {
//             // 设置锁屏
//             useLockscreen.setLock(true);
//             router.push('/lockscreen')
//             return clearInterval(timer);
//         }
//     }, 1000);
// };

onMounted(() => {
    if (userInfo) {
        IndexApi.base(true)
            .then(res => {
                languageStore().setLanguageList(res.data.language)
                const data = {}
                res.data.language.map((item, index) => {
                    data[(index + 1).toString()] = ''
                })
                languageStore().setLanguageData(data)
                //刷新
                let language = JSON.parse(localStorage.getItem("Language"));
                if (!language) {
                    location.reload();
                }
                //判断默认语言
                if (language && language.language == '' && language.languageList[0]?.name) {
                    languageStore().setLanguage(language.languageList[0]?.name)
                }
                /*获取基础配置*/
                const dataInfo = {
                    data: {
                        app_id: userInfo.AppID,
                        shop_name: res.data.settings.shop_name,
                        shop_supplier_id: userInfo.shop_supplier_id,
                        supplier_name: userInfo.supplier_name,
                        token: token,
                        user_name: userInfo.userName,
                        user_type: userInfo.user_type,
                        version: userInfo.version,
                        logoUrl: res.data.settings.shop_bg_img,
                        currency: currency
                    },
                }
                afterLogin(dataInfo);
                let auth = getSessionStorage('authlist');
                let authlist = {}
                auth = getStorage(menu);
                createdAuth(auth, authlist);
                setSessionStorage('authlist', authlist);
                auth = authlist;
            })
            .catch(error => {

            });
    }
    else{
        IndexApi.lang(true)
            .then(res => {
                languageStore().setLanguageList(res.data.language)
                const data = {}
                res.data.language.map((item, index) => {
                    data[(index + 1).toString()] = ''
                })
                languageStore().setLanguageData(data)
                //刷新
                let language = JSON.parse(localStorage.getItem("Language"));
                if (!language) {
                    location.reload();
                }
                //判断默认语言
                if (language && language.language == '' && language.languageList[0]?.name) {
                    languageStore().setLanguage(language.languageList[0]?.name)
                }
            })
            .catch(error => {

            });
    }



});

onUnmounted(() => {

});

</script>

<style lang="scss">
@import '@/assets/font/iconfont.css';
@import '@/assets/font/myIcon.css';
@import '@/styles/diy.scss';

* {
    margin: 0;
    padding: 0;
}

.common-level-rail {
    text-align: right;

    &.flex {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0;
    }
}

.common-seach-wrap {
    &.flex {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0;
    }
}
</style>