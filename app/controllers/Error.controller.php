<?php

/**
 * Show customized error message when page is not found.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 */

namespace CandyCMS\Controller;

use CandyCMS\Helper\Helper as Helper;

require_once 'app/controllers/Search.controller.php';

class Error extends Main {

  /**
   * Show a 404 error when a page is not avaiable or found.
   *
   * @access public
   * @return string HTML content
   * @todo Some language stuff.
   *
   */
  public function show404() {
    $this->oSmarty->assign('lang_headline', $this->oI18n->get('error.404.title'));
    $this->oSmarty->assign('lang_info', $this->oI18n->get('error.404.info'));
    $this->oSmarty->assign('lang_subheadline', 'Meinten Sie vielleicht:');

    if (isset($this->_aRequest['seo_title'])) {
      $oSearch = new Search($this->_aRequest, $this->_aSession);
      $oSearch->__init();
      $this->oSmarty->assign('_search_', $oSearch->getSearch(urldecode($this->_aRequest['seo_title'])));
    }

    $this->oSmarty->template_dir = Helper::getTemplateDir('errors', '404');
    return $this->oSmarty->fetch('404.tpl');
  }
}