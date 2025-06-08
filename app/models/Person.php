<?php

/**
 * Class Person
 *
 * Model class for handling person-related database operations.
 */
class Person
{
    /**
     * Database connection instance.
     *
     * @var Database
     */
    private Database $db;

    /**
     * Person constructor.
     *
     * Initializes a new Database connection.
     */
    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Retrieves all persons with optional pagination.
     *
     * @param string $lang The language code for retrieving person data.
     * @param string $query SQL clause for pagination (e.g., LIMIT and OFFSET).
     * @return array List of persons.
     */
    public function getAllPersons(string $lang, string $query = '', string $filterTag = ''): array
    {
        $sql = "SELECT p.*,
                COALESCE(cn_display_birth.name, cn_official_birth.name) AS birth_city_name,
                COALESCE(cn_display_death.name, cn_official_death.name) AS death_city_name
            FROM persons p
            LEFT JOIN city_names cn_official_birth 
                ON cn_official_birth.city_id = p.birth_city_id 
                AND cn_official_birth.language_code = 'on'
            LEFT JOIN city_names cn_display_birth
                ON cn_display_birth.city_id = p.birth_city_id 
                AND cn_display_birth.language_code = :lang
            LEFT JOIN city_names cn_official_death 
                ON cn_official_death.city_id = p.death_city_id 
                AND cn_official_death.language_code = 'on'
            LEFT JOIN city_names cn_display_death 
                ON cn_display_death.city_id = p.death_city_id 
                AND cn_display_death.language_code = :lang WHERE 1=1";

        if ($filterTag !== '') {
            $sql .= " AND JSON_CONTAINS(p.tags, '\"{$filterTag}\"')";
        }
        $sql .= $query;

        $this->db->query($sql);
        $this->db->bind(':lang', $lang);
        $this->db->execute();
        $rows = $this->db->results();
        // JSON‐decode für jede Zeile
        return array_map(function($r){
            $r['tags'] = json_decode($r['tags'], true) ?: [];
            return $r;
        }, $rows);
    }

    /**
     * Retrieves a single person by its ID, including biographies (filtered by language),
     * aliases, professions (translated via the i8n table), and sources.
     *
     * @param int $id The person ID.
     * @param string $lang The language code for retrieving localized data.
     * @return array The combined person data as an associative array.
     */
    public function getPersonById(int $id, string $lang): array
    {
        // Stammdaten aus der Tabelle persons abrufen
        $this->db->query("SELECT p.*,
                COALESCE(cn_display_birth.name, cn_official_birth.name) AS birth_city_name,
                COALESCE(cn_display_death.name, cn_official_death.name) AS death_city_name
            FROM persons p
            LEFT JOIN city_names cn_official_birth 
                ON cn_official_birth.city_id = p.birth_city_id 
                AND cn_official_birth.language_code = 'on'
            LEFT JOIN city_names cn_display_birth
                ON cn_display_birth.city_id = p.birth_city_id 
                AND cn_display_birth.language_code = :lang
            LEFT JOIN city_names cn_official_death 
                ON cn_official_death.city_id = p.death_city_id 
                AND cn_official_death.language_code = 'on'
            LEFT JOIN city_names cn_display_death 
                ON cn_display_death.city_id = p.death_city_id 
                AND cn_display_death.language_code = :lang WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':lang', $lang);
        $this->db->execute();
        $person = $this->db->result();
        
        if (!$person) {
            throw new ApiException(404, 'NOT_FOUND', 'ID not found');
        }
        $person['tags'] = json_decode($person['tags'] ?? '[]', true);
        
        // Biografie abrufen (je nach Sprache)
        $this->db->query("SELECT bio FROM biographies WHERE person_id = :id AND lang = :lang");
        $this->db->bind(':id', $id);
        $this->db->bind(':lang', $lang);
        $this->db->execute();
        $bio = $this->db->result();
        $person['biography'] = $bio ? $bio['bio'] : '';

        // Aliase abrufen
        $this->db->query("SELECT name, type FROM person_aliases WHERE person_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        $person['aliases'] = $this->db->results();

        // Berufe (professions) abrufen – hier erfolgt ein Join zur i8n-Tabelle,
        // um den übersetzten Berufsnamen anhand des übergebenen Sprachcodes zu erhalten.
        $trans = $person['gender'] == 'M' ? 'i.translation' : 'i.translation_alt';

        $this->db->query("SELECT " .$trans. " AS profession 
                        FROM person_professions pp 
                        JOIN i8n i ON pp.profession = i.variable 
                        WHERE pp.person_id = :id AND i.lang = :lang");
        $this->db->bind(':id', $id);
        $this->db->bind(':lang', $lang);
        $this->db->execute();
        // Falls gewünscht, nur die Übersetzung extrahieren
        $professionsRaw = $this->db->results();
        $person['professions'] = array_map(function($row) {
            return $row['profession'];
        }, $professionsRaw);

        // Quellen (sources) abrufen
        $this->db->query("SELECT title, url FROM person_sources WHERE person_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        $person['sources'] = $this->db->results();

        // Werke laden
        $this->db->query("
            SELECT *
              FROM works w
             WHERE w.person_id = :id
        ");
        $this->db->bind(':id', $id);
        $this->db->execute();
        $person['works'] = $this->db->results();

        return $person;
    }


    /**
     * Deletes a person by ID.
     *
     * @param int $id The ID of the person to delete.
     * @return bool True on success, false otherwise.
     */
    public function deletePersonById(int $id): bool
    {
        $sql = "DELETE FROM persons WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        $this->db->execute();

        if($this->db->rowCount() > 0){
            return [];
        } else {
            throw new ApiException(404, 'NOT_FOUND', 'ID not found, nothing to delete');
        }
    }

    /**
     * Creates a new person record.
     *
     * This method inserts a new record into the persons table and, if provided in the $data array,
     * also inserts related records into person_aliases, biographies, person_professions, and person_sources.
     *
     * Expected $data keys:
     * - honorificPrefix (optional)
     * - first_name (required)
     * - nobility_particle (optional)
     * - last_name (required)
     * - religion (optional)
     * - birth_city_id (optional)
     * - death_city_id (optional)
     * - date_of_birth (optional)
     * - date_of_death (optional)
     * - nationality (optional)
     * - gender (optional)
     * - tags (optional)
     * - biography (optional; requires 'lang' key as well)
     * - lang (optional; required if biography is provided)
     * - aliases (optional; an array of either strings or associative arrays with keys: 'name' and optionally 'type')
     * - professions (optional; an array of profession keys)
     * - sources (optional; an array of associative arrays with keys 'title' and 'url')
     *
     * @param array $data The data for the new person.
     * @return array Returns an array containing the ID of the newly created person.
     * @throws ApiException If any database operation fails.
     */
    public function createPerson(array $data): array
    {
        try {
            $this->db->begin();
    
            // Insert the base record in the persons table
            $sql = "INSERT INTO persons (honorificPrefix, first_name, nobility_particle, last_name, religion, 
                        birth_city_id, death_city_id, date_of_birth, date_of_death, nationality, gender, tags)
                    VALUES (:honorificPrefix, :first_name, :nobility_particle, :last_name, :religion, 
                            :birth_city_id, :death_city_id, :date_of_birth, :date_of_death, :nationality, :gender, :tags)";
            $this->db->query($sql);
            $this->db->bind(':honorificPrefix', $data['honorificPrefix'] ?? null);
            $this->db->bind(':first_name', $data['first_name']);
            $this->db->bind(':nobility_particle', $data['nobility_particle'] ?? null);
            $this->db->bind(':last_name', $data['last_name']);
            $this->db->bind(':religion', $data['religion'] ?? null);
            $this->db->bind(':birth_city_id', $data['birth_city_id'] ?? null);
            $this->db->bind(':death_city_id', $data['death_city_id'] ?? null);
            $this->db->bind(':date_of_birth', $data['date_of_birth'] ?? null);
            $this->db->bind(':date_of_death', $data['date_of_death'] ?? null);
            $this->db->bind(':nationality', $data['nationality'] ?? null);
            $this->db->bind(':gender', $data['gender'] ?? null);
            $this->db->bind(':tags', $data['tags'] ?? null);
            $this->db->execute();
    
            // Get the last inserted ID
            $newId = $this->db->lastInsertId();
    
            // Insert aliases if provided
            if (isset($data['aliases']) && is_array($data['aliases'])) {
                foreach ($data['aliases'] as $alias) {
                    $this->db->query("INSERT INTO person_aliases (person_id, name, type) VALUES (:id, :name, :type)");
                    $this->db->bind(':id', $newId);
                    // Check if alias is an array (with potential 'name' and 'type') or a string
                    if (is_array($alias)) {
                        $this->db->bind(':name', $alias['name']);
                        $this->db->bind(':type', $alias['type'] ?? null);
                    } else {
                        $this->db->bind(':name', $alias);
                        $this->db->bind(':type', null);
                    }
                    $this->db->execute();
                }
            }
    
            // Insert biography if provided (requires 'lang' to be present)
            if (!empty($data['biography']) && isset($data['lang'])) {
                $this->db->query("INSERT INTO biographies (person_id, lang, bio) VALUES (:id, :lang, :bio)");
                $this->db->bind(':id', $newId);
                $this->db->bind(':lang', $data['lang']);
                $this->db->bind(':bio', $data['biography']);
                $this->db->execute();
            }
    
            // Insert professions if provided
            if (isset($data['professions']) && is_array($data['professions'])) {
                foreach ($data['professions'] as $profession) {
                    $this->db->query("INSERT INTO person_professions (person_id, profession) VALUES (:id, :profession)");
                    $this->db->bind(':id', $newId);
                    $this->db->bind(':profession', $profession);
                    $this->db->execute();
                }
            }
    
            // Insert sources if provided
            if (isset($data['sources']) && is_array($data['sources'])) {
                foreach ($data['sources'] as $source) {
                    $this->db->query("INSERT INTO person_sources (person_id, title, url) VALUES (:id, :title, :url)");
                    $this->db->bind(':id', $newId);
                    $this->db->bind(':title', $source['title']);
                    $this->db->bind(':url', $source['url']);
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
     * Updates an existing person record.
     *
     * @param array $data The updated data for the person.
     * @return array Returns an array containing the updated person ID.
     * @throws Exception If any database operation fails.
     */
    public function updatePerson(array $data): array
    {
        $this->db->begin();

        $sql = "UPDATE persons SET 
                    honorificPrefix = :honorificPrefix,
                    first_name = :first_name,
                    nobility_particle = :nobility_particle,
                    last_name = :last_name,
                    religion = :religion,
                    birth_city_id = :birth_city_id,
                    death_city_id = :death_city_id,
                    date_of_birth = :date_of_birth,
                    date_of_death = :date_of_death,
                    nationality = :nationality,
                    gender = :gender,
                    tags = :tags
                WHERE id = :id";
        $this->db->query($sql);
        $this->db->bind(':honorificPrefix', $data['honorificPrefix'] ?? null);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':nobility_particle', $data['nobility_particle'] ?? null);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':religion', $data['religion'] ?? null);
        $this->db->bind(':birth_city_id', $data['birth_city_id'] ?? null);
        $this->db->bind(':death_city_id', $data['death_city_id'] ?? null);
        $this->db->bind(':date_of_birth', $data['date_of_birth'] ?? null);
        $this->db->bind(':date_of_death', $data['date_of_death'] ?? null);
        $this->db->bind(':nationality', $data['nationality'] ?? null);
        $this->db->bind(':gender', $data['gender'] ?? null);
        $this->db->bind(':tags', $data['tags'] ?? null);
        $this->db->bind(':id', $data['id']);
        $this->db->execute();

        $this->db->query("DELETE FROM person_aliases WHERE person_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        if (!empty($aliases)) {
            foreach ($aliases as $alias) {
                $this->db->query("INSERT INTO person_aliases (person_id, name) VALUES (:id, :alias)");
                $this->db->bind(':id', $id);
                $this->db->bind(':alias', $alias);
                $this->db->execute();
            }
        }

        $this->db->query("DELETE FROM person_sources WHERE person_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        if (!empty($sources)) {
            foreach ($sources as $source) {
                $this->db->query("INSERT INTO person_sources (person_id, title, url) VALUES (:id, :title, :url)");
                $this->db->bind(':id', $id);
                $this->db->bind(':title', $source['title']);
                $this->db->bind(':url', $source['url']);
                $this->db->execute();
            }
        }

        $this->db->query("DELETE FROM biographies WHERE person_id = :id AND lang = :lang");
        $this->db->bind(':id', $id);
        $this->db->bind(':lang', $lang);
        $this->db->execute();

        if (!empty($biography)) {
            $this->db->query("INSERT INTO biographies (person_id, lang, bio) VALUES (:id, :lang, :biography)");
            $this->db->bind(':id', $id);
            $this->db->bind(':lang', $lang);
            $this->db->bind(':biography', $biography);
            $this->db->execute();
        }

        $this->db->commit();

        return [(int)$data['id']];
    }

    /**
     * Updates the person aliases.
     *
     * @param int $id The person ID.
     * @param array $aliases Array of aliases.
     * @return array Returns an array containing the person ID.
     * @throws ApiException If any database operation fails.
     */
    public function updatePersonAlias(int $id, array $aliases): array {
        try {
            $this->db->begin();
            $this->db->query("DELETE FROM person_aliases WHERE person_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            if (!empty($aliases)) {
                foreach ($aliases as $alias) {
                    $this->db->query("INSERT INTO person_aliases (person_id, name) VALUES (:id, :alias)");
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
     * Updates the person sources.
     *
     * @param int $id The person ID.
     * @param array $sources Array of source objects (each with 'title' and 'url').
     * @return array Returns an array containing the person ID.
     * @throws ApiException If any database operation fails.
     */
    public function updatePersonSource(int $id, array $sources): array {
        try {
            $this->db->begin();
            $this->db->query("DELETE FROM person_sources WHERE person_id = :id");
            $this->db->bind(':id', $id);
            $this->db->execute();

            if (!empty($sources)) {
                foreach ($sources as $source) {
                    $this->db->query("INSERT INTO person_sources (person_id, title, url) VALUES (:id, :title, :url)");
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

    /**
     * Updates the person biography.
     *
     * @param int $id The person ID.
     * @param string $lang The language code.
     * @param string $biography The new biography.
     * @return array Returns an array containing the person ID.
     * @throws ApiException If any database operation fails.
     */
    public function updatePersonBiography(int $id, string $lang, string $biography): array {
        try {
            $this->db->begin();
            // Delete the existing biography for this person and language.
            $this->db->query("DELETE FROM biographies WHERE person_id = :id AND lang = :lang");
            $this->db->bind(':id', $id);
            $this->db->bind(':lang', $lang);
            $this->db->execute();

            if (!empty($biography)) {
                $this->db->query("INSERT INTO biographies (person_id, lang, bio) VALUES (:id, :lang, :biography)");
                $this->db->bind(':id', $id);
                $this->db->bind(':lang', $lang);
                $this->db->bind(':biography', $biography);
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
     * Creates a new work for a person.
     *
     * @param int   $personId
     * @param array $data ['title'=>string, 'year'=>int|null]
     * @return int Newly created work ID
     * @throws ApiException on failure
     */
    public function createPersonWork(int $personId, array $data): int
    {
        try {
            $this->db->begin();
            $this->db->query("
                INSERT INTO works (person_id, title, year)
                VALUES (:person_id, :title, :year)
            ");
            $this->db->bind(':person_id', $personId);
            $this->db->bind(':title',      $data['title']);
            $this->db->bind(':year',       $data['year'] ?? null);
            $this->db->execute();

            $this->db->commit();
            return [$personId];
        } catch (Exception $e) {
            $this->db->rollback();
            throw new ApiException(500, 'DATABASE_ERROR', $e->getMessage());
        }
    }
}
