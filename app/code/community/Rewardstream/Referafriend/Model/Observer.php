<?php

class Rewardstream_Referafriend_Model_Observer
{

	public function salesOrderInvoicePayAfter ( Varien_Event_Observer $observer )
	{
		// Is RewardStream extension enabled?
		$status = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_status' );
		if ( $status == 1 ) {
			$order = $observer->getEvent()->getInvoice()->getOrder();

			// Has order been completely paid?
			if ( $order->getBaseTotalDue() == 0 ) {
				$couponCode = $order['coupon_code'];

				// Was there a RewardStream coupon code?
				if ( substr( $couponCode, 0, 3 ) === 'ref' || substr( $couponCode, 0, 3 ) === 'rew') {
					$this->redeemCode( $couponCode, $order );
				}

			}
		}
	}

	public function createDiscountCode(Varien_Event_Observer $observer) {
		$status = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_status' );
		if ( $status == 1 ) {
			$quote = $observer->getQuote();
			$couponCode = $quote->getCouponCode();

			// Is there a coupon code in the discount codes input?
			// Is the coupon code Rewardstream offer code?
			if ( $couponCode && substr( $couponCode, 0, 3 ) === 'ref') {
				$this->createOfferSalesrule( $couponCode );
			}
			// Is the coupon code Rewardstream reward code?
			else if( $couponCode && substr( $couponCode, 0, 3 ) === 'rew') {
				$this->createRewardSalesrule( $couponCode );
			}
		}
	}

	/**
	 * API call to receive certificate based on coupon code
	 *
	 * @param $couponcode
	 * @return $certificate JSON object
	 */
	private function getCertificate($couponCode){
		$helper = Mage::helper( 'rewardstream' );
		$apiUrl = 'https://' . Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_url' );
		$apiKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_key' );
		$apiSecret = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_secret_key' );

		$response = $helper->getDataCallAPIJSON( $apiUrl . '/api/v2/members/all/certificates?j=GoodsId%20as%20Certificate&i=*, Certificate:*&q=' . urlencode('CertificateNumber="' . $couponCode . '"'), "GET", false, "Basic " . $apiSecret );
		$decodedResponse= json_decode($response);
		$certificate = $decodedResponse->records[0];
		return $certificate;
	}

	/**
	 * Check certificate to see if code has been used
	 *
	 * @param $certificate
	 * @return boolean
	 */
	public function is_code_valid( $certificate ){
		if($certificate->RedeemedAmount < $certificate->IssueAmount){
			return true;
		}
		return false;

	}

	/**
	 * Check if the user of the coupon code is the correct user
	 */
	public function validateCouponCodeEmail( Varien_Event_Observer $observer ){
		$status = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_status' );

		if ( $status == 1 ) {
			$quote = $observer->getQuote();
			$couponCode = $quote->getCouponCode();

			// Is the coupon code used by the correct user?
			if ( $couponCode && substr( $couponCode, 0, 3 ) === 'ref' && Mage::getSingleton('customer/session')->isLoggedIn()) {
				$customer = Mage::getSingleton('customer/session')->getCustomer();
				$responseData = $this->getOfferCode($couponCode);

				if ($responseData['Referee']['Email'] !== $customer->getEmail()) {
					Mage::getSingleton( 'checkout/session' )->addError( 'You are not eligible to use this coupon code.' );
					$quote->setCouponCode('');
					$quote->collectTotals()->save();
				}
			}
		}

		return $this;
	}

	/**
	 * Check if the user of the coupon code is the correct user
	 */
	public function validateCouponCodeEmail_BeforePlacingOrder( Varien_Event_Observer $observer ){
		$status = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_status' );

		if ( $status == 1 ) {
			$order = $observer->getEvent()->getOrder();
			$email = $order->getCustomerEmail();
			$couponCode = $order->getCouponCode();

			// Is the coupon code used by the correct user?
			if ( $couponCode && substr( $couponCode, 0, 3 ) === 'ref') {
				$responseData = $this->getOfferCode($couponCode);

				if ($responseData['Referee']['Email'] !== $email) {
					Mage::throwException("You are not eligible to use this coupon code.");
				}
			}
		}

		return $this;
	}

	/**
	 * Check if the user has coupon code on login at checkout/onepage
	 */
	public function validateCouponCodeEmail_customerLogin( Varien_Event_Observer $observer ) {
		$status = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_status' );

		if ( $status == 1 ) {
			$quote = Mage::getSingleton('checkout/session')->getQuote();
			$couponCode = $quote->getCouponCode();

			// Is the coupon code used by the correct user?
			if ( $couponCode && substr( $couponCode, 0, 3 ) === 'ref' && Mage::getSingleton('customer/session')->isLoggedIn()) {
				$customer = Mage::getSingleton('customer/session')->getCustomer();
				$responseData = $this->getOfferCode($couponCode);

				if ($responseData['Referee']['Email'] !== $customer->getEmail()) {
					$quote->setCouponCode('');
					$quote->collectTotals()->save();
				}
			}
		}
	}

	private function getOfferCode($couponCode) {
		$helper = Mage::helper( 'rewardstream' );
		$apiurl = 'https://' . Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_url' );
		$apiKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_key' );
		$secretKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_secret_key' );

		$result = $helper->getDataCallAPI( $apiurl . '/api/v2/custom/getOffer?api_key=' . $apiKey . '&code=' . $couponCode, "GET", false, "Basic " . $secretKey );
		$responseData = json_decode( $result, true );

		return $responseData;
	}

	/**
	 * Redeem code (POST to RewardStream API). Handles both offer codes and reward codes
	 *
	 * @params $couponCode, $order
	 *
	 */
	private function redeemCode($couponCode, $order)
	{
		$helper = Mage::helper( 'rewardstream' );
		$apiUrl = 'https://' . Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_url' );
		$apiKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_key' );
		$apiSecret = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_secret_key' );

		//IF OFFER CODE
		if(substr( $couponCode, 0, 3 ) === 'ref'){
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
			           '<Code>' . $couponCode . '</Code>' .
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

		//If Reward Code
		else if(substr( $couponCode, 0, 3 ) === 'rew'){
			$certificate =$this->getCertificate($couponCode);
			//Increase redeemedAmount in JSON
			$incrementedRedeemedAmount = $certificate->RedeemedAmount + 1;

			$userId = $certificate->UserId;
			$certId =  $certificate->Id;
			//Set up data for API call
			$args = array(
				"RedeemedAmount" => $incrementedRedeemedAmount
			);
			$json_args = json_encode( $args );
			$authorization_header = "Basic " . $apiSecret;
			$url = $apiUrl . '/api/v2/users/'. $userId . '/certificates/' . $certId;

			//Make API call
			$result = $helper->getDataCallAPIJSON( $url, "POST", $json_args, $authorization_header );
			$responseData = json_decode( $result, true );

			//Handle response
			if ( $responseData['Error']['Code'] ) {
				Mage::log( 'Error calling RS users API: ' . json_encode($responseData['Error']) );
				Mage::getSingleton( 'core/session' )->addError( 'Error redeeming reward in RewardStream system: ' . $responseData['Error']['Message'] );
			}
			else {
				Mage::getSingleton( 'core/session' )->addSuccess( 'Successfully redeemed reward in RewardStream system.' );
			}

		}
	}

	/**
	 * This function will call the RS API, and create a new sales rule if:
	 *   1) The offer is valid
	 *   2) The offer code hasn't been used before
	 *
	 * @param $offerCode string The offer code
	 */
	private function createOfferSalesrule($offerCode)
	{
		$helper = Mage::helper( 'rewardstream' );
		$apiurl = 'https://' . Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_url' );
		$apiKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_key' );
		$secretKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_secret_key' );

		$result = $helper->getDataCallAPI( $apiurl . '/api/v2/custom/getOffer?api_key=' . $apiKey . '&code=' . $offerCode, "GET", false, "Basic " . $secretKey );
		$responseData = json_decode( $result, true );

		$rsOfferStatus = $responseData['Status'];

		// If valid RS offer code, create coupon discount if necessary. Otherwise just apply code as normal and it will only work if there's a Magento coupon code already
		if ( $rsOfferStatus == "valid" ) {
			$rsDiscountValue = $responseData["Offer"]['Value'];
			$rsOfferType = $responseData["Offer"]['Type'];
			$rsMinimumPurchase = $responseData["Offer"]['MinimumPurchase'];
			if ( $rsOfferType == "percent_off" ) {

				$mgOfferType = "by_percent";
			}
			else if ( $rsOfferType == "dollar_off" ) {

				$mgOfferType = "cart_fixed";
			}
			else if ( $rsOfferType == "free_ship" ) {

				$mgOfferType = "free_ship";
			}
			else {
				throw new InvalidArgumentException("Offer type \"" . $rsOfferType . "\" not supported by this version of the RewardStream Refer-A-Friend extension.");
			}

			$name = "rewardstream_" . $offerCode;
			$rule = Mage::getModel( 'salesrule/rule' );
			$coupon = Mage::getModel( 'salesrule/coupon' );
			$couponAlreadyCreated = $coupon->getResource()->exists( $offerCode ); //check coupon code is already exist or not

			if ( $couponAlreadyCreated == false ) {
				// Get current website ID as that's the one it should be applicable to
				$websiteId = Mage::app()->getWebsite()->getId();
				// Get all customer group IDs, as this should be applicable to all
				$customerGroupIds = Mage::getModel('customer/group')->getCollection()->getAllIds();
				// Create the coupon as a Magento discount rule
				if ( $mgOfferType != "free_ship" ) {
					$rule->setName( $name )
					     ->setDescription( $name )
					     ->setFromDate( '' )
					     ->setCouponType( 2 )
					     ->setCouponCode( $offerCode )
					     ->setUsesPerCustomer( 1 )
					     ->setUsesPerCoupon( 1 )
					     ->setIsActive( 1 )
					     ->setCustomerGroupIds($customerGroupIds)
					     ->setConditionsSerialized( '' )
					     ->setActionsSerialized( '' )
					     ->setStopRulesProcessing( 0 )
					     ->setIsAdvanced( 1 )
					     ->setProductIds( '' )
					     ->setSortOrder( 0 )
					     ->setSimpleAction( $mgOfferType )
					     ->setDiscountAmount( $rsDiscountValue )
					     ->setDiscountQty( null )
					     ->setDiscountStep( 0 )
					     ->setSimpleFreeShipping( '0' )
					     ->setApplyToShipping( '0' )
					     ->setIsRss( 0 )
					     ->setWebsiteIds( array( $websiteId ) );

					$actions = Mage::getModel( 'salesrule/rule_condition_product' )
					               ->setType( 'salesrule/rule_condition_product' )
					               ->setAttribute( 'quote_item_row_total' )
					               ->setOperator( '>=' )
					               ->setValue( $rsMinimumPurchase );

					$rule->getActions()->addCondition( $actions );

					$rule->save();
				}
				else
				{
					$rule->setName( $name )
					     ->setDescription( $name )
					     ->setFromDate( '' )
					     ->setCouponType( 2 )
					     ->setCouponCode( $offerCode )
					     ->setUsesPerCustomer( 1 )
					     ->setUsesPerCoupon( 1 )
					     ->setIsActive( 1 )
					     ->setCustomerGroupIds($customerGroupIds)
					     ->setConditionsSerialized( '' )
					     ->setActionsSerialized( '' )
					     ->setStopRulesProcessing( 0 )
					     ->setIsAdvanced( 1 )
					     ->setProductIds( '' )
					     ->setSortOrder( 0 )
					     ->setSimpleAction( 'by_percent' )
					     ->setDiscountAmount( '0' )
					     ->setDiscountQty( null )
					     ->setDiscountStep( 0 )
					     ->setSimpleFreeShipping( '1' )
					     ->setApplyToShipping( '0' )
					     ->setIsRss( 0 )
					     ->setWebsiteIds( array( $websiteId ) );

					$actions = Mage::getModel( 'salesrule/rule_condition_product' )
					               ->setType( 'salesrule/rule_condition_product' )
					               ->setAttribute( 'quote_item_row_total' )
					               ->setOperator( '>=' )
					               ->setValue( $rsMinimumPurchase );

					$rule->getActions()->addCondition( $actions );

					$rule->save();
				}
			}
		}
	}

	/**
	 * This function will call the RS API, and create a new sales rule if:
	 *   1) The reward is valid
	 *   2) The reward code hasn't been used before
	 *
	 * @param $rewardCode string The reward code
	 * @throws Exception if offer
	 */
	private function createRewardSalesrule($rewardCode)
	{
		$helper = Mage::helper( 'rewardstream' );
		$apiurl = 'https://' . Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_url' );
		$apiKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_key' );
		$secretKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_secret_key' );

		$certificate = $this->getCertificate( $rewardCode );


		if ( $this->is_code_valid( $certificate ) && isset( $certificate->CertificateNumber ) ) {

			$rsRewardType = $certificate->Certificate->DiscountType;
			$rsMinimumPurchase = $certificate->Certificate->MinimumPurchase ?  $certificate->Certificate->MinimumPurchase : '';

			switch ( $rsRewardType ) {
				case 'percent_off':
					$mgRewardType = 'by_percent';
					$rsDiscountValue = (int) $certificate->Certificate->DiscountValue;
					break;
				case 'dollar_off':
					$mgRewardType = 'cart_fixed';
					$rsDiscountValue = (float) $certificate->Certificate->DiscountValue;
					break;
				case 'free_ship':
					$mgRewardType = 'free_ship';
					$rsDiscountValue = 0;
					break;
				default:
					throw new Exception("Unsupported discount type: " . $certificate->Certificate->DiscountType);
			}

			$name = "rewardstream_" . $rewardCode;
			$rule = Mage::getModel( 'salesrule/rule' );
			$coupon = Mage::getModel( 'salesrule/coupon' );
			$couponAlreadyCreated = $coupon->getResource()->exists( $rewardCode ); //check coupon code is already exist or not

			if ( $couponAlreadyCreated == false ) {
				// Get current website ID as that's the one it should be applicable to
				$websiteId = Mage::app()->getWebsite()->getId();
				// Get all customer group IDs, as this should be applicable to all
				$customerGroupIds = Mage::getModel('customer/group')->getCollection()->getAllIds();
				// Create the coupon as a Magento discount rule
				if ( $mgRewardType != "free_ship" ) {
					$rule->setName( $name )
					     ->setDescription( $name )
					     ->setFromDate( '' )
					     ->setCouponType( 2 )
					     ->setCouponCode( $rewardCode )
					     ->setUsesPerCustomer( 1 )
					     ->setUsesPerCoupon( 1 )
					     ->setIsActive( 1 )
					     ->setCustomerGroupIds($customerGroupIds)
					     ->setConditionsSerialized( '' )
					     ->setActionsSerialized( '' )
					     ->setStopRulesProcessing( 0 )
					     ->setIsAdvanced( 1 )
					     ->setProductIds( '' )
					     ->setSortOrder( 0 )
					     ->setSimpleAction( $mgRewardType )
					     ->setDiscountAmount( $rsDiscountValue )
					     ->setDiscountQty( null )
					     ->setDiscountStep( 0 )
					     ->setSimpleFreeShipping( '0' )
					     ->setApplyToShipping( '0' )
					     ->setIsRss( 0 )
					     ->setWebsiteIds( array( $websiteId ) );

					$actions = Mage::getModel( 'salesrule/rule_condition_product' )
					               ->setType( 'salesrule/rule_condition_product' )
					               ->setAttribute( 'quote_item_row_total' )
					               ->setOperator( '>=' )
					               ->setValue( $rsMinimumPurchase );

					$rule->getActions()->addCondition( $actions );

					$rule->save();
				}
				else
				{
					$rule->setName( $name )
					     ->setDescription( $name )
					     ->setFromDate( '' )
					     ->setCouponType( 2 )
					     ->setCouponCode( $rewardCode )
					     ->setUsesPerCustomer( 1 )
					     ->setUsesPerCoupon( 1 )
					     ->setIsActive( 1 )
					     ->setCustomerGroupIds($customerGroupIds)
					     ->setConditionsSerialized( '' )
					     ->setActionsSerialized( '' )
					     ->setStopRulesProcessing( 0 )
					     ->setIsAdvanced( 1 )
					     ->setProductIds( '' )
					     ->setSortOrder( 0 )
					     ->setSimpleAction( 'by_percent' )
					     ->setDiscountAmount( '0' )
					     ->setDiscountQty( null )
					     ->setDiscountStep( 0 )
					     ->setSimpleFreeShipping( '1' )
					     ->setApplyToShipping( '0' )
					     ->setIsRss( 0 )
					     ->setWebsiteIds( array( $websiteId ) );

					$actions = Mage::getModel( 'salesrule/rule_condition_product' )
					               ->setType( 'salesrule/rule_condition_product' )
					               ->setAttribute( 'quote_item_row_total' )
					               ->setOperator( '>=' )
					               ->setValue( $rsMinimumPurchase );

					$rule->getActions()->addCondition( $actions );

					$rule->save();
				}
			}

		}
	}
}


?>