<?php
//*****************************************************************************
//*****************************************************************************
/**
 * Stream Class
 *
 * @package         phpopenfw/phpopenfw3
 * @author          Christian J. Clark
 * @copyright       Copyright (c) Christian J. Clark
 * @website         https://phpopenfw.org
 * @license         https://mit-license.org
 **/
//*****************************************************************************
//*****************************************************************************

namespace phpOpenFW\Content;
use \phpOpenFW\Helpers\FilePath;

//*****************************************************************************
/**
 * Stream Class
 */
//*****************************************************************************
class Stream
{
    //=========================================================================
    // Traits
    //=========================================================================
    use Stream\Range;

    //=========================================================================
    // Class Members
    //=========================================================================
    protected $stream = null;
    protected $args = null;
    protected $dus_data = null;
    protected $embedded_headers = null;
    protected $headers = [];
    protected $output_headers = true;
    protected $ranges = null;
    protected $multiple_ranges = false;
    protected $detect_output_ranges = true;
    protected $base64_decode = true;
    protected $is_base64 = null;
    protected $file_name = null;
    protected $file_size = null;
    protected $content_type = null;

    //=========================================================================
    //=========================================================================
    // Constructor
    //=========================================================================
    //=========================================================================
    public function __construct($stream, Array $args=[])
    {
        //---------------------------------------------------------------------
        // Validate Resource is Stream
        //---------------------------------------------------------------------
        if (get_resource_type($stream) != 'stream') {
            throw new \Exception('Invalid stream given.');
        }
        $this->stream = $stream;
        $this->args = $args;

        //---------------------------------------------------------------------
        // Settings
        //---------------------------------------------------------------------
        if (isset($args['output_header'])) {
            $this->output_headers = $args['output_header'];
        }
        else if (isset($args['output_headers'])) {
            $this->output_headers = $args['output_headers'];
        }
        if (isset($args['detect_output_ranges'])) {
            $this->detect_output_ranges = $args['detect_output_ranges'];
        }
        if (isset($args['base64_decode'])) {
            $this->base64_decode = $args['base64_decode'];
        }
        if (isset($args['file_name'])) {
            $this->file_name = $args['file_name'];
        }
        if (isset($args['file_size'])) {
            $this->file_size = $args['file_size'];
        }
        if (isset($args['content_type'])) {
            $this->content_type = $args['content_type'];
        }

        //---------------------------------------------------------------------
        // Detect Ranges?
        //---------------------------------------------------------------------
        if ($this->detect_output_ranges) {
            $this->ranges = \phpOpenFW\HTTP\Request::GetRequestRanges($this->args);
            if ($this->ranges) {
                if (count($this->ranges) > 1) {
                    $this->multiple_ranges = true;
                }
            }
        }

        //---------------------------------------------------------------------
        // Get URI Scheme Data
        //---------------------------------------------------------------------
        $first_chunk = stream_get_contents($this->stream, 100);
        rewind($this->stream);
        $this->dus_data = \phpOpenFW\Data\UriScheme::ParseHeader($first_chunk);

        //---------------------------------------------------------------------
        // Update Settings based on URI Scheme Data
        //---------------------------------------------------------------------
        if ($this->dus_data) {
            $this->embedded_headers = $this->dus_data['embedded_headers'];
            if (!empty($this->dus_data['content_type']) && !$this->content_type) {
                $this->content_type = $this->dus_data['content_type'];
            }
            if (isset($this->dus_data['is_base64'])) {
                $this->is_base64 = $this->dus_data['is_base64'];
            }
        }
        else {
            $this->embedded_headers = false;
        }
    }

    //=========================================================================
    //=========================================================================
    // Instance
    //=========================================================================
    //=========================================================================
    public static function Instance($stream, Array $args=[])
    {
        return new static($stream, $args);
    }

    //=========================================================================
    //=========================================================================
    // Is Base64 Encoded?
    //=========================================================================
    //=========================================================================
    public function IsBase64()
    {
        return $this->is_base64;
    }

    //=========================================================================
    //=========================================================================
    // Get Ranges
    //=========================================================================
    //=========================================================================
    public function GetRanges()
    {
        return $this->ranges;
    }

    //=========================================================================
    //=========================================================================
    // Output Stream
    //=========================================================================
    //=========================================================================
    public function Output(Array $args=[])
    {
        //---------------------------------------------------------------------
        // Stream Full Contents
        //---------------------------------------------------------------------
        if (!$this->ranges) {
            return $this->StreamFullContents();
        }
        //---------------------------------------------------------------------
        // Stream Ranges
        //---------------------------------------------------------------------
        else {
            return $this->StreamRanges();
        }
    }

    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    // Protected / Internal Methods
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
    //%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%

    //=========================================================================
    //=========================================================================
    // Output Content Type Header
    //=========================================================================
    //=========================================================================
    protected function OutputContentTypeHeader()
    {
        //---------------------------------------------------------------------
        // Determine Mime Type
        //---------------------------------------------------------------------
        $mime_type = false;
        if ($this->content_type) {
            $mime_type = $this->content_type;
        }
        else if (!empty($this->args['mime_type'])) {
            $mime_type = $this->args['mime_type'];
        }
        else if (!empty($this->args['content_type'])) {
            $mime_type = $this->args['content_type'];
        }
        else if (!empty($this->args['file_name'])) {
            $mime_type = FilePath::GetMimeType($this->args['file_name']);
        }

        //---------------------------------------------------------------------
        // Did we get a mime type?
        //---------------------------------------------------------------------
        if ($mime_type) {
            header('Content-type: ' . $mime_type);
            if ($this->file_name) {
                $ext = FilePath::GetExtension($this->file_name);
                if ($ext == 'svgz') {
                    header('Content-Encoding: gzip');
                }
            }
            return true;
        }

        //---------------------------------------------------------------------
        // No content type found
        //---------------------------------------------------------------------
        return false;
    }

    //=========================================================================
    //=========================================================================
    // Output Content Disposition Header
    //=========================================================================
    //=========================================================================
    protected function OutputContentDispositionHeader()
    {
        if (!empty($this->args['content_disposition'])) {
            header('Content-Disposition: ' . $this->args['content_disposition']);
            return true;
        }
        else if (!empty($this->args['force_download'])) {
            $cont_disp = 'Content-Disposition: attachment;';
            if ($this->file_name) {
                $cont_disp .= ' filename=' . $this->file_name . ';';
            }
            header($cont_disp);
            return true;
        }
        return false;
    }

    //=========================================================================
    //=========================================================================
    // Stream Full Contents
    //=========================================================================
    //=========================================================================
    protected function StreamFullContents()
    {
        //---------------------------------------------------------------------
        // Output Headers?
        //---------------------------------------------------------------------
        if ($this->output_headers) {
            $this->OutputContentTypeHeader();
            $this->OutputContentDispositionHeader();
            if ($this->file_size) {
                header("Content-Length: {$this->file_size}");
            }
        }

        //---------------------------------------------------------------------
        // Start Buffering
        //---------------------------------------------------------------------
        ob_start();

        //---------------------------------------------------------------------
        // Stream File to Buffer
        //---------------------------------------------------------------------
        print stream_get_contents($this->stream);

        //---------------------------------------------------------------------
        // End Buffering and Output Contents
        //---------------------------------------------------------------------
        if (!empty($this->is_base64) && $this->base64_decode) {
            print base64_decode(ob_get_clean());
        }
        else {
            print ob_get_clean();
        }

        //---------------------------------------------------------------------
        // Return true for success
        //---------------------------------------------------------------------
        return true;
    }
}
