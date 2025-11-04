<?php

/**
 * Récupère la liste de tous les livres
 */
function get_all_books() {
    $query = "SELECT * FROM livre";
    return db_select($query);
}