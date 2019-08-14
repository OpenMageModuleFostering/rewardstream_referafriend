<?php
//create dynamically page of rewardstream when uploading extension in the magento
$cmsPage = array(
	'title' => 'Refer A Friend',
	'root_template' => 'one_column', // two_columns_left, two_columns_right, three_columns
	'meta_keywords' => 'Referral,Refer-a-Friend,Refer A Friend,Invite,Share,Reward',
	'meta_description' => 'Publicly accessible landing page that should advertise the referral program to customers with a call to action to send referrals.',
	'identifier' => 'refer',
	'content' => "<div class='main-rewardstream'>
	<div class='spark-refer-embed'></div>
	{{block type=\"core/template\" template=\"rewardstream/general_script.phtml\"}}
	</div>",
	'content_heading' => 'Refer A Friend',
	'is_active' => 0,
	'sort_order' => 0,
	'stores' => array( 0 ),
);

$page = Mage::getModel('cms/page')->load($cmsPage ['identifier']);
if ($page->getId())
{
	$page->setContent($cmsPage ['content'] . '<!-- Extension replaced previous content below --><div style="display:none;">' . $page['content'] . '</div>');
	$page->setTitle($cmsPage ['title']);
	$page->setIsActive(false);
	$page->save();
}
else
{
	Mage::getModel('cms/page')->setData($cmsPage )->save();
}

$staticBlock1 = array(
	'title' => 'Refer A Friend Checkout Success Content',
	'identifier' => 'refer-a-friend-checkout-success-content',
	'content' => "<div class='refer-a-friend-checkout-success-content'>
	<div class='checkout-success-promo'>
        <h2><strong>Want {referrer_reward_here} off your next purchase?</strong></h2>
        <p>Refer a friend and get {referrer_reward_here} for each successful referral. Your friends will get {referee_offer_here} off their first purchase. It's that easy.</p>
    </div>
    <div class='buttons-set'>
        <a href='{{store url=rewardstream/index/refer}}'><button type='button' class='button' title='Refer A Friend'><span><span>Refer A Friend</span></span></button></a>
    </div>
	</div>",
	'is_active' => 0,
	'stores' => array( 0 ),
);

Mage::getModel( 'cms/block' )->setData( $staticBlock1 )->save();


$staticBlock2 = array(
	'title' => 'Refer A Friend Purchase Required Notification',
	'identifier' => 'refer-a-friend-purchase-required',
	'content' => "<div class='refer-a-friend-purchase-required'>
					<h3>You must make an approved purchase before you can send a referral</h3>
					<p>If you have already made a purchase, you may need to wait for your order to ship before you can refer your friends and family.</p>
					</div>",
	'is_active' => 0,
	'stores' => array( 0 ),
);

Mage::getModel( 'cms/block' )->setData( $staticBlock2 )->save();

$staticBlock3 = array(
	'title' => 'Refer A Friend Page Content',
	'identifier' => 'refer-a-friend-page-content',
	'content' => "<div class='refer-a-friend-page-content'>
	<!-- Embeds the referral dashboard here -->
    <div class='section-first'>
        <!-- Referral interface embedded here using spark-refer-embed class -->
        <div class='spark-refer-embed'></div>
    </div>

    <!-- Embeds a button for your customers to check their referral history -->
    <div class='section-second'>
        <h3>Referral History</h3>
        <p>See a list of referrals you've made and the status of each referral:</p>
        <!-- Referral activity statement opens here using spark-statement class -->
        <p><a class='spark-statement' href='javascript:'><button class='button' title='Referral History' type='submit'><span><span>Referral History</span></span></button></a></p>
    </div>
	</div>",
	'is_active' => 0,
	'stores' => array( 0 ),
);

Mage::getModel( 'cms/block' )->setData( $staticBlock3 )->save();

