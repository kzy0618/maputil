<?php /** @noinspection PhpUndefinedClassInspection */

/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/12/18
 * Time: 12:56 PM
 */

namespace OCA\MapUtil\Controller;



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
     * * $strictMode === true, the UI will be eagerly synchronized and eagerly re-rendered
     * * $strictMode === false, the UI will be lazily synchronized and lazily re-rendered
     * * $idToSetTrue === "null", bypass set true operation
     * * $idToSetFalse === "null", bypass set false operation
     * @NoCSRFRequired
     * @param $idToSetTrue pk id of the recording to set is_representative to true
     * @param $idToSetFalse pk id of the recording to set is_representative to false
     * @param bool $strictMode default false, when set to true, 404 will be generated if the recording to be set to false was deleted permanently. when $strictMode == false, don't care whether the recording to be set to false has been deleted or not. In both case, 404 will be generated if the recording to be set to true has been permanently removed. Final remark: this $strictMode only matters if and only if none of the operations is bypassed. If any operation is bypassed, the remaining one follows the standard procedure. If both are bypassed, a 200 will be return with empty body.
     * @return DataResponse
     */
    public function updateRepresentativeForRadioBtn($idToSetTrue, $idToSetFalse, $strictMode = false)
    {
        if ($this->isInAdminGroup()) {

            $resultOfSetIdToTrue = null;
            $resultOfSetIdToFalse = null;

            if ( $idToSetTrue !== "null" ) {
                $resultOfSetIdToTrue = $this->recordingMapper->setIsRepresentativeStateToTrue($idToSetTrue);
            }
            if ( $idToSetFalse !== "null" ) {
                $resultOfSetIdToFalse = $this->recordingMapper->setIsRepresentativeStateToFalse($idToSetFalse);
            }
            // check bypass
            if ( $resultOfSetIdToTrue === null && $resultOfSetIdToFalse === null ) {
                return new DataResponse();
            }

            if ( $resultOfSetIdToTrue === null )
            {
                if ($resultOfSetIdToFalse === false) {
                    return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND);
                } else {
                    return new DataResponse($resultOfSetIdToFalse);
                }
            }
            elseif ( $resultOfSetIdToFalse === null )
            {
                if ($resultOfSetIdToTrue === false) {
                    return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND);
                } else {
                    return new DataResponse($resultOfSetIdToTrue);
                }
            }
            else {
                // check non existence
                if ( $resultOfSetIdToTrue === false ) {
                    return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND);
                } else {
                    switch ($strictMode) {
                        case true: // strict mode, eager re-render
                            if ( $resultOfSetIdToFalse === false ) { // if the recording being set to false is actually deleted from the file system, we still want to inform the client the non-existence of the recording so that it can eagerly conduct a refresh/full-reload, or just re-rendering the dom without reloading, depending on the implementation decision of the frontend. This is solely for allowing some freedom in UI logic. The database is ALWAYS synchronizing eagerly whenever it's got the chance. The frontend can utilize this mode to eagerly remove any UI elements that represent deleted recordings. The trade-off is, if the UI conducts full-reload, then it will be annoying from the user's perspective. This works most efficiently if the UI conducts only re-rendering.
                                return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND);
                            } else {
                                return new DataResponse($resultOfSetIdToTrue);
                            }
                            break;
                        default:
                            // lazy mode. The UI elements, which represent removed recordings, will only be deleted in the web page if the user wants to set it to true. Otherwise they will be persisted in UI before the web page conducts a full-reload/re-rendering intentionally (due to other 404/403 responses).
                            return new DataResponse($resultOfSetIdToTrue);
                    }
                }
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