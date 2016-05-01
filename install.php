<?php
//Connect to database
$connection = new mysqli('localhost', 'root', '');
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
//Create database
//$connection->query("CREATE DATABASE svada2");
mysqli_query($connection, "CREATE DATABASE svada2") or die(mysqli_error($connection));

require('util.php');

//Create tables
setQuery("CREATE TABLE `chat` (
  `name` varchar(20) NOT NULL,
  `topic` text NOT NULL,
  `image` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1");
setQuery("INSERT INTO `chat` (`name`, `topic`, `image`) VALUES
('', '', 0)");
setQuery("CREATE TABLE `emoticon` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(30) NOT NULL,
  `name` varchar(20) NOT NULL,
  `shortcut` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1");
setQuery("CREATE TABLE `file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(30) NOT NULL,
  `uploader` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=latin1");
setQuery("CREATE TABLE `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `author` int(11) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `edit` int(11) NOT NULL DEFAULT '0',
  `skype` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=latin1");
setQuery("CREATE TABLE `style` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `css` varchar(30) NOT NULL,
  `markup` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1");
setQuery("INSERT INTO `style` (`id`, `name`, `css`, `markup`) VALUES
(1, 'Standard', 'standard.css', 'highlight.xcode.css')");
setQuery("CREATE TABLE `user` (
  `username` varchar(20) NOT NULL,
  `display_name` varchar(30) NOT NULL,
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `status_message` varchar(100) NOT NULL,
  `image` int(11) DEFAULT NULL,
  `is_typing` int(11) NOT NULL DEFAULT '0',
  `language` varchar(20) NOT NULL DEFAULT 'english',
  `mute_sounds` int(11) NOT NULL DEFAULT '0',
  `last_activity` int(11) NOT NULL DEFAULT '0',
  `style` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1");
setQuery("CREATE TABLE IF NOT EXISTS `user_session` (
  `id` int(11) NOT NULL,
  `token` varchar(33) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1");

//Delete this file upon completion
//Insert code here

?>
