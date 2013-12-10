<?php
/**
 * MongoDB Options
 *
 * @package SurfStack
 * @copyright Copyright (C) 2013 Joseph Spurrier. All rights reserved.
 * @author Joseph Spurrier (http://www.josephspurrier.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0.html
 */

namespace SurfStack\MongoDB;

class Options
{    
    private $passthru = NULL;
    private $arr = array();
        
    function __construct(array $passthru=NULL)
    {
        if (is_array($passthru)) $this->passthru = $passthru;
    }
    
    function getArray()
    {
        if (!is_null($this->passthru)) return $this->passthru;
        else return $this->arr;
    }
    
    /**
     * Remove at most one records matching this criteria
     */
    function setJustOneEnabled()
    {
        $this->arr['justOne'] = true;
    }
    
    /**
     * Remove all records matching this criteria (default)
     */
    function setJustOneDisabled()
    {
        $this->arr['justOne'] = false;
    }
    
    /**
     * If no document matches $criteria, a new document will be inserted
     */
    function setUpsertEnabled()
    {
        $this->arr['upsert'] = true;
    }
    
    /**
     * If no document matches $criteria, a new document will NOT be inserted (default)
     */
    function setUpsertDisabled()
    {
        $this->arr['upsert'] = false;
    }

    /**
     * All documents matching $criteria will be updated (default)
     */
    function setMultipleEnabled()
    {
        $this->arr['multiple'] = true;
    }
    
    /**
     * Only only document matching $criteria will be updated
     */
    function setMultipleDisabled()
    {
        $this->arr['multiple'] = false;
    }
    
    /**
     * A write will not be followed up with a GLE call, and therefore not checked ("fire and forget")
     */
    function setConcernUnacknowledged()
    {
        $this->arr['w'] = 0;
    }
    
    /**
     * The write will be acknowledged by the server (the primary on replica set configuration) (default)
     */
    function setConcernAcknowledged()
    {
        $this->arr['w'] = 1;
    }
    
    /**
     * The write will be acknowledged by the primary server, and replicated to N-1 secondaries
     */
    function setConcernReplicaSetAcknowledged()
    {
        $this->arr['w'] = N;
    }
    
    /**
     * The write will be acknowledged by the majority of the replica set (including the primary), this is a special reserved string
     */
    function setConcernMajorityAcknowledged()
    {
        $this->arr['w'] = majority;
    }
    
    /**
     * The write will be acknowledged by members of the entire tag set
     * @param string $tagSet
     */
    function setConcernReplicaSetTagAcknowledged($tagSet)
    {
        $this->arr['w'] = $tagSet;
    }
    
    /**
     * Forces the insert to be synced to the journal before returning success
     */
    function setJournaledEnabled()
    {
        $this->arr['j'] = true;
    }
    
    /**
     * Forces the insert to NOT be synced to the journal before returning success (default)
     */
    function setJournaledDisabled()
    {
        $this->arr['j'] = false;
    }

    /**
     * How long to wait for WriteConcern acknowledgement,t he default value for MongoClient is 10000 milliseconds
     * @param int $int
     */
    function setWTimeout(int $int)
    {
        $this->arr['wtimeout'] = $int;
    }
    
    /**
     * Integer, defaults to MongoCursor::$timeout, if acknowledged writes are used, this sets how long (in milliseconds) for the client to wait for a database response
     * @param int $int
     */
    function setTimeout(int $int)
    {
        $this->arr['timeout'] = $int;
    }
}