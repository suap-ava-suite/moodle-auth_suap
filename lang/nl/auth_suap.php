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

$string['auth_description'] = 'OAuth2-authenticatie';
$string['auth_suap_description'] = 'SUAP is het Geüniformeerd Systeem voor Openbaar Bestuur (Sistema Unificado de Administração Pública) dat wordt gebruikt in Braziliaanse federale instellingen, waaronder het Federaal Instituut van Rio Grande do Norte (IFRN). Deze plugin maakt Single Sign-On (SSO) integratie mogelijk, zodat studenten en medewerkers kunnen inloggen op Moodle met hun SUAP-inloggegevens. Het synchroniseert automatisch gebruikersgegevens van SUAP (naam, e-mail, CPF, inschrijvingsstatus) en ondersteunt rolgebaseerde toegang op basis van institutionele gegevens.';
$string['auth_token_error'] = 'Er is een fout opgetreden bij het proberen te authenticeren met SUAP. De autorisatiecode is mogelijk verlopen of al gebruikt.';
$string['auth_token_error_button'] = 'Herstart inloggen via SUAP';
$string['auth_token_error_title'] = 'SUAP-authenticatiefout';
$string['authorize_url'] = 'SUAP-autorisatie-endpoint';
$string['authorize_url_desc'] = "SUAP OAuth2-autorisatie-URL (meestal https://suap.ifrn.edu.br/o/authorize/)";
$string['bulk_updatepicture'] = 'Foto bijwerken (SUAP)';
$string['bulk_updatepicture_confirm'] = 'Een foto-update op de achtergrond plannen voor de volgende gebruikers, op basis van de al opgeslagen SUAP-gegevens (geen nieuwe inlog vereist)? De verwerking wordt asynchroon uitgevoerd (een ad-hoctaak via cron) en blokkeert dit scherm niet: {$a}?';
$string['bulk_updatepicture_result'] = 'Voltooid: voor {$a->enfileirados} gebruiker(s) met SUAP-fotogegevens is een achtergrond-update gepland; {$a->semdados} gebruiker(s) hadden geen fotogegevens opgeslagen en zijn overgeslagen.';
$string['client_id'] = 'OAuth2 Client-ID';
$string['client_id_desc'] = "Verkrijg via SUAP: Technologiebeheer > Diensten > OAuth2-toepassingen. Registreer uw Moodle-instantie met autorisatietype 'Autorisatiecode' (openbare client) en stel de omleidings-URI in op: {$CFG->wwwroot}/auth/suap/authenticate.php";
$string['client_secret'] = 'OAuth2 Client Secret';
$string['client_secret_desc'] = "Dit geheim wordt slechts één keer getoond bij het aanmaken van de OAuth2-toepassing in SUAP. Sla het onmiddellijk op, want het kan later niet meer worden opgehaald. Registreer een nieuwe toepassing in SUAP om een nieuw geheim te genereren.";
$string['configincomplete'] = 'De SUAP-pluginconfiguraties (authorize_url, client_id, client_secret) zijn onvolledig of zijn niet opgeslagen in het sitebeheer.';
$string['ensino_meus_dados_aluno_url'] = 'SUAP Ensino/Meus Dados Aluno API-endpoint';
$string['ensino_meus_dados_aluno_url_desc'] = "SUAP API-endpoint voor het ophalen van academische gegevens van studenten (meestal https://suap.ifrn.edu.br/api/ensino/meus-dados-aluno/)";
$string['eventpictureupdatefailed'] = 'Mislukt bijwerken van foto (SUAP)';
$string['logout_url'] = 'SUAP Uitlog-URL';
$string['logout_url_desc'] = "SUAP uitlog-endpoint voor het beëindigen van de sessie (meestal https://suap.ifrn.edu.br/o/logout/)";
$string['name_source_order'] = 'Prioriteitsvolgorde voor weergavenaam';
$string['name_source_order_desc'] = 'Welke SUAP-naamveld(en) te gebruiken als bron voor de volledige Moodle-naam, en in welke prioriteitsvolgorde. De registratienaam (nome_registro) wordt altijd als laatste terugvaloptie gebruikt, omdat deze als enige gegarandeerd aanwezig is.';
$string['name_source_order_registro'] = 'Alleen registratienaam';
$string['name_source_order_social_registro'] = 'Sociale naam, daarna registratienaam';
$string['name_source_order_social_usual_registro'] = 'Sociale naam, daarna roepnaam, daarna registratienaam';
$string['name_source_order_usual_registro'] = 'Roepnaam, daarna registratienaam';
$string['name_source_order_usual_social_registro'] = 'Roepnaam, daarna sociale naam, daarna registratienaam';
$string['name_split_rule'] = 'Regel voor naamsplitsing';
$string['name_split_rule_desc'] = 'Hoe de gekozen volledige naam wordt gesplitst in voornaam/achternaam in Moodle.';
$string['name_split_rule_first_last'] = 'Eerste woord + laatste woord (tussenliggende namen worden weggelaten)';
$string['name_split_rule_first_rest'] = 'Eerste woord + al het overige';
$string['name_split_rule_firsts_last'] = 'Alles behalve het laatste woord + laatste woord';
$string['pluginname'] = 'SUAP OAuth2-authenticatie';
$string['privacy:metadata:suap:cpf'] = 'CPF van gebruiker (Braziliaans fiscaal nummer)';
$string['privacy:metadata:suap:email'] = 'E-mailadres';
$string['privacy:metadata:suap:explanation'] = 'Deze plugin communiceert met de externe SUAP-dienst voor gebruikersauthenticatie en gegevenssynchronisatie. Gebruikersinformatie zoals gebruikersnaam, e-mail, naam, CPF en rolinformatie wordt naar SUAP verzonden tijdens inloggen en regelmatige synchronisatieprocessen.';
$string['privacy:metadata:suap:firstname'] = 'Voornaam van gebruiker';
$string['privacy:metadata:suap:lastname'] = 'Achternaam van gebruiker';
$string['privacy:metadata:suap:tipo'] = 'Gebruikerstype/rol (student, medewerker, docent, etc.)';
$string['privacy:metadata:suap:username'] = 'Gebruikersnaam (institutioneel ID)';
$string['rh_eu_url'] = 'SUAP RH/EU API-endpoint';
$string['rh_eu_url_desc'] = "SUAP API-endpoint voor het ophalen van gebruikersidentificatie en persoonlijke documenten (meestal https://suap.ifrn.edu.br/api/eu/)";
$string['rh_meus_dados_url'] = 'SUAP RH/Meus Dados API-endpoint';
$string['rh_meus_dados_url_desc'] = "SUAP API-endpoint voor het ophalen van persoonlijke gegevens en primaire relatie van de gebruiker (meestal https://suap.ifrn.edu.br/api/rh/meus-dados/)";
$string['rh_meus_vinculos_url'] = 'SUAP RH/Meus Vínculos API-endpoint';
$string['rh_meus_vinculos_url_desc'] = "SUAP API-endpoint voor het ophalen van de lijst met relaties van de gebruiker (meestal https://suap.ifrn.edu.br/api/rh/meus-vinculos/)";
$string['suap:updatepicture'] = 'Foto\'s van SUAP-gebruikers bijwerken (massa-actie/geplande taak)';
$string['task_backfill_user_pictures'] = 'SUAP: ontbrekende gebruikersfoto\'s aanvullen';
$string['task_sync_user_names'] = 'SUAP: weergavenamen van gebruikers synchroniseren (nome social/usual/registro)';
$string['task_update_user_picture_adhoc'] = 'SUAP: foto van één gebruiker bijwerken (achtergrond)';
$string['token_url'] = 'SUAP Token-endpoint';
$string['token_url_desc'] = "SUAP OAuth2-tokenuitwisselings-URL (meestal https://suap.ifrn.edu.br/o/token/)";
