<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/12/18
 * Time: 12:54 PM
 */

namespace OCA\MapUtil\Db;


use OCP\AppFramework\Db\Mapper;
use OCP\IDb;
use OCP\ILogger;
use PDO;

class RecordingMapper extends Mapper
{

    private $logger;
    private $appName;

    /**
     * RecordingMapper constructor.
     * @param ILogger $logger
     * @param $AppName
     * @param IDb $db
     */
    public function __construct(ILogger $logger, $AppName, IDb $db = null)
    {
        parent::__construct($db, "recorder_recordings", "\OCA\MapUtil\Db\RecordingDTO");
        $this->logger = $logger;
        $this->appName = $AppName;
    }

    public function log($message) {
        $this->logger->error($message, ['app' => $this->appName]);
    }

    public function getRecordingsByCitiesAndSuburbs($city, $suburb){
        $sql =  "SELECT id, filename, recording_type, uploader, upload_time, content, is_standalone, is_representative FROM oc_recorder_recordings where city_name = ? AND suburb_name = ? order by upload_time DESC";
        $result = $this->execute($sql, [$city, $suburb]);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        $recordings = [];
        foreach ($rows as $row) {
            $temp = new RecordingDTO();
            $temp->id = $row['id'];
            $temp->filename = $row['filename'];
            $temp->recordingType = $row['recording_type'];
            $temp->uploader = $row['uploader'];
            $temp->uploadTime = $row['upload_time'];
            $temp->content = $row['content'];
            $temp->isStandalone = $row['is_standalone'];
            $temp->isRepresentative = $row['is_representative'];
            // ADD RECORDING DTO
            $recordings[] = $temp;
        }
        $this->log("recordings length => ".count($recordings));
        return $recordings;
    }

    /**
     * @param $id, THE PK OF oc_recorder_recordings
     * @return bool|RecordingDTO, returns the updated DTO, false if it has been permanently removed
     */
    public function updateIsStandaloneState($id){
        // UPDATE SQL FOR STANDALONE MARKER
        $this->log("UPDATED ROW => ID RECEIVED = ".($id));
        if ($this->isPermanentlyDeleted($id) == 1) {
            $this->log("UPDATED ROW => DELETED PERMANENTLY : NOT FOUND!!! row id : ".($id));
            $sql = "DELETE FROM oc_recorder_recordings WHERE id = ?";
            $this->execute($sql, [$id]);
            return false;
        } else {
            $sql = "UPDATE oc_recorder_recordings SET is_standalone = NOT is_standalone WHERE id = ?";
            $this->execute($sql, [$id]);
            $getUpdatedRowSql = "SELECT id, filename, recording_type, uploader, upload_time, content, is_standalone, is_representative FROM oc_recorder_recordings where id = ?";
            $result = $this->execute($getUpdatedRowSql, [$id]);
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $temp = new RecordingDTO();
            $temp->id = $row['id'];
            $temp->filename = $row['filename'];
            $temp->recordingType = $row['recording_type'];
            $temp->uploader = $row['uploader'];
            $temp->uploadTime = $row['upload_time'];
            $temp->content = $row['content'];
            $temp->isStandalone = $row['is_standalone'];
            $temp->isRepresentative = $row['is_representative'];
            $this->log("UPDATED ROW => COLUMN 'is_standalone' = ".($temp->isStandalone));
            $this->log("UPDATED ROW => COLUMN 'is_representative' = ".($temp->isRepresentative));
            return $temp;
        }
    }

    /**
     * @param $id, THE PK OF oc_recorder_recordings
     * @return bool|RecordingDTO, returns the updated DTO, false if it has been permanently removed
     */
    public function updateIsRepresentativeState($id) {
        // UPDATE SQL FOR REPRESENTATIVE MARKER
        $this->log("UPDATED ROW => ID RECEIVED = ".($id));
        if ($this->isPermanentlyDeleted($id) == 1) {
            $this->log("UPDATED ROW => DELETED PERMANENTLY : NOT FOUND!!! row id : ".($id));
            $sql = "DELETE FROM oc_recorder_recordings WHERE id = ?";
            $this->execute($sql, [$id]);
            return false;
        } else {
            $sql = "UPDATE oc_recorder_recordings SET is_representative = NOT is_representative WHERE id = ?";
            $this->execute($sql, [$id]);
            $getUpdatedRowSql = "SELECT id, filename, recording_type, uploader, upload_time, content, is_standalone, is_representative FROM oc_recorder_recordings where id = ?";
            $result = $this->execute($getUpdatedRowSql, [$id]);
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $temp = new RecordingDTO();
            $temp->id = $row['id'];
            $temp->filename = $row['filename'];
            $temp->recordingType = $row['recording_type'];
            $temp->uploader = $row['uploader'];
            $temp->uploadTime = $row['upload_time'];
            $temp->content = $row['content'];
            $temp->isStandalone = $row['is_standalone'];
            $temp->isRepresentative = $row['is_representative'];
            $this->log("UPDATED ROW => COLUMN 'is_standalone' = ".($temp->isStandalone));
            $this->log("UPDATED ROW => COLUMN 'is_representative' = ".($temp->isRepresentative));
            return $temp;
        }
    }

    /**
     * @param $id
     * @return mixed, 1 if permanently deleted, 0 if not, might throw exceptions
     */
    private function isPermanentlyDeleted($id) {
        // SELECT COUNT(*) FROM vonz.oc_recorder_recordings WHERE NOT EXISTS(
        //	SELECT NULL FROM vonz.oc_filecache f WHERE filename = f.name
        //    ) AND NOT EXISTS (
        //    SELECT NULL FROM vonz.oc_files_trash f WHERE filename = f.id
        //    ) AND id = 5;
        $sql = "SELECT COUNT(*) FROM oc_recorder_recordings WHERE NOT EXISTS(
                    SELECT NULL FROM oc_filecache f WHERE filename = f.name
                    ) AND NOT EXISTS (
                    SELECT NULL FROM oc_files_trash f WHERE filename = f.id
                    ) AND id = ?";
        $result = $this->execute($sql, [$id]);
        $numberOfRows = $result->fetchColumn();
        return $numberOfRows;
    }

    /**
     * @param $id
     * @return string return the internal path if found in oc_filecache, 'recycle' if in recycle bin, 'deleted' if the file has been deleted permanently
     */
    public function verifyDownload($id){
        // SELECT f.path from vonz.oc_recorder_recordings as r, vonz.oc_filecache as f WHERE r.id = 22 AND r.filename = f.name;
        $sql = "SELECT f.path FROM oc_recorder_recordings AS r, oc_filecache AS f WHERE r.id = ? AND r.filename = f.name";
        $row = $this->execute($sql, [$id])->fetch(PDO::FETCH_ASSOC);
        if ($row !== false) {
            return $row['path']; // internal path
        } else {
            // if not in oc_filecache
            // SELECT * from vonz.oc_recorder_recordings as r, vonz.oc_files_trash as f WHERE r.id = 22 AND r.filename = f.id;
            $sql = "SELECT * from oc_recorder_recordings as r, oc_files_trash as f WHERE r.id = ? AND r.filename = f.id";
            if ($this->execute($sql, [$id])->fetch(PDO::FETCH_ASSOC) !== false) {
                return "recycle"; // in recycle bin
            } else {
                return "deleted"; // deleted permanently
            }
        }
    }

}