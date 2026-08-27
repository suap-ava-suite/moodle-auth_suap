<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     auth_suap
 * @copyright   2020 Kelson Medeiros <kelsoncm@gmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['auth_description'] = 'Authentification OAuth2';
$string['auth_suap_description'] = "SUAP est le Système unifié d'administration publique (Sistema Unificado de Administração Pública) utilisé dans les établissements fédéraux brésiliens, y compris l'Institut fédéral de Rio Grande do Norte (IFRN). Cette extension permet l'authentification unique (SSO), permettant aux étudiants et au personnel de se connecter à Moodle avec leurs identifiants SUAP. Elle synchronise automatiquement les données utilisateur depuis SUAP (nom, e-mail, CPF, statut d'inscription) et prend en charge le contrôle d'accès basé sur les rôles selon les données institutionnelles.";
$string['auth_token_error'] = "Une erreur s'est produite lors de la tentative d'authentification avec SUAP. Le code d'autorisation a peut-être expiré ou a déjà été utilisé.";
$string['auth_token_error_button'] = 'Relancer la connexion via SUAP';
$string['auth_token_error_title'] = "Échec de l'authentification SUAP";
$string['authorize_url'] = "Point d'accès d'autorisation OAuth2 de SUAP";
$string['authorize_url_desc'] = "URL d'autorisation OAuth2 de SUAP (généralement https://suap.ifrn.edu.br/o/authorize/)";
$string['bulk_updatepicture'] = 'Mettre à jour la photo (SUAP)';
$string['bulk_updatepicture_confirm'] = 'Planifier en arrière-plan la mise à jour de la photo des utilisateurs suivants, à partir des données SUAP déjà enregistrées (sans nouvelle connexion) ? Le traitement s\'exécute de manière asynchrone (tâche ad hoc via cron) et ne bloquera pas cet écran : {$a} ?';
$string['bulk_updatepicture_result'] = 'Terminé : {$a->enfileirados} utilisateur(s) disposant de données de photo SUAP ont eu une mise à jour en arrière-plan planifiée ; {$a->semdados} utilisateur(s) n\'avaient pas de données de photo enregistrées et ont été ignorés.';
$string['client_id'] = 'Identifiant client OAuth2';
$string['client_id_desc'] = "Obtenez-le depuis SUAP : Gestion de la technologie > Services > Applications OAuth2. Enregistrez votre instance Moodle avec le type d'autorisation « Code d'autorisation » (client public) et définissez l'URI de redirection sur : {$CFG->wwwroot}/auth/suap/authenticate.php";
$string['client_secret'] = 'Secret client OAuth2';
$string['client_secret_desc'] = "Ce secret ne s'affiche qu'une seule fois lors de la création de l'application OAuth2 dans SUAP. Enregistrez-le immédiatement, car il ne pourra plus être récupéré par la suite. Pour générer un nouveau secret, enregistrez une nouvelle application dans SUAP.";
$string['configincomplete'] = "Les configurations de l'extension SUAP (authorize_url, client_id, client_secret) sont incomplètes ou n'ont pas été enregistrées dans l'administration du site.";
$string['ensino_meus_dados_aluno_url'] = "Point d'accès de l'API Ensino/Meus Dados Aluno de SUAP";
$string['ensino_meus_dados_aluno_url_desc'] = "Point d'accès de l'API SUAP pour récupérer les données académiques de l'étudiant (généralement https://suap.ifrn.edu.br/api/ensino/meus-dados-aluno/)";
$string['eventpictureupdatefailed'] = "Échec de la mise à jour de la photo de l'utilisateur (SUAP)";
$string['logout_url'] = 'URL de déconnexion SUAP';
$string['logout_url_desc'] = "Point d'accès de déconnexion de SUAP pour mettre fin à la session (généralement https://suap.ifrn.edu.br/o/logout/)";
$string['name_source_order'] = 'Ordre de priorité de la source du nom affiché';
$string['name_source_order_desc'] = "Quel(s) champ(s) de nom SUAP utiliser comme source du nom complet dans Moodle, et dans quel ordre de priorité. Le nom de registre (nome_registro) est toujours utilisé en dernier recours, car c'est le seul dont l'existence est garantie.";
$string['name_source_order_registro'] = 'Nom de registre uniquement';
$string['name_source_order_social_registro'] = 'Nom social, puis nom de registre';
$string['name_source_order_social_usual_registro'] = 'Nom social, puis nom usuel, puis nom de registre';
$string['name_source_order_usual_registro'] = 'Nom usuel, puis nom de registre';
$string['name_source_order_usual_social_registro'] = 'Nom usuel, puis nom social, puis nom de registre';
$string['name_split_rule'] = 'Règle de découpage du nom';
$string['name_split_rule_desc'] = 'Comment diviser le nom complet choisi en prénom/nom de famille dans Moodle.';
$string['name_split_rule_first_last'] = 'Premier mot + dernier mot (les noms intermédiaires sont supprimés)';
$string['name_split_rule_first_rest'] = 'Premier mot + tout le reste';
$string['name_split_rule_firsts_last'] = 'Tout sauf le dernier mot + dernier mot';
$string['pluginname'] = 'Authentification OAuth2 SUAP';
$string['privacy:metadata:suap:cpf'] = "CPF de l'utilisateur (identifiant fiscal brésilien)";
$string['privacy:metadata:suap:email'] = 'Adresse e-mail';
$string['privacy:metadata:suap:explanation'] = "Cette extension communique avec le service externe SUAP pour l'authentification des utilisateurs et la synchronisation des données. Les informations de l'utilisateur, y compris le nom d'utilisateur, l'e-mail, le nom, le CPF et les informations de rôle, sont envoyées à SUAP lors de la connexion et des processus de synchronisation réguliers.";
$string['privacy:metadata:suap:firstname'] = "Prénom de l'utilisateur";
$string['privacy:metadata:suap:lastname'] = "Nom de famille de l'utilisateur";
$string['privacy:metadata:suap:tipo'] = "Type/rôle de l'utilisateur (étudiant, personnel, enseignant, etc.)";
$string['privacy:metadata:suap:username'] = "Nom d'utilisateur (identifiant institutionnel)";
$string['rh_eu_url'] = "Point d'accès de l'API RH/EU de SUAP";
$string['rh_eu_url_desc'] = "Point d'accès de l'API SUAP pour récupérer les données d'identification et les documents personnels de l'utilisateur (généralement https://suap.ifrn.edu.br/api/eu/)";
$string['rh_meus_dados_url'] = "Point d'accès de l'API RH/Meus Dados de SUAP";
$string['rh_meus_dados_url_desc'] = "Point d'accès de l'API SUAP pour récupérer les données personnelles et le lien principal de l'utilisateur (généralement https://suap.ifrn.edu.br/api/rh/meus-dados/)";
$string['rh_meus_vinculos_url'] = "Point d'accès de l'API RH/Meus Vínculos de SUAP";
$string['rh_meus_vinculos_url_desc'] = "Point d'accès de l'API SUAP pour récupérer la liste des liens de l'utilisateur (généralement https://suap.ifrn.edu.br/api/rh/meus-vinculos/)";
$string['suap:updatepicture'] = 'Mettre à jour les photos des utilisateurs SUAP (action groupée/tâche planifiée)';
$string['task_backfill_user_pictures'] = 'SUAP : compléter les photos manquantes des utilisateurs';
$string['task_sync_user_names'] = 'SUAP : synchroniser les noms affichés des utilisateurs (nom social/usuel/registre)';
$string['task_update_user_picture_adhoc'] = "SUAP : mettre à jour la photo d'un utilisateur (arrière-plan)";
$string['token_url'] = "Point d'accès de jeton SUAP";
$string['token_url_desc'] = "URL d'échange de jeton OAuth2 de SUAP (généralement https://suap.ifrn.edu.br/o/token/)";
