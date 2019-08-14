<?php

class Rewardstream_Referafriend_Block_Customer_Account_Navigation extends Mage_Customer_Block_Account_Navigation {


	protected $_links = array();

	protected $_activeLink = false;


	public function addCustomLink( $name, $path, $label, $urlParams = array() ) {

		$status = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_status' );
		if ( $status == 1 ) {
			if ( Mage::getSingleton( 'customer/session' )->isLoggedIn() ) {

				$customer = Mage::getSingleton( 'customer/session' )->getCustomer();
				$email = $customer->getEmail();
				$orderCollection = Mage::getModel( 'sales/order' )->getCollection();
				$orderCollection->addFieldToFilter( 'customer_email', $email )->getFirstItem();
                $custom_label = Mage::getStoreConfig('rewardstream_options/section_two/rewardstream_menu_name');

                // If the user hasn't set the menu title name
                if (empty($custom_label)) {
                    $custom_label = "Refer A Friend";
                }

				$this->_links[$name] = new Varien_Object( array(
					'name' => $name,
					'path' => $path,
					'label' => $custom_label,
					'url' => $this->getUrl( $path, $urlParams ),
				) );
				return $this;
			}
		}
	}
}