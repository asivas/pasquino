<?php
namespace pQn\SistemaFCE\util;

use pQn\datos\ssHandler\ssHandler;
use pQn\auth\EcontrolAuth;

class Session extends ssHandler{

	protected $auths=array();

	/**
	 * PHP Session attribute with Last Valid Auth identifier
	 * @var String
	 */
	protected $sessionAuthClassName='pQn_AUTH_NAME';

    function Session($sessName = null) {
        if(!isset($sessName)) $sessName =Configuracion::getAppName();
    	$this->sessionName =  $sessName;
        $authsConfig = Configuracion::getAuths();
        if(count($authsConfig->auth)==0)
        	$this->addAuth(new EcontrolAuth());
        else
        {
	        foreach($authsConfig->auth as $authConfig)
	        {
	        	$authClass = (string)$authConfig['class'];
	        	$this->addAuth(new $authClass());
	        }
        }

        if ($this->getCurrentAuthClass() != null){
        	$authClass=$this->getCurrentAuthClass();
        	parent::__construct(new $authClass());
        }else{
        	parent::__construct($this->auths[0]);
        }
    }

    protected function addAuth(\Auth $auth){
    	$this->auths[]=$auth;
    }

    protected function checkValidAuths(){
    	if (!(count($this->auths)>0))
    		throw new \Exception('No valid auths');
    	return ;
    }

	/* (non-PHPdoc)
	 * @see ssHandler::initSessionData()
	 */
	public function initSessionData() {
		parent::initSessionData();

		$this->checkValidAuths();

		//Initialize all sessions
		foreach ($this->auths as $aAuth){
			$aAuth->setSessionName($this->sessionName.get_class($aAuth));
			$aAuth->setExpire($this->cookie_min*60);
		}
	}

	protected function setCurrentAuthClass($aClassName){
		$_SESSION[$this->sessionAuthClassName]=$aClassName;
	}

	protected function getCurrentAuthClass(){
		if (isset($_SESSION[$this->sessionAuthClassName]))
			return $_SESSION[$this->sessionAuthClassName];
		return null;
	}

	protected function checkSessions(){
		for ($i=0;$i< count($this->auths);$i++){
			$this->auths[$i]->start();
			if ($this->auths[$i]->getAuth()){
				$this->setCurrentAuthClass(get_class($this->auths[$i]));
				$this->auth=$this->auths[$i];
				return true;
			}
		}
		return false;
	}

	function IsLoged() {
		$this->checkValidAuths();
		return $this->checkSessions();
	}

    function initMembers()
    {
        parent::initMembers();
        $this->cookie_min= 0;
    }

    /**
     * Devuelve el tiempo restante de sesion en segundos o FALSE si la sesión no expira nunca
     */
    function getRemainingTime() {
    	if($this->auth->expire > 0)
    		return time() - ($this->auth->session['timestamp'] + $this->auth->expire);

    	if($this->auth->idle > 0)
    		return ($this->auth->session['idle'] + $this->auth->idle) - time();

    	return FALSE;
    }

    function close($deleteAuths=false) {
        if($deleteAuths)
        {
            foreach($this->auths as $auth) {
                if(method_exists($auth,'disconnectDB'))
                    $auth->disconnectDB();
                unset ($auth);
            }
        }
        parent::close();
    }
}