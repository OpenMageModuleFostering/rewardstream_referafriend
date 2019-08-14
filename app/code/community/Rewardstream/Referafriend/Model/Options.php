<?php
class Rewardstream_Referafriend_Model_Options
{
  /**
   * Provide available options as a value/label array
   *
   * @return array
   */
  public function toOptionArray()
  {
    return array(
      array('value'=>1, 'label'=>'Enable'),  
	  array('value'=>2, 'label'=>'Disable'),	  
    );
  
  }
  
}
?>