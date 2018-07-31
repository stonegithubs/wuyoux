<?php
/**
 * Created by PhpStorm.
 * User: gred
 * Date: 2018/2/9
 * Time: 9:47
 */
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
    <title>地址选择</title>
    <link rel="stylesheet" href="//cache.amap.com/lbs/static/main1119.css"/>
    <style>
        #myPageTop{
            border:none; top:0; right:0; width: 96%; margin: auto; padding: 2%;
        }
        #myPageTop input{ border-radius:20px; border: 1px solid #ddd; line-height: 35px; padding: 0 15px; outline: 0; }
        .auto-item{ padding: 10px}
    </style>
    <script type="text/javascript"
            src="//webapi.amap.com/maps?v=1.4.3&key=c4af304699451af317de903ac2a5b3f2&plugin=AMap.Autocomplete,AMap.PlaceSearch"></script>
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
</head>
<body>
<div id="container"></div>
<div id="myPageTop">
    <input id="tipinput" placeholder="请输入你要搜索的地址"/><button id="submit">确定</button><button id="cancel">取消</button>
    <span id="address_detail"></span>
    <table>
        <tr>
            <td>


                <span id="params" biz_name='<?= $biz_name ?>' biz_address_ext='<?= $biz_address_ext ?>'
                      biz_mobile='<?= $biz_mobile ?>' category_tag='<?= $category_tag ?>'></span><span
                        id="address"></span><span id="location"></span><span id="url" value="<?= $url ?>"></span>
                <span id="address_detail"></span>
            </td>
        </tr>
        <tr>
            <td>

            </td>
        </tr>
    </table>
</div>
<script type="text/javascript">
    //地图加载
    var map = new AMap.Map("container", {
        resizeEnable: true
    });
    //输入提示
    var autoOptions = {
        input: "tipinput"
    };
    var auto = new AMap.Autocomplete(autoOptions);
    var placeSearch = new AMap.PlaceSearch({
        map: map,
        pageSize: 1,
        pageIndex: 1
    });  //构造地点查询类
    AMap.event.addListener(auto, "select", select);//注册监听，当选中某条记录时会触发

    function select(e) {
        $('#address_detail').text(e.poi.district + e.poi.address);
        placeSearch.setCity(e.poi.adcode);
        placeSearch.search(e.poi.name);  //关键字查询查询
        var location = e.poi.location.lng + ',' + e.poi.location.lat;
        var address = e.poi.name;
        $('#address').val(address);
        $('#location').val(location);
    }

</script>
<script>
    $('#submit').click(function () {
        var address = $('#address').val();
        var location = $('#location').val();
        var url = $('#url').attr('value');

        if (!address || !location || !url || location == 'undefined,undefined') {
            alert('请选择企业地址(不含公交线路)');
            return;
        }

        var biz_name = $('#params').attr('biz_name');
        var biz_address_ext = $('#params').attr('biz_address_ext');
        var biz_mobile = $('#params').attr('biz_mobile');
        var category_tag = $('#params').attr('category_tag');

        window.location.href = url + '?address=' + address + '&location=[' + location + ']' + '&biz_name=' + biz_name + '&biz_address_ext=' + biz_address_ext + '&biz_mobile=' + biz_mobile + '&category_tag=' + category_tag;
    });

    $('#cancel').click(function () {
        var url = $('#url').attr('value');

        var biz_name = $('#params').attr('biz_name');
        var biz_address_ext = $('#params').attr('biz_address_ext');
        var biz_mobile = $('#params').attr('biz_mobile');
        var category_tag = $('#params').attr('category_tag');

        window.location.href = url + '?biz_name=' + biz_name + '&biz_address_ext=' + biz_address_ext + '&biz_mobile=' + biz_mobile + '&category_tag=' + category_tag;
    });
</script>
</body>
</html>