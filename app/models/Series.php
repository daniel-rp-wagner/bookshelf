<?php

/**
 * Class Series
 *
 * Model class for handling series-related database operations,
 * including the associated persons.
 */
class Series
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Retrieve all series.
     *
     * @return array List of series.
     */
    public function getAllSeries(): array
    {
        $this->db->query("SELECT * FROM series ORDER BY title ASC");
        $this->db->execute();
        return $this->db->results();
    }

    /**
     * Retrieve a single series by its ID, including associated persons.
     *
     * @param int $id
     * @return array|null
     */
    public function getSeriesById(int $id): ?array
    {
        // get series
        $this->db->query("SELECT * FROM series WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        $series = $this->db->result();
        if (!$series) {
            return null;
        }

        // get associated persons with their default alias name
        $this->db->query("
            SELECT
                sp.person_id,
                sp.role,
                pa.name AS name_default
            FROM series_person sp
        LEFT JOIN person_aliases pa
                ON pa.person_id = sp.person_id
            AND pa.type = 'name_default'
            WHERE sp.series_id = :id
        ");
        $this->db->bind(':id', $id);
        $this->db->execute();
        $series['persons'] = $this->db->results();

        // get associated organizations
        $this->db->query("
            SELECT so.org_id, o.name AS organization_name
                FROM series_organization so
                JOIN organizations o
                ON o.id = so.org_id
                WHERE so.series_id = :id
        ");
        $this->db->bind(':id', $id);
        $this->db->execute();
        $series['organizations'] = $this->db->results();

        return $series;
    }

    /**
     * Create a new series plus optional persons.
     *
     * Expected $data keys:
     * - title, subtitle, description
     * - persons: array of ['person_id'=>int, 'role'=>string]
     *
     * @param array $data
     * @return int new series ID
     * @throws ApiException
     */
    public function createSeries(array $data): array
    {
        try {
            $this->db->begin();

            // insert series
            $this->db->query("
                INSERT INTO series (title, subtitle, description)
                VALUES (:title, :subtitle, :description)
            ");
            $this->db->bind(':title',       $data['title']);
            $this->db->bind(':subtitle',    $data['subtitle']);
            $this->db->bind(':description', $data['description'] ?? null);
            $this->db->execute();
            $newId = (int)$this->db->lastInsertId();

            // insert persons if provided
            if (!empty($data['persons']) && is_array($data['persons'])) {
                foreach ($data['persons'] as $p) {
                    $this->db->query("
                        INSERT INTO series_person (series_id, person_id, role)
                        VALUES (:series_id, :person_id, :role)
                    ");
                    $this->db->bind(':series_id', $newId);
                    $this->db->bind(':person_id', $p['person_id']);
                    $this->db->bind(':role',      $p['role']);
                    $this->db->execute();
                }
            }

            // insert organizations if provided
            if (!empty($data['organizations']) && is_array($data['organizations'])) {
                foreach ($data['organizations'] as $orgId) {
                    $this->db->query("
                        INSERT INTO series_organization (series_id, org_id)
                        VALUES (:series_id, :org_id)
                    ");
                    $this->db->bind(':series_id', $newId);
                    $this->db->bind(':org_id',     $orgId);
                    $this->db->execute();
                }
            }

            $this->db->commit();
            return [(int)$newId];
        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }
    }

    /**
     * Update an existing series plus its persons.
     *
     * @param array $data ['id', 'title', 'subtitle', 'description', 'persons']
     * @return void
     * @throws ApiException
     */
    public function updateSeries(array $data): void
    {
        try {
            $this->db->begin();

            // update series record
            $this->db->query("
                UPDATE series
                   SET title = :title,
                       subtitle = :subtitle,
                       description = :description
                 WHERE id = :id
            ");
            $this->db->bind(':title',       $data['title']);
            $this->db->bind(':subtitle',    $data['subtitle']);
            $this->db->bind(':description', $data['description'] ?? null);
            $this->db->bind(':id',          $data['id']);
            $this->db->execute();

            // reset associations
            $this->db->query("DELETE FROM series_person WHERE series_id = :id");
            $this->db->bind(':id', $data['id']);
            $this->db->execute();

            // re-insert persons
            if (!empty($data['persons']) && is_array($data['persons'])) {
                foreach ($data['persons'] as $p) {
                    $this->db->query("
                        INSERT INTO series_person (series_id, person_id, role)
                        VALUES (:series_id, :person_id, :role)
                    ");
                    $this->db->bind(':series_id', $data['id']);
                    $this->db->bind(':person_id', $p['person_id']);
                    $this->db->bind(':role',      $p['role']);
                    $this->db->execute();
                }
            }

            // reset organization associations
            $this->db->query("DELETE FROM series_organization WHERE series_id = :id");
            $this->db->bind(':id', $data['id']);
            $this->db->execute();

            // re‐insert organizations
            if (!empty($data['organizations']) && is_array($data['organizations'])) {
                foreach ($data['organizations'] as $orgId) {
                    $this->db->query("
                        INSERT INTO series_organization (series_id, org_id)
                        VALUES (:series_id, :org_id)
                    ");
                    $this->db->bind(':series_id', $data['id']);
                    $this->db->bind(':org_id',     $orgId);
                    $this->db->execute();
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }
    }

    /**
     * Delete a series (and cascade persons via FK).
     *
     * @param int $id
     * @return void
     * @throws ApiException
     */
    public function deleteSeries(int $id): array
    {
        $sql = "DELETE FROM series WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->execute();

        if($this->db->rowCount() > 0){
            return [];
        } else {
            throw new ApiException(404, 'NOT_FOUND', 'ID not found, nothing to delete');
        }
    }
}