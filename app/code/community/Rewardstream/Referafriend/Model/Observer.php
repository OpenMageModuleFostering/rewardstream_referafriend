<?php

class Rewardstream_Referafriend_Model_Observer
{

	public function salesOrderInvoicePayAfter ( Varien_Event_Observer $observer )
	{
		$status = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_status' );
		if ( $status == 1 ) {
			$order = $observer->getEvent()->getInvoice()->getOrder();

			if ( $order->getBaseTotalDue() == 0 ) {
				$couponCode = $order['coupon_code'];

				// If this had an RS coupon code
				if ( substr( $couponCode, 0, 3 ) === 'ref' ) {
					$this->redeemOffer( $couponCode, $order );
				}
			}
		}
	}


	private function redeemOffer($offerCode, $order)
	{
		$total = $order['grand_total'];
		$subtotal = $order['subtotal'];
		$purchaseDate = ( new DateTime( $order['created_at'] ) )->format( DateTime::ATOM );
		$purchaseNumber = $order['increment_id'];

		if ( $order->getCustomerIsGuest() ) {
			$accountIdentifier = 'guestorder#' . $purchaseNumber;
		}
		else {
			$accountIdentifier = $order->getCustomerId();
		}

		$items = $order->getAllVisibleItems();

		$xmlData = '<Data>' .
		         '<Code>' . $offerCode . '</Code>' .
		         '<DateUsed>' . $purchaseDate . '</DateUsed>' .
		         '<Account>' .
		            '<Number>' . $accountIdentifier . '</Number>' .
		            '<ActivationDate>' . $purchaseDate . '</ActivationDate>' .
                 '</Account>' .
		         '<Purchase>' .
		            '<PurchaseNumber>' . $purchaseNumber . '</PurchaseNumber>' .
		            '<SubTotal>' . floor( $subtotal ) . '</SubTotal>';

		foreach ( $items as $item ) {
			$sku = $item->getSku();
			$qty = $item->getQtyOrdered();
			$price = $item->getPrice();
			$xmlData .= '<Items><Product>' . $sku . '</Product><Quantity>' . floor( $qty ) . '</Quantity><Amount>' . number_format( $price, 2, '.', '' ) . '</Amount></Items>';
		}

		$xmlData .= '</Purchase></Data>';


		$helper = Mage::helper( 'rewardstream' );
		$apiUrl = 'https://' . Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_url' );
		$apiKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_key' );
		$apiSecret = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_secret_key' );
		$result = $helper->getDataCallAPI( $apiUrl . '/api/v2/custom/redeemOffer?api_key=' . $apiKey, "POST", $xmlData, "Basic " . $apiSecret );

		$responseData = json_decode( $result, true );

		if ( $responseData['Error']['Code'] ) {
			Mage::log( 'Error calling RS redeemOffer API: ' . json_encode($responseData['Error']) );
			Mage::getSingleton( 'core/session' )->addError( 'Error redeeming offer in RewardStream system: ' . $responseData['Error']['Message'] );
		}
		else {
			Mage::getSingleton( 'core/session' )->addSuccess( 'Successfully redeemed offer in RewardStream system.' );
		}
	}
}

?>