<?php
/**
 * MongoDB library
 *
 * @package SurfStack
 * @copyright Copyright (C) 2014 Joseph Spurrier. All rights reserved.
 * @author Joseph Spurrier (http://www.josephspurrier.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0.html
 */

namespace SurfStack\MongoDB;

abstract class Driver
{
    /**
     * Database name
     * @var string
     */
    protected $database = false;
    /**
     * Collection name
     * @var string
     */
    protected $collection = false;
    /**
     * Verbose will die() on errors
     * @var bool
     */
    protected $verbose = true;

    /**
     * MongoDB client
     * @var MongoClient
     */
    private $dbClient;
    
    /**
     * MongoDB collection
     * @var MongoCollection
     */
    private $dbCollection;
    
    /**
     * MongoDB instance
     * @var MongoDB
     */
    private $dbInstance;
    
    function __construct()
    {
        // Throw an error is the extension is not loaded
        if(!class_exists('Mongo'))
        {
            die('MongoDB extension is not found');
        }
        
        // Throw errors if the child class does not specify the variables required
        if (!$this->database) die('Must specify a protected $database.');
        else if (!$this->collection) die('Must specify a protected $collection.');
        
        // Connect to database
        $this->connectAnonymous(new Connection_Info());
    }
    
    /**
     * Connect to a locally secured MongoDB instance
     * @param Connection_Info $ci
     */
    private function connectAnonymous(Connection_Info $ci)
    {
        try
        {
            // Establish a connection to the database
            $this->dbClient = new \MongoClient($ci->getConnectionString());
            
            // Select the database and collection
            $this->selectCollection($this->database, $this->collection);
        }
        catch (\MongoConnectionException $e)
        {
            die('Could not connect to MongoDB.');
        }
    }
    
    /**
     * Select a new collection object
     * @param string $database
     * @param string $collection
     */
    protected function selectCollection($database, $collection)
    {
        // Select the database and collection
        $this->dbCollection = $this->dbClient->selectCollection($database, $collection);
        $this->dbInstance = $this->dbClient->{$database};
    }

    /**
     * Find matching documents in the database
     * @param array $query The fields for which to search
     * @param array $fields Fields of the results to return
     * @return array Returns an array for the search results
     */
    protected function find(array $query=array(), array $fields=array())
    {    
        // Find
        $result = $this->dbCollection->find($query, $fields);
    
        // Return the results
        if ($result == null || $result == false) return array();
        // Return the array with integers as the keys
        return iterator_to_array($result, false);
    }
    
    /**
     * Find one matching document in the database
     * @param array $query The fields for which to search
     * @param array $fields Fields of the results to return
     * @return array Returns an array for the search results
     */
    protected function findOne(array $query=array(), array $fields=array())
    {
        // Find
        $result = $this->dbCollection->findOne($query, $fields);
    
        // Return the results
        if ($result == null || $result == false) return array();
        // Return a single array
        return $result;
    }
    
    /**
     * Modifies and returns a single document
     * @param array $query The fields for which to search
     * @param array $update The update query
     * @param array $fields Fields of the results to return
     * @param Find_Modify_Options $options Options for operation
     * @return array Returns the original document, or the modified document when new is set
     */
    protected function findAndModify(array $query, array $update, array $fields=array(), Find_Modify_Options $options=NULL)
    {
        // If verbose is enabled, kill the script on error
        if ($this->verbose)
        {
            if (empty($query)) die('no elements in doc');
            else if (empty($update)) die('no elements in doc');
        }
    
        // Update the options array
        $options = (is_null($options) ? new Find_Modify_Options() : $options);
    
        // Find/Modify
        $result = $this->dbCollection->findAndModify($query, $update, $fields, $options->getArray());
    
        // Return the results
        if ($result == null || $result == false) return array();
        // Return a single array
        return $result;
    }
    
    /**
     * Insert a document into the database
     * @param array|object $a An array or object
     * @param Options $options Options for operation
     * @return Return_Status Easy result object
     */
    protected function insert($a, Options $options=NULL)
    {        
        // If verbose is enabled, kill the script on error
        if ($this->verbose)
        {
            if (is_array($a) && empty($a)) die('no elements in doc');
        }
        
        // Create an Id if needed
        if (is_array($a) && !isset($a['_id'])) $a['_id'] = new \MongoId();

        // Update the options array
        $options = (is_null($options) ? new Options() : $options);
        
        // Insert
        $result = $this->dbCollection->insert($a, $options->getArray());

        // Set the status
        $status = new Return_Status($result);
        if (is_array($a) && isset($a['_id'])) $status->setId($a['_id']);
        
        // Return the status
        return $status;
    }
    
    /**
     * Update an existing database object or insert this object
     * @param array|object $a An array or object
     * @param Options $options Options for operation
     * @return Return_Status Easy result object
     */
    protected function save($a, Options $options=NULL)
    {
        // If verbose is enabled, kill the script on error
        if ($this->verbose)
        {
            if (is_array($a) && empty($a)) die('no elements in doc');
        }
    
        // Create an Id if needed
        if (is_array($a) && !isset($a['_id'])) $a['_id'] = new \MongoId();
    
        // Update the options array
        $options = (is_null($options) ? new Options() : $options);
    
        // Save
        $result = $this->dbCollection->save($a, $options->getArray());
    
        // Set the status
        $status = new Return_Status($result);
        if (is_array($a) && isset($a['_id'])) $status->setId($a['_id']);
    
        // Return the status
        return $status;
    }
    
    /**
     * Update an existing database object
     * @param array $criteria An array description of the objects to update
     * @param array $new_object An array object with which to update the matching records
     * @param Options $options Options for operation
     * @return Return_Status Easy result object
     */
    protected function update(array $criteria, array $new_object, Options $options=NULL)
    {
        // If verbose is enabled, kill the script on error
        if ($this->verbose)
        {
            if (empty($criteria)) die('no elements in doc');
            else if (empty($new_object)) die('no elements in doc');
        }
    
        // Update the options array
        $options = (is_null($options) ? new Options() : $options);
    
        // Update
        $result = $this->dbCollection->update($criteria, $new_object, $options->getArray());
    
        // Set the status
        $status = new Return_Status($result);
    
        // Return the status
        return $status;
    }
    
    /**
     * Remove a document from the database
     * @param array $criteria Description of records to remove
     * @param array $options Options for operation
     * @return Return_Status Easy result object
     */
    protected function remove(array $criteria=array(), Options $options=NULL)
    {
        // Update the options array
        $options = (is_null($options) ? new Options() : $options);
        
        // Remove
        $result = $this->dbCollection->remove($criteria, $options->getArray());
    
        // Set the status
        $status = new Return_Status($result);
        
        // Return the status
        return $status;
    }

    /**
     * Fetches the document pointed to by a database reference
     * @param array $ref Reference to fetch (array is actually a MongoDBRef)
     * @return array Returns the document to which the reference refers or empty array if the document does not exist (the reference is broken).
     */
    protected function getDBRef(array $ref)
    {
        // Get the database reference
        $result = $this->dbCollection->getDBRef($ref);
        
        // Return the results
        if ($result == null || $result == false) return array();
        // Return a single array
        return $result;
    }
}