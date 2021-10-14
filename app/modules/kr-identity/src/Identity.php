<?php

class Identity extends MySQL {

  private $User = null;

  private $IdentityStatus = null;

  private $StatusConverter = [
    0 => 'Not verified',
    1 => 'Verification submited',
    2 => 'In verification',
    3 => 'Verified'
  ];

  public function __construct($User = null){
    $this->_setUser($User);
  }

  public function _setUser($User = null){
    if(!is_null($User)){
      $this->User = $User;
      $this->_loadUserIdentityStatus();
      //$this->_loadUserIdentityAssets();
    }
  }

  public function _getUser(){
    return $this->User;
  }

  public function _loadUserIdentityStatus(){
    if(is_null($this->_getUser())) return null;
    $r = parent::querySqlRequest("SELECT * FROM identity_krypto WHERE id_user=:id_user",
                                [
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);
    if(count($r) > 0) $this->IdentityStatus = $r[0];
  }

  public function _getIdentityStatus(){
    if(is_null($this->IdentityStatus)) return 0;
    return $this->IdentityStatus['status_identity'];
  }

                                                                                                                                                                                                                                                                                                                                                                                                                public function _identityWizardNotStarted(){
    return $this->_getIdentityStatus() == 0 && !$this->_identityInVerifcation();
  }

  public function _identityVerified(){
    return $this->_getIdentityStatus() == 2;
  }

  public function _identityInVerifcation(){

    if($this->_getIdentityStatus() == 2) return false;

    $r = parent::querySqlRequest("SELECT * FROM identity_asset_krypto WHERE id_user=:id_user AND id_identity=:id_identity",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_identity' => $this->_initUserIdentity()
                                ]);

    return count($r) == ($this->_getNumberIdentityStep() - 1);
  }

  public function _getIdentityStatusStr(){
    return $this->StatusConverter[$this->_getIdentityStatus()];
  }

  public function _getIdentityStep($step = 1){
    $r = parent::querySqlRequest("SELECT * FROM identity_step_krypto ORDER BY order_identity_step");
    if(count($r) < $step) return false;
    return $r[$step - 1];
  }

  public function _getIdentityStepByID($step = 1){
    $r = parent::querySqlRequest("SELECT * FROM identity_step_krypto WHERE id_identity_step=:id_identity_step", ['id_identity_step' => $step]);
    if(count($r) == 0) return false;
    return $r[0];
  }

  public function _isFinalStep($step){
    return $this->_getIdentityStep($step) == false;
  }

  public function _getNumberIdentityStep(){
    $r = parent::querySqlRequest("SELECT * FROM identity_step_krypto");
    return count($r) + 1;
  }

  public function _checkUserIdentityDirectory($App){

    if(!file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/identity')) mkdir($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/identity', 0777);
    if(!file_exists($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/identity/'.$App::encrypt_decrypt('encrypt', $this->_getUser()->_getUserID()))) mkdir($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/identity/'.$App::encrypt_decrypt('encrypt', $this->_getUser()->_getUserID()), 0777);

  }

  public function _initUserIdentity(){
    $r = parent::querySqlRequest("SELECT * FROM identity_krypto WHERE id_user=:id_user",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID()
                                  ]);
    if(count($r) > 0) return $r[0]['id_identity'];

    $s = parent::execSqlRequest("INSERT INTO identity_krypto (id_user, date_processed_identity, status_identity, lupdate_identity, document_identity)
                                VALUES (:id_user, :date_processed_identity, :status_identity, :lupdate_identity, :document_identity)",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'date_processed_identity' => time(),
                                  'status_identity' => 0,
                                  'lupdate_identity' => time(),
                                  'document_identity' => 0
                                ]);
    if(!$s) throw new Exception("Error : Fail to init identity verification", 1);
    return $this->_initUserIdentity();
  }

  public function _postAsset($step, $content, $App, $document_type){

    $stepInformation = $this->_getIdentityStep($step);

    if($stepInformation['type_identity_step'] == 'document'){

      $fileName = $App::encrypt_decrypt('encrypt', uniqid()).'-'.$content['name'];

      $this->_checkUserIdentityDirectory($App);


      move_uploaded_file($content['tmp_name'], $_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/identity/'.$App::encrypt_decrypt('encrypt', $this->_getUser()->_getUserID()).'/'.$fileName);

      $this->_saveAsset($stepInformation['id_identity_step'], $App::encrypt_decrypt('encrypt', $this->_getUser()->_getUserID()).'/'.$fileName, $App, $document_type);

    }

  }

  public function _postAssetForm($step, $content, $App){

    $stepInformation = $this->_getIdentityStepByID($step);

    if($stepInformation['type_identity_step'] == 'form'){

      $this->_saveAsset($step, json_encode($content), $App, 'form');

    }
  }


  public function _postAssetCamera($step, $content, $App, $document_type){
    $stepInformation = $this->_getIdentityStep($step);

    if($stepInformation['type_identity_step'] == 'document'){

      $fileName = $App::encrypt_decrypt('encrypt', uniqid()).'-'.$App::encrypt_decrypt('encrypt', uniqid()).'.png';
      $img = str_replace('data:image/png;base64,', '', $content);
      $img = str_replace(' ', '+', $img);
      $data = base64_decode($img);

        $this->_checkUserIdentityDirectory($App);

      $infosCameraUpload = file_put_contents($_SERVER['DOCUMENT_ROOT'].FILE_PATH.'/public/identity/'.$App::encrypt_decrypt('encrypt', $this->_getUser()->_getUserID()).'/'.$fileName, $data);

      $this->_saveAsset($step, $App::encrypt_decrypt('encrypt', $this->_getUser()->_getUserID()).'/'.$fileName, $App, $document_type);

    }
  }

  public function _saveAsset($step, $content, $App, $document_type){
    $r = parent::querySqlRequest("SELECT * FROM identity_asset_krypto WHERE id_user=:id_user AND id_identity_step=:id_identity_step AND id_identity=:id_identity",
                                [
                                  'id_user' => $this->_getUser()->_getUserID(),
                                  'id_identity_step' => $step,
                                  'id_identity' => $this->_initUserIdentity()
                                ]);

    if(count($r) == 0){
      $r = parent::execSqlRequest("INSERT INTO identity_asset_krypto (id_identity, id_identity_step, id_user, value_identity_asset)
                                  VALUES (:id_identity, :id_identity_step, :id_user, :value_identity_asset)",
                                  [
                                    'id_user' => $this->_getUser()->_getUserID(),
                                    'id_identity_step' => $step,
                                    'id_identity' => $this->_initUserIdentity(),
                                    'value_identity_asset' => $content
                                  ]);
    } else {
      $r = parent::execSqlRequest("UPDATE identity_asset_krypto SET value_identity_asset=:value_identity_asset WHERE id_identity_asset=:id_identity_asset",
                                  [
                                    'id_identity_asset' => $r[0]['id_identity_asset'],
                                    'value_identity_asset' => $content
                                  ]);
    }

    $r = parent::execSqlRequest("UPDATE identity_krypto SET document_identity=:document_identity WHERE id_identity=:id_identity AND id_user=:id_user",
                                [
                                  'id_identity' => $this->_initUserIdentity(),
                                  'document_identity' => $document_type,
                                  'id_user' => $this->_getUser()->_getUserID()
                                ]);

    if(($this->_getNumberIdentityStep() - 1) == $step){
      $template = new Liquid\Template();
      $template->parse(file_get_contents(APP_URL.'/app/modules/kr-identity/templates/adminActionRequired.tpl'));

      // Render & send email
      $App->_sendMail('l.dumontier@ovrley.com', 'New identity verification required', $template->render([
        'APP_URL' => APP_URL,
        'LOGO_BLACK' => $App->_getLogoBlackPath(),
        'APP_TITLE' => $App->_getAppTitle(),
        'SUBJECT' => 'New identity verification required'
      ]));
    }
  }

  public function _getDocList(){

    return parent::querySqlRequest("SELECT * FROM identity_doclist_krypto");

  }

  public function _deleteIdentityDocument($id_document){
    $r = parent::execSqlRequest("DELETE FROM identity_doclist_krypto WHERE id_identity_doclist=:id_identity_doclist",
                                [
                                  'id_identity_doclist' => $id_document
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to delete identity document", 1);

  }

  public function _addIdentityDocument($document_name){
    $r = parent::execSqlRequest("INSERT INTO identity_doclist_krypto (name_identity_doclist) VALUES (:name_identity_doclist)",
                                [
                                  'name_identity_doclist' => $document_name
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to add identity document", 1);

  }

  public function _getListStepHard(){
    return parent::querySqlRequest("SELECT * FROM identity_step_krypto ORDER BY order_identity_step");
  }

  public function _moveIdentityStep($identitystep, $dir){

    $currentIdentityStep = $this->_getListStepHard();
    $newIdentityStep = $currentIdentityStep;
    $oldStep = null;
    $kIdStep = null;
    foreach ($currentIdentityStep as $key => $value) {
      if($value['id_identity_step'] == $identitystep) $kIdStep = $key;
    }

    $oldStep = $newIdentityStep[$kIdStep];
    if($dir == "down"){
      $newIdentityStep[$kIdStep] = $newIdentityStep[$kIdStep + 1];
      $newIdentityStep[$kIdStep + 1] = $oldStep;
    }

    if($dir == "up"){
      $newIdentityStep[$kIdStep] = $newIdentityStep[$kIdStep - 1];
      $newIdentityStep[$kIdStep - 1] = $oldStep;
    }

    foreach ($newIdentityStep as $key => $value) {
      $r = parent::execSqlRequest("UPDATE identity_step_krypto SET order_identity_step=:order_identity_step WHERE id_identity_step=:id_identity_step",
                                  [
                                    'id_identity_step' => $value['id_identity_step'],
                                    'order_identity_step' => ($key + 1)
                                  ]);
    }


  }

  public function _deleteIdentityStep($identitystep){

    $r = parent::execSqlRequest("DELETE FROM identity_step_krypto WHERE id_identity_step=:id_identity_step",
                                [
                                  'id_identity_step' => $identitystep
                                ]);

    foreach ($this->_getListStepHard() as $key => $value) {
      $r = parent::execSqlRequest("UPDATE identity_step_krypto SET order_identity_step=:order_identity_step WHERE id_identity_step=:id_identity_step",
                                  [
                                    'id_identity_step' => $value['id_identity_step'],
                                    'order_identity_step' => ($key + 1)
                                  ]);
    }

  }

  public function _addIdentityStep($stepname, $description, $type, $webcam_enable, $webcam_ratio){

    $r = parent::execSqlRequest("INSERT INTO identity_step_krypto (name_identity_step, type_identity_step, description_identity_step, order_identity_step, allow_cam_identity_step, document_ratio_identity_step)
                                VALUES (:name_identity_step, :type_identity_step, :description_identity_step, :order_identity_step, :allow_cam_identity_step, :document_ratio_identity_step)",
                                [
                                  'name_identity_step' => $stepname,
                                  'type_identity_step' => $type,
                                  'description_identity_step' => $description,
                                  'order_identity_step' => count($this->_getListStepHard()) + 1,
                                  'allow_cam_identity_step' => $webcam_enable,
                                  'document_ratio_identity_step' => $webcam_ratio
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to add identity step", 1);

    return true;
  }

  public function _addIdentityStepForm($name, $formlist){


    $r = parent::execSqlRequest("INSERT INTO identity_step_krypto (name_identity_step, type_identity_step, description_identity_step, order_identity_step, allow_cam_identity_step, document_ratio_identity_step)
                                VALUES (:name_identity_step, :type_identity_step, :description_identity_step, :order_identity_step, :allow_cam_identity_step, :document_ratio_identity_step)",
                                [
                                  'name_identity_step' => $name,
                                  'type_identity_step' => 'form',
                                  'description_identity_step' => json_encode($formlist),
                                  'order_identity_step' => count($this->_getListStepHard()) + 1,
                                  'allow_cam_identity_step' => 0,
                                  'document_ratio_identity_step' => '1/1'
                                ]);

    if(!$r) throw new Exception("Error SQL : Fail to add identity step", 1);

    return true;

  }

  public function _getListIdentity(){

    $stepList = parent::querySqlRequest("SELECT * FROM identity_step_krypto");
    $stepListFormated = [];

    foreach ($stepList as $key => $value) {
      $stepListFormated["->".$value['id_identity_step']] = $value;
    }

    $identityType = parent::querySqlRequest("SELECT * FROM identity_doclist_krypto");
    $identityTypeFormated = [];

    foreach ($identityType as $key => $value) {
      $identityTypeFormated[$value['id_identity_doclist']] = $value;
    }

    $r = parent::querySqlRequest("SELECT * FROM identity_krypto ORDER BY status_identity, date_processed_identity");
    $return = [];
    foreach ($r as $key => $value) {
      $assetsList = parent::querySqlRequest("SELECT * FROM identity_asset_krypto WHERE id_identity=:id_identity ORDER BY id_identity_step", ['id_identity' => $value['id_identity']]);
      $return[$value['id_identity']] = [
        'identity_infos' => $value,
        'assets' => $assetsList,
        'user' => new User($value['id_user']),
        'step_list' => $stepListFormated,
        'identity_type' => ($value['document_identity'] == 0 ? null : $identityTypeFormated[$value['document_identity']])
      ];
    }

    return $return;

  }

  public function _getIdentityByIdentityID($identity_id){

    $r = parent::querySqlRequest("SELECT * FROM identity_krypto WHERE id_identity=:id_identity", ['id_identity' => $identity_id]);

    if(count($r) == 0) throw new Exception("Error : Identity not found ".$identity_id, 1);

    $User = new User($r[0]['id_user']);
    return new Identity($User);

  }

  public function _changeStatus($status, $args){

    if($this->_getIdentityStatus() == $status) return true;

    $r = parent::execSqlRequest("UPDATE identity_krypto SET status_identity=:status_identity WHERE id_identity=:id_identity",
                                [
                                  'status_identity' => $status,
                                  'id_identity' => $this->_initUserIdentity()
                                ]);

    if(!$r) throw new Exception("Error Identity : Fail to save status", 1);

    $NotificationCenter = new NotificationCenter($this->_getUser());

    if($status == 1){

      $NotificationCenter->_sendNotification('Identity declined', 'Your identity has been declined, reason : '.$args, '');

    }

    if($status == 2){

      $NotificationCenter->_sendNotification('Identity approved', 'Your identity has been just approved !', '');

    }



  }

}

?>
