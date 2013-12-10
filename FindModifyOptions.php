<?php
/**
 * MongoDB FindandModify Options
 *
 * @package SurfStack
 * @copyright Copyright (C) 2013 Joseph Spurrier. All rights reserved.
 * @author Joseph Spurrier (http://www.josephspurrier.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0.html
 */

namespace SurfStack\MongoDB;

class FindModifyOptions
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
     * Determines which document the operation will modify if the query selects multiple documents
     * @param array $arr
     */
    function setSort(array $arr)
    {
        $this->arr['sort'] = $arr;
    }
    
    /**
     * Optional if update field exists, when TRUE, removes the selected document
     */
    function setRemoveEnabled()
    {
        $this->arr['remove'] = true;
    }
    
    /**
     * Optional if update field exists, when FALSE, does NOT remove the selected document (default)
     */
    function setRemoveDisabled()
    {
        $this->arr['remove'] = false;
    }
    
    /**
     * Optional if remove field exists, performs an update of the selected document
     * @param array $arr
     */
    function setUpdate(array $arr)
    {
        $this->arr['update'] = $arr;
    }

    /**
     * Optional, when TRUE, returns the modified document rather than the original, the findAndModify method ignores the new option for remove operations
     */
    function setNewEnabled()
    {
        $this->arr['new'] = true;
    }
    
    /**
     * Optional, when FALSE, returns the original, the findAndModify method ignores the new option for remove operations (default)
     */
    function setNewDisabled()
    {
        $this->arr['new'] = false;
    }

    /**
     * Optional, used in conjunction with the update field, when TRUE, the findAndModify command creates a new document if the query returns no documents
     */
    function setUpsetEnabled()
    {
        $this->arr['upset'] = true;
    }
    
    /**
     * Optional, used in conjunction with the update field, when FALSE, the findAndModify command does NOT create a new document if the query returns no documents (default)
     */
    function setUpsetDisabled()
    {
        $this->arr['upset'] = false;
    }
}