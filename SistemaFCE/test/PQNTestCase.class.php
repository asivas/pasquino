<?php
require_once "PHPUnit/Extensions/Database/TestCase.php";
require_once 'SistemaFCE/util/Configuracion.class.php';
require_once __DIR__."/PHPUnitExtensionsDatabaseOperationMySQL55Truncate.php";

abstract class PQNTestCase extends PHPUnit_Extensions_Database_TestCase
{
	// only instantiate pdo once for test clean-up/fixture load
	static private $pdo = null;
	static private $conn = null;
	
	protected $mod;
	protected $dao;
	
	protected $backupGlobalsBlacklist = array('ADODB_INCLUDED_LIB');
	
	// only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
	//private $conn = null;
	
	public function __construct($name = NULL, array $data = array(), $dataName = '') {
		$this->configurarSistema();
		$this->initDao();
		$this->initMod();
		parent::__construct($name, $data, $dataName);
	}
	
	public function __destruct() {
		unset($this->dao);
		unset($this->mod);
	}
	
	abstract protected function initMod();
	abstract protected function initDao();
	
	abstract protected function configurarSistema();
	
	final public function getConnection()
	{
		if (self::$conn === null) {
			if (self::$pdo == null) {
				$dsn = Configuracion::getDbDSN();
				if($dsn=="")
				{
					$dsn = Configuracion::getDBMS().":host=".Configuracion::getDbHost();
					$port = Configuracion::getDbPort();
					$dbName = (string)Configuracion::getDbName();
					if($port!="")
						$dsn .= ";port={$port}";
					if($dbName!="")
						$dsn .= ";dbname={$dbName}";
				}
				try {
					self::$pdo = new PDO( $dsn, (string)Configuracion::getDbUser(), (string)Configuracion::getDbPassword() );
				} catch (PDOException $e) {
					echo 'Connection failed: ' . $e->getMessage();
				}				
            }
            self::$conn = $this->createDefaultDBConnection(self::$pdo, (string)Configuracion::getDbName());
		}
		return self::$conn;
	}
	
	public function getSetUpOperation()
	{
		$cascadeTruncates = FALSE; //if you want cascading truncates, false otherwise
		//if unsure choose false
	
		return new PHPUnit_Extensions_Database_Operation_Composite(array(
				new PHPUnit_Extensions_Database_Operation_MySQL55Truncate($cascadeTruncates),
				PHPUnit_Extensions_Database_Operation_Factory::INSERT()
		));
	}
}