// Initialize your app
//var myApp = new Framework7();

// 初始化 app
var myApp = new Framework7({
    modalTitle: "提示",
    modalButtonOk: "确定",
    modalButtonCancel: "取消",
    modalPreloaderTitle: "加载中...",
    modalUsernamePlaceholder: "用户名",
    modalPasswordPlaceholder: "密码"
});


// Export selectors engine
var $$ = Dom7;
// Add view
var mainView = myApp.addView('.view-main', {
    // Because we use fixed-through navbar we can enable dynamic navbar
    dynamicNavbar: true
});

// Callbacks to run specific code for specific pages, for example for About page:
myApp.onPageInit('*', function (page) {
    // run createContentPage func after link was clicked
    console.log("onPageInit");
    $(".page").removeClass("page-on-left");
    $$('.create-page').on('click', function () {
        createContentPage();
    });
});
myApp.onPageAfterAnimation('*',function (page) {
    $(".page").removeClass("page-on-left");
})

$$(document).on('pageInit', function (e) {
    // 获取页面数据
    var page = e.detail.page;
    var title = page.url.split("?").toString().replace(',', '/');
    const pageTitle = title.split("/");
    pageTitle[2] === "base" ? $("title").text("新手入门基础") : (pageTitle[2] === "biz") ? $("title").text("企业送订单") :
        (pageTitle[2] === "motocycle") ? $("title").text("小帮出行订单") : (pageTitle[2] === "errand") ? $("title").text("小帮快送订单") : "";

    // if (pageTitle[2] === "base")
    //     $("title").text("新手入门基础");
    // if (pageTitle[2] === "motocycle")
    //     $("title").text("小帮出行订单");
    // if (pageTitle[2] === "errand")
    //     $("title").text("小帮快送订单");
    // if (pageTitle[2] === "biz")
    //     $("title").text("企业送订单");

})


// Generate dynamic page
var dynamicPageIndex = 0;

function createContentPage() {
    mainView.router.loadContent(
        '<!-- Top Navbar-->' +
        '<div class="navbar">' +
        '  <div class="navbar-inner">' +
        '    <div class="left"><a href="#" class="back link"><i class="icon icon-back"></i><span>Back</span></a></div>' +
        '    <div class="center sliding">Dynamic Page ' + (++dynamicPageIndex) + '</div>' +
        '  </div>' +
        '</div>' +
        '<div class="pages">' +
        '  <!-- Page, data-page contains page name-->' +
        '  <div data-page="dynamic-pages" class="page">' +
        '    <!-- Scrollable page content-->' +
        '    <div class="page-content">' +
        '      <div class="content-block">' +
        '        <div class="content-block-inner">' +
        '          <p>Here is a dynamic page created on ' + new Date() + ' !</p>' +
        '          <p>Go <a href="#" class="back">back</a> or go to <a href="services.html">Services</a>.</p>' +
        '        </div>' +
        '      </div>' +
        '    </div>' +
        '  </div>' +
        '</div>'
    );
    return;
}





