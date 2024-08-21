-- revert schema 163
DELETE FROM config WHERE conf_name = 'mass_email_in_sequences';
UPDATE config SET conf_value = 162 WHERE conf_name = 'schema';
