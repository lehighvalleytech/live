<?php
namespace IceCast; 
use Zend\Log\Filter\Mock;

class Server
{
    protected $client;
    
    protected $server;
    
    public function __construct($server)
    {
        $this->server = $server;
    }
    
    /**
     * Check if the server is online.
     */
    public function isOnline()
    {
        $client = $this->getClient();
        $client->setUri($this->server)
               ->setMethod(\Zend\Http\Request::METHOD_GET);
               
        $response = $client->send();
        
        if($response->getStatusCode() == $response::STATUS_CODE_200){
            return true;
        }
        
        return false;
    }
    
    public function getMount($mount)
    {
        //check if server is online
        if(!$this->isOnline()){
            return new Mount();
        }
        
        //make request
        $client = $this->getClient();
        $client->setUri($this->server . '/?' . http_build_query(array('mount' => '/' . $mount)));
        $client->setMethod(\Zend\Http\Request::METHOD_GET);
        $response = $client->send();

        return new Mount($response->getBody());
        
        //parse page
        $dom = new \Zend\Dom\Query($response->getBody());

        $results = $dom->execute('.newscontent > table td');
        
        $param = null;
        
        $data = array();
        foreach($results as $result){
            if(is_null($param)){
                $param = $result->textContent;
                continue;
            }
            
            $data[$param] = $result->textContent;
            $param = null;
        }
        
        var_dump($data);
        
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
    
    /**
     * @param \Zend\Http\Client $client
     */
    public function setClient(\Zend\Http\Client $client)
    {
        $this->client = $client;
    }
    
}