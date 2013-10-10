/**
 * This is an automatically generated file. Please do not edit.
 * @date 2013-10-10 15:54:33
 * @author phil
 * @description test
 */

ALTER TABLE `comments`
ADD `user_id` int(10) unsigned NOT NULL AFTER `id`,
DROP `email`;	