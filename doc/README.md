
Chaque site a un routeur Asus configuré pour servir de serveur DHCP. Il a l'IP 192.168.2.1 et donne des IPs sur la plage 192.168.2.20 à 192.168.2.254.

## Connectivité
Un script est appelé régulièrement pour valider que le site est toujours bien connecté à internet:
```
*/15 * * * * root wget -q http://intranet.coworking-toulouse.com/api/1.0/location/[LocationSlug]/[LocationHash] -O /dev/null
```
* [LocationSlug] est le slug correspondant au site concerné (toulouse-wilson, toulouse-victor-hugo, montauban...)
* [LocationHash] est une clef spécifique au site permettant d'identifier le site

## ASUS-SPY
Le repository <https://github.com/EtincelleCoworking/asus-spy> est dans /home/pi/asus-spy. Il permet de remonter dans l'intranet les membres présents connectés sur le réseau.

La tâche est éxecutée toutes les 5 minutes via CRON avec la commande suivante:
```
*/5 * * * * root /usr/bin/php /home/pi/asus-spy/bin/console etincelle:list-devices --host 192.168.2.1 --username admin --password [Password] 'http://intranet.coworking-toulouse.com/api/1.0/offix/[LocationSlug]/[LocationHash]'
```
* [Password] est le mot de passe pour accéder au routeur Asus
* [LocationSlug] est le slug correspondant au site concerné (toulouse-wilson, toulouse-victor-hugo, montauban...)
* [LocationHash] est une clef spécifique au site permettant d'identifier le site



*/15 * * * * root wget -q http://intranet.coworking-toulouse.com/api/1.0/location/albi/6zSIIUiesXPGTQST -O /dev/null
*/5 * * * * root /usr/bin/php /home/pi/asus-spy/bin/console etincelle:list-devices --host 192.168.2.1 --username admin --password 3tu5ymog 'http://intranet.coworking-toulouse.com/api/1.0/offix/albi/6zSIIUiesXPGTQST'
