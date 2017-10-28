# reach-challenge

A Amazon Web Service S3 bucket listing tool developed for Rea.ch Challenge.
Make sure you have PHP and Composer installed on your system., then open the src directory on Terminal and type:
To install dependencies:
```sh
$ composer install
```
To run the application:
Just type
```sh
./reach-challenge.php
```
to list your buckets or use some of the awesome options described below:
  -h, --help                    Prints this manual.
  -v, --version                 Shows the program`s version.
  --group-by-region             Group the results by AWS regions.
  --organize-by-storage         Organize the results by AWS Storage Classes.
  --size-format=[value]         Uses one of the supported size formats (bytes, KB, MB or GB). Ex: ./reach-challenge.php --size-format="GB"
  --filter=[value]              Uses a regular expression to filter the accounted files by its names. Ex (consider only files wich names begins with "UFO"): ./reach-challenge.php --file="/^UFO/"