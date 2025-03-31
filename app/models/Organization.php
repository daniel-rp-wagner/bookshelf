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
     * Retrieves all organizations, optionally filtered by type and city,
     * and returns organization data along with aggregated types (with translation)
     * and cities (with city ID and name).
     *
     * @param string $lang The language code for retrieving localized values.
     * @param string $filterType (Optional) The organization type to filter by.
     * @param string $filterCity (Optional) The city name (in the given language) to filter by.
     * @param string $paginationQuery (Optional) Additional SQL for pagination (e.g. LIMIT and OFFSET).
     * @return array The result set as an array of organizations.
     */
    public function getAllOrganizations(string $lang, string $filterType = '', string $filterCity = '', string $paginationQuery = ''): array {
        $sql = "SELECT 
                    o.*, 
                    -- Aggregate organization types with translation: format: type::translation
                    GROUP_CONCAT(DISTINCT CONCAT(ot.type, '::', COALESCE(i.translation, ot.type)) SEPARATOR ',') AS types,
                    -- Aggregate organization cities: format: city_id::city_name
                    GROUP_CONCAT(DISTINCT CONCAT(oc.city_id, '::', COALESCE(cn_display.name, cn_official.name)) SEPARATOR ',') AS cities
                FROM organizations o
                LEFT JOIN organization_types ot ON o.id = ot.org_id
                LEFT JOIN i8n i ON ot.type = i.variable AND i.lang = :lang
                LEFT JOIN organization_cities oc ON o.id = oc.org_id
                LEFT JOIN city_names cn_official ON oc.city_id = cn_official.city_id AND cn_official.language_code = 'on'
                LEFT JOIN city_names cn_display ON oc.city_id = cn_display.city_id AND cn_display.language_code = :lang
                WHERE 1=1";
        
        // Filter by organization type if provided
        if (!empty($filterType)) {
            $sql .= " AND ot.type = :filterType";
        }
        
        // Filter by city name if provided; matches against the localized city name
        if (!empty($filterCity)) {
            $sql .= " AND oc.city_id  = :filterCity";
        }
        
        $sql .= " GROUP BY o.id
                ORDER BY o.name ASC " . $paginationQuery;
        
        $this->db->query($sql);
        $this->db->bind(':lang', $lang);
        
        if (!empty($filterType)) {
            $this->db->bind(':filterType', $filterType);
        }
        if (!empty($filterCity)) {
            $this->db->bind(':filterCity', $filterCity);
        }
        
        $this->db->execute();
        $result = $this->db->results();

        if (is_array($result)) {
            foreach ($result as &$org) {
                // Convert aggregated types string to an object if it exists
                if (isset($org['types']) && is_string($org['types'])) {
                    $org['types'] = $this->convertAggregatedStringToObject($org['types']);
                }
                // Convert aggregated cities string similarly
                if (isset($org['cities']) && is_string($org['cities'])) {
                    $org['cities'] = $this->convertAggregatedStringToObject($org['cities']);
                }
            }
        }

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
            'preceding' => [],
            'succeeding'  => []
        ];
        
        foreach ($relations as $relation) {
            if ($relation['type'] === 'pre') {
                $groupedRelations['preceding'][] = [
                    'id'   => $relation['child_org_id'],
                    'name' => $relation['name']
                ];
            } elseif ($relation['type'] === 'suc') {
                $groupedRelations['succeeding'][] = [
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
    
            // Insert main organization record
            $this->db->query(
                "INSERT INTO organizations (name, established_year, terminated_year) 
                 VALUES (:name, :established_year, :terminated_year)"
            );
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':established_year', $data['established_year']);
            $this->db->bind(':terminated_year', $data['terminated_year']);
            $this->db->execute();
    
            $orgId = $this->db->lastInsertId();
    
            // Insert organization aliases if provided
            if (!empty($data['aliases']) && is_array($data['aliases'])) {
                foreach ($data['aliases'] as $alias) {
                    $this->db->query(
                        "INSERT INTO organization_aliases (org_id, name) 
                         VALUES (:org_id, :name)"
                    );
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':name', $alias);
                    $this->db->execute();
                }
            }
    
            // Insert organization description if provided (requires language)
            if (!empty($data['description']) && !empty($data['lang'])) {
                $this->db->query(
                    "INSERT INTO organization_description (org_id, lang, description) 
                     VALUES (:org_id, :lang, :description)"
                );
                $this->db->bind(':org_id', $orgId);
                $this->db->bind(':lang', $data['lang']);
                $this->db->bind(':description', $data['description']);
                $this->db->execute();
            }
    
            // Insert organization types if provided (array of type keys)
            if (!empty($data['types']) && is_array($data['types'])) {
                foreach ($data['types'] as $type) {
                    $this->db->query(
                        "INSERT INTO organization_types (org_id, type) 
                         VALUES (:org_id, :type)"
                    );
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':type', $type);
                    $this->db->execute();
                }
            }
    
            // Insert organization cities if provided (array of city IDs)
            if (!empty($data['cities']) && is_array($data['cities'])) {
                foreach ($data['cities'] as $cityId) {
                    $this->db->query(
                        "INSERT INTO organization_cities (org_id, city_id) 
                         VALUES (:org_id, :city_id)"
                    );
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':city_id', $cityId);
                    $this->db->execute();
                }
            }
    
            // Insert organization sources if provided (array of sources with title and url)
            if (!empty($data['sources']) && is_array($data['sources'])) {
                foreach ($data['sources'] as $source) {
                    $this->db->query(
                        "INSERT INTO organization_sources (org_id, title, url) 
                         VALUES (:org_id, :title, :url)"
                    );
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':title', $source['title']);
                    $this->db->bind(':url', $source['url']);
                    $this->db->execute();
                }
            }
    
            $this->db->commit();
            return [(int)$orgId];
    
        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }
    }
    
    /**
     * Update an existing organization.
     *
     * This method updates the main organization record and, for related data such as aliases,
     * description, types, cities, and sources, deletes the existing entries and reinserts the new data.
     *
     * @param array $data The updated organization data. Must include 'id' and optionally:
     *                    'name', 'established_year', 'terminated_year', 'aliases', 'description', 
     *                    'lang', 'types', 'cities', and 'sources'.
     * @return array Returns an array containing the updated organization ID.
     * @throws ApiException If any database operation fails.
     */
    public function updateOrganization(array $data): array
    {
        try {
            $this->db->begin();
    
            // Update main organization record
            $this->db->query(
                "UPDATE organizations SET 
                    name = :name, 
                    established_year = :established_year, 
                    terminated_year = :terminated_year 
                 WHERE id = :id"
            );
            $this->db->bind(':name', $data['name']);
            $this->db->bind(':established_year', $data['established_year']);
            $this->db->bind(':terminated_year', $data['terminated_year']);
            $this->db->bind(':id', $data['id']);
            $this->db->execute();
    
            $orgId = $data['id'];
    
            // Update aliases: Delete existing and reinsert new ones
            $this->db->query("DELETE FROM organization_aliases WHERE org_id = :org_id");
            $this->db->bind(':org_id', $orgId);
            $this->db->execute();
            if (!empty($data['aliases']) && is_array($data['aliases'])) {
                foreach ($data['aliases'] as $alias) {
                    $this->db->query("INSERT INTO organization_aliases (org_id, name) VALUES (:org_id, :name)");
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':name', $alias);
                    $this->db->execute();
                }
            }
    
            // Update description: Delete existing and reinsert if provided
            if (!empty($data['lang'])) {
                $this->db->query("DELETE FROM organization_description WHERE org_id = :org_id AND lang = :lang");
                $this->db->bind(':org_id', $orgId);
                $this->db->bind(':lang', $data['lang']);
                $this->db->execute();
                if (!empty($data['description'])) {
                    $this->db->query("INSERT INTO organization_description (org_id, lang, description) VALUES (:org_id, :lang, :description)");
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':lang', $data['lang']);
                    $this->db->bind(':description', $data['description']);
                    $this->db->execute();
                }
            }
    
            // Update organization types: Delete existing and reinsert new ones
            $this->db->query("DELETE FROM organization_types WHERE org_id = :org_id");
            $this->db->bind(':org_id', $orgId);
            $this->db->execute();
            if (!empty($data['types']) && is_array($data['types'])) {
                foreach ($data['types'] as $type) {
                    $this->db->query("INSERT INTO organization_types (org_id, type) VALUES (:org_id, :type)");
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':type', $type);
                    $this->db->execute();
                }
            }
    
            // Update organization cities: Delete existing and reinsert new ones
            $this->db->query("DELETE FROM organization_cities WHERE org_id = :org_id");
            $this->db->bind(':org_id', $orgId);
            $this->db->execute();
            if (!empty($data['cities']) && is_array($data['cities'])) {
                foreach ($data['cities'] as $cityId) {
                    $this->db->query("INSERT INTO organization_cities (org_id, city_id) VALUES (:org_id, :city_id)");
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':city_id', $cityId);
                    $this->db->execute();
                }
            }
    
            // Update organization sources: Delete existing and reinsert new ones
            $this->db->query("DELETE FROM organization_sources WHERE org_id = :org_id");
            $this->db->bind(':org_id', $orgId);
            $this->db->execute();
            if (!empty($data['sources']) && is_array($data['sources'])) {
                foreach ($data['sources'] as $source) {
                    $this->db->query("INSERT INTO organization_sources (org_id, title, url) VALUES (:org_id, :title, :url)");
                    $this->db->bind(':org_id', $orgId);
                    $this->db->bind(':title', $source['title']);
                    $this->db->bind(':url', $source['url']);
                    $this->db->execute();
                }
            }
    
            $this->db->commit();
            return [(int)$orgId];
        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }
    }

    /**
     * Updates the organization description for a specific organization and language.
     *
     * @param int $id The organization ID.
     * @param string $lang The language code.
     * @param string $description The new description.
     * @return array Returns an array containing the organization ID.
     * @throws ApiException If any database operation fails.
     */
    public function updateOrganizationDescription(int $id, string $lang, string $description): array
    {
        try {
            $this->db->begin();
            // Delete the existing description for this organization and language
            $this->db->query("DELETE FROM organization_description WHERE org_id = :id AND lang = :lang");
            $this->db->bind(':id', $id);
            $this->db->bind(':lang', $lang);
            $this->db->execute();

            // Insert new description if provided
            if (!empty($description)) {
                $this->db->query("INSERT INTO organization_description (org_id, lang, description) VALUES (:id, :lang, :description)");
                $this->db->bind(':id', $id);
                $this->db->bind(':lang', $lang);
                $this->db->bind(':description', $description);
                $this->db->execute();
            }
            $this->db->commit();
            return [(int)$id];
        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }
    }

    /**
     * Updates the organization aliases for a specific organization.
     *
     * @param int $id The organization ID.
     * @param array $aliases Array of aliases.
     * @return array Returns an array containing the organization ID.
     * @throws ApiException If any database operation fails.
     */
    public function updateOrganizationAlias(int $id, array $aliases): array
    {
        try {
            $this->db->begin();
            // Delete existing aliases
            $this->db->query("DELETE FROM organization_aliases WHERE org_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();
            // Insert new aliases if provided
            if (!empty($aliases)) {
                foreach ($aliases as $alias) {
                    $this->db->query("INSERT INTO organization_aliases (org_id, name) VALUES (:id, :alias)");
                    $this->db->bind(':id', $id);
                    $this->db->bind(':alias', $alias);
                    $this->db->execute();
                }
            }
            $this->db->commit();
            return [(int)$id];
        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }
    }

    /**
     * Updates the organization cities for a specific organization.
     *
     * @param int $id The organization ID.
     * @param array $cities Array of city IDs.
     * @return array Returns an array containing the organization ID.
     * @throws ApiException If any database operation fails.
     */
    public function updateOrganizationCity(int $id, array $cities): array
    {
        try {
            $this->db->begin();
            // Delete existing organization cities
            $this->db->query("DELETE FROM organization_cities WHERE org_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();
            // Insert new cities if provided
            if (!empty($cities)) {
                foreach ($cities as $cityId) {
                    $this->db->query("INSERT INTO organization_cities (org_id, city_id) VALUES (:id, :city_id)");
                    $this->db->bind(':id', $id);
                    $this->db->bind(':city_id', $cityId);
                    $this->db->execute();
                }
            }
            $this->db->commit();
            return [(int)$id];
        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }
    }

    /**
     * Updates the organization sources for a specific organization.
     *
     * @param int $id The organization ID.
     * @param array $sources Array of sources, each with keys 'title' and 'url'.
     * @return array Returns an array containing the organization ID.
     * @throws ApiException If any database operation fails.
     */
    public function updateOrganizationSource(int $id, array $sources): array
    {
        try {
            $this->db->begin();
            // Delete existing sources
            $this->db->query("DELETE FROM organization_sources WHERE org_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();
            // Insert new sources if provided
            if (!empty($sources)) {
                foreach ($sources as $source) {
                    $this->db->query("INSERT INTO organization_sources (org_id, title, url) VALUES (:id, :title, :url)");
                    $this->db->bind(':id', $id);
                    $this->db->bind(':title', $source['title']);
                    $this->db->bind(':url', $source['url']);
                    $this->db->execute();
                }
            }
            $this->db->commit();
            return [(int)$id];
        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }
    }

    private function convertAggregatedStringToObject(string $aggregatedString): object {
        $entries = explode(',', $aggregatedString);
        $result = [];
        foreach ($entries as $entry) {
            $entry = trim($entry);
            list($key, $value) = explode('::', $entry, 2);
            $result[$key] = $value;
        }
        return (object)$result;
    }    
}