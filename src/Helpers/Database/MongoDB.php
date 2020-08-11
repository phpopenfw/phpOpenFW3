<?php
//**************************************************************************************
//**************************************************************************************
/**
 * MongoDB Functions Plugin
 *
 * @package		phpOpenFW2
 * @author 		Christian J. Clark
 * @copyright	Copyright (c) Christian J. Clark
 * @license		https://mit-license.org
 **/
//**************************************************************************************
//**************************************************************************************

namespace phpOpenFW\Helpers\Database;

//**************************************************************************************
/**
 * MongoDB Database Helpers Class
 */
//**************************************************************************************
class MongoDB extends \phpOpenFW\Cores\StaticCore
{

	//*****************************************************************************
	//****************************************************************************
	// Stream MongoDB Image
	//*****************************************************************************
	//*****************************************************************************
	public static function stream_gridfs_file($gridfs, $id, $args=false)
	{
		if (is_array($args)) { extract($args); }

		//*****************************************************************
		// Try to Get File Record from MongoDB
		//*****************************************************************
		$mongo_file = false;
		try {
			$mongo_file = $gridfs->get(new MongoId($id));
		}
		catch (Exception $e) {
			if (!empty($show_errors)) {
				self::display_error(__METHOD__, 'First parameter must be a valid Memcached object.');
			}
			return false;
		}
		
		//*****************************************************************
		// Valid MongoDB File?
		//*****************************************************************
		if (!$mongo_file) { return false; }
		
		//*****************************************************************
		// Output Content Type / Content
		//*****************************************************************
		$stream = true;
		if (!empty($output_header)) {
			$stream = \phpOpenFW\Content\CDN::output_content_type($mongo_file->getFilename());
		}
		if ($stream) {
			print $mongo_file->getBytes();
		}
		else {
			return false;
		}

		return true;
	}

}

