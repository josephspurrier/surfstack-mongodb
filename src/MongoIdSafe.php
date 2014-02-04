<?php

namespace SurfStack\MongoDB;

/**
 * Create a MongoId that won't throw an error if passed an invalid value
 * 
 * @author georgedot dont spam me gmail caom
 * @see http://www.php.net/manual/en/class.mongoid.php#112598
 *
 */
class MongoIdSafe {

    /**
     * Return a MongoID using the ID or a new MongoID if the ID is invalid
     * @param string $id
     * @return \MongoId
     */
    public static function create($id)
    {
        try
        {
            return new \MongoId($id);
        }
        catch (\MongoException $ex)
        {
            return new \MongoId();
        }
    }
}

?>