/*
* 参数说明
* obj : 动画的节点，本例中是ul
* top : 动画的高度，本例中是-35px;注意向上滚动是负数
* time : 动画的速度，即完成动画所用时间，本例中是500毫秒，即marginTop从0到-35px耗时500毫秒
* function : 回调函数，每次动画完成，marginTop归零，并把此时第一条信息添加到列表最后;
*
*/

function noticeUp(obj,top,time) {
    $(obj).animate({
        marginTop: top
    }, time, function () {
        $(this).css({marginTop:"0"}).find(":first").appendTo(this);
    })
}