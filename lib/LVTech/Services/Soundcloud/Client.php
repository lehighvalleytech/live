<?php
namespace LVTech\Services\Soundcloud;

/**
 * Very Simple Soundcloud Client (basically, just what we need for stuff we do).
 *
 * @author Tim Lytle <tim@timlytle.net>
 */
class Client
{
    const API = 'http://api.soundcloud.com';
    const API_PLAYLIST = '/playlists';
    const API_OEMBED = '/oembed';

    /**
     * @var string
     */
    protected $api;

    /**
     * API Key
     * @var string
     */
    protected $key;

    /**
     * @var \Zend\Http\Client
     */
    protected $client;

    /**
     * @param string $key
     * @param string $api
     */
    public function __construct($key = null, $api = self::API)
    {
        $this->key = $key;
        $this->api = $api;
    }

    /**
     * Grab a playlist. Fun times.
     *
     * @param string $id
     */
    public function getPlaylist($id, $uri = self::API_PLAYLIST)
    {
        $client = $this->getClient();
        $request = $client->getRequest();

        $request->setMethod($request::METHOD_GET)
                ->setUri($this->api . $uri . '/' . $id . '.json')
                ->getQuery()->set('client_id', $this->key);

        $response = $client->dispatch($request);

        if(!$response->isOk()){
            throw new \RuntimeException('unexpected result from soundcloud');
        }

        $playlist = new Resource\Playlist($response->getBody());

        return $playlist;
    }

    public function getOembed($url, $uri = self::API_OEMBED)
    {
        $client = $this->getClient();
        $request = $client->getRequest();

        $request->setMethod($request::METHOD_GET)
            ->setUri($this->api . $uri)
            ->getQuery()->set('client_id', $this->key)
                        ->set('format', 'json')
                        ->set('url', $url)
                        ->set('show_artwork', 'false');

        $response = $client->dispatch($request);

        if(!$response->isOk()){
            throw new \RuntimeException('unexpected result from soundcloud');

        }

        $oembed = new Resource\Oembed($response->getBody());

        return $oembed;
    }

    /**
     * @param \Zend\Http\Client $client
     * @return $this
     */
    public function setClient(\Zend\Http\Client $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return \Zend\Http\Client
     */
    public function getClient()
    {
        if(empty($this->client)){
            $this->setClient(new \Zend\Http\Client());
        }

        return $this->client;
    }
}