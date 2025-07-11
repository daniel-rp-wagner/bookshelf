<?php

/**
 * Class Publications
 *
 */
class Publication
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
public function getAllPublications(): array
{
    try {
        // 1. Alle Publikationen laden
        $this->db->query("SELECT * FROM publications");
        $this->db->execute();
        $publications = $this->db->results();

        // 2. Volumes inkl. Personen und Organisationen mit Alias
        $sql = "
            SELECT
                v.*,
                vo.organization_alias_id,
                oa.name AS alias_name,
                o.id AS organization_id,
                o.name AS organization_name,
                vo.role AS organization_role,
                vo.city_names_id AS city_names_id,
                vp.person_id,
                pa.name AS person_name,
                vp.role AS person_role
            FROM volumes v
            LEFT JOIN volume_organizations vo ON vo.volume_id = v.id
            LEFT JOIN organization_aliases oa ON oa.id = vo.organization_alias_id
            LEFT JOIN organizations o ON o.id = oa.org_id
            LEFT JOIN volume_persons vp ON vp.volume_id = v.id
            LEFT JOIN person_aliases pa ON pa.id = vp.person_id
        ";
        $this->db->query($sql);
        $this->db->execute();
        $rows = $this->db->results();

        // 3. Volumes mit Personen und Organisationen strukturieren
$volumeMap = [];

foreach ($rows as $r) {
    $vId = $r['id'];

    if (!isset($volumeMap[$vId])) {
        $volumeMap[$vId] = [
            'id' => $r['id'],
            'publication_id' => $r['publication_id'],
            'title' => $r['title'],
            'subtitle' => $r['subtitle'],
            'notes' => $r['notes'],
            'edition' => $r['edition'],
            'year' => $r['year'],
            'pages' => $r['pages'],
            'collation' => $r['collation'],
            'volume_number' => $r['volume_number'],
            'organizations' => [],
            'persons' => [],
        ];
    }

    // Personen deduplizieren anhand person_id + role
    if ($r['person_id']) {
        $key = $r['person_id'] . '|' . $r['person_role'];
        if (!isset($volumeMap[$vId]['_person_keys'][$key])) {
            $volumeMap[$vId]['persons'][] = [
                'id' => $r['person_id'],
                'name' => $r['person_name'],
                'role' => $r['person_role']
            ];
            $volumeMap[$vId]['_person_keys'][$key] = true;
        }
    }

    // Organisationen deduplizieren anhand alias_id + role
    if ($r['organization_alias_id']) {
        $orgKey = $r['organization_alias_id'] . '|' . $r['organization_role'];
        if (!isset($volumeMap[$vId]['_org_keys'][$orgKey])) {
            $cityIds = explode('|', $r['city_names_id'] ?? '');

            // Städte laden
            $cities = [];
            if (!empty($cityIds)) {
                $ids = array_filter($cityIds);
                $placeholders = implode(',', array_map('intval', $ids));
                if ($placeholders) {
                    $sqlCities = "SELECT city_id, name FROM city_names WHERE name_id IN ($placeholders)";
                    $this->db->query($sqlCities);
                    $this->db->execute();
                    $cities = $this->db->results();
                }
            }

            $volumeMap[$vId]['organizations'][] = [
                'id' => $r['organization_id'],
                'alias_id' => $r['organization_alias_id'],
                'alias_name' => $r['alias_name'],
                'role' => $r['organization_role'],
                'city_name_ids' => $cityIds,
                'cities' => $cities
            ];
            $volumeMap[$vId]['_org_keys'][$orgKey] = true;
        }
    }
}

foreach ($volumeMap as &$vol) {
    unset($vol['_org_keys'], $vol['_person_keys']);
}

        // 4. Volumes jeder Publikation zuordnen
        foreach ($publications as &$pub) {
            $pub['volumes'] = [];

            foreach ($volumeMap as $vol) {
                if ($vol['publication_id'] == $pub['id']) {
                    $pub['volumes'][] = $vol;
                }
            }

            // Titel/Untertitel überschreiben bei Einzelband
            if (count($pub['volumes']) === 1) {
                $vol = $pub['volumes'][0];
                $pub['title'] = $vol['title'];
                $pub['subtitle'] = $vol['subtitle'];
            }

            // Gesamtseitenzahl & zusammengefasste Kollation berechnen
            $totalPages = 0;
            $collationList = [];

            foreach ($pub['volumes'] as $v) {
                if (!empty($v['pages'])) {
                    $totalPages += (int) $v['pages'];
                }
                if (!empty($v['collation'])) {
                    $collationList[] = $v['collation'];
                }
            }

            $pub['sum_pages'] = $totalPages;
            $pub['merged_collation'] = implode('; ', $collationList);

            $years = array_filter(array_column($pub['volumes'], 'year'));
            if (count($years) === 0) {
                $pub['years'] = null;
            } elseif (count($years) === 1 || min($years) === max($years)) {
                $pub['years'] = (string) min($years);
            } else {
                $pub['years'] = min($years) . '-' . max($years);
            }
        }

        usort($publications, function ($a, $b) {
            // NULL-Jahresangaben behandeln
            if ($a['years'] === null) return 1;
            if ($b['years'] === null) return -1;

            // Vergleich: erstes Jahr extrahieren
            $aYear = (int) explode('-', $a['years'])[0];
            $bYear = (int) explode('-', $b['years'])[0];

            if ($aYear !== $bYear) {
                return $aYear <=> $bYear;
            }

            // Sekundär: sort_index (NULLs ans Ende)
            $aSort = $a['sort_index'] ?? PHP_INT_MAX;
            $bSort = $b['sort_index'] ?? PHP_INT_MAX;

            return $aSort <=> $bSort;
        });

        return $publications;

    } catch (PDOException $e) {
        echo 'SQL-Fehler: ' . $e->getMessage();
        return [];
    }
}




    /**
     * Retrieve a single series by its ID, including associated persons.
     *
     * @param int $id
     * @return array|null
     */
public function getPublicationById(int $id): ?array
{
    $this->db->query("SELECT * FROM publications WHERE id = :id");
    $this->db->bind(':id', $id);
    $this->db->execute();
    $publication = $this->db->result();

    if (!$publication) {
        return null;
    }

    // Volumes + Personen + Organisationen
    $this->db->query("
        SELECT
            v.*,
            vo.organization_alias_id,
            oa.name AS alias_name,
            o.id AS organization_id,
            o.name AS organization_name,
            vo.role AS organization_role,
            vo.city_names_id AS city_names_id,
            vp.person_id,
            pa.name AS person_name,
            vp.role AS person_role
        FROM volumes v
            LEFT JOIN volume_organizations vo ON vo.volume_id = v.id
            LEFT JOIN organization_aliases oa ON oa.id = vo.organization_alias_id
            LEFT JOIN organizations o ON o.id = oa.org_id
            LEFT JOIN volume_persons vp ON vp.volume_id = v.id
            LEFT JOIN person_aliases pa ON pa.id = vp.person_id
        WHERE v.publication_id = :id
    ");
    $this->db->bind(':id', $id);
    $this->db->execute();
    $rows = $this->db->results();

    // Volumes strukturieren
$volumeMap = [];

foreach ($rows as $r) {
    $vId = $r['id'];

    if (!isset($volumeMap[$vId])) {
        $volumeMap[$vId] = [
            'id' => $r['id'],
            'publication_id' => $r['publication_id'],
            'title' => $r['title'],
            'subtitle' => $r['subtitle'],
            'notes' => $r['notes'],
            'edition' => $r['edition'],
            'year' => $r['year'],
            'pages' => $r['pages'],
            'collation' => $r['collation'],
            'volume_number' => $r['volume_number'],
            'organizations' => [],
            'persons' => [],
        ];
    }

    // Personen deduplizieren anhand person_id + role
    if ($r['person_id']) {
        $key = $r['person_id'] . '|' . $r['person_role'];
        if (!isset($volumeMap[$vId]['_person_keys'][$key])) {
            $volumeMap[$vId]['persons'][] = [
                'id' => $r['person_id'],
                'name' => $r['person_name'],
                'role' => $r['person_role']
            ];
            $volumeMap[$vId]['_person_keys'][$key] = true;
        }
    }

    // Organisationen deduplizieren anhand alias_id + role
    if ($r['organization_alias_id']) {
        $orgKey = $r['organization_alias_id'] . '|' . $r['organization_role'];
        if (!isset($volumeMap[$vId]['_org_keys'][$orgKey])) {
            $cityIds = explode('|', $r['city_names_id'] ?? '');

            // Städte laden
            $cities = [];
            if (!empty($cityIds)) {
                $ids = array_filter($cityIds);
                $placeholders = implode(',', array_map('intval', $ids));
                if ($placeholders) {
                    $sqlCities = "SELECT city_id, name FROM city_names WHERE name_id IN ($placeholders)";
                    $this->db->query($sqlCities);
                    $this->db->execute();
                    $cities = $this->db->results();
                }
            }

            $volumeMap[$vId]['organizations'][] = [
                'id' => $r['organization_id'],
                'alias_id' => $r['organization_alias_id'],
                'alias_name' => $r['alias_name'],
                'role' => $r['organization_role'],
                'city_name_ids' => $cityIds,
                'cities' => $cities
            ];
            $volumeMap[$vId]['_org_keys'][$orgKey] = true;
        }
    }
}

foreach ($volumeMap as &$vol) {
    unset($vol['_org_keys'], $vol['_person_keys']);
}

    $publication['volumes'] = array_values($volumeMap);

    // Optional: Titel überschreiben bei Einzelband
    if (count($publication['volumes']) === 1) {
        $publication['title'] = $publication['volumes'][0]['title'];
        $publication['subtitle'] = $publication['volumes'][0]['subtitle'];
    }

    // NEU: Seitenzahl & Kollation aggregieren
    $totalPages = 0;
    $collationList = [];

    foreach ($publication['volumes'] as $v) {
        if (!empty($v['pages'])) {
            $totalPages += (int) $v['pages'];
        }
        if (!empty($v['collation'])) {
            $collationList[] = $v['collation'];
        }
    }

    $publication['sum_pages'] = $totalPages;
    $publication['merged_collation'] = implode('; ', $collationList);

    $years = array_filter(array_column($publication['volumes'], 'year'));

    if (count($years) === 0) {
        $publication['years'] = null;
    } elseif (count($years) === 1 || min($years) === max($years)) {
        $publication['years'] = (string) min($years);
    } else {
        $publication['years'] = min($years) . '-' . max($years);
    }


    return $publication;
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
    public function createPublications(array $data): array
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
    public function updatePublications(array $data): void
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
    public function deletePublications(int $id): array
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