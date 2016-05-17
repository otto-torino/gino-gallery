-- phpMyAdmin SQL Dump
-- Application: gallery
--

-- --------------------------------------------------------

--
-- Struttura della tabella `gallery_category`
--

CREATE TABLE IF NOT EXISTS `gallery_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `showcase` tinyint(1) NOT NULL,
  `slideshow` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `gallery_image`
--

CREATE TABLE IF NOT EXISTS `gallery_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `file` varchar(255) NOT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Struttura della tabella `gallery_video`
--

CREATE TABLE IF NOT EXISTS `gallery_video` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` int(11) NOT NULL,
  `platform` tinyint(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `code` varchar(255) NOT NULL,
  `width` smallint(4) NOT NULL,
  `height` smallint(4) NOT NULL,
  `thumb` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

INSERT INTO `auth_permission` (`class`, `code`, `label`, `description`, `admin`) VALUES
('gallery', 'can_admin', 'amministrazione', 'amministrazione completa del modulo', 1);
