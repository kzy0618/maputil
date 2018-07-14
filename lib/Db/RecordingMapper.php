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
        $sql =  "SELECT id, filename, recording_type, uploader, upload_time, content, is_added_to_map FROM oc_recorder_recordings where city_name = ? AND suburb_name = ? order by upload_time DESC";
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
            $temp->isAddedToMap = $row['is_added_to_map'];
            // ADD RECORDING DTO
            $recordings[] = $temp;
        }
        $this->log("recordings length => ".count($recordings));
        return $recordings;
    }

    /**
     * @param $id
     * @return bool|RecordingDTO, returns the updated DTO, false if it has been permanently removed
     */
    public function updateIsAddedToMapState($id){
        $this->log("UPDATED ROW => ID RECEIVED = ".($id));
        if ($this->isPermanentlyDeleted($id) == 1) {
            $this->log("UPDATED ROW => DELETED PERMANENTLY : NOT FOUND!!! row id : ".($id));
            $sql = "DELETE FROM oc_recorder_recordings WHERE id = ?";
            $this->execute($sql, [$id]);
            return false;
        } else {
            $sql = "UPDATE oc_recorder_recordings SET is_added_to_map = NOT is_added_to_map WHERE id = ?";
            $this->execute($sql, [$id]);
            $getUpdatedRowSql = "SELECT id, filename, recording_type, uploader, upload_time, content, is_added_to_map FROM oc_recorder_recordings where id = ?";
            $result = $this->execute($getUpdatedRowSql, [$id]);
            $row = $result->fetch(PDO::FETCH_ASSOC);
            $temp = new RecordingDTO();
            $temp->id = $row['id'];
            $temp->filename = $row['filename'];
            $temp->recordingType = $row['recording_type'];
            $temp->uploader = $row['uploader'];
            $temp->uploadTime = $row['upload_time'];
            $temp->content = $row['content'];
            $temp->isAddedToMap = $row['is_added_to_map'];
            $this->log("UPDATED ROW => COLUMN 'is_added_to_map' = ".($temp->isAddedToMap));
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

}