# -*- mode: ruby -*-
# vi: set ft=ruby :

# All Vagrant configuration is done below. The "2" in Vagrant.configure
# configures the configuration version (we support older styles for
# backwards compatibility). Please don't change it unless you know what
# you're doing.
Vagrant.configure(2) do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://atlas.hashicorp.com/search.
  config.vm.box = "centos-7"

  # Disable automatic box update checking. If you disable this, then
  # boxes will only be checked for updates when the user runs
  # `vagrant box outdated`. This is not recommended.
  # config.vm.box_check_update = false

  # Create a forwarded port mapping which allows access to a specific port
  # within the machine from a port on the host machine. In the example below,
  # accessing "localhost:8080" will access port 80 on the guest machine.
  config.vm.network "forwarded_port", guest: 80, host: 8085
  config.vm.network "forwarded_port", guest: 3306, host: 3306

  # Create a private network, which allows host-only access to the machine
  # using a specific IP.
  # config.vm.network "private_network", ip: "192.168.33.10"

  # Create a public network, which generally matched to bridged network.
  # Bridged networks make the machine appear as another physical device on

  # Provider-specific configuration so you can fine-tune various
  # backing providers for Vagrant. These expose provider-specific options.
  # Example for VirtualBox:
  #
  # config.vm.provider "virtualbox" do |vb|
  #   # Display the VirtualBox GUI when booting the machine
  #   vb.gui = true
  #
  #   # Customize the amount of memory on the VM:
  #   vb.memory = "1024"
  # end
  #
  # View the documentation for the provider you are using for more
  # information on available options.
  # your network.
  # config.vm.network "public_network"

  # Share an additional folder to the guest VM. The first argument is
  # the path on the host to the actual folder. The second argument is
  # the path on the guest to mount the folder. And the optional third
  # argument is a set of non-required options.
  config.vm.synced_folder ".", "/var/www/pdf_sample", type: "nfs"

  # Define a Vagrant Push strategy for pushing to Atlas. Other push strategies
  # such as FTP and Heroku are also available. See the documentation at
  # https://docs.vagrantup.com/v2/push/atlas.html for more information.
  # config.push.define "atlas" do |push|
  #   push.app = "YOUR_ATLAS_USERNAME/YOUR_APPLICATION_NAME"
  # end

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL
    sudo localectl set-locale LANG=en_US.UTF-8
    
    sudo yum update
    sudo rpm -Uvh https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm
    sudo rpm -Uvh https://mirror.webtatic.com/yum/el7/webtatic-release.rpm

    sudo yum install -y http://dev.mysql.com/get/mysql-community-release-el7-5.noarch.rpm

    sudo yum install -y mysql-community-server
    sudo yum install -y httpd
    sudo yum install -y php56w php56w-opcache php56w-fpm php56w-common php56w-intl php56w-mbstring php56w-mysql php56w-pdo php56w-devel php56w-xml php56w-pear
    
    sudo yum install -y ImageMagick*
    sudo printf "\n" | pecl install imagick 
    sudo bash -c "echo '' >> /etc/php.ini"
    sudo bash -c "echo extension=imagick.so >> /etc/php.ini"    

    PROJECT_NAME="pdf_sample"
    DOCUMENT_ROOT="/var/www/${PROJECT_NAME}/public"

    sudo bash -c "echo extension= /var/www/${PROJECT_NAME}/reference/php-560/php_pdflib.so >> /etc/php.ini"

    sudo echo "
    <VirtualHost *:80>
        ServerName ${PROJECT_NAME}.local
        DocumentRoot $DOCUMENT_ROOT
        SetEnv APPLICATION_ENV "development"
        <Directory $DOCUMENT_ROOT >
            DirectoryIndex index.php
            AllowOverride All
            Order allow,deny
            Allow from all
        </Directory>
    </VirtualHost>
    " > /etc/httpd/conf.d/${PROJECT_NAME}.conf

    sudo systemctl start httpd
    sudo systemctl start mysqld
    
    sudo systemctl disable firewalld
    sudo systemctl stop firewalld

    sudo echo "
      GRANT ALL PRIVILEGES ON *.* TO 'root'@'%' IDENTIFIED BY 'root' WITH GRANT OPTION;
      use mysql;
      update user set password=PASSWORD('root') where User='root'; 
      flush privileges" | mysql -uroot 

    cd /var/www/${PROJECT_NAME}    
    sudo php composer.phar install
  SHELL
end
