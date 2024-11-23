const m=["transitionend","webkitTransitionEnd","oTransitionEnd"],p=["transition","MozTransition","webkitTransition","WebkitTransition","OTransition"],y=`
.layout-menu-fixed .layout-navbar-full .layout-menu,
.layout-page {
  padding-top: {navbarHeight}px !important;
}
.content-wrapper {
  padding-bottom: {footerHeight}px !important;
}`;function l(e){throw new Error(`Parameter required${e?`: \`${e}\``:""}`)}const c={ROOT_EL:typeof window<"u"?document.documentElement:null,LAYOUT_BREAKPOINT:1200,RESIZE_DELAY:200,menuPsScroll:null,mainMenu:null,_curStyle:null,_styleEl:null,_resizeTimeout:null,_resizeCallback:null,_transitionCallback:null,_transitionCallbackTimeout:null,_listeners:[],_initialized:!1,_autoUpdate:!1,_lastWindowHeight:0,_scrollToActive(e=!1,t=500){const i=this.getLayoutMenu();if(!i)return;let s=i.querySelector("li.menu-item.active:not(.open)");if(s){const n=(r,h,d,f)=>(r/=f/2,r<1?d/2*r*r+h:(r-=1,-d/2*(r*(r-2)-1)+h)),o=this.getLayoutMenu().querySelector(".menu-inner");if(typeof s=="string"&&(s=document.querySelector(s)),typeof s!="number"&&(s=s.getBoundingClientRect().top+o.scrollTop),s<parseInt(o.clientHeight*2/3,10))return;const a=o.scrollTop,u=s-a-parseInt(o.clientHeight/2,10),_=+new Date;if(e===!0){const r=()=>{const d=+new Date-_,f=n(d,a,u,t);o.scrollTop=f,d<t?requestAnimationFrame(r):o.scrollTop=u};r()}else o.scrollTop=u}},_addClass(e,t=this.ROOT_EL){t&&t.length!==void 0?t.forEach(i=>{i&&e.split(" ").forEach(s=>i.classList.add(s))}):t&&e.split(" ").forEach(i=>t.classList.add(i))},_removeClass(e,t=this.ROOT_EL){t&&t.length!==void 0?t.forEach(i=>{i&&e.split(" ").forEach(s=>i.classList.remove(s))}):t&&e.split(" ").forEach(i=>t.classList.remove(i))},_toggleClass(e=this.ROOT_EL,t,i){e.classList.contains(t)?e.classList.replace(t,i):e.classList.replace(i,t)},_hasClass(e,t=this.ROOT_EL){let i=!1;return e.split(" ").forEach(s=>{t.classList.contains(s)&&(i=!0)}),i},_findParent(e,t){if(e&&e.tagName.toUpperCase()==="BODY"||e.tagName.toUpperCase()==="HTML")return null;for(e=e.parentNode;e&&e.tagName.toUpperCase()!=="BODY"&&!e.classList.contains(t);)e=e.parentNode;return e=e&&e.tagName.toUpperCase()!=="BODY"?e:null,e},_triggerWindowEvent(e){if(!(typeof window>"u"))if(document.createEvent){let t;typeof Event=="function"?t=new Event(e):(t=document.createEvent("Event"),t.initEvent(e,!1,!0)),window.dispatchEvent(t)}else window.fireEvent(`on${e}`,document.createEventObject())},_triggerEvent(e){this._triggerWindowEvent(`layout${e}`),this._listeners.filter(t=>t.event===e).forEach(t=>t.callback.call(null))},_updateInlineStyle(e=0,t=0){this._styleEl||(this._styleEl=document.createElement("style"),this._styleEl.type="text/css",document.head.appendChild(this._styleEl));const i=y.replace(/\{navbarHeight\}/gi,e).replace(/\{footerHeight\}/gi,t);this._curStyle!==i&&(this._curStyle=i,this._styleEl.textContent=i)},_removeInlineStyle(){this._styleEl&&document.head.removeChild(this._styleEl),this._styleEl=null,this._curStyle=null},_redrawLayoutMenu(){const e=this.getLayoutMenu();if(e&&e.querySelector(".menu")){const t=e.querySelector(".menu-inner"),{scrollTop:i}=t,s=document.documentElement.scrollTop;return e.style.display="none",e.style.display="",t.scrollTop=i,document.documentElement.scrollTop=s,!0}return!1},_supportsTransitionEnd(){if(window.QUnit)return!1;const e=document.body||document.documentElement;if(!e)return!1;let t=!1;return p.forEach(i=>{typeof e.style[i]<"u"&&(t=!0)}),t},_getNavbarHeight(){const e=this.getLayoutNavbar();if(!e)return 0;if(!this.isSmallScreen())return e.getBoundingClientRect().height;const t=e.cloneNode(!0);t.id=null,t.style.visibility="hidden",t.style.position="absolute",Array.prototype.slice.call(t.querySelectorAll(".collapse.show")).forEach(s=>this._removeClass("show",s)),e.parentNode.insertBefore(t,e);const i=t.getBoundingClientRect().height;return t.parentNode.removeChild(t),i},_getFooterHeight(){const e=this.getLayoutFooter();return e?e.getBoundingClientRect().height:0},_getAnimationDuration(e){const t=window.getComputedStyle(e).transitionDuration;return parseFloat(t)*(t.indexOf("ms")!==-1?1:1e3)},_setMenuHoverState(e){this[e?"_addClass":"_removeClass"]("layout-menu-hover")},_setCollapsed(e){this.isSmallScreen()&&(e?this._removeClass("layout-menu-expanded"):setTimeout(()=>{this._addClass("layout-menu-expanded")},this._redrawLayoutMenu()?5:0))},_bindLayoutAnimationEndEvent(e,t){const i=this.getMenu(),s=i?this._getAnimationDuration(i)+50:0;if(!s){e.call(this),t.call(this);return}this._transitionCallback=n=>{n.target===i&&(this._unbindLayoutAnimationEndEvent(),t.call(this))},m.forEach(n=>{i.addEventListener(n,this._transitionCallback,!1)}),e.call(this),this._transitionCallbackTimeout=setTimeout(()=>{this._transitionCallback.call(this,{target:i})},s)},_unbindLayoutAnimationEndEvent(){const e=this.getMenu();this._transitionCallbackTimeout&&(clearTimeout(this._transitionCallbackTimeout),this._transitionCallbackTimeout=null),e&&this._transitionCallback&&m.forEach(t=>{e.removeEventListener(t,this._transitionCallback,!1)}),this._transitionCallback&&(this._transitionCallback=null)},_bindWindowResizeEvent(){this._unbindWindowResizeEvent();const e=()=>{this._resizeTimeout&&(clearTimeout(this._resizeTimeout),this._resizeTimeout=null),this._triggerEvent("resize")};this._resizeCallback=()=>{this._resizeTimeout&&clearTimeout(this._resizeTimeout),this._resizeTimeout=setTimeout(e,this.RESIZE_DELAY)},window.addEventListener("resize",this._resizeCallback,!1)},_unbindWindowResizeEvent(){this._resizeTimeout&&(clearTimeout(this._resizeTimeout),this._resizeTimeout=null),this._resizeCallback&&(window.removeEventListener("resize",this._resizeCallback,!1),this._resizeCallback=null)},_bindMenuMouseEvents(){if(this._menuMouseEnter&&this._menuMouseLeave&&this._windowTouchStart)return;const e=this.getLayoutMenu();if(!e)return this._unbindMenuMouseEvents();this._menuMouseEnter||(this._menuMouseEnter=()=>this.isSmallScreen()||this._hasClass("layout-transitioning")?this._setMenuHoverState(!1):this._setMenuHoverState(!1),e.addEventListener("mouseenter",this._menuMouseEnter,!1),e.addEventListener("touchstart",this._menuMouseEnter,!1)),this._menuMouseLeave||(this._menuMouseLeave=()=>{this._setMenuHoverState(!1)},e.addEventListener("mouseleave",this._menuMouseLeave,!1)),this._windowTouchStart||(this._windowTouchStart=t=>{(!t||!t.target||!this._findParent(t.target,".layout-menu"))&&this._setMenuHoverState(!1)},window.addEventListener("touchstart",this._windowTouchStart,!0))},_unbindMenuMouseEvents(){if(!this._menuMouseEnter&&!this._menuMouseLeave&&!this._windowTouchStart)return;const e=this.getLayoutMenu();this._menuMouseEnter&&(e&&(e.removeEventListener("mouseenter",this._menuMouseEnter,!1),e.removeEventListener("touchstart",this._menuMouseEnter,!1)),this._menuMouseEnter=null),this._menuMouseLeave&&(e&&e.removeEventListener("mouseleave",this._menuMouseLeave,!1),this._menuMouseLeave=null),this._windowTouchStart&&(e&&window.addEventListener("touchstart",this._windowTouchStart,!0),this._windowTouchStart=null),this._setMenuHoverState(!1)},scrollToActive(e=!1){this._scrollToActive(e)},setCollapsed(e=l("collapsed"),t=!0){this.getLayoutMenu()&&(this._unbindLayoutAnimationEndEvent(),t&&this._supportsTransitionEnd()?(this._addClass("layout-transitioning"),e&&this._setMenuHoverState(!1),this._bindLayoutAnimationEndEvent(()=>{this.isSmallScreen&&this._setCollapsed(e)},()=>{this._removeClass("layout-transitioning"),this._triggerWindowEvent("resize"),this._triggerEvent("toggle"),this._setMenuHoverState(!1)})):(this._addClass("layout-no-transition"),e&&this._setMenuHoverState(!1),this._setCollapsed(e),setTimeout(()=>{this._removeClass("layout-no-transition"),this._triggerWindowEvent("resize"),this._triggerEvent("toggle"),this._setMenuHoverState(!1)},1)))},toggleCollapsed(e=!0){this.setCollapsed(!this.isCollapsed(),e)},setPosition(e=l("fixed"),t=l("offcanvas")){this._removeClass("layout-menu-offcanvas layout-menu-fixed layout-menu-fixed-offcanvas"),!e&&t?this._addClass("layout-menu-offcanvas"):e&&!t?(this._addClass("layout-menu-fixed"),this._redrawLayoutMenu()):e&&t&&(this._addClass("layout-menu-fixed-offcanvas"),this._redrawLayoutMenu()),this.update()},getLayoutMenu(){return document.querySelector(".layout-menu")},getMenu(){const e=this.getLayoutMenu();return e?this._hasClass("menu",e)?e:e.querySelector(".menu"):null},getLayoutNavbar(){return document.querySelector(".layout-navbar")},getLayoutFooter(){return document.querySelector(".content-footer")},update(){(this.getLayoutNavbar()&&(!this.isSmallScreen()&&this.isLayoutNavbarFull()&&this.isFixed()||this.isNavbarFixed())||this.getLayoutFooter()&&this.isFooterFixed())&&this._updateInlineStyle(this._getNavbarHeight(),this._getFooterHeight()),this._bindMenuMouseEvents()},setAutoUpdate(e=l("enable")){e&&!this._autoUpdate?(this.on("resize.Helpers:autoUpdate",()=>this.update()),this._autoUpdate=!0):!e&&this._autoUpdate&&(this.off("resize.Helpers:autoUpdate"),this._autoUpdate=!1)},isRtl(){return document.querySelector("body").getAttribute("dir")==="rtl"||document.querySelector("html").getAttribute("dir")==="rtl"},isMobileDevice(){return typeof window.orientation<"u"||navigator.userAgent.indexOf("IEMobile")!==-1},isSmallScreen(){return(window.innerWidth||document.documentElement.clientWidth||document.body.clientWidth)<this.LAYOUT_BREAKPOINT},isLayoutNavbarFull(){return!!document.querySelector(".layout-wrapper.layout-navbar-full")},isCollapsed(){return this.isSmallScreen()?!this._hasClass("layout-menu-expanded"):this._hasClass("layout-menu-collapsed")},isFixed(){return this._hasClass("layout-menu-fixed layout-menu-fixed-offcanvas")},isNavbarFixed(){return this._hasClass("layout-navbar-fixed")||!this.isSmallScreen()&&this.isFixed()&&this.isLayoutNavbarFull()},isFooterFixed(){return this._hasClass("layout-footer-fixed")},isLightStyle(){return document.documentElement.classList.contains("light-style")},on(e=l("event"),t=l("callback")){const[i]=e.split(".");let[,...s]=e.split(".");s=s.join(".")||null,this._listeners.push({event:i,namespace:s,callback:t})},off(e=l("event")){const[t]=e.split(".");let[,...i]=e.split(".");i=i.join(".")||null,this._listeners.filter(s=>s.event===t&&s.namespace===i).forEach(s=>this._listeners.splice(this._listeners.indexOf(s),1))},init(){this._initialized||(this._initialized=!0,this._updateInlineStyle(0),this._bindWindowResizeEvent(),this.off("init._Helpers"),this.on("init._Helpers",()=>{this.off("resize._Helpers:redrawMenu"),this.on("resize._Helpers:redrawMenu",()=>{this.isSmallScreen()&&!this.isCollapsed()&&this._redrawLayoutMenu()}),typeof document.documentMode=="number"&&document.documentMode<11&&(this.off("resize._Helpers:ie10RepaintBody"),this.on("resize._Helpers:ie10RepaintBody",()=>{if(this.isFixed())return;const{scrollTop:e}=document.documentElement;document.body.style.display="none",document.body.style.display="block",document.documentElement.scrollTop=e}))}),this._triggerEvent("init"))},destroy(){this._initialized&&(this._initialized=!1,this._removeClass("layout-transitioning"),this._removeInlineStyle(),this._unbindLayoutAnimationEndEvent(),this._unbindWindowResizeEvent(),this._unbindMenuMouseEvents(),this.setAutoUpdate(!1),this.off("init._Helpers"),this._listeners.filter(e=>e.event!=="init").forEach(e=>this._listeners.splice(this._listeners.indexOf(e),1)))},initPasswordToggle(){const e=document.querySelectorAll(".form-password-toggle i");typeof e<"u"&&e!==null&&e.forEach(t=>{t.addEventListener("click",i=>{i.preventDefault();const s=t.closest(".form-password-toggle"),n=s.querySelector("i"),o=s.querySelector("input");o.getAttribute("type")==="text"?(o.setAttribute("type","password"),n.classList.replace("bx-show","bx-hide")):o.getAttribute("type")==="password"&&(o.setAttribute("type","text"),n.classList.replace("bx-hide","bx-show"))})})},initSpeechToText(){const e=window.SpeechRecognition||window.webkitSpeechRecognition,t=document.querySelectorAll(".speech-to-text");if(e!=null&&typeof t<"u"&&t!==null){const i=new e;document.querySelectorAll(".speech-to-text i").forEach(n=>{let o=!1;n.addEventListener("click",()=>{n.closest(".input-group").querySelector(".form-control").focus(),i.onspeechstart=()=>{o=!0},o===!1&&i.start(),i.onerror=()=>{o=!1},i.onresult=a=>{n.closest(".input-group").querySelector(".form-control").value=a.results[0][0].transcript},i.onspeechend=()=>{o=!1,i.stop()}})})}},ajaxCall(e){return new Promise((t,i)=>{const s=new XMLHttpRequest;s.open("GET",e),s.onload=()=>s.status===200?t(s.response):i(Error(s.statusText)),s.onerror=n=>i(Error(`Network Error: ${n}`)),s.send()})},initSidebarToggle(){document.querySelectorAll('[data-bs-toggle="sidebar"]').forEach(t=>{t.addEventListener("click",()=>{const i=t.getAttribute("data-target"),s=t.getAttribute("data-overlay"),n=document.querySelectorAll(".app-overlay");document.querySelectorAll(i).forEach(a=>{a.classList.toggle("show"),typeof s<"u"&&s!==null&&s!==!1&&typeof n<"u"&&(a.classList.contains("show")?n[0].classList.add("show"):n[0].classList.remove("show"),n[0].addEventListener("click",u=>{u.currentTarget.classList.remove("show"),a.classList.remove("show")}))})})})}};typeof window<"u"&&(c.init(),c.isMobileDevice()&&window.chrome&&document.documentElement.classList.add("layout-menu-100vh"),document.readyState==="complete"?c.update():document.addEventListener("DOMContentLoaded",function e(){c.update(),document.removeEventListener("DOMContentLoaded",e)}));window.Helpers=c;
