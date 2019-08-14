<?php
class Rewardstream_Referafriend_Block_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $apiurl = '';
        $apiurl_var = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_url' );
        if (is_string($apiurl_var) && !empty($apiurl_var)) {
            $apiurl = 'https://portal-' . $apiurl_var;
        }

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setLabel('Launch RewardStream Management Portal')
            ->setOnClick("popWin('$apiurl', '_blank')")
            ->toHtml();

        return $html;
    }
}
?>
