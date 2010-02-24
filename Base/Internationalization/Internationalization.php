<?php
/**
 * @package Base
 * @subpackage Internationalization
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 */

/**
 * Include Data.php so that DataControl can be extended.
 */
require_once("Atrox/Core/Data/Data.php");

 /**
 * Allows elements of the application to be in different languages.
 * @author David Folan (Clock Ltd) {@link mailto:david.folan@clock.co.uk david.folan@clock.co.uk }
 * @copyright Clock Limited 2010
 * @version 3.2 - $Revision$ - $Date$
 * @package Base
 * @subpackage Internationalization
 */
class InternationalizationControl extends DataControl{

	var $table = "Internationalization";
	var $key = "Id";
	var $sequence = "Internationalization_Id_seq";
	var $defaultOrder = "Identifier";
	var $languageCode = null;
	
	var $fullLanguageCodeArray = array(
		"af" => "Afrikaans",
		"sq" => "Albanian",
		"eu" => "Basque",
		"bg" => "Bulgarian",
		"be" => "Byelorussian",
		"ca" => "Catalan",
		"zh" => "Chinese",
		"zh-cn" => "Chinese/China",
		"zh-tw" => "Chinese/Taiwan",
		"zh-hk" => "Chinese/Hong Kong",
		"zh-sg" => "Chinese/singapore",
		"hr" => "Croatian",
		"cs" => "Czech",
		"da" => "Danish",
		"nl" => "Dutch",
		"nl-be" => "Dutch/Belgium",
		"en" => "English",
		"en-gb" => "English/United Kingdom",
		"en-us" => "English/United Satates",
		"en-au" => "English/Australian",
		"en-ca" => "English/Canada",
		"en-nz" => "English/New Zealand",
		"en-ie" => "English/Ireland",
		"en-za" => "English/South Africa",
		"en-jm" => "English/Jamaica",
		"en-bz" => "English/Belize",
		"en-tt" => "English/Trinidad",
		"et" => "Estonian",
		"fo" => "Faeroese",
		"fa" => "Farsi",
		"fi" => "Finnish",
		"fr" => "French",
		"fr-be" => "French/Belgium",
		"fr-fr" => "French/France",
		"fr-ch" => "French/Switzerland",
		"fr-ca" => "French/Canada",
		"fr-lu" => "French/Luxembourg",
		"gd" => "Gaelic",
		"gl" => "Galician",
		"de" => "German",
		"de-at" => "German/Austria",
		"de-de" => "German/Germany",
		"de-ch" => "German/Switzerland",
		"de-lu" => "German/Luxembourg",
		"de-li" => "German/Liechtenstein",
		"el" => "Greek",
		"hi" => "Hindi",
		"hu" => "Hungarian",
		"is" => "Icelandic",
		"id" => "Indonesian",
		"in" => "Indonesian",
		"ga" => "Irish",
		"it" => "Italian",
		"it-ch" => "Italian/ Switzerland",
		"ja" => "Japanese",
		"ko" => "Korean",
		"lv" => "Latvian",
		"lt" => "Lithuanian",
		"mk" => "Macedonian",
		"ms" => "Malaysian",
		"mt" => "Maltese",
		"no" => "Norwegian",
		"pl" => "Polish",
		"pt" => "Portuguese",
		"pt-br" => "Portuguese/Brazil",
		"rm" => "Rhaeto-Romanic",
		"ro" => "Romanian",
		"ro-mo" => "Romanian/Moldavia",
		"ru" => "Russian",
		"ru-mo" => "Russian /Moldavia",
		"gd" => "Scots Gaelic",
		"sr" => "Serbian",
		"sk" => "Slovack",
		"sl" => "Slovenian",
		"sb" => "Sorbian",
		"es" => "Spanish",
		"es-do" => "Spanish",
		"es-ar" => "Spanish/Argentina",
		"es-co" => "Spanish/Colombia",
		"es-mx" => "Spanish/Mexico",
		"es-es" => "Spanish/Spain",
		"es-gt" => "Spanish/Guatemala",
		"es-cr" => "Spanish/Costa Rica",
		"es-pa" => "Spanish/Panama",
		"es-ve" => "Spanish/Venezuela",
		"es-pe" => "Spanish/Peru",
		"es-ec" => "Spanish/Ecuador",
		"es-cl" => "Spanish/Chile",
		"es-uy" => "Spanish/Uruguay",
		"es-py" => "Spanish/Paraguay",
		"es-bo" => "Spanish/Bolivia",
		"es-sv" => "Spanish/El salvador",
		"es-hn" => "Spanish/Honduras",
		"es-ni" => "Spanish/Nicaragua",
		"es-pr" => "Spanish/Puerto Rico",
		"sx" => "Sutu",
		"sv" => "Swedish",
		"sv-fi" => "Swedish/Findland",
		"ts" => "Thai",
		"tn" => "Tswana",
		"tr" => "Turkish",
		"uk" => "Ukrainian",
		"ur" => "Urdu",
		"vi" => "Vietnamese",
		"xh" => "Xshosa",
		"ji" => "Yiddish",
		"zu" => "Zulu"
	);
	
	function init() {

		$this->fieldMeta["Id"] = new FieldMeta(
			"Id", "", FM_TYPE_INTEGER, null, FM_STORE_NEVER, false);

		$this->fieldMeta["Identifier"] = new FieldMeta(
			"Identifier", "", FM_TYPE_STRING, 255, FM_STORE_ALWAYS, false);

		$this->fieldMeta["LanguageCode"] = new FieldMeta(
			"Language Code", "", FM_TYPE_STRING, null, FM_STORE_ALWAYS, false);
					
		$this->fieldMeta["Content"] = new FieldMeta(
			"Content", "", FM_TYPE_STRING, 1000, FM_STORE_ALWAYS, true);
			
		$this->fieldMeta["Content"]->setFormatter(CoreFactory::getBodyTextFormatter());
		
		$this->fieldMeta["FileId"] = new FieldMeta(
			"FileId", "", FM_TYPE_BINARY, null, FM_STORE_ALWAYS, true);			

	}

	function getInternationalizedFile($identifier) {			
		$filter = CoreFactory::getFilter();
		$this->clearFilter();
		$filter->addConditional($this->table, "LanguageCode",  $this->getCurrentLanguage());
		$filter->addConditional($this->table, "Identifier", $identifier);			
		$this->setFilter($filter);
		if ($internationalized = $this->getNext()) {
			return $internationalized->get("FileId");
		} else {
			return false;
		}
	}

	//Alias of getInternationalized
	function getI18nFile($identifier) {
		return $this->getInternationalizedFile($identifier);
	}
	
	function getInternationalized($identifier) {
		$filter = CoreFactory::getFilter();
		$this->clearFilter();
		$filter->addConditional($this->table, "LanguageCode", $this->getCurrentLanguage());
		$filter->addConditional($this->table, "Identifier", $identifier);			
		$this->setFilter($filter);
		if ($internationalized = $this->getNext()) {
			return $internationalized->get("Content");
		} else {
			return false;
		}
	}

	//Alias of getInternationalized
	function getI18n($identifier) {
		return $this->getInternationalized($identifier);
	}

	function getFormattedInternationalized($identifier) {
		$filter = CoreFactory::getFilter();
		$this->clearFilter();
		$filter->addConditional($this->table, "LanguageCode", $this->getCurrentLanguage());
		$filter->addConditional($this->table, "Identifier", $identifier);			
		$this->setFilter($filter);
		if ($internationalized = $this->getNext()) {
			return $internationalized->getFormatted("Content");
		} else {
			return false;
		}
	}

	// Alias of getFormattedInternationalized
	function getFormattedI18n($identifier) {
		return $this->getFormattedInternationalized($identifier);
	}

	function getInternationalizedEntity($identifier) {		
		$filter = CoreFactory::getFilter();
		$this->clearFilter();
		$filter->addConditional($this->table, "LanguageCode", $this->getCurrentLanguage());
		$filter->addConditional($this->table, "Identifier", $identifier);			
		$this->setFilter($filter);
		
		return $internationalized = $this->getNext();			
	}
	
	// Alias of getInternationalizedEntity
	function getI18nEntity($identifier) {
		return $this->getInternationalizedEntity($identifier);
	}		
	
	function getDefaultLanguage($language) {
		//if $language is in the table ChosenLanguage setLanguage to it
		//else use default language (first in table)
		$chosenLanguagesControl = BaseFactory::getChosenLanguageControl();
		
		if ($chosenLanguage = $chosenLanguagesControl->itemByField($language, "LanguageCode")) {
			return $language;
		} else {
			$chosenLanguage = $chosenLanguagesControl->firstItem();
			return $chosenLanguage->get("LanguageCode");
		}
	}

	function findAcceptableLanaguage($languageArray) {
	
		$chosenLanguagesControl = BaseFactory::getChosenLanguageControl();
	
		foreach ($languageArray as $language) {
			if ($chosenLanguage = $chosenLanguagesControl->itemByField($language, "LanguageCode")) {
				return $language;
			}			
		}
		
		foreach ($languageArray as $language) {
			$language = explode("-", $language);		
			if ($chosenLanguage = $chosenLanguagesControl->itemByField($language[0], "LanguageCode")) {
				return $chosenLanguage->get("LanguageCode");
			}
		}
		
		$chosenLanguage = $chosenLanguagesControl->firstItem();
		return $chosenLanguage->get("LanguageCode");
	}

	function setLanguage($languageCode) {
		$this->languageCode = $languageCode;
	}
	
	function getCurrentLanguage() {
		if ($this->languageCode == null) {
			$chosenLanguage = "en";
			$chosenLanguagesControl = BaseFactory::getChosenLanguageControl();
			if ($chosenLanguage = $chosenLanguagesControl->firstItem()) {
				$chosenLanguage = $chosenLanguage->get("LanguageCode");
			}
			$this->setLanguage($chosenLanguage);
		}
		return $this->languageCode;
	}

	function setInternationalizedFilter(&$dataControl, $languageField = "LanguageCode") {
		$filter = CoreFactory::getFilter();
		$dataControl->clearFilter();
		$filter->addConditional($dataControl->table, $languageField, $this->getCurrentLanguage());
		$dataControl->setFilter($filter);
	}

	function setI18nFilter(&$dataControl, $languageField = "LanguageCode") {
		$this->setInternationalizedFilter($dataControl, $languageField);
	}	
}
