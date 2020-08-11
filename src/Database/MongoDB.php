<?php
//**************************************************************************************
//**************************************************************************************
/**
 * MongoDB Class
 *
 * @package		phpOpenFW
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 */
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Database;

//**************************************************************************************
/**
 * MongoDB Class
 */
//**************************************************************************************
class MongoDB {

	//*****************************************************************************
	// Class Members
	//*****************************************************************************
	protected $data_src;
	protected $mongo_client;
	protected $mongo_client_db;
	protected $mongo_client_db_gridfs;

	//*****************************************************************************
	//*****************************************************************************
	// Constructor function
	//*****************************************************************************
	//*****************************************************************************
	public function __construct($data_src='')
	{
        //=================================================================
		// Data Source
        //=================================================================
		if ($data_src != '') {
			if (!isset($_SESSION[$data_src])) {
				throw new \Exception("Invalid Data Source '{$data_src}'.");
			}
		}
		else {
			if (isset($_SESSION['default_data_source'])) {
				$data_src = $_SESSION['default_data_source'];
			}
			else {
				throw new \Exception('Data Source not given and default data source is not set.');
			}
		}
		$this->data_src = $data_src;

        //=================================================================
		// Connect
        //=================================================================
		if (!isset($GLOBALS[$this->data_src])) {
			$GLOBALS[$this->data_src] = [];
			$this->mongo_client = self::Connect($this->data_src);
			$GLOBALS[$this->data_src]['mongo_client'] =& $this->mongo_client;
			$this->mongo_client_db = $this->mongo_client->{$_SESSION[$this->data_src]['source']};
			$GLOBALS[$this->data_src]['mongo_client_db'] =& $this->mongo_client_db;
			$this->mongo_client_db_gridfs = $this->mongo_client_db->selectGridFSBucket();
			$GLOBALS[$this->data_src]['mongo_client_db_gridfs'] =& $this->mongo_client_db_gridfs;
		}
		else {
			$this->mongo_client =& $GLOBALS[$this->data_src]['mongo_client'];
			$this->mongo_client_db =& $GLOBALS[$this->data_src]['mongo_client_db'];
			$this->mongo_client_db_gridfs =& $GLOBALS[$this->data_src]['mongo_client_db_gridfs'];
		}
	}

	//*****************************************************************************
	//*****************************************************************************
    // Get Object Instance
	//*****************************************************************************
	//*****************************************************************************
    public static function Instance($data_src='')
    {
	    return new static($data_src);
    }

	//*****************************************************************************
	//*****************************************************************************
	// Get MongoDB Client Connection
	//*****************************************************************************
	//*****************************************************************************
	public static function Connect($data_source)
	{
		$conn_str = self::ConnectionString($data_source);
		if (!$data_source || !isset($_SESSION[$data_source])) {
			throw new \Exception('Invalid data source.');
			return false;
		}
		$ds = $_SESSION[$data_source];
		$opts = (isset($ds['options']) && is_array($ds['options'])) ? ($ds['options']) : ([]);
		return new \MongoDB\Client($conn_str, $opts);
	}

	//*****************************************************************************
	//*****************************************************************************
	// Get MongoDB Connection String
	//*****************************************************************************
	//*****************************************************************************
	public static function ConnectionString($data_source)
	{
		//=================================================================
		// Validate Data Source
		//=================================================================
		if (!$data_source || !isset($_SESSION[$data_source])) {
			throw new \Exception('Invalid data source.');
			return false;
		}
		$ds = $_SESSION[$data_source];

		//=================================================================
		// Build Connection String
		//=================================================================
		return "mongodb://{$ds['user']}:{$ds['pass']}@{$ds['server']}:{$ds['port']}/{$ds['source']}";
	}

	//*****************************************************************************
	//*****************************************************************************
	// Create Object ID
	//*****************************************************************************
	//*****************************************************************************
	public static function CreateObjectID($id)
	{
		try {
			return new \MongoDB\BSON\ObjectId($id);
		}
		catch (\MongoDB\Driver\Exception\InvalidArgumentException $e) {
			trigger_error($e);
			return false;
		}
	}

	//*****************************************************************************
	//*****************************************************************************
	// Build Find Options
	//*****************************************************************************
	//*****************************************************************************
	public static function BuildFindOptions(Array $args=[], Array $opts=[])
	{
		$possibles = [
			'projection', 'sort', 'skip', 'limit', 'batchSize', 'collation', 'comment',
			'cursorType', 'maxTimeMS', 'readConcern', 'readPreference', 'noCursorTimeout', 
			'allowPartialResults', 'typeMap', 'modifiers'
		];

		//--------------------------------------------------
		// Check For Find Option Parameters
		//--------------------------------------------------
		foreach ($possibles as $p) {
			if (!empty($args[$p])) { $opts[$p] = $args[$p]; }
			else if (array_key_exists($p, $opts) && $opts[$p] == '') {
				unset($opts[$p]);
			}
		}

		//--------------------------------------------------
		// Default Type Map
		//--------------------------------------------------
		if (!isset($opts['typeMap'])) {
			$opts['typeMap'] = ['root' => 'array', 'document' => 'array'];
		}

		return $opts;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Get MongoDB Client
	//*****************************************************************************
	//*****************************************************************************
	public function GetClient()
	{
		return $this->mongo_client;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Get MongoDB Database Object
	//*****************************************************************************
	//*****************************************************************************
	public function GetDatabase()
	{
		return $this->mongo_client_db;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Get MongoDB Collection Object
	//*****************************************************************************
	//*****************************************************************************
	public function GetCollection($collection)
	{
		return $this->mongo_client_db->$collection;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Get MongoDB Database GridFS Object
	//*****************************************************************************
	//*****************************************************************************
	public function GetGridFS()
	{
		return $this->$this->mongo_client_db_gridfs;
	}

	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	// Document Methods
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	//*****************************************************************************
	//*****************************************************************************
	// Get One Document By ID
	//*****************************************************************************
	//*****************************************************************************
	public function GetDocumentByID($collection, $id, Array $args=[])
	{
		if (!$collection) { return false; }
		$find_opts = self::BuildFindOptions($args);

		$doc_oid = self::CreateObjectID($id);
		if (!$doc_oid) { return false; }

		return $this->mongo_client_db->$collection->findOne(['_id' => $doc_oid], $find_opts);
	}

	//*****************************************************************************
	//*****************************************************************************
	// Insert One Document
	//*****************************************************************************
	//*****************************************************************************
	public function InsertDocument($collection, $data, array $args=[])
	{
		if (!$collection) { return false; }
		extract($args);

		$result = $this->mongo_client_db->$collection->insertOne($data);
		return (empty($return_raw_result)) ? ($result->getInsertedId()) : ($result);
	}

	//*****************************************************************************
	//*****************************************************************************
	// Update One Document By ID
	//*****************************************************************************
	//*****************************************************************************
	public function UpdateDocumentByID($collection, $id, $data, array $args=[])
	{
		if (!$collection) { return false; }
		extract($args);

		$doc_oid = self::CreateObjectID($id);
		if (!$doc_oid) { return false; }

		$result = $this->mongo_client_db->$collection->updateOne(
			['_id' => $doc_oid],
			['$set' => $data]
		);

		return (empty($return_raw_result)) ? ($result->getMatchedCount()) : ($result);
	}

	//*****************************************************************************
	//*****************************************************************************
	// Upsert One Document By ID
	//*****************************************************************************
	//*****************************************************************************
	public function UpsertDocumentByID($collection, $id, $data, array $args=[])
	{
		if (!$collection) { return false; }
		extract($args);

		$doc_oid = self::CreateObjectID($id);
		if (!$doc_oid) { return false; }

		$result = $this->mongo_client_db->$collection->updateOne(
			['_id' => $doc_oid],
			['$set' => $data],
			['upsert' => true]
		);

		if (!empty($return_raw_result)) {
			return $result;
		}
		$upsert_id = $result->getUpsertedId();
		return ($upsert_id) ? ($upsert_id) : ($id);
	}

	//*****************************************************************************
	//*****************************************************************************
	// Delete One Document By ID
	//*****************************************************************************
	//*****************************************************************************
	public function DeleteDocumentByID($collection, $id)
	{
		if (!$collection) { return false; }
		$doc_oid = self::CreateObjectID($id);
		if (!$doc_oid) { return false; }

		$result = $this->mongo_client_db->$collection->deleteOne(
			['_id' => $doc_oid]
		);

		return $result->getDeletedCount();
	}

	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	// Grid FS Methods
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
	//%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

	//*****************************************************************************
	//*****************************************************************************
	// Get GridFS File By ID
	//*****************************************************************************
	//*****************************************************************************
	public function GetGridFSFileByID($id, Array $args=[])
	{
		$find_opts = self::BuildFindOptions($args);

		$oid = self::CreateObjectID($id);
		if (!$oid) { return false; }

		return $this->mongo_client_db_gridfs->findOne(['_id' => $oid], $find_opts);
	}

	//*****************************************************************************
	//*****************************************************************************
	// Stream GridFS File By ID
	//*****************************************************************************
	//*****************************************************************************
	public function StreamGridFSFileByID($id, Array $args=[])
	{
		//=================================================================
		// Default Args / Extract Args
		//=================================================================
		$output_header = false;
		$content_type = false;
		extract($args);

		//=================================================================
		// Try to Get File Record from MongoDB
		//=================================================================
		$file_data = $this->GetGridFSFileByID($id);
		if (!$file_data) { return false; }
		$file_data['stream'] = $this->mongo_client_db_gridfs->openDownloadStream($file_data['_id']);
		if (!$file_data['stream']) { return false; }

		//=================================================================
		// Stream
		//=================================================================
		\phpOpenFW\Content\CDN::OutputStream($file_data['stream'], [
			'file_name' => $file_data['filename'],
			'output_header' => $output_header,
			'content_type' => $content_type
		]);

		return true;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Get GridFS File Stream By ID
	//*****************************************************************************
	//*****************************************************************************
	public function GetGridFSFileStreamByID($id)
	{
		//=================================================================
		// Try to Get File Record from MongoDB
		//=================================================================
		$file_data = $this->GetGridFSFileByID($id);
		if (!$file_data) { return false; }
		
		//=================================================================
		// Return Stream
		//=================================================================
		return $this->mongo_client_db_gridfs->openDownloadStream($file_data['_id']);
	}

	//*****************************************************************************
	//*****************************************************************************
	// Add GridFS File
	//*****************************************************************************
	//*****************************************************************************
	public function AddGridFSFile($file, Array $args=[])
	{
		if (!$file) { return false; }

		//=================================================================
		// Defaults / Extract Args
		//=================================================================
		$options = [];
		extract($args);

		//=================================================================
		// No Options?
		//=================================================================
		if (!$options) {
			if (!empty($metadata)) {
				$options['metadata'] = $metadata;
			}
		}

		//=================================================================
		// Resource Passed
		//=================================================================
		if (gettype($file) == 'resource') {
			fseek($file, 0);
			if (empty($file_name)) {
				$file_name = uniqid();
			}
			return $this->mongo_client_db_gridfs->uploadFromStream($file_name, $file, $options);			
		}
		//=================================================================
		// Scalar Passed: Does the file exist?
		//=================================================================
		else if (file_exists($file)) {
			$fhandle = fopen($file, 'rb');
			if (empty($file_name)) {
				$file_name = basename($file);
			}
			return $this->mongo_client_db_gridfs->uploadFromStream($file_name, $fhandle, $options);
		}
		
		return false;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Add GridFS File From Data
	//*****************************************************************************
	//*****************************************************************************
	public function AddGridFSFileFromData($data, Array $args=[])
	{
		$temp = tmpfile();
		fwrite($temp, $data);
		$id = $this->AddGridFSFile($temp, $args);
		fclose($temp);
		return $id;
	}

	//*****************************************************************************
	//*****************************************************************************
	// Delete GridFS File
	//*****************************************************************************
	//*****************************************************************************
	public function DeleteGridFSFileByID($id, Array $args=[])
	{
		$file_data = $this->GetGridFSFileByID($id);
		if ($file_data) {
			$fileId = self::CreateObjectID($id);
			if (!$fileId) { return false; }
			$this->mongo_client_db_gridfs->delete($fileId);
			return true;
		}

		return false;
	}
}
