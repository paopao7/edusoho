webpackJsonp(["app/js/classroom-manage/course-manage/index"],{"1dc821166b9aaaf0b4d1":function(e,s,a){"use strict";var t=a("8f840897d9471c8c1fbd"),o=function(e){return e&&e.__esModule?e:{default:e}}(t);$(".js-course-list-group").on("click",".js-delete-btn",function(){var e=this;cd.confirm({title:Translator.trans("classroom.manage.delete_course_hint_title"),content:Translator.trans("classroom.manage.delete_course_hint"),okText:Translator.trans("site.confirm"),cancelText:Translator.trans("site.cancel")}).on("ok",function(){$.post($(e).data("url"),function(e){e.success?(cd.message({type:"success",message:Translator.trans("classroom.manage.delete_course_success_hint")}),window.location.reload()):cd.message({type:"danger",message:Translator.trans("classroom.manage.delete_course_fail_hint")+":"+e.message})})})}),(0,o.default)({element:"#course-list-group",itemSelector:"li",ajax:!1},function(e){$.post($("#course-list-group").data("sortUrl"),$("#courses-form").serialize(),function(e){})})}},["1dc821166b9aaaf0b4d1"]);