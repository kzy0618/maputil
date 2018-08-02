<?php /** @noinspection PhpUndefinedClassInspection */

/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/12/18
 * Time: 12:56 PM
 */

namespace OCA\MapUtil\Controller;


use OC\Files\Node\File;
use OCA\MapUtil\Db\CityTableHandler;
use OCA\MapUtil\Db\SuburbTableHandler;
use OCA\MapUtil\Db\RecordingMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\Files\NotFoundException;
use OCP\ILogger;
use OCP\IRequest;

class RecordingController extends Controller
{

    private $logger;
    private $uid;
    private $cityTableHandler;
    private $suburbTableHandler;
    private $recordingMapper;

    /**
     * PageController constructor.
     * @param ILogger $logger
     * @param $AppName
     * @param IRequest $request
     * @param $UserId
     * @param CityTableHandler $cityTableHandler
     * @param SuburbTableHandler $suburbTableHandler
     * @param RecordingMapper $recordingMapper
     */
    public function __construct(ILogger $logger, $AppName, IRequest $request, $UserId, CityTableHandler $cityTableHandler, SuburbTableHandler $suburbTableHandler, RecordingMapper $recordingMapper)
    {
        parent::__construct($AppName, $request);
        $this->appName = $AppName;
        $this->logger = $logger;
        $this->uid = $UserId;
        $this->cityTableHandler = $cityTableHandler;
        $this->suburbTableHandler = $suburbTableHandler;
        $this->recordingMapper = $recordingMapper;
    }

    /**
     * Check if the current user is in admin group
     * @return bool
     */
    private function isInAdminGroup () {
        $user = \OC::$server->getUserManager()->get($this->uid);
        $groupMan = \OC::$server->getGroupManager();
        $this->log("DEBUGGING IN MapUtil->RecordingController->isInAdminGroup => {
            uid => ".$this->uid."
            username => ".$user->getDisplayName()."
        }");
        foreach ($groupMan->getUserGroupIds($user) as $id) {
            $this->log("DEBUGGING IN MapUtil->RecordingController->isInAdminGroup => {group_id => $id}");
        }

        return $groupMan->isAdmin($this->uid);
    }

    public function log($message) {
        $this->logger->error($message, ['app' => $this->appName]);
    }

    /**
     * all handlers in this controller must be privileged to admins only
     * @NoCSRFRequired
     */
    public function getCities() {
        if ($this->isInAdminGroup()) {
            return new DataResponse($this->cityTableHandler->findUploadedCities());
        } else {
            return new DataResponse(["YOU NEED TO BE IN ADMIN GROUP IN ORDER TO USE THIS APP!!!"], Http::STATUS_UNAUTHORIZED); // 401 unauthorized
        }
    }

    /**
     * all handlers in this controller must be privileged to admins only
     * @NoCSRFRequired
     * @param $city
     * @return DataResponse
     */
    public function getSuburbs($city) {
		// Get SUBURBS BY CITY
        if ($this->isInAdminGroup()) {
            return new DataResponse($this->suburbTableHandler->findSuburbsByCity($city));
        } else {
            return new DataResponse(["YOU NEED TO BE IN ADMIN GROUP IN ORDER TO USE THIS APP!!!"], Http::STATUS_UNAUTHORIZED); // 401 unauthorized
        }
    }

    /**
     * all handlers in this controller must be privileged to admins only
     * @NoCSRFRequired
     * @param $city
     * @param $suburb
     * @return DataResponse
     */
    public function showRecordings($city, $suburb){
        // Get recordings by city and suburb
        if ($this->isInAdminGroup()) {
            return new DataResponse($this->recordingMapper->getRecordingsByCitiesAndSuburbs($city, $suburb));
        } else {
            return new DataResponse(["YOU NEED TO BE IN ADMIN GROUP IN ORDER TO USE THIS APP!!!"], Http::STATUS_UNAUTHORIZED); // 401 unauthorized
        }
    }

    /**
     * all handlers in this controller must be privileged to admins only
     * @NoCSRFRequired
     * @param $id
     * @return DataResponse
     */
    public function updateStandalone($id) {
        if ($this->isInAdminGroup()) {
            $result = $this->recordingMapper->updateIsStandaloneState($id);
            if ($result === false) {
                return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND);
            } else {
                return new DataResponse($result);
            }
        } else {
            return new DataResponse(["YOU NEED TO BE IN ADMIN GROUP IN ORDER TO USE THIS APP!!!"], Http::STATUS_UNAUTHORIZED); // 401 unauthorized
        }
    }

    /**
     * all handlers in this controller must be privileged to admins only
     * @NoCSRFRequired
     * @param $id
     * @return DataResponse
     */
    public function updateRepresentative($id)
    {
        // updateIsRepresentativeState in RecordingMapper
        if ($this->isInAdminGroup()) {
            $result = $this->recordingMapper->updateIsRepresentativeState($id);
            if ($result === false) {
                return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND);
            } else {
                return new DataResponse($result);
            }
        } else {
            return new DataResponse(["YOU NEED TO BE IN ADMIN GROUP IN ORDER TO USE THIS APP!!!"], Http::STATUS_UNAUTHORIZED); // 401 unauthorized
        }
    }

    /**
     * all handlers in this controller must be privileged to admins only
     * @NoCSRFRequired
     * @param $id
     * @return DataResponse|Http\DownloadResponse
     */
    public function download($id) {
        if ($this->isInAdminGroup()) {
            $result = $this->recordingMapper->verifyDownload($id);
            if ($result === "recycle") {
                return new DataResponse(["recycle"], Http::STATUS_FORBIDDEN); // 403
            } elseif ($result === "deleted") {
                return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
            } else {
                return $this->generateDownloadResponse($id, $result);
            }
        } else {
            return new DataResponse(["YOU NEED TO BE IN ADMIN GROUP IN ORDER TO USE THIS APP!!!"], Http::STATUS_UNAUTHORIZED); // 401 unauthorized
        }
    }

    private function generateDownloadResponse ($id, $path) {
        try {
            $storage = \OC::$server->getUserFolder('Frenchalexia')->getStorage();
            $temp = null;
            $handle = null;
            $size = 1;
            $pos = 0;
            while ($pos !== $size) {
                $isExisting = $storage->file_exists($path);
                $this->log("DEBUGGING IN RecordingController->download id received => $id , file exist : ".$isExisting);
                if (!$isExisting) {
                    throw new NotFoundException("deleted");
                }
                $handle = $storage->fopen($path, "rb"); // b for Binary fread()
                $this->log("DEBUGGING IN RecordingController->download id received => $id , file read : ".$handle);
                $size = $storage->filesize($path);
                $this->log("DEBUGGING IN RecordingController->download id received => $id , file size : ".$size);
                $temp = fread($handle, $size);
                $pos = ftell($handle);
                $this->log("DEBUGGING IN RecordingController->download id received => $id , file pointer pos : ".$pos);
            }
            fclose($handle);
            $pathFragments = explode("/", $path);
            return new Http\DataDownloadResponse($temp, end($pathFragments), "audio/wav");
        } catch (NotFoundException $e) {
            return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
        }
    }

}