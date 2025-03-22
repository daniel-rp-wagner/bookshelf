<?php

/**
 * Class Organization
 *
 * Model class for handling organization-related database operations.
 */
class Organization
{
    /**
     * Database connection instance.
     *
     * @var Database
     */
    private Database $db;

    /**
     * Organization constructor.
     *
     * Initializes a new database connection instance.
     */
    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Retrieve all organizations.
     *
     * This method fetches all organizations with optional filtering, sorting,
     * or pagination (if implemented).
     *
     * @param string $lang The language code for retrieving names.
     * @return array The result set as an array of organizations.
     */
    public function getAllOrganizations(string $query): array
    {
        $this->db->query("SELECT * FROM organizations" . $query);
        $this->db->execute();
        
        $result = $this->db->results();
        return is_array($result) ? $result : [];
    }

    /**
     * Retrieve a single organization by its ID
     *
     * @param int $id The ID of the organization.
     * @param string $lang The language code for retrieving names.
     * @return array The result as an associative array.
     */
    public function getOrganizationById(int $id, string $lang): array
    {
        $this->db->query("SELECT * FROM organizations WHERE id = :id");
        $this->db->bind(':id', $id);

        $this->db->execute();

        $org = $this->db->result();
        if (!$org) {
            return [];
        }

        $this->db->query("SELECT i.translation 
            FROM organization_types t
            JOIN i8n i ON t.type = i.variable
            WHERE t.org_id = :id AND i.lang = :lang");
        $this->db->bind(':id', $id);
        $this->db->bind(':lang', $lang);
        $this->db->execute();
        
        $org['types'] = [];
        foreach($this->db->results() as $type){
            array_push($org['types'], $type['translation']);
        };

        $this->db->query("SELECT description FROM organization_description WHERE org_id = :id AND lang = :lang");
        $this->db->bind(':id', $id);
        $this->db->bind(':lang', $lang);

        $this->db->execute();
        $res = $this->db->result();

        $org['description'] = $res ? $res['description'] : '';

        $this->db->query("SELECT name FROM organization_aliases WHERE org_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        
        $org['aliases'] = [];
        foreach($this->db->results() as $name){
            array_push($org['aliases'], $name['name']);
        };

        // Städte laden
        $this->db->query("SELECT c.id, COALESCE(cn_display.name, cn_official.name) AS city_name
            FROM organization_cities oc
            JOIN cities c ON oc.city_id = c.id
            LEFT JOIN city_names cn_official 
                ON cn_official.city_id = c.id 
                AND cn_official.language_code = 'on'
            LEFT JOIN city_names cn_display 
                ON cn_display.city_id = c.id 
                AND cn_display.language_code = :lang
            WHERE oc.org_id = :id;");
        $this->db->bind(':id', $id);
        $this->db->bind(':lang', $lang);
        $this->db->execute();

        $org['cities'] = $this->db->results();

        // SQL-Abfrage (ohne GROUP BY, um alle Zeilen zu erhalten)
        $this->db->query("SELECT r.type, r.child_org_id, o.name
            FROM organization_rels r 
            JOIN organizations o ON r.child_org_id = o.id
            WHERE org_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        
        $relations = $this->db->results();
        
        // Gruppierung der Ergebnisse
        $groupedRelations = [
            'preceeding' => [],
            'suceeding'  => []
        ];
        
        foreach ($relations as $relation) {
            if ($relation['type'] === 'pre') {
                $groupedRelations['preceeding'][] = [
                    'id'   => $relation['child_org_id'],
                    'name' => $relation['name']
                ];
            } elseif ($relation['type'] === 'suc') {
                $groupedRelations['suceeding'][] = [
                    'id'   => $relation['child_org_id'],
                    'name' => $relation['name']
                ];
            }
        }
        $org['relations'] = $groupedRelations;

        $this->db->query("SELECT title, url FROM organization_sources WHERE org_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        $org['sources'] = $this->db->results();

        return $org;
    }

    /**
     * Delete a organization by its ID.
     *
     * @param int $id The organization ID.
     * @return array True on success, false otherwise.
     */
    public function deleteOrganizationById(int $id): array
    {
        $this->db->query("DELETE FROM organizations WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        if($this->db->rowCount() > 0){
            return [];
        } else {
            throw new ApiException(404, 'NOT_FOUND', 'ID not found, nothing to delete');
        }
    }

    public function createOrganization(array $data): array
    {
        try {
            $this->db->begin();

            $this->db->query("INSERT INTO organizations (name, established_year, terminated_year) VALUES (:name, :established_year, :terminated_year)");
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':established_year', $data['established_year']);
            $this->db->bind(':terminated_year', $data['terminated_year']);
            $this->db->execute();

            $orgId = $this->db->lastInsertId();

            // Aliase einfügen
            $aliasQuery = "INSERT INTO organization_aliases (org_id, name) VALUES ";
            if (!empty($data['aliases']) && is_array($data['aliases'])) {
                foreach ($data['aliases'] as $alias) {
                    $this->db->query("INSERT INTO organization_aliases (org_id, name) VALUES (:org_id, :name)");
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':name', $alias);
                    $this->db->execute();
                }
            }
    
            $this->db->commit();

            return [$orgId];

        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }   
    }
}
