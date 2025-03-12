<?php

// Define a class named Book this will be the Book model
class City
{

    // Declare a private property to hold the database connection
    private $db;

    // Constructor method to initialize the database connection
    public function __construct()
    {
        // Create a new instance of the Database class and assign it to $db
        $this->db = new Database();
    }

    /**
     * Holt alle Orte (ggf. erweiterbar um Paginierung oder Filter).
     *
     * @return array
     */
    public function getAllCities($lang)
    {
        // Prepare a SQL query to select a record from the book table by ID
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
            ORDER BY officialName ASC");
        // Bind the id parameter to the query
        $this->db->bind(':lang', $lang);
        // Execute the prepared query
        $this->db->execute();
        // Return the result of the query
        return $this->db->results() ?? [];
    }

    /**
     * Holt einen Ort inklusive aller zugehörigen Daten anhand der ID.
     *
     * @param int $id
     * @return array
     */
    public function getCityById($id, $lang)
    {
        // Prepare a SQL query to select a record from the book table by ID
        $this->db->query("SELECT 
                c.id,
                cn_official.name AS officialName,
                COALESCE(cn_display.name, cn_official.name) AS displayName,
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
        // Bind the id parameter to the query
        $this->db->bind(':lang', $lang);
        $this->db->bind(':id', $id);
        // Execute the prepared query
        $this->db->execute();
        // Return the result of the query
        return $this->db->result() ?? [];
    }

    public function deleteCityById($id)
    {
        $this->db->query("DELETE FROM cities WHERE id = :id");
        $this->db->bind(':id', $id);

        // Execute the prepared query
        return $this->db->execute();
    }

    public function createCity($data)
    {
        $this->db->begin();

        $this->db->query("INSERT INTO cities (id, country_iso, parent_city_id, type) VALUES (:id, :country_iso, :parent, :type)");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':country_iso', $data['country_iso']);
        $this->db->bind(':parent', $data['parent_city_id']);
        $this->db->bind(':type', $data['type']);

        // Execute the prepared query
        $this->db->execute();

        $this->db->query("INSERT INTO city_coordinates (city_id, latitude, longitude) VALUES (:id, :latitude, :longitude)");
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':latitude', $data['coordinates']['latitude']);
        $this->db->bind(':longitude', $data['coordinates']['longitude']);

        // Execute the prepared query
        $this->db->execute();

        foreach($data['names'] as $names){
            $this->db->query("INSERT INTO city_names (city_id, language_code, name) VALUES (:id, :language_code, :name)");
            $this->db->bind(':id', $data['id']);
            $this->db->bind(':language_code', $names['language_code']);
            $this->db->bind(':name', $names['name']);

            // Execute the prepared query
            $this->db->execute();
        }

        $this->db->commit();

        return [$data['id']];
    }
}
