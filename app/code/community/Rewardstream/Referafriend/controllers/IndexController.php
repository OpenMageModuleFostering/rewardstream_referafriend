<?php

class Rewardstream_Referafriend_IndexController extends Mage_Core_Controller_Front_Action {

	public function preDispatch() {
		parent::preDispatch();
		$action = $this->getRequest()->getActionName();
		$loginUrl = Mage::helper( 'customer' )->getLoginUrl();

		if ( !Mage::getSingleton( 'customer/session' )->authenticate( $this, $loginUrl ) ) {
			$this->setFlag( '', self::FLAG_NO_DISPATCH, true );
		}
	}

	public function referAction() {
		$this->loadLayout();
		$this->renderLayout();
	}

	public function tokenAction() {
		$id = $this->getRequest()->getParam( 'customer_id' );
		$customer = Mage::getModel( 'customer/customer' )->load( $id );
		$firstname = $customer->getFirstname();
		$lastname = $customer->getLastname();
		$email = $customer->getEmail();
		$address_id = $customer->getDefaultBilling();
		$addressdata = Mage::getModel( 'customer/address' )->load( $address_id );
		$street = $addressdata['street'];
		$city = $addressdata['city'];
		$postcode = $addressdata['postcode'];
		$region = $addressdata['region'];
		$phone = $addressdata['phone'];
		$country = $addressdata->getCountry();
		$accountnumber = $id;
		$region_id = $addressdata['region_id'];
		$regioncode = Mage::getModel( 'directory/region' )->load( $region_id );
		$state_code = $regioncode->getCode(); //CA
		$helper = Mage::helper( 'rewardstream' );
		$apiurl = 'https://' . Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_url' );
		$apiKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_key' );
		$url = $apiurl . '/api/v2/custom/syncMemberData?api_key=' . $apiKey;
		$method = "POST";
		$apiSecret = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_secret_key' );
		$authorization_header = "Basic " . $apiSecret;
		$currenttime = date( 'Y-m-d H:i:s' );
		$currentdate = date( "Ymd" );
		$data = '<Data>
		<FirstName>' . $firstname . '</FirstName>
		<LastName>' . $lastname . '</LastName>
		<EmailAddress>' . $email . '</EmailAddress>
		<Account>
		<Number>' . $accountnumber . '</Number>
		<InternalIdentifier>' . $phone . '</InternalIdentifier>
		<Status>A</Status>
		<ActivationDate>' . $currentdate . '</ActivationDate>
		</Account></Data>';
		// TODO Re-add address if valid
		//		<Address>
		//		<StreetLine1>'.$street.'</StreetLine1>
		//		<City>'.$city.'</City>
		//		<State>'.$state_code.'</State>
		//		<Country>'.$country.'</Country>
		//		<ZipCode>'.$postcode.'</ZipCode>
		//		</Address>
		//		</Data>';


		$result = $helper->getDataCallAPI( $url, $method, $data, $authorization_header );//Call SyncMemberApi for rewardstream


		if ( $result ) {
			$arrayofjson = json_decode( $result, true );


			// save response to database with all detail
			$model = Mage::getModel( 'rewardstream/rewardstream' );
			$model->setCustomer_id( $id );
			$model->setAccess_token( $arrayofjson['access_token'] );
			$model->setExpires_in( $arrayofjson['expires_in'] );
			$model->setMember_id( $arrayofjson['Member']['Id'] );

			$model->setFirstname( $arrayofjson['Member']['FirstName'] );
			$model->setLastname( $arrayofjson['Member']['LastName'] );
			$model->setEmail( $arrayofjson['Member']['EmailAddress'] );
			$model->setAccount_number( $arrayofjson['Member']['AccountNumber'] );
			$model->setActivationdate( $currentdate );
			$model->setActivationtime( $currenttime );
			$model->save();
			$token = $arrayofjson['access_token'];
			$html = '<script type="text/javascript" src="' . $apiurl . '/js/spark.v2.min.js?api_key=' . $apiKey . '&token=' . $token . '"></script>';
			$html .= '<a class="spark-refer">Refer a Friend</a>';

			echo $html;
		} else {

			echo "Sorry, the Refer a Friend functionality is currently unavailable. Please try again later.";
			exit();
		}
	}

	//Refresh token for when reward member token expire than click to refresh button than apply this action
	public function refreshtokenAction() {

		$rewardid = $this->getRequest()->getParam( 'reward_id' );
		$id = $this->getRequest()->getParam( 'customer_id' );
		$customer = Mage::getModel( 'customer/customer' )->load( $id );
		$firstname = $customer->getFirstname();
		$lastname = $customer->getLastname();
		$email = $customer->getEmail();
		$address_id = $customer->getDefaultBilling();
		$addressdata = Mage::getModel( 'customer/address' )->load( $address_id );
		$street = $addressdata['street'];
		$city = $addressdata['city'];
		$postcode = $addressdata['postcode'];
		$region = $addressdata['region'];
		$region_id = $addressdata['region_id'];
		$regioncode = Mage::getModel( 'directory/region' )->load( $region_id );
		$state_code = $regioncode->getCode(); //CA

		$phone = $addressdata['phone'];
		$country = $addressdata->getCountry();
		$state = $addressdata->getRegion();
		$accountnumber = $id;
		$helper = Mage::helper( 'rewardstream' );
		$apiurl = 'https://' . Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_url' );
		$apiKey = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_api_key' );
		$url = $apiurl . '/api/v2/custom/syncMemberData?api_key=' . $apiKey;
		$method = "POST";
		$apiSecret = Mage::getStoreConfig( 'rewardstream_options/section_one/rewardstream_secret_key' );

		$authorization_header = "Basic " . $apiSecret;
		$currenttime = date( 'Y-m-d H:i:s' );
		$currentdate = date( "Ymd" );
		$data = '<Data>
		<FirstName>' . $firstname . '</FirstName>
		<LastName>' . $lastname . '</LastName>
		<EmailAddress>' . $email . '</EmailAddress>
		<Account>
		<Number>' . $accountnumber . '</Number>
		<InternalIdentifier>' . $phone . '</InternalIdentifier>
		<Status>A</Status>
		<ActivationDate>' . $currentdate . '</ActivationDate>
		</Account></Data>';
		// TODO Re-add address if valid
		//		<Address>
		//		<StreetLine1>' . $street . '</StreetLine1>
		//		<City>' . $city . '</City>
		//		<State>' . $state_code . '</State>
		//		<Country>' . $country . '</Country>
		//		<ZipCode>' . $postcode . '</ZipCode>
		//		</Address>
		//		</Data>';
		$result = $helper->getDataCallAPI( $url, $method, $data, $authorization_header );//Call SyncMemberApi for rewardstream update data
		if ( $result ) {

			$arrayofjson = json_decode( $result, true );
			if ($arrayofjson['Error'])
			{
				echo "Sorry, the Refer a Friend functionality is currently unavailable. Please try again later. (" . $arrayofjson['Error']['Message'] . ")";
				exit();
			}
			else {
				$model = Mage::getModel( 'rewardstream/rewardstream' )->load( $rewardid );
				$model->setCustomer_id( $id );
				$model->setAccess_token( $arrayofjson['access_token'] );
				$model->setExpires_in( $arrayofjson['expires_in'] );
				$model->setMember_id( $arrayofjson['Member']['Id'] );

				$model->setFirstname( $arrayofjson['Member']['FirstName'] );
				$model->setLastname( $arrayofjson['Member']['LastName'] );
				$model->setEmail( $arrayofjson['Member']['EmailAddress'] );
				$model->setAccount_number( $arrayofjson['Member']['AccountNumber'] );
				$model->setActivationdate( $currentdate );
				$model->setActivationtime( $currenttime );
				$model->save();
				$token = $arrayofjson['access_token'];
				$html = '<script type="text/javascript" src="' . $apiurl . '/js/spark.v2.min.js?api_key=' . $apiKey . '&token=' . $token . '"></script>';
				$html .= '<a class="spark-refer">Refer a Friend</a>';
				echo $html;
			}
		} else {

			echo "Sorry, the Refer a Friend functionality is currently unavailable. Please try again later.";
			exit();
		}
	}
}