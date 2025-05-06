<?php
/**
 * WordCloudModel – wraps WORDCLOUD table operations for Involved! app.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';
require_once __DIR__ . '/../../../lib/Logger.php';
require_once __DIR__ . '/EventItemModel.php';

class WordCloudModel
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseHelper::getInstance();
    }

    /**
     * Create a word cloud for an event
     * @param int $eventId
     * @param string $question
     * @param int $position optional ordering value
     * @param string $type event item type
     * @return int|false inserted ID or false
     */
    public function create(int $eventId, string $question, int $position = 0, string $type = 'wordcloud'): int|false
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        // First create an event item record
        $eventItemModel = new EventItemModel();
        $eventItemId = $eventItemModel->create($eventId, $question, $position, $type);

        if ($eventItemId === false) {
            Logger::getInstance()->error('Failed to create EVENT_ITEM for word cloud');
            return false;
        }

        // Create the word cloud referencing the new event_item_id
        return $this->db->insert('WORDCLOUD', [
            'event_item_id' => $eventItemId
        ]);
    }

    /**
     * Add a word to a word cloud
     * @param int $wordCloudId
     * @param string $word
     * @return int|false inserted ID or false
     */
    public function addWord(int $wordCloudId, string $word): int|false
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        return $this->db->insert('WORD', [
            'wordcloud_id' => $wordCloudId,
            'word' => substr(trim($word), 0, 30) // Ensure word fits in VARCHAR(30)
        ]);
    }

    /**
     * Get all words for a word cloud, aggregating duplicates by summing their weights
     * @param int $wordCloudId
     * @return array list of words with aggregated weights
     */
    public function getWords(int $wordCloudId): array
    {
        // First get the raw words
        $rawWords = $this->db->fetchAll('SELECT * FROM WORD WHERE wordcloud_id = ? ORDER BY created_at DESC', [$wordCloudId]) ?? [];
        
        // Aggregate duplicates by summing their weights
        $aggregatedWords = [];
        foreach ($rawWords as $word) {
            $lowerWord = strtolower($word['word']);
            if (!isset($aggregatedWords[$lowerWord])) {
                $aggregatedWords[$lowerWord] = [
                    'word' => $word['word'],
                    'weight' => 1,
                    'created_at' => $word['created_at']
                ];
            } else {
                $aggregatedWords[$lowerWord]['weight'] += 1;
            }
        }
        
        // Convert to array of values
        return array_values($aggregatedWords);
    }

    /**
     * Delete a word from a word cloud by its text
     * @param int $wordCloudId
     * @param string $word
     * @return bool success
     */
    public function deleteWordByText(int $wordCloudId, string $word): bool
    {
        // Delete the word with the matching text (case-insensitive)
        return $this->db->delete('WORD', 'wordcloud_id = ? AND LOWER(word) = ?', [
            $wordCloudId,
            strtolower($word)
        ]);
    }

    /**
     * Delete a word from a word cloud
     * @param int $wordId
     * @return bool success
     */
    public function deleteWord(int $wordId): bool
    {
        return $this->db->delete('WORD', 'id = ?', [$wordId]);
    }

    /**
     * Get all word clouds for an event
     * @param int $eventId
     * @return array list
     */
    public function getByEvent(int $eventId): array
    {
        $rows = $this->db->fetchAll('SELECT wc.id, ei.question, ei.created_at FROM WORDCLOUD wc JOIN EVENT_ITEM ei ON wc.event_item_id = ei.id WHERE ei.event_id = ? ORDER BY ei.created_at DESC', [$eventId]);
        return $rows ?? [];
    }

    /**
     * Get a word cloud by ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $result = $this->db->fetchOne('SELECT wc.*, ei.event_id, ei.question, ei.position FROM WORDCLOUD wc JOIN EVENT_ITEM ei ON wc.event_item_id = ei.id WHERE wc.id = ?', [$id]);
        return $result === false ? null : $result;
    }

    /**
     * Delete a word cloud by ID
     */
    public function delete(int $id): bool
    {
        // Fetch the event_item_id before deleting the wordcloud
        $row = $this->db->fetchOne('SELECT event_item_id FROM WORDCLOUD WHERE id = ?', [$id]);
        if (!$row) {
            Logger::getInstance()->warning('Attempted to delete non-existent wordcloud id: ' . $id);
            return false;
        }
        $eventItemId = $row['event_item_id'];

        // Delete the wordcloud entry
        $deleted = $this->db->delete('WORDCLOUD', 'id = ?', [$id]);
        if ($deleted) {
            // Delete the corresponding event item
            $eventItemModel = new EventItemModel();
            $eventItemModel->delete($eventItemId);
            Logger::getInstance()->info('Deleted wordcloud and corresponding event_item id: ' . $eventItemId);
        }
        return $deleted;
    }
}
