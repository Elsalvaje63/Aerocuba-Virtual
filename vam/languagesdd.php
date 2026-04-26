<?php
	/**
	 * @Project: Virtual Airlines Manager (VAM)
	 * @Author: Alejandro Garcia
	 * @Web http://virtualairlinesmanager.net
	 * Copyright (c) 2013 - 2016 Alejandro Garcia
	 * VAM is licenced under the following license:
	 *   Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International (CC BY-NC-SA 4.0)
	 *   View license.txt in the root, or visit http://creativecommons.org/licenses/by-nc-sa/4.0/
	 */
?>
<?php
// Conexión a PostgreSQL
$conn_string = "host=$db_host port=5432 dbname=$db_database user=$db_username password=$db_password";
$db = pg_connect($conn_string);

if (!$db) {
    die('Unable to connect to database');
}

// Consulta para obtener idiomas
$sql = "SELECT language_name, file_sufix FROM languages ORDER BY language_name ASC";
$result = pg_query($db, $sql);

if (!$result) {
    die('There was an error running the query: ' . pg_last_error($db));
}

$linklanguage = '';
$combolanguage = '';
while ($row = pg_fetch_assoc($result)) {
    $combolanguage .= " <option value='" . $row['file_sufix'] . "'>" . $row['language_name'] . "</option>";
    $linklanguage .= "<li><a href=index.php?lang=" . $row['file_sufix'] . ">" . $row['language_name'] . "</a></li>";
}
?>
