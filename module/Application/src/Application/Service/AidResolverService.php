<?php
namespace Application\Service;

use Zend\Http\Client;
use Zend\Json\Json;

class AidResolverService {
    const CACHE_DIR = "/tmp"; // XXX no time for anything better
    
    private $sm;
    
    public function setServiceManager($sm)
    {
        $this->sm = $sm;
    }
    
    public function getPartnersArray()
    {
        $url = sprintf("http://api.openaid.se/api/v1/partner_organization");
        $body = self::getUrl($url);
        return $body;
    }
    
    public function getContributionsArray($countryId, $year)
    {
        $url = sprintf("http://api.openaid.se/api/v1/contribution?country=%d&year=%d", $countryId, $year);
        $body = self::getUrl($url);
        return $body;
    }
    
    /**
     * Resolves the URL
     * 
     * @param string $url
     * @param array  $options (optional)
     * @return array or FALSE
     */
    private static function getUrl($url, $options = array())
    {
        $hash = sha1(serialize(array($url, $options)));
        $cachedFilename = self::CACHE_DIR . $hash;
        if (file_exists($cachedFilename)) {
            if (filemtime($cachedFilename) < strtotime("-1 hour"))
                unlink($cachedFilename);
            else {
                $body = file_get_contents($cachedFilename);
                return unserialize($body);
            }
        }
        
        $defaults = array(
        	'maxredirects' => 0,
        	'timeout'      => 10
        );
        $mergedOptions = array_merge($defaults, $options);
        $client = new Client($url, $mergedOptions);        
        $response = $client->send();
        if ($response->isSuccess()) {
            $encodedBody = $response->getBody();
            $body = Json::decode($encodedBody);
            file_put_contents($cachedFilename, serialize($body));
            return $body;
        } else 
            return FALSE;
    }
}
?>