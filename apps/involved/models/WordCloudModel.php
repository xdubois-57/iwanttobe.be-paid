<?php
/**
 * WordCloudModel – wraps WORDCLOUD table operations for Involved! app.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';
require_once __DIR__ . '/../../../lib/Logger.php';

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
     * @return int|false inserted ID or false
     */
    public function create(int $eventId, string $question): int|false
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        return $this->db->insert('WORDCLOUD', [
            'event_id' => $eventId,
            'question' => $question
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
        return $this->db->fetchAll('SELECT id, question, created_at FROM WORDCLOUD WHERE event_id = ? ORDER BY created_at DESC', [$eventId]) ?? [];
    }

    /**
     * Get a word cloud by ID
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        $result = $this->db->fetchOne('SELECT * FROM WORDCLOUD WHERE id = ?', [$id]);
        return $result === false ? null : $result;
    }

    /**
     * Delete a word cloud by ID
     */
    public function delete(int $id): bool
    {
        return $this->db->delete('WORDCLOUD', 'id = ?', [$id]);
    }
}
