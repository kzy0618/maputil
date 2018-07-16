<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/12/18
 * Time: 1:27 PM
 */

namespace OCA\MapUtil\Db;


use OCP\AppFramework\Db\Entity;

class RecordingDTO extends Entity
{

    // make a POJO
    public $id;
    public $filename;
    public $recordingType;
    public $uploader;
    public $uploadTime;
    public $content;
    public $isStandalone;
    public $isRepresentative;

}