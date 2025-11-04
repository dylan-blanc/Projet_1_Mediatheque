<?php
/**
 * Modèle Evenement
 * Gestion des événements de la médiathèque
 */

// CRUD événements

/**
 * Récupère tous les événements (admin)
 */
function get_all_evenements() {
    $query = "SELECT e.*, m.titre AS media_titre, m.image AS media_image, 
              (SELECT image_path FROM evenement_images WHERE evenement_id = e.id LIMIT 1) AS event_image 
              FROM evenements e 
              LEFT JOIN medias m ON e.media_id = m.id 
              ORDER BY date_evenement ASC, id ASC";
    return db_select($query);
}

/**
 * Récupère un événement par son ID
 */
function get_evenement_by_id($id) {
    $query = "SELECT * FROM evenements WHERE id = ?";
    return db_select_one($query, [$id]);
}

/**
 * Ajoute un événement
 */
function create_evenement($data) {
    $query = "INSERT INTO evenements (titre, date_evenement, heure_evenement, description, media_id) VALUES (?, ?, ?, ?, ?)";
    return db_execute($query, [
        $data['titre'],
        $data['date_evenement'],
        $data['heure_evenement'],
        $data['description'],
        !empty($data['media_id']) ? $data['media_id'] : null
    ]) ? db_last_insert_id() : false;
}

/**
 * Met à jour un événement
 */
function update_evenement($id, $data) {
    $query = "UPDATE evenements SET titre = ?, date_evenement = ?, heure_evenement = ?, description = ?, media_id = ? WHERE id = ?";
    return db_execute($query, [
        $data['titre'],
        $data['date_evenement'],
        $data['heure_evenement'],
        $data['description'],
        !empty($data['media_id']) ? $data['media_id'] : null,
        $id
    ]);
}

/**
 * Supprime un événement
 */
function delete_evenement($id) {
    $query = "DELETE FROM evenements WHERE id = ?";
    return db_execute($query, [$id]);
}

/**
 * Ajoute une image à un événement
 */
function add_evenement_image($evenement_id, $image_path) {
    $query = "INSERT INTO evenement_images (evenement_id, image_path, created_at) VALUES (?, ?, NOW())";
    return db_execute($query, [$evenement_id, $image_path]);
}

/**
 * Récupère l'image d'un événement
 */
function get_evenement_image($evenement_id) {
    $query = "SELECT image_path FROM evenement_images WHERE evenement_id = ? LIMIT 1";
    $result = db_select_one($query, [$evenement_id]);
    return $result ? $result['image_path'] : null;
}

/**
 * Supprime l'image d'un événement
 */
function delete_evenement_image($evenement_id) {
    $query = "DELETE FROM evenement_images WHERE evenement_id = ?";
    return db_execute($query, [$evenement_id]);
}

/**
 * Met à jour l'image d'un événement (supprime l'ancienne et ajoute la nouvelle)
 */
function update_evenement_image($evenement_id, $image_path) {
    delete_evenement_image($evenement_id);
    return add_evenement_image($evenement_id, $image_path);
}
