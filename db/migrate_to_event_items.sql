-- Create EVENT_ANSWERS table with same structure as POLLS_ANSWERS
CREATE TABLE `EVENT_ANSWERS` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_item_id` int(11) NOT NULL,
  `answer` varchar(255) NOT NULL,
  `type` varchar(50) DEFAULT 'text',
  `votes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_item_id_idx` (`event_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Delete old tables
DROP TABLE IF EXISTS `POLLS_ANSWERS`;
DROP TABLE IF EXISTS `POLLS`;
DROP TABLE IF EXISTS `WORD`;
DROP TABLE IF EXISTS `WORDCLOUD`;
