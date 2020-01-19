#!/bin/bash

echo "Provisioning development environment for Amghost Panel."
cp /var/www/html/amghost/.dev/vagrant/motd.txt /etc/motd
chmod -x /etc/update-motd.d/10-help-text /etc/update-motd.d/51-cloudguest

apt-get install -y software-properties-common > /dev/null

echo "Add the ondrej/php ppa repository"
add-apt-repository -y ppa:ondrej/php > /dev/null
echo "Add the mariadb repository"
curl -sS https://downloads.mariadb.com/MariaDB/mariadb_repo_setup | bash > /dev/null

apt-get update > /dev/null

echo "Install the dependencies"
export DEBIAN_FRONTEND=noninteractive
# set the mariadb root password because mariadb asks for it
debconf-set-selections <<< 'mariadb-server-5.5 mysql-server/root_password password amghost'
debconf-set-selections <<< 'mariadb-server-5.5 mysql-server/root_password_again password amghost'
# actually install
apt-get install -y php7.2 php7.2-cli php7.2-gd php7.2-mysql php7.2-pdo php7.2-mbstring php7.2-tokenizer php7.2-bcmath php7.2-xml php7.2-fpm php7.2-memcached php7.2-curl php7.2-zip php-xdebug mariadb-server nginx curl tar unzip git memcached > /dev/null

echo "Install composer"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo "Install and run mailhog"
curl -sL -o /usr/bin/mailhog https://github.com/mailhog/MailHog/releases/download/v1.0.0/MailHog_linux_amd64
chmod +x /usr/bin/mailhog
cp /var/www/html/amghost/.dev/vagrant/mailhog.service /etc/systemd/system/
systemctl enable mailhog.service
systemctl start mailhog

echo "Configure xDebug"
cp /var/www/html/amghost/.dev/vagrant/xdebug.ini /etc/php/7.2/mods-available/
systemctl restart php7.2-fpm

echo "Configure nginx"
cp /var/www/html/amghost/.dev/vagrant/amghost.conf /etc/nginx/sites-available/
rm /etc/nginx/sites-available/default
ln -s /etc/nginx/sites-available/amghost.conf /etc/nginx/sites-enabled/amghost.conf
systemctl restart nginx

echo "Setup database"
# Replace default config with custom one to bind mysql to 0.0.0.0 to make it accessible from the host
cp /var/www/html/amghost/.dev/vagrant/mariadb.cnf /etc/mysql/my.cnf
systemctl restart mariadb
mysql -u root -pamghost << SQL
CREATE DATABASE panel;
GRANT ALL PRIVILEGES ON panel.* TO 'amghost'@'%' IDENTIFIED BY 'amghost' WITH GRANT OPTION;
GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'amghost' WITH GRANT OPTION;
FLUSH PRIVILEGES;
SQL

echo "Setup amghost queue worker service"
cp /var/www/html/amghost/.dev/vagrant/pteroq.service /etc/systemd/system/
systemctl enable pteroq.service


echo "Setup panel with base settings"
cp /var/www/html/amghost/.dev/vagrant/.env.vagrant /var/www/html/amghost/.env
cd /var/www/html/amghost
chmod -R 755 storage/* bootstrap/cache
composer install --no-progress
php artisan key:generate --force
php artisan migrate
php artisan db:seed
php artisan p:user:make --name-first Test --name-last Admin --username admin --email testadmin@amghost.io --password Ptero123 --admin 1
php artisan p:user:make --name-first Test --name-last User --username user --email testuser@amghost.io --password Ptero123 --admin 0

echo "Add queue cronjob and start queue worker"
(crontab -l 2>/dev/null; echo "* * * * * php /var/www/html/amghost/artisan schedule:run >> /dev/null 2>&1") | crontab -
systemctl start pteroq

echo "   ----------------"
echo "Provisioning is completed."
echo "The panel should be available at http://localhost:50080/"
echo "You may use the default admin user to login: admin/Ptero123"
echo "A normal user has also been created: user/Ptero123"
echo "MailHog is available at http://localhost:58025/"
echo "Connect to the database using root/amghost or amghost/amghost on localhost:53306"
echo "If you want to access the panel using http://amghost.app you can use the vagrant-dns plugin"
echo "Install it with 'vagrant plugin install vagrant-dns', then run 'vagrant dns --install' once"
echo "On first use you'll have to manually start vagrant-dns with 'vagrant dns --start'"
