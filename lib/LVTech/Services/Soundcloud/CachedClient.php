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

    /**
     * Grab a playlist. Fun times.
     *
     * @param string $id
     */
    public function getPlaylist($id, $uri = self::API_PLAYLIST)
    {
        if(!$return = $this->cache->getItem($id . $uri)){
            $return = parent::getPlaylist($id, $uri);
            $this->cache->setItem($id . $uri, $return);
        }

        return $return;
    }

    public function getOembed($url, $uri = self::API_OEMBED)
    {
        if(!$return = $this->cache->getItem($url . $uri)){
            $return = parent::getOembed($url, $uri);
            $this->cache->setItem($url . $uri, $return);
        }

        return $return;
    }
}