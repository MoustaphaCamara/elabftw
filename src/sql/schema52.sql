-- Schema 52
START TRANSACTION;
    ALTER TABLE `experiments` DROP FOREIGN KEY `fk_experiments_teams_id`;
    ALTER TABLE `experiments` DROP `team`;
    ALTER TABLE `experiments` ADD `canwrite` VARCHAR(255) NOT NULL DEFAULT 'user';
    ALTER TABLE `experiments` CHANGE `visibility` `canread` VARCHAR(255) NOT NULL DEFAULT 'team';
    ALTER TABLE `items` ADD `canwrite` VARCHAR(255) NOT NULL DEFAULT 'team';
    ALTER TABLE `items` CHANGE `visibility` `canread` VARCHAR(255) NOT NULL DEFAULT 'team';
    ALTER TABLE `users` CHANGE `default_vis` `default_read` VARCHAR(255) NULL DEFAULT 'team';
    ALTER TABLE `users` ADD `default_write` VARCHAR(255) NULL DEFAULT 'user';
    ALTER TABLE `users` ADD `display_size` VARCHAR(2) NOT NULL DEFAULT 'lg';
    ALTER TABLE `users` DROP `allow_edit`;
    ALTER TABLE `users` DROP `allow_group_edit`;
    ALTER TABLE `users` DROP `close_warning`;
    ALTER TABLE `team_events` ADD `experiment` int(10) UNSIGNED DEFAULT NULL;
    INSERT INTO config (conf_name, conf_value) VALUES ('email_domain', NULL);
    ALTER TABLE `users` DROP FOREIGN KEY `fk_users_teams_id`;
    CREATE TABLE `users2teams` (
      `users_id` int(10) UNSIGNED NOT NULL,
      `teams_id` int(10) UNSIGNED NOT NULL
    );
    INSERT INTO `users2teams`(`users_id`, `teams_id`) SELECT `userid`, `team` FROM `users`;
    ALTER TABLE `users` DROP `team`;
    UPDATE config SET conf_value = 52 WHERE conf_name = 'schema';
COMMIT;
