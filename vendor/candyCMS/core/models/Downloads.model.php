<?php

/**
 * Handle all download SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace CandyCMS\Core\Models;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\Pagination;
use CandyCMS\Core\Helpers\Upload;
use PDO;

class Downloads extends Main {

  /**
   * Get download data.
   *
   * @access public
   * @param integer $iId ID to get data from.
   * @param boolean $bUpdate prepare data for update
   * @return array data
   *
   */
  public function getData($iId = '', $bUpdate = false) {
    $aInts = array('id', 'author_id', 'downloads', 'uid');

    if (empty($iId)) {
      try {
        $oQuery = $this->_oDb->prepare("SELECT
                                          d.*,
                                          u.id AS user_id,
                                          u.name AS user_name,
                                          u.surname AS user_surname,
                                          u.email AS user_email
                                        FROM
                                          " . SQL_PREFIX . "downloads d
                                        LEFT JOIN
                                          " . SQL_PREFIX . "users u
                                        ON
                                          d.author_id=u.id
                                        ORDER BY
                                          d.category ASC,
                                          d.title ASC");

        $oQuery->execute();
        $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
      }
      catch (\PDOException $p) {
        AdvancedException::reportBoth('0032 - ' . $p->getMessage());
        exit('SQL error.');
      }

      foreach ($aResult as $aRow) {
        $iId = $aRow['id'];
        $sCategory = $aRow['category'];

        $this->_aData[$sCategory]['category'] = $sCategory; # Name category for overview
        $this->_aData[$sCategory]['files'][$iId] = $this->_formatForOutput($aRow, $aInts);
        $this->_aData[$sCategory]['files'][$iId]['size'] = Helper::getFileSize(PATH_UPLOAD . '/' .
                $this->_aRequest['controller'] . '/' . $aRow['file']);
      }
    }
    else {
      try {
        $oQuery = $this->_oDb->prepare("SELECT
                                          d.*,
                                          u.id AS user_id,
                                          u.name AS user_name,
                                          u.surname AS user_surname,
                                          u.email AS user_email
                                        FROM
                                          " . SQL_PREFIX . "downloads d
                                        LEFT JOIN
                                          " . SQL_PREFIX . "users u
                                        ON
                                          d.author_id=u.id
                                        WHERE
                                          d.id = :id
                                        LIMIT
                                          1");

        $oQuery->bindParam('id', $iId);
        $oQuery->execute();
        $aRow = & $oQuery->fetch(PDO::FETCH_ASSOC);
      }
      catch (\PDOException $p) {
        AdvancedException::reportBoth('0033 - ' . $p->getMessage());
        exit('SQL error.');
      }

      $this->_aData = $bUpdate === true ? $this->_formatForUpdate($aRow) : $aRow;
    }

    return $this->_aData;
  }

  /**
   * Return the name of a file.
   *
   * @static
   * @access public
   * @param integer $iId ID to get data from.
   * @return string $aResult['file'] file name.
   *
   */
  public static function getFileName($iId) {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                file
                                              FROM
                                                " . SQL_PREFIX . "downloads
                                              WHERE
                                                id = :id
                                              LIMIT 1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetch(PDO::FETCH_ASSOC);
      return $aResult['file'];
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0101 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Create new download.
   *
   * @access public
   * @param string $sFile file name
   * @param string $sExtension file extension
   * @return boolean status of query
   *
   */
  public function create($sFile, $sExtension) {
    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "downloads
                                        ( author_id,
                                          title,
                                          content,
                                          category,
                                          file,
                                          extension,
                                          date)
                                      VALUES
                                        ( :author_id,
                                          :title,
                                          :content,
                                          :category,
                                          :file,
                                          :extension,
                                          :date )");

      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('title', Helper::formatInput($this->_aRequest['title']), PDO::PARAM_STR);
      $oQuery->bindParam('content', Helper::formatInput($this->_aRequest['content']), PDO::PARAM_STR);
      $oQuery->bindParam('category', Helper::formatInput($this->_aRequest['category']), PDO::PARAM_STR);
      $oQuery->bindParam('file', $sFile, PDO::PARAM_STR);
      $oQuery->bindParam('extension', $sExtension, PDO::PARAM_STR);
      $oQuery->bindParam('date', time(), PDO::PARAM_INT);

      $bReturn = $oQuery->execute();
      parent::$iLastInsertId = Helper::getLastEntry('downloads');

      return $bReturn;
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0034 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0035 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Update a download.
   *
   * @access public
   * @param integer $iId ID to update
   * @return boolean status of query
   *
   */
  public function update($iId) {
    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "downloads
                                      SET
                                        author_id = :author_id,
                                        title = :title,
                                        category = :category,
                                        content = :content,
                                        downloads = :downloads
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('title', Helper::formatInput($this->_aRequest['title']), PDO::PARAM_STR);
      $oQuery->bindParam('category', Helper::formatInput($this->_aRequest['category']), PDO::PARAM_STR);
      $oQuery->bindParam('content', Helper::formatInput($this->_aRequest['content']), PDO::PARAM_STR);
      $oQuery->bindParam('downloads', Helper::formatInput($this->_aRequest['downloads']), PDO::PARAM_STR);
      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0036 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0037 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Destroy a download and its file.
   *
   * @access public
   * @param integer $iId ID to destroy
   * @return boolean status of query
   *
   */
  public function destroy($iId) {
    # Get file name
    $aFile = $this->getData($iId);
    $sFile = $aFile['file'];

    $bReturn = parent::destroy($iId);

    if (is_file(Helper::removeSlash(PATH_UPLOAD . '/' . $this->_aRequest['controller'] . '/' . $sFile)))
      unlink(Helper::removeSlash(PATH_UPLOAD . '/' . $this->_aRequest['controller'] . '/' . $sFile));

    return $bReturn;
  }

  /**
   * Updates a download count +1.
   *
   * @static
   * @access public
   * @param integer $iId ID to update
   * @return boolean status of query
   *
   */
  public static function updateDownloadCount($iId) {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("UPDATE
                                                " . SQL_PREFIX . "downloads
                                              SET
                                                downloads = downloads + 1
                                              WHERE
                                                id = :id");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        parent::$_oDbStatic->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0040 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0041 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }
}