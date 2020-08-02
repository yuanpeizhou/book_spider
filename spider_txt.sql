/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : spider_txt

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2020-04-28 14:47:46
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for website
-- ----------------------------
DROP TABLE IF EXISTS `website`;
CREATE TABLE `website` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `website` varchar(255) DEFAULT NULL COMMENT '小说网站名称',
  `url` varchar(255) DEFAULT NULL COMMENT '网站网址',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of website
-- ----------------------------

-- ----------------------------
-- Table structure for word
-- ----------------------------
DROP TABLE IF EXISTS `word`;
CREATE TABLE `word` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  `tag` varchar(255) DEFAULT NULL,
  `local_url` varchar(255) DEFAULT NULL,
  `word` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=320 DEFAULT CHARSET=utf8mb4;

-- ----------------------------
-- Records of word
-- ----------------------------
INSERT INTO `word` VALUES ('2', 'https://www.diyibanzhu4.pro/toimg/data', 'a1.png', 'public\\img\\a1.png', '爱', '2020-04-28 01:17:06', null);
INSERT INTO `word` VALUES ('3', 'https://www.diyibanzhu4.pro/toimg/data', 'a11.png', 'public\\img\\a11.png', '爱', '2020-04-28 01:17:17', null);
INSERT INTO `word` VALUES ('4', 'https://www.diyibanzhu4.pro/toimg/data', 'b1.png', 'public\\img\\b1.png', '棒', '2020-04-28 01:28:09', null);
INSERT INTO `word` VALUES ('5', 'https://www.diyibanzhu4.pro/toimg/data', 'b2.png', 'public\\img\\b2.png', '帮', '2020-04-28 01:28:10', null);
INSERT INTO `word` VALUES ('6', 'https://www.diyibanzhu4.pro/toimg/data', 'b3.png', 'public\\img\\b3.png', '暴', '2020-04-28 01:28:11', null);
INSERT INTO `word` VALUES ('7', 'https://www.diyibanzhu4.pro/toimg/data', 'b4.png', 'public\\img\\b4.png', '勃', '2020-04-28 01:28:13', null);
INSERT INTO `word` VALUES ('8', 'https://www.diyibanzhu4.pro/toimg/data', 'b5.png', 'public\\img\\b5.png', '逼', '2020-04-28 01:28:14', null);
INSERT INTO `word` VALUES ('9', 'https://www.diyibanzhu4.pro/toimg/data', 'b6.png', 'public\\img\\b6.png', '勃', '2020-04-28 01:28:15', null);
INSERT INTO `word` VALUES ('10', 'https://www.diyibanzhu4.pro/toimg/data', 'b11.png', 'public\\img\\b11.png', '棒', '2020-04-28 01:28:24', null);
INSERT INTO `word` VALUES ('11', 'https://www.diyibanzhu4.pro/toimg/data', 'b22.png', 'public\\img\\b22.png', '帮', '2020-04-28 01:28:40', null);
INSERT INTO `word` VALUES ('12', 'https://www.diyibanzhu4.pro/toimg/data', 'b33.png', 'public\\img\\b33.png', '暴', '2020-04-28 01:28:59', null);
INSERT INTO `word` VALUES ('13', 'https://www.diyibanzhu4.pro/toimg/data', 'b44.png', 'public\\img\\b44.png', '婊', '2020-04-28 01:29:15', null);
INSERT INTO `word` VALUES ('14', 'https://www.diyibanzhu4.pro/toimg/data', 'b55.png', 'public\\img\\b55.png', '逼', '2020-04-28 01:29:30', null);
INSERT INTO `word` VALUES ('15', 'https://www.diyibanzhu4.pro/toimg/data', 'b66.png', 'public\\img\\b66.png', '勃', '2020-04-28 01:29:48', null);
INSERT INTO `word` VALUES ('16', 'https://www.diyibanzhu4.pro/toimg/data', 'c1.png', 'public\\img\\c1.png', '操', '2020-04-28 01:30:38', null);
INSERT INTO `word` VALUES ('17', 'https://www.diyibanzhu4.pro/toimg/data', 'c2.png', 'public\\img\\c2.png', '插', '2020-04-28 01:30:39', null);
INSERT INTO `word` VALUES ('18', 'https://www.diyibanzhu4.pro/toimg/data', 'c3.png', 'public\\img\\c3.png', '潮', '2020-04-28 01:30:40', null);
INSERT INTO `word` VALUES ('19', 'https://www.diyibanzhu4.pro/toimg/data', 'c4.png', 'public\\img\\c4.png', '处', '2020-04-28 01:30:42', null);
INSERT INTO `word` VALUES ('20', 'https://www.diyibanzhu4.pro/toimg/data', 'c5.png', 'public\\img\\c5.png', '唇', '2020-04-28 01:30:43', null);
INSERT INTO `word` VALUES ('21', 'https://www.diyibanzhu4.pro/toimg/data', 'c11.png', 'public\\img\\c11.png', '操', '2020-04-28 01:30:52', null);
INSERT INTO `word` VALUES ('22', 'https://www.diyibanzhu4.pro/toimg/data', 'c22.png', 'public\\img\\c22.png', '插', '2020-04-28 01:31:11', null);
INSERT INTO `word` VALUES ('23', 'https://www.diyibanzhu4.pro/toimg/data', 'c33.png', 'public\\img\\c33.png', '潮', '2020-04-28 01:31:26', null);
INSERT INTO `word` VALUES ('24', 'https://www.diyibanzhu4.pro/toimg/data', 'c44.png', 'public\\img\\c44.png', '处', '2020-04-28 01:31:41', null);
INSERT INTO `word` VALUES ('25', 'https://www.diyibanzhu4.pro/toimg/data', 'c55.png', 'public\\img\\c55.png', '唇', '2020-04-28 01:32:01', null);
INSERT INTO `word` VALUES ('26', 'https://www.diyibanzhu4.pro/toimg/data', 'd1.png', 'public\\img\\d1.png', '蛋', '2020-04-28 01:33:09', null);
INSERT INTO `word` VALUES ('27', 'https://www.diyibanzhu4.pro/toimg/data', 'd2.png', 'public\\img\\d2.png', '弹', '2020-04-28 01:33:10', null);
INSERT INTO `word` VALUES ('28', 'https://www.diyibanzhu4.pro/toimg/data', 'd3.png', 'public\\img\\d3.png', '荡', '2020-04-28 01:33:12', null);
INSERT INTO `word` VALUES ('29', 'https://www.diyibanzhu4.pro/toimg/data', 'd4.png', 'public\\img\\d4.png', '党', '2020-04-28 01:33:13', null);
INSERT INTO `word` VALUES ('30', 'https://www.diyibanzhu4.pro/toimg/data', 'd5.png', 'public\\img\\d5.png', '弟', '2020-04-28 01:33:14', null);
INSERT INTO `word` VALUES ('31', 'https://www.diyibanzhu4.pro/toimg/data', 'd6.png', 'public\\img\\d6.png', '嫡', '2020-04-28 01:33:17', null);
INSERT INTO `word` VALUES ('32', 'https://www.diyibanzhu4.pro/toimg/data', 'd7.png', 'public\\img\\d7.png', '丁', '2020-04-28 01:33:18', null);
INSERT INTO `word` VALUES ('33', 'https://www.diyibanzhu4.pro/toimg/data', 'd8.png', 'public\\img\\d8.png', '洞', '2020-04-28 01:33:20', null);
INSERT INTO `word` VALUES ('34', 'https://www.diyibanzhu4.pro/toimg/data', 'd9.png', 'public\\img\\d9.png', '杜', '2020-04-28 01:33:21', null);
INSERT INTO `word` VALUES ('35', 'https://www.diyibanzhu4.pro/toimg/data', 'd10.png', 'public\\img\\d10.png', '毒', '2020-04-28 01:33:23', null);
INSERT INTO `word` VALUES ('36', 'https://www.diyibanzhu4.pro/toimg/data', 'd11.png', 'public\\img\\d11.png', '蛋', '2020-04-28 01:33:24', null);
INSERT INTO `word` VALUES ('37', 'https://www.diyibanzhu4.pro/toimg/data', 'd22.png', 'public\\img\\d22.png', '弹', '2020-04-28 01:33:40', null);
INSERT INTO `word` VALUES ('38', 'https://www.diyibanzhu4.pro/toimg/data', 'd33.png', 'public\\img\\d33.png', '荡', '2020-04-28 01:33:55', null);
INSERT INTO `word` VALUES ('39', 'https://www.diyibanzhu4.pro/toimg/data', 'd44.png', 'public\\img\\d44.png', '党', '2020-04-28 01:34:12', null);
INSERT INTO `word` VALUES ('40', 'https://www.diyibanzhu4.pro/toimg/data', 'd55.png', 'public\\img\\d55.png', '弟', '2020-04-28 01:34:28', null);
INSERT INTO `word` VALUES ('41', 'https://www.diyibanzhu4.pro/toimg/data', 'd66.png', 'public\\img\\d66.png', '嫡', '2020-04-28 01:34:46', null);
INSERT INTO `word` VALUES ('42', 'https://www.diyibanzhu4.pro/toimg/data', 'd77.png', 'public\\img\\d77.png', '丁', '2020-04-28 01:35:02', null);
INSERT INTO `word` VALUES ('43', 'https://www.diyibanzhu4.pro/toimg/data', 'd88.png', 'public\\img\\d88.png', '洞', '2020-04-28 01:35:18', null);
INSERT INTO `word` VALUES ('44', 'https://www.diyibanzhu4.pro/toimg/data', 'd99.png', 'public\\img\\d99.png', '杜', '2020-04-28 01:35:35', null);
INSERT INTO `word` VALUES ('45', 'https://www.diyibanzhu4.pro/toimg/data', 'd100.png', 'public\\img\\d100.png', '毒', '2020-04-28 02:27:02', null);
INSERT INTO `word` VALUES ('46', 'https://www.diyibanzhu4.pro/toimg/data', 'f1.png', 'public\\img\\f1.png', '粉', '2020-04-28 02:39:38', null);
INSERT INTO `word` VALUES ('47', 'https://www.diyibanzhu4.pro/toimg/data', 'f2.png', 'public\\img\\f2.png', '缝', '2020-04-28 02:39:39', null);
INSERT INTO `word` VALUES ('48', 'https://www.diyibanzhu4.pro/toimg/data', 'f3.png', 'public\\img\\f3.png', '腐', '2020-04-28 02:39:41', null);
INSERT INTO `word` VALUES ('49', 'https://www.diyibanzhu4.pro/toimg/data', 'f4.png', 'public\\img\\f4.png', '妇', '2020-04-28 02:39:42', null);
INSERT INTO `word` VALUES ('50', 'https://www.diyibanzhu4.pro/toimg/data', 'g1.png', 'public\\img\\g1.png', '干', '2020-04-28 02:39:50', null);
INSERT INTO `word` VALUES ('51', 'https://www.diyibanzhu4.pro/toimg/data', 'g2.png', 'public\\img\\g2.png', '肛', '2020-04-28 02:39:52', null);
INSERT INTO `word` VALUES ('52', 'https://www.diyibanzhu4.pro/toimg/data', 'g3.png', 'public\\img\\g3.png', '搞', '2020-04-28 02:39:53', null);
INSERT INTO `word` VALUES ('53', 'https://www.diyibanzhu4.pro/toimg/data', 'g4.png', 'public\\img\\g4.png', '高', '2020-04-28 02:39:54', null);
INSERT INTO `word` VALUES ('54', 'https://www.diyibanzhu4.pro/toimg/data', 'f11.png', 'public\\img\\f11.png', '粉', '2020-04-28 02:39:55', null);
INSERT INTO `word` VALUES ('55', 'https://www.diyibanzhu4.pro/toimg/data', 'g5.png', 'public\\img\\g5.png', '宫', '2020-04-28 02:39:57', null);
INSERT INTO `word` VALUES ('56', 'https://www.diyibanzhu4.pro/toimg/data', 'g6.png', 'public\\img\\g6.png', '共', '2020-04-28 02:39:58', null);
INSERT INTO `word` VALUES ('57', 'https://www.diyibanzhu4.pro/toimg/data', 'g7.png', 'public\\img\\g7.png', '狗', '2020-04-28 02:39:59', null);
INSERT INTO `word` VALUES ('58', 'https://www.diyibanzhu4.pro/toimg/data', 'g8.png', 'public\\img\\g8.png', '龟', '2020-04-28 02:40:00', null);
INSERT INTO `word` VALUES ('59', 'https://www.diyibanzhu4.pro/toimg/data', 'g9.png', 'public\\img\\g9.png', '棍', '2020-04-28 02:40:01', null);
INSERT INTO `word` VALUES ('60', 'https://www.diyibanzhu4.pro/toimg/data', 'g10.png', 'public\\img\\g10.png', '国', '2020-04-28 02:40:02', null);
INSERT INTO `word` VALUES ('61', 'https://www.diyibanzhu4.pro/toimg/data', 'g11.png', 'public\\img\\g11.png', '干', '2020-04-28 02:40:03', null);
INSERT INTO `word` VALUES ('62', 'https://www.diyibanzhu4.pro/toimg/data', 'h1.png', 'public\\img\\h1.png', '含', '2020-04-28 02:40:04', null);
INSERT INTO `word` VALUES ('63', 'https://www.diyibanzhu4.pro/toimg/data', 'h2.png', 'public\\img\\h2.png', '胡', '2020-04-28 02:40:05', null);
INSERT INTO `word` VALUES ('64', 'https://www.diyibanzhu4.pro/toimg/data', 'h3.png', 'public\\img\\h3.png', '秽', '2020-04-28 02:40:06', null);
INSERT INTO `word` VALUES ('65', 'https://www.diyibanzhu4.pro/toimg/data', 'f22.png', 'public\\img\\f22.png', '粉', '2020-04-28 02:40:13', null);
INSERT INTO `word` VALUES ('66', 'https://www.diyibanzhu4.pro/toimg/data', 'h11.png', 'public\\img\\h11.png', '含', '2020-04-28 02:40:17', null);
INSERT INTO `word` VALUES ('67', 'https://www.diyibanzhu4.pro/toimg/data', 'g22.png', 'public\\img\\g22.png', '肛', '2020-04-28 02:40:19', null);
INSERT INTO `word` VALUES ('68', 'https://www.diyibanzhu4.pro/toimg/data', 'f33.png', 'public\\img\\f33.png', '腐', '2020-04-28 02:40:33', null);
INSERT INTO `word` VALUES ('69', 'https://www.diyibanzhu4.pro/toimg/data', 'j1.png', 'public\\img\\j1.png', '鸡', '2020-04-28 02:40:35', null);
INSERT INTO `word` VALUES ('70', 'https://www.diyibanzhu4.pro/toimg/data', 'j2.png', 'public\\img\\j2.png', '纪', '2020-04-28 02:40:36', null);
INSERT INTO `word` VALUES ('71', 'https://www.diyibanzhu4.pro/toimg/data', 'h22.png', 'public\\img\\h22.png', '胡', '2020-04-28 02:40:37', null);
INSERT INTO `word` VALUES ('72', 'https://www.diyibanzhu4.pro/toimg/data', 'j3.png', 'public\\img\\j3.png', '妓', '2020-04-28 02:40:37', null);
INSERT INTO `word` VALUES ('73', 'https://www.diyibanzhu4.pro/toimg/data', 'j4.png', 'public\\img\\j4.png', '贱', '2020-04-28 02:40:38', null);
INSERT INTO `word` VALUES ('74', 'https://www.diyibanzhu4.pro/toimg/data', 'g33.png', 'public\\img\\g33.png', '搞', '2020-04-28 02:40:40', null);
INSERT INTO `word` VALUES ('75', 'https://www.diyibanzhu4.pro/toimg/data', 'j5.png', 'public\\img\\j5.png', '奸', '2020-04-28 02:40:40', null);
INSERT INTO `word` VALUES ('76', 'https://www.diyibanzhu4.pro/toimg/data', 'j6.png', 'public\\img\\j6.png', '交', '2020-04-28 02:40:41', null);
INSERT INTO `word` VALUES ('77', 'https://www.diyibanzhu4.pro/toimg/data', 'j7.png', 'public\\img\\j7.png', '介', '2020-04-28 02:40:42', null);
INSERT INTO `word` VALUES ('78', 'https://www.diyibanzhu4.pro/toimg/data', 'j8.png', 'public\\img\\j8.png', '挤', '2020-04-28 02:40:43', null);
INSERT INTO `word` VALUES ('79', 'https://www.diyibanzhu4.pro/toimg/data', 'j9.png', 'public\\img\\j9.png', '精', '2020-04-28 02:40:44', null);
INSERT INTO `word` VALUES ('80', 'https://www.diyibanzhu4.pro/toimg/data', 'j10.png', 'public\\img\\j10.png', '茎', '2020-04-28 02:40:45', null);
INSERT INTO `word` VALUES ('81', 'https://www.diyibanzhu4.pro/toimg/data', 'j11.png', 'public\\img\\j11.png', '锦', '2020-04-28 02:40:46', null);
INSERT INTO `word` VALUES ('82', 'https://www.diyibanzhu4.pro/toimg/data', 'j12.png', 'public\\img\\j12.png', '九', '2020-04-28 02:40:47', null);
INSERT INTO `word` VALUES ('83', 'https://www.diyibanzhu4.pro/toimg/data', 'f44.png', 'public\\img\\f44.png', '妇', '2020-04-28 02:40:48', null);
INSERT INTO `word` VALUES ('84', 'https://www.diyibanzhu4.pro/toimg/data', 'j13.png', 'public\\img\\j13.png', '厥', '2020-04-28 02:40:50', null);
INSERT INTO `word` VALUES ('85', 'https://www.diyibanzhu4.pro/toimg/data', 'j14.png', 'public\\img\\j14.png', '菊', '2020-04-28 02:40:52', null);
INSERT INTO `word` VALUES ('86', 'https://www.diyibanzhu4.pro/toimg/data', 'h33.png', 'public\\img\\h33.png', '秽', '2020-04-28 02:40:53', null);
INSERT INTO `word` VALUES ('87', 'https://www.diyibanzhu4.pro/toimg/data', 'k1.png', 'public\\img\\k1.png', '坑', '2020-04-28 02:40:53', null);
INSERT INTO `word` VALUES ('88', 'https://www.diyibanzhu4.pro/toimg/data', 'j15.png', 'public\\img\\j15.png', '具', '2020-04-28 02:40:56', null);
INSERT INTO `word` VALUES ('89', 'https://www.diyibanzhu4.pro/toimg/data', 'g44.png', 'public\\img\\g44.png', '高', '2020-04-28 02:40:59', null);
INSERT INTO `word` VALUES ('90', 'https://www.diyibanzhu4.pro/toimg/data', 'k11.png', 'public\\img\\k11.png', '坑', '2020-04-28 02:41:06', null);
INSERT INTO `word` VALUES ('91', 'https://www.diyibanzhu4.pro/toimg/data', 'j22.png', 'public\\img\\j22.png', '纪', '2020-04-28 02:41:07', null);
INSERT INTO `word` VALUES ('92', 'https://www.diyibanzhu4.pro/toimg/data', 'l1.png', 'public\\img\\l1.png', '凌', '2020-04-28 02:41:10', null);
INSERT INTO `word` VALUES ('93', 'https://www.diyibanzhu4.pro/toimg/data', 'l2.png', 'public\\img\\l2.png', '流', '2020-04-28 02:41:12', null);
INSERT INTO `word` VALUES ('94', 'https://www.diyibanzhu4.pro/toimg/data', 'l3.png', 'public\\img\\l3.png', '漏', '2020-04-28 02:41:13', null);
INSERT INTO `word` VALUES ('95', 'https://www.diyibanzhu4.pro/toimg/data', 'l4.png', 'public\\img\\l4.png', '撸', '2020-04-28 02:41:14', null);
INSERT INTO `word` VALUES ('96', 'https://www.diyibanzhu4.pro/toimg/data', 'l5.png', 'public\\img\\l5.png', '颅', '2020-04-28 02:41:15', null);
INSERT INTO `word` VALUES ('97', 'https://www.diyibanzhu4.pro/toimg/data', 'l6.png', 'public\\img\\l6.png', '乱', '2020-04-28 02:41:17', null);
INSERT INTO `word` VALUES ('98', 'https://www.diyibanzhu4.pro/toimg/data', 'l7.png', 'public\\img\\l7.png', '露', '2020-04-28 02:41:18', null);
INSERT INTO `word` VALUES ('99', 'https://www.diyibanzhu4.pro/toimg/data', 'g55.png', 'public\\img\\g55.png', '宫', '2020-04-28 02:41:19', null);
INSERT INTO `word` VALUES ('100', 'https://www.diyibanzhu4.pro/toimg/data', 'l8.png', 'public\\img\\l8.png', '轮', '2020-04-28 02:41:20', null);
INSERT INTO `word` VALUES ('101', 'https://www.diyibanzhu4.pro/toimg/data', 'l9.png', 'public\\img\\l9.png', '伦', '2020-04-28 02:41:21', null);
INSERT INTO `word` VALUES ('102', 'https://www.diyibanzhu4.pro/toimg/data', 'l10.png', 'public\\img\\l10.png', '裸', '2020-04-28 02:41:26', null);
INSERT INTO `word` VALUES ('103', 'https://www.diyibanzhu4.pro/toimg/data', 'l11.png', 'public\\img\\l11.png', '凌', '2020-04-28 02:41:27', null);
INSERT INTO `word` VALUES ('104', 'https://www.diyibanzhu4.pro/toimg/data', 'j33.png', 'public\\img\\j33.png', '妓', '2020-04-28 02:41:27', null);
INSERT INTO `word` VALUES ('105', 'https://www.diyibanzhu4.pro/toimg/data', 'g66.png', 'public\\img\\g66.png', '共', '2020-04-28 02:41:34', null);
INSERT INTO `word` VALUES ('106', 'https://www.diyibanzhu4.pro/toimg/data', 'l22.png', 'public\\img\\l22.png', '流', '2020-04-28 02:41:45', null);
INSERT INTO `word` VALUES ('107', 'https://www.diyibanzhu4.pro/toimg/data', 'j44.png', 'public\\img\\j44.png', '贱', '2020-04-28 02:41:46', null);
INSERT INTO `word` VALUES ('108', 'https://www.diyibanzhu4.pro/toimg/data', 'g77.png', 'public\\img\\g77.png', '狗', '2020-04-28 02:41:49', null);
INSERT INTO `word` VALUES ('109', 'https://www.diyibanzhu4.pro/toimg/data', 'j55.png', 'public\\img\\j55.png', '奸', '2020-04-28 02:42:01', null);
INSERT INTO `word` VALUES ('110', 'https://www.diyibanzhu4.pro/toimg/data', 'l33.png', 'public\\img\\l33.png', '漏', '2020-04-28 02:42:01', null);
INSERT INTO `word` VALUES ('111', 'https://www.diyibanzhu4.pro/toimg/data', 'g88.png', 'public\\img\\g88.png', '龟', '2020-04-28 02:42:07', null);
INSERT INTO `word` VALUES ('112', 'https://www.diyibanzhu4.pro/toimg/data', 'j66.png', 'public\\img\\j66.png', '交', '2020-04-28 02:42:19', null);
INSERT INTO `word` VALUES ('113', 'https://www.diyibanzhu4.pro/toimg/data', 'l44.png', 'public\\img\\l44.png', '撸', '2020-04-28 02:42:21', null);
INSERT INTO `word` VALUES ('114', 'https://www.diyibanzhu4.pro/toimg/data', 'g99.png', 'public\\img\\g99.png', '棍', '2020-04-28 02:42:22', null);
INSERT INTO `word` VALUES ('115', 'https://www.diyibanzhu4.pro/toimg/data', 'g100.png', 'public\\img\\g100.png', '国', '2020-04-28 02:42:23', null);
INSERT INTO `word` VALUES ('116', 'https://www.diyibanzhu4.pro/toimg/data', 'l55.png', 'public\\img\\l55.png', '颅', '2020-04-28 02:42:40', null);
INSERT INTO `word` VALUES ('117', 'https://www.diyibanzhu4.pro/toimg/data', 'j77.png', 'public\\img\\j77.png', '介', '2020-04-28 02:42:44', null);
INSERT INTO `word` VALUES ('118', 'https://www.diyibanzhu4.pro/toimg/data', 'j88.png', 'public\\img\\j88.png', '挤', '2020-04-28 02:43:00', null);
INSERT INTO `word` VALUES ('119', 'https://www.diyibanzhu4.pro/toimg/data', 'l66.png', 'public\\img\\l66.png', '乱', '2020-04-28 02:43:00', null);
INSERT INTO `word` VALUES ('120', 'https://www.diyibanzhu4.pro/toimg/data', 'j99.png', 'public\\img\\j99.png', '精', '2020-04-28 02:43:16', null);
INSERT INTO `word` VALUES ('121', 'https://www.diyibanzhu4.pro/toimg/data', 'j100.png', 'public\\img\\j100.png', '茎', '2020-04-28 02:43:18', null);
INSERT INTO `word` VALUES ('122', 'https://www.diyibanzhu4.pro/toimg/data', 'l77.png', 'public\\img\\l77.png', '露', '2020-04-28 02:43:25', null);
INSERT INTO `word` VALUES ('123', 'https://www.diyibanzhu4.pro/toimg/data', 'j111.png', 'public\\img\\j111.png', '锦', '2020-04-28 02:43:36', null);
INSERT INTO `word` VALUES ('124', 'https://www.diyibanzhu4.pro/toimg/data', 'l88.png', 'public\\img\\l88.png', '轮', '2020-04-28 02:43:41', null);
INSERT INTO `word` VALUES ('125', 'https://www.diyibanzhu4.pro/toimg/data', 'j122.png', 'public\\img\\j122.png', '九', '2020-04-28 02:43:54', null);
INSERT INTO `word` VALUES ('126', 'https://www.diyibanzhu4.pro/toimg/data', 'l99.png', 'public\\img\\l99.png', '伦', '2020-04-28 02:44:01', null);
INSERT INTO `word` VALUES ('127', 'https://www.diyibanzhu4.pro/toimg/data', 'l100.png', 'public\\img\\l100.png', '裸', '2020-04-28 02:44:05', null);
INSERT INTO `word` VALUES ('128', 'https://www.diyibanzhu4.pro/toimg/data', 'j133.png', 'public\\img\\j133.png', '厥', '2020-04-28 02:44:13', null);
INSERT INTO `word` VALUES ('129', 'https://www.diyibanzhu4.pro/toimg/data', 'j144.png', 'public\\img\\j144.png', '菊', '2020-04-28 02:44:32', null);
INSERT INTO `word` VALUES ('130', 'https://www.diyibanzhu4.pro/toimg/data', 'j155.png', 'public\\img\\j155.png', '具', '2020-04-28 02:44:50', null);
INSERT INTO `word` VALUES ('131', 'https://www.diyibanzhu4.pro/toimg/data', 'm1.png', 'public\\img\\m1.png', '马', '2020-04-28 02:57:42', null);
INSERT INTO `word` VALUES ('132', 'https://www.diyibanzhu4.pro/toimg/data', 'm2.png', 'public\\img\\m2.png', '妈', '2020-04-28 02:57:43', null);
INSERT INTO `word` VALUES ('133', 'https://www.diyibanzhu4.pro/toimg/data', 'm3.png', 'public\\img\\m3.png', '麻', '2020-04-28 02:57:44', null);
INSERT INTO `word` VALUES ('134', 'https://www.diyibanzhu4.pro/toimg/data', 'm4.png', 'public\\img\\m4.png', '氓', '2020-04-28 02:57:46', null);
INSERT INTO `word` VALUES ('135', 'https://www.diyibanzhu4.pro/toimg/data', 'm5.png', 'public\\img\\m5.png', '美', '2020-04-28 02:57:48', null);
INSERT INTO `word` VALUES ('136', 'https://www.diyibanzhu4.pro/toimg/data', 'm6.png', 'public\\img\\m6.png', '蜜', '2020-04-28 02:57:49', null);
INSERT INTO `word` VALUES ('137', 'https://www.diyibanzhu4.pro/toimg/data', 'm7.png', 'public\\img\\m7.png', '灭', '2020-04-28 02:57:51', null);
INSERT INTO `word` VALUES ('138', 'https://www.diyibanzhu4.pro/toimg/data', 'n1.png', 'public\\img\\n1.png', '奶', '2020-04-28 02:57:52', null);
INSERT INTO `word` VALUES ('139', 'https://www.diyibanzhu4.pro/toimg/data', 'm8.png', 'public\\img\\m8.png', '咪', '2020-04-28 02:57:52', null);
INSERT INTO `word` VALUES ('140', 'https://www.diyibanzhu4.pro/toimg/data', 'n2.png', 'public\\img\\n2.png', '内', '2020-04-28 02:57:53', null);
INSERT INTO `word` VALUES ('141', 'https://www.diyibanzhu4.pro/toimg/data', 'm9.png', 'public\\img\\m9.png', '母', '2020-04-28 02:57:54', null);
INSERT INTO `word` VALUES ('142', 'https://www.diyibanzhu4.pro/toimg/data', 'n3.png', 'public\\img\\n3.png', '嫩', '2020-04-28 02:57:55', null);
INSERT INTO `word` VALUES ('143', 'https://www.diyibanzhu4.pro/toimg/data', 'm11.png', 'public\\img\\m11.png', '马', '2020-04-28 02:57:56', null);
INSERT INTO `word` VALUES ('144', 'https://www.diyibanzhu4.pro/toimg/data', 'n4.png', 'public\\img\\n4.png', '尿', '2020-04-28 02:57:57', null);
INSERT INTO `word` VALUES ('145', 'https://www.diyibanzhu4.pro/toimg/data', 'n5.png', 'public\\img\\n5.png', '虐', '2020-04-28 02:57:58', null);
INSERT INTO `word` VALUES ('146', 'https://www.diyibanzhu4.pro/toimg/data', 'n6.png', 'public\\img\\n6.png', '奴', '2020-04-28 02:58:00', null);
INSERT INTO `word` VALUES ('147', 'https://www.diyibanzhu4.pro/toimg/data', 'p1.png', 'public\\img\\p1.png', '剖', '2020-04-28 02:58:06', null);
INSERT INTO `word` VALUES ('148', 'https://www.diyibanzhu4.pro/toimg/data', 'n11.png', 'public\\img\\n11.png', '奶', '2020-04-28 02:58:08', null);
INSERT INTO `word` VALUES ('149', 'https://www.diyibanzhu4.pro/toimg/data', 'p2.png', 'public\\img\\p2.png', '炮', '2020-04-28 02:58:08', null);
INSERT INTO `word` VALUES ('150', 'https://www.diyibanzhu4.pro/toimg/data', 'p3.png', 'public\\img\\p3.png', '鹏', '2020-04-28 02:58:11', null);
INSERT INTO `word` VALUES ('151', 'https://www.diyibanzhu4.pro/toimg/data', 'p4.png', 'public\\img\\p4.png', '屁', '2020-04-28 02:58:13', null);
INSERT INTO `word` VALUES ('152', 'https://www.diyibanzhu4.pro/toimg/data', 'm22.png', 'public\\img\\m22.png', '妈', '2020-04-28 02:58:17', null);
INSERT INTO `word` VALUES ('153', 'https://www.diyibanzhu4.pro/toimg/data', 'q1.png', 'public\\img\\q1.png', '枪', '2020-04-28 02:58:19', null);
INSERT INTO `word` VALUES ('154', 'https://www.diyibanzhu4.pro/toimg/data', 'q2.png', 'public\\img\\q2.png', '情', '2020-04-28 02:58:20', null);
INSERT INTO `word` VALUES ('155', 'https://www.diyibanzhu4.pro/toimg/data', 'q3.png', 'public\\img\\q3.png', '亲', '2020-04-28 02:58:22', null);
INSERT INTO `word` VALUES ('156', 'https://www.diyibanzhu4.pro/toimg/data', 'p11.png', 'public\\img\\p11.png', '剖', '2020-04-28 02:58:22', null);
INSERT INTO `word` VALUES ('157', 'https://www.diyibanzhu4.pro/toimg/data', 'n22.png', 'public\\img\\n22.png', '内', '2020-04-28 02:58:25', null);
INSERT INTO `word` VALUES ('158', 'https://www.diyibanzhu4.pro/toimg/data', 'm33.png', 'public\\img\\m33.png', '麻', '2020-04-28 02:58:32', null);
INSERT INTO `word` VALUES ('159', 'https://www.diyibanzhu4.pro/toimg/data', 'q11.png', 'public\\img\\q11.png', '枪', '2020-04-28 02:58:34', null);
INSERT INTO `word` VALUES ('160', 'https://www.diyibanzhu4.pro/toimg/data', 'p22.png', 'public\\img\\p22.png', '炮', '2020-04-28 02:58:39', null);
INSERT INTO `word` VALUES ('161', 'https://www.diyibanzhu4.pro/toimg/data', 'r1.png', 'public\\img\\r1.png', '日', '2020-04-28 02:58:42', null);
INSERT INTO `word` VALUES ('162', 'https://www.diyibanzhu4.pro/toimg/data', 'n33.png', 'public\\img\\n33.png', '嫩', '2020-04-28 02:58:43', null);
INSERT INTO `word` VALUES ('163', 'https://www.diyibanzhu4.pro/toimg/data', 'r2.png', 'public\\img\\r2.png', '肉', '2020-04-28 02:58:44', null);
INSERT INTO `word` VALUES ('164', 'https://www.diyibanzhu4.pro/toimg/data', 'r3.png', 'public\\img\\r3.png', '辱', '2020-04-28 02:58:45', null);
INSERT INTO `word` VALUES ('165', 'https://www.diyibanzhu4.pro/toimg/data', 'r4.png', 'public\\img\\r4.png', '乳', '2020-04-28 02:58:46', null);
INSERT INTO `word` VALUES ('166', 'https://www.diyibanzhu4.pro/toimg/data', 'm44.png', 'public\\img\\m44.png', '氓', '2020-04-28 02:58:48', null);
INSERT INTO `word` VALUES ('167', 'https://www.diyibanzhu4.pro/toimg/data', 'q22.png', 'public\\img\\q22.png', '情', '2020-04-28 02:58:49', null);
INSERT INTO `word` VALUES ('168', 'https://www.diyibanzhu4.pro/toimg/data', 'r11.png', 'public\\img\\r11.png', '日', '2020-04-28 02:58:56', null);
INSERT INTO `word` VALUES ('169', 'https://www.diyibanzhu4.pro/toimg/data', 'p33.png', 'public\\img\\p33.png', '鹏', '2020-04-28 02:58:57', null);
INSERT INTO `word` VALUES ('170', 'https://www.diyibanzhu4.pro/toimg/data', 'q33.png', 'public\\img\\q33.png', '亲', '2020-04-28 02:59:05', null);
INSERT INTO `word` VALUES ('171', 'https://www.diyibanzhu4.pro/toimg/data', 'n44.png', 'public\\img\\n44.png', '尿', '2020-04-28 02:59:05', null);
INSERT INTO `word` VALUES ('172', 'https://www.diyibanzhu4.pro/toimg/data', 'm55.png', 'public\\img\\m55.png', '美', '2020-04-28 02:59:10', null);
INSERT INTO `word` VALUES ('173', 'https://www.diyibanzhu4.pro/toimg/data', 'r22.png', 'public\\img\\r22.png', '肉', '2020-04-28 02:59:10', null);
INSERT INTO `word` VALUES ('174', 'https://www.diyibanzhu4.pro/toimg/data', 'p44.png', 'public\\img\\p44.png', '屁', '2020-04-28 02:59:15', null);
INSERT INTO `word` VALUES ('175', 'https://www.diyibanzhu4.pro/toimg/data', 'n55.png', 'public\\img\\n55.png', '虐', '2020-04-28 02:59:21', null);
INSERT INTO `word` VALUES ('176', 'https://www.diyibanzhu4.pro/toimg/data', 'r33.png', 'public\\img\\r33.png', '辱', '2020-04-28 02:59:26', null);
INSERT INTO `word` VALUES ('177', 'https://www.diyibanzhu4.pro/toimg/data', 'm66.png', 'public\\img\\m66.png', '蜜', '2020-04-28 02:59:28', null);
INSERT INTO `word` VALUES ('178', 'https://www.diyibanzhu4.pro/toimg/data', 'n66.png', 'public\\img\\n66.png', '奴', '2020-04-28 02:59:38', null);
INSERT INTO `word` VALUES ('179', 'https://www.diyibanzhu4.pro/toimg/data', 'r44.png', 'public\\img\\r44.png', '乳', '2020-04-28 02:59:43', null);
INSERT INTO `word` VALUES ('180', 'https://www.diyibanzhu4.pro/toimg/data', 'm77.png', 'public\\img\\m77.png', '灭', '2020-04-28 02:59:44', null);
INSERT INTO `word` VALUES ('181', 'https://www.diyibanzhu4.pro/toimg/data', 'm88.png', 'public\\img\\m88.png', '咪', '2020-04-28 03:00:01', null);
INSERT INTO `word` VALUES ('182', 'https://www.diyibanzhu4.pro/toimg/data', 'm99.png', 'public\\img\\m99.png', '母', '2020-04-28 03:00:16', null);
INSERT INTO `word` VALUES ('183', 'https://www.diyibanzhu4.pro/toimg/data', 'm115.png', 'public\\img\\m115.png', null, '2020-04-28 03:00:56', null);
INSERT INTO `word` VALUES ('184', 'https://www.diyibanzhu4.pro/toimg/data', 'p120.png', 'public\\img\\p120.png', null, '2020-04-28 03:01:34', null);
INSERT INTO `word` VALUES ('185', 'https://www.diyibanzhu4.pro/toimg/data', 'q134.png', 'public\\img\\q134.png', null, '2020-04-28 03:02:00', null);
INSERT INTO `word` VALUES ('186', 'https://www.diyibanzhu4.pro/toimg/data', 'm188.png', 'public\\img\\m188.png', null, '2020-04-28 03:03:05', null);
INSERT INTO `word` VALUES ('187', 'https://www.diyibanzhu4.pro/toimg/data', 'q175.png', 'public\\img\\q175.png', null, '2020-04-28 03:03:19', null);
INSERT INTO `word` VALUES ('188', 'https://www.diyibanzhu4.pro/toimg/data', 'o192.png', 'public\\img\\o192.png', null, '2020-04-28 03:03:21', null);
INSERT INTO `word` VALUES ('189', 'https://www.diyibanzhu4.pro/toimg/data', 'p191.png', 'public\\img\\p191.png', null, '2020-04-28 03:03:40', null);
INSERT INTO `word` VALUES ('190', 'https://www.diyibanzhu4.pro/toimg/data', 'o196.png', 'public\\img\\o196.png', null, '2020-04-28 03:03:41', null);
INSERT INTO `word` VALUES ('191', 'https://www.diyibanzhu4.pro/toimg/data', 'q195.png', 'public\\img\\q195.png', null, '2020-04-28 03:04:09', null);
INSERT INTO `word` VALUES ('192', 'https://www.diyibanzhu4.pro/toimg/data', 'n240.png', 'public\\img\\n240.png', null, '2020-04-28 03:04:31', null);
INSERT INTO `word` VALUES ('193', 'https://www.diyibanzhu4.pro/toimg/data', 'm252.png', 'public\\img\\m252.png', null, '2020-04-28 03:04:58', null);
INSERT INTO `word` VALUES ('194', 'https://www.diyibanzhu4.pro/toimg/data', 'p240.png', 'public\\img\\p240.png', null, '2020-04-28 03:05:13', null);
INSERT INTO `word` VALUES ('195', 'https://www.diyibanzhu4.pro/toimg/data', 'n257.png', 'public\\img\\n257.png', null, '2020-04-28 03:05:18', null);
INSERT INTO `word` VALUES ('196', 'https://www.diyibanzhu4.pro/toimg/data', 'o271.png', 'public\\img\\o271.png', null, '2020-04-28 03:05:51', null);
INSERT INTO `word` VALUES ('197', 'https://www.diyibanzhu4.pro/toimg/data', 'n310.png', 'public\\img\\n310.png', null, '2020-04-28 03:06:57', null);
INSERT INTO `word` VALUES ('198', 'https://www.diyibanzhu4.pro/toimg/data', 'o305.png', 'public\\img\\o305.png', null, '2020-04-28 03:07:02', null);
INSERT INTO `word` VALUES ('199', 'https://www.diyibanzhu4.pro/toimg/data', 'p340.png', 'public\\img\\p340.png', null, '2020-04-28 03:08:01', null);
INSERT INTO `word` VALUES ('200', 'https://www.diyibanzhu4.pro/toimg/data', 'p344.png', 'public\\img\\p344.png', null, '2020-04-28 03:08:22', null);
INSERT INTO `word` VALUES ('201', 'https://www.diyibanzhu4.pro/toimg/data', 'm377.png', 'public\\img\\m377.png', null, '2020-04-28 03:08:29', null);
INSERT INTO `word` VALUES ('202', 'https://www.diyibanzhu4.pro/toimg/data', 'n369.png', 'public\\img\\n369.png', null, '2020-04-28 03:08:44', null);
INSERT INTO `word` VALUES ('203', 'https://www.diyibanzhu4.pro/toimg/data', 'q377.png', 'public\\img\\q377.png', null, '2020-04-28 03:09:10', null);
INSERT INTO `word` VALUES ('204', 'https://www.diyibanzhu4.pro/toimg/data', 'p372.png', 'public\\img\\p372.png', null, '2020-04-28 03:09:19', null);
INSERT INTO `word` VALUES ('205', 'https://www.diyibanzhu4.pro/toimg/data', 'o395.png', 'public\\img\\o395.png', null, '2020-04-28 03:09:40', null);
INSERT INTO `word` VALUES ('206', 'https://www.diyibanzhu4.pro/toimg/data', 'o402.png', 'public\\img\\o402.png', null, '2020-04-28 03:10:09', null);
INSERT INTO `word` VALUES ('207', 'https://www.diyibanzhu4.pro/toimg/data', 'r426.png', 'public\\img\\r426.png', null, '2020-04-28 03:10:18', null);
INSERT INTO `word` VALUES ('208', 'https://www.diyibanzhu4.pro/toimg/data', 'r442.png', 'public\\img\\r442.png', null, '2020-04-28 03:10:59', null);
INSERT INTO `word` VALUES ('209', 'https://www.diyibanzhu4.pro/toimg/data', 'm466.png', 'public\\img\\m466.png', null, '2020-04-28 03:11:09', null);
INSERT INTO `word` VALUES ('210', 'https://www.diyibanzhu4.pro/toimg/data', 'n454.png', 'public\\img\\n454.png', null, '2020-04-28 03:11:10', null);
INSERT INTO `word` VALUES ('211', 'https://www.diyibanzhu4.pro/toimg/data', 'o434.png', 'public\\img\\o434.png', null, '2020-04-28 03:11:14', null);
INSERT INTO `word` VALUES ('212', 'https://www.diyibanzhu4.pro/toimg/data', 'q489.png', 'public\\img\\q489.png', null, '2020-04-28 03:12:17', null);
INSERT INTO `word` VALUES ('213', 'https://www.diyibanzhu4.pro/toimg/data', 's1.png', 'public\\img\\s1.png', '骚', '2020-04-28 03:18:20', null);
INSERT INTO `word` VALUES ('214', 'https://www.diyibanzhu4.pro/toimg/data', 's2.png', 'public\\img\\s2.png', '色', '2020-04-28 03:18:22', null);
INSERT INTO `word` VALUES ('215', 'https://www.diyibanzhu4.pro/toimg/data', 's3.png', 'public\\img\\s3.png', '杀', '2020-04-28 03:18:24', null);
INSERT INTO `word` VALUES ('216', 'https://www.diyibanzhu4.pro/toimg/data', 's4.png', 'public\\img\\s4.png', '射', '2020-04-28 03:18:26', null);
INSERT INTO `word` VALUES ('217', 'https://www.diyibanzhu4.pro/toimg/data', 't1.png', 'public\\img\\t1.png', '台', '2020-04-28 03:18:26', null);
INSERT INTO `word` VALUES ('218', 'https://www.diyibanzhu4.pro/toimg/data', 's5.png', 'public\\img\\s5.png', '呻', '2020-04-28 03:18:27', null);
INSERT INTO `word` VALUES ('219', 'https://www.diyibanzhu4.pro/toimg/data', 't2.png', 'public\\img\\t2.png', '涛', '2020-04-28 03:18:28', null);
INSERT INTO `word` VALUES ('220', 'https://www.diyibanzhu4.pro/toimg/data', 's6.png', 'public\\img\\s6.png', '舌', '2020-04-28 03:18:29', null);
INSERT INTO `word` VALUES ('221', 'https://www.diyibanzhu4.pro/toimg/data', 't3.png', 'public\\img\\t3.png', '舔', '2020-04-28 03:18:29', null);
INSERT INTO `word` VALUES ('222', 'https://www.diyibanzhu4.pro/toimg/data', 't4.png', 'public\\img\\t4.png', '童', '2020-04-28 03:18:30', null);
INSERT INTO `word` VALUES ('223', 'https://www.diyibanzhu4.pro/toimg/data', 's7.png', 'public\\img\\s7.png', '湿', '2020-04-28 03:18:30', null);
INSERT INTO `word` VALUES ('224', 'https://www.diyibanzhu4.pro/toimg/data', 't5.png', 'public\\img\\t5.png', '偷', '2020-04-28 03:18:31', null);
INSERT INTO `word` VALUES ('225', 'https://www.diyibanzhu4.pro/toimg/data', 's8.png', 'public\\img\\s8.png', '尸', '2020-04-28 03:18:32', null);
INSERT INTO `word` VALUES ('226', 'https://www.diyibanzhu4.pro/toimg/data', 't6.png', 'public\\img\\t6.png', '腿', '2020-04-28 03:18:33', null);
INSERT INTO `word` VALUES ('227', 'https://www.diyibanzhu4.pro/toimg/data', 's9.png', 'public\\img\\s9.png', '兽', '2020-04-28 03:18:33', null);
INSERT INTO `word` VALUES ('228', 'https://www.diyibanzhu4.pro/toimg/data', 't7.png', 'public\\img\\t7.png', '吞', '2020-04-28 03:18:34', null);
INSERT INTO `word` VALUES ('229', 'https://www.diyibanzhu4.pro/toimg/data', 's10.png', 'public\\img\\s10.png', '骚', '2020-04-28 03:18:34', null);
INSERT INTO `word` VALUES ('230', 'https://www.diyibanzhu4.pro/toimg/data', 's11.png', 'public\\img\\s11.png', '熟', '2020-04-28 03:18:35', null);
INSERT INTO `word` VALUES ('231', 'https://www.diyibanzhu4.pro/toimg/data', 't8.png', 'public\\img\\t8.png', '臀', '2020-04-28 03:18:35', null);
INSERT INTO `word` VALUES ('232', 'https://www.diyibanzhu4.pro/toimg/data', 's12.png', 'public\\img\\s12.png', '丝', '2020-04-28 03:18:37', null);
INSERT INTO `word` VALUES ('233', 'https://www.diyibanzhu4.pro/toimg/data', 's13.png', 'public\\img\\s13.png', '死', '2020-04-28 03:18:38', null);
INSERT INTO `word` VALUES ('234', 'https://www.diyibanzhu4.pro/toimg/data', 's14.png', 'public\\img\\s14.png', '酸', '2020-04-28 03:18:40', null);
INSERT INTO `word` VALUES ('235', 'https://www.diyibanzhu4.pro/toimg/data', 't11.png', 'public\\img\\t11.png', '台', '2020-04-28 03:18:40', null);
INSERT INTO `word` VALUES ('236', 'https://www.diyibanzhu4.pro/toimg/data', 'w1.png', 'public\\img\\w1.png', '亡', '2020-04-28 03:18:43', null);
INSERT INTO `word` VALUES ('237', 'https://www.diyibanzhu4.pro/toimg/data', 'w2.png', 'public\\img\\w2.png', '未', '2020-04-28 03:18:44', null);
INSERT INTO `word` VALUES ('238', 'https://www.diyibanzhu4.pro/toimg/data', 'w3.png', 'public\\img\\w3.png', '温', '2020-04-28 03:18:46', null);
INSERT INTO `word` VALUES ('239', 'https://www.diyibanzhu4.pro/toimg/data', 'x1.png', 'public\\img\\x1.png', '席', '2020-04-28 03:18:48', null);
INSERT INTO `word` VALUES ('240', 'https://www.diyibanzhu4.pro/toimg/data', 'x2.png', 'public\\img\\x2.png', '吸', '2020-04-28 03:18:50', null);
INSERT INTO `word` VALUES ('241', 'https://www.diyibanzhu4.pro/toimg/data', 'x3.png', 'public\\img\\x3.png', '酰', '2020-04-28 03:18:51', null);
INSERT INTO `word` VALUES ('242', 'https://www.diyibanzhu4.pro/toimg/data', 's22.png', 'public\\img\\s22.png', '色', '2020-04-28 03:18:51', null);
INSERT INTO `word` VALUES ('243', 'https://www.diyibanzhu4.pro/toimg/data', 'x4.png', 'public\\img\\x4.png', '性', '2020-04-28 03:18:53', null);
INSERT INTO `word` VALUES ('244', 'https://www.diyibanzhu4.pro/toimg/data', 'x5.png', 'public\\img\\x5.png', '胸', '2020-04-28 03:18:54', null);
INSERT INTO `word` VALUES ('245', 'https://www.diyibanzhu4.pro/toimg/data', 'x6.png', 'public\\img\\x6.png', '锡', '2020-04-28 03:18:55', null);
INSERT INTO `word` VALUES ('246', 'https://www.diyibanzhu4.pro/toimg/data', 'w11.png', 'public\\img\\w11.png', '亡', '2020-04-28 03:18:56', null);
INSERT INTO `word` VALUES ('247', 'https://www.diyibanzhu4.pro/toimg/data', 'x7.png', 'public\\img\\x7.png', '穴', '2020-04-28 03:18:57', null);
INSERT INTO `word` VALUES ('248', 'https://www.diyibanzhu4.pro/toimg/data', 'x8.png', 'public\\img\\x8.png', '血', '2020-04-28 03:18:58', null);
INSERT INTO `word` VALUES ('249', 'https://www.diyibanzhu4.pro/toimg/data', 'x9.png', 'public\\img\\x9.png', '学', '2020-04-28 03:18:59', null);
INSERT INTO `word` VALUES ('250', 'https://www.diyibanzhu4.pro/toimg/data', 't22.png', 'public\\img\\t22.png', '涛', '2020-04-28 03:18:59', null);
INSERT INTO `word` VALUES ('251', 'https://www.diyibanzhu4.pro/toimg/data', 'x11.png', 'public\\img\\x11.png', '席', '2020-04-28 03:19:02', null);
INSERT INTO `word` VALUES ('252', 'https://www.diyibanzhu4.pro/toimg/data', 's33.png', 'public\\img\\s33.png', '杀', '2020-04-28 03:19:06', null);
INSERT INTO `word` VALUES ('253', 'https://www.diyibanzhu4.pro/toimg/data', 'w22.png', 'public\\img\\w22.png', '未', '2020-04-28 03:19:13', null);
INSERT INTO `word` VALUES ('254', 'https://www.diyibanzhu4.pro/toimg/data', 't33.png', 'public\\img\\t33.png', '舔', '2020-04-28 03:19:17', null);
INSERT INTO `word` VALUES ('255', 'https://www.diyibanzhu4.pro/toimg/data', 'x22.png', 'public\\img\\x22.png', '吸', '2020-04-28 03:19:19', null);
INSERT INTO `word` VALUES ('256', 'https://www.diyibanzhu4.pro/toimg/data', 's44.png', 'public\\img\\s44.png', '射', '2020-04-28 03:19:22', null);
INSERT INTO `word` VALUES ('257', 'https://www.diyibanzhu4.pro/toimg/data', 'w33.png', 'public\\img\\w33.png', '温', '2020-04-28 03:19:28', null);
INSERT INTO `word` VALUES ('258', 'https://www.diyibanzhu4.pro/toimg/data', 'x33.png', 'public\\img\\x33.png', '酰', '2020-04-28 03:19:35', null);
INSERT INTO `word` VALUES ('259', 'https://www.diyibanzhu4.pro/toimg/data', 't44.png', 'public\\img\\t44.png', '童', '2020-04-28 03:19:38', null);
INSERT INTO `word` VALUES ('260', 'https://www.diyibanzhu4.pro/toimg/data', 's55.png', 'public\\img\\s55.png', '呻', '2020-04-28 03:19:43', null);
INSERT INTO `word` VALUES ('261', 'https://www.diyibanzhu4.pro/toimg/data', 'x44.png', 'public\\img\\x44.png', '性', '2020-04-28 03:19:52', null);
INSERT INTO `word` VALUES ('262', 'https://www.diyibanzhu4.pro/toimg/data', 't55.png', 'public\\img\\t55.png', '偷', '2020-04-28 03:19:56', null);
INSERT INTO `word` VALUES ('263', 'https://www.diyibanzhu4.pro/toimg/data', 's66.png', 'public\\img\\s66.png', '舌', '2020-04-28 03:20:01', null);
INSERT INTO `word` VALUES ('264', 'https://www.diyibanzhu4.pro/toimg/data', 'x55.png', 'public\\img\\x55.png', '胸', '2020-04-28 03:20:09', null);
INSERT INTO `word` VALUES ('265', 'https://www.diyibanzhu4.pro/toimg/data', 't66.png', 'public\\img\\t66.png', '腿', '2020-04-28 03:20:11', null);
INSERT INTO `word` VALUES ('266', 'https://www.diyibanzhu4.pro/toimg/data', 's77.png', 'public\\img\\s77.png', '湿', '2020-04-28 03:20:19', null);
INSERT INTO `word` VALUES ('267', 'https://www.diyibanzhu4.pro/toimg/data', 'x66.png', 'public\\img\\x66.png', '锡', '2020-04-28 03:20:26', null);
INSERT INTO `word` VALUES ('268', 'https://www.diyibanzhu4.pro/toimg/data', 't77.png', 'public\\img\\t77.png', '吞', '2020-04-28 03:20:27', null);
INSERT INTO `word` VALUES ('269', 'https://www.diyibanzhu4.pro/toimg/data', 's88.png', 'public\\img\\s88.png', '尸', '2020-04-28 03:20:37', null);
INSERT INTO `word` VALUES ('270', 'https://www.diyibanzhu4.pro/toimg/data', 'x77.png', 'public\\img\\x77.png', '穴', '2020-04-28 03:20:43', null);
INSERT INTO `word` VALUES ('271', 'https://www.diyibanzhu4.pro/toimg/data', 't88.png', 'public\\img\\t88.png', '臀', '2020-04-28 03:20:45', null);
INSERT INTO `word` VALUES ('272', 'https://www.diyibanzhu4.pro/toimg/data', 's99.png', 'public\\img\\s99.png', '兽', '2020-04-28 03:20:53', null);
INSERT INTO `word` VALUES ('273', 'https://www.diyibanzhu4.pro/toimg/data', 's100.png', 'public\\img\\s100.png', '水', '2020-04-28 03:20:55', null);
INSERT INTO `word` VALUES ('274', 'https://www.diyibanzhu4.pro/toimg/data', 'x88.png', 'public\\img\\x88.png', '血', '2020-04-28 03:20:58', null);
INSERT INTO `word` VALUES ('275', 'https://www.diyibanzhu4.pro/toimg/data', 's111.png', 'public\\img\\s111.png', '熟', '2020-04-28 03:21:12', null);
INSERT INTO `word` VALUES ('276', 'https://www.diyibanzhu4.pro/toimg/data', 'x99.png', 'public\\img\\x99.png', '学', '2020-04-28 03:21:17', null);
INSERT INTO `word` VALUES ('277', 'https://www.diyibanzhu4.pro/toimg/data', 's122.png', 'public\\img\\s122.png', '丝', '2020-04-28 03:21:30', null);
INSERT INTO `word` VALUES ('278', 'https://www.diyibanzhu4.pro/toimg/data', 's133.png', 'public\\img\\s133.png', '死', '2020-04-28 03:21:47', null);
INSERT INTO `word` VALUES ('279', 'https://www.diyibanzhu4.pro/toimg/data', 's144.png', 'public\\img\\s144.png', '酸', '2020-04-28 03:22:02', null);
INSERT INTO `word` VALUES ('280', 'https://www.diyibanzhu4.pro/toimg/data', 'y1.png', 'public\\img\\y1.png', '药', '2020-04-28 03:34:26', null);
INSERT INTO `word` VALUES ('281', 'https://www.diyibanzhu4.pro/toimg/data', 'y2.png', 'public\\img\\y2.png', '摇', '2020-04-28 03:34:27', null);
INSERT INTO `word` VALUES ('282', 'https://www.diyibanzhu4.pro/toimg/data', 'y3.png', 'public\\img\\y3.png', '漪', '2020-04-28 03:34:29', null);
INSERT INTO `word` VALUES ('283', 'https://www.diyibanzhu4.pro/toimg/data', 'y4.png', 'public\\img\\y4.png', '阴', '2020-04-28 03:34:30', null);
INSERT INTO `word` VALUES ('284', 'https://www.diyibanzhu4.pro/toimg/data', 'z1.png', 'public\\img\\z1.png', '宰', '2020-04-28 03:34:31', null);
INSERT INTO `word` VALUES ('285', 'https://www.diyibanzhu4.pro/toimg/data', 'y5.png', 'public\\img\\y5.png', '淫', '2020-04-28 03:34:31', null);
INSERT INTO `word` VALUES ('286', 'https://www.diyibanzhu4.pro/toimg/data', 'z2.png', 'public\\img\\z2.png', '泽', '2020-04-28 03:34:32', null);
INSERT INTO `word` VALUES ('287', 'https://www.diyibanzhu4.pro/toimg/data', 'y6.png', 'public\\img\\y6.png', '硬', '2020-04-28 03:34:33', null);
INSERT INTO `word` VALUES ('288', 'https://www.diyibanzhu4.pro/toimg/data', 'z3.png', 'public\\img\\z3.png', '斩', '2020-04-28 03:34:34', null);
INSERT INTO `word` VALUES ('289', 'https://www.diyibanzhu4.pro/toimg/data', 'y7.png', 'public\\img\\y7.png', '吟', '2020-04-28 03:34:34', null);
INSERT INTO `word` VALUES ('290', 'https://www.diyibanzhu4.pro/toimg/data', 'z4.png', 'public\\img\\z4.png', '炸', '2020-04-28 03:34:35', null);
INSERT INTO `word` VALUES ('291', 'https://www.diyibanzhu4.pro/toimg/data', 'y8.png', 'public\\img\\y8.png', '义', '2020-04-28 03:34:36', null);
INSERT INTO `word` VALUES ('292', 'https://www.diyibanzhu4.pro/toimg/data', 'z5.png', 'public\\img\\z5.png', '指', '2020-04-28 03:34:36', null);
INSERT INTO `word` VALUES ('293', 'https://www.diyibanzhu4.pro/toimg/data', 'y9.png', 'public\\img\\y9.png', '幼', '2020-04-28 03:34:37', null);
INSERT INTO `word` VALUES ('294', 'https://www.diyibanzhu4.pro/toimg/data', 'z6.png', 'public\\img\\z6.png', '中', '2020-04-28 03:34:37', null);
INSERT INTO `word` VALUES ('295', 'https://www.diyibanzhu4.pro/toimg/data', 'y10.png', 'public\\img\\y10.png', '铀', '2020-04-28 03:34:38', null);
INSERT INTO `word` VALUES ('296', 'https://www.diyibanzhu4.pro/toimg/data', 'z7.png', 'public\\img\\z7.png', '主', '2020-04-28 03:34:39', null);
INSERT INTO `word` VALUES ('297', 'https://www.diyibanzhu4.pro/toimg/data', 'y11.png', 'public\\img\\y11.png', '欲', '2020-04-28 03:34:39', null);
INSERT INTO `word` VALUES ('298', 'https://www.diyibanzhu4.pro/toimg/data', 'z8.png', 'public\\img\\z8.png', '做', '2020-04-28 03:34:40', null);
INSERT INTO `word` VALUES ('299', 'https://www.diyibanzhu4.pro/toimg/data', 'z9.png', 'public\\img\\z9.png', '足', '2020-04-28 03:34:42', null);
INSERT INTO `word` VALUES ('300', 'https://www.diyibanzhu4.pro/toimg/data', 'z11.png', 'public\\img\\z11.png', '宰', '2020-04-28 03:34:44', null);
INSERT INTO `word` VALUES ('301', 'https://www.diyibanzhu4.pro/toimg/data', 'y22.png', 'public\\img\\y22.png', '摇', '2020-04-28 03:34:54', null);
INSERT INTO `word` VALUES ('302', 'https://www.diyibanzhu4.pro/toimg/data', 'z22.png', 'public\\img\\z22.png', '泽', '2020-04-28 03:35:00', null);
INSERT INTO `word` VALUES ('303', 'https://www.diyibanzhu4.pro/toimg/data', 'y33.png', 'public\\img\\y33.png', '漪', '2020-04-28 03:35:10', null);
INSERT INTO `word` VALUES ('304', 'https://www.diyibanzhu4.pro/toimg/data', 'z33.png', 'public\\img\\z33.png', '斩', '2020-04-28 03:35:16', null);
INSERT INTO `word` VALUES ('305', 'https://www.diyibanzhu4.pro/toimg/data', 'y44.png', 'public\\img\\y44.png', '阴', '2020-04-28 03:35:25', null);
INSERT INTO `word` VALUES ('306', 'https://www.diyibanzhu4.pro/toimg/data', 'z44.png', 'public\\img\\z44.png', '炸', '2020-04-28 03:35:35', null);
INSERT INTO `word` VALUES ('311', 'https://www.diyibanzhu4.pro/toimg/data', 'y77.png', 'public\\img\\y77.png', '吟', '2020-04-28 03:36:20', null);
INSERT INTO `word` VALUES ('307', 'https://www.diyibanzhu4.pro/toimg/data', 'y55.png', 'public\\img\\y55.png', '淫', '2020-04-28 03:35:46', null);
INSERT INTO `word` VALUES ('308', 'https://www.diyibanzhu4.pro/toimg/data', 'z55.png', 'public\\img\\z55.png', '指', '2020-04-28 03:35:52', null);
INSERT INTO `word` VALUES ('309', 'https://www.diyibanzhu4.pro/toimg/data', 'y66.png', 'public\\img\\y66.png', '硬', '2020-04-28 03:36:03', null);
INSERT INTO `word` VALUES ('310', 'https://www.diyibanzhu4.pro/toimg/data', 'z66.png', 'public\\img\\z66.png', '中', '2020-04-28 03:36:09', null);
INSERT INTO `word` VALUES ('312', 'https://www.diyibanzhu4.pro/toimg/data', 'z77.png', 'public\\img\\z77.png', '主', '2020-04-28 03:36:27', null);
INSERT INTO `word` VALUES ('313', 'https://www.diyibanzhu4.pro/toimg/data', 'y88.png', 'public\\img\\y88.png', '义', '2020-04-28 03:36:36', null);
INSERT INTO `word` VALUES ('314', 'https://www.diyibanzhu4.pro/toimg/data', 'z88.png', 'public\\img\\z88.png', '做', '2020-04-28 03:36:44', null);
INSERT INTO `word` VALUES ('315', 'https://www.diyibanzhu4.pro/toimg/data', 'y99.png', 'public\\img\\y99.png', '幼', '2020-04-28 03:36:53', null);
INSERT INTO `word` VALUES ('316', 'https://www.diyibanzhu4.pro/toimg/data', 'z99.png', 'public\\img\\z99.png', '足', '2020-04-28 03:36:58', null);
INSERT INTO `word` VALUES ('317', 'https://www.diyibanzhu4.pro/toimg/data', 'y100.png', 'public\\img\\y100.png', '铀', '2020-04-28 03:36:59', null);
INSERT INTO `word` VALUES ('318', 'https://www.diyibanzhu4.pro/toimg/data', 'y111.png', 'public\\img\\y111.png', '欲', '2020-04-28 03:37:15', null);
INSERT INTO `word` VALUES ('319', 'https://www.diyibanzhu4.pro/toimg/data', 'z152.png', 'public\\img\\z152.png', '', '2020-04-28 03:38:43', null);
