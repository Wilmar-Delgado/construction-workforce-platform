<?php

return [
    //:: General
    'app_name' => 'Construction Workforce',
    'home' => 'Home',
    'profiles' => 'Worker Profiles',
    'profile' => 'My Profile',
    'availability' => 'Availability',
    'find_workers' => 'Find Workers',
    'find_missions' => 'Find Missions',
    'missions' => 'My Missions',
    'mission_management' => 'Mission Management',
    'settings' => 'Settings',
    'logout' => 'Logout',

    //:: Post Registration Onboarding
    'onboarding' => [
        'company' => [
            'title' => 'Company Setup',
            'description' => 'Complete your company profile to get started.',
            'name' => 'Company Name',
            'phone' => 'Phone',
            'address' => 'Address',
            'create' => 'Create Company',
        ],
    ],

    //:: Auth
    'auth' => [

        // Login
        'login' => [
            'title' => 'Welcome Back',
            'email' => 'Email',
            'password' => 'Password',
            'remember' => 'Remember me',
            'forgot' => 'Forgot password?',
            'submit' => 'Log in',
        ],

        // Register
        'register' => [
            'title' => 'Create Account',
            'name' => 'Full Name',
            'email' => 'Email',
            'role' => 'Select Role',
            'role_placeholder' => 'Select your role',
            'password' => 'Password',
            'confirm_password' => 'Confirm Password',
            'already_registered' => 'Already registered?',
            'submit' => 'Register',
        ],

    ],

    //:: Welcome Page
    'welcome_page' => [
        'hero_highlight' => 'Matching',
        'subtitle' => 'Share workforce between construction companies — no downtime, no layoffs. Put idle crews to work or find skilled workers instantly when you need them.',
        'description' => 'This platform connects construction companies with available employees, understaffed companies looking for skilled workers, and self-employed contractors seeking short-term missions.',

        // Audience
        'audience_title' => 'Who is this platform for?',
        'audience_subtitle' => 'Connecting three key groups in the construction industry',

        'audience' => [
            'employers' => [
                'title' => 'Companies with Available Employees',
                'description' => "List your skilled workers when they're available between projects. Turn downtime into revenue.",
                'points' => [
                    'Maximize workforce utilization',
                    'Generate additional revenue',
                    'Easy worker management',
                ],
            ],

            'hiring' => [
                'title' => 'Companies Looking to Hire Skilled Workers',
                'description' => "Find qualified construction professionals instantly. Fill workforce gaps without the hassle.",
                'points' => [
                    'Access vetted professionals',
                    'Fast hiring process',
                    'Flexible short-term missions',
                ],
            ],

            'contractors' => [
                'title' => 'Self-Employed Contractors',
                'description' => "Discover missions instantly. Build your reputation and track your earnings all in one place.",
                'points' => [
                    'Discover missions instantly',
                    'Build your reputation',
                    'Track your earnings',
                ],
            ],
        ],

        // Badges
        'badges' => [
            'popular' => 'Popular',
        ],

        // CTA
        'cta' => [
            'title' => 'Ready to Get Started?',
            'subtitle' => 'Join thousands of construction professionals already using our platform',
            'action' => 'Create an account',
        ],

        // Actions
        'actions' => [
            'login' => 'Log in',
            'register' => 'Create an account',
        ],

        // Footer
        'footer_rights' => 'All rights reserved. A product of <a href="https://andromedasoftware.ca" target="_blank" rel="noopener noreferrer">Andromeda Software</a>.',
    ],

    //:: Home Page
    'home_page' => [
        'title' => 'Home',

        'welcome' => 'Welcome, :name!',
        'welcome_subtitle' => 'What would you like to do today?',

        'stats' => [
            'ongoing_missions' => 'Ongoing Missions',
            'pending_requests' => 'Pending Requests',
            'active_workers' => 'Active Workers',
            'total_missions' => 'Total Missions',
        ],

        'actions' => [
            'make_available' => 'Make an Employee Available',
            'make_available_desc' => "Add or update your workers' availability schedules. Quick editing in less than 30 seconds.",

            'search_worker' => 'Search for a Worker',
            'search_worker_desc' => 'Browse available workers by job, experience, and skills. Find the perfect match for your mission.',

            'create_profile' => 'Create Your Profile',
            'create_profile_desc' => 'Showcase your skills and experience to get discovered by companies and land your next mission.',

            'edit_profile' => 'Edit Your Profile',
            'edit_profile_desc' => 'Update your skills, experience, and availability to stay visible to employers.',

            'browse_missions' => 'Browse Missions & Opportunities',
            'browse_missions_desc' => 'Discover available missions and job opportunities. Find your next project and apply directly.',
        ],

        'quick_access' => 'Quick Access',
        'mission_hub' => 'Mission Hub',
        'manage_profile' => 'Manage Profile',
        'manage_profiles' => 'Manage Profiles',
        'manage_missions' => 'Manage Missions',
        'settings' => 'Settings',
    ],

    //:: Profiles Page
    'profiles_page' => [
        'self_title' => 'My Profile',
        'company_title' => 'Worker Profiles',
        'self_subtitle' => 'View and manage your profile',
        'edit_profile' => 'Edit Profile',
        'empty_title' => 'No profile yet',
        'empty_desc' => 'Create your profile to start getting discovered by companies.',
        'create_profile' => 'Create Your Profile',
        'company_subtitle' => 'Manage your worker profiles',
        'add_worker' => 'Add New Worker',
        'confirm_delete' => 'Are you sure you want to delete this worker profile?',

        'table' => [
            'name' => 'Name',
            'job' => 'Job / Trade',
            'experience' => 'Experience',
            'rate' => 'Rate',
            'rating' => 'Rating',
            'skills' => 'Skills',
            'actions' => 'Actions',
        ],

        'jobs' => [
            'general_labourer' => 'General Labourer',
            'electrician' => 'Electrician',
            'carpenter' => 'Carpenter',
            'plumber' => 'Plumber',
            'hvac_technician' => 'HVAC Technician',
            'heavy_equipment' => 'Heavy Equipment Operator',
            'welder' => 'Welder',
            'concrete_worker' => 'Concrete Worker',
            'roofer' => 'Roofer',
            'painter' => 'Painter',
            'mason' => 'Mason',
            'ironworker' => 'Ironworker',
            'insulator' => 'Insulator',
        ],

        'add_modal' => [
            'title' => 'Add Worker Profile',
            'name' => 'Full Name *',
            'company' => 'Company (auto-filled)',
            'job' => 'Job / Trade Title *',
            'job_select' => 'Select a Job or Trade',
            'experience' => 'Years of Experience *',
            'rate' => 'Hourly Rate ($) *',
            'certifications' => 'Certifications (optional)',
            'skills' => 'Skills *',
            'saving' => 'Saving...',
            'save' => 'Create Profile',
            'cancel' => 'Cancel',
        ],

        'edit_modal' => [
            'title' => 'Edit Worker Profile',
            'updating' => 'Updating...',
            'update' => 'Update Profile',
        ],

        'delete_modal' => [
            'title' => 'Delete Worker Profile',
            'message' => 'Are you sure you want to delete this worker profile? This action cannot be undone.',
            'confirm' => 'Yes, delete it',
            'cancel' => 'Cancel',
        ],
    ],

    //:: Availability Page
    'availability_page' => [
        'title' => 'Availability Management',
        'subtitle_company' => 'Manage your workers\' availability',
        'subtitle_self' => 'Manage your availability',
        'add_availability' => 'Add Time Slot',
        'confirm_delete' => 'Are you sure you want to delete this availability entry?',

        'table' => [
            'worker' => 'Worker',
            'job' => 'Job / Trade',
            'date' => 'Date',
            'time' => 'Time',
            'status' => 'Status',
            'actions' => 'Actions',
        ],

        'status_options' => [
            'available' => 'Available',
            'booked' => 'Booked',
            'unavailable' => 'Unavailable',
        ],

        'add_modal' => [
            'title' => 'Add Availability Slot',
            'select_worker' => 'Select Worker',
            'worker' => 'Worker *',
            'date' => 'Date *',
            'start_time' => 'Start Time *',
            'end_time' => 'End Time *',
            'status' => 'Status *',
            'saving' => 'Saving...',
            'save' => 'Add Slot',
            'cancel' => 'Cancel',
        ],
        
        'edit_modal' => [
            'title' => 'Edit Availability Slot',
            'updating' => 'Updating...',
            'update' => 'Save Changes',
        ],

        'delete_modal' => [
            'title' => 'Delete Availability',
            'message' => 'Are you sure you want to delete this availability entry? This action cannot be undone.',
            'confirm' => 'Yes, delete it',
            'cancel' => 'Cancel',
        ],
    ],

    //:: Find Workers Page
    'find_workers_page' => [
        'title' => 'Find Workers',
        'subtitle' => 'Browse and request available workers for your missions',
        'workers_found' => 'workers found',
        'view_profile' => 'View Profile',
        'request' => 'Request Worker',
        'certifications' => 'Certifications',
        'top_skills' => 'Top Skills',

        'filters' => [
            'search' => 'Search by name, job, skills or certifications...',
            'job' => 'All jobs',
        ],

        'profile_modal' => [
            'title' => 'Worker Profile',
            'experience' => 'Experience',
            'rate' => 'Rate',
            'certifications' => 'Certifications',
            'skills' => 'Skills',
        ],

        'request_modal' => [
            'title' => 'Request Worker for Mission',

            'rate' => 'Rate',
            'rating' => 'Rating',

            'process_title' => 'Request Process',
            'steps' => [
                'step1_self' => 'Request sent to worker',
                'step1_company' => 'Request sent to lending company',
                'self_employed' => 'Worker can accept or reject',
                'company_worker' => 'Company (Owner or Planning Manager) can accept or reject',
                'step3' => 'Phone number unlocked upon acceptance',
                'step4' => 'Mission confirmed automatically',
            ],

            'select_mission' => 'Select Mission *',
            'choose_mission' => 'Choose a mission for this worker',
            'mission_desc' => 'Optional Message (e.g. specific tasks, project details, etc.)',
            'already_requested' => 'Already Requested',
            'sending' => 'Sending Request...',
            'send' => 'Send Request',
            'cancel' => 'Cancel',
        ],
    ],

    //:: Find Missions Page
    'find_missions_page' => [
        'title' => 'Find Missions',
        'subtitle' => 'Browse available mission opportunities and apply',
        'missions_found' => 'missions found',

        'filters' => [
            'search' => 'Search by title, company or requirements...',
            'job' => 'All jobs',
            'location' => 'All locations',
        ],

        'mission_card' => [
            'duration' => 'Duration',
            'rate' => 'Rate',
            'per_hour' => '/hour',
            'starts' => 'Starts',
            'requirements' => 'Requirements',
            'posted_by' => 'Posted by',
            'request_join' => 'Request to Join',
        ],

        'request_modal' => [
            'title' => 'Request to Join Mission',
            'select_worker' => 'Select Worker *',
            'worker' => 'Select a Worker',
            'no_matching_workers' => "No workers match this mission's job type.",
            'message' => 'Optional Message (e.g. specific skills, experience, questions, etc.)',
            'info' => 'Contact information will be unlocked upon acceptance',
            'already_requested' => 'Already Requested',
            'sending' => 'Sending Application...',
            'send' => 'Send Application',
            'cancel' => 'Cancel',
        ],
    ],

    //:: My Missions Page
    'missions_page' => [
        'title' => 'Missions',
        'subtitle' => "Create, manage, and track your company’s missions",
        'create_mission' => 'Create Mission',
        'empty_title' => 'No missions yet',
        'empty_desc' => 'Create your first mission to start finding workers and filling your workforce gaps.',

        'stats' => [
            'total_missions' => 'Total Missions',
            'draft' => 'Draft',
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
        ],

        'add_modal' => [
            'title' => 'Create Mission',
            'mission_title' => 'Mission Title *',
            'description' => 'Description *',
            'start_date' => 'Start Date *',
            'end_date' => 'End Date *',
            'city' => 'City *',
            'province' => 'Province *',
            'country' => 'Country *',
            'address_line_1' => 'Address Line 1',
            'address_line_2' => 'Address Line 2',
            'postal_code' => 'Postal Code',
            'site_name' => 'Site Name',
            'directions' => 'Directions',
            'job_type' => 'Required Job Type *',
            'number_of_workers' => 'Number of Workers Needed',
            'status' => 'Status *',
            'saving' => 'Saving...',
            'save' => 'Create Mission',
            'cancel' => 'Cancel',
        ],

        'edit_modal' => [
            'title' => 'Edit Mission',
            'save' => 'Update Mission',
        ],
    ],

    //:: Mission Management Page
    'mission_management_page' => [
        'title' => 'Mission Management',
        'subtitle' => 'Your central hub for requests, mission activity, and progress tracking.',
        'create_mission' => 'Create Mission',

        'stats' => [
            'ongoing' => 'Ongoing',
            'pending' => 'Pending',
            'completed' => 'Completed',
            'total' => 'Total',
        ],

        'tabs' => [
            'requests_sent' => 'Requests Sent',
            'requests_received' => 'Requests Received',
            'requests_join' => 'Requests to Join Missions',
            'ongoing_missions' => 'Ongoing Missions',
            'completed_missions' => 'Completed Missions',
            'date' => 'Requested on',
            'waiting_response' => 'Waiting for response...',
            'accept' => 'Accept',
            'reject' => 'Reject',
        ],
    ],

    //:: Settings Page
    'settings_page' => [
        'title' => 'Settings',

        'personal' => [
            'title' => 'Personal Information',
            'subtitle' => 'Update your personal details',
            'save_changes' => 'Save Changes',
            'name' => 'Full Name',
            'email' => 'Email Address',
            'phone' => 'Phone Number',
            'company' => 'Company Name',
        ],

        'security' => [
            'title' => 'Password & Security',
            'subtitle' => 'Manage your password and security settings',
            'change_password' => 'Change Password',
        ],

        'notifications' => [
            'title' => 'Notifications & Preferences',
            'subtitle' => 'Customize your notification settings',
            'email' => 'Email Notifications',
            'sms' => 'SMS Notifications',
            'missions' => 'Mission Alerts',
            'language' => 'Language',
            'timezone' => 'Timezone',
            'save' => 'Save Preferences',
        ],

        'danger_zone' => [
            'title' => 'Danger Zone',
            'subtitle' => 'Irreversible and destructive actions',
            'delete_account' => 'Delete Account',
            'confirm' => 'Are you sure you want to delete your account? This cannot be undone.',
        ],

        'common' => [
            'languages' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ],
    ],
];