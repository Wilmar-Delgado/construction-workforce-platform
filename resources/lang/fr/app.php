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
        ],

        'quick_access' => 'Accès rapide',
        'view_all_missions' => 'Voir toutes les missions',
        'manage_profiles' => 'Gérer les profils',
        'manage_availability' => 'Gérer les disponibilités',
        'settings' => 'Paramètres',
    ],

    //:: Profiles Page
    'profiles_page' => [
        'title' => 'Profils des travailleurs',
        'subtitle' => 'Gérez les profils de vos travailleurs',
        'add_worker' => 'Ajouter un nouveau travailleur',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer ce profil de travailleur ?',

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
            'skills' => 'Compétences *',
            'save' => 'Créer le profil',
            'cancel' => 'Annuler',
        ],

        'edit_modal' => [
            'title' => 'Modifier le profil du travailleur',
            'save' => 'Mettre à jour le profil',
        ],
    ],

    //:: Availability Page
    'availability_page' => [
        'title' => 'Gestion des disponibilités',
        'subtitle' => 'Gérez les disponibilités de vos travailleurs',
        'add_availability' => 'Ajouter une disponibilité',
        'confirm_delete' => 'Êtes-vous sûr de vouloir supprimer cette entrée de disponibilité ?',

        'table' => [
            'worker' => 'Travailleur',
            'job' => 'Poste',
            'date' => 'Date',
            'time' => 'Heure',
            'status' => 'Statut',
            'actions' => 'Actions',
        ],

        'add_modal' => [
            'title' => 'Ajouter une disponibilité',
            'select_worker' => 'Sélectionner un travailleur',
            'worker' => 'Travailleur *',
            'date' => 'Date *',
            'start_time' => 'Heure de début *',
            'end_time' => 'Heure de fin *',
            'status' => 'Statut *',
            'save' => 'Créer la disponibilité',
            'cancel' => 'Annuler',
        ],

        'status_options' => [
            'available' => 'Disponible',
            'booked' => 'Réservé',
            'unavailable' => 'Indisponible',
        ],

        'edit_modal' => [
            'title' => 'Modifier la disponibilité',
            'save' => 'Mettre à jour la disponibilité',
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
            'mission_desc' => 'Description de la mission',

            'send' => 'Envoyer la demande',
            'cancel' => 'Annuler',
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
        ],

        'security' => [
            'title' => 'Mot de passe & Sécurité',
            'subtitle' => 'Gérez votre mot de passe et vos paramètres de sécurité',
            'change_password' => 'Changer le mot de passe',
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
        ],

        'danger_zone' => [
            'title' => 'Zone de danger',
            'subtitle' => 'Actions irréversibles et destructrices',
            'delete_account' => 'Supprimer le compte',
            'confirm' => 'Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible.',
        ],

        'common' => [
            'languages' => [
                'en' => 'Anglais',
                'fr' => 'Français',
            ],
        ],
    ],
];