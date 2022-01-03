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
     * @return boolean
     */
	public function create ( $airport ) {
        $prep = $this->db->prepare("INSERT INTO airports(name, latitude, longitude) VALUES(:name, :latitude, :longitude)");
        $prep->bindValue(':name', $airport->name);
        $prep->bindValue(':latitude', $airport->latitude, SQLITE3_FLOAT);
        $prep->bindValue(':longitude', $airport->longitude, SQLITE3_FLOAT);
        $prep->execute();
	}

    /**
     * Read all airports
     *
     * @return array
     */
	public function read() {
        $query = $this->db->query('SELECT * FROM airports');
        $jsonArray = array();
		while( $row = $query->fetchArray(SQLITE3_ASSOC)) {
            array_push($jsonArray, $row);
        }
        return $jsonArray;
	}

    /**
     * Update airport
     *
     * @return boolean
     */
	public function update( $id, $airport ) {
        $prep = $this->db->prepare('UPDATE airports SET name = :name, latitude = :latitude, longitude = :longitude WHERE id = :id');
        $prep->bindValue(':id', $id, SQLITE3_INTEGER);
        $prep->bindValue(':name', $airport->name);
        $prep->bindValue(':latitude', $airport->latitude, SQLITE3_FLOAT);
        $prep->bindValue(':longitude', $airport->longitude, SQLITE3_FLOAT);
        $prep->execute();
	}

    /**
     * Delete airport
     *
     * @param $id
     * @return boolean
     */
	public function delete ( $id ) {
        $prep = $this->db->prepare('DELETE FROM airports WHERE id = :id');
        $prep->bindValue(':id', $id, SQLITE3_INTEGER);
        $prep->execute();
	}
}

?>
