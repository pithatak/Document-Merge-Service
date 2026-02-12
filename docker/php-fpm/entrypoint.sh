#!/bin/sh
set -e

echo "Clearing cache..."
rm -rf  var/cache/*

echo "Running composer install..."
composer install --no-interaction --prefer-dist

echo "Installing frontend dependencies..."
npm install
npm run dev

echo "Starting application..."
exec "$@"
