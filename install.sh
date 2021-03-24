#!/bin/bash
# Pasquino Ubuntu installer 
#

STEPS=4

getopts "y" options

# Make sure only root (or executed via sudo) can run our script
if [[ $EUID -ne 0 ]]; then
   echo "This script must be run with root privileges" 1>&2
   exit 1
fi

pQnDir=`pwd`
APACHECONF=/etc/apache2/conf-available
pQnConfFileName=pasquino.conf
pQnIncludepathIniDir=/etc/php5/mods-available

echo Welcome to pasquino Ubuntu installer
  echo "Step 1 of $STEPS: set the directories and config file paths "

if [ "${options}" != "y" ]; then

  #alias de js y css (visual)
  read -p "Pasquino Path [$pQnDir]:" -r
  [[ -n "$REPLY" ]] && pQnDir=$REPLY

  read -p "Apache Configs dir [$APACHECONF]:" -r
  [[  -n "$REPLY" ]]  && APACHECONF=$REPLY

  read -p "Pasquino Config filename [$pQnConfFileName]:" -r
  [[ -n "$REPLY" ]] && pQnConfFileName=$REPLY

  read -p "Php mods-available dir [$pQnIncludepathIniDir]:" -r
  [[ -n "$REPLY" ]] && pQnIncludepathIniDir=$REPLY
else
  echo "Using defaults"
fi
# this could be useful if the installer was standalone
#
##check git command is installed
#git > /dev/null 2>&1
#
## if it isn't we install it via apt-get
#if [ $? != 0 ]; then
#	apt-get -y install git
#fi
#
#cd $pQnDir


echo "Step 2 of $STEPS: installing pear dependencies"

# Corroboramos si esta instalado pear
which pear > /dev/null 2>&1

# si no existe intalamos via apt-get
if [ $? -ne 0 ]; then
	echo "Pear is required, installing pear"
	apt-get update && apt-get -y install php-pear 
	pear channel-update pear.php.net > /dev/null
fi
pear -q upgrade -f PEAR
pear -q install -f Archive_Tar 
pear -q install -f Auth Auth_SASL 
pear -q install -f Console_Getopt DB File HTML_Common HTML_QuickForm 
pear -q install -f HTTP HTTP_Client HTTP_Request 
pear -q install -f Log 
pear -q install -f MDB2 MDB2_Driver_mysql MDB2_Driver_mysqli 
pear -q install -f Mail Mail_Mime Mail_Queue Mail_mimeDecode 
pear -q install -f Net_SMTP Net_Socket Net_URL Net_UserAgent_Detect 
pear -q install -f Structures_Graph 
pear -q install -f XML_Parser XML_RPC XML_Util

echo "Step 3 of $STEPS: installing apache aliases"
echo replacing {pasquino} by ${pQnDir} from ${pQnDir}/.alias_apache.conf to ${APACHECONF}/$pQnConfFileName 
pQnDirEscaped=$(echo ${pQnDir} | sed -e 's/\//\\\//g' -e 's/\&/\\\&/g' )
sed -e "s/{pasquino}/${pQnDirEscaped}/" ${pQnDir}/.alias_apache.conf > ${APACHECONF}/$pQnConfFileName

read -p "Do you wish to install the alias with a2enconf [Y/n]?" yn
case $yn in	    
    [Nn]* ) break;;
    [Yy]* )
        pQnConfName="${$pQnConfFileName%.*}";
	    a2enconf $pQnConfName;
	 ;;
    * ) 
	   pQnConfName="${$pQnConfFileName%.*}";
	   a2enconf $pQnConfName;
	 ;;
esac

echo "Step 4 of $STEPS: replacing php include path"
pQnIncludepathIni=$pQnIncludepathIniDir/pasquino.ini
includepath=$(php -i | grep include_path | awk '{print $5}')

# Check if phpenmod exists
which phpenmod > /dev/null 2>&1
if [ $? -eq 0 ]; then
    PHPENMODCMD=phpenmod
else
    which php5enmod > /dev/null 2>&1
    if [ $? -eq 0 ]; then
        PHPENMODCMD=php5enmod
    fi
fi


createPhpIncludePathIni() {
    iniFile=$1;
    echo "; configuration for pasquino include_path" > $iniFile;
    echo "; priority=20" >> $iniFile;
    echo "include_path='$includepath:${pQnDir}:'\${include_path}" >> $iniFile;
    echo $iniFile created, executing $PHPENMODCMD pasquino
    $PHPENMODCMD pasquino
}

read -p "Do you wish to set include_path to $includepath:${pQnDir} [Y/n]?" yn
case $yn in	    
    [Nn]* ) break;;
	[Yy]* )
	    createPhpIncludePathIni $pQnIncludepathIni;
	   ;;
    * )
        createPhpIncludePathIni $pQnIncludepathIni;
     ;;
esac

echo Restarting apache
/etc/init.d/apache2 restart

echo 
echo DONE, Enjoy Pasquining ";-D"
