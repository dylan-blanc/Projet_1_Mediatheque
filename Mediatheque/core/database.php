<?php

/**
 * Gestionnaire de Base de Données
 * Fonctions d'accès à la base avec PDO
 */

// Gestion de la connexion

/**
 * Connexion singleton à la base de données
 */
function db_connect()
{
    // Variable statique pour maintenir la connexion entre les appels
    static $pdo = null;

    // Créer la connexion seulement si elle n'existe pas déjà
    if ($pdo === null) {
        try {
            // Construction du DSN (Data Source Name) pour MySQL
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

            // Options de configuration PDO pour la sécurité et performance
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,      // Exceptions en cas d'erreur
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Tableaux associatifs par défaut
                PDO::ATTR_EMULATE_PREPARES => false,              // Vraies requêtes préparées
            ];

            // Créer la connexion PDO
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Détermination d'un message adapté selon le type d'erreur
            $errorInfo  = $e->errorInfo ?? [];
            $sqlState   = $errorInfo[0] ?? ($e->getCode() ?: null); // ex: '28000', 'HY000'
            $driverCode = $errorInfo[1] ?? null;                    // ex: 1045, 1049, 2002, 2003, 1044

            $userMessage = 'Erreur de connexion à la base de données.';

            if ($driverCode === 1045 || $sqlState === '28000') {
                $userMessage .= ' Identifiants invalides (utilisateur ou mot de passe).';
            } elseif ($driverCode === 1049) {
                $userMessage .= ' Base de données introuvable. exportez la depuis database/creation-data, ou, Vérifiez la configuration. ;dbname= et DB_NAME';
            } elseif ($driverCode === 2002 || $driverCode === 2003) {
                $userMessage .= ' Serveur MySQL injoignable (hôte/port). Vérifiez la configuration mysql:host et DB_HOST';
            } elseif ($driverCode === 1044) {
                $userMessage .= ' Accès refusé à la base de données pour l’utilisateur configuré.';
            } elseif ($sqlState === 'HY000') {
                $userMessage .= ' Erreur interne MySQL (HY000).';
            } else {
                $userMessage .= ' Veuillez réessayer plus tard.';
            }

            // Journalisation détaillée côté serveur (sans exposer le mot de passe)
            error_log(sprintf(
                '[DB] Connexion échouée: SQLSTATE=%s, CODE=%s, MSG=%s, HOST=%s, DB=%s, USER=%s',
                $sqlState ?: 'n/a',
                $driverCode ?: 'n/a',
                $e->getMessage(),
                defined('DB_HOST') ? DB_HOST : 'n/a',
                defined('DB_NAME') ? DB_NAME : 'n/a',
                defined('DB_USER') ? DB_USER : 'n/a'
            ));

            // Sortie contrôlée
            die($userMessage);
        }
    }

    return $pdo;
}


function db_select($query, $params = [])
{
    $pdo = db_connect();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Alias pour db_select (compatibilité)
 */
function db_query($query, $params = [])
{
    return db_select($query, $params);
}

function db_select_all($query, $params = [])
{
    return db_select($query, $params);
}


function db_select_one($query, $params = [])
{
    $pdo = db_connect();
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch();
}


function db_execute($query, $params = [])
{
    $pdo = db_connect();
    $stmt = $pdo->prepare($query);
    return $stmt->execute($params);
}

/**
 * Retourne l'ID du dernier enregistrement inséré
 */
function db_last_insert_id()
{
    $pdo = db_connect();
    return $pdo->lastInsertId();
}

/**
 * Retourne la date/heure actuelle au format MySQL
 */
function db_now()
{
    return date('Y-m-d H:i:s');
}

/**
 * Commence une transaction
 */
function db_begin_transaction()
{
    $pdo = db_connect();
    return $pdo->beginTransaction();
}

/**
 * Valide une transaction
 */
function db_commit()
{
    $pdo = db_connect();
    return $pdo->commit();
}

/**
 * Annule une transaction
 */
function db_rollback()
{
    $pdo = db_connect();
    return $pdo->rollBack();
}
