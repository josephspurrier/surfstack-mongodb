SurfStack MongoDB Library Wrapper in PHP
========================================

* There are two classes (Options and Find_Modify_Options) that ease the use of options in MongoDB operations
* There is a class (Return_Status) that makes it easy to check whether a read or write operation succeeded as well as count the number of affected records
* Requires the [MongoDB PHP extension](http://www.php.net/manual/en/book.mongo.php)
* Driver is an abstract class so you'll need to extend it to use it
* All of the classes were created using information from the [PHP Manual](http://www.php.net/manual/en/class.mongocollection.php)

Strengths:
* Driver has no mixed-typed return values
* Limits direct access to MongoDB objects

Instructions
------------
Create a new PHP file called Test.php and add this:

```php
<?php

// Add the namespace
namespace SurfStack\MongoDB;

// Extend the Driver class
class Test extends Driver
{
    // Set the name of the datbase
    protected $database = 'test';

    // Set the name of the collection
    protected $collection = 'test1';

    // Added a record to the database
    function write()
    {
        // Test out the insert function
        $result = $this->insert(array(
            '_id' => new \MongoId(),
            'first_name' => 'Foo',
            'last_name' => 'Bar',
            'creation_date' => new \MongoDate(),
        ));
        
        // Return Return_Status object
        return $result;
    }
    
    // Retrieve records from database
    function read()
    {
    	// Set cursor options
        $this->setLimit(0);
        $this->setFields(array('_id' => 0));
        $this->setSkip(0);
        $this->setSort(array('first_name' => 1));
    
        $result = $this->find(array(
            'first_name' => 'Foo',
        ));
        
        // Return Return_Status object
        return $result;
    }
    
    // Clear records from database
    function clear()
    {
        // Set the options
        $opt = new Options();
        // Remove all
        $opt->setJustOneDisabled();
        
        // Remove matching records
        $result = $this->remove(array(
            'first_name' => 'Foo',
        ), $opt);
        
        // Return Return_Status object
        return $result;
    }
}

?>
```

Then create an index.php file and add this:

```php
<?php

// To start using the library, include each PHP file in your application:
require_once 'Connection_Info.php';
require_once 'Driver.php';
require_once 'Find_Modify_Options.php';
require_once 'Options.php';
require_once 'Return_Status.php';
require_once 'Test.php';

// Create an instance of the class
$db = new \SurfStack\MongoDB\Test();

// Add a record to the database
$result = $db->write();

// If record write was successful
if ($result->isOk())
{
    echo 'Successfully wrote record to database.';
}
// Else the record write was not successful
else
{ 
    echo 'Error writing record to database.';
}

// Write a line break
echo nl2br(PHP_EOL);

// Get all records from the database
$result = $db->read();

echo 'Found '.count($result).' records(s).';

// Write a line break
echo nl2br(PHP_EOL);

// Output results
foreach($result as $record)
{
    echo 'ID is '.$record['_id'].' for ';
    echo $record['first_name'].' '.$record['last_name'];

    // Write a line break
    echo nl2br(PHP_EOL);
}

// Clear all matching records from the database
$result = $db->clear();

// If record delete was successful
if ($result->getAffectedRecords())
{
    echo 'Successfully deleted '.$result->getAffectedRecords().' record(s) from database.';
}
// Else the record delete was not successful
else
{
    echo 'No records deleted from the database.';
}

?>
```
    
When you execute index.php, you'll add a new record to the database, retrieve
it, and then delete it.