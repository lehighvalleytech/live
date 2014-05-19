<?php
namespace LVTech\Radio\Feed;
use LVTech\Services\Soundcloud\Resource\Playlist;
use LVTech\Services\Soundcloud\Resource\Track;
use Zend\Feed\Writer\Feed as FeedWriter;
use Zend\Feed\Writer\Entry as Entry;

/**
 * @author Tim Lytle <tim@timlytle.net>
 */
class Startup extends \LVTech\Radio\Feed
{
    /**
     * TODO: make this more abstract and build from config file
     * @param FeedWriter $feed
     * @param Playlist $playlist
     */
    protected function customFeed(FeedWriter $feed, Playlist $playlist)
    {
        $this->feed->setTitle('Startup Lehigh Valley [LVTech Radio]')
                   ->setLink('http://radio.lehighvalleytech.org/')
                   ->setFeedLink('http://radio.lehighvalleytech.org/feed/startup', $this->type)
                   ->setDescription('Wayne and Anthony talk everything Startups.')
                   ->setDateModified(new \DateTime());

    }

    protected function customTrack(Entry $entry, Track $track){}
}