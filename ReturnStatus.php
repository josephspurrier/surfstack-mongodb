<?php
/**
 * MongoDB Return Status Handler
 *
 * @package SurfStack
 * @copyright Copyright (C) 2013 Joseph Spurrier. All rights reserved.
 * @author Joseph Spurrier (http://www.josephspurrier.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0.html
 */

namespace SurfStack\MongoDB;

class ReturnStatus
{
    private $arr;
    private $id;
    
    function __construct(array $result)
    {
        $this->arr = $result;
    }
    
    function setId(\MongoId $id)
    {
        $this->id = $id;
    }
    
    function isOk()
    {
        if (isset($this->arr['ok'])) return (bool) $this->arr['ok'];
        else return false;
    }
    
    function getConnectionId()
    {
        if (isset($this->arr['connectionId'])) return $this->arr['connectionId'];
        else return false;
    }
    
    function getAffectedRecords()
    {
        if (isset($this->arr['n'])) return (int)$this->arr['n'];
        else return false;
    }
    
    function getErrorMessage()
    {
        if (isset($this->arr['err'])) return $this->arr['err'];
        else return false;
    }
    
    function getErrorMessageAlt()
    {
        if (isset($this->arr['errmsg'])) return $this->arr['errmsg'];
        else return false;
    }
    
    function getErrorCode()
    {
        if (isset($this->arr['code'])) return (int)$this->arr['code'];
        else return false;
    }
    
    function getNewID()
    {
        if (!is_null($this->id)) return $this->id;
        else if (isset($this->arr['upserted'])) return new Mongoid($this->arr['upserted']);
        else if (isset($this->arr['updatedExisting'])) return new Mongoid($this->arr['updatedExisting']);
        else return false;
    }
    
    function isTimeout()
    {
        if (isset($this->arr['wtimeout'])) return true;
        else return false;
    }
    
    function getWaitedTimeout()
    {
        if (isset($this->arr['waited'])) return (int) $this->arr['waited'];
        else return false;
    }
    
    function getReplicationTime()
    {
        if (isset($this->arr['wtime'])) return (int) $this->arr['wtime'];
        else return false;
    }
    
    
}