<?php

use yii\helpers\Url;

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>成为企业送用户</title>
    <link href="/static/bizapply/css/style.css" rel="stylesheet">
    <link href="/static/bizapply/css/fonts/iconfont.css" rel="stylesheet">
    <script type="text/javascript" src="/static/js/jquery.min.js"></script>
    <style>
        .page-content{ padding: 15px;}
        .button {
            width: 92%;
            margin-left: 4%;
        }


        .category p {
            margin-bottom: 15px;
        }

        .category p span {
            margin: 0 1% 10px 1%;
            font-size: 14px;
            color: #666;
            border: 1px solid #ccc;
            border-radius: 20px;
            display: inline-block;
            cursor: pointer;
            width: 22%;
            text-align: center;
            line-height: 30px;
            font-size: 12px;
        }

        .category p span.on {
            border: 1px solid #ff6000;
            border-radius: 20px;
            color: #fff;
            background: #ff6000;
        }
    </style>
</head>

<body>
<div class="container">

    <div class="page-content">
        <div class="list-block">
            <ul>
                <li>
                    <div class="item-content">
                        <div class="item-media"><i class="iconfont icon-qiye"></i></div>
                        <div class="item-inner">
                            <div class="item-title label">企业名称</div>
                            <div class="item-input">
                                <input placeholder="请输入你的企业名称" type="text"
                                       id="biz_name" value="<?= $biz_name ?>" />
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="item-content">
                        <div class="item-media"><i class="iconfont icon-dizhi txt_green"></i></div>
                        <div class="item-inner">
                            <div class="item-title label">企业地址</div>
                            <div class="item-input">
                                <input type="text"
                                       placeholder="请选择企业地点" readonly
                                       value="<?= $address ?>" id="biz_address" map_url='<?= $map_url ?>' /><input style="display: none" type="text" value="<?= $location ?>" id="biz_location" />
                            </div>
                            <div class="item-after"><i class="iconfont icon-xiangyou f12"></i></div>
                        </div>
                    </div>
                </li>

                <li>
                    <div class="item-content">
                        <div class="item-media"><i class="iconfont icon-zhejiao"></i></div>
                        <div class="item-inner">
                            <div class="item-input">
                                <input type="text"
                                       placeholder="详细发单地址，例如:1室2号（必填）" id="biz_address_ext" value="<?= $biz_address_ext ?>" />
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="item-content">
                        <div class="item-media"><i class="iconfont icon-dianhua1"></i></div>
                        <div class="item-inner">
                            <div class="item-title label">联系电话</div>
                            <div class="item-input">
                                <input placeholder="输入手机号" type="tel" maxlength="11"
                                       id="biz_mobile" value="<?= $biz_mobile ?>" />
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="item-content">
                        <div class="item-media"><i class="iconfont icon-gongzuotai"></i></div>
                        <div class="item-inner">
                            <div class="item-title">经营品类&nbsp;&nbsp;<span class="f12 txt_999 text-center">每90天只能修改一次经营品类</span></div>
                        </div>
                    </div>
                    <div class="item-content category">
                        <p id='category_tag' category_tag='<?= $category_tag ?>'>
							<?php
							foreach ($biz_tag_data as $value) {
								echo "<span value =" . $value['tag_id'] . ">" . $value['tag_name'] . "</span>";
							}
							?>
                        </p>

                    </div>

                </li>
            </ul>
        </div>
        <button type='button' id="apply" class="button button-big button-round button-fill bg_orange mt15" url='<?= $url ?>'
                jump_url='<?= $jump_url ?>'>提交申请
        </button>
        <div class="mt15 txt_999 f12 text-center">我们会将审核结果在24小时内通过短信告知</div>
    </div>
</div>

<script type="text/javascript">
  var category_tag = $('#category_tag').attr('category_tag');
  var length = $('#category_tag').children("").length;
  for (var i = 0; i < length; i++) {
    var category_tag_value = $('#category_tag').children("").eq(i).attr('value');
    if(category_tag == category_tag_value){
      document.getElementsByTagName('span')[i+1].className = 'on';
    }
  }

  //var mSpan = document.getElementById('category_tag').getElementsByTagName('span');
  var aSpan = document.getElementsByTagName('span');
  //mSpan[0].className = 'on';

  for (var i = 0; i < aSpan.length; i++) {

    aSpan[i].onclick = function () {
      var siblings = this.parentNode.children;
      for (var j = 0; j < siblings.length; j++) {
        siblings[j].className = '';
      }
      this.className = 'on';

    };
  }
  ;
</script>
<script>
  $('#biz_address').click(function(){
    var $this = $(this);
    var biz_name = $('#biz_name').val();
    var biz_address_ext = $('#biz_address_ext').val();
    var biz_mobile = $('#biz_mobile').val();
    var category_tag = $('#category_tag').children(".on").attr('value');
    var map_url = $this.attr('map_url');

    window.location.href = map_url + '?biz_name=' + biz_name + '&biz_address_ext=' + biz_address_ext + '&biz_mobile=' + biz_mobile + '&category_tag=' + category_tag;
  });

  $('#apply').click(function () {
    var $this = $(this);
    var url = $this.attr('url');
    var jump_url = $this.attr('jump_url');

    var biz_name = $('#biz_name').val();
    var biz_location = $('#biz_location').val();
    var biz_address = $('#biz_address').val();
    var biz_address_ext = $('#biz_address_ext').val();
    var biz_mobile = $('#biz_mobile').val();
    var category_tag = $('#category_tag').children(".on").attr('value');

    if (!biz_location || !biz_address) {
      alert('请选择企业地址');
      return;
    } else if (!biz_name) {
      alert('请填写企业名称');
      return;
    }else if (!biz_address_ext) {
      alert('请填写企业详细地址');
      return;
    } else if (!biz_mobile) {
      alert('请填写联系电话');
      return;
    } else if (!category_tag) {
      alert('请选择经营品类');
      return;
    }

    if (confirm("请确认信息是否正确")) {
      var data = {
        biz_name: biz_name,
        biz_location: biz_location,
        biz_address: biz_address,
        biz_address_ext: biz_address_ext,
        biz_mobile: biz_mobile,
        biz_tag: category_tag,
      };
      //console.log(data);

      $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        data: data,
        success: function (json) {
          if(json.code==0){
            window.location.href = jump_url;
          }else{
            alert(json.message);
          }
        },
        fail: function (json) {
          //alert(json.msg);
        }
      });
    }
  });

</script>
</body>

</html>