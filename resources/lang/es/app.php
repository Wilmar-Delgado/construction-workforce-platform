<?php

return [
    //:: General
    'app_name' => 'Construction Workforce',
    'home' => 'Inicio',
    'profiles' => 'Trabajadores',
    'profile' => 'Mi perfil',
    'availability' => 'Disponibilidad',
    'find_workers' => 'Buscar trabajadores',
    'find_projects' => 'Buscar proyectos',
    'projects' => 'Mis proyectos',
    'project_management' => 'Gestión de proyectos',
    'settings' => 'Configuración',
    'self_employed' => 'Trabajador autónomo',
    'logout' => 'Cerrar sesión',

    'common' => [
        'save' => 'Guardar',
        'cancel' => 'Cancelar',
        'delete' => 'Eliminar',
        'close' => 'Cerrar',
        'loading' => 'Cargando...',
        'not_available' => 'N/A',
        'unknown_company' => 'Empresa desconocida',
        'administrator' => 'Administrador',
        'self_employed' => 'Trabajador autónomo',
        'per_hour' => '/hora',
        'no_message_provided' => 'No se proporcionó ningún mensaje.',
        'statuses' => [
            'pending' => 'Pendiente',
            'accepted' => 'Aceptada',
            'ongoing' => 'En curso',
            'completed' => 'Completada',
            'ended_early' => 'Finalizada antes de tiempo',
            'rejected' => 'Rechazada',
            'cancelled' => 'Cancelada',
            'draft' => 'Borrador',
            'open' => 'Abierta',
            'in_progress' => 'En curso',
        ],
        'validation' => [
            'company_context_required' => 'Se requiere el contexto de una empresa para esta acción.',
        ],
    ],

    //:: Post Registration Onboarding
    'onboarding' => [
        'company' => [
            'title' => 'Configuración de la empresa',
            'description' => 'Complete el perfil de su empresa para comenzar.',
            'name' => 'Nombre de la empresa',
            'phone' => 'Teléfono',
            'address' => 'Dirección',
            'create' => 'Crear empresa',
            'validation' => [
                'not_eligible' => 'No puede crear una empresa.',
            ],
        ],
    ],

    'onboarding_page' => [
        'title' => 'Configuración inicial',
    ],

    //:: Auth
    'auth' => [

        // Login
        'login' => [
            'title' => 'Bienvenido de nuevo',
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'remember' => 'Recordarme',
            'forgot' => '¿Olvidó su contraseña?',
            'submit' => 'Iniciar sesión',
        ],

        // Register
        'register' => [
            'title' => 'Crear cuenta',
            'name' => 'Nombre completo',
            'email' => 'Correo electrónico',
            'role' => 'Seleccionar rol',
            'role_placeholder' => 'Seleccione su rol',
            'company_owner' => 'Propietario de la empresa',
            'self_employed' => 'Trabajador autónomo',
            'password' => 'Contraseña',
            'confirm_password' => 'Confirmar contraseña',
            'already_registered' => '¿Ya está registrado?',
            'submit' => 'Registrarse',
        ],

        'forgot_password' => [
            'title' => 'Olvidó su contraseña',
            'description' => 'Introduzca su correo electrónico y le enviaremos un enlace para restablecer su contraseña.',
            'email' => 'Correo electrónico',
            'submit' => 'Enviar enlace para restablecer la contraseña',
        ],

        'reset_password' => [
            'title' => 'Restablecer contraseña',
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'confirm_password' => 'Confirmar contraseña',
            'submit' => 'Restablecer contraseña',
        ],

        'confirm_password' => [
            'title' => 'Confirmar contraseña',
            'description' => 'Esta es una zona segura de la aplicación. Confirme su contraseña antes de continuar.',
            'password' => 'Contraseña',
            'submit' => 'Confirmar',
        ],

        'verify_email' => [
            'title' => 'Verificación de correo electrónico',
            'description' => 'Gracias por registrarse. Antes de comenzar, verifique su correo electrónico mediante el enlace que le enviamos. Si no lo recibió, podemos enviar otro.',
            'link_sent' => 'Se ha enviado un nuevo enlace de verificación a la dirección de correo electrónico proporcionada durante el registro.',
            'resend' => 'Reenviar correo de verificación',
            'logout' => 'Cerrar sesión',
        ],

    ],

    //:: Welcome Page
    'welcome_page' => [
        'hero_highlight' => 'Conexión',
        'subtitle' => 'Comparte mano de obra entre empresas de construcción: sin tiempos muertos ni despidos. Pon a trabajar a los equipos disponibles o encuentra trabajadores calificados al instante cuando los necesites.',
        'description' => 'Esta plataforma conecta empresas de construcción con empleados disponibles, empresas con falta de personal que buscan trabajadores calificados y trabajadores autónomos que buscan proyectos a corto plazo.',

        // Audience
        'audience_title' => '¿Para quién es esta plataforma?',
        'audience_subtitle' => 'Conectamos a tres grupos clave de la industria de la construcción',

        'audience' => [
            'employers' => [
                'title' => 'Empresas con empleados disponibles',
                'description' => 'Publica a tus trabajadores calificados cuando estén disponibles entre proyectos. Convierte el tiempo de inactividad en ingresos.',
                'points' => [
                    'Maximiza el aprovechamiento de la mano de obra',
                    'Genera ingresos adicionales',
                    'Gestión sencilla de trabajadores',
                ],
            ],

            'hiring' => [
                'title' => 'Empresas que buscan trabajadores calificados',
                'description' => 'Encuentra profesionales de la construcción calificados al instante. Cubre tus necesidades de personal sin complicaciones.',
                'points' => [
                    'Accede a profesionales verificados',
                    'Proceso de contratación rápido',
                    'Proyectos flexibles a corto plazo',
                ],
            ],

            'contractors' => [
                'title' => 'Trabajadores autónomos',
                'description' => 'Descubre proyectos al instante. Desarrolla tu reputación y lleva el control de tus ingresos en un solo lugar.',
                'points' => [
                    'Descubre proyectos al instante',
                    'Desarrolla tu reputación',
                    'Lleva el control de tus ingresos',
                ],
            ],
        ],

        // Badges
        'badges' => [
            'popular' => 'Popular',
        ],

        // CTA
        'cta' => [
            'title' => '¿Listo para comenzar?',
            'subtitle' => 'Únete a miles de profesionales de la construcción que ya utilizan nuestra plataforma',
            'action' => 'Crear una cuenta',
        ],

        // Actions
        'actions' => [
            'login' => 'Iniciar sesión',
            'register' => 'Crear una cuenta',
        ],

        // Footer
        'footer_rights' => 'Todos los derechos reservados. Un producto de <a href="https://andromedasoftware.ca" target="_blank" rel="noopener noreferrer">Andromeda Software</a>.',
    ],

    //:: Home Page
    'home_page' => [
        'title' => 'Inicio',

        'welcome' => '¡Bienvenido, :name!',
        'welcome_subtitle' => '¿Qué te gustaría hacer hoy?',

        'stats' => [
            'ongoing_projects' => 'Proyectos en curso',
            'pending_requests' => 'Solicitudes pendientes',
            'active_workers' => 'Trabajadores activos',
            'total_projects' => 'Total de proyectos',
            'completed_projects' => 'Proyectos completados',
            'total_applications' => 'Total de solicitudes',
            'ongoing' => 'En curso',
            'pending' => 'Pendiente',
            'workers' => 'Trabajadores',
            'projects' => 'Proyectos',
        ],

        'actions' => [
            'make_available' => 'Marcar un empleado como disponible',
            'make_available_desc' => 'Agrega o actualiza los horarios de disponibilidad de tus trabajadores. Edición rápida en menos de 30 segundos.',

            'search_worker' => 'Buscar un trabajador',
            'search_worker_desc' => 'Explora trabajadores disponibles por oficio, experiencia y habilidades. Encuentra al candidato ideal para tu proyecto.',

            'create_profile' => 'Crear tu perfil',
            'create_profile_desc' => 'Muestra tus habilidades y experiencia para que las empresas te encuentren y consigas tu próximo proyecto.',

            'edit_profile' => 'Editar tu perfil',
            'edit_profile_desc' => 'Actualiza tus habilidades, experiencia y disponibilidad para mantenerte visible ante los empleadores.',

            'browse_projects' => 'Explorar proyectos y oportunidades',
            'browse_projects_desc' => 'Descubre proyectos y oportunidades laborales disponibles. Encuentra tu próximo proyecto y postúlate directamente.',
        ],

        'quick_access' => 'Acceso rápido',
        'project_hub' => 'Centro de proyectos',
        'manage_profile' => 'Gestionar perfil',
        'manage_profiles' => 'Gestionar perfiles',
        'manage_projects' => 'Gestionar proyectos',
        'view_all_projects' => 'Ver todos los proyectos',
        'manage_availability' => 'Gestionar disponibilidad',
        'settings' => 'Configuración',
    ],

    //:: Profiles Page
    'profiles_page' => [
        'title' => 'Trabajadores',
        'subtitle' => 'Gestiona los perfiles de tus trabajadores',
        'self_title' => 'Mi perfil',
        'company_title' => 'Trabajadores',
        'self_subtitle' => 'Consulta y gestiona tu perfil',
        'edit_profile' => 'Editar perfil',
        'empty_title' => 'Aún no hay perfil',
        'empty_desc' => 'Crea tu perfil para que las empresas puedan encontrarte.',
        'create_profile' => 'Crear tu perfil',
        'company_subtitle' => 'Gestiona los perfiles de tus trabajadores',
        'add_worker' => 'Agregar nuevo trabajador',
        'confirm_delete' => '¿Seguro que deseas eliminar este perfil de trabajador?',
        'empty_table' => 'Aún no se han agregado trabajadores. Haz clic en «:action» para comenzar.',
        'experience_years_short' => ':count años',

        'labels' => [
            'certifications' => 'Certificaciones',
            'skills' => 'Habilidades',
        ],

        'table' => [
            'name' => 'Nombre',
            'job' => 'Puesto / Oficio',
            'experience' => 'Experiencia',
            'rate' => 'Tarifa',
            'rating' => 'Calificación',
            'skills' => 'Habilidades',
            'actions' => 'Acciones',
        ],

        'jobs' => [
            'general_labourer' => 'Ayudante general',
            'electrician' => 'Electricista',
            'carpenter' => 'Carpintero',
            'plumber' => 'Plomero',
            'hvac_technician' => 'Técnico de HVAC',
            'heavy_equipment' => 'Operador de maquinaria pesada',
            'welder' => 'Soldador',
            'concrete_worker' => 'Trabajador de concreto',
            'roofer' => 'Techador',
            'painter' => 'Pintor',
            'mason' => 'Albañil',
            'ironworker' => 'Montador de estructuras de acero',
            'insulator' => 'Instalador de aislamiento',
            'drywall_installer' => 'Instalador de paneles de yeso',
        ],

        'add_modal' => [
            'title' => 'Agregar perfil de trabajador',
            'name' => 'Nombre completo *',
            'company' => 'Empresa (completada automáticamente)',
            'job' => 'Puesto / Oficio *',
            'job_select' => 'Selecciona un puesto u oficio',
            'experience' => 'Años de experiencia *',
            'rate' => 'Tarifa por hora ($) *',
            'certifications' => 'Certificaciones (opcional)',
            'certifications_placeholder' => 'Agrega varias certificaciones separándolas con comas',
            'skills' => 'Habilidades *',
            'skills_placeholder' => 'Agrega varias habilidades separándolas con comas',
            'saving' => 'Guardando...',
            'save' => 'Crear perfil',
            'cancel' => 'Cancelar',
        ],

        'edit_modal' => [
            'title' => 'Editar perfil de trabajador - :name',
            'updating' => 'Actualizando...',
            'update' => 'Actualizar perfil',
            'save' => 'Actualizar perfil',
        ],

        'delete_modal' => [
            'title' => 'Eliminar perfil de trabajador',
            'message' => '¿Eliminar permanentemente este perfil de trabajador sin uso? Esta acción no se puede deshacer.',
            'action' => 'Eliminar',
            'confirm' => 'Sí, eliminar',
            'cancel' => 'Cancelar',
        ],

        'archive_modal' => [
            'title' => 'Archivar perfil de trabajador',
            'message' => '¿Archivar este perfil de trabajador? Se conservará su historial de proyectos, solicitudes y calificaciones.',
            'action' => 'Archivar',
            'confirm' => 'Sí, archivar',
            'cancel' => 'Cancelar',
        ],

        'validation' => [
            'cannot_delete_worker' => 'Solo los perfiles de trabajadores no archivados y sin historial de actividad pueden eliminarse permanentemente.',
            'cannot_archive_worker' => 'Solo se pueden archivar perfiles con historial resuelto y sin trabajos ni solicitudes activas.',
            'cannot_edit_archived_worker' => 'Los perfiles de trabajadores archivados no se pueden editar.',
        ],

        'success' => [
            'created' => 'Perfil de trabajador creado correctamente.',
            'updated' => 'Perfil de trabajador actualizado correctamente.',
            'deleted' => 'Perfil de trabajador eliminado correctamente.',
            'archived' => 'Perfil de trabajador archivado correctamente.',
        ],
    ],

    //:: Availability Page
    'availability_page' => [
        'title' => 'Gestión de disponibilidad',
        'subtitle' => 'Gestiona la disponibilidad de tus trabajadores',
        'subtitle_company' => 'Gestiona la disponibilidad de tus trabajadores',
        'subtitle_self' => 'Gestiona tu disponibilidad',
        'add_availability' => 'Agregar franja horaria',

        'calendar' => [
            'today' => 'Hoy',
            'previous' => 'Anterior',
            'next' => 'Siguiente',
            'day' => 'Día',
            'week' => 'Semana',
            'month' => 'Mes',
            'loading' => 'Cargando calendario…',
            'load_error' => 'No se pudo cargar la disponibilidad del calendario.',
            'worker_filter_label' => 'Trabajador',
            'all_workers' => 'Todos los trabajadores',
            'status_legend' => 'Leyenda de estados de disponibilidad',
            'no_workers' => 'No hay trabajadores disponibles para programar.',
            'no_slots' => 'No hay franjas de disponibilidad en este período.',
        ],

        'status_options' => [
            'available' => 'Disponible',
            'booked' => 'Reservado',
            'unavailable' => 'No disponible',
        ],

        'validation' => [
            'overlap' => 'Este trabajador ya tiene una franja de disponibilidad que se superpone con este horario.',
            'end_after_start' => 'La hora de finalización debe ser posterior a la hora de inicio.',
            'project_assignment_conflict' => 'Este trabajador ya está asignado a un proyecto en esta fecha.',
            'calendar_range_too_large' => 'El intervalo del calendario no puede superar :days días.',
            'worker_not_available' => 'El trabajador seleccionado no está disponible para ti.',
            'archived_worker_cannot_receive_availability' => 'Los trabajadores archivados no pueden recibir nuevas disponibilidades ni actualizaciones.',
        ],

        'add_modal' => [
            'title' => 'Agregar disponibilidad',
            'select_worker' => 'Seleccionar trabajador',
            'worker' => 'Trabajador *',
            'date' => 'Fecha *',
            'start_time' => 'Hora de inicio *',
            'end_time' => 'Hora de finalización *',
            'status' => 'Estado *',
            'saving' => 'Guardando...',
            'save' => 'Agregar franja',
            'cancel' => 'Cancelar',
        ],
        
        'edit_modal' => [
            'title' => 'Editar disponibilidad',
            'updating' => 'Actualizando...',
            'update' => 'Guardar cambios',
            'save' => 'Guardar cambios',
        ],

        'delete_modal' => [
            'title' => 'Eliminar disponibilidad',
            'message' => '¿Seguro que deseas eliminar esta disponibilidad? Esta acción no se puede deshacer.',
            'confirm' => 'Sí, eliminar',
            'cancel' => 'Cancelar',
            'item_name' => ':worker el :date',
        ],

        'success' => [
            'created' => 'Disponibilidad agregada correctamente.',
            'updated' => 'Disponibilidad actualizada correctamente.',
            'deleted' => 'Disponibilidad eliminada correctamente.',
        ],
    ],

    //:: Find Workers Page
    'find_workers_page' => [
        'title' => 'Buscar trabajadores',
        'subtitle' => 'Explora y solicita trabajadores disponibles para tus proyectos',
        'workers_found' => 'trabajadores encontrados',
        'view_profile' => 'Ver perfil',
        'request' => 'Solicitar trabajador',
        'certifications' => 'Certificaciones',
        'top_skills' => 'Habilidades principales',
        'no_certifications' => 'No se agregaron certificaciones',
        'experience_years_short' => ':count años',
        'experience_years' => ':count años',

        'filters' => [
            'search' => 'Buscar por nombre, oficio, habilidades o certificaciones...',
            'job' => 'Todos los oficios',
        ],

        'profile_modal' => [
            'title' => 'Perfil del trabajador',
            'experience' => 'Experiencia',
            'rate' => 'Tarifa',
            'certifications' => 'Certificaciones',
            'skills' => 'Habilidades',
        ],

        'request_modal' => [
            'title' => 'Solicitar trabajador para un proyecto',

            'rate' => 'Tarifa',
            'rating' => 'Calificación',

            'process_title' => 'Proceso de solicitud',
            'steps' => [
                'step1_self' => 'Solicitud enviada al trabajador',
                'step1_company' => 'Solicitud enviada a la empresa proveedora',
                'self_employed' => 'El trabajador puede aceptar o rechazar',
                'company_worker' => 'La empresa (propietario o responsable de planificación) puede aceptar o rechazar',
                'step3' => 'El número de teléfono se habilita al aceptar',
                'step4' => 'El proyecto se confirma automáticamente',
            ],

            'select_project' => 'Seleccionar proyecto *',
            'company' => 'Nombre de tu empresa',
            'start_date' => 'Fecha de inicio',
            'end_date' => 'Fecha de finalización',
            'choose_project' => 'Elige un proyecto para este trabajador',
            'project_desc' => 'Mensaje opcional (p. ej., tareas específicas, detalles del proyecto, etc.)',
            'already_requested' => 'Ya solicitado',
            'archived_worker_cannot_receive_request' => 'Los trabajadores archivados no pueden recibir nuevas solicitudes.',
            'sending' => 'Enviando solicitud...',
            'send' => 'Enviar solicitud',
            'cancel' => 'Cancelar',
        ],

        'validation' => [
            'project_required' => 'Selecciona un proyecto.',
            'project_invalid' => 'El proyecto seleccionado no es válido.',
            'message_max' => 'El mensaje no puede superar los 1000 caracteres.',
            'worker_already_requested' => 'Ya solicitaste a este trabajador para este proyecto.',
        ],

        'success' => [
            'request_sent' => 'Solicitud enviada correctamente.',
        ],
    ],

    //:: Find Projects Page
    'find_projects_page' => [
        'title' => 'Buscar proyectos',
        'subtitle' => 'Explora los proyectos disponibles y postúlate',
        'projects_found' => 'proyectos encontrados',

        'filters' => [
            'search' => 'Buscar por título, empresa o requisitos...',
            'job' => 'Todos los oficios',
            'location' => 'Todas las ubicaciones',
        ],

        'project_card' => [
            'duration' => 'Duración',
            'duration_day' => ':count día',
            'duration_days' => ':count días',
            'rate' => 'Tarifa',
            'starts' => 'Comienza',
            'workers' => 'Trabajadores',
            'capacity' => ':committed / :required',
            'staffing_progress' => 'Trabajadores: :committed / :required',
            'remaining_capacity' => 'Quedan :count',
            'requirements' => 'Requisitos',
            'posted_by' => 'Publicado por',
            'request_join' => 'Solicitar unirse',
            'applied' => 'Solicitud enviada',
            'invited' => 'Invitado',
            'active_profile_required' => 'Se requiere un perfil de trabajador activo',
        ],

        'request_modal' => [
            'title' => 'Solicitar unirse a el proyecto',
            'select_worker' => 'Seleccionar trabajador *',
            'worker' => 'Seleccionar un trabajador',
            'no_matching_workers' => 'Ningún trabajador coincide con el oficio requerido para este proyecto.',
            'message' => 'Mensaje opcional (p. ej., habilidades específicas, experiencia, preguntas, etc.)',
            'info' => 'La información de contacto se habilitará al aceptar',
            'already_requested' => 'Ya solicitado',
            'already_requested_for_project' => 'Este trabajador ya tiene una solicitud para este proyecto.',
            'sending' => 'Enviando solicitud...',
            'send' => 'Enviar solicitud',
            'cancel' => 'Cancelar',
        ],

        'validation' => [
            'archived_worker_cannot_request' => 'Los trabajadores archivados no pueden enviar nuevas solicitudes a proyectos.',
            'worker_already_requested' => 'Este trabajador ya fue propuesto para este proyecto.',
        ],

        'success' => [
            'request_sent' => 'Solicitud para unirse a el proyecto enviada correctamente.',
        ],

        'details_modal' => [
            'title' => 'Detalles de el proyecto',
            'trade' => 'Oficio',
            'description' => 'Descripción',
            'requirements' => 'Requisitos',
            'operational_details' => 'Detalles operativos',
            'site_name' => 'Nombre del sitio',
            'address' => 'Dirección del sitio',
            'directions' => 'Indicaciones',
            'contact' => 'Contacto del sitio',
        ],
    ],

    //:: My Projects Page
    'projects_page' => [
        'title' => 'Proyectos',
        'subtitle' => 'Crea, gestiona y realiza el seguimiento de los proyectos de tu empresa',
        'create_project' => 'Crear proyecto',
        'empty_title' => 'Aún no hay proyectos',
        'empty_desc' => 'Crea tu primera proyecto para comenzar a encontrar trabajadores y cubrir tus necesidades de personal.',
        'empty_tab_title' => 'No se encontraron proyectos con estado :status',
        'empty_search_description' => 'Prueba ajustando la búsqueda o los filtros.',
        'empty_tab_description' => 'Actualmente no tienes proyectos con estado :status.',
        'copy_title' => ':title (Copia :count)',

        'filters' => [
            'search' => 'Buscar por título, ubicación o requisitos...',
            'status' => 'Todos los estados',
        ],

        'tabs' => [
            'all' => 'Todas',
        ],

        'labels' => [
            'date_range' => ':start a :end',
            'workers_needed_singular' => 'Se necesita :count trabajador',
            'workers_needed_plural' => 'Se necesitan :count trabajadores',
            'workers_needed_remaining' => 'Trabajadores necesarios: :workers — Quedan :remaining',
            'staffing_progress' => 'Trabajadores: :committed / :required',
            'remaining_capacity' => 'Quedan :count',
            'recruiting' => 'Reclutamiento',
            'staffing_closed' => 'Contratación cerrada',
        ],

        'fallbacks' => [
            'no_description' => 'No se proporcionó una descripción.',
        ],

        'actions' => [
            'edit' => 'Editar',
            'view' => 'Ver',
        ],

        'table' => [
            'title' => 'Título',
            'status' => 'Estado',
            'start_date' => 'Fecha de inicio',
            'end_date' => 'Fecha de finalización',
            'city' => 'Ciudad',
            'staffing' => 'Dotación de personal',
            'actions' => 'Acciones',
        ],

        'stats' => [
            'total_projects' => 'Total de proyectos',
            'draft' => 'Borrador',
            'open' => 'Abierta',
            'in_progress' => 'En curso',
            'completed' => 'Completado',
        ],

        'add_modal' => [
            'title' => 'Crear proyecto',
            'project_title' => 'Título de el proyecto *',
            'description' => 'Descripción *',
            'start_date' => 'Fecha de inicio *',
            'end_date' => 'Fecha de finalización *',
            'city' => 'Ciudad *',
            'province' => 'Provincia *',
            'country' => 'País *',
            'address_line_1' => 'Dirección - línea 1',
            'address_line_2' => 'Dirección - línea 2',
            'postal_code' => 'Código postal',
            'site_name' => 'Nombre del sitio',
            'directions' => 'Indicaciones',
            'job_type' => 'Oficio requerido *',
            'number_of_workers' => 'Número de trabajadores necesarios',
            'hourly_rate' => 'Tarifa por hora ($)',
            'requirements' => 'Requisitos',
            'requirements_placeholder' => 'Agrega varios requisitos separándolos con comas',
            'status' => 'Estado *',
            'saving' => 'Guardando...',
            'save' => 'Crear proyecto',
            'cancel' => 'Cancelar',
        ],

        'edit_modal' => [
            'title' => 'Editar proyecto - :title',
            'save' => 'Actualizar proyecto',
        ],

        'view_modal' => [
            'title' => 'Ver proyecto - :title',
        ],

        'delete_modal' => [
            'title' => 'Eliminar proyecto en borrador - :title',
            'message' => '¿Eliminar este proyecto en borrador?',
            'subtitle' => 'Esta acción elimina permanentemente el borrador sin uso.',
            'confirm' => 'Sí, eliminar',
        ],

        'archive_modal' => [
            'title' => 'Archivar proyecto - :title',
            'message' => '¿Archivar este proyecto?',
            'subtitle' => 'Este proyecto se eliminará de tu lista habitual, pero se conservará su historial de personal, solicitudes, calificaciones y trabajadores.',
            'confirm' => 'Sí, archivar',
        ],

        'validation' => [
            'lifecycle_managed_status' => 'El estado de este proyecto se gestiona mediante el ciclo de asignación de personal y no puede editarse aquí.',
            'open_project_must_remain_open' => 'Un proyecto abierto no puede volver al estado de borrador mediante la edición normal.',
            'capacity_below_committed' => 'La capacidad no puede reducirse por debajo de los :count trabajadores comprometidos.',
            'cannot_delete_project' => 'Solo se pueden eliminar permanentemente los proyectos en borrador, no archivados y sin uso.',
            'cannot_archive_project' => 'Solo se pueden archivar los proyectos completados y no archivadas.',
        ],

        'success' => [
            'created' => 'Proyecto creado correctamente.',
            'updated' => 'Proyecto actualizado correctamente.',
            'deleted' => 'Proyecto eliminado correctamente.',
            'archived' => 'Proyecto archivado correctamente.',
        ],
    ],

    //:: Project Management Page
    'project_management_page' => [
        'title' => 'Gestión de proyectos',
        'subtitle' => 'Tu centro para gestionar solicitudes, actividad de proyectos y seguimiento del progreso.',
        'create_project' => 'Crear proyecto',

        'stats' => [
            'ongoing' => 'En curso',
            'pending' => 'Pendiente',
            'completed' => 'Completado',
            'total' => 'Total',
        ],

        'tabs' => [
            'requests' => 'Solicitudes',
            'staffing' => 'Dotación de personal',
            'in_progress' => 'En curso',
            'active' => 'Activo',
            'completed' => 'Completado',
            'requests_sent' => 'Solicitudes enviadas',
            'requests_received' => 'Solicitudes recibidas',
            'requests_join' => 'Solicitudes para unirse a proyectos',
            'awaiting_response_invitations' => 'Esperando su respuesta — Invitaciones',
            'awaiting_response_applications' => 'Esperando su respuesta — Solicitudes',
            'needs_your_response' => 'Requiere tu respuesta',
            'invitations' => 'Invitaciones',
            'applications' => 'Solicitudes',
            'assignments' => 'Asignaciones',
            'your_active_projects' => 'Tus proyectos activos',
            'external_assignments' => 'Asignaciones externas',
            'your_project' => 'Tu proyecto',
            'external_assignment' => 'Asignación externa',
            'ongoing_projects' => 'Proyectos en curso',
            'completed_projects' => 'Proyectos completados',
            'date' => 'Solicitado el',
            'waiting_response' => 'Esperando respuesta...',
            'accept' => 'Aceptar',
            'reject' => 'Rechazar',
        ],

        'sections' => [
            'completed_created' => 'Proyectos completados que creaste',
            'completed_joined' => 'Proyectos completados a las que te uniste',
            'completed_assignments' => 'Asignaciones completadas',
            'pending_activity' => 'Actividad pendiente',
            'ongoing_activity' => 'Actividad en curso',
            'completed_activity' => 'Actividad completada',
        ],

        'empty_states' => [
            'no_requests' => 'No hay solicitudes',
            'requests_description' => 'Las solicitudes relacionadas con tus proyectos y trabajadores aparecerán aquí.',
            'self_employed_requests_description' => 'Las invitaciones y solicitudes aparecerán aquí.',
            'no_staffing_projects' => 'No hay proyectos en dotación',
            'staffing_description' => 'Los proyectos previos al inicio con trabajadores aceptados o reclutamiento cerrado aparecerán aquí.',
            'no_assignments' => 'No hay asignaciones',
            'assignments_description' => 'Las asignaciones aceptadas que aún no han comenzado aparecerán aquí.',
            'no_in_progress_projects' => 'No hay proyectos en curso',
            'in_progress_description' => 'Las asignaciones de proyectos activos aparecerán aquí.',
            'completed_description' => 'Los resultados de los proyectos completados aparecerán aquí.',
            'no_sent_requests' => 'No hay solicitudes enviadas',
            'sent_requests_description' => 'Las invitaciones a proyectos que envíes a trabajadores aparecerán aquí.',
            'no_received_requests' => 'No hay solicitudes recibidas',
            'received_requests_description' => 'Las solicitudes e invitaciones de trabajadores aparecerán aquí.',
            'no_join_requests' => 'No hay solicitudes para unirse',
            'join_requests_description' => 'Las solicitudes a proyectos enviadas por tu empresa aparecerán aquí.',
            'no_active_projects' => 'No hay proyectos activos',
            'active_created_description' => 'Los trabajadores aceptados para tus proyectos aparecerán aquí.',
            'active_joined_description' => 'Los proyectos externos a las que se unieron tus trabajadores aparecerán aquí.',
            'no_completed_projects' => 'No hay proyectos completados',
            'completed_created_description' => 'Los proyectos completados de tu organización aparecerán aquí.',
            'completed_joined_description' => 'Los proyectos externos completadas por tus trabajadores aparecerán aquí.',
            'no_activity' => 'Aún no hay actividad de proyectos',
            'activity_description' => 'Las solicitudes, proyectos en curso y trabajos completados aparecerán aquí.',
            'no_pending_activity' => 'No hay actividad pendiente',
            'pending_activity_description' => 'Las solicitudes pendientes y proyectos a la espera de aceptación aparecerán aquí.',
            'no_ongoing_activity' => 'No hay actividad en curso',
            'ongoing_activity_description' => 'Las solicitudes y proyectos en curso aparecerán aquí.',
            'no_completed_activity' => 'No hay actividad completada',
            'completed_activity_description' => 'Las solicitudes y proyectos completados aparecerán aquí.',
        ],

        'labels' => [
            'project' => 'Proyecto',
            'requests' => 'Solicitudes',
            'worker' => 'Trabajador',
            'assigned_workers' => 'Trabajadores asignados',
            'assignments' => 'Asignaciones',
            'worker_outcomes' => 'Resultados de los trabajadores',
            'requested_worker' => 'Trabajador solicitado',
            'proposed_worker' => 'Trabajador propuesto',
            'assigned_worker' => 'Trabajador asignado',
            'requested_dates' => 'Fechas solicitadas',
            'project_dates' => 'Fechas de el proyecto',
            'worker_rate' => 'Tarifa del trabajador',
            'rate' => 'Tarifa',
            'final_rate' => 'Tarifa final',
            'message' => 'Mensaje',
            'message_sent' => 'Mensaje enviado',
            'message_received' => 'Mensaje recibido',
            'application_message' => 'Mensaje de solicitud',
            'invitation_message' => 'Mensaje de invitación',
            'company_name' => 'Nombre de la empresa',
            'company' => 'Empresa',
            'company_owner' => 'Propietario de la empresa',
            'worker_offered' => 'Trabajador ofrecido',
            'reviewed_by' => 'Revisado por',
            'requested_on' => 'Solicitado el',
            'accepted_on' => 'Aceptado el',
            'rejected_on' => 'Rechazado el',
            'cancelled_on' => 'Cancelado el',
            'completed_on' => 'Completado el',
            'original_request' => 'Solicitud original',
            'rejection' => 'Rechazo',
            'ended_on' => 'Finalizado el',
            'contact_company_owner' => 'Contactar al propietario de la empresa',
            'worker_review' => 'Evaluación del trabajador',
            'staffing_progress' => 'Dotación de personal',
            'capacity' => ':committed / :required',
            'remaining' => 'Quedan :count',
            'recruiting' => 'Reclutamiento',
            'staffing_closed' => 'Contratación cerrada',
            'invitation' => 'Invitación',
            'application' => 'Solicitud',
            'incoming' => 'Entrante',
            'outgoing' => 'Saliente',
        ],

        'actions' => [
            'complete_and_rate' => 'Completar y calificar',
            'complete_project' => 'Completar proyecto',
            'end_assignment' => 'Finalizar asignación',
            'end_assignment_and_rate' => 'Finalizar asignación y calificar',
            'view_worker_profile' => 'Ver perfil del trabajador',
            'view_request_history' => 'Ver historial de la solicitud',
            'view_project' => 'Ver proyecto',
            'stop_recruiting' => 'Cerrar reclutamiento',
            'start_project' => 'Iniciar proyecto',
        ],

        'details_modal' => [
            'load_error' => 'No se pudieron cargar los detalles de el proyecto. Inténtalo de nuevo.',
        ],

        'worker_details_modal' => [
            'load_error' => 'No se pudieron cargar los detalles del trabajador. Inténtalo de nuevo.',
        ],

        'roles' => [
            'administrator' => 'Administrador',
            'company_owner' => 'Propietario de la empresa',
            'planning_manager' => 'Responsable de planificación',
            'self_employed' => 'Trabajador autónomo',
        ],

        'states' => [
            'project_accepted' => 'Proyecto aceptado.',
            'project_completed' => 'Proyecto completado correctamente.',
            'assignment_completed' => 'Asignación completada correctamente.',
            'assignment_ended_early' => 'La asignación finalizó antes de tiempo.',
        ],

        'success' => [
            'request_updated' => 'Solicitud actualizada correctamente.',
            'recruiting_closed' => 'Se ha cerrado el reclutamiento para este proyecto.',
            'project_started' => 'El proyecto comenzó correctamente.',
            'assignment_completed' => 'Asignación completada correctamente.',
            'assignment_ended_early' => 'La asignación finalizó antes de tiempo correctamente.',
        ],

        'validation' => [
            'request_no_longer_pending' => 'Esta solicitud ya no está pendiente.',
            'recruiting_closed' => 'Este proyecto ya no acepta trabajadores.',
            'project_not_eligible_for_staffing' => 'Este proyecto no admite más personal.',
            'worker_already_committed' => 'Este trabajador ya está comprometido con este proyecto.',
            'recruiting_already_closed' => 'El reclutamiento ya está cerrado para este proyecto.',
            'recruiting_requires_committed_worker' => 'Debe aceptarse al menos un trabajador antes de cerrar el reclutamiento.',
            'project_not_ready_to_start' => 'Este proyecto aún no está lista para comenzar.',
            'project_start_date_not_reached' => 'Este proyecto no puede comenzar antes de su fecha de inicio.',
            'project_start_requires_committed_worker' => 'Se requiere al menos un trabajador comprometido para iniciar este proyecto.',
            'project_not_in_progress' => 'Este proyecto no está actualmente en curso.',
            'assignment_not_ready_to_complete' => 'Esta asignación aún no está lista para completarse.',
            'assignment_not_ready_to_end_early' => 'Esta asignación aún no está lista para finalizar antes de tiempo.',
            'project_end_date_not_reached' => 'Esta asignación no puede completarse antes de la fecha de finalización de el proyecto.',
            'rating_already_exists' => 'Este trabajador ya fue calificado para este proyecto.',
        ],

        'fallbacks' => [
            'no_feedback' => 'No se proporcionaron comentarios sobre este trabajador.',
            'pending_activity' => 'Actividad de proyecto pendiente.',
            'active_project' => 'Proyecto actualmente activo.',
        ],

        'rating' => [
            'score_given' => ':score/5 otorgada',
            'score_received' => ':score/5 recibida',
        ],

        'response_modal' => [
            'accept_title' => 'Aceptar solicitud',
            'reject_title' => 'Rechazar solicitud',
            'accept_note' => 'Se notificará a :contact cuando confirmes esta solicitud.',
            'reject_note' => 'Se notificará a :contact cuando confirmes este rechazo.',
            'acceptance_placeholder' => 'Agrega un mensaje opcional...',
            'rejection_placeholder' => 'Motivo de rechazo opcional...',
            'confirm_accept' => 'Confirmar aceptación',
            'confirm_reject' => 'Rechazar solicitud',
            'company_contact_fallback' => 'Equipo',
            'acceptance_message_self_employed' => "Hola :contact,\\n\\nHas sido aceptado para el proyecto “:proyecto”.\\n\\nEsperamos trabajar contigo. ¡Gracias!",
            'acceptance_message_company' => "Hola :contact,\\n\\n:worker ha sido aceptado para el proyecto “:proyecto”.\\n\\nEsperamos trabajar contigo y con tu equipo. ¡Gracias!",
            'rejection_message_self_employed' => "Hola :contact,\\n\\nLamentablemente, no has sido seleccionado para el proyecto “:proyecto”.\\n\\nGracias por tu interés.",
            'rejection_message_company' => "Hola :contact,\\n\\nLamentablemente, :worker no ha sido seleccionado para el proyecto “:proyecto”.\\n\\nGracias por tu interés.",
        ],

        'request_history_modal' => [
            'title' => 'Historial de la solicitud',
        ],

        'completion_modal' => [
            'title' => 'Completar y calificar al trabajador',
            'end_early_title' => 'Finalizar asignación y calificar al trabajador',
            'rating_label' => '¿Cómo fue tu experiencia?',
            'comments_label' => 'Comentarios (opcional)',
            'comments_placeholder' => 'Comparte tu experiencia...',
        ],

        'recruiting_modal' => [
            'title' => 'Cerrar reclutamiento',
            'message' => '¿Cerrar el reclutamiento para este proyecto?',
            'subtitle' => 'Las solicitudes pendientes se cancelarán. Los trabajadores aceptados permanecerán asignados.',
            'confirm' => 'Cerrar reclutamiento',
        ],

        'start_modal' => [
            'title' => 'Iniciar proyecto',
            'message' => '¿Iniciar este proyecto ahora?',
            'subtitle' => 'Las asignaciones de trabajadores aceptados pasarán al estado en curso.',
            'confirm' => 'Iniciar proyecto',
        ],
    ],

    //:: Settings Page
    'company_team' => [
        'title' => 'Equipo de la empresa',
        'subtitle' => 'Gestiona los responsables de planificación de :company.',
        'add_planning_manager' => 'Agregar responsable de planificación',
        'pending_invitations' => 'Invitaciones pendientes',
        'pending' => 'Pendiente',
        'expires_at' => 'Vence el :date',
        'cancel_invitation' => 'Cancelar invitación',
        'remove' => 'Eliminar',
        'cancel' => 'Cancelar',
        'roles' => [
            'company_owner' => 'Propietario de la empresa',
            'planning_manager' => 'Responsable de planificación',
        ],
        'invite_modal' => [
            'title' => 'Invitar a un responsable de planificación',
            'name' => 'Nombre completo',
            'email' => 'Correo electrónico',
            'send' => 'Enviar invitación',
        ],
        'cancel_invitation_modal' => [
            'title' => '¿Cancelar esta invitación?',
            'message' => 'Este enlace de invitación dejará de ser válido.',
        ],
        'remove_member_modal' => [
            'title' => '¿Eliminar a este responsable de planificación?',
            'message' => 'Esto elimina el acceso a la empresa y desactiva la cuenta. Se conservarán los registros históricos.',
        ],
        'accept' => [
            'title' => 'Unirse al equipo de la empresa',
            'subtitle' => 'Establece una contraseña para unirte a :company como responsable de planificación.',
            'name' => 'Nombre',
            'email' => 'Correo electrónico',
            'password' => 'Contraseña',
            'password_confirmation' => 'Confirmar contraseña',
            'submit' => 'Aceptar invitación',
        ],
        'email' => [
            'subject' => 'Has sido invitado a unirte al equipo de una empresa',
            'heading' => 'Has sido invitado a unirte a :company',
            'introduction' => ':inviter te invitó a unirte al equipo de su empresa.',
            'company' => 'Empresa',
            'inviter' => 'Invitado por',
            'role' => 'Rol',
            'accept' => 'Aceptar invitación',
            'expires' => 'Esta invitación vence el :date.',
        ],
        'validation' => [
            'existing_user' => 'Ya existe una cuenta con este correo electrónico.',
            'active_invitation' => 'Ya existe una invitación activa para este correo electrónico.',
            'invalid_invitation' => 'Esta invitación no es válida, venció, fue cancelada o ya fue aceptada.',
            'invalid_company_owner' => 'Esta empresa ya no tiene un propietario elegible.',
        ],
        'success' => [
            'invitation_sent' => 'Invitación al responsable de planificación enviada.',
            'invitation_cancelled' => 'Invitación al responsable de planificación cancelada.',
            'member_removed' => 'Responsable de planificación eliminado de la empresa.',
            'invitation_accepted' => 'Bienvenido al equipo de la empresa.',
        ],
    ],

    'settings_page' => [
        'title' => 'Configuración',

        'personal' => [
            'title' => 'Información personal',
            'subtitle' => 'Actualiza tus datos personales',
            'save_changes' => 'Guardar cambios',
            'name' => 'Nombre completo',
            'email' => 'Correo electrónico',
            'phone' => 'Número de teléfono',
            'company' => 'Nombre de la empresa',
            'success' => 'Información personal actualizada.',
        ],

        'security' => [
            'title' => 'Contraseña y seguridad',
            'subtitle' => 'Gestiona tu contraseña y la configuración de seguridad',
            'change_password' => 'Cambiar contraseña',
            'hide' => 'Ocultar',
            'current_password' => 'Contraseña actual',
            'new_password' => 'Nueva contraseña',
            'confirm_new_password' => 'Confirmar nueva contraseña',
            'update_password' => 'Actualizar contraseña',
            'password_updated' => 'Contraseña actualizada.',
        ],

        'notifications' => [
            'title' => 'Notificaciones y preferencias',
            'subtitle' => 'Personaliza la configuración de notificaciones',
            'email' => 'Notificaciones por correo electrónico',
            'sms' => 'Notificaciones por SMS',
            'projects' => 'Alertas de proyectos',
            'language' => 'Idioma',
            'timezone' => 'Zona horaria',
            'save' => 'Guardar preferencias',
            'success' => 'Preferencias de notificaciones actualizadas.',
            'email_description' => 'Recibir actualizaciones por correo electrónico',
            'sms_description' => 'Recibir actualizaciones por SMS',
            'projects_description' => 'Recibir notificaciones sobre nuevas proyectos',
            'timezone_options' => [
                'utc' => 'UTC',
                'newfoundland' => 'Hora de Terranova',
                'atlantic' => 'Hora del Atlántico',
                'eastern' => 'Hora del Este',
                'central' => 'Hora Central',
                'saskatchewan' => 'Hora de Saskatchewan',
                'mountain' => 'Hora de la Montaña',
                'pacific' => 'Hora del Pacífico',
            ],
        ],

        'danger_zone' => [
            'title' => 'Zona de peligro',
            'subtitle' => 'Desactiva el acceso a tu cuenta',
            'deactivate_account' => 'Desactivar cuenta',
            'deactivate_modal_title' => '¿Desactivar tu cuenta?',
            'deactivate_modal_description' => 'Ingresa tu contraseña para desactivar tu cuenta. Se cerrará tu sesión en todos los dispositivos.',
            'password' => 'Contraseña',
            'owner_cannot_deactivate' => 'Los propietarios de empresa no pueden desactivar su cuenta mientras sean propietarios de una empresa.',
        ],

        'common' => [
            'languages' => [
                'en' => 'Inglés',
                'fr' => 'Francés',
                'es' => 'Español',
            ],
        ],
    ],

    'emails' => [
        'common' => [
            'project_details' => 'Detalles de el proyecto',
            'title' => 'Título',
            'message' => 'Mensaje',
            'worker' => 'Trabajador',
            'role' => 'Rol',
            'name' => 'Nombre',
            'company' => 'Empresa',
            'view_request' => 'Ver solicitud',
        ],
        'project_request' => [
            'subject' => 'Nueva solicitud para unirse a un proyecto',
            'heading' => 'Nueva solicitud para unirse a el proyecto',
            'company_intro' => ':company ha propuesto un trabajador para tu proyecto.',
            'self_employed_intro' => 'Un trabajador autónomo se ha postulado a tu proyecto.',
            'worker_information' => 'Información del trabajador',
            'employment' => 'Situación laboral',
            'lending_company' => 'Empresa proveedora',
            'login_instruction' => 'Inicia sesión para revisar esta solicitud.',
        ],
        'worker_request' => [
            'subject' => 'Nueva solicitud de trabajador',
            'company_heading' => 'Nueva solicitud para tu trabajador',
            'self_employed_heading' => 'Has sido invitado a un proyecto',
            'company_intro' => ':company ha solicitado a uno de tus trabajadores para un proyecto.',
            'self_employed_intro' => ':company te ha invitado a unirte a un proyecto.',
            'worker_details' => 'Detalles del trabajador',
            'self_employed_notice' => 'Esta solicitud es específicamente para ti.',
            'requesting_company' => 'Empresa solicitante',
            'login_instruction' => 'Inicia sesión en tu cuenta para aceptar o rechazar esta solicitud.',
        ],
    ],
];
