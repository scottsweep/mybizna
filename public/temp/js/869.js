"use strict";(self["webpackChunk"]=self["webpackChunk"]||[]).push([[869],{2869:function(e,t,o){o.r(t),o.d(t,{default:function(){return D}});var a=o(9199);const l=e=>((0,a.pushScopeId)("data-v-1e706010"),e=e(),(0,a.popScopeId)(),e),s={class:"authincation h-100"},c={class:"container h-100"},n={class:"row justify-content-center h-100 align-items-center"},r={class:"col-md-6"},d={class:"authincation-content"},i={class:"row no-gutters"},m={class:"col-xl-12"},u={class:"auth-form"},h=l((()=>(0,a.createElementVNode)("div",{class:"text-center mb-3"},[(0,a.createElementVNode)("img",{src:"images/logo.jpg",alt:""})],-1))),p=l((()=>(0,a.createElementVNode)("h4",{class:"text-center mb-4"},"Login to your account",-1))),N={class:"form-group"},V=l((()=>(0,a.createElementVNode)("label",{class:"mb-1"},[(0,a.createElementVNode)("strong",null,"Email")],-1))),E={class:"form-group"},v=l((()=>(0,a.createElementVNode)("label",{class:"mb-1"},[(0,a.createElementVNode)("strong",null,"Password")],-1))),b={class:"form-row d-flex justify-content-between mt-4 mb-2"},g=l((()=>(0,a.createElementVNode)("div",{class:"form-group"},[(0,a.createElementVNode)("div",{class:"custom-control custom-checkbox ml-1"},[(0,a.createElementVNode)("input",{type:"checkbox",class:"custom-control-input",id:"basic_checkbox_1"}),(0,a.createElementVNode)("label",{class:"custom-control-label",for:"basic_checkbox_1"},"Remember my preference")])],-1))),w={class:"form-group"},f=(0,a.createTextVNode)("Forgot Password?"),x={class:"text-center"},k=["loading"],C={class:"new-account mt-5"},_=(0,a.createTextVNode)(" Don't have an account? "),y=l((()=>(0,a.createElementVNode)("br",null,null,-1))),T=(0,a.createTextVNode)("CREATE ACCOUNT");function $(e,t,o,l,$,I){const U=(0,a.resolveComponent)("router-link"),j=(0,a.resolveComponent)("b-button");return(0,a.openBlock)(),(0,a.createElementBlock)("div",s,[(0,a.createElementVNode)("div",c,[(0,a.createElementVNode)("div",n,[(0,a.createElementVNode)("div",r,[(0,a.createElementVNode)("div",d,[(0,a.createElementVNode)("div",i,[(0,a.createElementVNode)("div",m,[(0,a.createElementVNode)("div",u,[h,p,(0,a.createElementVNode)("div",null,[(0,a.createElementVNode)("div",N,[V,(0,a.withDirectives)((0,a.createElementVNode)("input",{type:"email",class:"form-control","onUpdate:modelValue":t[0]||(t[0]=t=>e.model.username=t)},null,512),[[a.vModelText,e.model.username]])]),(0,a.createElementVNode)("div",E,[v,(0,a.withDirectives)((0,a.createElementVNode)("input",{type:"password",class:"form-control","onUpdate:modelValue":t[1]||(t[1]=t=>e.model.password=t)},null,512),[[a.vModelText,e.model.password]])]),(0,a.createElementVNode)("div",b,[g,(0,a.createElementVNode)("div",w,[(0,a.createVNode)(U,{to:"/forgotpassword"},{default:(0,a.withCtx)((()=>[f])),_:1})])]),(0,a.createElementVNode)("div",x,[(0,a.createElementVNode)("button",{type:"submit",class:"btn btn-primary btn-block",onClick:t[2]||(t[2]=(...e)=>I.login&&I.login(...e)),loading:e.loading}," LOGIN ",8,k)])]),(0,a.createElementVNode)("div",C,[(0,a.createElementVNode)("p",null,[_,y,(0,a.createVNode)(j,{variant:"success"},{default:(0,a.withCtx)((()=>[(0,a.createVNode)(U,{to:"/register",class:"text-white"},{default:(0,a.withCtx)((()=>[T])),_:1})])),_:1})])])])])])])])])])])}var I={watch:{"$store.state.auth.token":{immediate:!0,handler(){this.$store.getters["auth/loggedIn"]&&(this.$store.dispatch("auth/getUser",{that:this}),window.is_frontend?this.$router.push("/dashboard"):this.$router.push("/manage/dashboard"))}}},data:()=>({loading:!1,model:{username:"",password:""}}),methods:{login(){let e={username:this.model.username,password:this.model.password,that:this};this.$store.dispatch("auth/authenticate",e)}}},U=o(89);const j=(0,U.Z)(I,[["render",$],["__scopeId","data-v-1e706010"]]);var D=j}}]);
//# sourceMappingURL=869.js.map