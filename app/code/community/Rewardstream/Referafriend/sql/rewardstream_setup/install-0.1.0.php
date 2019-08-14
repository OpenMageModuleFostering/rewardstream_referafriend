<?php
$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()->newTable($installer->getTable('rewardstream/rewardstream'))
	->addColumn('reward_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 50, array(
		'unsigned' => true,
		'nullable' => false,
		'primary' => true,
		'identity' => true,
	), 'Entity ID')
	->addColumn('customer_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 50, array(
		'unsigned' => true,
		'nullable' => false,
	), 'Customer ID')
	->addColumn('access_token', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
		'nullable' => false,
	), 'Access Token')
	->addColumn('expires_in', Varien_Db_Ddl_Table::TYPE_INTEGER, 50, array(
		'unsigned' => true,
		'nullable' => false,
	), 'Expires In')
	->addColumn('member_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 50, array(
		'unsigned' => true,
		'nullable' => false,
	), 'Member Id')
	->addColumn('firstname', Varien_Db_Ddl_Table::TYPE_VARCHAR, 50, array(
		'nullable' => false,
		'default' => '',
	), 'First Name')
	->addColumn('lastname', Varien_Db_Ddl_Table::TYPE_VARCHAR, 60, array(
		'nullable' => false,
		'default' => '',
	), 'Last Name')
	->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 160, array(
		'nullable' => false,
		'default' => '',
	), 'Email Address')
	->addColumn('account_number', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
		'nullable' => false,
		'default' => '',
	), 'Account Number')
	->addColumn('activationdate', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
		'nullable' => false,
		'default' => '',
	), 'Activation Date')
	->addColumn('activationtime', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
		'nullable' => false,
		'default' => '',
	), 'Activation Time')
	->setComment('RewardStream Refer a Friend Table');

$installer->getConnection()->createTable($table);

$installer->endSetup();