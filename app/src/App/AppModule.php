<?php

/**
 * Module class
 *
 * @package Krypto
 * @author Ovrley <hello@ovrley.com>
 */
class AppModule {

  /**
   * Module directory
   * @var String
   */
  private $moduleDirectory = null;

  /**
   * Module configuration
   * @var Array
   */
  private $moduleConfig = null;

  /**
   * Module constructor
   * @param String $moduleDirectory Module directory
   */
  public function __construct($moduleDirectory = null){

    if(is_null($moduleDirectory)) throw new Exception("Error : Fail to load module (Directory is null)", 1);
    $this->moduleDirectory = $moduleDirectory;

    // Load module
    $this->_loadModule();
  }

  /**
   * Get module directory
   * @return String Module directory
   */
  public function _getDirectory(){
    if(is_null($this->moduleDirectory)) throw new Exception("Error : Module directory is empty", 1);
    return $this->moduleDirectory;
  }

  /**
   * Get module URL
   * @return String Module URL
   */
  public function _getModuleURL(){ return APP_URL.'/app/modules/'.$this->_getDirectory(); }

  /**
   * Get module PATH
   * @return String Module PATH
   */
  public function _getModulePath(){ return $_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/app/modules/'.$this->_getDirectory(); }

  /**
   * Load module
   */
  private function _loadModule(){

    // Load module configuration file
    if(!file_exists($this->_getModulePath().'/config.json')) throw new Exception("Error : Config file not exist for module : ".$this->_getDirectory(), 1);

    // Parse module configuration file from JSON
    //$this->moduleConfig = json_decode(file_get_contents($this->_getModulePath().'/config.json'), true);

    //if(!$this->moduleConfig) throw new Exception("Error : Fail to open config file for module : ".$this->_getDirectory(), 1);

  }

  /**
   * Check if module was enabled
   * @return boolean Module enable status
   */
  public function _isEnable(){
    return true;
    if(!array_key_exists('enable', $this->moduleConfig)) return false;
    return $this->moduleConfig['enable'];
  }

  /**
   * Check module configuration file
   * @return Boolean Module configuration file was correct
   */
  public function _checkConfig(){
    return true;
    if(count($this->moduleConfig) > 0) return true;
    return false;
  }

  /**
   * Load module assets
   * @param  String $type Assets type (css, js)
   * @return Array        Assets List
   */
  public function _loadAssets($type = "css"){
    $res = [];
    // Check if directory exist
    if(!file_exists($this->_getModulePath().'/statics/'.$type)) return [];

    // Get list assets files
    foreach (scandir($this->_getModulePath().'/statics/'.$type) as $asset) {

      // Check validity assets & is not directory
      if($asset == "." || $asset == ".." || is_dir($this->_getModulePath().'/assets/'.$type.'/'.$asset)) continue; // Check file validity

      // If assets need is CSS
      if($type == "css"){
        $res[] = '<link rel="stylesheet" href="'.$this->_getModuleURL().'/statics/'.$type.'/'.$asset.'?v='.App::_getVersion().'">'; // Load css stylesheet
      }
      if($type == "js"){ // If assets need is JS
        $res[] = '<script src="'.$this->_getModuleURL().'/statics/'.$type.'/'.$asset.'?v='.App::_getVersion().'" charset="utf-8"></script>'; // Load JS script
      }
    }
    return $res;
  }

  /**
   * Load module controllers list
   * @return Array Controllers list
   */
  public function _loadControllers(){
    $res = [];

    // Check if module directory controllers exist
    if(!file_exists($this->_getModulePath().'/src')) return [];

    // Get list controllers list
    foreach (scandir($this->_getModulePath().'/src') as $asset) {
      // Check validy controllers & is not directory
      if($asset == "." || $asset == ".." || is_dir($this->_getModulePath().'/src/'.$asset)) continue;

      // Append assets
      $res[] = $asset;
    }
    return $res;
  }

}

?>
