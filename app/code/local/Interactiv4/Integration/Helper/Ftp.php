<?php
/**
 * Integration
 *
 * @category    Interactiv4
 * @package     Interactiv4_Integration
 * @copyright   Copyright (c) 2013 Interactiv4 SL. (http://www.interactiv4.com)
 */

class Interactiv4_Integration_Helper_Ftp extends Mage_Core_Helper_Abstract
{

    const FILEMODE_ASCII = FTP_ASCII;
    const FILEMODE_BINARY = FTP_BINARY;

	private $_connection = null;
    private $_settings = null;
    
    /**
     * Recieves the array with the values of the intetgration module. 
     * All values are saved into the $this->_settings instance.
     */
    public function setSettings($process_name = NULL) {
        $_settings = Mage::getStoreConfig($process_name);
        try {
            $_ftp = new Varien_Object;
            $_logs = new Varien_Object;
            $_process = new Varien_Object;
            $this->_settings = new Varien_Object;
            $_ftp->setHost($_settings['ftp']['host']);
            $_ftp->setPort($_settings['ftp']['port']);
            $_ftp->setTimeout($_settings['ftp']['timeout']);
            $_ftp->setUsername($_settings['ftp']['username']);
            $_ftp->setPassword(Mage::helper('core')->decrypt($_settings['ftp']['password']));
            $_ftp->setSsl($_settings['ftp']['ssl']);
            $_ftp->setPassive($_settings['ftp']['passive']);
            $_ftp->setFilemode($this->_getFilemode($_settings['ftp']['filemode']));
            $this->_settings->setFtp($_ftp);
            unset($_ftp);
            $_logs->setEnable($_settings['logs']['enable']);
            $this->_settings->setLogs($_logs);
            unset($_logs);
            if ($process_name) {
            	$_process->setName($process_name);
            } else {
            	$_process->setName('ftp');
            }
            $this->_settings->setProcess($_process);
            unset($_process);
        } catch (Exception $e) {
        	Mage::getModel('i4integration/logs')->saveLog($e->getMessage(), $this->_settings->getProcess()->getName(), true);
            return false;
        }
    }
    
    /**
     * Open the ftp connection.
     * 
     * @return  bool    True if can connect or False if isn't possible. The error is logged.
     */
    public function openConnection($process_name = NULL) {
        try {
            if (is_null($this->_connection)) {
                $this->setSettings($process_name);
            	if ($this->_settings->getFtp()->getSsl() == 1) {
            		$this->_connection = @ftp_ssl_connect($this->_settings->getFtp()->getHost(), $this->_settings->getFtp()->getPort(), $this->_settings->getFtp()->getTimeout());
            	} else {
            		$this->_connection = @ftp_connect($this->_settings->getFtp()->getHost(), $this->_settings->getFtp()->getPort(), $this->_settings->getFtp()->getTimeout());
            	}
            }
            Mage::getModel('i4integration/logs')->saveLog("Connected to " . $this->_settings->getFtp()->getHost() . ".", $this->_settings->getProcess()->getName());
        } catch (Exception $e) {
            Mage::getModel('i4integration/logs')->saveLog("ERROR: Could not connect to " . $this->_settings->getFtp()->getHost() . ".", $this->_settings->getProcess()->getName(), true);
            Mage::getModel('i4integration/logs')->saveLog("ERROR: " . $e->getMessage(), $this->_settings->getProcess()->getName(), true);
            return false;
        }
        return true;
    }
    
    /**
     * Do the login into the ftp server with the configured user and password.
     * 
     * @return  bool    True if the user can login into the server. False if can't loign. The error is logged.
     */
    public function doLogin() {
        try {
            @ftp_login($this->_connection, $this->_settings->getFtp()->getUsername(), $this->_settings->getFtp()->getPassword());
            Mage::getModel('i4integration/logs')->saveLog("Logged on " . $this->_settings->getFtp()->getHost() . "", $this->_settings->getProcess()->getName());
        } catch (Exception $e) {
            Mage::getModel('i4integration/logs')->saveLog("ERROR: Could not login to " . $this->_settings->getFtp()->getHost() . " with this credentials", $this->_settings->getProcess()->getName(), true);
            Mage::getModel('i4integration/logs')->saveLog("ERROR: " . $e->getMessage(), $this->_settings->getProcess()->getName(), true);
            $this->closeConnection();
            return false;
        }
        return true;
    }
    
    /**
     * Close ftp connection and save a message.
     * 
     * @return  bool    Always returns True.
     */
    public function closeConnection() {
        try {
            @ftp_close($this->_connection);
	    $this->_connection = null;
            Mage::getModel('i4integration/logs')->saveLog("Connection with " . $this->_settings->getFtp()->getHost() . " was closed.", $this->_settings->getProcess()->getName());
        } catch (Exception $e) {
            Mage::getModel('i4integration/logs')->saveLog("ERROR: Could not close connection with " . $this->_settings->getFtp()->getHost() . ".", $this->_settings->getProcess()->getName(), true);
            Mage::getModel('i4integration/logs')->saveLog("ERROR: " . $e->getMessage(), $this->_settings->getProcess()->getName(), true);
            return false;
        }
        return true;
    }
    
    /**
     * List all the files available into a remote directory.
     * 
     * @return  array   List of files available into a directory.
     */
    public function listRemoteFiles($folder_remote, $subfolder = null) {
        try {
            $list = array();
            if ($subfolder && $subfolder != '/') {
                $directory = $folder_remote . $subfolder;
            } else {
                $directory = $folder_remote;
            }
            if ($this->_settings->getFtp()->getPassive() == 1) {
                ftp_pasv($this->_connection, true);
            }
            $files = ftp_nlist($this->_connection, $directory);
            
            if (!count($files) > 0) {
                $files = $this->_listRemoteFilesFB($directory);
            }
            ftp_chdir($this->_connection, $directory);
            foreach ($files as $file) {
                if ((ftp_size($this->_connection, $file) != '-1') && ((strtoupper(substr($file, (strrpos($file, '.') + 1), strlen($file))) == 'TXT') || (strtoupper(substr($file, (strrpos($file, '.') + 1), strlen($file))) == 'CSV'))) {
                    $list[] = $file;
                }
            }
            if (!count($list) > 0) {
            	Mage::getModel('i4integration/logs')->saveLog("No files were founded into " . $directory . ".", $this->_settings->getProcess()->getName());
            }
            return $list;
        } catch (Exception $e) {
            Mage::getModel('i4integration/logs')->saveLog("ERROR: There was an error trying to get the remote files list.", $this->_settings->getProcess()->getName(), true);
            Mage::getModel('i4integration/logs')->saveLog("ERROR: " . $e->getMessage(), $this->_settings->getProcess()->getName(), true);
            return false;
        }
    }
    
    /**
     * Search into the file list all the occurencies with the same pattern as the given file.
     * 
     * @param   $file_name  string  Pattern to be found.
     * @param   $files      array   List of files to be searched in.
     * @return  array   List of files founded.
     */
    public function findFile($file_name, $files) {
        $found_files = array();
        if (count($files) > 0) {
            foreach ($files as $file) {
                if (is_numeric(strpos($file, $file_name))) {
                    if (strpos($file, '/')) {
                        $found_files[] = substr($file, 2, strlen($file));
                    } else {
                        $found_files[] = $file;
                    }
                }
            }
        }
        return $found_files;
    }
    
    private function _cleanFileName($file_name) {
    	if (strrpos($file_name, "/") != false) {
    		return substr($file_name, strrpos($file_name, "/"), strlen($file_name));
    	}
        return $file_name;
    }
    
    /**
     * Gets the constant name for every value.
     * 
     * @param integer $value Value stored for filemode value.
     * @return string Ftp filemode.
     */
    private function _getFilemode($value) {
    	$filemode = null;
        switch($value) {
    		case 1:
                $filemode = self::FILEMODE_ASCII;
            break;
            case 2:
                $filemode = self::FILEMODE_BINARY;
            break;
            default:
                $filemode = $value;
    	}
        return $filemode;
    }
    
    public function checkFolderExists($path) {
        try {
        	$ioProxy = new Varien_Io_File();
            $ioProxy->setAllowCreateFolders(true);
            $ioProxy->open(array('path' => $path));
            $ioProxy->close();
            unset($ioProxy);
            return true;
        } catch (Exception $e) {
        	return false;
        } 
    }
    
    /**
     * deprecated
     */
    private function _unzipFile($file, $subfolder = null) {
    	$zip = new ZipArchive;
        if ($zip->open($subfolder . DS . $file) === TRUE) {
            $zip->extractTo($subfolder . DS);
            $zip->close();
            $_directory = dir($subfolder);
            while ($_file = $_directory->read()) {
                if ($_file != "." && $_file != "..") {
                    Mage::getModel('i4integration/processed')->saveFile($subfolder . DS . $_file, now());
                }
            }
            $_directory->close();
            Mage::getModel('i4integration/logs')->saveLog("File " . $subfolder . DS .  $file . " was unzipped to " . $subfolder, $this->_settings->getProcess()->getName());
        } else {
            Mage::getModel('i4integration/logs')->saveLog("File " . $subfolder . DS .  $file . " can not be unzipped", $this->_settings->getProcess()->getName());
        }
    }
    
    /**
     * Downloads a given file from the host and folder seted into the backend.
     * 
     * @param   string   $file       Filename without path.
     * @param   string   $subfolder  Specific subfolder.
     * @return  bool    True if the file was downloaded. False if isn't possible to upload the file. The error is logged.
     */
    public function downloadFile($folder_local, $folder_remote,$file, $subfolder = null) {
        try {
            if ($subfolder && $subfolder != '/') {
            	$_folder_local = $folder_local . $subfolder;
                $_folder_remote = $folder_remote . $subfolder;
            } else {
                $_folder_local = $folder_local;
                $_folder_remote = $folder_remote;
            }
            if (!$this->checkFolderExists($_folder_local)) {
                Mage::getModel('i4integration/logs')->saveLog($_folder_local . " does not exists.", $this->_settings->getProcess()->getName(), true);
            	return false;
            }
            if ($this->_settings->getFtp()->getPassive() == 1) {
                ftp_pasv($this->_connection, true);
            }
            if (ftp_get($this->_connection, $_folder_local . DS . $this->_cleanFileName($file), $file, $this->_settings->getFtp()->getFilemode())) {
                Mage::getModel('i4integration/logs')->saveLog("File " . $file . " was downloaded to " . $_folder_local, $this->_settings->getProcess()->getName());
            } else {
                Mage::getModel('i4integration/logs')->saveLog("File " . $file . " can not be downloaded", $this->_settings->getProcess()->getName(), true);
                return false;
            }

        } catch (Exception $e) {
            Mage::getModel('i4integration/logs')->saveLog("FILE: " . $file . " can not be downloaded.", $this->_settings->getProcess()->getName(), true);
            Mage::getModel('i4integration/logs')->saveLog("ERROR: " . $e->getMessage(), $this->_settings->getProcess()->getName(), true);
            return false;
        }
        return true;
    }

    /**
     * Uploads a given file to the host and folder seted into the backend.
     * 
     * @param   string  $file   Filename without path.
     * @return  bool    True if the file was uploaded. False if isn't possible to upload the file. The error is logged.
     */
    public function uploadFile($folder_local, $folder_remote, $file) {
        try {
            $_folder_local = $folder_local;
            $_folder_remote = $folder_remote;
            if (!$this->checkFolderExists($_folder_local)) {
                Mage::getModel('i4integration/logs')->saveLog($_folder_local . " does not exists.", $this->_settings->getProcess()->getName(), true);
                return false;
            }
            if ($this->_settings->getFtp()->getPassive() == 1) {
                ftp_pasv($this->_connection, true);
            }
            if (ftp_put($this->_connection, $_folder_remote . DS . $file, $_folder_local . DS . $file, $this->_settings->getFtp()->getFilemode())) {
                if ($this->_settings->getLogs()->getEnable() == 1) {
                    Mage::getModel('i4integration/logs')->saveLog("File " . $file . " was uploaded to " . $_folder_remote, $this->_settings->getProcess()->getName());
                }
            } else {
                Mage::getModel('i4integration/logs')->saveLog("File " . $_folder_local . DS . $file . " can not be uploaded", $this->_settings->getProcess()->getName(), true);
                return false;
            }
        } catch (Exception $e) {
            Mage::getModel('i4integration/logs')->saveLog("FILE: " . $file . " can not be uploaded.", $this->_settings->getProcess()->getName(), true);
            Mage::getModel('i4integration/logs')->saveLog("ERROR: " . $e->getMessage(), $this->_settings->getProcess()->getName(), true);
            return false;
        }
        return true;
    }
    
    /**
     * @param integer file_type Sets which type of resources can be listed: 1. Files. 2. Folders.
     */
    public function renameFile($old_name, $new_name, $file_type, $_root_path) {
        try {
            $_blocked_names = array('.', '..');
            if (!in_array($old_name, $_blocked_names)) {
                $_paths = explode('/', $new_name);
                if ($file_type == 1) {
                    unset($_paths[(count($_paths)-1)]);
                }
                ftp_chdir($this->_connection, $_root_path);
                foreach ($_paths as $_path) {
                    if ($_path) {
                        if(!@ftp_chdir($this->_connection, $_path)){
                            ftp_mkdir($this->_connection, $_path);
                            ftp_chdir($this->_connection, $_path);
                        }
                    }
                }
                if (ftp_rename($this->_connection, $old_name, $new_name)) {
                    Mage::getModel('i4integration/logs')->saveLog("File " . $old_name . " was moved to " . $new_name, $this->_settings->getProcess()->getName());
                } else {
                    throw new Exception("No se puedo mover el fichero " . $old_name . " to " . $new_name);
                }
            }
        } catch (Exception $e) {
            Mage::getModel('i4integration/logs')->saveLog("File " . $old_name . " can't be moved.", $this->_settings->getProcess()->getName(), true);
            Mage::getModel('i4integration/logs')->saveLog("ERROR: " . $e->getMessage(), $this->_settings->getProcess()->getName(), true);
            return false;
        }
        return true;
    }
    
    //-----------------------------------------------------
    
    /**
     * Fail back method
     */
    private function _listRemoteFilesFB($_folder_remote) {
        if ($this->_settings->getFtp()->getSsl() == 1) {
            $_connection = 'ftps://' . $this->_settings->getFtp()->getHost() . $_folder_remote . DS;
        } else {
            $_connection = 'ftp://' . $this->_settings->getFtp()->getHost() . $_folder_remote . DS;
        }
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $_connection);
        curl_setopt($curl, CURLOPT_USERPWD, $this->_settings->getFtp()->getUsername() . ':' . $this->_settings->getFtp()->getPassword());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FTPLISTONLY, TRUE);
        $result = curl_exec ($curl);
        curl_close ($curl);
        $_remote_files = explode("\n", $result);
        $_files = array();
        foreach ($_remote_files as $_remote_file) {
            if ($_remote_file) {
                $_files[] = $_remote_file;
            }
        }
        return $_files;
    }
    
    //-----------------------------------------------------
    
    /**
     * deprecated
     * 
     * Validates the size of a given file when on demand process is executed.
     * 
     * @param string $file Remote file name.
     * @return integer File size expressed in KB.
     */
    public function validateSize($file, $subfolder = null) {
        if ($subfolder && $subfolder != '/') {
            $_size = ftp_size($this->_connection, $subfolder . $file);
        } else {
            $subfolder = null;
            $_size = ftp_size($this->_connection, $file);
        }
        if ($_size < 0) {
            Mage::getModel('i4integration/logs')->saveLog("File size can not be calculated for " . $subfolder . $file . ".", $this->_settings->getProcess()->getName());
            return false;
        }
        $_allowed_max_size = $this->_settings->getOndemand()->getMaxSize();
        if (!is_numeric($_allowed_max_size)) {
            Mage::getModel('i4integration/logs')->saveLog("" . $_allowed_max_size . " is not a valid value for max file size. Please change the configuration.", $this->_settings->getProcess()->getName());
            return false;   
        }
        $_kb_size = round(($_size / 1024), 2);
        if ($_kb_size > $_allowed_max_size) {
            Mage::getModel('i4integration/logs')->saveLog("File " . $subfolder . $file . " is too big to be executed on demand. Size: " . $_kb_size . " kb.", $this->_settings->getProcess()->getName());
            return false;
        }
        Mage::getModel('i4integration/logs')->saveLog("File " . $subfolder . $file . " is valid to be downloaded on demand. Size: " . $_kb_size . " kb.", $this->_settings->getProcess()->getName());
        return $_kb_size;
    }
    
    /**
     * Deprecated
     */
    private function _getFileDate($file, $subfolder = null) {
        
        if ($subfolder && $subfolder != '/') {
            $_folder_remote = $this->_settings->getDownload()->getRemote() . $subfolder . $file;
        } else {
            $_folder_remote = $this->_settings->getDownload()->getRemote() . $file;
        }
        
        if ($this->_settings->getFtp()->getSsl() == 1) {
            $_connection = 'ftps://' . $this->_settings->getFtp()->getHost() . $_folder_remote;
        } else {
            $_connection = 'ftp://' . $this->_settings->getFtp()->getHost() . $_folder_remote;
        }
        Mage::getModel('i4integration/logs')->saveLog("Connection: " . $_connection, $this->_settings->getProcess()->getName(), true);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $_connection);
        curl_setopt($curl, CURLOPT_USERPWD, $this->_settings->getFtp()->getUsername() . ':' . $this->_settings->getFtp()->getPassword());
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_NOBODY, 1);
        curl_setopt($curl, CURLOPT_FILETIME, TRUE);
        $result = curl_exec ($curl);
        $time = curl_getinfo($curl, CURLINFO_FILETIME);
        curl_close ($curl);
        return date('d/m/y H:i:s', $time);
    }
	
}
