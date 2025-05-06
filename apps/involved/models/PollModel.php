<?php
/**
 * PollModel – wraps POLLS table operations
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';
require_once __DIR__ . '/../../../lib/Logger.php';

class PollModel
{
    private DatabaseHelper $db;

    public function __construct()
    {
        $this->db = DatabaseHelper::getInstance();
    }

    /**
     * Create a poll linked to an event_item
     * @param int $eventItemId
     * @param string $type e.g. horizontal_bar_chart
     * @return int|false
     */
    public function create(int $eventItemId, string $type = 'horizontal_bar_chart'): int|false
    {
        if (!$this->db->isConnected()) {
            Logger::getInstance()->error('PollModel:create DB not connected');
            return false;
        }
        return $this->db->insert('POLLS', [
            'event_item_id' => $eventItemId,
            'type' => $type
        ]);
    }

    public function getById(int $id): ?array
    {
        $row = $this->db->fetchOne('SELECT * FROM POLLS WHERE id = ?', [$id]);
        return $row === false ? null : $row;
    }

    public function getByEventItemId(int $eid): ?array
    {
        $row = $this->db->fetchOne('SELECT * FROM POLLS WHERE event_item_id = ?', [$eid]);
        return $row === false ? null : $row;
    }
}
