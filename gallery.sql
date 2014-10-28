--
-- Table structure for table `gallery_category`
--

CREATE TABLE IF NOT EXISTS `gallery_category` (
`id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `showcase` int(1) NOT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_image`
--

CREATE TABLE IF NOT EXISTS `gallery_image` (
`id` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `file` varchar(255) NOT NULL,
  `thumb` varchar(255) DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `gallery_video`
--

CREATE TABLE IF NOT EXISTS `gallery_video` (
`id` int(11) NOT NULL,
  `category` int(11) NOT NULL,
  `platform` int(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `code` varchar(255) NOT NULL,
  `width` int(4) NOT NULL,
  `height` int(4) NOT NULL,
  `thumb` varchar(255) DEFAULT NULL
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gallery_category`
--
ALTER TABLE `gallery_category`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_image`
--
ALTER TABLE `gallery_image`
 ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_video`
--
ALTER TABLE `gallery_video`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gallery_category`
--
ALTER TABLE `gallery_category`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `gallery_image`
--
ALTER TABLE `gallery_image`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
--
-- AUTO_INCREMENT for table `gallery_video`
--
ALTER TABLE `gallery_video`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;
