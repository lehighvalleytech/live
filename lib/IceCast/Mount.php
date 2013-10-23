<?php
namespace IceCast;
class Mount
{
    const MOUNT_TITLE       = 'Stream Title:';
    const MOUNT_DESCRIPTION = 'Stream Description:';
    const MOUNT_TYPE        = 'Content Type:';
    const MOUNT_CREATED     = 'Mount started:';
    const MOUNT_LISENERS    = 'Current Listeners:';
    const MOUNT_PEAK        = 'Peak Listeners:';
    const MOUNT_GENRE       = 'Stream Genre:';
    const MOUNT_URL         = 'Stream URL:';
    const MOUNT_SONG        = 'Current Song:';
    
    protected $values = array(
        self::MOUNT_CREATED => null,
        self::MOUNT_DESCRIPTION => null,
        self::MOUNT_GENRE => null,
        self::MOUNT_LISENERS => 0,
        self::MOUNT_PEAK => 0,
        self::MOUNT_SONG => null,
        self::MOUNT_TITLE => null,
        self::MOUNT_TYPE => null,
        self::MOUNT_URL => null
    );
    
    protected $online = false;
    
    public function __construct($html = null)
    {
        if(is_null($html)){
            return;
        }
        
        if(is_string($html)){
            $html = new \Zend\Dom\Query($html);
        }
        
        if(!($html instanceof \Zend\Dom\Query)){
            throw new InvalidArgumentException('Expected string or \Zend\Dom\Query');
        }
        
        //parse html
        $results = $html->execute('.newscontent > table td');

        //loop through cells
        $param = null;

        foreach($results as $result){
            //assume if the table was found, it's online
            $this->online = true;
            
            //frst td is the param name
            if(is_null($param)){
                $param = $result->textContent;
                continue;
            }

            //use the param name as key, and the current cell as data
            switch($param){
                case self::MOUNT_CREATED:
                    $this->values[$param] = new \DateTime($result->textContent);
                    break;
                case self::MOUNT_LISENERS:
                case self::MOUNT_PEAK:
                    $this->values[$param] = (int) $result->textContent;
                    break;
                default:
                    $this->values[$param] = (string) $result->textContent;
                    if(empty($this->values[$param])){
                        $this->values[$param] = null;
                    }
            }
            
            //reset the param so the next cell sets the value
            $param = null;
        }
    }
    
    /**
     * Current number of listeners.
     * @return int
     */
    public function getListners()
    {
        return $this->values[self::MOUNT_LISENERS];
    }
    
    /**
     * Peak number of listeners.
     * @return int
     */
    public function getPeak()
    {
        return $this->values[self::MOUNT_PEAK];
    }
    
    /**
     * Date mount started.
     * @return DateTime|null
     */
    public function getCreated()
    {
        return $this->values[self::MOUNT_CREATED];
    }

    public function isOnline()
    {
        return $this->online;
    }
}