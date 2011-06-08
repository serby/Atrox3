<?php
/**
 * @package Core
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * @author Paul Serby {@link mailto:paul.serby@clock.co.uk paul.serby@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Core
 */
class CoreFactory {

	/**
	 * Returns a Application
	 * @return Application
	 */
	static function &getApplication($application = null) {
		static $singleton;
		if (isset($application)) {
			$singleton = $application;
			return $singleton;
		}
		require_once("Application.php");
		isset($singleton) || $singleton = new Application();
		return $singleton;
	}

	/**
	 * Returns a DatabaseControl
	 * @return DatabaseControl
	 */
	static function &getDatabaseControl() {
		static $singleton;
		require_once("Data/DataConnection.php");
		isset($singleton) || $singleton = new DatabaseControl();
		return $singleton;
	}

	/**
	 * Returns a ErrorControl
	 * @return ErrorControl
	 */
	static function &getErrorControl() {
		static $singleton;
		require_once("Error.php");
		isset($singleton) || $singleton = new ErrorControl();
		return $singleton;
	}

	/**
	 * Returns a Registry
	 * @return Registry
	 */
	static function getRegistry() {
		require_once("Registry.php");
		$obj = new Registry();
		return $obj;
	}

	/**
	 * Returns a Layout
	 * @return Layout
	 */
	static function getLayout($template) {
		require_once("Layout.php");
		$obj = new Layout($template);
		return $obj;
	}

	/**
	 * @return FileSystem
	 */
	static function getFileSystem() {
		require_once("System/FileSystem.php");
		$obj = new FileSystem();
		return $obj;
	}

	/**
	 * Returns a ArrayRelationFormatter
	 * @return ArrayRelationFormatter
	 */
	static function getArrayRelationFormatter(&$array, $default) {
		require_once("Formatting.php");
		return new ArrayRelationFormatter($array, $default);
	}

	/**
	 * Returns a SmsGateway
	 * @return SmsGateway
	 */
	static function getSmsGateway($country = "UK") {
		require_once("SmsGateway.php");
		return SmsGatewayFactory::getSmsGateway($country);
	}

	/**
	 * Returns a SmsSubscriptionControl
	 * @return SmsSubscriptionControl
	 */
	static function getSmsSubscriptionControl() {
		require_once("SmsSubscription.php");
		$obj = new SmsSubscriptionControl();
		return $obj;
	}

	/**
	 * Returns a SmsControl
	 * @return SmsControl
	 */
	static function getSmsControl() {
		require_once("Sms.php");
		$obj = new SmsControl();
		return $obj;
	}

	/**
	 * Returns a PremiumSmsControl
	 * @return PremiumSmsControl
	 */
	static function getPremiumSmsControl() {
		require_once("Sms.php");
		$obj = new PremiumSmsControl();
		return $obj;
	}

	/**
	 * Returns a CssCollector
	 *
	 * @return ResourceAggregator
	 */
	static function getCssAggregator($sitePath, $cachePath, $cacheUrl = null) {
		static $singleton;
		if (!isset($singleton)) {
			require_once("Internet/ResourceAggregator.php");
			require_once("Internet/CssResourceAggregator.php");

			$delegate = new CssResourceAggregator();
			$minifier = CoreFactory::getCssMinifier();

			$singleton = new ResourceAggregator($sitePath, $cachePath, $cacheUrl, $delegate, $minifier);
		}
		return $singleton;
	}


	/**
	 * Returns a CheetahmailServiceAdaptor
	 *
	 * @return CheetahmailServiceAdaptor
	 */
	static function getCheetahmailServiceAdaptor($userName, $password, $affiliateId = null,
			HttpRequest $request, $host = "ebm.cheetahmail.com", $port = 80) {
		require_once "Internet/Cheetahmail/CheetahmailServiceAdaptor.php";
		$object = new CheetahmailServiceAdaptor($userName, $password, $affiliateId = null, $request,p $host, $port);
		return $object;
	}



	/**
	 * Returns a Js Resource Aggregator
	 *
	 * @return ResourceAggregator
	 */
	static function getJsAggregator($sitePath, $cachePath, $cacheUrl = null) {
		static $singleton;
		if (!isset($singleton)) {
			require_once("Internet/ResourceAggregator.php");
			require_once("Internet/JsResourceAggregator.php");

			$delegate = new JsResourceAggregator();
			$minifier = CoreFactory::getJsMinifier();

			$singleton = new ResourceAggregator($sitePath, $cachePath, $cacheUrl, $delegate, $minifier);
		}
		return $singleton;
	}

	/**
	 * Returns a Routing
	 * @return Router
	 */
	static function getRouter() {
		require_once("Routing/Router.php");
		$obj = new Router();
		return $obj;
	}

	/**
	 * Returns a MathControl
	 * @return MathControl
	 */
	static function getMathControl() {
		require_once("Math.php");
		$obj = new MathControl();
		return $obj;
	}

	/**
	 * Returns a NetworkControl
	 * @return NetworkControl
	 */
	static function getNetworkControl() {
		require_once("Network.php");
		$obj = new NetworkControl();
		return $obj;
	}

	/**
	 * Returns a CookieControl
	 * @return CookieControl
	 */
	static function getCookieControl() {
		require_once("Network.php");
		$obj = new CookieControl();
		return $obj;
	}


	/**
	 * Returns a AutoDataControl
	 * @return AutoDataControl
	 */
	static function getAutoDataControl() {
		require_once("AutoData.php");
		$obj = new AutoDataControl();
		return $obj;
	}

	/**
	 * Returns a BinaryControl
	 * @return BinaryControl
	 */
	static function getBinaryControl() {
		require_once("Data/Binary.php");
		$obj = new BinaryControl();
		return $obj;
	}

	/**
	 * @return CacheControl
	 */
	static function &getCacheControl() {
		static $singleton;
		require_once("Cache/Cache.php");
		isset($singleton) || $singleton = new CacheControl();
		return $singleton;
	}

	/**
	 * @return MemcachedControl
	 */
	static function &getMemcachedControl($keyPrefix = null) {
		static $singleton;
		require_once("Cache/Memcached.php");
		isset($singleton) || $singleton = new MemcachedControl($keyPrefix);
		return $singleton;
	}

	/**
	 * @return NoCacheControl
	 */
	static function &getNoCacheControl($keyPrefix = null) {
		static $singleton;
		require_once("Cache/NoCache.php");
		isset($singleton) || $singleton = new NoCacheControl($keyPrefix);
		return $singleton;
	}

	/**
	 * Returns a HtmlControl
	 * @return HtmlControl
	 */
	static function getHtmlControl() {
		require_once("Internet/Html.php");
		$obj = new HtmlControl();
		return $obj;
	}

	/**
	 * Return HtmlFormControl
	 * @return HtmlControl
	 */
	static function getHtmlFormControl() {
		require_once("Internet/HtmlForm.php");
		$obj = new HtmlFormControl();
		return $obj;
	}

	/**
	 * Returns a HttpRequest
	 * @return HttpRequest
	 */
	static function getHttpRequest($url = null) {
		require_once("Internet/HttpRequest.php");
		$obj = new HttpRequest($url);
		return $obj;
	}

	/**
	 * Returns a MockHttpRequest
	 * @return MockHttpRequest
	 */
	static function getMockHttpRequest() {
		require_once("Internet/MockHttpRequest.php");
		$obj = new MockHttpRequest();
		return $obj;
	}

	/**
	 * @return UrlBuilder
	 */
	static function getUrlBuilder($url) {
		require_once("Internet/UrlBuilder.php");
		$obj = new UrlBuilder($url);
		return $obj;
	}

	/**
	 * Returns a GData
	 * @return GData
	 */
	static function getGData() {
		require_once("Internet/Google/GData.php");
		$obj = new GData();
		return $obj;
	}

	/**
	 * Returns a YouTube
	 * @return YouTube
	 */
	static function getYouTube() {
		require_once("Internet/YouTube/YouTube.php");
		$obj = new YouTube();
		return $obj;
	}

	/**
	 * @return OpenGraph
	 */
	static function getOpenGraph() {
		require_once "Internet/Facebook/OpenGraph.php";
		$obj = new OpenGraph();
		return $obj;
	}

	/**
	 * Returns a Mime
	 * @return Mime
	 */
	static function getMime() {
		require_once("Internet/Mime.php");
		$obj = new Mime();
		return $obj;
	}

	/**
	 * Returns a FormatterStack
	 * @return FormatterStack
	 */
	static function getFormatterStack($formatter = null) {
		require_once("Formatting.php");
		return new FormatterStack($formatter);
	}

	/**
	 * Returns a PrettyUrlFormatter
	 * @return PrettyUrlFormatter
	 */
	static function getPrettyUrlFormatter($formatter = null) {
		require_once("Formatting.php");
		$obj = new PrettyUrlFormatter();
		return $obj;
	}

	/**
	 * Returns a FormatterStack
	 * @return FormatterStack
	 */
	static function getDasherizeFormatter() {
		require_once("Formatting.php");
		$obj = new DasherizeFormatter();
		return $obj;
	}

	/**
	 * Returns a FormatterStack
	 * @return UrlEncoder
	 */
	static function getUrlSlashEncoder() {
		require_once("Formatting.php");
		$obj = new UrlSlashEncoder();
		return $obj;
	}

	/**
	 * Returns a HtmlSelectOptions
	 * @return HtmlSelectOptions
	 */
	static function getHtmlSelectOptions() {
		require_once("Formatting.php");
		$obj = new HtmlSelectOptions();
		return $obj;
	}

	/**
	 * Returns a Lists
	 * @return Lists
	 */
	static function getLists() {
		require_once("Lists.php");
		$obj = new Lists();
		return $obj;
	}

	/**
	 * Returns a XmlParser
	 * @return XmlParser
	 */
	static function getXmlParser() {
		require_once("Xml/Xml.php");
		$obj = new XmlParser();
		return $obj;
	}

	/**
	 * Returns a Email
	 * @return Email
	 */
	static function getEmail() {
		require_once("Internet/Email.php");
		$obj = new Email();
		return $obj;
	}

	/**
	 * Returns a BsmtpControl
	 * @return BsmtpControl
	 */
	static function getBsmtpControl($temporaryDirectory = null) {
		require_once("Internet/Bsmtp.php");
		return new BsmtpControl($temporaryDirectory);
	}

	/**
	 * Returns a Template
	 * @return Template
	 */
	static function getTemplate() {
		require_once("Template.php");
		$obj = new Template();
		return $obj;
	}

	/**
	 * Returns a ImageControl
	 * @return ImageControl
	 */
	static function getImageControl() {
		require_once("Graphic/Image.php");
		$obj = new ImageControl();
		return $obj;
	}

	/**
	 * Returns a AutoTitleControl
	 * @return AutoTitleControl
	 */
	static function getAutoTitleControl($options = null) {
		require_once("Graphic/AutoTitle.php");
		return new AutoTitleControl($options);
	}

	/**
	 * Returns a ColorControl
	 * @return ColorControl
	 */
	static function getColorControl() {
		require_once("Graphic/Color.php");
		$obj = new ColorControl();
		return $obj;
	}

	/**
	 * Returns a VideoControl
	 * @return VideoControl
	 */
	static function getVideoControl() {
		require_once("Graphic/Video.php");
		$obj = new VideoControl();
		return $obj;
	}

	/**
	 * Returns a ImageValidation
	 * @return ImageValidation
	 */
	static function getImageValidation($minWidth = null, $minHeight = null, $maxWidth = null, $maxHeight = null, $widthAspect = null, $heightAspect = null) {
		require_once("Graphic/Image.php");
		return new ImageValidation($minWidth, $minHeight, $maxWidth, $maxHeight, $widthAspect, $heightAspect);
	}

	/**
	 * Returns a MobileValidation
	 * @return MobileValidation
	 */
	static function getMobileValidation($mobileOnly = false) {
		require_once("Data/Validation.php");
		return new MobileValidation($mobileOnly);
	}

	/**
	 * Returns a NotContainValidation
	 * @return NotContainValidation
	 */
	static function getNotContainValidation($phrase) {
		require_once("Data/Validation.php");
		return new NotContainValidation($phrase);
	}

	/**
	 * Returns a UrlEncoder
	 * @return UrlEncoder
	 */
	static function getUrlEncoder($append = false) {
		require_once("Formatting.php");
		return new UrlEncoder($append);
	}

	/**
	 * Returns a UrlDecoder
	 * @return UrlDecoder
	 */
	static function getUrlDecoder() {
		require_once("Formatting.php");
		$obj = new UrlDecoder();
		return $obj;
	}


	/**
	 * Returns a UrlFieldEncoder
	 * @return UrlFieldEncoder
	 */
	static function getUrlFieldEncoder() {
		require_once("Formatting.php");
		$obj = new UrlFieldEncoder();
		return $obj;
	}

	/**
	 * Returns a PercentageFormatter
	 * @return PercentageFormatter
	 */
	static function getPercentageFormatter() {
		require_once("Formatting.php");
		$obj = new PercentageFormatter();
		return $obj;
	}

	/**
	 * Returns a PercentageEncoder
	 * @return PercentageEncoder
	 */
	static function getPercentageEncoder() {
		require_once("Formatting.php");
		$obj = new PercentageEncoder();
		return $obj;
	}

	/**
	 * Returns a StringEncoder
	 * @return StringEncoder
	 */
	static function getStringEncoder() {
		require_once("Formatting.php");
		$obj = new StringEncoder();
		return $obj;
	}

	/**
	 * Returns a StringFormatter
	 * @return StringFormatter
	 */
	static function getStringFormatter() {
		require_once("Formatting.php");
		$obj = new StringFormatter();
		return $obj;
	}

	/**
	 * Returns a HtmlEncoder
	 * @return HtmlEncoder
	 */
	static function getHtmlEncoder() {
		require_once("Formatting.php");
		$obj = new HtmlEncoder();
		return $obj;
	}


	/**
	 * Returns a SafeHtmlEncoder
	 * @return SafeHtmlEncoder
	 */
	static function getSafeHtmlEncoder() {
		require_once("Formatting.php");
		$obj = new SafeHtmlEncoder();
		return $obj;
	}

	/**
	 * Returns a SafeHtmlEncoder
	 * @return SafeHtmlEncoder
	 */
	static function getJavascriptStringEncoder() {
		require_once("Formatting.php");
		$obj = new JavascriptStringEncoder();
		return $obj;
	}

	/**
	 * Returns a StringLowerEncoder
	 * @return StringLowerEncoder
	 */
	static function getStringLowerEncoder() {
		require_once("Formatting.php");
		$obj = new StringLowerEncoder();
		return $obj;
	}

	/**
	 * Returns a BooleanEncoder
	 * @return BooleanEncoder
	 */
	static function getBooleanEncoder() {
		require_once("Formatting.php");
		$obj = new BooleanEncoder();
		return $obj;
	}

	/**
	 * Returns a BooleanFormatter
	 * @return BooleanFormatter
	 */
	static function getBooleanFormatter() {
		require_once("Formatting.php");
		$obj = new BooleanFormatter();
		return $obj;
	}

	/**
	 * Returns a LeftCropEncoder
	 * @return LeftCropEncoder
	 */
	static function getLeftCropEncoder($length) {
		require_once("Formatting.php");
		return new LeftCropEncoder($length);
	}

	/**
	 * Returns a LeftCropEllipsisEncoder
	 * @return LeftCropEllipsisEncoder
	 */
	static function getLeftCropEllipsisEncoder($length, $flash = false) {
		require_once("Formatting.php");
		return new LeftCropEllipsisEncoder($length, $flash);
	}

	/**
	 * Returns a LeftCropBreakEllipsisEncoder
	 * @return LeftCropBreakEllipsisEncoder
	 */
	static function getLeftCropBreakEllipsisEncoder($length, $flash = false) {
		require_once("Formatting.php");
		return new LeftCropBreakEllipsisEncoder($length, $flash);
	}

	/**
	 * Returns a LeftCropWordEllipsisEncoder
	 * @return LeftCropWordEllipsisEncoder
	 */
	static function getLeftCropWordEllipsisEncoder($length, $flash = false) {
		require_once("Formatting.php");
		return new LeftCropWordEllipsisEncoder($length, $flash);
	}

	/**
	 * Returns a CurrencyEncoder
	 * @return CurrencyEncoder
	 */
	static function getCurrencyEncoder() {
		require_once("Formatting.php");
		$obj = new CurrencyEncoder();
		return $obj;
	}

	/**
	 * Returns a IntegerEncoder
	 * @return IntegerEncoder
	 */
	static function getIntegerEncoder() {
		require_once("Formatting.php");
		$obj = new IntegerEncoder();
		return $obj;
	}

	/**
	 * Returns an instance of JsonEncoder
	 * @return JsonEncoder
	 */
	static function getJsonEncoder() {
		require_once("Formatting.php");
		$obj = new JsonEncoder();
		return $obj;
	}

	/**
	 * Returns an instance of JsonDecoder
	 * @return JsonDecoder
	 */
	static function getJsonDecoder() {
		require_once("Formatting.php");
		$obj = new JsonDecoder();
		return $obj;
	}

	/**
	 * Returns a IntegerFormatter
	 * @return IntegerFormatter
	 */
	static function getIntegerFormatter() {
		require_once("Formatting.php");
		$obj = new IntegerFormatter();
		return $obj;
	}

	/**
	 * Returns a RealFormatter
	 * @return RealFormatter
	 */
	static function getRealFormatter() {
		require_once("Formatting.php");
		$obj = new RealFormatter();
		return $obj;
	}

	/**
	 * Returns a RealWithTrailingZerosFormatter
	 * @return RealWithTrailingZerosFormatter
	 */
	static function getRealWithTrailingZerosFormatter() {
		require_once("Formatting.php");
		$obj = new RealWithTrailingZerosFormatter();
		return $obj;
	}

	/**
	 * Returns a PasswordEncoder
	 * @return PasswordEncoder
	 */
	static function getPasswordEncoder() {
		require_once("Formatting.php");
		$obj = new PasswordEncoder();
		return $obj;
	}

	/**
	 * Returns a Filter
	 * @return Filter
	 */
	static function getFilter() {
		require_once("Data/Filter.php");
		$obj = new Filter();
		return $obj;
	}

	/**
	 * Returns a StringUpperEncoder
	 * @return StringUpperEncoder
	 */
	static function getStringUpperEncoder() {
		require_once("Formatting.php");
		$obj = new StringUpperEncoder();
		return $obj;
	}

	/**
	 * Returns a DatabaseDateEncoder
	 * @return DatabaseDateEncoder
	 */
	static function getDatabaseDateEncoder() {
		require_once("Formatting.php");
		$obj = new DatabaseDateEncoder();
		return $obj;
	}

	/**
	 * Returns a FuzzyTimeFormatter
	 * @return FuzzyTimeFormatter
	 */
	static function getFuzzyTimeFormatter() {
		require_once("Formatting.php");
		$obj = new FuzzyTimeFormatter();
		return $obj;
	}

	/**
	 * Returns a SecondsFormatter
	 * @return SecondsFormatter
	 */
	static function getSecondsFormatter() {
		require_once("Formatting.php");
		$obj = new SecondsFormatter();
		return $obj;
	}

	/**
	 * Returns a DatabaseDateTimeEncoder
	 * @return DatabaseDateTimeEncoder
	 */
	static function getDatabaseDateTimeEncoder() {
		require_once("Formatting.php");
		$obj = new DatabaseDateTimeEncoder();
		return $obj;
	}

	/**
	 * Returns a ManyToMany
	 * @return ManyToMany
	 */
	static function getManyToMany($table, $linkTable, $pKey, $fKey, $control) {
		require_once("Data/ManyToMany.php");
		return new ManyToMany($table, $linkTable, $pKey, $fKey, $control);
	}

	/**
	 * Returns a CurrentMember
	 * @return CurrentMember
	 */
	static function getCurrentMember() {
		require_once("AutoData.php");
		$obj = new CurrentMember();
		return $obj;
	}

	/**
	 * Returns a QueryString
	 * @return QueryString
	 */
	static function getQueryString() {
		require_once("Internet/QueryString.php");
		$obj = new QueryString();
		return $obj;
	}

	/**
	 * Returns a DataEntity
	 * @return DataEntity
	 */
	static function getDataEntity(&$object) {
		require_once("Data/DataEntity.php");
		return new DataEntity($object);
	}

	/**
	 * Returns a SecurityControl
	 * @return SecurityControl
	 */
	static function getSecurityControl($customFields = null) {
		require_once("Security.php");
		return new SecurityControl($customFields);
	}

	/**
	 * Returns a DateTimeFieldFormatter
	 * @return DateTimeFieldFormatter
	 */
	static function getDateTimeFieldFormatter($nullValue = "Not Set") {
		require_once("Formatting.php");
		return new DateTimeFieldFormatter($nullValue);
	}

	/**
	 * Returns a DateTimeFieldEncoder
	 * @return DateTimeFieldEncoder
	 */
	static function getDateTimeFieldEncoder($nullValue = "Not Set") {
		require_once("Formatting.php");
		return new DateTimeFieldEncoder($nullValue);
	}

	/**
	 * Returns a TimeFieldFormatter
	 * @return TimeFieldFormatter
	 */
	static function getTimeFieldFormatter($nullValue = "Not Set") {
		require_once("Formatting.php");
		return new TimeFieldFormatter($nullValue);
	}

	/**
	 * Returns a DecimalTimeFieldFormatter
	 * @return DecimalTimeFieldFormatter
	 */
	static function getDecimalTimeFieldFormatter($nullValue = "Not Set") {
		require_once("Formatting.php");
		return new DecimalTimeFieldFormatter($nullValue);
	}

	/**
	 * Returns a MonthYearFieldFormatter
	 * @return MonthYearFieldFormatter
	 */
	static function getMonthYearFieldFormatter($nullValue = "Not Set") {
		require_once("Formatting.php");
		return new MonthYearFieldFormatter($nullValue);
	}

	/**
	 * Returns a BodyTextFormatter
	 * @return BodyTextFormatter
	 */
	static function getBodyTextFormatter() {
		require_once("Formatting.php");
		$obj = new BodyTextFormatter();
		return $obj;
	}

	/**
	 * @return ImageResizeAndCropEncoder
	 */
	static function getImageResizeAndCropEncoder($width, $height) {
		require_once("Formatting.php");
		return new ImageResizeAndCropEncoder($width, $height);
	}

/**
	 * @return ImageResizeAndCropEncoder
	 */
	static function getImageResizeEncoder($width, $height) {
		require_once("Formatting.php");
		return new ImageResizeEncoder($width, $height);
	}

	/**
	 * Returns a DateTimeEncoder
	 * @return DateTimeEncoder
	 */
	static function getDateTimeEncoder() {
		require_once("Formatting.php");
		$obj = new DateTimeEncoder();
		return $obj;
	}

	/**
	 * Returns a CSS Minifier processor
	 * @return CssMinifier
	 */
	static function getCssMinifier() {
		require_once("Data/Processor/CssMinifier.php");
		$obj = new CssMinifier();
		return $obj;
	}

	/**
	 * Returns a JS Minifier processor
	 * @return JsMinifier
	 */
	static function getJsMinifier() {
		require_once("Data/Processor/JsMinifier.php");
		$obj = new JsMinifier();
		return $obj;
	}

	/**
	 * Returns a DocumentFormatter
	 * @return DocumentFormatter
	 */
	static function getDocumentFormatter() {
		require_once("Formatting.php");
		$obj = new DocumentFormatter();
		return $obj;
	}

	/**
	 * Returns a SimpleMarkup
	 * @return SimpleMarkup
	 */
	static function getSimpleDocumentMarkupFormatter() {
		require_once("Formatting.php");
		$obj = new SimpleDocumentMarkupFormatter();
		return $obj;
	}

	/**
	 * Returns a SimpleDocumentMarkupWithTableOfContentsParser
	 * @return SimpleDocumentMarkupWithTableOfContentsParser
	 */
	static function getSimpleDocumentMarkupWithTableOfContentsFormatter() {
		require_once("Formatting.php");
		$obj = new SimpleDocumentMarkupWithTableOfContentsFormatter();
		return $obj;
	}

	/**
	 * Returns a SimpleMarkup
	 * @return SimpleMarkup
	 */
	static function getSimpleDocumentMarkupWithTableOfContentsParser() {
		require_once("Internet/SimpleDocumentMarkupWithTableOfContentsParser.php");
		$obj = new SimpleDocumentMarkupWithTableOfContentsParser();
		return $obj;
	}

	/**
	 * Returns a SimpleMarkup
	 * @return SimpleMarkup
	 */
	static function getSimpleDocumentMarkupParser() {
		require_once("Internet/SimpleDocumentMarkupParser.php");
		$obj = new SimpleDocumentMarkupParser();
		return $obj;
	}

	/**
	 * Returns a ClockDocumentParser
	 * @return ClockDocumentParser
	 */
	static function getDocumentMarkupParser() {
		require_once("Internet/DocumentMarkup.php");
		$obj = new DocumentMarkupParser();
		return $obj;
	}

	/**
	 * @return Captcha
	 */
	static function getCaptcha() {
		require_once("Internet/Captcha.php");
		$obj = new Captcha();
		return $obj;
	}

	/**
	 * Returns a RealEncoder
	 * @return RealEncoder
	 */
	static function getRealEncoder() {
		require_once("Formatting.php");
		$obj = new RealEncoder();
		return $obj;
	}

	/**
	 * Returns a PlainCurrencyFormatter
	 * @return PlainCurrencyFormatter
	 */
	static function getPlainCurrencyFormatter() {
		require_once("Formatting.php");
		$obj = new PlainCurrencyFormatter();
		return $obj;
	}

	/**
	 * Returns a CurrencyFormatter
	 * @return CurrencyFormatter
	 */
	static function getCurrencyFormatter() {
		require_once("Formatting.php");
		$obj = new CurrencyFormatter();
		return $obj;
	}

	/**
	 * Returns a CurrencyNoUnitsFormatter
	 * @return CurrencyNoUnitsFormatter
	 */
	static function getCurrencyNoUnitsFormatter() {
		require_once("Formatting.php");
		$obj = new CurrencyNoUnitsFormatter();
		return $obj;
	}

	/**
	 * Returns a HtmlMultiPageControlResultsFormatter
	 * @return HtmlMultiPageControlResultsFormatter
	 */
	static function getHtmlMultiPageControlResultsFormatter() {
		require_once("Formatting.php");
		$obj = new HtmlMultiPageControlResultsFormatter();
		return $obj;
	}

	/**
	 * Returns a SimpleHtmlMultiPageControlResultsFormatter
	 * @return SimpleHtmlMultiPageControlResultsFormatter
	 */
	static function getSimpleHtmlMultiPageControlResultsFormatter() {
		require_once("Formatting.php");
		$obj = new SimpleHtmlMultiPageControlResultsFormatter();
		return $obj;
	}

	/**
	 * Returns a PreviousNextHtmlMultiPageControlResultsFormatter
	 * @return PreviousNextHtmlMultiPageControlResultsFormatter
	 */
	static function getPreviousNextHtmlMultiPageControlResultsFormatter() {
		require_once("Formatting.php");
		$obj = new PreviousNextHtmlMultiPageControlResultsFormatter();
		return $obj;
	}

	/**
	 * Returns a SimpleHtmlMultiPageControlResultsFormatter
	 * @return SimpleHtmlMultiPageControlResultsFormatter
	 */
	static function getForwardBackwardsControlResultsFormatter() {
		require_once("Formatting.php");
		$obj = new ForwardBackwardsControlResultsFormatter();
		return $obj;
	}

	/**
	 * Returns a IsbnValidation
	 * @return IsbnValidation
	 */
	static function getIsbnValidation() {
		require_once("Data/Validation.php");
		$obj = new IsbnValidation();
		return $obj;
	}

	/**
	 * Returns a Mod10Validation
	 * @return Mod10Validation
	 */
	static function getMod10Validation() {
		require_once("Data/Validation.php");
		$obj = new Mod10Validation();
		return $obj;
	}

	/**
	 * Returns a MustAgreeValidation
	 * @return MustAgreeValidation
	 */
	static function getMustAgreeValidation() {
		require_once("Data/Validation.php");
		$obj = new MustAgreeValidation();
		return $obj;
	}

	/**
	 * Returns a StrongPasswordValidation
	 * @return StrongPasswordValidation
	 */
	static function getStrongPasswordValidation() {
		require_once("Data/Validation.php");
		$obj = new StrongPasswordValidation();
		return $obj;
	}

	/**
	 * Returns a WeakPasswordValidation
	 * @return WeakPasswordValidation
	 */
	static function getWeakPasswordValidation() {
		require_once("Data/Validation.php");
		$obj = new WeakPasswordValidation();
		return $obj;
	}

	/**
	 * Returns a CardNumberFormatter
	 * @return CardNumberFormatter
	 */
	static function getCardNumberFormatter() {
		require_once("Formatting.php");
		$obj = new CardNumberFormatter();
		return $obj;
	}

	/**
	 * Returns a DateFieldFormatter
	 * @return DateFieldFormatter
	 */
	static function getDateFieldFormatter($nullValue = "Not set") {
		require_once("Formatting.php");
		return new DateFieldFormatter($nullValue);
	}

	/**
	 * Returns a PadFormatter
	 * @return PadFormatter
	 */
	static function getPadFormatter($amount, $padding, $prefix = null, $padType = null) {
		require_once("Formatting.php");
		return new PadFormatter($amount, $padding, $padType, $prefix);
	}

	/**
	 * Returns a ConCatFormatter
	 * @return ConCatFormatter
	 */
	static function getConCatFormatter($prefix = null, $postfix = null) {
		require_once("Formatting.php");
		return new ConCatFormatter($prefix, $postfix);
	}

	/**
	 * Returns a ArrayDateTimeEncoder
	 * @return ArrayDateTimeEncoder
	 */
	static function getArrayDateTimeEncoder() {
		require_once("Formatting.php");
		$obj = new ArrayDateTimeEncoder();
		return $obj;
	}

	/**
	 * Returns a ArrayDateEncoder
	 * @return ArrayDateEncoder
	 */
	static function getArrayDateEncoder() {
		require_once("Formatting.php");
		$obj = new ArrayDateEncoder();
		return $obj;
	}

	/**
	 * Returns a ArrayMonthYearEncoder
	 * @return ArrayMonthYearEncoder
	 */
	static function getArrayMonthYearEncoder() {
		require_once("Formatting.php");
		$obj = new ArrayMonthYearEncoder();
		return $obj;
	}

	/**
	 * Returns a MonthYearEncoder
	 * @return MonthYearEncoder
	 */
	static function getMonthYearEncoder() {
		require_once("Formatting.php");
		$obj = new MonthYearEncoder();
		return $obj;
	}

	/**
	 * Returns a IpAddress
	 * @return IpAddress
	 */
	static function getIpAddress() {
		require_once("AutoData.php");
		$obj = new IpAddress();
		return $obj;
	}

	/**
	 * Returns a XPayControl.
	 * Added define for 3D Secure system - Ed. 29/03/2007
	 * @return XPayControl
	 */
	static function getXPayControl($siteReference = null, $certificatePath = null, $port = 5000, $host = "127.0.0.1") {
		$application = CoreFactory::getApplication();

		if ($siteReference == null) {
			$siteReference = $application->registry->get("Payment/XPay/Account", "testclock5681");
		}
		if ($certificatePath == null) {
			$certificatePath = $application->registry->get("Payment/XPay/Certificate", "/webpub/xpaycerts/testclock5681testcerts.pem");
		}
		if (defined("THREEDSECURE") && (THREEDSECURE == true)) {
			require_once("Payment/XPay3d.php");
			return new CustomXPayControl($siteReference, $certificatePath, $port, $host);
		} else {
			require_once("Payment/XPay.php");
			return new XPayControl($siteReference, $certificatePath, $port, $host);
		}
	}

	/**
	 * Returns a Uid
	 * @return Uid
	 */
	static function getUid() {
		require_once("AutoData.php");
		$obj = new Uid();
		return $obj;
	}

	/**
	 * Returns a Guid
	 * @return Guid
	 */
	static function getGuid() {
		require_once("AutoData.php");
		$obj = new Guid();
		return $obj;
	}

	/**
	 * Returns a BackgroundJobControl
	 * @return BackgroundJobControl
	 */
	static function getBackgroundJobControl() {
		require_once("backgroundjob.php");
		$obj = new BackgroundJobControl();
		return $obj;
	}


	/**
	 * Returns a TimezoneControl
	 * @return TimezoneControl
	 */
	static function getTimezoneControl() {
		require_once("timezone.php");
		$obj = new TimezoneControl();
		return $obj;
	}

	/**
	 * Returns a RssFeeder
	 * @return RssFeeder
	 */
	static function getRssFeeder($dataControl, $title, $description, $link, $imageLink, $itemProcessor, $selfLink = null) {
		require_once("Internet/Rss.php");
		return new RssFeeder($dataControl, $title, $description, $link, $imageLink, $itemProcessor, $selfLink);
	}

	/**
	 * Returns a RssReader
	 * @return RssReader
	 */
	static function getRssReader($url = null, $expiryTime = null) {
		require_once("Internet/Rss.php");
		return new RssReader($url, $expiryTime);
	}

	/**
	 * Returns a DateTimeAutoData
	 * @return DateTimeAutoData
	 */
	static function getDateTimeAutoData() {
		require_once("AutoData.php");
		$obj = new DateTimeAutoData();
		return $obj;
	}

	/**
	 * Returns utc DateTimeAutoData
	 * @return UtcDateTimeAutoData
	 */
	static function getUtcDateTimeAutoData() {
		require_once("AutoData.php");
		$obj = new UtcDateTimeAutoData();
		return $obj;
	}

	/**
	 * Returns a EmailFieldFormatter
	 * @return EmailFieldFormatter
	 */
	static function getEmailFieldFormatter() {
		require_once("Formatting.php");
		$obj = new EmailFieldFormatter();
		return $obj;
	}

	/**
	 * Returns a UrlFieldFormatter
	 * @return UrlFieldFormatter
	 */
	static function getUrlFieldFormatter() {
		require_once("Formatting.php");
		$obj = new UrlFieldFormatter();
		return $obj;
	}

	/**
	 * Returns a Graph
	 * @return Graph
	 */
	static function getGraph() {
		require_once("Graph.php");
		$obj = new Graph();
		return $obj;
	}

	/**
	 * Returns a PostcodeLocator
	 * @return PostcodeLocator
	 */
	static function getPostcodeLocator() {
		require_once("Geo.php");
		$obj = new PostcodeLocator();
		return $obj;
	}

	/**
	 * Returns a GoogleMapsAddressFinder
	 * @return GoogleMapsAddressFinder
	 */
	static function getGoogleMapsAddressFinder() {
		require_once("Geo.php");
		$obj = new GoogleMapsAddressFinder();
		return $obj;
	}

	/**
	 * Returns a IpToCountry
	 * @return IpToCountry
	 */
	static function getIpToCountry() {
		require_once("Geo.php");
		$obj = new IpToCountry();
		return $obj;
	}

	/**
	 * Returns a Mp3Control
	 * @return Mp3Control
	 */
	static function getMp3Control() {
		require_once("Mp3.php");
		$obj = new Mp3Control();
		return $obj;
	}
	/**
	 * Returns a Id3Control
	 * @return Id3Control
	 */
	static function getId3Control($filename, $study = false) {
		require_once("Mp3.php");
		return new Id3Control($filename, $study = false);
	}

	/**
	 * Returns a EncryptionControl
	 * @return EncryptionControl
	 */
	static function getEncryptionControl() {
		require_once("Encryption.php");
		$obj = new EncryptionControl();
		return $obj;
	}

	/**
	 * Returns a ArrayControl
	 * @return ArrayControl
	 */
	static function getArrayControl() {
		require_once("Utility/Array.php");
		$obj = new ArrayControl();
		return $obj;
	}

	/**
	 * @var String
	 */
	static function getString() {
		require_once("Utility/String.php");
		$obj = new String();
		return $obj;
	}

/**
 * Returns a DirectAdministrationControl
 * @return DirectAdministrationControl
 */

	static function &getDirectAdministrationControl() {
		static $singleton;
		require_once("DirectAdministration.php");
		isset($singleton) || $singleton = new DirectAdministrationControl();
		return $singleton;
	}

	/**
	 * Returns a TaggedDataFormatter
	 * @return TaggedDataFormatter
	 */
	static function getTaggedDataFormatter($wrapTag = null) {
		require_once("Formatting.php");
		return new TaggedDataFormatter($wrapTag);
	}

	/**
	 * Returns a TaggedDataArrayFormatter
	 * @return TaggedDataArrayFormatter
	 */
	static function getTaggedDataArrayFormatter() {
		require_once("Formatting.php");
		$obj = new TaggedDataArrayFormatter();
		return $obj;
	}

	/**
	 * Returns a TaggedDataFormatter
	 * @return TaggedDataFormatter
	 */
	static function getTaggedDataEncoder() {
		require_once("Formatting.php");
		$obj = new TaggedDataEncoder();
		return $obj;
	}

	/**
	 * Returns a Ftp
	 * @return Ftp
	 */
	static function getFtp($host, $username, $password) {
		require_once("Ftp/Ftp.php");
		return new Ftp($host, $username, $password);
	}

	/**
	 * @return SitemapGenerator
	 */
	static function getSitemapGenerator() {
		require_once "Internet/SitemapGenerator.php";
		return new SitemapGenerator();
	}
}
