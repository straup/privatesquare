<VirtualHost *:80>

	# ServerName example.com
	# ServerAdmin webmaster@localhost

	DocumentRoot /usr/local/PROJECT/www

	<Directory />
		Options FollowSymLinks
		AllowOverride None
	</Directory>

	<Directory /usr/local/PROJECT/www>
		Options FollowSymLinks Indexes
		# AllowOverride FileInfo Limit
		AllowOverride All
		Order allow,deny
		allow from all
	</Directory>

	ErrorLog ${APACHE_LOG_DIR}/error.log

	# Possible values include: debug, info, notice, warn, error, crit,
	# alert, emerg.
	LogLevel warn

	CustomLog ${APACHE_LOG_DIR}/access.log combined

</VirtualHost>
