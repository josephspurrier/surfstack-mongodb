<?php
/**
 * MongoDB library wrapper
 *
 * @package SurfStack
 * @copyright Copyright (C) 2014 Joseph Spurrier. All rights reserved.
 * @author Joseph Spurrier (http://www.josephspurrier.com)
 * @license http://www.apache.org/licenses/LICENSE-2.0.html
 */

namespace SurfStack\MongoDB;

abstract class Driver
{
    //TODO: Analyze Return_Status for all functions
    
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
     * @var \MongoClient
     */
    private $dbClient;
    
    /**
     * MongoDB collection
     * @var \MongoCollection
     */
    private $dbCollection;
    
    /**
     * MongoDB instance
     * @var \MongoDB
     */
    private $dbInstance;
    
    /**
     * MongoGridFS instance
     * @var \MongoGridFS
     */
    private $gridFS;
    
    // MongoCursor default options
    private $fields = array();
    private $limit = 0;
    private $skip = 0;
    private $sort = array();
    
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
        $this->gridFS = $this->dbInstance->getGridFS();
    }

    /**
     * Find matching documents in the database
     * @param array $query The fields for which to search
     * @param array $fields Fields of the results to return
     * @return array Returns an array for the search results
     */
    protected function find(array $query=array(), array $fields=array())
    {    
        // Get the MongoCursor of results
        $result = $this->dbCollection->find($query, $fields);
        
        // Set the MongoCursor options
        $result->fields($this->fields);
        $result->limit($this->limit);
        $result->skip($this->skip);
        $result->sort($this->sort);
        
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
     * @return array Returns the original document, or the modified document
     * when new is set
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
     * Update records based on a given criteria (updates one document by default)
     * @param array $criteria An array description of the objects to update
     * @param array $new_object An array object with which to update the
     * matching records
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
        $result = $this->dbCollection->update(
            $criteria,
            $new_object,
            $options->getArray()
        );
    
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
     * @return array Returns the document to which the reference refers or empty
     * array if the document does not exist (the reference is broken)
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
    
    /**
     * Sets the fields for a query (MongoCursor)
     * @param array $fields Fields to return (or not return)
     */
    protected function setFields(array $fields)
    {
        $this->fields = $fields;
    }
    
    /**
     * Limits the number of results returned (MongoCursor)
     * @param int $num The number of results to return
     */
    protected function setLimit($num)
    {
        // If the number is an int
        if (is_int($num))
        {
            $this->limit = $num;
        }
        // Else if verbose is enabled, kill the script on error
        elseif ($this->verbose)
        {
            die('must be an instance of integer');
        }
    }
    
    /**
     * Skips a number of results (MongoCursor)
     * @param int $num The number of results to skip
     */
    protected function setSkip($num)
    {
        // If the number is an int
        if (is_int($num))
        {
            $this->skip = $num;
        }
        // Else if verbose is enabled, kill the script on error
        elseif ($this->verbose)
        {
            die('must be an instance of integer');
        }
    }
    
    /**
     * Sorts the results by given fields (MongoCursor)
     * @param array $sort An array of fields by which to sort. Each element in
     * the array has as key the field name, and as value either 1 for ascending
     * sort, or -1 for descending sort
     */
    protected function setSort(array $sort)
    {
        $this->sort = $sort;
    }

    /**
     * Lists all of the databases available
     * @return Returns an associative array containing three fields. The first
     * field is databases, which in turn contains an array. Each element of the
     * array is an associative array corresponding to a database, giving the
     * database's name, size, and if it's empty. The other two fields are
     * totalSize (in bytes) and ok, which is 1 if this method ran successfully.
     */
    protected function listDBs()
    {
        return $this->dbClient->listDBs();
    }
    
    /**
     * Gets an array of all MongoCollections for this database
     * @param bool $includeSystemCollections Include system collections
     * @return array Returns an array of MongoCollection objects
     */
    protected function listCollections($includeSystemCollections = false)
    {
        return $this->dbInstance->listCollections($includeSystemCollections);
    }
    
    /**
     * Creates a collection
     * @param string $name The name of the collection
     * @param array $options An array containing options for the collections.
     * Each option is its own element in the options array, with the option
     * name listed below being the key of the element. The supported options
     * depend on the MongoDB server version. See here for the options
     * supported: http://www.php.net/manual/en/mongodb.createcollection.php
     */
    protected function createCollection($name, array $options = array())
    {
        return $this->dbInstance->createCollection($name, $options);
    }
    
    /**
     * Drops the current database
     * @return Return_Status Returns object with: ok, dropped
     */
    protected function dropDatabase()
    {
        // Drop the database
        $result = $this->dbInstance->drop();

        // Set the status
        $status = new Return_Status($result);
        
        // Return the status
        return $status;
    }
    
    /**
     * Drops the current collection
     * @return \SurfStack\MongoDB\Return_Status Returns object with: ok, errmsg
     */
    protected function dropCollection()
    {
        // Drop the database
        $result = $this->dbCollection->drop();
        
        // Set the status
        $status = new Return_Status($result);
        
        // Return the status
        return $status;
    }
    
    /**
     * Clone a collection (collection to be cloned cannot be empty)
     * @param string $oldName Old collection name
     * @param string $newName New collection name
     * @return \SurfStack\MongoDB\Return_Status Return_Status Returns object with: ok, retval
     */
    protected function cloneCollection($oldName, $newName)
    {
        $result = $this->dbInstance->execute('db.'.$oldName.'.find().forEach( function(x){db.'.$newName.'.insert(x)} );');
        
        // Set the status
        $status = new Return_Status($result);
        
        // Return the status
        return $status;
    }

    /**
     * Renames a collection
     * @param string $oldName Old collection name
     * @param string $newName New collection name
     * @return \SurfStack\MongoDB\Return_Status Return_Status Returns object with: ok, retval
     */
    protected function renameCollection($oldName, $newName)
    {
        $result = $this->dbInstance->execute('db.'.$oldName.'.renameCollection("'.$newName.'");');
        
        // Set the status
        $status = new Return_Status($result);
        
        // Return the status
        return $status;
    }

    /**
     * Stores a file in the database
     * @param string $filename Name of the file to store
     * @param array $metadata Other metadata fields to include in the file document
     * @param Options $options Options for the store (Only WriteConcern)
     * @return \MongoId Returns the _id of the saved file document
     */
    protected function storeFile($filename, array $metadata = array(), Options $options = null)
    {
        // Update the options array
        $options = (is_null($options) ? new Options() : $options);
        
        return $id = $this->gridFS->storeFile($filename, $metadata, $options->getArray());
    }
        
    /**
     * Delete a file from the database
     * @param \MongoId $id _id of the file to remove
     * @return boolean Returns if the remove was successfully sent to the database
     */
    protected function deleteFile(\MongoId $id)
    {
        return $this->gridFS->delete($id);
    }
    
    /**
     * Drops the files and chunks collections from EVERY collection
     * @return \SurfStack\MongoDB\Return_Status Returns object with:
     */
    protected function dropFileCollection()
    {
        $result = $this->gridFS->drop();        

        // Set the status
        $status = new Return_Status($result);
        
        // Return the status
        return $status;
    }
    
    /**
     * Retrieve a file from the database
     * @param \MongoId $id _id of the file to find
     * @return \MongoGridFSFile | NULL Returns the file, if found, or NULL
     */
    protected function getFile(\MongoId $id)
    {
        // Get the file
        $file = $this->gridFS->get($id);
        
        // If the file is found
        if (is_a($file, 'MongoGridFSFile'))
        {
            return $file;
        }
        // Else the file is not found
        else
        {
            return null;
        }
    }
    
    /**
     * Download a file to your computer
     * @param \MongoGridFSFile $file
     */
    protected function downloadFile(\MongoGridFSFile $file)
    {
        // Verify the file actually exists before downloading
        // This is the only way determine if the file exists in the database
        // or not
        $tempCollection = $this->dbClient->selectCollection($this->database, 'fs.chunks');
        $result = $tempCollection->findOne(array(
            'files_id' => $file->file['_id'],
        ));

        // If the file is not found
        if (empty($result))
        {
            // Inform the user
            echo 'File not found';
            exit;
        }
        
        // Output the file directly to the screen so the user is prompted to
        // download
        header('Content-Type: '.$file->file['type']);
        header('Content-Disposition: attachment; filename='.$file->file['name']);
        header('Content-Transfer-Encoding: binary');
        echo $file->getBytes();
        exit;        
    }
    
    /**
     * Find matching files in the database
     * @param array $query The fields for which to search
     * @param array $fields Fields of the results to return
     * @return array Returns an array of MongoGridFSFile objets
     */
    protected function findFiles(array $query=array(), array $fields=array())
    {    
        // Get the MongoCursor of results
        $result = $this->gridFS->find($query, $fields);
        
        // Set the MongoCursor options
        $result->fields($this->fields);
        $result->limit($this->limit);
        $result->skip($this->skip);
        $result->sort($this->sort);
        
        // Return the results
        if ($result == null || $result == false) return array();
        // Return the array with integers as the keys
        return iterator_to_array($result, false);
    }
    
    /**
     * Find one matching file in the database
     * @param array | string $query The filename or criteria for which to search
     * @param array $fields Fields of the results to return
     * @return \MongoGridFSFile | NULL Returns a MongoGridFSFile or NULL
     */
    protected function findOneFile($query=array(), array $fields=array())
    {
        // Find
        $result = $this->gridFS->findOne($query, $fields);
    
        // Return the results
        if ($result == null || $result == false) return null;
        // Return an object
        return $result;
    }
}