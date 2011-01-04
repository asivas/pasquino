<?php
/**
 * $Header: /server/cvsroot/pasquino/Log/adodb.php,v 1.2 2011-01-04 13:47:16 martinezdiaz Exp $
 *
 * @version $Revision: 1.2 $
 * @package Log
 */
require_once("datos/adodb/adodb.inc.php");
require_once("Log.php");

class Log_adodb extends Log
{
     /**
     * Handler existente de la base.
     */
    var $_db = null;

	/**
	 * Opciones de coneccion
	 */
	var $_options = array();
    /**
     * Booleano de estado de la coneccion
     */
    var $_conectado = false;

    /**
     * Tabla de la base a usar
     */
    var $_tabla = 'logs';

 
    /**
     * Constructs a new sql logging object.
     *
     * @param string $name         The target SQL table.
     * @param string $ident        The identification field.
     * @param array $conf          The connection configuration array.
     * @param int $level           Log messages up to and including this level.
     * @access public
     */
    function Log_adodb($name = 'logs', $ident = '', $conf = array(),
                     $level = PEAR_LOG_DEBUG)
    {
        $this->_id = md5(microtime());
        if($name!='')
        	$this->_tabla = $name;
        $this->_mask = Log::UPTO($level);

        /* If an options array was provided, use it. */
        if (isset($conf['options']) && is_array($conf['options'])) {
            $this->_options = $conf['options'];
        }
        else
        {
        	try 
        	{
	        	if(Configuracion::getDbHost() != "")
	        	{
	        	   	$this->_options = array();
					$this->_options['db_host'] = Configuracion::getDbHost();
					$this->_options['db_user'] = Configuracion::getDbUser();
					$this->_options['db_pass'] = Configuracion::getDbPassword();
					$this->_options['db_schema'] = Configuracion::getDbName();
	        	}
	        	else
	        	{
		        	$this->_options = array();
					$this->_options['db_host'] = DB_HOST;
					$this->_options['db_user'] = DB_USER;
					$this->_options['db_pass'] = DB_PASS;
					$this->_options['db_schema'] = DB_SCHEMA;
	        	}
        	}
        	catch(Exeption $e)
        	{
        		   	$this->_options = array();
					$this->_options['db_host'] = DB_HOST;
					$this->_options['db_user'] = DB_USER;
					$this->_options['db_pass'] = DB_PASS;
					$this->_options['db_schema'] = DB_SCHEMA;
        	}
        }
 
        /* Now that the ident limit is confirmed, set the ident string. */
        $this->setIdent($ident);

        /* If an existing database connection was provided, use it. */
        if (isset($conf['db'])) 
        {
            $this->_db = $conf['db'];
            $this->_existingConnection = true;
            $this->_opened = true;
        }
        else $this->_db = $this->open();
        
 
    }

    /**
     * Opens a connection to the database, if it has not already
     * been opened. This is implicitly called by log(), if necessary.
     *
     * @return boolean   True on success, false on failure.
     * @access public
     */
    function open()
    {
        if (!$this->_opened) 
        {
		    $tmp_db = ADONewConnection('mysql');
		    if(!$tmp_db) 
			    $doDie = true;
		    else
		    {    
		        $tmp_db->SetFetchMode(ADODB_FETCH_BOTH);
				
		        $doDie = !$tmp_db->Connect($this->_options['db_host'],$this->_options['db_user'],$this->_options['db_pass'],$this->_options['db_schema']);
		    }
		    if($doDie) 
	        die("Ha ocurrido un error tratando de conectarse con el origen de datos.".__FILE__.' '.__LINE__.' ');
    
		    return $tmp_db;

            /* We now consider out connection open. */
            $this->_opened = true;
        }

        return $this->_opened;
     }

    /**
     * Closes the connection to the database if it is still open and we were
     * the ones that opened it.  It is the caller's responsible to close an
     * existing connection that was passed to us via $conf['db'].
     *
     * @return boolean   True on success, false on failure.
     * @access public
     */
    function close()
    {
        /* If we have a statement object, free it. */
        if (is_object($this->_statement)) {
            $this->_statement->free();
            $this->_statement = null;
        }

        /* If we opened the database connection, disconnect it. */
        if ($this->_opened && !$this->_existingConnection) {
            $this->_opened = false;
            return $this->_db->Disconnect();
        }

        return ($this->_opened === false);
    }

    /**
     * Sets this Log instance's identification string.  Note that this
     * SQL-specific implementation will limit the length of the $ident string
     * to sixteen (16) characters.
     *
     * @param string    $ident      The new identification string.
     *
     * @access  public
     * @since   Log 1.8.5
     */
    function setIdent($ident)
    {
        $this->_ident = substr($ident, 0, $this->_identLimit);
    }

    /**
     * Inserts $message to the currently open database.  Calls open(),
     * if necessary.  Also passes the message along to any Log_observer
     * instances that are observing this Log.
     *
     * @param mixed  $message  String or object containing the message to log.
     * @param string $priority The priority of the message.  Valid
     *                  values are: PEAR_LOG_EMERG, PEAR_LOG_ALERT,
     *                  PEAR_LOG_CRIT, PEAR_LOG_ERR, PEAR_LOG_WARNING,
     *                  PEAR_LOG_NOTICE, PEAR_LOG_INFO, and PEAR_LOG_DEBUG.
     * @return boolean  True on success or false on failure.
     * @access public
     */
    function log($message, $priority = null)
    {
        /* If the connection isn't open and can't be opened, return failure. */
        if (!$this->_opened && !$this->open()) {
            return false;
        }

        /* Extract the string representation of the message. */
        $message = $this->_extractMessage($message);

        /* Build our set of values for this log entry. */
        $values = array
        (
            'logtime'  => date("Y-m-d H:i",strtotime("now")),
            'priority' => $priority,
            'message'  => $message
        );

        /* Execute the SQL query for this log entry insertion. */
        
        if(!$resultado = $this->_db->AutoExecute($this->_tabla,$values,'INSERT'))
		{
			print $this->_db->ErrorMsg();
			die;
		}	
 
        $this->_announce(array('priority' => $priority, 'message' => $message));

        return true;
    }

    /**
     * Create the log table in the database.
     *
     * @return boolean  True on success or false on failure.
     * @access private
     */
    function _createTable()
    {
        $this->_db->loadModule('Manager');
        $result = $this->_db->manager->createTable(
            $this->_table,
            array(
                'id'    => array('type' => $this->_types['id']),
                'logtime'     => array('type' => $this->_types['logtime']),
                'ident'     => array('type' => $this->_types['ident']),
                'priority'  => array('type' => $this->_types['priority']),
                'message'   => array('type' => $this->_types['message'])
            )
        );
        if (PEAR::isError($result)) {
            return false;
        }

        $result = $this->_db->manager->createIndex(
            $this->_table,
            'unique_id',
            array('fields' => array('id' => true), 'unique' => true)
        );
        if (PEAR::isError($result)) {
            return false;
        }

        return true;
    }

    /**
     * Prepare the SQL insertion statement.
     *
     * @return boolean  True if the statement was successfully created.
     *
     * @access  private
     * @since   Log 1.9.0
     */
    function _prepareStatement()
    {
        $this->_statement = &$this->_db->prepare(
                'INSERT INTO ' . $this->_table .
                ' (id, logtime, ident, priority, message)' .
                ' VALUES(:id, :logtime, :ident, :priority, :message)',
                $this->_types);

        /* Return success if we didn't generate an error. */
        return (PEAR::isError($this->_statement) === false);
    }
}
