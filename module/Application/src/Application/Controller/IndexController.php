<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $countryId = 120; // Iraq (no time to lookup Sudan country id)
        
        $aidResolver = $this->serviceLocator->get("Aid/ResolverService");
        $aidProcessor = $this->serviceLocator->get("Aid/ProcessorService");

        $aggregatedArray = array();
        for ($year = date("Y") - 1, $min = $year - 5; $year > $min; $year--) {
            $contributionsArray = $aidResolver->getContributionsArray($countryId, $year);
            $listArray = $aidProcessor->listAidAgenciesByYearAndContributions($contributionsArray, $year);            
            $partnersArray = $aidResolver->getPartnersArray();
            $printableListArray = $aidProcessor->makePartnersHumanReadable($listArray, $partnersArray);
            $aggregatedArray[$year] = $printableListArray;
        }
        
        return new JsonModel($aggregatedArray);
    }
}
