#!/bin/bash
#

pQnDir=$(dirname `pwd`)
APACHECONF=/etc/apache2/conf.d
pQnConfFileName=pasquino.conf

read -p "Pasquino Path [$pQnDir]:" -r
[[ -n "$REPLY" ]] && pQnDir=$REPLY

read -p "Apache Configs dir [$APACHECONF]:" -r
[[  -n "$REPLY" ]]  && APACHECONF=$REPLY

read -p "Pasquino Config filename [$pQnConfFileName]:" -r
[[  -n "$REPLY" ]] && pQnConfFileName=$REPLY

read -p "SymLink Pasquino config file (Leave empty if not necessary) [$pQnConfFileSymLink]:" -r
[[  -n "$REPLY" ]] && pQnConfFileSymLink=$REPLY


echo replacing {pasquino} by ${pQnDir} from ${pQnDir}/.alias_apache.conf to ${APACHECONF}/$pQnConfFileName 
pQnDirEscaped=$(echo ${pQnDir} | sed -e 's/\//\\\//g' -e 's/\&/\\\&/g' )
sed -e "s/{pasquino}/${pQnDirEscaped}/" ${pQnDir}/.alias_apache.conf > ${APACHECONF}/$pQnConfFileName

if [[  -n "$REPLY" ]]; then
	echo Creating symlink $pQnConfFileSymLink of $pQnConfFileName
	ln -s ${APACHECONF}/$pQnConfFileName $pQnConfFileSymLink 
fi 

echo restarting apache
/etc/init.d/apache2 restart

echo DONE, Enjoy Pasquining ";-D"