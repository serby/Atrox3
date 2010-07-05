<?php
/**
 * @package Base
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base 
 */
class BaseFactory {

	/**
	 * Content Management Classes
	 */
	/**
	 * @return ContentPageControl
	 */
	function getContentPageControl() {
		require_once("ContentManagement/ContentPage.php");
		$obj = new ContentPageControl();
		return $obj;
	}		
	/**
	 * @return ContentPageElementControl
	 */
	function getContentPageElementControl() {
		require_once("ContentManagement/ContentPageElement.php");
		$obj = new ContentPageElementControl();
		return $obj;
	}
	
	/**
	 * Default Module Classes
	 */
	/**
	 * @return SectionControl
	 */
	function getSectionControl() {
		require_once("Default/Section.php");
		$obj = new SectionControl();
		return $obj;
	}
	/**
	 * @return PaymentControl
	 */
	function getPaymentControl() {
		require_once("Default/Payment.php");
		return new PaymentControl(CoreFactory::getXPayControl());
	}	
	/**
	 * @return NoteControl
	 */
	function getNoteControl() {
		require_once("Default/Note.php");
		$obj = new NoteControl();
		return $obj;
	}
	
	/**
	 * Returns a TemporaryStoreControl
	 * @return TemporaryStoreControl
	 */
	function getTemporaryStoreControl() {
		require_once("Default/TemporaryStore.php");
		$obj = new TemporaryStoreControl();
		return $obj;
	}		
	
	/**
	 * Community Module Classes
	 */
	/**
	 * @return ProfanityValidation
	 */
	function getProfanityValidation() {
		require_once("Community/Profanity.php");
		$obj = new ProfanityValidation();
		return $obj;
	}
	/**
	 * @return ProfanityControl
	 */
	function getProfanityControl() {
		require_once("Community/Profanity.php");
		$obj = new ProfanityControl();
		return $obj;
	}
	/**
	 * @return ForumControl
	 */
	function getForumControl() {
		require_once("Community/Forum.php");
		$obj = new ForumControl();
		return $obj;
	}
	/**
	 * @return ForumThreadControl
	 */
	function getForumThreadControl() {
		require_once("Community/ForumThread.php");
		$obj = new ForumThreadControl();
		return $obj;
	}
	/**
	 * @return ForumPostControl
	 */
	function getForumPostControl() {
		require_once("Community/ForumPost.php");
		$obj = new ForumPostControl();
		return $obj;
	}
	/**
	 * @return BlogControl
	 */
	function getBlogControl() {
		require_once("Community/Blog.php");
		$obj = new BlogControl();
		return $obj;
	}
	/**
	 * @return BlogEntryControl
	 */
	function getBlogEntryControl() {
		require_once("Community/BlogEntry.php");
		$obj = new BlogEntryControl();
		return $obj;
	}
	
	/**
	 * @return CommentControl
	 */
	function getCommentControl() {
		require_once("Community/Comment.php");
		$obj = new CommentControl();
		return $obj;
	}
	
	/**
	 * Feedback Module Classes
	 */
	/**
	 * @return FeedbackControl
	 */
	function getFeedbackControl() {
		require_once("Feedback/Feedback.php");
		$obj = new FeedbackControl();
		return $obj;
	}
	/**
	 * @return FeedbackNoteControl
	 */
	function getFeedbackNoteControl() {
		require_once("Feedback/FeedbackNote.php");
		$obj = new FeedbackNoteControl();
		return $obj;
	}

	/**
	 * Member Module Classes
	 */
	/**
	 * @return MemberControl
	 */
	function getMemberControl() {
		static $singleton;
		require_once("Member/Member.php");
		isset($singleton) || $singleton = new MemberControl();
		return $singleton;
	}
	/**
	 * @return CustomMemberControl
	 */
	function getCustomMemberControl() {
		require_once("Member/Member.php");
		$obj = new MemberControl();
		return $obj;
	}
	/**
	 * @return NewMemberControl
	 */
	function getNewMemberControl() {
		require_once("Member/Member.php");
		$obj = new MemberControl();
		return $obj;
	}
	/**
	 * @return SecurityGroupControl
	 */
	function getSecurityGroupControl() {
		require_once("Member/SecurityGroup.php");
		$obj = new SecurityGroupControl();
		return $obj;
	}
	/**
	 * @return SecurityGroupToSecurityResourceControl
	 */
	function getSecurityGroupToSecurityResourceControl() {
		require_once("Member/SecurityGroupToSecurityResource.php");
		$obj = new SecurityGroupToSecurityResourceControl();
		return $obj;
	}
	/**
	 * @return SecurityResourceControl
	 */
	function getSecurityResourceControl() {
		require_once("Member/SecurityResource.php");
		$obj = new SecurityResourceControl();
		return $obj;
	}
 	/**
	 * @return AddressControl
	 */
	function getAddressControl() {
		require_once("Member/Address.php");
		$obj = new AddressControl();
		return $obj;
	}
 	/**
	 * @return SettingControl
	 */
	function getSettingControl() {
		require_once("Member/Setting.php");
		$obj = new SettingControl();
		return $obj;
	}
	/**
	 * @return MemberUploadControl
	 */
	function getMemberUploadControl() {
		require_once("Member/MemberUpload.php");
		$obj = new MemberUploadControl();
		return $obj;
	}
	/**
	 * @return MemberStatisticsControl
	 */
	function getMemberStatisticsControl() {
		require_once("Member/MemberStatistics.php");
		$obj = new MemberStatisticsControl();
		return $obj;
	}

	/**
	 * News Module Classes
	 */
	/**
	 * @return NewsControl
	 */
	function getNewsControl() {
		require_once("News/News.php");
		$obj = new NewsControl();
		return $obj;
	}
	/**
	 * @return NewsCategoryControl
	 */
	function getNewsCategoryControl() {
		require_once("News/NewsCategory.php");
		$obj = new NewsCategoryControl();
		return $obj;
	}
			
	/**
	 * Poll Module Classes
	 */
	/**
	 * @return PollControl
	 */
	function getPollControl() {
		require_once("Poll/Poll.php");
		$obj = new PollControl();
		return $obj;
	}
	/**
	 * @return PollResultControl
	 */
	function getPollResultControl() {
		require_once("Poll/PollResult.php");
		$obj = new PollResultControl();
		return $obj;
	}
	/**
	 * @return PollAnswerControl
	 */
	function getPollAnswerControl() {
		require_once("Poll/PollAnswer.php");
		$obj = new PollAnswerControl();
		return $obj;
	}
	
	/**
	 * Quiz Module
	 */
	
	/**
	 * @return QuizControl
	 */
	function getQuizControl() {
		require_once("Quiz/Quiz.php");
		$obj = new QuizControl();
		return $obj;
	}
	
	/**
	 * @return QuizAnswerControl
	 */
	function getQuizAnswerControl() {
		require_once("Quiz/QuizAnswer.php");
		$obj = new QuizAnswerControl();
		return $obj;
	}
	
	/**
	 * @return QuizEntryControl
	 */
	function getQuizEntryControl() {
		require_once("Quiz/QuizEntry.php");
		$obj = new QuizEntryControl();
		return $obj;
	}
	
	/**
	 * @return QuizEntryAnswerControl
	 */
	function getQuizEntryAnswerControl() {
		require_once("Quiz/QuizEntryAnswer.php");
		$obj = new QuizEntryAnswerControl();
		return $obj;
	}
	
	/**
	 * @return QuizQuestionControl
	 */
	function getQuizQuestionControl() {
		require_once("Quiz/QuizQuestion.php");
		$obj = new QuizQuestionControl();
		return $obj;
	}
	
	/**
	 * Store Module Classes
	 */
	/**
	 * @return CountryControl
	 */
	function getCountryControl() {
		require_once("Store/Country.php");
		$obj = new CountryControl();
		return $obj;
	}
	/**
	 * @return PostageRateControl
	 */
	function getPostageRateControl() {
		require_once("Store/PostageRate.php");
		$obj = new PostageRateControl();
		return $obj;
	}
	
	/**
	 * @return DeliveryMethodControl
	 */
	function getDeliveryMethodControl() {
		require_once("Store/DeliveryMethod.php");
		$obj = new DeliveryMethodControl();
		return $obj;
	}					
	/**
	 * @return DonationControl
	 */
	function getDonationControl() {
		require_once("Store/Donation.php");
		$obj = new DonationControl();
		return $obj;
	}
	/**
	 * @return OrderControl
	 */
	function getOrderControl() {
		require_once("Store/Order.php");
		$obj = new OrderControl();
		return $obj;
	}
	/**
	 * @return OrderNoteControl
	 */
	function getOrderNoteControl() {
		require_once("Store/OrderNote.php");
		$obj = new OrderNoteControl();
		return $obj;
	}
	/**
	 * @return OrderItemControl
	 */
	function getOrderItemControl() {
		require_once("Store/OrderItem.php");
		$obj = new OrderItemControl();
		return $obj;
	}
	/**
	 * @return PackagingControl
	 */
	function getPackagingControl() {
		require_once("Store/Packaging.php");
		$obj = new PackagingControl();
		return $obj;
	}
	/**
	 * @return RecommendationControl
	 */
	function getRecommendationControl() {
		require_once("Store/Recommendation.php");
		$obj = new RecommendationControl();
		return $obj;
	}
	/**
	 * @return ReviewControl
	 */
	function getReviewControl() {
		require_once("Store/Review.php");
		$obj = new ReviewControl();
		return $obj;
	}
	/**
	 * @return RatingControl
	 */
	function getRatingControl() {
		require_once("Store/Rating.php");
		$obj = new RatingControl();
		return $obj;
	}
	/**
	 * @return SalesReportControl
	 */
	function getSalesReportControl() {
		require_once("Store/SalesData/Report.php");
		$obj = new SalesReportControl();
		return $obj;
	}
	/**
	 * @return SupplierControl
	 */
	function getSupplierControl() {
		require_once("Store/Supplier.php");
		$obj = new SupplierControl();
		return $obj;
	}
	/**
	 * @return ProductControl
	 */
	function getProductControl() {
		require_once("Store/Product.php");
		$obj = new ProductControl();
		return $obj;
	}
	/**
	 * @return ProductCategoryControl
	 */
	function getProductCategoryControl() {
		require_once("Store/ProductCategory.php");
		$obj = new ProductCategoryControl();
		return $obj;
	}
	/**
	 * @return StockItemControl
	 */
	function getStockItemControl() {
		require_once("Store/StockItem.php");
		$obj = new StockItemControl();
		return $obj;
	}

	/**
	 * @return StockItemReportControl
	 */
	function getStockItemReportControl() {
		require_once("Store/StockItem.php");
		$obj = new StockItemReportControl();
		return $obj;
	}

	/**
	 * @return TopProductReportControl
	 */
	function getTopProductReportControl() {
		require_once("Store/Product.php");
		$obj = new TopProductReportControl();
		return $obj;
	}

	/**
	 * @return ShoppingBasketControl
	 */
	function getShoppingBasketControl() {
		require_once("Store/ShoppingBasket.php");
		$obj = new ShoppingBasketControl();
		return $obj;
	}

	/**
	 * @return ShoppingBasketItemControl
	 */
	function getShoppingBasketItemControl() {
		require_once("Store/ShoppingBasketitem.php");
		$obj = new ShoppingBasketItemControl();
		return $obj;
	}

	/**
	 * @return ShoppingBasketSummary
	 */
	function getShoppingBasketSummary(&$shoppingBasket) {
		require_once("Store/ShoppingBasketItem.php");
		return new ShoppingBasketSummary($shoppingBasket);
	}

	/**
	 * @return TransactionControl
	 */
	function getTransactionControl() {
		require_once("Store/Transaction.php");
		return new TransactionControl(CoreFactory::getXPayControl());
	}
	
/*		
/**
	 * @return CustomTransactionControl

	function getCustomTransactionControl() {
		require_once($_SERVER["DOCUMENT_ROOT"] . "/includes/class/application/transaction.php");
		return new CustomTransactionControl(CoreFactory::getXPayControl(XPAY_ACCOUNT, XPAY_CERT));
	}
	*/
		
	/**
	 * @return RefundControl
	 */
	function getRefundControl() {
		require_once("Store/Refund.php");
		return new RefundControl(CoreFactory::getXPayControl());
	}
	
	/**
	 * @return VoucherControl
	 */
	function getVoucherControl() {
		require_once("Store/Voucher.php");
		$obj = new VoucherControl();
		return $obj;
	}
	
	/**
	 * @return ProductHtmlControl
	 */
	function getProductHtmlControl() {
		require_once("Store/StoreHtml.php");
		$obj = new ProductHtmlControl();
		return $obj;
	}
	
	/**
	 * @return LibraryItemControl
	 */
	function getLibraryItemControl() {
		require_once("Store/LibraryItem.php");
		$obj = new LibraryItemControl();
		return $obj;
	}
	
	/**
	 * @return SubscriptionControl
	 */
	function getSubscriptionControl() {
		require_once("Store/Subscription.php");
		$obj = new SubscriptionControl();
		return $obj;
	}
	
	/**
	 * @return SubscriptionItemControl
	 */
	function getSubscriptionItemControl() {
		require_once("Store/SubscriptionItem.php");
		$obj = new SubscriptionItemControl();
		return $obj;
	}

	/**
	 * Mobile Module Classes
	 */
	/**
	 * @return WapItemControl
	 */
	function getWapItemControl() {
		require_once("Mobile/WapItem.php");
		$obj = new WapItemControl();
		return $obj;
	}
	/**
	 * @return WapDownloadControl
	 */
	function getWapDownloadControl() {
		require_once("Mobile/WapDownload.php");
		$obj = new WapDownloadControl();
		return $obj;
	}
	
	/**
	 * Campaign Module Classes
	 */
	 /**
	 * @return CampaignControl
	 */
	function getCampaignControl() {
		require_once("Campaign/Campaign.php");
		$obj = new CampaignControl();
		return $obj;
	}
	/**
	 * @return CampaignResultControl
	 */
	function getCampaignResultControl() {
		require_once("Campaign/CampaignResult.php");
		$obj = new CampaignResultControl();
		return $obj;
	}
	/**
	 * @return CampaignHistoryControl
	 */
	function getCampaignHistoryControl() {
		require_once("Campaign/CampaignHistory.php");
		$obj = new CampaignHistoryControl();
		return $obj;
	}
	/**
	 * @return CampaignTemplateControl
	 */
	function getCampaignTemplateControl() {
		require_once("Campaign/CampaignTemplate.php");
		$obj = new CampaignTemplateControl();
		return $obj;
	}
	/**
	 * @return CampaignResultReportControl
	 */
	function getCampaignResultReportControl() {
		require_once("Campaign/CampaignResult.php");
		$obj = new CampaignResultReportControl();
		return $obj;
	}
	/**
	 * @return MailingListControl
	 */
	function getMailingListControl() {
		require_once("Campaign/MailingList.php");
		$obj = new MailingListControl();
		return $obj;
	}
	/**
	 * @return MailingListItemControl
	 */
	function getMailingListItemControl() {
		require_once("Campaign/MailingListItem.php");
		$obj = new MailingListItemControl();
		return $obj;
	}
	/**
	 * @return CampaignImageControl
	 */
	function getCampaignImageControl() {
		require_once("Campaign/CampaignImage.php");
		$obj = new CampaignImageControl();
		return $obj;
	}
	
	/**
	 * Dynamic Entity Module Classes
	 */

	/**
	 * @var EntityTemplateControl
	 */
	/**
	 * @return EntityTemplateControl
	 */
	function getEntityTemplateControl() {
		require_once("DynamicEntity/EntityTemplate.php");
		$obj = new EntityTemplateControl();
		return $obj;
	}
	
	/**
	 * @var EntityTemplateElementControl
	 */		
	/**
	 * @return EntityTemplateElementControl
	 */
	function getEntityTemplateElementControl() {
		require_once("DynamicEntity/EntityTemplateElement.php");
		$obj = new EntityTemplateElementControl();
		return $obj;
	}
	
	/**
	 * @var EntityControl
	 */
	/**
	 * @return EntityControl
	 */
	function getEntityControl() {
		require_once("DynamicEntity/Entity.php");
		$obj = new EntityControl();
		return $obj;
	}
			
	/**
	 * @var EntityValueControl
	 */
	/**
	 * @return EntityValueControl
	 */
	function getEntityValueControl() {
		require_once("DynamicEntity/EntityValue.php");
		$obj = new EntityValueControl();
		return $obj;
	}
	
	/**
	 * @var TagControl
	 */
	/**
	 * @return TagControl
	 */
	function getTagControl() {
		require_once("Tag/Tag.php");
		$obj = new TagControl();
		return $obj;
	}
	/**
	 * @var TagToDataControl
	 */
	/**
	 * @return TagToDataControl
	 */
	function getTagToDataControl() {
		require_once("Tag/TagToData.php");
		$obj = new TagToDataControl();
		return $obj;
	}
	
	/**
	 * @var ArticleControl
	 * @return TagControl
	 */
	function getArticleControl($type = null) {
		require_once("Article/Article.php");
		return new ArticleControl($type);
	}
}
