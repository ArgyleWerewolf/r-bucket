# R-Bucket

A basic PHP and Javascript implementation of a [Photobucket](https://photobucket.com/)-style image gallery, initially written for my wife's use, now cleaned up and available as my first-ever potentially useful open-source project.


## What It Does

If you're an artist who regularly creates images and need to distribute them to clients, colleagues, or elsewhere on the Internet, R-Bucket (short for "Rabbit Bucket", previously "Tandye Bucket") might be for you! Upload your work in JPG or PNG format to a private, your-eyes-only gallery. The originals are stored on Amazon's S3 service, and smaller thumbnails are generated locally and shown to you in a grid gallery (or sub-galleries that you create), along with public links to the originals. Copy-paste those links into emails, instant messages, on Twitter, or wherever you might need to share your work.


## Getting Started

Here's what you need to get started!

### Prerequisites

* A web server running PHP 5.6.30 (or higher) MySQL 5.0.11 (or higher)
* An Amazon Web Services (AWS) account with an S3 Bucket for storing the images
* `Access Key` and `Secret Key` for an AWS user with permission to connect to the bucket

### Installing

1) Open `credentials.php` and fill out the values for the user's web access, the MySQL database info, and AWS access
2) Save the credentials and then upload (or copy) the whole project to a directory on your web server
3) Visit the public URL for the directory on the server. You should be presented with a log in screen.
4) Log in with the `USER_NAME` and `USER_PASS` you entered in `credentials.php`. You're in business!


## Problems?

This is my first open-source project and I originally wrote it for an audience of one, so there's bound to be bugs. I welcome bug reports or pull requests here on Github.


## Built With

* [Foundation Sites CSS](https://foundation.zurb.com/)
* [Donovan Schönknecht's Amazon S3 PHP class](http://undesigned.org.za/2007/10/22/amazon-s3-php-class)


## License
This project is licensed under the MIT License.


## Thanks!

* the long-lost tutorial on which some of the upload and file-checking code is based. This was written back in 2017 and no amount of Googling can show me where this tutorial came from. If you know, please let me know!
