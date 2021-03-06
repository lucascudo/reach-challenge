# Introduction

A simple Amazon Web Service S3 bucket listing tool developed for Rea.ch Challenge.
In ordert to use it, make sure that you have PHP and Composer installed on your system, then open the src directory on Terminal and type:
# Setup:
```sh
$ composer install
```
# Run:
```sh
$ ./reach-challenge.php
```
You can also use it with some of the awesome options described below:

-h, --help &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Prints this manual.

-v, --version&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Shows the program`s version.

--group-by-region&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Group the results by AWS regions.

--organize-by-storage&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Organize the results by AWS Storage Classes.

--size-format=[value]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Uses one of the supported size formats (bytes, KB, MB or GB). Ex: ./reach-challenge.php --size-format="GB"

--filter=[value]&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Uses a regular expression to filter the accounted files by its names. Ex (consider only files wich names begins with "UFO"): ./reach-challenge.php --file="/^UFO/"
# Unit tests:
In the tests directory, type:
```sh
$ ../vendor/phpunit/phpunit/phpunit bucket-reader.class.test --colors --debug
```
