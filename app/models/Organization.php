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
    public function getAllOrganizations(string $lang, string $query): array
    {
        $this->db->query("SELECT * FROM organizations" . $query);
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
    public function getOrganizationById(int $id, string $lang): array
    {
        $this->db->query("SELECT * FROM organizations WHERE id = :id");
        $this->db->bind(':id', $id);

        $this->db->execute();

        $org = $this->db->result();
        if (!$org) {
            return null;
        }

        $this->db->query("SELECT description FROM organization_description WHERE org_id = :id AND lang = :lang");
        $this->db->bind(':id', $id);
        $this->db->bind(':lang', $lang);

        $this->db->execute();

        $org['description'] = $this->db->result() ? $this->db->result()['description'] : '';

        $this->db->query("SELECT name FROM organization_aliases WHERE org_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();
        
        $org['aliases'] = [];
        foreach($this->db->results() as $name){
            array_push($org['aliases'], $name['name']);
        };

        // Städte laden
        $this->db->query("SELECT city_id FROM organization_cities WHERE org_id = :id");
        $this->db->bind(':id', $id);
        $this->db->execute();

        $org['cities'] = [];
        foreach($this->db->results() as $name){
            array_push($org['cities'], $name['city_id']);
        };

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
     * @return bool True on success, false otherwise.
     */
    public function deleteOrganizationById(int $id): bool
    {
        $this->db->query("DELETE FROM organizations WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

}