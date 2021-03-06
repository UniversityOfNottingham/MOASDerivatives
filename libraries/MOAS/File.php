<?php

/**
 * @package     omeka
 * @subpackage  moas-derivatives
 * @copyright   2016 University of Nottingham
 * @license     MIT
 * @author      Adam Cooper <adam.cooper@nottingham.ac.uk>
 */

/**
 * Class MOAS_File
 * 
 * Implementation of the decorator pattern for the manipulation of \File objects
 */
class MOAS_File
{
    /** @var boolean */
    public $has_derivative_image;

    /** @var \File */
    protected $_file;
    
    protected static $_derivatives = array(
        'header' => 1500
    );

    /**
     * @param \File $file The Omeka File class to decorate
     */
    public function __construct(\File $file) 
    {
        $this->_file = $file;
    }
    
    public function delete()
    {
        $storage = $this->getStorage();
        $files = array();
        if ($this->getProperty('has_derivative_image') === 1) {
            $types = self::$_derivatives;
            foreach ($types as $type => $path) {
                $files[] = $this->getStoragePath($type);
            }
        }
        foreach ($files as $file) {
            $storage->delete($file);
        }
    }

    public function extractMetadata()
    {
        $this->_file->extractMetadata();
    }

    /**
     * Adds our custom image derivatives to the creator and then creates them.
     *
     * @return bool
     * @throws Zend_Exception
     */
    public function createDerivatives()
    {
        if (!Zend_Registry::isRegistered('file_derivative_creator')) {
            return false;
        }
        $creator = Zend_Registry::get('file_derivative_creator');
        
        foreach (self::$_derivatives as $type => $size) {
            $creator->addDerivative($type, $size);
        }

        if ($creator->create($this->getPath('original'),
                             $this->_file->getDerivativeFilename(),
                             $this->_file->mime_type)) {
            $this->has_derivative_image = 1;
        }
    }
    
    public function storeFiles()
    {
        if ($this->has_derivative_image) {
            $storage = $this->getStorage();
            /** @var MOAS_Storage_Adapter_Filesystem $adapter */
            $adapter = $storage->getAdapter();

            // cleanup
            $storage->delete($this->getPath('original'));

            foreach (self::$_derivatives as $type => $size) {
                $adapter->registerSubDir($type);
                $storage->store($this->getPath($type), $this->getStoragePath($type));
            }
        }
    }

    public function getProperty($property)
    {
        return $this->_file->getProperty($property);
    }

    public function getPath($type = 'original')
    {
        $fn = $this->_file->getDerivativeFilename();
        $dir = $this->getStorage()->getTempDir();
        if ($type == 'original') {
            return $dir . '/' . $this->_file->filename;
        } else {
            return $dir . "/{$type}_{$fn}";
        }
    }

    public function getStorage()
    {
        return $this->_file->getStorage();
    }

    /**
     * Responsible for returning the path to the stored file.
     *
     * The decorator needs to do some things slightly differently so we can handle our own
     * image derivatives. We first try the default implementation and if that fails our own.
     *
     * @throws RuntimeException
     * @param string $type
     * @return string
     */
    public function getStoragePath($type = 'original')
    {
        $storage = $this->getStorage();

        try {
            $path = $this->_file->getStoragePath($type);
        } catch (RuntimeException $ex) {
            $fn = $this->_file->getDerivativeFilename();

            if (!isset(self::$_derivatives[$type])) {
                throw new RuntimeException(__('"%s" is not a valid file derivative.', $type));
            }

            $path = $storage->getPathByType($fn, $type);
        }

        return $path;
    }
}
