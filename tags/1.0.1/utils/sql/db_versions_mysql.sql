/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

/*Table structure for table `z_db_versions` */

DROP TABLE IF EXISTS `z_db_versions`;

CREATE TABLE `z_db_versions` (
  `id` smallint(4) unsigned NOT NULL auto_increment,
  `major` tinyint(2) unsigned NOT NULL,
  `minor` tinyint(2) unsigned NOT NULL,
  `point` tinyint(2) unsigned NOT NULL,
  `script_type` tinyint(1) NOT NULL default '0',
  `date_added` datetime NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 CHECKSUM=1 DELAY_KEY_WRITE=1 ROW_FORMAT=DYNAMIC;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
