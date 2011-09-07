<?php

/*
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 */

class Model_Newsletter extends Model_Main {

  # TODO: Why static
  public static function handleNewsletter($sEmail) {
    try {
      $oDb = new PDO('mysql:host=' . SQL_HOST . ';dbname=' . SQL_DB, SQL_USER, SQL_PASSWORD, array(
                PDO::ATTR_PERSISTENT => true));
      $oDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      $oQuery = $oDb->prepare("  SELECT
                                  email
                                FROM
                                  " . SQL_PREFIX . "newsletters
                                WHERE
                                  email = :email
                                LIMIT
                                  1");

      $oQuery->bindParam('email', $sEmail);
      $oQuery->execute();

      $aResult = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (AdvancedException $e) {
      $this->_oDb->rollBack();
    }

    if (isset($aResult) && !empty($aResult['email'])) {
      try {
        $oQuery = $oDb->prepare("  DELETE FROM
                                    " . SQL_PREFIX . "newsletters
                                  WHERE
                                    email = :email");

        $oQuery->bindParam('email', $sEmail);
        $bResult = $oQuery->execute();
        $oDb = null;

        if ($bResult === true)
          # UGLY: Needed for status message
          return 'DESTROY';
      }
      catch (AdvancedException $e) {
        $oDb->rollBack();
      }
    }
    else {
      try {
        $oQuery = $oDb->prepare("  INSERT INTO
                                    " . SQL_PREFIX . "newsletters (email)
                                  VALUES
                                    ( :email )");

        $oQuery->bindParam('email', $sEmail);
        $bResult = $oQuery->execute();
        $oDb = null;

        if ($bResult == true)
          # UGLY: Needed for status message
          return 'INSERT';
      }
      catch (AdvancedException $e) {
        $oDb->rollBack();
      }
    }
  }

  # TODO: Why static?
  public static function getNewsletterRecipients($sMySqlTable = 'newsletter') {
    if ($sMySqlTable == 'newsletter') {
      try {
        $oDb = new PDO('mysql:host=' . SQL_HOST . ';dbname=' . SQL_DB, SQL_USER, SQL_PASSWORD);
        $oDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $oQuery = $oDb->query("  SELECT
                                  email
                                FROM
                                  " . SQL_PREFIX . "newsletters");

        $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
        $oDb = null;
        return $aResult;
      }
      catch (AdvancedException $e) {
        $oDb->rollBack();
      }
    }
    elseif ($sMySqlTable == 'user') {
      try {
        $oDb = new PDO('mysql:host=' . SQL_HOST . ';dbname=' . SQL_DB, SQL_USER, SQL_PASSWORD);
        $oDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $oQuery = $oDb->query("  SELECT
                                  name, email
                                FROM
                                  " . SQL_PREFIX . "users
                                WHERE
                                  receive_newsletter = '1'");

        $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
        $oDb = null;
        return $aResult;
      }
      catch (AdvancedException $e) {
        $oDb->rollBack();
      }
    }
    # TODO: Kick off return?
    else
      return false;
  }
}