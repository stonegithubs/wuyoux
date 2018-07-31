<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/5/3
 */

?>
<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link href="/static/bizapply/css/style.css" rel="stylesheet">
<title>小帮保险</title>
<style type="text/css">
    h1{
      color:black!important;
      margin-top: 2em!important;
      font-size:.8rem!important;
    }
    .header-img{
           width: 90%;
           position: absolute;
          margin: -14% -5% 0 5%;
    }
    .protocol-body{
          position: relative;
          margin-top: 60px;
    }
    .protocol-body-part{
        width:100%;
        background:white;
        height: 120px;
        border-top: 1px solid #e9e9e9;
        display:block;
    }
    .protocol{
       width: 100%;
       display: flex;
       justify-content: center;
    }
    .body-part{
        text-align: center;
        width: 45%;
    }
    .body-img{
        padding-top: 25px;
        width: 120px;
        height: 70px;
    }
    .body-part p{
    color:#aaa!important;
     letter-spacing: 0!important;
    }
    .col-part p{
        margin-top: 0!important;
        text-align:center!important;
        color:black!important;
        letter-spacing: 0!important;
    }
    .body-part-right{
    text-align:left;
    }
    .protocol-second-part{
        width:100%;
        margin:20px 0;
        background:white;
        height: 220px;
    }
    .protocol-second-part h1{
    text-align:center;
    padding: 38px 0 ;
    margin: 0!important;
    color:black;
    font-size:1rem!important;
    }
    .col-part{
    width:25%;
    text-align:center;
    margin-top: -35px;
    }
    .protocol-four-part{
        padding: 10px 0;
        background: white;
        text-align: center;
    }
    .protocol-four-part h1{
    font-size:1rem!important;
    }
    .second-part-img{
        width: 100px;
        height: 100px;
    }
    .protocol-four-part img{
    width:20%;
    }
    .col-flex{
    display: flex;
    justify-content: center;
    }

</style>
<link rel="stylesheet" href="/static/protocol/css/protocol.css">
</head>
<body>
<div >
    <div class="protocol-header">
     <div ><img class="protocol-header-img" src="/static/protocol/img/img_topbar.png"></div>
        <a href="<?= $bail_explain_url ?>"><img class="header-img" src="/static/protocol/img/btn_insurance.png"></a>
    </div>
    <div class="protocol-body">
       <a href="<?= $motorcycle_bail_url ?>" class="protocol-body-part " style="border-top:none">
       <div class="protocol">
            <div class="body-part body-part-left">
             <img class="body-img" src="/static/protocol/img/img_moto_insurance.png">
             </div>
            <div class="body-part body-part-right">
             <h1>个人摩托车驾驶人意外伤害</h1>
             <p>保险条款</p>
            </div>
            </div>
       </a>
       <a href="<?= $electrombile_bail_url  ?>" class="protocol-body-part">
          <div class="protocol">
                   <div class="body-part body-part-left">
                   <img class="body-img" src="/static/protocol/img/img_bike_insurance.png">
                   </div>
                   <div class="body-part body-part-right">
                   <h1>非机动车驾驶人意外伤害</h1>
                   <p>保险条款</p>
                   </div>
                   </div>
              </a>
                 <a href="<?=  $settlement_claim_url ?>"  class="protocol-body-part">
                    <div class="protocol">
                  <div class="body-part body-part-left">
                    <img class="body-img" src="/static/protocol/img/img_flow_insurance.png">
                    </div>
                      <div class="body-part body-part-right">
                    <h1>理赔流程</h1>
                    <p>理赔无忧</p>
                    </div>
                    </div>
               </a>
    </div>
    </div>
    <div class="protocol-second-part">
            <div> <h1>无忧帮帮特色</h1> </div>
            <div class="col-flex">
            <div class="col-part" >
            <img class="second-part-img" src="/static/protocol/img/icon_helicopter.png">
            <p>出行保险</p>
            </div>
            <div class="col-part" >
               <img class="second-part-img" src="/static/protocol/img/icon_coin.png">
                  <p>低价直供</p>
            </div>
             <div class="col-part" >
                <img class="second-part-img" src="/static/protocol/img/icon_shield.png">
                   <p>安全出行</p>
                 </div>        
                <div class="col-part" >
                <img class="second-part-img" src="/static/protocol/img/icon_note.png">
                 <p>及时理赔</p>
            </div>
            </div>
    </div>

    <div class="protocol-four-part">
           <h1>  合作伙伴</h1>
           <img src="/static/protocol/img/icon_insurance.png"></img>
    </div>
<div class="protocol-footer"><b></b> <img src="/static/protocol/img/icon_logo.png"></img><b></b></div>
</div>
</body>
</html>