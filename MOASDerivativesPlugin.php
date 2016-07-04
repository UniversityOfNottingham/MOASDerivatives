<?php

/**
 * @package     omeka
 * @subpackage  moas-derivatives
 * @copyright   2016 University of Nottingham
 * @license     MIT
 * @author      Adam Cooper <adam.cooper@nottingham.ac.uk>
 */

use \File as Omeka_File;

class MOASDerivativesPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'after_delete_record',
        'after_save_record',
        'initialize',
        'uninstall_message'
    );

    public function hookInitialize()
    {
        Zend_Registry::get('bootstrap')->bootstrap('Storage');
        $storage = Zend_Registry::get('storage');

        if (!$storage->getAdapter() instanceof MOAS_Storage_Adapter_Filesystem) {
            /** @var $flashMessenger Omeka_Controller_Action_Helper_FlashMessenger */
            $flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
            $flashMessenger->addMessage('The MOAS Derivatives plugin has been enabled without the ' .
                'MOAS_Storage_Adapter_Filesystem storage adapter being configured in the config.ini');
        }
    }

    public function hookAfterDeleteRecord($args)
    {
        if ($args['record'] instanceof Omeka_File) {
            $file = new MOAS_File($args['record']);
            $file->delete();
        }
    }

    public function hookAfterSaveRecord($args)
    {
        if ($args['record'] instanceof Omeka_File) {
            if ($args['insert']) {
                $dispatcher = Zend_Registry::get('job_dispatcher');
                $dispatcher->setQueueName('uploads');
                $dispatcher->send('MOAS_Job_FileProcessUpload', array('fileData' => $args['record']->toArray()));
            }
        }
    }

    /**
     * Display the uninstall message.
     */
    public function hookUninstallMessage()
    {
        echo __('%sWarning%s: Uninstalling this plugin without removing the relevant configuration stanza in ' .
            'your Omeka installations config.ini file %swill%s cause your site to become inoperable.%s',
            '<p><strong>', '</strong>', '<strong>', '</strong>', '</p>');
    }
}
