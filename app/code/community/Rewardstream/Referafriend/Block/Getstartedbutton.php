<?php
class Rewardstream_Referafriend_Block_Getstartedbutton extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $apiurl = 'http://rewardstream.com/magento/setup';

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel('Get Started Here')
            ->setOnClick("popWin('$apiurl', '_blank')")
            ->toHtml();
        return $html;
    }
}
?>
