"use strict";(self["webpackChunk"]=self["webpackChunk"]||[]).push([[684],{3684:function(e,t,l){l.r(t),l.d(t,{default:function(){return i}});var o=l(9199);const a={class:"grid grid-cols-12 gap-1"},n={class:"text-xs italic font-semibold border-b border-dotted border-gray-100 text-blue-900 my-2"};function r(e,t,l,r,d,c){const s=(0,o.resolveComponent)("FormKit"),i=(0,o.resolveComponent)("edit-render");return(0,o.openBlock)(),(0,o.createBlock)(i,{path_param:e.$route.meta.path,title:"Chart of Account",model:e.model},{default:(0,o.withCtx)((()=>[(0,o.createElementVNode)("div",a,[((0,o.openBlock)(!0),(0,o.createElementBlock)(o.Fragment,null,(0,o.renderList)(e.layout,((t,l)=>((0,o.openBlock)(),(0,o.createElementBlock)("div",{key:l,class:(0,o.normalizeClass)(t.class)},[(0,o.createElementVNode)("h4",n,(0,o.toDisplayString)(t.label),1),((0,o.openBlock)(!0),(0,o.createElementBlock)(o.Fragment,null,(0,o.renderList)(t.fields,((t,l)=>((0,o.openBlock)(),(0,o.createBlock)(s,{key:l,modelValue:e.model[t.name],"onUpdate:modelValue":l=>e.model[t.name]=l,label:t.label,id:t.name,type:t.html},null,8,["modelValue","onUpdate:modelValue","label","id","type"])))),128))],2)))),128))])])),_:1},8,["path_param","model"])}var d={created(){var e=this.$route.meta.path;window.axios.get("fetch_layout/"+e[0]+"/"+e[1]+"/create").then((e=>{this.layout=e.data.layout,e.data.fields.forEach((e=>{this.model[e]=""})),this.layout_fetched=!0})).catch((e=>{console.log(e)}))},data:function(){return{id:"",layout_fetched:!1,model:{},layout:{}}}},c=l(89);const s=(0,c.Z)(d,[["render",r]]);var i=s}}]);
//# sourceMappingURL=684.js.map