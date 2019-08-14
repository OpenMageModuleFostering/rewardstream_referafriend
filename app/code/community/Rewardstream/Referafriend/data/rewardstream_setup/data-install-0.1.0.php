<?php
//create dynamically page of rewardstream when uploading extension in the magento
$cmsPage = array(
	'title' => 'Refer A Friend Public Page',
	'root_template' => 'one_column', // two_columns_left, two_columns_right, three_columns
	'meta_keywords' => 'Referral,Refer-a-Friend,Refer A Friend,Invite,Share,Reward',
	'meta_description' => 'Publicly accessible landing page that should advertise the referral program to customers with a call to action to send referrals.',
	'identifier' => 'refer',
	'content' => "<div class='main-rewardstream'>
<div class='reward-image'><img src='{{media url=wysiwyg/rewardstream/sharing-is-caring.jpg}}' alt='' /></div>
<div class='rewardstream-cms'>
<h2>Give Your Friends [Referee_Offer_Here]</h2>
<p>Invite your friends and they'll get <strong>[Referee_Offer_Here]</strong>. You'll also [Referrer_Reward_Here] for referring your friend. Refer as often as you like!</p>
<h3>How to send a referral</h3>
<ol>
<li>Click <a href='{{store url=rewardstream/index/refer}}'>Refer A Friend </a>to get started</li>
<li>Sign into your My Account or create a My Account</li>
<li>Refer your friends by email, social media, text message, or your personal referral link</li>
<li>After your friend makes their first purchase, you'll get [Referrer_Reward_Here]</li>
</ol><p><a href='{{store url=rewardstream/index/refer}}'><button class='button' title='Refer A Friend' type='submit'><span><span>Refer A Friend</span></span></button></a></p>
<p>*Note: You must make an approved purchase before you can send a referral. For more details regarding our referral program, read our <a href='#'>Program Rules</a>.</p>
</div>
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
		<h2>Give Your Friends [Referee_Offer_Here]</h2>
		<p>Invite your friends and they'll get&nbsp;<strong>[Referee_Offer_Here]</strong>. You'll also [Referrer_Reward_Here] for referring your friend. Refer as often as you like!</p>
		<h3>How to send a referral</h3>
		<ol>
		<li>Click &nbsp;<a href='{{store url=rewardstream/index/refer}}'>Refer A Friend</a>&nbsp; to get started</li>
		<li>Sign into your My Account or create a My Account</li>
		<li>Refer your friends by email, social media, text message, or your personal referral link</li>
		<li>After your friend makes their first purchase, you'll get [Referrer_Reward_Here]</li>
		</ol><p><a href='{{store url=rewardstream/index/refer}}'><button class='button' title='Refer A Friend' type='submit'><span><span>Refer A Friend</span></span></button></a></p>
		<p>*Note: You must make an approved purchase before you can send a referral. For more details regarding our referral program, read our&nbsp;<a href='#'>Program Rules</a>.</p>
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
	'content' => "<div class=\"refer-a-friend-page-content\">
                         <div class=\"section-first\">
                            <h3>How to send a referral</h3>
                                 <p>Choose a way to send referral to your friends and family using your social networks,email,or face-to-face. Refer as often as you like!</p>
                        </div>

                         <div class=\"section-second\">
                            <h3>What can I earn?</h3>
                              <p>Give your friend [Referee_Offer_Here] and earn [Referrer_Reward_Here] for every successful referral.</p>
                         </div>

                         <div class=\"section-third\">
                           <h3>Send A Referral</h3>
                           <!-- Referral interface embedded here using spark-refer-embed class -->
                            <div class=\"spark-refer-embed\"></div>
                         </div>

                         <div class=\"section-four\">
                            <h3>Referral History</h3>
                             <p>Click the button below to see the status of referrals you have made.</p>
                             <!-- Referral activity statement opens here using spark-statement class -->
							 <p><a class=\"spark-statement\" href=\"javascript:\"><button class='button' title='Referral History' type='submit'><span><span>Referral History</span></span></button></a></p>
                         </div>
					</div>",
	'is_active' => 0,
	'stores' => array( 0 ),
);

Mage::getModel( 'cms/block' )->setData( $staticBlock3 )->save();

