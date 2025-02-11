import{w as e,x as l,h as a,n as o,p as t,q as d,m,o as r,g as s}from"./element-plus-bf694ad1.js";import{u as i}from"./index-7f22f02f.js";import{_ as n}from"./_plugin-vue_export-helper-1b428a4d.js";import{o as u,c as p,a as _,W as f,S as c,R as b,O as g,V as y,X as V,P as h,a7 as j}from"./@vue-b57a05a6.js";import"./lodash-es-234e1c00.js";import"./async-validator-cf877c1f.js";import"./@vueuse-793cab0b.js";import"./@element-plus-ce7ae957.js";import"./dayjs-33a066dd.js";import"./call-bind-912d4e9d.js";import"./get-intrinsic-878e88ff.js";import"./has-symbols-456daba2.js";import"./has-proto-4a87f140.js";import"./function-bind-afbcd6f2.js";import"./hasown-c3b72c9b.js";import"./set-function-length-a0a50b12.js";import"./define-data-property-da2cc9a9.js";import"./has-property-descriptors-2aeb73fe.js";import"./gopd-15a2da42.js";import"./@popperjs-b78c3215.js";import"./normalize-wheel-es-3222b0a2.js";import"./@ctrl-91de2ec7.js";import"./vue-router-24c2a4a1.js";import"./pinia-6eed225f.js";import"./axios-b48e0a85.js";import"./qs-49804a56.js";import"./side-channel-7f79a019.js";import"./object-inspect-9ade9731.js";import"./pinia-plugin-persistedstate-35ef556e.js";import"./vue-ueditor-wrap-5c153e91.js";import"./vue-i18n-8b8412df.js";import"./@intlify-658c8624.js";const{currency:$}=i(),v={class:"buy-set-content"},k={class:"common-form mt50"},x={class:"gray9"},w={class:"common-form mt50"},U={key:0,class:"gray9"},S={key:1,class:"gray9"},q={class:"percent-w50"},C={class:"d-s-c"},z={class:"ml10"},L={key:4,class:"common-form mt50"},P={class:"gray9"},T={key:0,style:{"padding-left":"10px"}},G={key:1,style:{"padding-left":"10px"}},M={key:2,style:{"padding-left":"10px"}};const I=n({data:()=>({unit:"%",grade_unit:"%",currency:$,minPrice:0}),created(){"20"==this.form.model.alone_grade_type&&(this.grade_unit="元"),"20"==this.form.model.agent_money_type&&(this.unit="元")},inject:["form"],watch:{form:{handler(e){let l=[];e.model.sku.map((e=>{l.push(e.product_price)})),this.minPrice=Math.min(...l)},immediate:!0,deep:!0}},methods:{changeMoneyType:function(e){this.unit="10"==e?"%":"元"},changeGradeType:function(e){this.form.gradeList.map(((e,l)=>{this.form.gradeList[l].product_equity=null})),this.grade_unit="10"==e?"%":"元"}}},[["render",function(i,n,$,I,B,J){const O=e,R=l,W=a,X=o,A=t,D=d,E=m,F=r,H=s;return u(),p("div",v,[_("div",k,f(i.$t("其他设置")),1),40!=J.form.model.product_status?(u(),c(W,{key:0,label:i.$t("商品状态："),rules:[{required:!0,message:i.$t("选择商品状态")}],prop:"model.product_status"},{default:b((()=>[g(R,{modelValue:J.form.model.product_status,"onUpdate:modelValue":n[0]||(n[0]=e=>J.form.model.product_status=e)},{default:b((()=>[g(O,{label:10},{default:b((()=>[y(f(i.$t("上架")),1)])),_:1}),g(O,{label:20},{default:b((()=>[y(f(i.$t("下架")),1)])),_:1})])),_:1},8,["modelValue"])])),_:1},8,["label","rules"])):V("",!0),g(W,{label:i.$t("显示在平板端："),rules:[{required:!0,message:i.$t("选择是否显示")}],prop:"model.product_status"},{default:b((()=>[g(R,{modelValue:J.form.model.is_show_tablet,"onUpdate:modelValue":n[1]||(n[1]=e=>J.form.model.is_show_tablet=e)},{default:b((()=>[g(O,{label:1},{default:b((()=>[y(f(i.$t("显示")),1)])),_:1}),g(O,{label:2},{default:b((()=>[y(f(i.$t("不显示")),1)])),_:1})])),_:1},8,["modelValue"])])),_:1},8,["label","rules"]),g(W,{label:i.$t("需要送厨："),rules:[{required:!0,message:i.$t(" ")}],prop:"model.product_status"},{default:b((()=>[g(R,{modelValue:J.form.model.is_show_kitchen,"onUpdate:modelValue":n[2]||(n[2]=e=>J.form.model.is_show_kitchen=e)},{default:b((()=>[g(O,{label:1},{default:b((()=>[y(f(i.$t("是")),1)])),_:1}),g(O,{label:2},{default:b((()=>[y(f(i.$t("否")),1)])),_:1})])),_:1},8,["modelValue"])])),_:1},8,["label","rules"]),g(W,{label:i.$t("商品排序："),rules:[{required:!0,message:i.$t("接近0，排序等级越高")}],prop:"model.product_sort"},{default:b((()=>[g(X,{controls:!1,min:0,max:999,placeholder:i.$t("接近0，排序等级越高"),modelValue:J.form.model.product_sort,"onUpdate:modelValue":n[3]||(n[3]=e=>J.form.model.product_sort=e),class:"max-w460"},null,8,["placeholder","modelValue"])])),_:1},8,["label","rules"]),g(W,{label:i.$t("限购数量："),rules:[{required:!0,message:i.$t("请输入限购数量")}],prop:"model.limit_num"},{default:b((()=>[g(X,{controls:!1,min:0,max:999,modelValue:J.form.model.limit_num,"onUpdate:modelValue":n[4]||(n[4]=e=>J.form.model.limit_num=e),class:"max-w460"},null,8,["modelValue"]),_("div",x,f(i.$t("每单/每桌购买的最大数量，0为不限购")),1)])),_:1},8,["label","rules"]),g(W,{label:i.$t("打印标签："),prop:"model.label_id"},{default:b((()=>[g(D,{modelValue:J.form.model.label_id,"onUpdate:modelValue":n[5]||(n[5]=e=>J.form.model.label_id=e),clearable:"",class:"max-w460",size:"default"},{default:b((()=>[g(A,{value:0,label:i.$t("无")},null,8,["label"]),(u(!0),p(h,null,j(J.form.labelList,(e=>(u(),c(A,{key:e.label_id,value:e.label_id,label:e.label_name_text},null,8,["value","label"])))),128))])),_:1},8,["modelValue"])])),_:1},8,["label"]),_("div",w,f(i.$t("会员折扣设置")),1),g(W,{label:i.$t("是否开启会员折扣：")},{default:b((()=>[g(R,{modelValue:J.form.model.is_enable_grade,"onUpdate:modelValue":n[6]||(n[6]=e=>J.form.model.is_enable_grade=e)},{default:b((()=>[g(O,{label:1},{default:b((()=>[y(f(i.$t("开启")),1)])),_:1}),g(O,{label:0},{default:b((()=>[y(f(i.$t("关闭")),1)])),_:1})])),_:1},8,["modelValue"])])),_:1},8,["label"]),1==J.form.model.is_enable_grade?(u(),c(W,{key:1,label:i.$t("会员折扣设置：")},{default:b((()=>[g(R,{modelValue:J.form.model.is_alone_grade,"onUpdate:modelValue":n[7]||(n[7]=e=>J.form.model.is_alone_grade=e)},{default:b((()=>[g(O,{label:0},{default:b((()=>[y(f(i.$t("默认折扣")),1)])),_:1})])),_:1},8,["modelValue"]),0==J.form.model.is_alone_grade?(u(),p("div",U,f(i.$t("默认折扣：默认为用户所属会员等级的折扣率")),1)):V("",!0),1==J.form.model.is_alone_grade?(u(),p("div",S,f(i.$t("仅需支付：用户购买此商品仅需支付的金额或比例")),1)):V("",!0)])),_:1},8,["label"])):V("",!0),1==J.form.model.is_alone_grade&&1==J.form.model.is_enable_grade?(u(),c(W,{key:2,label:i.$t("折扣佣金类型：")},{default:b((()=>[g(R,{modelValue:J.form.model.alone_grade_type,"onUpdate:modelValue":n[8]||(n[8]=e=>J.form.model.alone_grade_type=e),onChange:J.changeGradeType},{default:b((()=>[g(O,{label:10},{default:b((()=>[y(f(i.$t("百分比")),1)])),_:1}),g(O,{label:20},{default:b((()=>[y(f(i.$t("固定金额")),1)])),_:1})])),_:1},8,["modelValue","onChange"])])),_:1},8,["label"])):V("",!0),1==J.form.model.is_alone_grade&&1==J.form.model.is_enable_grade?(u(),c(W,{key:3,label:""},{default:b((()=>[_("div",q,[g(F,{data:J.form.gradeList,border:"",size:""},{default:b((()=>[g(E,{prop:"name",label:i.$t("会员等级")},null,8,["label"]),g(E,{prop:"name",label:i.$t("折扣")},{default:b((e=>[_("div",C,[g(W,{class:"product-equity",rules:[{validator:()=>!!e.row.product_equity,message:i.$t("请输入折扣")}],prop:"model.image"},{default:b((()=>[g(X,{modelValue:e.row.product_equity,"onUpdate:modelValue":l=>e.row.product_equity=l,min:10==J.form.model.alone_grade_type?1:0,max:10==J.form.model.alone_grade_type?100:B.minPrice,controls:!1,placeholder:i.$t("请输入折扣")},null,8,["modelValue","onUpdate:modelValue","min","max","placeholder"]),_("span",z,f(10==J.form.model.alone_grade_type?B.grade_unit:B.currency.unit),1)])),_:2},1032,["rules"])])])),_:1},8,["label"])])),_:1},8,["data"])])])),_:1})):V("",!0),1==J.form.basicSetting.is_open?(u(),p("div",L,"分销设置")):V("",!0),1==J.form.basicSetting.is_open?(u(),c(W,{key:5,label:"是否开启分销："},{default:b((()=>[g(R,{modelValue:J.form.model.is_agent,"onUpdate:modelValue":n[9]||(n[9]=e=>J.form.model.is_agent=e)},{default:b((()=>[g(O,{label:1},{default:b((()=>[y("开启")])),_:1}),g(O,{label:0},{default:b((()=>[y("关闭")])),_:1})])),_:1},8,["modelValue"])])),_:1})):V("",!0),1===J.form.model.is_agent?(u(),p(h,{key:6},[1==J.form.basicSetting.is_open?(u(),c(W,{key:0,label:"分销规则："},{default:b((()=>[g(R,{modelValue:J.form.model.is_ind_agent,"onUpdate:modelValue":n[10]||(n[10]=e=>J.form.model.is_ind_agent=e)},{default:b((()=>[g(O,{label:0},{default:b((()=>[y("平台规则")])),_:1}),g(O,{label:1},{default:b((()=>[y("单独规则")])),_:1})])),_:1},8,["modelValue"]),_("div",P,[y("平台规则：层级("+f(J.form.basicSetting.level)+"级) ",1),J.form.basicSetting.level>=1?(u(),p("span",T,"1级佣金("+f(J.form.agentSetting.first_money)+"%)",1)):V("",!0),J.form.basicSetting.level>=2?(u(),p("span",G,"2级佣金("+f(J.form.agentSetting.second_money)+"%)",1)):V("",!0),J.form.basicSetting.level>=3?(u(),p("span",M,"3级佣金("+f(J.form.agentSetting.third_money)+"%)",1)):V("",!0)])])),_:1})):V("",!0),1===J.form.model.is_ind_agent&&1==J.form.basicSetting.is_open?(u(),p(h,{key:1},[g(W,{label:"分销佣金类型："},{default:b((()=>[g(R,{modelValue:J.form.model.agent_money_type,"onUpdate:modelValue":n[11]||(n[11]=e=>J.form.model.agent_money_type=e),onChange:J.changeMoneyType},{default:b((()=>[g(O,{label:10},{default:b((()=>[y("百分比")])),_:1}),g(O,{label:20},{default:b((()=>[y("固定金额")])),_:1})])),_:1},8,["modelValue","onChange"])])),_:1}),g(W,{label:"单独分销设置："},{default:b((()=>[g(H,{type:"number",min:"0",modelValue:J.form.model.first_money,"onUpdate:modelValue":n[12]||(n[12]=e=>J.form.model.first_money=e),class:"max-w460"},{prepend:b((()=>[y(" 一级佣金： ")])),append:b((()=>[y(f(B.unit),1)])),_:1},8,["modelValue"])])),_:1}),J.form.basicSetting.level>=2?(u(),c(W,{key:0},{default:b((()=>[g(H,{type:"number",min:"0",modelValue:J.form.model.second_money,"onUpdate:modelValue":n[13]||(n[13]=e=>J.form.model.second_money=e),class:"max-w460"},{prepend:b((()=>[y(" 二级佣金： ")])),append:b((()=>[y(f(B.unit),1)])),_:1},8,["modelValue"])])),_:1})):V("",!0),J.form.basicSetting.level>=3?(u(),c(W,{key:1},{default:b((()=>[g(H,{type:"number",min:"0",modelValue:J.form.model.third_money,"onUpdate:modelValue":n[14]||(n[14]=e=>J.form.model.third_money=e),class:"max-w460"},{prepend:b((()=>[y(" 三级佣金： ")])),append:b((()=>[y(f(B.unit),1)])),_:1},8,["modelValue"])])),_:1})):V("",!0)],64)):V("",!0)],64)):V("",!0)])}],["__scopeId","data-v-22eb24bc"]]);export{I as default};
