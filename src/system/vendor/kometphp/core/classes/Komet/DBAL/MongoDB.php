<?php

/**
 * Part of the KometPHP Framework
 * 
 * @package Komet/DBAL
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 */

namespace Komet\DBAL;

/**
 * MongoDB wrapper and abstraction layer
 * 
 * @package Komet/DBAL
 * @author Javier Aguilar
 * @license GPL License
 * @license MIT License
 * 
 */
class MongoDB extends AbstractDBAL
{

    /**
     * 
     * @var \Mongo
     */
    protected $mongo = null;

    /**
     * 
     * @var \MongoDB
     */
    protected $db = null;

    /**
     * Holds all the select options
     *
     * @var  array
     */
    protected $selects = array();

    /**
     * Holds all the where options.
     *
     * @var  array
     */
    protected $wheres = array();

    /**
     * Holds the sorting options
     *
     * @var  array
     */
    protected $sorts = array();

    /**
     * Holds the limit of the number of results to return
     *
     * @var  int
     */
    protected $limit = 999999;

    /**
     * The offset to start from.
     *
     * @var  int
     */
    protected $offset = 0;

    /**
     * 
     * @param string $name Instance name
     * @param array $config Connection configuration
     */
    public function __construct(array $config = array())
    {
        $config = array_merge(array(
            "name" => "default",
            "host" => \Mongo::DEFAULT_HOST,
            "port" => \Mongo::DEFAULT_PORT,
            "schema" => null,
            "username" => null,
            "password" => null,
            "persistent" => false,
            "autoconnect" => false,
            "options" => array()
                ), $config);

        $config["driver"] = "mongo"; //always is mongo
        if ($config['persistent']) {
            $config["options"]['persist'] = 'kometphp_mongo_persist_' . $name;
        }

        if ($config['autoconnect']) {
            $config["options"]['connect'] = true;
        }

        $config['dsn'] = "mongodb://";

        if (!empty($config['username']) and !empty($config['password'])) {
            $config['dsn'] .= "{$config['username']}:{$config['password']}@";
        }

        if (isset($config['port']) and !empty($config['port'])) {
            $config['dsn'] .= "{$config['host']}:{$config['port']}";
        } else {
            $config['dsn'] .= "{$config['host']}";
        }

        if (!empty($config['schema']))
            $config['dsn'] .= "/{$config['schema']}";

        $config['dsn'] = trim($config['dsn']);

        $this->config = $config;

        // Autoconnect?
        if ($config["autoconnect"]) {
            $this->connect();
        }

        // Set instance to the static scope
        $instName = $config["name"];
        static::$instances[$instName] = $this;
        if (static::$activeInstance == false) {
            static::$activeInstance = $config["name"];
        }
    }

    //####################### INTERFACE METHODS

    public function connect()
    {
        if (!$this->isConnected()) {
            \Komet\K::app()->trigger("dbal_before_mongo_connect", $this);
            $this->mongo = new \Mongo($this->config['dsn'], $this->config['options']);
            if (!empty($this->config['schema'])) {
                $this->selectDB($this->config["schema"]);
            }
            \Komet\K::app()->trigger("dbal_mongo_connect", $this);
            return $this->isConnected();
        }
        return false;
    }

    public function isConnected()
    {
        return is_object($this->mongo) && ($this->mongo->connected == true);
    }

    public function close()
    {
        if ($this->isConnected())
            return $this->mongo->close();
        else
            return false;
    }

    public function isError()
    {
        if (!$this->isConnected())
            return false;

        $err = $this->db->lastError();

        return is_array($err) && isset($err["err"]) && ($err["err"] != null);
    }

    public function getErrorCode()
    {
        if (!$this->isConnected())
            return false;

        $err = $this->db->lastError();
        if ($err["err"] == null)
            return 0;
        $err = explode(" ", $err["err"], 2);
        return \Komet\Arr::first($err[0]);
    }

    public function getErrorMessage()
    {
        if (!$this->isConnected())
            return false;

        $err = $this->db->lastError();
        return $err["err"];
    }

    public function getRowCount()
    {
        if (!$this->isConnected())
            return false;

        $err = $this->db->lastError();
        return $err["n"];
    }

    public function opBegin($triggerName = null, array &$triggerData = null)
    {
        parent::opBegin("mongo_" . $triggerName, $triggerData);
    }

    public function opEnd($result = null, $triggerName = null, array &$triggerData = null)
    {

        $query = array(
            "selects" => $this->selects,
            "wheres" => $this->wheres,
            "sorts" => $this->sorts,
            "limit" => $this->limit,
            "offset" => $this->offset
        );
        if (isset($triggerData["collection"])) {
            $query["collection"] = $triggerData["collection"];
        }

        $triggerData["query"] = $query;

        parent::opEnd($result, "mongo_" . $triggerName, $triggerData);
    }

    ####################### END INTERFACE METHODS
    ####################### DRIVER METHODS:

    /**
     * 
     * @return \Mongo
     */
    public function getMongo()
    {
        $this->connect();
        return $this->mongo;
    }

    /**
     * 
     * @return \MongoDB
     */
    public function getDB()
    {
        $this->connect();
        return $this->db;
    }

    /**
     * 
     * @return \MongoDB
     */
    public function selectDB($name)
    {
        $this->connect();
        $this->db = $this->mongo->selectDB($name);
        return $this->db;
    }

    /**
     * 
     * @param string $dbname
     * @return \MongoDB
     */
//    public function __get($dbname)
//    {
//        return $this->selectDB($dbname);
//    }

    /**
     * 	Drop a Mongo database
     *
     * 	@param	string	$dbname		the database name
     * 	@usage	$mongodb->dropDb("foobar");
     *  @return array The database response
     */
    public function dropDb($dbname)
    {
        $this->connect();
        return $this->mongo->{$dbname}->drop();
    }

    /**
     * 	Drop a Mongo collection
     *
     * 	@param	string	$dbname		the database name
     * 	@param	string	$colname		the collection name
     * 	@usage	$mongodb->dropCollection('foo', 'bar');
     *  @return array The database response
     */
    public function dropCollection($dbname, $colname)
    {
        $this->connect();
        return $this->mongo->{$dbname}->{$colname}->drop();
    }

    /**
     * 	Determine which fields to include OR which to exclude during the query process.
     * 	Currently, including and excluding at the same time is not available, so the
     * 	$includes array will take precedence over the $excludes array.  If you want to
     * 	only choose fields to exclude, leave $includes an empty array().
     *
     * 	@param	array	$includes	which fields to include
     * 	@param	array	$excludes	which fields to exclude
     * 	@usage	$mongodb->select(array('foo', 'bar'))->get('foobar');
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function select($includes = array(), $excludes = array())
    {
        if (!is_array($includes)) {
            $includes = array($includes);
        }

        if (!is_array($excludes)) {
            $excludes = array($excludes);
        }

        if (!empty($includes)) {
            foreach ($includes as $col) {
                $this->selects[$col] = 1;
            }
        } else {
            foreach ($excludes as $col) {
                $this->selects[$col] = 0;
            }
        }
        return $this;
    }

    /**
     * 	Get the documents based on these search parameters.  The $wheres array should
     * 	be an associative array with the field as the key and the value as the search
     * 	criteria.
     *
     * 	@param	array	$wheres		an associative array with conditions, array(field => value)
     * 	@usage	$mongodb->where(array('foo' => 'bar'))->get('foobar');
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function where($wheres = array())
    {
        foreach ($wheres as $wh => $val) {
            $this->wheres[$wh] = $val;
        }
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field may be something else
     *
     * 	@param	array	$wheres		an associative array with conditions, array(field => value)
     * 	@usage	$mongodb->orWhere(array( array('foo'=>'bar', 'bar'=>'foo' ))->get('foobar');
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function orWhere($wheres = array())
    {
        if (count($wheres) > 0) {
            if (!isset($this->wheres['$or']) or !is_array($this->wheres['$or'])) {
                $this->wheres['$or'] = array();
            }

            foreach ($wheres as $wh => $val) {
                $this->wheres['$or'][] = array($wh => $val);
            }
        }
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is in a given $in array().
     *
     * 	@param	string	$field		the field name
     * 	@param	array	$in			an array of values to compare to
     * 	@usage	$mongodb->whereIn('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereIn($field = '', $in = array())
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$in'] = $in;
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is in all of a given $in array().
     *
     * 	@param	string	$field		the field name
     * 	@param	array	$in			an array of values to compare to
     * 	@usage	$mongodb->whereInAll('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereInAll($field = '', $in = array())
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$all'] = $in;
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is not in a given $in array().
     *
     * 	@param	string	$field		the field name
     * 	@param	array	$in			an array of values to compare to
     * 	@usage	$mongodb->whereNotIn('foo', array('bar', 'zoo', 'blah'))->get('foobar');
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereNotIn($field = '', $in = array())
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$nin'] = $in;
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is greater than $x
     *
     * 	@param	string	$field		the field name
     * 	@param	mixed	$x			the value to compare to
     * 	@usage	$mongodb->whereGt('foo', 20);
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereGt($field, $x)
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$gt'] = $x;
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is greater than or equal to $x
     *
     * 	@param	string	$field		the field name
     * 	@param	mixed	$x			the value to compare to
     * 	@usage	$mongodb->whereGte('foo', 20);
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereGte($field, $x)
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$gte'] = $x;
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is less than $x
     *
     * 	@param	string	$field		the field name
     * 	@param	mixed	$x			the value to compare to
     * 	@usage	$mongodb->whereLt('foo', 20);
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereLt($field, $x)
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$lt'] = $x;
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is less than or equal to $x
     *
     * 	@param	string	$field		the field name
     * 	@param	mixed	$x			the value to compare to
     * 	@usage	$mongodb->whereLte('foo', 20);
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereLte($field, $x)
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$lte'] = $x;
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is between $x and $y
     *
     * 	@param	string	$field		the field name
     * 	@param	mixed	$x			the value to compare to
     * 	@param	mixed	$y			the high value to compare to
     * 	@usage	$mongodb->whereBetween('foo', 20, 30);
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereBetween($field, $x, $y)
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$gte'] = $x;
        $this->wheres[$field]['$lte'] = $y;
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is between but not equal to $x and $y
     *
     * 	@param	string	$field		the field name
     * 	@param	mixed	$x			the low value to compare to
     * 	@param	mixed	$y			the high value to compare to
     * 	@usage	$mongodb->whereBetweenNe('foo', 20, 30);
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereBetweenNe($field, $x, $y)
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$gt'] = $x;
        $this->wheres[$field]['$lt'] = $y;
        return $this;
    }

    /**
     * 	Get the documents where the value of a $field is not equal to $x
     *
     * 	@param	string	$field		the field name
     * 	@param	mixed	$x			the value to compare to
     * 	@usage	$mongodb->whereNe('foo', 1)->get('foobar');
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function whereNe($field, $x)
    {
        $this->_whereInit($field);
        $this->wheres[$field]['$ne'] = $x;
        return $this;
    }

    /**
     * 	Get the documents nearest to an array of coordinates (your collection must have a geospatial index)
     *
     * 	@param	string	$field		the field name
     * 	@param	array	$co			array of 2 coordinates
     * 	@usage	$mongodb->whereNear('foo', array('50','50'))->get('foobar');
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function wherNear($field = '', $co = array())
    {
        $this->_whereInit($field);
        $this->where[$field]['$near'] = $co;
        return $this;
    }

    /**
     * 	--------------------------------------------------------------------------------
     * 	LIKE PARAMETERS
     * 	--------------------------------------------------------------------------------
     *
     * 	Get the documents where the (string) value of a $field is like a value. The defaults
     * 	allow for a case-insensitive search.
     *
     * 	@param $flags
     * 	Allows for the typical regular expression flags:
     * 		i = case insensitive
     * 		m = multiline
     * 		x = can contain comments
     * 		l = locale
     * 		s = dotall, "." matches everything, including newlines
     * 		u = match unicode
     *
     * 	@param $enable_start_wildcard
     * 	If set to anything other than TRUE, a starting line character "^" will be prepended
     * 	to the search value, representing only searching for a value at the start of
     * 	a new line.
     *
     * 	@param $enable_end_wildcard
     * 	If set to anything other than TRUE, an ending line character "$" will be appended
     * 	to the search value, representing only searching for a value at the end of
     * 	a line.
     *
     * 	@usage	$mongodb->like('foo', 'bar', 'im', false, TRUE);
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function like($field = '', $value = '', $flags = 'i', $enable_start_wildcard = TRUE, $enable_end_wildcard = TRUE)
    {
        $field = (string) trim($field);
        $this->_whereInit($field);
        $value = quotemeta((string) trim($value));

        if ($enable_start_wildcard !== TRUE) {
            $value = '^' . $value;
        }

        if ($enable_end_wildcard !== TRUE) {
            $value .= '$';
        }

        $regex = "/$value/$flags";
        $this->wheres[$field] = new \MongoRegex($regex);

        return $this;
    }

    /**
     * 	Sort the documents based on the parameters passed. To set values to descending order,
     * 	you must pass values of either -1, false, 'desc', or 'DESC', else they will be
     * 	set to 1 (ASC).
     *
     * 	@param	array	$fields		an associative array, array(field => direction)
     * 	@usage	$mongodb->orderBy('foo' => "desc", "bar" => "asc");
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function orderBy($fields = array())
    {
        foreach ($fields as $col => $val) {
            if ($val == -1 or $val === false or strtolower($val) == 'desc') {
                $this->sorts[$col] = -1;
            } else {
                $this->sorts[$col] = 1;
            }
        }
        return $this;
    }

    /**
     * 	Limit the result set to $x number of documents
     *
     * 	@param	number	$x			the max amount of documents to fetch
     * 	@usage	$mongodb->limit($x);
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function limit($x = 99999)
    {
        if ($x !== null and is_numeric($x) and $x >= 1) {
            $this->limit = (int) $x;
        }
        return $this;
    }

    /**
     * 	--------------------------------------------------------------------------------
     * 	OFFSET DOCUMENTS
     * 	--------------------------------------------------------------------------------
     *
     * 	Offset the result set to skip $x number of documents
     *
     * 	@param	number	$x			the number of documents to skip
     * 	@usage	$mongodb->offset($x);
     *  @return \Komet\DBAL\MongoDBAL
     */
    public function offset($x = 0)
    {
        if ($x !== null and is_numeric($x) and $x >= 1) {
            $this->offset = (int) $x;
        }
        return $this;
    }

    /**
     * 	Get the documents based upon the passed parameters
     *
     * 	@param	string	$collection		the collection name
     * 	@param	array	$where			an array of conditions, array(field => value)
     * 	@param	number	$limit			the max amount of documents to fetch
     * 	@usage	$mongodb->getWhere('foo', array('bar' => 'something'));
     *  @return \MongoCursor documents iterator
     */
    public function getWhere($collection, $where = array(), $limit = 99999)
    {
        return ($this->where($where)->limit($limit)->get($collection));
    }

    /**
     * 	Get the documents based upon the passed parameters
     *
     * 	@param	string	$collection		the collection name
     * 	@usage	$mongodb->get('foo', array('bar' => 'something'));
     *  @return \MongoCursor documents iterator
     */
    public function get($collection)
    {
        if (empty($collection)) {
            $this->log(\Komet\Logger\Logger::CRITICAL, "MongoDBAL::get : 'collection' parameter cannot be empty");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection);
        $this->opBegin("get", $triggerData);

        $mongoCursor = $this->db->{$collection}->find($this->wheres, $this->selects)->limit((int) $this->limit)->skip((int) $this->offset)->sort($this->sorts);

        $this->_clear();

        $this->opEnd($mongoCursor, "get", $triggerData);

        return $mongoCursor;
    }

    /**
     * Get one document based upon the passed parameters
     *
     * 	@param	string	$collection		the collection name
     * 	@usage	$mongodb->getOne('foo');
     *  @return array a single document
     */
    public function getOne($collection)
    {
        if (empty($collection)) {
            $this->log(500, "MongoDBAL::getOne : 'collection' parameter cannot be empty");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection);
        $this->opBegin("getone", $triggerData);

        $result = $this->db->{$collection}->findOne($this->wheres, $this->selects);

        $this->_clear();

        $this->opEnd($result, "getone", $triggerData);

        return $result;
    }

    /**
     * 	Count the documents based upon the passed parameters
     *
     * 	@param	string	$collection		the collection name
     * 	@param	boolean	$foundonly		send cursor limit and skip information to the count function, if applicable.
     * 	@usage	$mongodb->count('foo');
     * @return int
     */
    public function count($collection, $foundonly = false)
    {
        if (empty($collection)) {
            $this->log(500, "MongoDBAL::count : 'collection' parameter cannot be empty");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection, "foundonly" => &$foundonly);
        $this->opBegin("count", $triggerData);

        $this->connect();

        $count = $this->db->{$collection}->find($this->wheres)->limit((int) $this->limit)->skip((int) $this->offset)->count($foundonly);
        $this->_clear();

        $this->opEnd($count, "count", $triggerData);

        return ($count);
    }

    /**
     * 	--------------------------------------------------------------------------------
     * 	INSERT
     * 	--------------------------------------------------------------------------------
     *
     * 	Insert a new document into the passed collection
     *
     * 	@param	string	$collection		the collection name
     * 	@param	array	$insert			an array of values to insert, array(field => value)
     * 	@usage	$mongodb->insert('foo', $data = array());
     */
    public function insert($collection, $insert = array())
    {
        if (empty($collection)) {
            $this->log(500, "MongoDBAL::insert : 'collection' parameter cannot be empty");
        }

        if (empty($insert) or !is_array($insert)) {
            $this->log(400, "MongoDBAL::insert : Nothing to insert into Mongo collection or insert is not an array");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection, "insert" => &$insert);
        $this->opBegin("insert", $triggerData);

        try {
            $this->db->selectCollection($collection)->insert($insert, array('fsync' => true));
            if (isset($insert['_id'])) {
                $result = $insert['_id'];
            } else {
                $result = false;
            }

            $this->opEnd($result, "insert", $triggerData);
            return $result;
        } catch (\Exception $e) {
            throw new \Exception("Insert of data into MongoDB failed: {$e->getMessage()}");
        }
    }

    /**
     * 	Updates a single document
     *
     * 	@param	string	$collection		the collection name
     * 	@param	array	$data			an associative array of values, array(field => value)
     * 	@param	array	$options		an associative array of options
     * 	@usage	$mongodb->update('foo', $data = array());
     *  @return boolean True if the operation was successful
     */
    public function update($collection, $data = array(), $options = array(), $literal = false)
    {
        if (empty($collection)) {
            $this->log(500, "MongoDBAL::update : 'collection' parameter cannot be empty");
        }

        if (empty($data) or !is_array($data)) {
            $this->log(400, "MongoDBAL::update : Nothing to update in Mongo collection or update is not an array");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection, "data" => &$data, "options" => &$options, "literal" => &$literal);
        $this->opBegin("update", $triggerData);

        try {
            $options = array_merge($options, array('fsync' => true, 'multiple' => false));
            $updated = $this->db->{$collection}->update($this->wheres, (($literal) ? $data : array('$set' => $data)), $options);
            $this->_clear();
            $this->opEnd($updated, "update", $triggerData);
            return $updated;
        } catch (\Exception $e) {
            $this->log(500, "Update of data into MongoDB failed: {$e->getMessage()}");
        }
    }

    /**
     * 	Updates a collection of documents
     *
     * 	@param	string	$collection		the collection name
     * 	@param	array	$data			an associative array of values, array(field => value)
     * 	@usage	$mongodb->updateAll('foo', $data = array());
     *  @return boolean True if the operation was successful
     */
    public function updateAll($collection, $data = array(), $literal = false)
    {
        if (empty($collection)) {
            $this->log(500, "MongoDBAL::updateAll : 'collection' parameter cannot be empty");
        }

        if (empty($data) or !is_array($data)) {
            $this->log(500, "MongoDBAL::updateAll : Nothing to update in Mongo collection or update is not an array");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection, "data" => &$data, "literal" => &$literal);
        $this->opBegin("updateall", $triggerData);

        try {
            $updated = $this->db->{$collection}->update($this->wheres, (($literal) ? $data : array('$set' => $data)), array('fsync' => true, 'multiple' => true));
            $this->_clear();
            $this->opEnd($updated, "updateall", $triggerData);
            return $updated;
        } catch (\Exception $e) {
            $this->log(500, "Update of data into MongoDB failed: {$e->getMessage()}");
        }
    }

    /**
     * 	Delete a document from the passed collection based upon certain criteria
     *
     * 	@param	string	$collection		the collection name
     * 	@usage	$mongodb->delete('foo');
     *  @return boolean True if the operation was successful
     */
    public function delete($collection)
    {
        if (empty($collection)) {
            $this->log(500, "MongoDBAL::delete : 'collection' parameter cannot be empty");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection);
        $this->opBegin("delete", $triggerData);

        try {
            $deleted = $this->db->{$collection}->remove($this->wheres, array('fsync' => true, 'justOne' => true));
            $this->_clear();
            $this->opEnd($deleted, "delete", $triggerData);
            return $deleted;
        } catch (\Exception $e) {
            $this->log(500, "Delete of data into MongoDB failed: {$e->getMessage()}");
        }
    }

    /**
     * 	Delete all documents from the passed collection based upon certain criteria.
     *
     * 	@param	string	$collection		the collection name
     * 	@usage	$mongodb->deleteAll('foo');
     *  @return boolean True if the operation was successful
     */
    public function deleteAll($collection)
    {
        if (empty($collection)) {
            $this->log(500, "MongoDBAL::deleteAll : 'collection' parameter cannot be empty");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection);
        $this->opBegin("deleteall", $triggerData);

        try {
            $deleted = $this->db->{$collection}->remove($this->wheres, array('fsync' => true, 'justOne' => false));
            $this->_clear();
            $this->opEnd($deleted, "deleteall", $triggerData);
            return $deleted;
        } catch (\Exception $e) {
            $this->log(500, "Delete of data into MongoDB failed: {$e->getMessage()}");
        }
    }

    /**
     * 	Runs a MongoDB command (such as GeoNear). See the MongoDB documentation for more usage scenarios:
     * 	http://dochub.mongodb.org/core/commands
     *
     * 	@param	array	$query	a query array
     * 	@usage	$mongodb->command(array('geoNear'=>'buildings', 'near'=>array(53.228482, -0.547847), 'num' => 10, 'nearSphere'=>TRUE));
     */
    public function command($query = array())
    {
        $triggerData = array("instance" => &$this, "query" => &$query);
        $this->opBegin("command", $triggerData);
        try {
            $run = $this->db->command($query);
            $this->opEnd($run, "command", $triggerData);
            return $run;
        } catch (\Exception $e) {
            $this->log(500, "MongoDB command failed to execute: {$e->getMessage()}");
        }
    }

    /**
     * 	Ensure an index of the keys in a collection with optional parameters. To set values to descending order,
     * 	you must pass values of either -1, false, 'desc', or 'DESC', else they will be
     * 	set to 1 (ASC).
     *
     * 	@param	string	$collection		the collection name
     * 	@param	array	$keys			an associative array of keys, array(field => direction)
     * 	@param	array	$options		an associative array of options
     * 	@usage	$mongodb->addIndex($collection, array('first_name' => 'ASC', 'last_name' => -1), array('unique' => TRUE));
     */
    public function addIndex($collection, $keys = array(), $options = array())
    {
        if (empty($collection)) {
            $this->log(500, "No Mongo collection specified to add index to");
        }

        if (empty($keys) or !is_array($keys)) {
            $this->log(400, "Index could not be created to MongoDB Collection because no keys were specified");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection, "keys" => &$keys, "options" => &$options);
        $this->opBegin("addindex", $triggerData);

        foreach ($keys as $col => $val) {
            if ($val == -1 or $val === false or strtolower($val) == 'desc') {
                $keys[$col] = -1;
            } else {
                $keys[$col] = 1;
            }
        }

        if ($this->db->{$collection}->ensureIndex($keys, $options) == true) {
            $this->_clear();
            $this->opEnd(true, "addindex", $triggerData);
            return $this;
        } else {
            $this->log(500, "An error occured when trying to add an index to MongoDB Collection");
        }
    }

    /**
     * 	Remove an index of the keys in a collection. To set values to descending order,
     * 	you must pass values of either -1, false, 'desc', or 'DESC', else they will be
     * 	set to 1 (ASC).
     *
     * 	@param	string	$collection		the collection name
     * 	@param	array	$keys			an associative array of keys, array(field => direction)
     * 	@usage	$mongodb->removeIndex($collection, array('first_name' => 'ASC', 'last_name' => -1));
     */
    public function removeIndex($collection, $keys = array())
    {
        if (empty($collection)) {
            $this->log(500, "No Mongo collection specified to remove index from");
        }

        if (empty($keys) or !is_array($keys)) {
            $this->log(400, "Index could not be removed from MongoDB Collection because no keys were specified");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection, "keys" => &$keys);
        $this->opBegin("removeindex", $triggerData);

        if ($this->db->{$collection}->deleteIndex($keys) == true) {
            $this->_clear();
            $this->opEnd(true, "removeindex", $triggerData);
            return $this;
        } else {
            $this->log(500, "An error occured when trying to remove an index from MongoDB Collection");
        }
    }

    /**
     * 	Remove all indexes from a collection.
     *
     * 	@param	string	$collection		the collection name
     * 	@usage	$mongodb->remove_all_index($collection);
     */
    public function removeAllIndexes($collection)
    {
        if (empty($collection)) {
            $this->log(500, "No Mongo collection specified to remove all indexes from");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection);
        $this->opBegin("removeallindexes", $triggerData);

        $this->db->{$collection}->deleteIndexes();
        $this->_clear();
        $this->opEnd(true, "removeallindexes", $triggerData);
        return $this;
    }

    /**
     * 	Lists all indexes in a collection.
     *
     * 	@param	string	$collection		the collection name
     * 	@usage	$mongodb->listIndexes($collection);
     */
    public function listIndexes($collection)
    {
        if (empty($collection)) {
            $this->log(500, "No Mongo collection specified to list all indexes from");
        }

        $triggerData = array("instance" => &$this, "collection" => &$collection);
        $this->opBegin("listindexes", $triggerData);

        $result = ($this->db->{$collection}->getIndexInfo());
        $this->opEnd($result, "listindexes", $triggerData);

        return $result;
    }

    /**
     * 	Resets the class variables to default settings
     */
    protected function _clear()
    {
        $this->selects = array();
        $this->wheres = array();
        $this->limit = 999999;
        $this->offset = 0;
        $this->sorts = array();
    }

    /**
     * 	Prepares parameters for insertion in $wheres array().
     *
     * 	@param	string	$param		the field name
     */
    protected function _whereInit($param)
    {
        if (!isset($this->wheres[$param])) {
            $this->wheres[$param] = array();
        }
    }

    ####################### END DRIVER METHODS
}
