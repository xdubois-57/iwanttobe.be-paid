<?php
/**
 * EventModel – wraps EVENT table operations for Involved! app.
 */
require_once __DIR__ . '/../../../lib/DatabaseHelper.php';

class EventModel
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseHelper::getInstance();
    }

    /**
     * Create event with empty description and generated key
     * @return string|false Generated key or false on failure
     */
    public function createEmpty(): string|false
    {
        // Check connection
        if (!$this->db->isConnected()) {
            error_log('Database connection failed: ' . $this->db->getErrorMessage());
            return false;
        }
        
        $code = $this->generateUniqueCode();
        if ($code === false) {
            return false;
        }
        $insertId = $this->db->insert('EVENT', [
            'key' => $code,
            'description' => ''
        ]);
        return $insertId !== false ? $code : false;
    }

    /**
     * Get event by key
     */
    public function getByKey(string $code): array|null
    {
        return $this->db->fetchOne('SELECT * FROM EVENT WHERE `key` = ?', [$code]);
    }

    /**
     * Generate unique 4-char code
     */
    private function generateUniqueCode(): string|false
    {
        $chars = array_diff(array_merge(range('A', 'Z'), range('1', '9')), ['O']);
        $attempts = 0;
        do {
            $code = '';
            for ($i = 0; $i < 4; $i++) {
                $code .= $chars[array_rand($chars)];
            }
            $exists = $this->db->fetchValue('SELECT COUNT(*) FROM EVENT WHERE `key` = ?', [$code]);
            $attempts++;
            if ($attempts > 100) {
                return false;
            }
        } while ($exists);
        return $code;
    }
}
