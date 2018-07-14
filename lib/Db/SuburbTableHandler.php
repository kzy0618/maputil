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

class SuburbTableHandler extends Mapper
{

    private $logger;
    private $appName;

    public function __construct(ILogger $logger, $AppName, IDBConnection $db)
    {
        parent::__construct($db, "suburb_city_coords", null);
        $this->logger = $logger;
        $this->appName = $AppName;
    }

    public function log($message) {
        $this->logger->error($message, ['app' => $this->appName]);
    }

    /**
     * @param $city
     * @return array
     */
    public function findSuburbsByCity($city) {
        $sql =  "SELECT DISTINCT suburb_name FROM oc_recorder_recordings where city_name = ? order by suburb_name";
        $result = $this->execute($sql, [$city]);
        $rows = $result->fetchAll(PDO::FETCH_ASSOC);
        $suburbs = [];
        foreach ($rows as $row) {
            $suburbs[] = $row['suburb_name'];
        }
        return $suburbs;
    }

}