<?php
namespace LVTech\Services\Soundcloud;

/**
 * Very Simple Soundcloud Client (basically, just what we need for stuff we do).
 *
 * @author Tim Lytle <tim@timlytle.net>
 */
class CachedClient extends Client
{
    /**
     * @var \Zend\Cache\Storage\StorageInterface
     */
    protected $cache;

    /**
     * @param string $key
     * @param string $api
     */
    public function __construct($key = null, $api = self::API, \Zend\Cache\Storage\StorageInterface $cache)
    {
        $this->cache = $cache;
        parent::__construct($key, $api);
    }

    public function getOembed($url, $uri = self::API_OEMBED)
    {
        $hash = md5($url.$uri);
        if(!$return = $this->cache->getItem($hash)){
            $return = parent::getOembed($url, $uri);
            $this->cache->setItem($hash, serialize($return));
        } else {
            $return = unserialize($return);
        }

        return $return;
    }
}