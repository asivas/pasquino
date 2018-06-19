<?php
require_once __DIR__."/Configuracion.class.php";

class DbUpdaterBase {
	private $db;
	private $dataDict;
	static private $propMgr;

	static public $expectedDBVersion;

	static protected $dataSource;

	const R_INFO='Info';
	const R_ERROR='Error';
	const R_OK='Ok';
	const R_ACTION='Action';
	const R_ECHO='Echo';
	const R_IMPORTANT='Important';

	function __destruct() {
		if(isset($this->db) && $this->db->isConnected())
			$this->db->close();
	}

	private function createConection($dataSource=null)
	{
		if(!isset($dataSource))
			$dataSource = Configuracion::getDefaultDataSource();

		$dbms = Configuracion::getDBMS($dataSource);
		$db = NewADOConnection($dbms); # eg 'mysql' or 'postgres'

		if($dbms=='mysqli')
			$dbms = 'mysql';
		$this->dataDict = NewDataDictionary($db, $dbms); # force mssql
		$db->SetFetchMode(ADODB_FETCH_ASSOC);
		if(Configuracion::getDbDSN($dataSource)!='')
			$db->NConnect(Configuracion::getDbDSN($dataSource), Configuracion::getDbUser($dataSource), Configuracion::getDbPassword($dataSource));
		elseif(Configuracion::getDBMS($dataSource)=='mysqli') //mysqli no conecta con servidor:puerto
		{
			if(($port = Configuracion::getDbPort($dataSource) ) != '')
				$db->port = $port;
			$db->NConnect(Configuracion::getDbHost($dataSource), Configuracion::getDbUser($dataSource), Configuracion::getDbPassword($dataSource), Configuracion::getDbName($dataSource));
		}
		else
			$db->NConnect(Configuracion::getDbHostPort($dataSource), Configuracion::getDbUser($dataSource), Configuracion::getDbPassword($dataSource), Configuracion::getDbName($dataSource));
		return $db;
	}

	/**
	 * Obtiene el handler (objeto adodb) para interactuar con la base de datos.
	 *
	 * En caso de no estar creada la conexión para la clase la crea invocando createConection.
	 * @return Objet Objeto AdoDb de la conexion
	 */
	protected function getDb() {
		if(!isset($this->db))
		{
			$class = get_class($this);
			$this->db = $this->createConection($class::$dataSource);
		}

		return $this->db;
	}

	/**
	 * Ejecuta una consulta SQL en la base del sistema (se conexta mediante createConection)
	 * queda creado porque inicalmente se creó con error de ortografía
	 * @deprecated usar executeQuery
	 * @param string $query
	 */
	protected function excecuteQuery($query) {
		return $this->executeQuery($query);
	}

	/**
	 * Ejecuta una consulta SQL en la base del sistema (se conexta mediante createConection)
	 * @param string $query
	 */
	protected function executeQuery($query) {
		$db = $this->getDb();
		if(stripos($query, ";")!==false)
		{
			$queriesOK = true;
			$queries = explode(";", $query);
			foreach($queries as $q) {
				$q=trim($q);
				if(!empty($q))
				{
					$rs = $db->execute($q);
					if(!$rs){
						$queriesOK =false;
						$this->report($db->ErrorMsg() . "<br><b> Ejecutando</b>: $q",self::R_ERROR);
					}
				}
			}
			return $queriesOK;
		}
		$rs = $db->execute($query);
		if(!$rs) $this->report($db->ErrorMsg() . " <br><b>Ejecutando</b>: $query",self::R_ERROR);
		return $rs;
	}

	/**
	 * Retorna el ultimo mensaje obtenido de la base de datos
	 */
	public function getErrorMsg() {
		return $this->getDb()->ErrorMsg();
	}

	protected function reportLastError() {
		$this->report($this->getErrorMsg(),self::R_ERROR);
	}

	/**
	 * Muestra por pantalla un mensaje dada la tipificación de reporte lo muestra diferente
	 * @param unknown $message
	 * @param unknown $type
	 */
	protected function report ($message,$type=self::R_ECHO) {
		$color = 'black';
		if($type == self::R_ERROR)
			$color = 'red';
		if($type == self::R_OK)
			$color = 'green';

		if($type == self::R_ECHO)
			$type = "";

		print "<span style='color:{$color}; font-weight:bold;'>{$type}</span> {$message} </br>";
		ob_flush();
	}
	
	protected function getVersionUpdateMethod($version) {
		$version = str_replace('.', "_", $version);
		return "changesVersion{$version}";
	}
	
	protected function versionUpdateExists($version) {
		$changesMethod = $this->getVersionUpdateMethod($version);
		return method_exists($this, $changesMethod);
	}	
	
	protected function runChangesForVersion($version) {
		if($this->versionUpdateExists($version))
		{
			$changesMethod = $this->getVersionUpdateMethod($version);
			$this->report("Actualizando desde versión ".SistemaFCE::getVersionDB()." a versión {$version}");
			return $this->$changesMethod();
		}
		else
			$this->report("No se pudo actualizar desde versión ".SistemaFCE::getVersionDB()." a versión {$version} el metodo de actualización no existe",self::R_ERROR);
		return false;

	}
	
	protected function increaseVersion($ver,$increaseLimit) {
		
		do {		
			$ver += 0.1;
		}
		while($ver<$increaseLimit && !$this->versionUpdateExists($ver));
		
		return round($ver,1);
	}

	/**
	 * Actualiza la base de datos ejecuntado lo necesario desde a la versión inicial hasta la versión final
	 * @param unknown $fromVersion versión en la que de la base que se espera que se encuentre
	 * @param string $toVersion versión de la base a la que se quiere llegar
	 */
	public function updateDb($fromVersion=null,$toVersion=null) {

		if(!isset($fromVersion))
			$fromVersion = SistemaFCE::getVersionDB();
		if(!isset($toVersion))
		{
			$class = get_class($this);
			$toVersion = $class::$expectedDBVersion;
		}

		if($toVersion == $fromVersion)	return; //no hace falta actualizar

		$this->report("Se detectó que hay una actualización disponible. Al finalizar la actualización podrá usar el sistema nuevamente. Por favor espere un momento");

		set_time_limit(0);
		$ver = $this->increaseVersion($fromVersion,$toVersion);
		$noErrors = true;
		$db = $this->getDb();
		while($ver<=$toVersion && $noErrors)
		{
			$bVerUpdated = false;

			$db->startTrans();
			$this->runChangesForVersion($ver);
			$bVerUpdated |= $db->completeTrans();

			if($bVerUpdated)
				$bVerUpdated = $this->updateVersionProperty($ver);

			if($bVerUpdated)
				$this->report("Actualizado a $ver",self::R_OK);
			else if($this->getErrorMsg()!='')
				$this->reportLastError();

			$noErrors &= $bVerUpdated;
			
			$updatedTo = $ver;
			
			//busco sub versiones
			$ver = $this->increaseVersion($ver,$toVersion);
		}

		if(!$noErrors)
			print $this->reportLastError();
		if($updatedTo==$toVersion)
		{
			$this->report("Gracias! <a href='./'>Volviendo a cargar el sistema</a> podrá usar la nueva versión");
		}
		else
			$this->report("Hubo errores, intentelo de nuevo <a href='./'>Volviendo a cargar el sistema</a>",self::R_ERROR);
		die;
	}

	private function updateVersionProperty($version) {
		$propsMgrClass = SistemaFCE::getPropertiesManagerClass();
		if(!empty($propsMgrClass))
		{
			require_once "$propsMgrClass.class.php";
			$bVerUpdated = $propsMgrClass::setPropertyValue('versionDB',$version);
			if(!$bVerUpdated)
				throw new Exception("No se pudo actualizar el nro de versión ". $this->getDb()->ErrorMsg());
			return $bVerUpdated;
		}

		throw new Exception("No se pudo actualizar el nro de versión. No hay administrador de propiedades");
		return false;
	}

	protected function disableForeignKeyChecks() {
		$this->executeQuery("SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0");
	}

	protected function resetForeignKeyChecks() {
		$this->executeQuery("SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS");
	}

	protected function setSqlMode($sqlMode='TRADITIONAL') {
		$this->executeQuery("SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='{$sqlMode}'");
	}

	protected function resetSqlMode() {
		$this->executeQuery("SET SQL_MODE=@OLD_SQL_MODE");
	}

	protected function disableUniqueKeyChecks() {
		$this->executeQuery("SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0");
	}

	protected function reseteUniqueKeyChecks() {
		$this->executeQuery("SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS");
	}

	protected function setNamesCharset($charset='utf8') {
		$this->executeQuery("SET NAMES {$charset}");
	}

	public function tableExists($tableName) {
		if($rs = $this->executeQuery("SHOW TABLES LIKE '{$tableName}'"))
		{
			return $rs->RowCount()!=0;
		}

		return false;
	}

	public function foreignKeyExists($tableName,$foreignKeyName) {
		if($this->tableExists($tableName))
			if($rs = $this->executeQuery("SHOW CREATE TABLE {$tableName}"))
			{
				$create =$rs->fields['Create Table'];
				return stripos($create, "CONSTRAINT `$foreignKeyName`")!==FALSE;
			}

		return false;
	}

	public function columnExists($tableName,$columnName) {
		if($this->tableExists($tableName))
			if($rs = $this->executeQuery("SHOW COLUMNS FROM `{$tableName}` WHERE Field = '$columnName'"))
				return $rs->RowCount()>0;

			return false;
	}

	public function indexExists($tableName,$indexName) {
	    if($this->columnExists($tableName))
	        if($rs = $this->executeQuery("select count(*) as c from information_schema.statistics where table_name = '{$tableName}' and index_name = '{$indexName}' and table_schema = database()"))
	            return $rs->fields['c'] > 0;
	    return false;
    }

	/**
	 * Crea una tabla con un nombre, columnas, indices y foreign keys dadas
	 * @param unknown $tableName
	 * @param unknown $columns
	 */
	protected function createTable($tableName,$columns,$indexes=null,$foreignKeys=null,$collation=null,$engine=null) { }

	protected function alterTable($tableName,$columns,$indexes=null,$foreignKeys=null,$collation=null,$engine=null) { }

	protected function renameTable($tableName,$newTableName) { }

	/**
	 * Inserta el arreglo de valores en la tabla
	 * @param string $tableName nombre de la tabla donde insertar campos
	 * @param array $values arreglo asociativo que tiene las claves como nombre de campo de tipo array(nombre_campo1=>valor1,...,nombre_campoN=>valorN)
	 */
	protected function insert($tableName,$record) {
		return $this->getDb()->AutoExecute($tableName,$record,'INSERT');
	}

	/**
	 * Actualiza el arreglo de valores en la tabla
	 * @param string $tableName nombre de la tabla donde insertar campos
	 * @param array $values arreglo asociativo que tiene las claves como nombre de campo de tipo array(nombre_campo1=>valor1,...,nombre_campoN=>valorN)
	 * @param string $where condición de filtro para hacer el update del registro
	 */
	protected function update($tableName,$record,$where) {
		return $this->getDb()->AutoExecute($tableName,$record,'UPDATE',$where);
	}
}