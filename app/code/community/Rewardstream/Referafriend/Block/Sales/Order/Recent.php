<?php
class Rewardstream_Referafriend_Block_Sales_Order_Recent extends Mage_Sales_Block_Order_Recent
{

    public function __construct()
    {
        parent::__construct();

        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->joinAttribute(
                'shipping_firstname',
                'order_address/firstname',
                'shipping_address_id',
                null,
                'left'
            )
            ->joinAttribute(
                'shipping_middlename',
                'order_address/middlename',
                'shipping_address_id',
                null,
                'left'
            )
            ->joinAttribute(
                'shipping_lastname',
                'order_address/lastname',
                'shipping_address_id',
                null,
                'left'
            )
            ->addAttributeToFilter(
                'customer_email',
                Mage::getSingleton('customer/session')->getCustomer()->getEmail()
            )
            ->addAttributeToFilter(
                'state',
                array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
            )
            ->addAttributeToSort('created_at', 'desc')
            ->setPageSize('5')
            ->load()
        ;

        $this->setOrders($orders);
    }

  
}
