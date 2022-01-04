<?php

class Airports {

	// table
	private $db;
    private $table = "airports";

    // object properties
    public $id;
    public $name;
    public $latitude;
    public $longitude;

    /**
     * Constructor with $db
     *
     * @param $db
     */
    public function __construct($db){
        $this->db = $db;
    }


    /**
     * Create airport
     *
     * @param $airport
     * @return array
     */
	public function create () {
        $prep = $this->db->prepare("INSERT INTO " . $this->table . "(name, latitude, longitude) VALUES(:name, :latitude, :longitude)");
        $prep->bindValue(':name', $this->name);
        $prep->bindValue(':latitude', $this->latitude, SQLITE3_FLOAT);
        $prep->bindValue(':longitude', $this->longitude, SQLITE3_FLOAT);
        $prep->execute();
        return $this->read();
	}

    /**
     * Read all airports
     *
     * @return array
     */
	public function read() {
        $query = $this->db->query('SELECT * FROM ' . $this->table);
        $jsonArray = array();
		while( $row = $query->fetchArray(SQLITE3_ASSOC)) {
            array_push($jsonArray, $row);
        }
        return $jsonArray;
	}

    /**
     * Update airport
     *
     * @return array
     */
	public function update() {
        $prep = $this->db->prepare('UPDATE ' . $this->table . ' SET name = :name, latitude = :latitude, longitude = :longitude WHERE id = :id');
        $prep->bindValue(':id', $this->id, SQLITE3_INTEGER);
        $prep->bindValue(':name', $this->name);
        $prep->bindValue(':latitude', $this->latitude, SQLITE3_FLOAT);
        $prep->bindValue(':longitude', $this->longitude, SQLITE3_FLOAT);
        $prep->execute();
        return $this->read();
	}

    /**
     * Delete airport
     *
     * @param $id
     * @return array
     */
	public function delete () {
        $prep = $this->db->prepare('DELETE FROM ' . $this->table . ' WHERE id = :id');
        $prep->bindValue(':id', $this->id, SQLITE3_INTEGER);
        $prep->execute();
        return $this->read();
	}
}

?>
