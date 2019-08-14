<?php

require_once 'Mage/Checkout/controllers/CartController.php';

class Rewardstream_Referafriend_CartController extends Mage_Checkout_CartController {

	public function couponPostAction()
	{
		/**
		 * No reason continue with empty shopping cart
		 */
		if ( !$this->_getCart()->getQuote()->getItemsCount() ) {
			$this->_goBack();
			return;
		}

		$couponCode = (string) $this->getRequest()->getParam( 'coupon_code' );
		if ( $this->getRequest()->getParam( 'remove' ) == 1 ) {
			$couponCode = '';
		}
		$oldCouponCode = $this->_getQuote()->getCouponCode();

		if ( !strlen( $couponCode ) && !strlen( $oldCouponCode ) ) {
			$this->_goBack();
			return;
		}

		try {
			// Get RS Offer and create as coupon discount if code matches our format
			$status = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_status' );
			if ( $status == 1 ) {
				if ( substr( $couponCode, 0, 3 ) === 'ref' ) {
					$this->createRewardstreamSalesrule( $couponCode );
				}
			}

			$codeLength = strlen( $couponCode );
			$isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;



			$this->_getQuote()->getShippingAddress()->setCollectShippingRates( true );
			$this->_getQuote()->setCouponCode( $isCodeLengthValid ? $couponCode : '' )
				->collectTotals()
				->save();

			if ( $codeLength ) {
				if ( $isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode() ) {
					$this->_getSession()->addSuccess( $this->__( 'Coupon code "%s" was applied.', Mage::helper( 'core' )->escapeHtml( $couponCode ) ) );
				}
				else {
					$this->_getSession()->addError( $this->__( 'Coupon code "%s" is not valid.', Mage::helper( 'core' )->escapeHtml( $couponCode ) ) );
				}
			}
			else {
				$this->_getSession()->addSuccess( $this->__( 'Coupon code was canceled.' ) );
			}
		}
		catch ( Mage_Core_Exception $e ) {
			$this->_getSession()->addError( $e->getMessage() );
		}
		catch ( Exception $e ) {
			$this->_getSession()->addError( $this->__( 'Cannot apply the coupon code.' ) );
			Mage::logException( $e );
		}
		$this->_goBack();
	}

	/**
	 * This function will call the RS API, and create a new sales rule if:
	 *   1) The offer is valid
	 *   2) The offer code hasn't been used before
	 *
	 * @param $offerCode string The offer code
	 */
	private function createRewardstreamSalesrule($offerCode)
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
}