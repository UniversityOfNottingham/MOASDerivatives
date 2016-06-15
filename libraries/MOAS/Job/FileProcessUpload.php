<?php

/**
 * @package     omeka
 * @subpackage  moas-derivatives
 * @copyright   2016 University of Nottingham
 * @license     MIT
 * @author      Adam Cooper <adam.cooper@nottingham.ac.uk>
 */

class MOAS_Job_FileProcessUpload extends Omeka_Job_AbstractJob
{
    public function perform()
    {
        $file = $this->_getFile();

        try {
            $this->_copyToTmp($file);
            $file->extractMetadata();
            $file->createDerivatives();
            $file->storeFiles();
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function _getFile()
    {
        $file = new File();
        $file->setArray($this->_options['fileData']);

        // Wrap in decorator
        $decoration = new MOAS_File($file);
        return $decoration;
    }

    /**
     * The act of saving a \File also causes the /tmp file that is used to create
     * derivatives to be deleted. Because of this we need to copy it back again
     * so that we can create our custom derivatives.
     * 
     * @param $file MOAS_File
     */
    private function _copyToTmp(MOAS_File $file)
    {
        /** @var Omeka_Storage $storage */
        $storage = $file->getStorage();
        
        $storage->copy($file->getStoragePath('original'), $storage->getTempDir() . '/' . $file->getProperty('filename'));
    }
}
