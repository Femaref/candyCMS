<?php

/**
 * Translate a string.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace CandyCMS\Helper;

use CandyCMS\Helper\AdvancedException as AdvancedException;
use sfYaml;

require_once 'lib/symfony_yaml/sfYaml.php';

class I18n {

	protected $_aI18n = array();
	static $oI18n;

	public function __construct($sLanguage) {
		$sLanguageFile = 'languages/' . $sLanguage . '/' . $sLanguage . '.language.yml';

		try {
			$this->_aI18n = sfYaml::load(file_get_contents($sLanguageFile));
		}
		catch (AdvancedException $e) {
			die('Unable to load language file.');
		}
	}

	public function getArray() {
		return $this->_aI18n;
	}

	public function get($sLanguagePart) {
		$aLang = preg_split("/[\s]*[.][\s]*/", $sLanguagePart);

		$mTemp = $this->_aI18n;
		foreach ($aLang as $sPart) {
			if (array_key_exists($sPart, $mTemp)) {
				$mTemp = & $mTemp[$sPart];
			}
		}

		return $mTemp;
	}

	public static function fetch($sLanguagePart) {
		self::$oI18n = new I18n(WEBSITE_LANGUAGE);
		return self::$oI18n->get($sLanguagePart);
	}
}