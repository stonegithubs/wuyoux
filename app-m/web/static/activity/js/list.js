try {
    var scriptArgs = document.getElementById('base').getAttribute('data');
    scriptArgs = JSON.parse(scriptArgs);//json字符串转对象
    var requestUrl = scriptArgs.requestUrl;
    var requestType = scriptArgs.requestType;
    var requestData = scriptArgs.requestData;
    var requestMaxNum = scriptArgs.requestMaxNum;
    var requestPageSize = scriptArgs.requestPageSize;
    var errorWord = "暂无活动,敬请期待";

    var myApp = new Framework7();
    // 初始化 app
    var $$ = Dom7;
    var script_data = {
        tableRow: '.page-list ul li',
        requestUrl: requestUrl,
        requestData: requestData,
        requestType: requestType,
        requestMaxNum: requestMaxNum,
        requestPageSize: requestPageSize,
        successFun: "successFun",
        endLine:"<div class='page-tail'><p class='page-tail-wrap'><span class='page-tail-title'>我是有底线的</span></p></div>"
    };
    //存储活动列表分页数据到本地缓存
    var pageKey = "activity_page_key";
    localStorage[pageKey] = JSON.stringify(script_data);
    loadScript("/static/unit/js/page.js", function () {
        console.log("加载分页插件成功");
    }, "page", JSON.stringify({key:pageKey}))

    function successFun(code, message, data) {
        if(code == 0){
            if(data.curr == 1)  {
                $(".page-list ul").html("");
            }
           console.log(data);
            //请求成功
            list = data.list;
            if(list)
            {
                $.each(list,function (key,value) {
                    var pageHtml = "<li>" +
                        "<div  class='date-time'>"+value.create_time+"</div>\n"+
                        "               <div class='card demo-card-header-pic' onclick=detail('"+value.url+"') >\n" +
                        "                   <div valign='bottom' class='card-header color-white no-border'>\n" +
                        "                       <img src='"+value.pic+"'  width='100%'>\n" +
                        "                   </div>\n" +
                        "                   <div class='card-content'>\n" +
                        "                        <div class='card-content-inner'>\n" +
                        "                            <p>"+value.title+"</p>\n" +
                        "                            <p>"+value.brief+"</p>\n" +
                        "                            <p class='color-gray'>查看详细 ></p>\n" +
                        "                        </div>\n" +
                        "                   </div>\n" +
                        "               </div>" +
                        "           </li>";
                    $(".page-list ul").append(pageHtml);
                })
            }else{
                if(data.curr == 1){
                    $(".page-list ul").html("<div class='error' style='text-align:center'><p class='img'><img src='/static/404/img/error.png' width='70%'/></p><p class='title'>"+errorWord+"</p></div>");
                }else{
                    $(".infinite-scroll-preloader").show();
                    $(".infinite-scroll-preloader").html("<div class='page-tail'><p class='page-tail-wrap'><span class='page-tail-title'>我是有底线的</span></p></div>");
                }
            }
        }else{
            //请求失败
        }
    }

    function detail(url) {
        self.location = url;
    }
} catch (e) {
    //访问错误，跳转错误页面
    console.log("参数缺失或者程序错误");
}
