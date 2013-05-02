<?php
namespace Application\Service;

class AidProcessorService
{
    private $sm;
    
    public function setServiceManager($sm)
    {
        $this->sm = $sm;
    }
    
    public function listAidAgenciesByYearAndContributions($contributionsArray, $year)
    {
        $processed = array();
        foreach ($contributionsArray as $id => $entry) {
            // XXX no organization for this entry?
            if (empty($entry->partner_organization)) {
                $entry->partner_organization = "Other";
            }
            
            if (!array_key_exists($entry->partner_organization, $processed)) {
                if (isset($entry->outcome_usd->$year)) {
                    $processed[$entry->partner_organization] = $entry->outcome_usd->$year;
                    // XXX bad data coming in?
                    if ($entry->outcome_usd->$year < 0) {
                        /*print_r($entry);
                        die();*/ // this could be a support request ticket
                    }
                }
            } else {
                $processed[$entry->partner_organization] += $entry->outcome_usd->$year;
            }            
        }
        
        uasort($processed, function($a, $b) {
        	if ($a == $b)
        	    return 0;
        	
        	return $a < $b ? 1 : -1;
        });
        return $processed;
    }
    
    public function makePartnersHumanReadable($contributionsArray, $partnersArray)
    {
        $printableArray = array();
        
        $hashMap = array();
        foreach ($partnersArray as $id => $partner) {
            $hashMap[$partner->id] = $partner->name;
        }
        
        foreach ($contributionsArray as $partnerId => $contributionUsDollars) {
            if (isset($hashMap[$partnerId]))
                $printableArray[] = array($hashMap[$partnerId] => $contributionUsDollars);
            else 
                $printableArray[] = array('Other' => $contributionUsDollars);
        }
        
        return $printableArray;
    }
}
?>