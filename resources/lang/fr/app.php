<?php

return [
    //:: General
    'app_name' => 'Construction Workforce',
    'home' => 'Accueil',
    'profiles' => 'Profils des travailleurs',
    'availability' => 'Disponibilités',
    'find_workers' => 'Trouver des travailleurs',
    'find_missions' => 'Trouver des missions',
    'dashboard' => 'Tableau de bord',
    'settings' => 'Paramètres',
    'logout' => 'Déconnexion',

    'common' => [
        'save' => 'Enregistrer',
        'cancel' => 'Annuler',
        'delete' => 'Supprimer',
        'close' => 'Fermer',
        'loading' => 'Chargement...',
        'not_available' => 'N/D',
        'unknown_company' => 'Entreprise inconnue',
        'administrator' => 'Administrateur',
        'self_employed' => 'Travailleur autonome',
        'per_hour' => '/heure',
        'no_message_provided' => 'Aucun message fourni.',
        'statuses' => [
            'pending' => 'En attente',
            'accepted' => 'Acceptée',
            'ongoing' => 'En cours',
            'completed' => 'Terminée',
            'ended_early' => 'Terminée plus tôt',
            'rejected' => 'Refusée',
            'cancelled' => 'Annulée',
            'draft' => 'Brouillon',
            'open' => 'Ouverte',
            'in_progress' => 'En cours',
        ],
    ],

    //:: Post Registration Onboarding
    'onboarding' => [
        'company' => [
            'title' => "Configuration de l’entreprise",
            'description' => 'Complétez le profil de votre entreprise pour commencer.',
            'name' => "Nom de l'entreprise",
            'phone' => 'Téléphone',
            'address' => 'Adresse',
            'create' => "Créer l'entreprise",
        ],
    ],

    'onboarding_page' => [
        'title' => 'Configuration initiale',
    ],

    //:: Auth
    'auth' => [

        // Login
        'login' => [
            'title' => 'Bon retour',
            'email' => 'Adresse e-mail',
            'password' => 'Mot de passe',
            'remember' => 'Se souvenir de moi',
            'forgot' => 'Mot de passe oublié ?',
            'submit' => 'Se connecter',
        ],

        // Register
        'register' => [
            'title' => 'Créer un compte',
            'name' => 'Nom complet',
            'email' => 'Adresse e-mail',
            'role' => 'Sélectionner un rôle',
            'role_placeholder' => 'Sélectionnez votre rôle',
            'password' => 'Mot de passe',
            'confirm_password' => 'Confirmer le mot de passe',
            'already_registered' => 'Déjà inscrit ?',
            'submit' => 'Créer un compte',
        ],

    ],

    //:: Welcome Page
    'welcome_page' => [
        'hero_highlight' => 'Mise en relation',
        'subtitle' => "Mise en relation rapide entre entreprises de construction et travailleurs disponibles. Trouvez la correspondance idéale pour votre prochaine mission en quelques minutes.",
        'description' => "Cette plateforme met en relation les entreprises disposant de travailleurs disponibles, celles qui recherchent de la main-d’œuvre qualifiée, ainsi que les travailleurs autonomes à la recherche de missions.",

        // Audience
        'audience_title' => "À qui s’adresse cette plateforme?",
        'audience_subtitle' => "Connexion de trois groupes clés de l’industrie de la construction",

        'audience' => [
            'employers' => [
                'title' => 'Entreprises avec des employés disponibles',
                'description' => "Listez vos travailleurs qualifiés lorsqu’ils sont disponibles entre deux projets. Transformez les périodes creuses en revenus.",
                'points' => [
                    'Optimiser l’utilisation de la main-d’œuvre',
                    'Générer des revenus supplémentaires',
                    'Gestion simplifiée des travailleurs',
                ],
            ],

            'hiring' => [
                'title' => 'Entreprises à la recherche de travailleurs qualifiés',
                'description' => "Trouvez instantanément des professionnels qualifiés du secteur de la construction. Comblez vos besoins en main-d’œuvre sans complications.",
                'points' => [
                    'Accès à des professionnels qualifiés',
                    'Processus d’embauche rapide',
                    'Missions flexibles à court terme',
                ],
            ],

            'contractors' => [
                'title' => 'Travailleurs autonomes',
                'description' => "Découvrez des missions instantanément. Développez votre réputation et suivez vos revenus en un seul endroit.",
                'points' => [
                    'Découvrez des missions instantanément',
                    'Développez votre réputation',
                    'Suivez vos revenus',
                ],
            ],
        ],

        // Badges
        'badges' => [
            'popular' => 'Populaire',
        ],

        // CTA
        'cta' => [
            'title' => 'Prêt à commencer?',
            'subtitle' => "Rejoignez des milliers de professionnels de la construction qui utilisent déjà notre plateforme",
            'action' => 'Créer un compte',
        ],

        // Actions
        'actions' => [
            'login' => 'Se connecter',
            'register' => 'Créer un compte',
        ],

        // Footer
        'footer_rights' => 'Tous droits réservés.',
    ],

    //:: Home Page
    'home_page' => [
        'title' => 'Accueil',

        'welcome' => 'Bienvenue, :name!',
        'welcome_subtitle' => 'Que souhaitez-vous faire aujourd’hui?',

        'stats' => [
            'ongoing_missions' => 'Missions en cours',
            'pending_requests' => 'Demandes en attente',
            'active_workers' => 'Travailleurs actifs',
            'total_missions' => 'Total des missions',
            'ongoing' => 'En cours',
            'pending' => 'En attente',
            'workers' => 'Travailleurs',
            'missions' => 'Missions',
        ],

        'actions' => [
            'make_available' => 'Rendre un employé disponible',
            'make_available_desc' => "Ajouter ou mettre à jour les disponibilités de vos travailleurs. Édition rapide en moins de 30 secondes.",
            'browse_missions' => 'Parcourir les missions & opportunités',
            'browse_missions_desc' => "Découvrez les missions et opportunités d’emploi disponibles. Trouvez votre prochain projet et postulez directement.",
            'search_worker' => 'Rechercher un travailleur',
            'search_worker_desc' => 'Parcourez les travailleurs disponibles par métier, expérience et compétences. Trouvez la correspondance parfaite pour votre mission.',
            'create_profile' => 'Créer votre profil',
            'create_profile_desc' => 'Présentez vos compétences et votre expérience pour être découvert par les entreprises et obtenir votre prochaine mission.',
            'edit_profile' => 'Modifier votre profil',
            'edit_profile_desc' => 'Mettez à jour vos compétences, votre expérience et vos disponibilités pour rester visible auprès des employeurs.',
        ],

        'quick_access' => 'Accès rapide',
        'mission_hub' => 'Centre des missions',
        'manage_profile' => 'Gérer mon profil',
        'view_all_missions' => 'Voir toutes les missions',
        'manage_profiles' => 'Gérer les profils',
        'manage_missions' => 'Gérer les missions',
        'manage_availability' => 'Gérer les disponibilités',
        'settings' => 'Paramètres',
    ],

    //:: Profiles Page
    'profiles_page' => [
        'title' => 'Profils des travailleurs',
        'subtitle' => 'Gérez les profils de vos travailleurs',
        'self_title' => 'Mon profil',
        'company_title' => 'Profils des travailleurs',
        'self_subtitle' => 'Consultez et gérez votre profil',
        'edit_profile' => 'Modifier le profil',
        'empty_title' => 'Aucun profil pour le moment',
        'empty_desc' => 'Créez votre profil pour commencer à être découvert par les entreprises.',
        'create_profile' => 'Créer votre profil',
        'company_subtitle' => 'Gérez les profils de vos travailleurs',
        'add_worker' => 'Ajouter un nouveau travailleur',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ce profil de travailleur ?',
        'empty_table' => 'Aucun travailleur ajouté pour le moment. Cliquez sur « :action » pour commencer.',
        'experience_years_short' => ':count ans',

        'labels' => [
            'certifications' => 'Certifications',
            'skills' => 'Compétences',
        ],

        'table' => [
            'name' => 'Nom',
            'job' => 'Poste',
            'experience' => 'Expérience',
            'rate' => 'Tarif',
            'rating' => 'Note',
            'skills' => 'Compétences',
            'actions' => 'Actions',
        ],

        'jobs' => [
            'general_labourer' => 'Manœuvre général',
            'electrician' => 'Électricien',
            'carpenter' => 'Charpentier',
            'plumber' => 'Plombier',
            'hvac_technician' => 'Technicien CVC',
            'heavy_equipment' => 'Opérateur de machinerie lourde',
            'welder' => 'Soudeur',
            'concrete_worker' => 'Ouvrier en béton',
            'roofer' => 'Couvreur',
            'painter' => 'Peintre',
            'mason' => 'Maçon',
            'ironworker' => "Monteur d’acier",
            'insulator' => 'Calorifugeur',
            'drywall_installer' => 'Installateur de cloisons sèches',
        ],

        'add_modal' => [
            'title' => 'Ajouter un profil de travailleur',
            'name' => 'Nom complet *',
            'company' => 'Entreprise (rempli automatiquement)',
            'job' => 'Emploi / Métier *',
            'job_select' => 'Sélectionnez un emploi ou un métier',
            'experience' => "Années d'expérience *",
            'rate' => 'Tarif horaire ($) *',
            'certifications' => 'Certifications (optionnel)',
            'certifications_placeholder' => 'Ajoutez plusieurs certifications en les séparant par des virgules',
            'skills' => 'Compétences *',
            'skills_placeholder' => 'Ajoutez plusieurs compétences en les séparant par des virgules',
            'saving' => 'Enregistrement...',
            'save' => 'Créer le profil',
            'cancel' => 'Annuler',
        ],

        'edit_modal' => [
            'title' => 'Modifier le profil du travailleur — :name',
            'updating' => 'Mise à jour...',
            'update' => 'Mettre à jour le profil',
            'save' => 'Mettre à jour le profil',
        ],

        'delete_modal' => [
            'title' => 'Supprimer le profil du travailleur',
            'message' => 'Supprimer définitivement ce profil de travailleur inutilisé ? Cette action est irréversible.',
            'action' => 'Supprimer',
            'confirm' => 'Oui, supprimer',
            'cancel' => 'Annuler',
        ],

        'archive_modal' => [
            'title' => 'Archiver le profil du travailleur',
            'message' => 'Archiver ce profil de travailleur ? Son historique de missions, de demandes et d’évaluations sera préservé.',
            'action' => 'Archiver',
            'confirm' => 'Oui, archiver',
            'cancel' => 'Annuler',
        ],

        'validation' => [
            'cannot_delete_worker' => 'Seuls les profils de travailleurs non archivés sans historique d’activité peuvent être supprimés définitivement.',
            'cannot_archive_worker' => 'Seuls les profils avec un historique résolu et sans travail ou demande active peuvent être archivés.',
            'cannot_edit_archived_worker' => 'Les profils de travailleurs archivés ne peuvent pas être modifiés.',
        ],
    ],

    //:: Availability Page
    'availability_page' => [
        'title' => 'Gestion des disponibilités',
        'subtitle' => 'Gérez les disponibilités de vos travailleurs',
        'subtitle_company' => 'Gérez les disponibilités de vos travailleurs',
        'subtitle_self' => 'Gérez vos disponibilités',
        'add_availability' => 'Ajouter une disponibilité',

        'calendar' => [
            'today' => 'Aujourd’hui',
            'previous' => 'Précédent',
            'next' => 'Suivant',
            'day' => 'Jour',
            'week' => 'Semaine',
            'month' => 'Mois',
            'loading' => 'Chargement du calendrier…',
            'load_error' => 'Impossible de charger les disponibilités du calendrier.',
            'worker_filter_label' => 'Travailleur',
            'all_workers' => 'Tous les travailleurs',
            'status_legend' => 'Légende des statuts de disponibilité',
            'no_workers' => 'Aucun travailleur n’est disponible pour la planification.',
            'no_slots' => 'Aucune disponibilité pour cette période.',
        ],

        'add_modal' => [
            'title' => 'Ajouter une disponibilité',
            'select_worker' => 'Sélectionner un travailleur',
            'worker' => 'Travailleur *',
            'date' => 'Date *',
            'start_time' => 'Heure de début *',
            'end_time' => 'Heure de fin *',
            'status' => 'Statut *',
            'saving' => 'Enregistrement...',
            'save' => 'Créer la disponibilité',
            'cancel' => 'Annuler',
        ],

        'status_options' => [
            'available' => 'Disponible',
            'booked' => 'Réservé',
            'unavailable' => 'Indisponible',
        ],

        'validation' => [
            'overlap' => 'Ce travailleur a déjà une disponibilité qui chevauche cette période.',
            'end_after_start' => 'L’heure de fin doit être postérieure à l’heure de début.',
            'mission_assignment_conflict' => 'Ce travailleur est déjà affecté à une mission à cette date.',
            'calendar_range_too_large' => 'La période du calendrier ne peut pas dépasser :days jours.',
            'worker_not_available' => 'Le travailleur sélectionné ne vous est pas accessible.',
            'archived_worker_cannot_receive_availability' => 'Les travailleurs archivés ne peuvent pas recevoir de nouvelles disponibilités ni de mises à jour.',
        ],

        'edit_modal' => [
            'title' => 'Modifier la disponibilité',
            'updating' => 'Mise à jour...',
            'update' => 'Enregistrer les modifications',
            'save' => 'Mettre à jour la disponibilité',
        ],

        'delete_modal' => [
            'title' => 'Supprimer la disponibilité',
            'message' => 'Êtes-vous sûr de vouloir supprimer cette disponibilité? Cette action est irréversible.',
            'confirm' => 'Oui, supprimer',
            'cancel' => 'Annuler',
            'item_name' => ':worker le :date',
        ],
    ],

    //:: Find Workers Page
    'find_workers_page' => [
        'title' => 'Trouver des travailleurs',
        'subtitle' => 'Parcourez et demandez des travailleurs disponibles pour vos missions',
        'workers_found' => 'travailleurs trouvés',
        'view_profile' => 'Voir le profil',
        'request' => 'Demander ce travailleur',
        'certifications' => 'Certifications',
        'top_skills' => 'Compétences principales',
        'no_certifications' => 'Aucune certification ajoutée',
        'experience_years_short' => ':count ans',
        'experience_years' => ':count ans',

        'filters' => [
            'search' => 'Rechercher par nom, métier, compétences ou certifications...',
            'job' => 'Tous les métiers',
        ],

        'profile_modal' => [
            'title' => 'Profil du travailleur',
            'experience' => 'Expérience',
            'rate' => 'Tarif',
            'certifications' => 'Certifications',
            'skills' => 'Compétences',
        ],

        'request_modal' => [
            'title' => 'Demander un travailleur pour une mission',

            'select_mission' => 'Sélectionner une mission *',

            'rate' => 'Tarif',
            'rating' => 'Note',

            'process_title' => 'Processus de demande',
            'steps' => [
                'step1_self' => 'Demande envoyée au travailleur',
                'step1_company' => "Demande envoyée à l’entreprise prêteuse",
                'self_employed' => 'Le travailleur peut accepter ou refuser',
                'company_worker' => 'Le propriétaire ou le responsable planification peut accepter ou refuser',
                'step3' => 'Numéro de téléphone débloqué après acceptation',
                'step4' => 'Mission confirmée automatiquement',
            ],

            'company' => 'Nom de votre entreprise',
            'start_date' => 'Date de début',
            'end_date' => 'Date de fin',
            'mission_desc' => 'Message facultatif (p. ex. tâches précises ou détails du projet)',
            'choose_mission' => 'Choisissez une mission pour ce travailleur',
            'already_requested' => 'Déjà demandée',
            'archived_worker_cannot_receive_request' => 'Les travailleurs archivés ne peuvent pas recevoir de nouvelles demandes.',
            'sending' => 'Envoi de la demande...',

            'send' => 'Envoyer la demande',
            'cancel' => 'Annuler',
        ],
    ],

    //:: Find Missions Page
    'find_missions_page' => [
        'title' => 'Trouver des missions',
        'subtitle' => 'Parcourez les occasions de mission disponibles et postulez',
        'missions_found' => 'missions trouvées',

        'filters' => [
            'search' => 'Rechercher par titre, entreprise ou exigences...',
            'job' => 'Tous les métiers',
            'location' => 'Tous les lieux',
        ],

        'mission_card' => [
            'duration' => 'Durée',
            'duration_day' => ':count jour',
            'duration_days' => ':count jours',
            'rate' => 'Tarif',
            'starts' => 'Débute le',
            'workers' => 'Travailleurs',
            'capacity' => ':committed / :required',
            'staffing_progress' => 'Travailleurs : :committed / :required',
            'remaining_capacity' => ':count restant',
            'requirements' => 'Exigences',
            'posted_by' => 'Publiée par',
            'request_join' => 'Demander à rejoindre',
            'applied' => 'Candidature envoyée',
            'invited' => 'Invitation reçue',
            'active_profile_required' => 'Un profil de travailleur actif est requis',
        ],

        'request_modal' => [
            'title' => 'Demander à rejoindre la mission',
            'select_worker' => 'Sélectionner un travailleur *',
            'worker' => 'Sélectionnez un travailleur',
            'no_matching_workers' => 'Aucun travailleur ne correspond au type de métier de cette mission.',
            'message' => 'Message facultatif (p. ex. compétences, expérience ou questions précises)',
            'info' => 'Les coordonnées seront accessibles après l’acceptation',
            'already_requested' => 'Déjà demandée',
            'already_requested_for_mission' => 'Ce travailleur a déjà une demande pour cette mission.',
            'sending' => 'Envoi de la candidature...',
            'send' => 'Envoyer la candidature',
            'cancel' => 'Annuler',
        ],

        'validation' => [
            'archived_worker_cannot_request' => 'Les travailleurs archivés ne peuvent pas soumettre de nouvelles candidatures.',
        ],

        'details_modal' => [
            'title' => 'Détails de la mission',
            'trade' => 'Métier',
            'description' => 'Description',
            'requirements' => 'Exigences',
            'operational_details' => 'Détails opérationnels',
            'site_name' => 'Nom du chantier',
            'address' => 'Adresse du chantier',
            'directions' => 'Instructions',
            'contact' => 'Personne-ressource du chantier',
        ],
    ],

    //:: My Missions Page
    'missions_page' => [
        'title' => 'Missions',
        'subtitle' => 'Créez, gérez et suivez les missions de votre entreprise',
        'create_mission' => 'Créer une mission',
        'empty_title' => 'Aucune mission pour le moment',
        'empty_desc' => 'Créez votre première mission pour trouver des travailleurs et répondre à vos besoins en main-d’œuvre.',
        'empty_tab_title' => 'Aucune mission :status trouvée',
        'empty_search_description' => 'Essayez de modifier votre recherche ou vos filtres.',
        'empty_tab_description' => 'Vous n’avez actuellement aucune mission :status.',
        'copy_title' => ':title (Copie :count)',

        'filters' => [
            'search' => 'Rechercher par titre, lieu ou exigences...',
            'status' => 'Tous les statuts',
        ],

        'tabs' => [
            'all' => 'Toutes',
        ],

        'labels' => [
            'date_range' => 'Du :start au :end',
            'workers_needed_singular' => ':count travailleur requis',
            'workers_needed_plural' => ':count travailleurs requis',
            'workers_needed_remaining' => 'Travailleurs requis : :workers — :remaining restants',
            'staffing_progress' => 'Travailleurs : :committed / :required',
            'remaining_capacity' => ':count restant',
            'recruiting' => 'Recrutement',
            'staffing_closed' => 'Recrutement fermé',
        ],

        'fallbacks' => [
            'no_description' => 'Aucune description fournie.',
        ],

        'actions' => [
            'edit' => 'Modifier',
            'view' => 'Voir',
        ],

        'table' => [
            'title' => 'Titre',
            'status' => 'Statut',
            'start_date' => 'Date de début',
            'end_date' => 'Date de fin',
            'city' => 'Ville',
            'staffing' => 'Dotation',
            'actions' => 'Actions',
        ],

        'stats' => [
            'total_missions' => 'Total des missions',
            'draft' => 'Brouillon',
            'open' => 'Ouverte',
            'in_progress' => 'En cours',
            'completed' => 'Terminée',
        ],

        'add_modal' => [
            'title' => 'Créer une mission',
            'mission_title' => 'Titre de la mission *',
            'description' => 'Description *',
            'start_date' => 'Date de début *',
            'end_date' => 'Date de fin *',
            'city' => 'Ville *',
            'province' => 'Province *',
            'country' => 'Pays *',
            'address_line_1' => 'Adresse, ligne 1',
            'address_line_2' => 'Adresse, ligne 2',
            'postal_code' => 'Code postal',
            'site_name' => 'Nom du chantier',
            'directions' => 'Instructions',
            'job_type' => 'Métier requis *',
            'number_of_workers' => 'Nombre de travailleurs requis',
            'hourly_rate' => 'Taux horaire ($)',
            'requirements' => 'Exigences',
            'requirements_placeholder' => 'Ajoutez plusieurs exigences en les séparant par des virgules',
            'status' => 'Statut *',
            'saving' => 'Enregistrement...',
            'save' => 'Créer la mission',
            'cancel' => 'Annuler',
        ],

        'edit_modal' => [
            'title' => 'Modifier la mission — :title',
            'save' => 'Mettre à jour la mission',
        ],

        'view_modal' => [
            'title' => 'Voir la mission — :title',
        ],

        'delete_modal' => [
            'title' => 'Supprimer la mission brouillon — :title',
            'message' => 'Supprimer cette mission brouillon?',
            'subtitle' => 'Cette action supprime définitivement le brouillon inutilisé.',
            'confirm' => 'Oui, supprimer',
        ],

        'archive_modal' => [
            'title' => 'Archiver la mission — :title',
            'message' => 'Archiver cette mission?',
            'subtitle' => 'Cette mission sera retirée de votre liste de missions habituelle, mais l’historique de dotation, des demandes, des évaluations et des travailleurs sera conservé.',
            'confirm' => 'Oui, archiver',
        ],

        'validation' => [
            'lifecycle_managed_status' => 'Le statut de cette mission est géré par le cycle de dotation et ne peut pas être modifié ici.',
            'open_mission_must_remain_open' => 'Une mission ouverte ne peut pas être remise à l’état de brouillon par une modification ordinaire.',
            'capacity_below_committed' => 'La capacité ne peut pas être réduite sous le nombre de travailleurs affectés (:count).',
            'cannot_delete_mission' => 'Seules les missions brouillon inutilisées et non archivées peuvent être supprimées définitivement.',
            'cannot_archive_mission' => 'Seules les missions terminées et non archivées peuvent être archivées.',
        ],
    ],

    //:: Mission Management Page
    'mission_management_page' => [
        'title' => 'Gestion des missions',
        'subtitle' => 'Votre espace central pour les demandes, l’activité des missions et le suivi de leur avancement.',
        'create_mission' => 'Créer une mission',

        'stats' => [
            'ongoing' => 'En cours',
            'pending' => 'En attente',
            'completed' => 'Terminées',
            'total' => 'Total',
        ],

        'tabs' => [
            'requests' => 'Demandes',
            'staffing' => 'Dotation',
            'in_progress' => 'En cours',
            'active' => 'Actives',
            'completed' => 'Terminées',
            'requests_sent' => 'Demandes envoyées',
            'requests_received' => 'Demandes reçues',
            'requests_join' => 'Demandes pour rejoindre des missions',
            'awaiting_response_invitations' => 'En attente de leur réponse — Invitations',
            'awaiting_response_applications' => 'En attente de leur réponse — Candidatures',
            'needs_your_response' => 'Votre réponse est requise',
            'invitations' => 'Invitations',
            'applications' => 'Candidatures',
            'assignments' => 'Affectations',
            'your_active_missions' => 'Vos missions actives',
            'external_assignments' => 'Affectations externes',
            'your_mission' => 'Votre mission',
            'external_assignment' => 'Affectation externe',
            'ongoing_missions' => 'Missions en cours',
            'completed_missions' => 'Missions terminées',
            'date' => 'Demandée le',
            'waiting_response' => 'En attente d’une réponse...',
            'accept' => 'Accepter',
            'reject' => 'Refuser',
        ],

        'sections' => [
            'completed_created' => 'Missions terminées que vous avez créées',
            'completed_joined' => 'Missions terminées auxquelles vous avez participé',
            'completed_assignments' => 'Affectations terminées',
            'pending_activity' => 'Activité en attente',
            'ongoing_activity' => 'Activité en cours',
            'completed_activity' => 'Activité terminée',
        ],

        'empty_states' => [
            'no_requests' => 'Aucune demande',
            'requests_description' => 'Les demandes liées à vos missions et travailleurs apparaîtront ici.',
            'self_employed_requests_description' => 'Les invitations et demandes de candidature apparaîtront ici.',
            'no_staffing_missions' => 'Aucune mission en dotation',
            'staffing_description' => 'Les missions avant leur début avec des travailleurs acceptés ou un recrutement fermé apparaîtront ici.',
            'no_assignments' => 'Aucune affectation',
            'assignments_description' => 'Les affectations acceptées qui n’ont pas encore commencé apparaîtront ici.',
            'no_in_progress_missions' => 'Aucune mission en cours',
            'in_progress_description' => 'Les affectations de missions actives apparaîtront ici.',
            'completed_description' => 'Les résultats des missions terminées apparaîtront ici.',
            'no_sent_requests' => 'Aucune demande envoyée',
            'sent_requests_description' => 'Les invitations de mission envoyées aux travailleurs apparaîtront ici.',
            'no_received_requests' => 'Aucune demande reçue',
            'received_requests_description' => 'Les candidatures et invitations de travailleurs apparaîtront ici.',
            'no_join_requests' => 'Aucune demande pour rejoindre une mission',
            'join_requests_description' => 'Les candidatures à des missions soumises par votre entreprise apparaîtront ici.',
            'no_active_missions' => 'Aucune mission active',
            'active_created_description' => 'Les travailleurs acceptés pour vos missions apparaîtront ici.',
            'active_joined_description' => 'Les missions externes auxquelles vos travailleurs ont participé apparaîtront ici.',
            'no_completed_missions' => 'Aucune mission terminée',
            'completed_created_description' => 'Les missions terminées de votre organisation apparaîtront ici.',
            'completed_joined_description' => 'Les missions externes terminées par vos travailleurs apparaîtront ici.',
            'no_activity' => 'Aucune activité de mission pour le moment',
            'activity_description' => 'Les demandes, missions en cours et travaux terminés apparaîtront ici.',
            'no_pending_activity' => 'Aucune activité en attente',
            'pending_activity_description' => 'Les demandes et missions en attente d’acceptation apparaîtront ici.',
            'no_ongoing_activity' => 'Aucune activité en cours',
            'ongoing_activity_description' => 'Les demandes et missions en cours apparaîtront ici.',
            'no_completed_activity' => 'Aucune activité terminée',
            'completed_activity_description' => 'Les demandes et missions terminées apparaîtront ici.',
        ],

        'labels' => [
            'mission' => 'Mission',
            'requests' => 'Demandes',
            'worker' => 'Travailleur',
            'assigned_workers' => 'Travailleurs affectés',
            'assignments' => 'Affectations',
            'worker_outcomes' => 'Résultats des travailleurs',
            'requested_worker' => 'Travailleur demandé',
            'proposed_worker' => 'Travailleur proposé',
            'assigned_worker' => 'Travailleur affecté',
            'requested_dates' => 'Dates demandées',
            'mission_dates' => 'Dates de la mission',
            'worker_rate' => 'Taux du travailleur',
            'rate' => 'Taux',
            'final_rate' => 'Taux final',
            'message' => 'Message',
            'message_sent' => 'Message envoyé',
            'message_received' => 'Message reçu',
            'application_message' => 'Message de candidature',
            'invitation_message' => 'Message d’invitation',
            'company_name' => 'Nom de l’entreprise',
            'company' => 'Entreprise',
            'company_owner' => 'Propriétaire de l’entreprise',
            'worker_offered' => 'Travailleur proposé',
            'reviewed_by' => 'Évalué par',
            'requested_on' => 'Demandée le',
            'accepted_on' => 'Acceptée le',
            'rejected_on' => 'Refusée le',
            'cancelled_on' => 'Annulée le',
            'completed_on' => 'Terminée le',
            'original_request' => 'Demande originale',
            'rejection' => 'Refus',
            'ended_on' => 'Terminée plus tôt le',
            'contact_company_owner' => 'Communiquer avec le propriétaire de l’entreprise',
            'worker_review' => 'Évaluation du travailleur',
            'staffing_progress' => 'Dotation',
            'capacity' => ':committed / :required',
            'remaining' => ':count restant',
            'recruiting' => 'Recrutement',
            'staffing_closed' => 'Recrutement fermé',
            'invitation' => 'Invitation',
            'application' => 'Candidature',
            'incoming' => 'Reçue',
            'outgoing' => 'Envoyée',
        ],

        'actions' => [
            'complete_and_rate' => 'Terminer et évaluer',
            'complete_mission' => 'Terminer la mission',
            'end_assignment' => 'Mettre fin à l’affectation',
            'end_assignment_and_rate' => 'Mettre fin à l’affectation et évaluer',
            'view_worker_profile' => 'Voir le profil du travailleur',
            'view_request_history' => 'Voir l’historique de la demande',
            'view_mission' => 'Voir la mission',
            'stop_recruiting' => 'Arrêter le recrutement',
            'start_mission' => 'Démarrer la mission',
        ],

        'roles' => [
            'administrator' => 'Administrateur',
            'company_owner' => 'Propriétaire de l’entreprise',
            'planning_manager' => 'Responsable de la planification',
            'self_employed' => 'Travailleur autonome',
        ],

        'states' => [
            'mission_accepted' => 'Mission acceptée.',
            'mission_completed' => 'Mission terminée avec succès.',
            'assignment_completed' => 'Affectation terminée avec succès.',
            'assignment_ended_early' => 'Affectation terminée plus tôt.',
        ],

        'success' => [
            'recruiting_closed' => 'Le recrutement a été fermé pour cette mission.',
            'mission_started' => 'La mission a été démarrée avec succès.',
            'assignment_completed' => 'Affectation terminée avec succès.',
            'assignment_ended_early' => 'Affectation terminée plus tôt avec succès.',
        ],

        'validation' => [
            'request_no_longer_pending' => 'Cette demande n’est plus en attente.',
            'recruiting_closed' => 'Cette mission n’accepte plus de travailleurs.',
            'mission_not_eligible_for_staffing' => 'Cette mission n’est pas admissible à l’ajout de travailleurs.',
            'worker_already_committed' => 'Ce travailleur est déjà affecté à cette mission.',
            'recruiting_already_closed' => 'Le recrutement est déjà fermé pour cette mission.',
            'recruiting_requires_committed_worker' => 'Au moins un travailleur doit être accepté avant de pouvoir fermer le recrutement.',
            'mission_not_ready_to_start' => 'Cette mission n’est pas prête à démarrer.',
            'mission_start_date_not_reached' => 'Cette mission ne peut pas démarrer avant sa date de début.',
            'mission_start_requires_committed_worker' => 'Au moins un travailleur affecté est requis pour démarrer cette mission.',
            'mission_not_in_progress' => 'Cette mission n’est pas actuellement en cours.',
            'assignment_not_ready_to_complete' => 'Cette affectation n’est pas prête à être terminée.',
            'assignment_not_ready_to_end_early' => 'Cette affectation ne peut pas être terminée plus tôt.',
            'mission_end_date_not_reached' => 'Cette affectation ne peut pas être terminée avant la date de fin de la mission.',
            'rating_already_exists' => 'Ce travailleur a déjà été évalué pour cette mission.',
        ],

        'fallbacks' => [
            'no_feedback' => 'Aucun commentaire n’a été fourni pour ce travailleur.',
            'pending_activity' => 'Activité de mission en attente.',
            'active_mission' => 'Mission actuellement active.',
        ],

        'rating' => [
            'score_given' => ':score/5 attribué',
            'score_received' => ':score/5 reçu',
        ],

        'response_modal' => [
            'accept_title' => 'Accepter la demande',
            'reject_title' => 'Refuser la demande',
            'accept_note' => ':contact sera avisé lorsque vous confirmerez cette demande.',
            'reject_note' => ':contact sera avisé lorsque vous confirmerez ce refus.',
            'acceptance_placeholder' => 'Ajoutez un message facultatif...',
            'rejection_placeholder' => 'Motif de refus facultatif...',
            'confirm_accept' => 'Confirmer l’acceptation',
            'confirm_reject' => 'Refuser la demande',
            'company_contact_fallback' => 'Équipe',
            'acceptance_message_self_employed' => "Bonjour :contact,\n\nVous avez été accepté pour la mission « :mission ».\n\nNous avons hâte de travailler avec vous. Merci!",
            'acceptance_message_company' => "Bonjour :contact,\n\n:worker a été accepté pour la mission « :mission ».\n\nNous avons hâte de travailler avec vous et votre équipe. Merci!",
            'rejection_message_self_employed' => "Bonjour :contact,\n\nMalheureusement, vous n’avez pas été sélectionné pour la mission « :mission ».\n\nMerci de votre intérêt.",
            'rejection_message_company' => "Bonjour :contact,\n\nMalheureusement, :worker n’a pas été sélectionné pour la mission « :mission ».\n\nMerci de votre intérêt.",
        ],

        'request_history_modal' => [
            'title' => 'Historique de la demande',
        ],

        'completion_modal' => [
            'title' => 'Terminer et évaluer le travailleur',
            'end_early_title' => 'Mettre fin à l’affectation et évaluer le travailleur',
            'rating_label' => 'Comment s’est déroulée votre expérience?',
            'comments_label' => 'Commentaires (facultatif)',
            'comments_placeholder' => 'Partagez votre expérience...',
        ],

        'recruiting_modal' => [
            'title' => 'Arrêter le recrutement',
            'message' => 'Voulez-vous arrêter le recrutement pour cette mission?',
            'subtitle' => 'Les demandes en attente seront annulées. Les travailleurs acceptés resteront affectés.',
            'confirm' => 'Arrêter le recrutement',
        ],

        'start_modal' => [
            'title' => 'Démarrer la mission',
            'message' => 'Voulez-vous démarrer cette mission maintenant?',
            'subtitle' => 'Les affectations des travailleurs acceptés passeront en cours.',
            'confirm' => 'Démarrer la mission',
        ],
    ],

    //:: Settings Page
    'settings_page' => [
        'title' => 'Paramètres',

        'personal' => [
            'title' => 'Informations personnelles',
            'subtitle' => 'Mettez à jour vos informations personnelles',
            'save_changes' => 'Enregistrer les modifications',
            'name' => 'Nom complet',
            'email' => 'Adresse e-mail',
            'phone' => 'Numéro de téléphone',
            'company' => "Nom de l'entreprise",
            'success' => 'Les informations personnelles ont été mises à jour.',
        ],

        'security' => [
            'title' => 'Mot de passe & Sécurité',
            'subtitle' => 'Gérez votre mot de passe et vos paramètres de sécurité',
            'change_password' => 'Changer le mot de passe',
            'hide' => 'Masquer',
            'current_password' => 'Mot de passe actuel',
            'new_password' => 'Nouveau mot de passe',
            'confirm_new_password' => 'Confirmer le nouveau mot de passe',
            'update_password' => 'Mettre à jour le mot de passe',
            'password_updated' => 'Mot de passe mis à jour.',
        ],

        'notifications' => [
            'title' => 'Notifications & Préférences',
            'subtitle' => "Personnalisez vos paramètres de notification",
            'email' => 'Notifications par e-mail',
            'sms' => 'Notifications par SMS',
            'missions' => 'Alertes de mission',
            'language' => 'Langue',
            'timezone' => 'Fuseau horaire',
            'save' => 'Enregistrer les préférences',
            'success' => 'Les préférences de notification ont été mises à jour.',
            'email_description' => 'Recevez les mises à jour par e-mail',
            'sms_description' => 'Recevez les mises à jour par SMS',
            'missions_description' => 'Recevez des notifications sur les nouvelles missions',
            'timezone_options' => [
                'utc' => 'UTC',
                'newfoundland' => 'Heure de Terre-Neuve',
                'atlantic' => 'Heure de l’Atlantique',
                'eastern' => 'Heure de l’Est',
                'central' => 'Heure du Centre',
                'saskatchewan' => 'Heure de la Saskatchewan',
                'mountain' => 'Heure des Rocheuses',
                'pacific' => 'Heure du Pacifique',
            ],
        ],

        'danger_zone' => [
            'title' => 'Zone de danger',
            'subtitle' => 'Désactiver l’accès à votre compte',
            'deactivate_account' => 'Désactiver le compte',
            'deactivate_modal_title' => 'Désactiver votre compte ?',
            'deactivate_modal_description' => 'Saisissez votre mot de passe pour désactiver votre compte. Vous serez déconnecté de tous vos appareils.',
            'password' => 'Mot de passe',
            'owner_cannot_deactivate' => 'Les propriétaires d’entreprise ne peuvent pas désactiver leur compte tant qu’ils possèdent une entreprise.',
        ],

        'common' => [
            'languages' => [
                'en' => 'Anglais',
                'fr' => 'Français',
            ],
        ],
    ],
];
