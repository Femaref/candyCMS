<?php

/*
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 */

class Index extends Main {

  protected $_aRequest;
  protected $_aSession;
  protected $_aFile;
  protected $_aCookie;

  private $_sTemplate;
  private $_sLanguage;

  public function __construct($aRequest, $aSession, $aFile = '', $aCookie = '') {
    # Set up our software
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;
    $this->_aFile     = & $aFile;
    $this->_aCookie 	= & $aCookie;

  }

  public function loadConfig($sPath = '') {
    # Essential config file
		try {
      if (!file_exists($sPath . 'config/Candy.inc.php'))
        throw new AdvancedException('Missing Candy.inc.php file.');
      else
        require_once $sPath . 'config/Candy.inc.php';
    }
    catch (AdvancedException $e) {
      die($e->getMessage());
    }

    # Optional facebook config. Used for apps and share buttons
		if (file_exists($sPath . 'config/Facebook.inc.php'))
			require_once $sPath . 'config/Facebook.inc.php';
	}


  public function loadPlugins($sPath = '') {
		try {
      if (!file_exists($sPath . 'config/Plugins.inc.php'))
        throw new AdvancedException('Missing plugin config file.');
      else
        require_once $sPath . 'config/Plugins.inc.php';
    }
    catch (AdvancedException $e) {
      die($e->getMessage());
    }

		$aPlugins = preg_split("/[\s]*[,][\s]*/", ALLOW_PLUGINS);

		foreach ($aPlugins as $sPluginName) {
			if (file_exists('plugins/controllers/' . (string) ucfirst($sPluginName) . '.controller.php'))
				require_once 'plugins/controllers/' . (string) ucfirst($sPluginName) . '.controller.php';
		}
	}

	# Give the users the ability to login via their facebook information
	public function loadFacebookExtension() {
		if (class_exists('FacebookCMS')) {
			return new FacebookCMS(array(
					'appId' => FACEBOOK_APP_ID,
					'secret' => FACEBOOK_SECRET,
					'cookie' => true,
			));
		}
	}

  public function setSkin() {
		# We got a template request? Let's change it!
    if (isset($this->_aRequest['template'])) {
      setcookie('default_template', (string) $this->_aRequest['template'], time() + 2592000, '/');
      $this->_sTemplate = (string) $this->_aRequest['template'];
    }
    # There is no request, but there might be a cookie instead
    else {
      $aRequest = isset($this->_aCookie) ? array_merge($this->_aRequest, $this->_aCookie) : $this->_aRequest;
      $this->_sTemplate = isset($aRequest['default_template']) ?
              (string) $aRequest['default_template'] :
              '';
    }
  }

  public function setLanguage($sPath = '') {
		# We got a language request? Let's change it!
		if (isset($this->_aRequest['language'])) {
			setcookie('default_language', (string) $this->_aRequest['language'], time() + 2592000, '/');
			$this->_sLanguage = (string) $this->_aRequest['language'];
		}
    # There is no request, but there might be a cookie instead
		else {
			$aRequest = isset($this->_aCookie) ? array_merge($this->_aRequest, $this->_aCookie) : $this->_aRequest;
			$this->_sLanguage = isset($aRequest['default_language']) ?
							(string) $aRequest['default_language'] :
							substr(DEFAULT_LANGUAGE, 0, 2);
		}

		# Set iso language codes
    switch ($this->_sLanguage) {
      default:
      case 'de':
        $sLocale = 'de_DE';
        break;

      case 'en':
        $sLocale = 'en_US';
        break;

      case 'es':
        $sLocale = 'es_ES';
        break;

      case 'fr':
        $sLocale = 'fr_FR';
        break;

      case 'pt':
        $sLocale = 'pt_PT';
        break;
    }

		define('WEBSITE_LANGUAGE',  $this->_sLanguage);
		define('WEBSITE_LOCALE',    $sLocale);

    # Include language if possible
		try {
      if (!file_exists($sPath . 'languages/' . $this->_sLanguage . '/' . $this->_sLanguage . '.language.php'))
        throw new AdvancedException('Missing language file.');
      else
        require_once $sPath . 'languages/' . $this->_sLanguage . '/' . $this->_sLanguage . '.language.php';
    }
    catch (AdvancedException $e) {
      die($e->getMessage());
    }
	}

  public function loadCronjob() {
    if (class_exists('Cronjob')) {
      if (Cronjob::getNextUpdate() === true) {
        Cronjob::cleanup();
        Cronjob::optimize();
        Cronjob::backup(USER_ID);
      }
    }
  }

  protected function _getFlashMessage() {
    $aFlashMessage['type'] = isset($this->_aSession['flash_message']['type']) && !empty($this->_aSession['flash_message']['type']) ?
            $this->_aSession['flash_message']['type'] :
            '';
    $aFlashMessage['message'] = isset($this->_aSession['flash_message']['message']) && !empty($this->_aSession['flash_message']['message']) ?
            $this->_aSession['flash_message']['message'] :
            '';
    $aFlashMessage['headline'] = isset($this->_aSession['flash_message']['headline']) && !empty($this->_aSession['flash_message']['headline']) ?
            $this->_aSession['flash_message']['headline'] :
            '';

    unset($_SESSION['flash_message']);
    return $aFlashMessage;
  }

  public function show() {
		# Redirect to landing page if we got no section
		if (!isset($this->_aRequest['section'])) {
			Helper::redirectTo('/' . WEBSITE_LANDING_PAGE);
			exit();
		}

		# Load JS language
    $sLangVars = '';
    $oFile = fopen('languages/' . $this->_sLanguage . '/' . $this->_sLanguage . '.language.js', 'rb');

    while (!feof($oFile)) {
      $sLangVars .= fgets($oFile);
    }

		# Header.tpl
		$oSmarty = $this->_setSmarty();
		$oSmarty->assign('user', USER_NAME);

		# Check for new version of script
		if (USER_RIGHT == 4 && ALLOW_VERSION_CHECK == true) {
      $oFile = @fopen('http://www.empuxa.com/misc/candycms/version.txt', 'rb');
      $sVersionContent = @stream_get_contents($oFile);
      @fclose($oFile);

      $sVersionContent &= ( $sVersionContent > VERSION ) ? (int) $sVersionContent : '';
    }

    $sLangUpdateAvaiable = isset($sVersionContent) && !empty($sVersionContent) ?
            str_replace('%v', $sVersionContent, LANG_GLOBAL_UPDATE_AVAIABLE) :
            '';
    $sLangUpdateAvaiable = str_replace('%l', Helper::createLinkTo('http://candycms.com', true), $sLangUpdateAvaiable);

		# System variables
		$oSmarty->assign('_javascript_language_file_', $sLangVars);
		$oSmarty->assign('_update_avaiable_', $sLangUpdateAvaiable);

		# Get possible flash messages
		$aFlashMessages = $this->_getFlashMessage();

		# Replace flash message with content
		$oSmarty->assign('_flash_type_', $aFlashMessages['type']);
		$oSmarty->assign('_flash_message_', $aFlashMessages['message']);
		$oSmarty->assign('_flash_headline_', $aFlashMessages['headline']);

		# Global language
		$oSmarty->assign('lang_newsletter_handle', LANG_NEWSLETTER_HANDLE_TITLE);
		$oSmarty->assign('lang_newsletter_create', LANG_NEWSLETTER_CREATE_TITLE);

		# Define out core modules. All of them are separately handled in app/helper/Section.helper.php
		if (!isset($this->_aRequest['section']) ||
						empty($this->_aRequest['section']) ||
						strtolower($this->_aRequest['section']) == 'blog' ||
						strtolower($this->_aRequest['section']) == 'comment' ||
						strtolower($this->_aRequest['section']) == 'content' ||
						strtolower($this->_aRequest['section']) == 'download' ||
						strtolower($this->_aRequest['section']) == 'error' ||
						strtolower($this->_aRequest['section']) == 'gallery' ||
						strtolower($this->_aRequest['section']) == 'log' ||
						strtolower($this->_aRequest['section']) == 'mail' ||
						strtolower($this->_aRequest['section']) == 'media' ||
						strtolower($this->_aRequest['section']) == 'newsletter' ||
            strtolower($this->_aRequest['section']) == 'rss' ||
            strtolower($this->_aRequest['section']) == 'search' ||
						strtolower($this->_aRequest['section']) == 'session' ||
            strtolower($this->_aRequest['section']) == 'sitemap' ||
            strtolower($this->_aRequest['section']) == 'static' ||
						strtolower($this->_aRequest['section']) == 'user') {

			$oSection = new Section($this->_aRequest, $this->_aSession, $this->_aFile);
			$oSection->getSection();
		}

		# We do not have a standard action, so fetch it from the addon folder.
		# If addon exists, proceed with override.
		elseif (ALLOW_ADDONS === true) {
      $oSection = new Addon($this->_aRequest, $this->_aSession, $this->_aFile);
      $oSection->getSection();
    }

    # Redirect to start page
    elseif (strtolower($this->_aRequest['section']) == 'start')
      Helper::redirectTo('/');

		# There's no request on a core module and Addons are disabled. */
		else {
			header('Status: 404 Not Found');
      Helper::redirectTo('/error/404');
    }

		# Avoid Header and Footer HTML if RSS or AJAX are requested
		if ((isset($this->_aRequest['section']) && 'rss' == strtolower($this->_aRequest['section'])) ||
						(isset($this->_aRequest['ajax']) && true == $this->_aRequest['ajax']))
			$sCachedHTML = $oSection->getContent();

		else {
			$oSmarty->assign('meta_expires', gmdate('D, d M Y H:i:s', time() + 60) . ' GMT');
			$oSmarty->assign('meta_description', $oSection->getDescription());
			$oSmarty->assign('meta_keywords', $oSection->getKeywords());
			$oSmarty->assign('meta_og_description', $oSection->getDescription());
			$oSmarty->assign('meta_og_site_name', WEBSITE_NAME);
			$oSmarty->assign('meta_og_title', $oSection->getTitle());
			$oSmarty->assign('meta_og_url', CURRENT_URL);

			$oSmarty->assign('_content_', $oSection->getContent());
      # We must recreate the request id because it's yet only set in the Main.controller.php
			$oSmarty->assign('_request_id_', isset($this->_aRequest['id']) ? (int)$this->_aRequest['id'] : '');
			$oSmarty->assign('_title_', $oSection->getTitle() . ' - ' . LANG_WEBSITE_TITLE);

			$oSmarty->template_dir = Helper::getTemplateDir('layouts', 'application');
			$sCachedHTML = $oSmarty->fetch('application.tpl');
		}

		# Build absolute Path because of pretty URLs
		$sCachedHTML = str_replace('%PATH_TEMPLATE%', PATH_TEMPLATE, $sCachedHTML);
		$sCachedHTML = str_replace('%PATH_PUBLIC%', WEBSITE_CDN . '/public', $sCachedHTML);
		$sCachedHTML = str_replace('%PATH_UPLOAD%', WEBSITE_URL . '/' . PATH_UPLOAD, $sCachedHTML);

		# Check for user custom css
		$sCachedCss = str_replace('%PATH_CSS%', WEBSITE_CDN . '/public/css', $sCachedHTML);
		if (!empty($this->_sTemplate))
			$sCachedCss = str_replace('%PATH_CSS%', WEBSITE_CDN . '/public/templates/' . $this->_sTemplate . '/css', $sCachedHTML);

		elseif (PATH_TEMPLATE !== '')
			$sCachedCss = str_replace('%PATH_CSS%', WEBSITE_CDN . '/public/templates/' . PATH_TEMPLATE . '/css', $sCachedHTML);

		$sCachedHTML = & $sCachedCss;

		# Check for user custom icons etc.
		$sCachedImages = str_replace('%PATH_IMAGES%', WEBSITE_CDN . '/public/images', $sCachedHTML);
		if (!empty($this->_sTemplate))
			$sCachedImages = str_replace('%PATH_IMAGES%', WEBSITE_CDN . '/public/templates/' . $this->_sTemplate . '/images', $sCachedHTML);

		elseif (PATH_TEMPLATE !== '')
			$sCachedImages = str_replace('%PATH_IMAGES%', WEBSITE_CDN . '/public/templates/' . PATH_TEMPLATE . '/images', $sCachedHTML);

		$sCachedHTML = & $sCachedImages;

		# Cut spaces to minimize filesize
		$sCachedHTML = str_replace('	', '', $sCachedHTML); # Normal tab
		$sCachedHTML = str_replace('  ', '', $sCachedHTML); # Tab as two spaces

		# Compress Data
		if (extension_loaded('zlib'))
			@ob_start('ob_gzhandler');

		return $sCachedHTML;
	}
}