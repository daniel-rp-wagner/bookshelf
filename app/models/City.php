<?php

/**
 * Class City
 *
 * Model class for handling city-related database operations.
 */
class City
{
    /**
     * Database connection instance.
     *
     * @var Database
     */
    private Database $db;

    /**
     * City constructor.
     *
     * Initializes a new database connection instance.
     */
    public function __construct()
    {
        $this->db = new Database();
    }

    /**
     * Retrieve all cities.
     *
     * This method fetches all cities with optional filtering, sorting,
     * or pagination (if implemented).
     *
     * @param string $lang The language code for retrieving names.
     * @return array The result set as an array of cities.
     */
    public function getAllCities(string $lang, string $query): array
    {
        $this->db->query("SELECT 
                c.id,
                cn_official.name AS officialName,
                COALESCE(cn_display.name, cn_official.name) AS displayName,
                c.country_iso AS countryCode,
                CASE :lang
                    WHEN 'fr' THEN co.name_fr
                    WHEN 'de' THEN co.name_de
                    WHEN 'la' THEN co.name_la
                    ELSE co.name_de -- Standardfallback
                END AS country,
                c.type,
                COALESCE(pcn_display.name, pcn_official.name) AS parentCity
            FROM cities c
            JOIN countries co ON c.country_iso = co.iso_code
            LEFT JOIN city_names cn_official 
                ON cn_official.city_id = c.id 
                AND cn_official.language_code = 'on'
            LEFT JOIN city_names cn_display 
                ON cn_display.city_id = c.id 
                AND cn_display.language_code = :lang
            LEFT JOIN cities pc 
                ON c.parent_city_id = pc.id
            LEFT JOIN city_names pcn_official 
                ON pcn_official.city_id = pc.id 
                AND pcn_official.language_code = 'on'
            LEFT JOIN city_names pcn_display 
                ON pcn_display.city_id = pc.id 
                AND pcn_display.language_code = :lang
            ORDER BY officialName ASC" . $query);
        $this->db->bind(':lang', $lang);
        $this->db->execute();
        
        $result = $this->db->results();
        return is_array($result) ? $result : [];
    }

    /**
     * Retrieve a single city by its ID.
     *
     * @param int $id The city ID.
     * @param string $lang The language code for retrieving names.
     * @return array The result as an associative array.
     */
    public function getCityById(int $id, string $lang): array
    {
        $this->db->query("SELECT 
                c.id,
                cn_official.name AS officialName,
                COALESCE(cn_display.name, cn_official.name) AS displayName,
                c.country_iso AS countryCode,
                CASE :lang
                    WHEN 'fr' THEN co.name_fr
                    WHEN 'de' THEN co.name_de
                    WHEN 'la' THEN co.name_la
                    ELSE co.name_de -- Standardfallback
                END AS country,
                cc.latitude,
                cc.longitude,
                c.type,
                COALESCE(pcn_display.name, pcn_official.name) AS parentCity
            FROM cities c
            JOIN countries co ON c.country_iso = co.iso_code
            JOIN city_coordinates cc ON cc.city_id = c.id
            LEFT JOIN city_names cn_official 
                ON cn_official.city_id = c.id 
                AND cn_official.language_code = 'on'
            LEFT JOIN city_names cn_display 
                ON cn_display.city_id = c.id 
                AND cn_display.language_code = :lang
            LEFT JOIN cities pc 
                ON c.parent_city_id = pc.id
            LEFT JOIN city_names pcn_official 
                ON pcn_official.city_id = pc.id 
                AND pcn_official.language_code = 'on'
            LEFT JOIN city_names pcn_display 
                ON pcn_display.city_id = pc.id 
                AND pcn_display.language_code = :lang
            WHERE c.id = :id;");
        $this->db->bind(':lang', $lang);
        $this->db->bind(':id', $id);
        $this->db->execute();

        $result = $this->db->result();
        return is_array($result) ? $result : [];
    }

    /**
     * Delete a city by its ID.
     *
     * @param int $id The city ID.
     * @return bool True on success, false otherwise.
     */
    public function deleteCityById(int $id): bool
    {
        $this->db->query("DELETE FROM cities WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    /**
     * Create a new city.
     *
     * This method inserts a new city, its coordinates, and names into the database.
     *
     * @param array $data The data for the new city. Expected keys: 'id', 'country_iso', 'parent_city_id', 'type',
     *                    'coordinates' (associative array with 'latitude' and 'longitude'),
     *                    and 'names' (an array of arrays with keys 'language_code' and 'name').
     * @return array Returns an array containing the ID of the newly created city.
     * @throws Exception If any database operation fails.
     */
    public function createCity(array $data): array
    {
        try {
            $this->db->begin();

            $this->db->query("INSERT INTO cities (id, country_iso, parent_city_id, type) VALUES (:id, :country_iso, :parent, :type)");
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':country_iso', $data['country_iso']);
            $this->db->bind(':parent', $data['parent_city_id']);
            $this->db->bind(':type', $data['type']);
            $this->db->execute();
    
            $this->db->query("INSERT INTO city_coordinates (city_id, latitude, longitude) VALUES (:id, :latitude, :longitude)");
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':latitude', $data['coordinates']['latitude']);
            $this->db->bind(':longitude', $data['coordinates']['longitude']);
            $this->db->execute();
    
            foreach ($data['names'] as $nameEntry) {
                $this->db->query("INSERT INTO city_names (city_id, language_code, name) VALUES (:id, :language_code, :name)");
                $this->db->bind(':id', $data['id']);
                $this->db->bind(':language_code', $nameEntry['language_code']);
                $this->db->bind(':name', $nameEntry['name']);
                $this->db->execute();
            }
    
            $this->db->commit();

            return [$data['id']];

        } catch (Exception $e) {
            $this->db->rollback();
            // Optional: Fehler protokollieren oder erneut werfen
            exit;
        }

        
    }

    /**
     * Update an existing city.
     *
     * This method deletes the existing record and inserts new data for the city.
     *
     * @param array $data The updated data for the city. Expected keys are the same as in createCity().
     * @return array Returns an array containing the updated city ID.
     * @throws Exception If any database operation fails.
     */
    public function updateCity(array $data): array
    {
        $this->deleteCityById($data['id']);
        $this->createCity($data);

        return [$data['id']];
    }
}
