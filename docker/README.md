# Test/Develop with Docker

## Testing with PHP8.x
The HEAD of master branch is PHP8 ready. 

Move to Dockerfile for php 8.x and build an image `php56_imagick`
```shell
cd docker/php_8.x
docker build -t php8_imagick .
```
Now go back to source root and run a container with memorable name (used `watermark` here).
```shell
cd ../..
docker run -dit --name watermark -v $(pwd):/usr/app php8_imagick
```

Done! You are ready to run any command from that container and of course the tests :)
```shell
docker exec watermark vendor/bin/phpunit -c phpunit.xml.dist
```

## Testing with older version (PHP 5.6 - 7.x)

First, switch to right version of source code
```shell
git checkout tags/0.1.2
```

Move to Dockerfile for php 5.6 and build an image `php56_imagick`
```shell
cd docker/php_5.6
docker build -t php56_imagick .
```
Now go back to source root and run a container with memorable name (used `watermark56` here).
```shell
cd ../..
docker run -dit --name watermark56 -v $(pwd):/usr/app php56_imagick
```

Done! You are ready to run any command from that container and of course the tests :) 
```shell
docker exec watermark56 vendor/bin/phpunit -c phpunit.xml.dist
```

