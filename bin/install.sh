#!/bin/sh

# install dependencies
curl -s https://getcomposer.org/installer | php
php composer.phar install

# checkout a branch
cd src/system/vendor/kometphp/core
git checkout master

cd ../../../../../
rm -f composer.phar

if [ ! -d "src/public" ]; then
# install Komet assets and bootstrap file
    cd src
    git clone https://github.com/kometphp/assets.git
    mv assets public
    chmod 0755 public
    rm -rf public/.git public/index.html public/.gitignore public/.gitattributes
    cp system/vendor/kometphp/core/index.default.php public/index.php
fi

echo "\033[1;32mKometPHP has been installed successfully\033[m"