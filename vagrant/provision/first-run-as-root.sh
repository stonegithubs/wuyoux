#!/usr/bin/env bash

#== Import script args ==

timezone=$(echo "$1")

#== Bash helpers ==

function info {
  echo " "
  echo "--> $1"
  echo " "
}

#== Provision script ==

info "Provision-script user: `whoami`"

info "Allocate swap for MySQL 5.6"
fallocate -l 2048M /swapfile
chmod 600 /swapfile
mkswap /swapfile
swapon /swapfile
echo '/swapfile none swap defaults 0 0' >> /etc/fstab

info "Configure locales"
update-locale LC_ALL="C"
dpkg-reconfigure locales

info "Configure timezone"
echo ${timezone} | tee /etc/timezone
#dpkg-reconfigure --frontend noninteractive tzdata

#info "Prepare root password for MySQL"
#debconf-set-selections <<< "mysql-server-5.7 mysql-server/root_password password \"''\""
#debconf-set-selections <<< "mysql-server-5.7 mysql-server/root_password_again password \"''\""
#echo "Done!"


info "Configure MySQL"
sed -i "s/.*bind-address.*/bind-address = 0.0.0.0/" /etc/my.cnf
echo "Done!"

info "Configure PHP-FPM"
sed -i 's/www/vagrant/g' /data/server/php/etc/php-fpm.conf
sed -i 's/listen.owner = www/listen.owner = vagrant/g' /data/server/php/etc/php-fpm.conf
sed -i 's/listen.group = www/listen.group = vagrant/g' /data/server/php/etc/php-fpm.conf
sed -i 's/user = www/user = vagrant/g' /data/server/php/etc/php-fpm.conf
sed -i 's/group = www/group = vagrant/g' /data/server/php/etc/php-fpm.conf
echo "Done!"

info "Configure NGINX"
sed -i 's/user www www/user vagrant vagrant/g' /data/server/nginx/conf/nginx.conf
echo "Done!"

info "Enabling site configuration"
ln -s /app/vagrant/nginx/app.conf /data/server/nginx/conf/vhost/app.conf
echo "Done!"

#info "Initailize databases for MySQL"
#mysql -uroot <<< "CREATE DATABASE yii2advanced"
#mysql -uroot <<< "CREATE DATABASE yii2advanced_test"
#echo "Done!"

#info "Install composer"
#curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer