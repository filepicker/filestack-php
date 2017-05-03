<?php
namespace Filestack;

use GuzzleHttp\Client;
use Filestack\FilestackConfig;

/**
 * Class representing a filestack filelink object
 */
class Filelink
{
    use Mixins\CommonMixin;
    use Mixins\ImageConversionMixin;

    public $api_key;
    public $handle;

    /**
     * Filelink constructor
     *
     * @param string    $handle     Filestack file handle
     * @param string    $api_key    Filestack API Key
     */
    function __construct($handle, $api_key='', $http_client=null)
    {
        $this->handle = $handle;
        $this->api_key = $api_key;

        if (is_null($http_client)) {
            $http_client = new Client();
        }
        $this->http_client = $http_client; // CommonMixin
    }

    /**
     * Get the content of filelink
     *
     * @param Filetack\Security $security   Filestack security object if
     *                                      security settings is turned on
     *
     * @throws FilestackException   if API call fails, e.g 404 file not found
     *
     * @return string (file content)
     */
    public function getContent($security=null)
    {
        // call CommonMixin function
        $result = $this->sendGetContent($this->url());

        return $result;
    }

    /**
     * Get metadata of filehandle
     *
     * @param array             $fields     optional, specific fields to retrieve.
     *                                      possible fields are:
     *                                      mimetype, filename, size, width, height,
     *                                      location, path, container, exif,
     *                                      uploaded (timestamp), writable, cloud, source_url
     *
     * @param Filetack\Security $security   Filestack security object if
     *                                      security settings is turned on
     *
     * @throws FilestackException   if API call fails
     *
     * @return json
     */
    public function getMetaData($fields=[], $security=null)
    {
        // call CommonMixin function
        $result = $this->sendGetMetaData($this->url(), $fields);
        return $result;
    }

    /**
     * Download filelink as a file, saving it to specified destination
     *
     * @param string            $handle         Filestack file handle
     * @param string            $destination    destination filepath to save to,
     *                                          can be folder name (defaults to stored filename)
     * @param Filetack\Security $security       Filestack security object if
     *                                          security settings is turned on
     *
     * @throws FilestackException   if API call fails
     *
     * @return bool (true = download success, false = failed)
     */
    public function download($destination, $security=null)
    {
        // call CommonMixin function
        $result = $this->sendDownload($this->url(), $destination, $security);
        return $result;
    }

    /**
     * Store this file to desired cloud service, defaults to Filestack's S3
     * storage.  Set $extra['location'] to specify location.
     * Possible values are: S3, gcs, azure, rackspace, dropbox
     *
     * @param array                 $extras     extra optional params.  Allowed options are:
     *                                          location, filename, mimetype, path, container,
     *                                          access (public|private), base64decode (true|false)
     * @param Filestack\Security    $security   Filestack Security object
     *
     * @throws FilestackException   if API call fails
     *
     * @return Filestack\Filelink or null
     */
    public function store($extras=[], $security=null)
    {
        $filepath = $this->url();

        // call CommonMixin function
        $filelink = $this->sendStore($filepath, $this->api_key, $extras, $security);

        return $filelink;
    }

    /**
     * return the URL (cdn) of this filelink
     *
     * @return string
     */
    public function url()
    {
        return sprintf('%s/%s', FilestackConfig::CDN_URL, $this->handle);
    }

    /**
     * return the URL (cdn) of this filelink with security policy
     *
     * @param Filestack\Security    $security   Filestack security object
     *
     * @return string
     */
    public function signedUrl($security)
    {
        return sprintf('url?policy=%s&signature=%s',
            $security->policy, $security->signature);
    }
}