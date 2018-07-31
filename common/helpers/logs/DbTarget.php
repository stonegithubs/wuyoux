<?php
/**
 * @link http://www.281.com.cn/
 * @copyright Copyright (c) 2014 中山市中辰信息科技有限公司
 * @license http://www.281.com.cn/license
 * User: Andy Wong(黄燕弟)
 * Date: AndyWong 2017/6/14
 */

namespace common\helpers\logs;


class DbTarget extends \yii\log\DbTarget
{
	//TODO 先用MySQL后期改用mongoDB
	public function export()
	{
		if ($this->db->getTransaction()) {
			// create new database connection, if there is an open transaction
			// to ensure insert statement is not affected by a rollback
			$this->db = clone $this->db;
		}

		$tableName = $this->db->quoteTableName($this->logTable);
		$sql
				   = "INSERT INTO $tableName ([[level]], [[category]], [[log_time]], [[prefix]], [[message]])
                VALUES (:level, :category, :log_time, :prefix, :message)";
		$command   = $this->db->createCommand($sql);
		$flag      = time();
		foreach ($this->messages as $message) {
			list($text, $level, $category, $timestamp) = $message;
			if (!is_string($text)) {
				// exceptions may not be serializable if in the call stack somewhere is a Closure
				if ($text instanceof \Throwable || $text instanceof \Exception) {
					$text = (string)$text;
				} else {
					$text = VarDumper::export($text);
				}
			}
			$command->bindValues([
				':level'    => $level,
				':category' => $category,
				':log_time' => $timestamp,
				':prefix'   => $flag.$this->getMessagePrefix($message),
				':message'  => $text,
			])->execute();
		}
	}
}