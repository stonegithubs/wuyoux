<?php
/**
 * Created by PhpStorm.
 * User: JasonLeung
 * Date: 2017/11/27
 * Time: 16:36
 */
namespace common\traits;


use api\modules\v1\helpers\StateCode;
use common\helpers\security\SecurityHelper;
use common\helpers\utils\DocumentHelper;

trait APIDocumentTrait{

	/**
	 * 文档接口
	 * @return array
	 */
	public function actionGet()
	{
		$document_type = SecurityHelper::getBodyParam('type',SecurityHelper::getBodyParam('document_type'));
		$data          = DocumentHelper::getDocument($document_type);
		if ($data) {
			$this->_data = $data;
		} else {
			$this->_message = StateCode::get(StateCode::OTHER_EMPTY_DATA);
			$this->_code    = StateCode::OTHER_EMPTY_DATA;
		}

		return $this->response();
	}
}