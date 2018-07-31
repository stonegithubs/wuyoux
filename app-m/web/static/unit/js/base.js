/**
 * 动态加载JS文件
 * @param url       JS文件地址
 * @param callback  回调方法
 */
function loadScript(url, callback,id,data) {
    html = "<script type='application/javascript' id='"+id+"' data='"+data+"' src='"+url+"'></script>";
    document.write(html);
    // var script = document.createElement("script");
    // script.type = "text/javascript";
    // if(typeof(callback) != "undefined"){
    //     if (script.readyState) {
    //         script.onreadystatechange = function () {
    //             if (script.readyState == "loaded" || script.readyState == "complete") {
    //                 script.onreadystatechange = null;
    //                 callback();
    //             }
    //         };
    //     } else {
    //         script.onload = function () {
    //             callback();
    //         };
    //     }
    // }
    // script.src = url;
    // if(id != undefined )
    // {
    //     script.id = id;
    // }
    // if(data != undefined)
    // {
    //     script.data = data;
    // }
    // script.charset = "utf-8";
    // document.body.appendChild(script);
}