<?php

class Rewardstream_Referafriend_Helper_Data extends Mage_Core_Helper_Abstract
{

	function getDataCallAPI ( $url, $method, $data = false, $authorization_header )
	{
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $method );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/xml ',
			'Authorization: ' . $authorization_header . '',
		) );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec( $curl );
		curl_close( $curl );

		return $result;
	}

	function getDataCallAPIJSON ( $url, $method, $data = false, $authorization_header )
	{
		$curl = curl_init();
		curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, $method );
		curl_setopt( $curl, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json ;charset=UTF-8',
			'Authorization: ' . $authorization_header . '',
		) );
		curl_setopt( $curl, CURLOPT_POSTFIELDS, $data );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
		$result = curl_exec( $curl );
		curl_close( $curl );

		return $result;
	}
}

?>