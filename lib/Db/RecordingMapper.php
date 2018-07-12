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

}