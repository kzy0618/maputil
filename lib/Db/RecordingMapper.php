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

    /**
     * @param $city
     * @param $suburb
     * @return array
     */
    public function getOneOrMoreRecordingRows($city, $suburb) {
        $sql =  "SELECT id, filename, recording_type, uploader, upload_time, content, is_standalone, is_representative FROM oc_recorder_recordings where city_name = ? AND suburb_name = ? order by upload_time DESC";
        $result = $this->execute($sql, [$city, $suburb]);
        return $result->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getOneRecordingRowOnly($id) {
        $getUpdatedRowSql = "SELECT id, filename, recording_type, uploader, upload_time, content, is_standalone, is_representative FROM oc_recorder_recordings where id = ?";
        $result = $this->execute($getUpdatedRowSql, [$id]);
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    private function recordingDTOAssembler($row) {
        $temp = new RecordingDTO();
        $temp->id = $row['id'];
        $temp->filename = $row['filename'];
        $temp->recordingType = $row['recording_type'];
        $temp->uploader = $row['uploader'];
        $temp->uploadTime = $row['upload_time'];
        $temp->content = $row['content'];
        $temp->isStandalone = $row['is_standalone'];
        $temp->isRepresentative = $row['is_representative'];
        return $temp;
    }

    /**
     * @param $city
     * @param $suburb
     * @return array
     */
    public function getRecordingsByCitiesAndSuburbs($city, $suburb){
        $rows = $this->getOneOrMoreRecordingRows($city, $suburb);
        $recordings = [];
        foreach ($rows as $row) {
            // ADD RECORDING DTO
            $recordings[] = $this->recordingDTOAssembler($row);
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

            $row = $this->getOneRecordingRowOnly($id);
            if ($row === false) {
                return false;
            }
            $temp = $this->recordingDTOAssembler($row);
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

            $row = $this->getOneRecordingRowOnly($id);
            if ($row === false) {
                return false;
            }
            $temp = $this->recordingDTOAssembler($row);
            $this->log("UPDATED ROW => COLUMN 'is_standalone' = ".($temp->isStandalone));
            $this->log("UPDATED ROW => COLUMN 'is_representative' = ".($temp->isRepresentative));
            return $temp;
        }
    }

    /**
     * Bulk operations with locking, might lead to long blocking.
     * Trade off performance in favour of consistency. This is to ensure concurrent safety.
     * Try not to open multiple clients of maputil.
     * This function is optimistic, the database will still try to update as many records as possible, even with warnings, it just won't return a DTO in such case and instead, it will return false to inform the maputil clients about external changes so that they can refresh themselves accordingly. Otherwise a DTO will be handed back.
     * @param $idToBeTrue
     * @param $arrayOfIdsToBeFalse
     * @return bool|RecordingDTO false if warnings occurred, RecordingDTO otherwise
     */
    public function isRepresentativeStateHandler($idToBeTrue, array $arrayOfIdsToBeFalse) {

        // placeholder for the DTO
        $temp = false;

        // global error flag
        $error = false;

        // flag to detect whether the oc_recorder_recording table has been modified by other admin users
        $warningOfExternalModification = false;

        try {
            $this->db->lockTable("recorder_recordings");
            $this->log("start locking table!!!!!!!!!!!!!!!!!!!!!!!!!!");

            // probe invalid id
            $row = $this->getOneRecordingRowOnly($idToBeTrue);
            if ($row === false) {
                $error = true;
            }

            // optimistic set-true
            $sql = "UPDATE oc_recorder_recordings SET is_representative = TRUE WHERE id = ?";
            $this->execute($sql, [$idToBeTrue]);
            $row = $this->getOneRecordingRowOnly($idToBeTrue);
            $temp = $this->recordingDTOAssembler($row);

            // start bulk operations
            foreach ($arrayOfIdsToBeFalse as $id) {
                // optimistic set-false
                $sql = "UPDATE oc_recorder_recordings SET is_representative = FALSE WHERE id = ?";
                $this->execute($sql, [$id]);
                $row = $this->getOneRecordingRowOnly($id);
                if ($row === false) {
                    // probe external modification from other admins
                    $warningOfExternalModification = true;
                }
            }

            // wait until bulk operations all terminated and then check for any warnings
            if ($warningOfExternalModification) {
                $error = true;
            }



        } catch (\Exception $exception) {
            $this->log($exception->getMessage());
            $error = true;
        } finally {
            $this->db->unlockTable();
            $this->log("unlock table!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!");

            $sql = "SELECT COUNT(*) FROM oc_recorder_recordings WHERE NOT EXISTS(
                    SELECT NULL FROM oc_filecache f WHERE filename = f.name
                    ) AND NOT EXISTS (
                    SELECT NULL FROM oc_files_trash f WHERE filename = f.id
                    )";
            $result = $this->execute($sql);
            $numberOfRows = $result->fetchColumn();

            if ($error || $temp === false || $temp === null || $numberOfRows > 0) {
                // do some clean up for our bulk operations
                $n = $this->tableCleanUp();
                $this->log("BULK OPERATIONS EXIT WITH WARNINGS. NUM OF ROWS DELETED ".$n." STATE : {
                    error : $error;
                    temp : $temp;
                    #deleted recording files: $numberOfRows
                }");
                return false; // inform the frontend to refresh itself
            }

        }

        // if the function survives until this point, return the resulting DTO
        $this->log("BULK OPERATIONS EXIT GRACEFULLY. ALL GOOD.");
        return $temp;

    }

    /**
     * forcibly set is_representative to true (1)
     * @param $id, THE PK OF oc_recorder_recordings
     * @return bool|RecordingDTO, returns the updated DTO, false if it has been permanently removed
     */
    public function setIsRepresentativeStateToTrue($id) {
        // UPDATE SQL FOR REPRESENTATIVE MARKER
        $this->log("UPDATED ROW => ID RECEIVED = ".($id));
        if ($this->isPermanentlyDeleted($id) == 1) {
            $this->log("UPDATED ROW => DELETED PERMANENTLY : NOT FOUND!!! row id : ".($id));
            $sql = "DELETE FROM oc_recorder_recordings WHERE id = ?";
            $this->execute($sql, [$id]);
            return false;
        } else {
            $sql = "UPDATE oc_recorder_recordings SET is_representative = TRUE WHERE id = ?";
            $this->execute($sql, [$id]);

            $row = $this->getOneRecordingRowOnly($id);
            if ($row === false) {
                return false;
            }
            $temp = $this->recordingDTOAssembler($row);
            $this->log("UPDATED ROW => COLUMN 'is_standalone' = ".($temp->isStandalone));
            $this->log("UPDATED ROW => COLUMN 'is_representative' = ".($temp->isRepresentative));
            return $temp;
        }
    }

    /**
     * forcibly set is_representative to false (0)
     * @param $id, THE PK OF oc_recorder_recordings
     * @return bool|RecordingDTO, returns the updated DTO, false if it has been permanently removed
     */
    public function setIsRepresentativeStateToFalse($id) {
        // UPDATE SQL FOR REPRESENTATIVE MARKER
        $this->log("UPDATED ROW => ID RECEIVED = ".($id));
        if ($this->isPermanentlyDeleted($id) == 1) {
            $this->log("UPDATED ROW => DELETED PERMANENTLY : NOT FOUND!!! row id : ".($id));
            $sql = "DELETE FROM oc_recorder_recordings WHERE id = ?";
            $this->execute($sql, [$id]);
            return false;
        } else {
            $sql = "UPDATE oc_recorder_recordings SET is_representative = FALSE WHERE id = ?";
            $this->execute($sql, [$id]);

            $row = $this->getOneRecordingRowOnly($id);
            if ($row === false) {
                return false;
            }
            $temp = $this->recordingDTOAssembler($row);
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
            // check if the input id is valid
            $checkIfIDValid = "SELECT COUNT(*) FROM oc_recorder_recordings WHERE id = ?";
            $count = $this->execute($checkIfIDValid, [$id])->fetchColumn();
            if ($count == 0) {
                return "deleted"; // not a valid pk
            }
            
            // if not in oc_filecache
            $result = $this->isPermanentlyDeleted($id);
            if ($result == 1) {

                // ONLY MAPUTIL CAN DELETE ROWS
                $this->log("UPDATED ROW => DELETED PERMANENTLY : NOT FOUND!!! row id : ".($id));
                $sql = "DELETE FROM oc_recorder_recordings WHERE id = ?";
                $this->execute($sql, [$id]);

                return "deleted"; // deleted permanently
            } else {
                return "recycle"; // in recycle bin
            }
        }
    }

    /**
     * @return int, rows affected
     */
    private function tableCleanUp() {
        // DELETE FROM vonz.oc_recorder_recordings WHERE NOT EXISTS(
        //	SELECT NULL FROM vonz.oc_filecache f WHERE filename = f.name
        //    ) AND NOT EXISTS (
        //    SELECT NULL FROM vonz.oc_files_trash f WHERE filename = f.id
        //    );
        $sql = "DELETE FROM oc_recorder_recordings WHERE NOT EXISTS(
                    SELECT NULL FROM oc_filecache f WHERE filename = f.name
                    ) AND NOT EXISTS (
                    SELECT NULL FROM oc_files_trash f WHERE filename = f.id
                    )";
        return $this->execute($sql)->rowCount();
    }

    public function deleteFileRowInFilecacheByName($name) {
        $sql = "DELETE FROM oc_filecache WHERE name = ?";
        $this->execute($sql, [$name]);
    }

    public function deleteFileRowInFileTrashBinByName($name) {
        $sql = "DELETE FROM oc_files_trash WHERE id = ?";
        $this->execute($sql, [$name]);
    }

    public function deleteRecordingsByIds(array $ids) {
        foreach ($ids as $id) {
            $sql = "DELETE FROM oc_recorder_recordings WHERE id = ?";
            $this->execute($sql, [$id]);
        }
    }

    public function getRecordingInternalPathById($id) {
        $sql = "SELECT internal_path FROM oc_recorder_recordings WHERE id = ?";
        $result = $this->execute($sql, [$id])->fetchColumn();
        return $result;
    }

}