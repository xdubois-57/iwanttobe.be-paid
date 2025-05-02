<?php
/**
 * WordCloudModel – wraps WORDCLOUD table operations for Involved! app.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';

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
            error_log('DB connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        return $this->db->insert('WORDCLOUD', [
            'event_id' => $eventId,
            'question' => $question,
            'data' => json_encode([])
        ]);
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
     */
    public function getById(int $id): array|null
    {
        return $this->db->fetchOne('SELECT * FROM WORDCLOUD WHERE id = ?', [$id]);
    }

    /**
     * Delete a word cloud by ID
     */
    public function delete(int $id): bool
    {
        return $this->db->delete('WORDCLOUD', 'id = ?', [$id]);
    }
}
