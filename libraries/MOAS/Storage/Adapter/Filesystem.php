<?php

/**
 * @package     omeka
 * @subpackage  moas-derivatives
 * @copyright   2016 University of Nottingham
 * @license     MIT
 * @author      Adam Cooper <adam.cooper@nottingham.ac.uk>
 */

class MOAS_Storage_Adapter_Filesystem extends Omeka_Storage_Adapter_Filesystem
{
    public function registerSubDir($subDir)
    {
        $this->_subDirs[] = $subDir;

        $dirToCreate = $this->_getAbsPath($subDir);
        if (!is_dir($dirToCreate)) {
            $made = @mkdir($dirToCreate, 0770, true);
            if (!$made || !is_readable($dirToCreate)) {
                throw new Omeka_Storage_Exception("Error making directory: "
                    . "'$dirToCreate'");
            }
        }
        if (!is_writable($dirToCreate)) {
            throw new Omeka_Storage_Exception("Directory not writable: "
                . "'$dirToCreate'");
        }
    }

    public function copy($source, $dest)
    {
        $status = $this->_copy($this->_getAbsPath($source),
            $dest);

        if (!$status) {
            throw new Omeka_Storage_Exception('Unable to copy file.');
        }
    }

    /**
     * @param $path string The Omeka storage path to a file
     * @return bool The file exists
     */
    public function isFile($path)
    {
        return is_file($this->_getAbsPath($path));
    }

    /**
     * Get a local path for a "stored" file.
     *
     * @param string $path
     * @return string Absolute filesystem path to file
     */
    public function getLocalPath($path)
    {
        return $this->_localDir . '/' . $path;
    }

    /**
     * @throws Omeka_Storage_Exception
     * @return boolean
     */
    protected function _copy($source, $dest)
    {
        $destDir = dirname($dest);
        if (!is_writable($destDir)) {
            throw new Omeka_Storage_Exception("Destination directory is not "
                . "writable: '$destDir'.");
        }
        return copy($source, $dest);
    }
}
