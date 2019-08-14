<?php

class Rewardstream_Referafriend_Block_Sales_Order_History extends Mage_Sales_Block_Order_History
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/history.phtml');

        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_email', Mage::getSingleton('customer/session')->getCustomer()->getEmail())
            ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates()))
            ->setOrder('created_at', 'desc')
        ;

        $this->setOrders($orders);

        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Orders'));
    }

   
}
