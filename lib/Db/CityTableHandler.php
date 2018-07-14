<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/14/18
 * Time: 3:03 PM
 */

namespace OCA\MapUtil\Db;

use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;
use OCP\ILogger;
use PDO;

class CityTableHandler extends Mapper
{

    private $logger;
    private $appName;

    public function __construct(ILogger $logger, $AppName, IDBConnection $db)
    {
        parent::__construct($db, "recorder_recordings", null);
        $this->logger = $logger;
        $this->appName = $AppName;
    }

    public function log($message) {
        $this->logger->error($message, ['app' => $this->appName]);
    }

    /**
     * @return array
     */
    public function findUploadedCities(){
        $n = $this->tableCleanUp();
        $this->log("DEBUGGING IN MapUtil::CityTableHandler->tableCleanUp rows affected => $n");
        $sql =  "SELECT DISTINCT city_name FROM oc_recorder_recordings order by city_name";
        $result = $this->execute($sql);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        $cities = [];
        foreach ($rows as $row) {
            $cities[] = $row['city_name'];
        }
        $this->log("DEBUGGING IN MapUtil::CityTableHandler->findUploadedCities cities length => ".count($cities));
        return $cities;
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

}