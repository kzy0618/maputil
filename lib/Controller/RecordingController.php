<?php /** @noinspection PhpUndefinedClassInspection */

/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/12/18
 * Time: 12:56 PM
 */

namespace OCA\MapUtil\Controller;


use OCP\AppFramework\Controller;
use OCP\ILogger;
use OCP\IRequest;

class RecordingController extends Controller
{

    private $appName;
    private $logger;
    private $uid;

    /**
     * PageController constructor.
     * @param ILogger $logger
     * @param $AppName
     * @param IRequest $request
     * @param $UserId
     */
    public function __construct(ILogger $logger, $AppName, IRequest $request, $UserId)
    {
        parent::__construct($AppName, $request);
        $this->appName = $AppName;
        $this->logger = $logger;
        $this->uid = $UserId;
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
    public function index() {
        // TODO: Get ALL
    }

    /**
     * all handlers in this controller must be privileged to admins only
     * @NoCSRFRequired
     * @param $id
     */
    public function show($id){
        // TODO: Get one from url
    }

    /**
     * all handlers in this controller must be privileged to admins only
     * @NoCSRFRequired
     */
    public function updateWithUrl() {
        // TODO: update one from url, fill in the params list
    }

    /**
     * all handlers in this controller must be privileged to admins only
     * @NoCSRFRequired
     */
    public function updateWithoutUrl() {
        // TODO: update one in terms of ajax body, fill in the params list
    }

}