try {
    var Args = document.getElementById('page').getAttribute('data');
    Args = JSON.parse(Args);//json字符串转对象
    //获取本地数据缓存
    var key = Args.key;
    scriptArgs = JSON.parse(localStorage[key]);
    console.log(scriptArgs);
    var tableRow = scriptArgs.tableRow;//计算单条数据CLASS
    var requestUrl = scriptArgs.requestUrl;//数据请求连接
    var requestData = scriptArgs.requestData;//数据请求数据
    var requestType = scriptArgs.requestType;//数据请求方式
    var requestMaxNum = scriptArgs.requestMaxNum;//最多加载条数
    var requestPageSize = scriptArgs.requestPageSize;//单页显示条数
    var endLine = scriptArgs.endLine;//单页显示条数
    if(tableRow == undefined)
    {
        tableRow = ".page-list ul li";
    }
    if(endLine == undefined)
    {
        endLine = "<div class='page-tail'><p class='page-tail-wrap'><span class='page-tail-title'>我是有底线的</span></p></div>";
    }

    // 加载flag
    var loading = false;

// 上次加载的序号
    var lastIndex = $$(tableRow).length;

// 最多可加载的条目
    var maxItems = requestMaxNum;

// 每次加载添加多少条目
    var itemsPerLoad = requestPageSize;

// 注册'infinite'事件处理函数
    var page = 1;
    getPageData(1);

    $$('.infinite-scroll').on('infinite', function () {
        // 如果正在加载，则退出
        if (loading) return;
        lastIndex = $$(tableRow).length;
        // 设置flag
        loading = true;
        // 模拟1s的加载过程
        setTimeout(function () {
            // 重置加载flag
            loading = false;
            if (lastIndex >= maxItems) {
                // 加载完毕，则注销无限加载事件，以防不必要的加载
                myApp.detachInfiniteScroll($$('.infinite-scroll'));
                // 删除加载提示符

                $$('.infinite-scroll-preloader').html(endLine);
                $$('.infinite-scroll-preloader').show();
                //$$('.infinite-scroll-preloader').remove();
                return;
            }
            $$(".infinite-scroll-preloader").show();
            // 生成新条目的HTML
            page++;
            getPageData(page);

            // 更新最后加载的序号
            lastIndex = $$('.message-list li').length;
        }, 100);
    });

    function getPageData(page) {
        requestData.curr = page;
        requestData.pageSize = itemsPerLoad;
        var ajaxOption = {
            url: requestUrl,
            type: requestType,
            show_loading:false,
            data: requestData
        };
        ajaxRequest(ajaxOption, function (json) {
            $$(".infinite-scroll-preloader").hide();
            var ajaxData = json.data;
            successFun(json.code, json.message, ajaxData);
            if (json.code != 0) {
                $$('.infinite-scroll-preloader').remove();
            }

            return false;
        })
    }

    /**
     * 异步请求获取数据
     * @param option        请求参数
     * @param successfn     成功方法
     * @param errorfn       错误方法
     * @returns {boolean}
     */
    function ajaxRequest(option, successfn, errorfn) {
        var result = false;
        var mLoading = "";

        if (option.url == undefined || option.type == undefined) {
            return result;
        }
        if (option.dataType == undefined) {
            option.dataType = "text";
        }

        if (option.show_loading == undefined) {
            option.show_loading = true;
        }

        //判断是否开启加载层
        if (option.show_loading == true) {
            myApp.showIndicator();
        }

        $$.ajax({
            url: option.url,
            method: option.type,
            dataType: option.dataType,
            timeout: 8000,
            data: option.data,
            success: function (json, status, xhr) {
                if (option.show_loading != "") {
                    myApp.hideIndicator();
                }
                if (isJSON(json) == false) {
                    ajax_request = xhr.requestParameters;
                    console.log("ajax_url:" + xhr.responseURL);
                    console.log("ajax_type:" + ajax_request.method);
                    console.log("ajax_data");
                    console.log(option.data);
                    console.log("ajax_return:" + xhr.responseText);
                    return false;
                }
                console.log("ajax_result:请求成功");

                if (successfn == undefined || successfn == '') {
                    return reuslt;
                }

                if (successfn != undefined) {
                    successfn(JSON.parse(json));
                }

                return false;
            }, error: function (jqXHR, textStatus) {
                if (option.show_loading != "") {
                    myApp.hideIndicator();
                }
                if (typeof jqXHR.response != "object") {
                    ajax_request = jqXHR.requestParameters;
                    console.log("ajax_result:请求失败");
                    console.log("ajax_url:" + jqXHR.responseURL);
                    console.log("ajax_type:" + ajax_request.method);
                    console.log("ajax_data");
                    console.log(option.data);
                    console.log("ajax_return:" + jqXHR.responseText);

                    //myApp.alert("服务器异常！", '提示');
                    if (errorfn != undefined) {
                        errorfn(textStatus, jqXHR.responseText);
                    }
                }
                return false;
            }
        });
        return false;
    }

    /**
     * 判断是否为JSON格式
     * @param str
     * @returns {boolean}
     */
    function isJSON(str) {
        if (typeof str == 'string') {
            try {
                var obj = JSON.parse(str);
                if (typeof obj == 'object' && obj) {
                    return true;
                } else {
                    return false;
                }

            } catch (e) {
                console.log('error：' + str + '!!!' + e);
                return false;
            }
        }
        console.log('It is not a string!')
    }
} catch (e) {
    //访问错误，跳转错误页面
    throw "参数缺失或程序异常";
}

