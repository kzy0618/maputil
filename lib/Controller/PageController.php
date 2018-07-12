<?php
/**
 * Created by PhpStorm.
 * User: mason
 * Date: 7/12/18
 * Time: 12:55 PM
 */

namespace OCA\MapUtil\Controller;


use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\ILogger;
use OCP\IRequest;

class PageController extends Controller
{

    private $appName;
    private $logger;

    /**
     * PageController constructor.
     * @param ILogger $logger
     * @param $AppName
     * @param IRequest $request
     */
    public function __construct(ILogger $logger, $AppName, IRequest $request)
    {
        parent::__construct($AppName, $request);
        $this->appName = $AppName;
        $this->logger = $logger;
    }

    /**
     * The @NoAdminRequired and @NoCSRFRequired annotations in index’s doc block above turn off security checks, as they’re not necessary for this method.
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function index() {
        $csp = new ContentSecurityPolicy();
        // Allows to access resources from a specific domain. Use * to allow everything from all domains.
        // Here we allow ALL Javascript, images, styles, and fonts from ALL domains.
        // Here we relax these constraints because we consider some developers might want to use cdn links for their frontend libraries (e.g., bootstrap, etc.)
        $csp->addAllowedScriptDomain("*")->addAllowedImageDomain("*")->addAllowedStyleDomain("*")->addAllowedFontDomain("*");
        $response = new TemplateResponse($this->appName, 'main');
        $response->setContentSecurityPolicy($csp);
        // Renders maputil/templates/main.php
        return $response;
    }

}