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
     * this request is handled by bulk operations, it can take some time
     * @NoCSRFRequired
     * @param $idToSetTrue pk id of the recording to set is_representative to true
     * @param array $arrayOfIdsToSetFalse
     * @return DataResponse
     */
    public function updateRepresentativeForRadioBtn($idToSetTrue, array $arrayOfIdsToSetFalse)
    {
        if ($this->isInAdminGroup()) {

            $result = $this->recordingMapper->isRepresentativeStateHandler($idToSetTrue, $arrayOfIdsToSetFalse);
            if ($result === false) {
                // not necessary means the target record is deleted but this is an indication of page reload
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
     * download audio only
     * @NoCSRFRequired
     * @param $id
     * @return DataResponse|Http\DownloadResponse
     * @throws \Exception if anything goes burst unexpectedly
     */
    public function downloadAudioOnly($id) {
        if ($this->isInAdminGroup()) {
            $result = $this->recordingMapper->verifyDownload($id);
            if ($result === "recycle") {
                return new DataResponse(["recycle"], Http::STATUS_FORBIDDEN); // 403
            } elseif ($result === "deleted") {
                return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
            } else {
                return $this->generateDownloadResponse($id, $result, "audio/wav");
            }
        } else {
            return new DataResponse(["YOU NEED TO BE IN ADMIN GROUP IN ORDER TO USE THIS APP!!!"], Http::STATUS_UNAUTHORIZED); // 401 unauthorized
        }
    }

    /**
     * Pessimistically attempt to undertake a download
     * Download both audio and text files, returning a zip containing a pair of audio and text upon success
     * @NoCSRFRequired
     * @param $id
     * @return Http\DataDownloadResponse|DataResponse
     * @throws \Exception
     */
    public function download($id) {
        return $this->bulkDownload([$id]);
    }

    /**
     * @param $id $id is purely for debug logging purpose, you can pass whatever you want to be logged out
     * @param $path $path relative to Frenchalexia's data dir (i.e., paths should start with "files/...")
     * @param $contentType
     * @param bool $doCleanUp $doCleanUp === true will delete the file in the $path given, after reading it
     * @return Http\DataDownloadResponse|DataResponse
     * @throws \Exception
     */
    private function generateDownloadResponse ($id, $path, $contentType, $doCleanUp = false) {
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
            if ($doCleanUp) {
                $this->log("going to delete temp zip at Frenchalexia's files dir at path : $path");
                $isDeleted = $storage->unlink($path);
                $this->recordingMapper->deleteFileRowInFilecacheByName(end($pathFragments));
                if ($isDeleted === false) {
                    throw new \Exception("FAIL TO DELETE TEMP FILE BEFORE GENERATING DATA DOWNLOAD RESPONSE");
                } else {
                    $this->log("going to delete temp zip at Frenchalexia's files_trashbin/files dir");
                    $dir = $storage->opendir("files_trashbin/files");
                    if ($dir === false) {
                        throw new \Exception("FAIL TO OPEN TRASH BIN");
                    }
                    $isDeleted = false;
                    $nameToSearch = explode(".", end($pathFragments))[0];
                    $this->log("FILENAME TO SEARCH IN TRASH BIN : $nameToSearch");
                    while (($file = readdir($dir)) !== false) {
//                        $this->log("LISTING FILES IN TRASH BIN : $file");
                        $targetName = explode(".", $file)[0];
                        if ($targetName === $nameToSearch) {
                            $this->log("FIND THE FILE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! : $file");
                            $this->recordingMapper->deleteFileRowInFilecacheByName($file);
                            $isDeleted = $storage->unlink("files_trashbin/files/".$file);
                        }
                    }
                    closedir($dir);
                    $this->recordingMapper->deleteFileRowInFileTrashBinByName(end($pathFragments));
                    if ($isDeleted === false) {
                        $this->log("the file $path has gone");
                    } else {
                        $this->log("deleted $path SUCCESSFULLY!!!!!!!!!!!!");
                    }
                }
            }
            return new Http\DataDownloadResponse($temp, end($pathFragments), $contentType);
        } catch (NotFoundException $e) {
            return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
        }
    }

    /**
     * Pessimistically attempt to undertake a bulk download
     * Download both audio and text files, returning a zip upon success
     * @NoCSRFRequired
     * @param array $idsToDownload
     * @return Http\DataDownloadResponse|DataResponse
     * @throws \Exception
     */
    public function bulkDownload(array $idsToDownload){
        if ($this->isInAdminGroup()) {
            $owncloudDataRoot = "/var/www/p4/owncloud/data";
            $folder = \OC::$server->getUserFolder('Frenchalexia');
            $folderPath = $folder->getPath();
            $this->log("DEBUGGING IN BULK DOWNLOAD: folder full path: $owncloudDataRoot$folderPath");
            $folderFullPath = $owncloudDataRoot.$folderPath;
            $zip = new \ZipArchive();
            $explodeduuid = explode(".", uniqid("temp", true));
            $tempname = $explodeduuid[0].$explodeduuid[1].".zip";
            $filename = $folderFullPath."/".$tempname;
            $filenameRelativeToFrenchalexiaDir = "files/".$tempname;
            $this->log("DEBUGGING IN BULK DOWNLOAD: file full path: $filename");
            $this->log("DEBUGGING IN BULK DOWNLOAD: file relative path: $filenameRelativeToFrenchalexiaDir");

            // create zip
            $folder->newFile($tempname);
            if ($zip->open($filename) !== TRUE) {
                $this->log("DEBUGGING IN BULK DOWNLOAD: cannot open <$filename>");
                throw new \Exception("cannot open <$filename>");
            }
            $this->log("DEBUGGING IN BULK DOWNLOAD: temp zip <$filename> created!!!!!!!!!!!!!!!!!");

            try {
                $isErrorOccur = false;
                foreach ($idsToDownload as $id) {
                    $result = $this->recordingMapper->verifyDownload($id);
                    if ($result === "recycle") {
                        $isErrorOccur = true;
                        return new DataResponse(["recycle"], Http::STATUS_FORBIDDEN); // 403
                    } elseif ($result === "deleted") {
                        $isErrorOccur = true;
                        return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
                    } else {
                        $splitPath = explode("/", $result);
                        $this->log("DEBUGGING IN BULK DOWNLOAD: attempt to zip file ".$owncloudDataRoot . $result);
                        $isSuccess = $zip->addFile($owncloudDataRoot . "/Frenchalexia/" . $result, end($splitPath)); // we only care about the origins in Frenchalexia's dir, since they are free from interventions of non-admin users
                        $txtPath = "";
                        for ($i = 0 ; $i < count($splitPath) - 1 ; $i++ ) {
                            $txtPath .= $splitPath[$i] . "/";
                        }
                        $endOfPath = end($splitPath);
                        $txtFilename = explode(".", $endOfPath)[0] . ".txt";
                        $txtPath .= $txtFilename;
                        $isTxtAdded = $zip->addFile($owncloudDataRoot . "/Frenchalexia/" . $txtPath, $txtFilename);
                        if ($isSuccess === false || $isTxtAdded === false) {
                            $isErrorOccur = true;
                            return new DataResponse(["deleted"], Http::STATUS_NOT_FOUND); // 404
                        }
                    }
                }
            } finally {
                $this->log("numfiles: " . $zip->numFiles);
                $this->log("status:" . $zip->status);
                $zip->close();
                if ($isErrorOccur) {
                    $storage = \OC::$server->getUserFolder('Frenchalexia')->getStorage();
                    $this->log("going to delete temp zip at Frenchalexia's files dir at path : $filenameRelativeToFrenchalexiaDir");
                    $isDeleted = $storage->unlink($filenameRelativeToFrenchalexiaDir);
                    $this->recordingMapper->deleteFileRowInFilecacheByName($tempname);
                    if ($isDeleted === false) {
                        throw new \Exception("FAIL TO DELETE TEMP FILE BEFORE GENERATING DATA DOWNLOAD RESPONSE");
                    } else {
                        $this->log("going to delete temp zip at Frenchalexia's files_trashbin/files dir");
                        $dir = $storage->opendir("files_trashbin/files");
                        if ($dir === false) {
                            throw new \Exception("FAIL TO OPEN TRASH BIN");
                        }
                        $isDeleted = false;
                        $nameToSearch = explode(".", $tempname)[0];
                        $this->log("FILENAME TO SEARCH IN TRASH BIN : $nameToSearch");
                        while (($file = readdir($dir)) !== false) {
//                        $this->log("LISTING FILES IN TRASH BIN : $file");
                            $targetName = explode(".", $file)[0];
                            if ($targetName === $nameToSearch) {
                                $this->log("FIND THE FILE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! : $file");
                                $this->recordingMapper->deleteFileRowInFilecacheByName($file);
                                $isDeleted = $storage->unlink("files_trashbin/files/".$file);
                            }
                        }
                        closedir($dir);
                        $this->recordingMapper->deleteFileRowInFileTrashBinByName($tempname);
                        if ($isDeleted === false) {
                            $this->log("the file $tempname has gone");
                        } else {
                            $this->log("deleted $tempname SUCCESSFULLY!!!!!!!!!!!!");
                        }
                    }
                }
            }

            // mime type application/zip
            return $this->generateDownloadResponse("zip", $filenameRelativeToFrenchalexiaDir, "application/zip", true);
        } else {
            return new DataResponse(["YOU NEED TO BE IN ADMIN GROUP IN ORDER TO USE THIS APP!!!"], Http::STATUS_UNAUTHORIZED); // 401 unauthorized
        }
    }

    /**
     * Optimistic bulk deletion
     * @NoCSRFRequired
     * @param array $idsToDelete
     * @throws NotFoundException
     */
    public function bulkDelete (array $idsToDelete) {
        $storage = \OC::$server->getUserFolder('Frenchalexia')->getStorage();
        foreach ($idsToDelete as $id) {
            $internalPath = $this->recordingMapper->getRecordingInternalPathById($id);
            if ($internalPath !== false) {
                $storage->unlink($internalPath);
                $pathFragments = explode("/", $internalPath);
                $filename = end($pathFragments);
                $this->recordingMapper->deleteFileRowInFilecacheByName($filename);
                $unqualifiedFilename = explode(".", $filename)[0];
                $this->recordingMapper->deleteFileRowInFilecacheByName($unqualifiedFilename.".txt");
                $text = "";
                for ($i = 0; $i < (count($pathFragments) - 1); $i++) {
                    $text .= $pathFragments[$i]."/";
                }
                $text .= $unqualifiedFilename;
                $this->log("DEBUGGING IN BULK DELETION : Attempt to delete text file at <$text.txt>");
                $storage->unlink($text.".txt");
                $dir = $storage->opendir("files_trashbin/files");
                if ($dir !== false) {
                    $nameToSearch = explode(".", $filename)[0];
                    $this->log("FILENAME TO SEARCH IN TRASH BIN : $nameToSearch");
                    while (($file = readdir($dir)) !== false) {
//                        $this->log("LISTING FILES IN TRASH BIN : $file");
                        $targetName = explode(".", $file)[0];
                        if ($targetName === $nameToSearch) {
                            $this->log("FIND THE FILE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!! : $file");
                            $this->recordingMapper->deleteFileRowInFilecacheByName($file);
                            $storage->unlink("files_trashbin/files/".$file);
                        }
                    }
                    closedir($dir);
                    $this->recordingMapper->deleteFileRowInFileTrashBinByName($filename);
                    $this->recordingMapper->deleteFileRowInFileTrashBinByName($unqualifiedFilename.".txt");
                }
            }
        }
        $this->recordingMapper->deleteRecordingsByIds($idsToDelete);
    }

}