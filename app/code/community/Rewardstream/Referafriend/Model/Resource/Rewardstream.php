<?php 
class Rewardstream_Referafriend_Model_Resource_Rewardstream extends Mage_Core_Model_Resource_Db_Abstract
{
	public function _construct()
	{
		$this->_init('rewardstream/rewardstream', 'reward_id');
	}
}