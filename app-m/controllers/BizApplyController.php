<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: gred(关瑞宏)
 * Date: gred 2018/1/3
 */

namespace m\controllers;

use common\helpers\utils\UrlHelper;
use m\helpers\BizApplyHelper;
use Yii;

class BizApplyController extends ControllerAccess
{
	/**
	 * 入驻介绍
	 * @return string
	 */
	public function actionAbout()
	{
		$status = BizApplyHelper::ApplyCheck($this->user_id);
		//0待审核;1是企业送;2:不是企业送（包括审核失败的）
		if ($status == 0) {
			$this->redirect(['waiting']);
		} elseif ($status == 2) {
			$this->redirect(['apply-fail']);
		} elseif ($status == []) {
			return $this->renderPartial('about');
		}else{
			return $this->renderPartial('index');
		}
	}

	/**
	 * 填写入驻资料
	 * @return string
	 */
	public function actionApply()
	{
		$data         = BizApplyHelper::Apply($this->user_id);
		$biz_tag_data = BizApplyHelper::getTagList();

		$data['biz_tag_data'] = $biz_tag_data;

		return $this->renderPartial('apply', $data);
	}

	/**
	 * 等待审核结果
	 * @return string
	 */
	public function actionWaiting()
	{
		return $this->renderPartial('waiting');
	}

	/**
	 * 入驻信息提交成功
	 * @return string
	 */
	public function actionApplySuccess()
	{
		return $this->renderPartial('apply-success');
	}

	/**
	 * 审核失败
	 * @return string
	 */
	public function actionApplyFail()
	{
		$result['apply_url'] = UrlHelper::webLink('biz-apply/apply');

		return $this->renderPartial('apply-fail', $result);
	}

	/**
	 * 选择企业地址
	 * @return string
	 */
	public function actionMap()
	{
		$result['biz_name']        = Yii::$app->request->get('biz_name');
		$result['biz_address_ext'] = Yii::$app->request->get('biz_address_ext');
		$result['biz_mobile']      = Yii::$app->request->get('biz_mobile');
		$result['category_tag']    = Yii::$app->request->get('category_tag');
		$result['url']             = UrlHelper::webLink('biz-apply/apply');

		return $this->renderPartial('map', $result);
	}

	/**
	 * 处理入驻信息
	 * @return string
	 */
	public function actionApplySave()
	{

		if (BizApplyHelper::getBizStatus($this->user_id)) {
			//$this->setCodeMessage(StateCode::BUSINESS_HAVE_ENTER);
			//return $this->response();
			$result['code']    = 10001;
			$result['message'] = "企业入驻失败！您已提交申请，请耐心等待审核结果。";

			return json_encode($result);
		}

		$params = [
			'user_id'         => $this->user_id,
			'biz_name'        => Yii::$app->request->post("biz_name"),
			'biz_location'    => Yii::$app->request->post("biz_location"),
			'biz_address'     => Yii::$app->request->post("biz_address"),
			'biz_address_ext' => Yii::$app->request->post("biz_address_ext"),
			'biz_mobile'      => Yii::$app->request->post("biz_mobile"),
			'biz_tag_id'      => Yii::$app->request->post('biz_tag'),
		];

		$data = BizApplyHelper::applySave($params);
		if (!$data) {
			//$this->setCodeMessage(StateCode::BUSINESS_ENTER_FAILED);
			//return false;
			$result['code']    = 10002;
			$result['message'] = "企业入驻失败！您重新提交资料。";

			return json_encode($result);
		}
		$result['code'] = 0;

		return json_encode($result);
	}
}