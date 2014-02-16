#!/usr/bin/php
<?php
//	var_dump( "testphp" );
$command = "/bin/touch /var/www/html/mail/mailtest.txt";
exec( $command );
