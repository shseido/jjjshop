import {
	createApp
} from 'vue'
import router from "./router";
import "../static/css/app.css";
import "../static/css/common.css";

import piniaPluginPersistedstate from "pinia-plugin-persistedstate";
import 'virtual:svg-icons-register'

import {
	createPinia
} from 'pinia'
const pinia = createPinia();
pinia.use(piniaPluginPersistedstate)
import {
	setupRouter
} from "@/router";
import App from './App.vue'
import * as ElementPlusIconsVue from '@element-plus/icons-vue'
import {
	loadDirectives
} from "@/directive"
import VueUeditorWrap from 'vue-ueditor-wrap'
import filters from '@/filters/index.js' 
import I18n from "./lang/index";
import { message } from './utils/message.js'

const app = createApp(App);
/** 加载自定义指令 */
loadDirectives(app)
for (const [key, component] of Object.entries(ElementPlusIconsVue)) {
	app.component(key, component)
}
window.$t = I18n.global.t
app.use(VueUeditorWrap)
app.use(I18n)
app.use(pinia)
app.use(router)
app.mount('#app')
app.config.globalProperties.$filter = filters;
app.config.globalProperties.$ElMessage = message;
setupRouter(app);