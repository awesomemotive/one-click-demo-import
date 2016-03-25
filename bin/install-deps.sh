#!/bin/bash -ex

# Install dependencies through Composer
composer install --prefer-source --no-interaction --no-dev

# install grunt and grunt deps
npm install -g grunt-cli
npm install