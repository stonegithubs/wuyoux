<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/9/30
 */

namespace common\components\wechat;

use maxwen\easywechat\WechatUser as MaxWenWechatUser;

/**
 * Class WechatUser
 * @package common\components\wechat
 *
 * @property string $unionId
 * @property string $token
 * @property string $nickname
 *
 */
class WechatUser extends MaxWenWechatUser
{

	/**
	 * @var string
	 */
	public $provider;

	/**
	 * @var string
	 */
	public $unionid;
	/**
	 * @var string
	 */
	public $openid;
	/**
	 * @var string
	 */
	public $language;

	/**
	 * @return string
	 */
	public function getUnionId()
	{
		return isset($this->original['unionid']) ? $this->original['unionid'] : '';
	}

	/**
	 * @return \Overtrue\Socialite\AccessToken
	 */
	public function getToken()
	{
		return isset($this->original['token']) ? $this->original['token'] : '';
	}

	public function getNickname()
	{
		return isset($this->original['nickname']) ? $this->original['nickname'] : '';
	}

	/**
	 * WechatUser constructor.
	 */
	public function __construct($config = [])
	{
		parent::__construct($config);
		$this->unionid  = isset($this->original['unionid']) ? $this->original['unionid'] : '';
		$this->openid   = isset($this->original['openid']) ? $this->original['openid'] : '';
		$this->language = isset($this->original['language']) ? $this->original['language'] : '';
	}
}