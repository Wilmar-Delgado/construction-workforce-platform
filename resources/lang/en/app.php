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
    'self_employed' => 'Self-employed',
    'logout' => 'Logout',

    'common' => [
        'save' => 'Save',
        'cancel' => 'Cancel',
        'delete' => 'Delete',
        'close' => 'Close',
        'loading' => 'Loading...',
        'not_available' => 'N/A',
        'unknown_company' => 'Unknown Company',
        'administrator' => 'Administrator',
        'self_employed' => 'Self-employed',
        'per_hour' => '/hour',
        'no_message_provided' => 'No message provided.',
        'statuses' => [
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'ongoing' => 'Ongoing',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            'draft' => 'Draft',
            'open' => 'Open',
            'in_progress' => 'In progress',
        ],
    ],

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

    'onboarding_page' => [
        'title' => 'Onboarding',
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
            'ongoing' => 'Ongoing',
            'pending' => 'Pending',
            'workers' => 'Workers',
            'missions' => 'Missions',
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
        'view_all_missions' => 'View All Missions',
        'manage_availability' => 'Manage Availability',
        'settings' => 'Settings',
    ],

    //:: Profiles Page
    'profiles_page' => [
        'title' => 'Worker Profiles',
        'subtitle' => 'Manage your worker profiles',
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
        'empty_table' => 'No workers added yet. Click “:action” to get started.',
        'experience_years_short' => ':count yrs',

        'labels' => [
            'certifications' => 'Certifications',
            'skills' => 'Skills',
        ],

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
            'drywall_installer' => 'Drywall Installer',
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
            'title' => 'Edit Worker Profile - :name',
            'updating' => 'Updating...',
            'update' => 'Update Profile',
            'save' => 'Update Profile',
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
        'subtitle' => 'Manage your workers\' availability',
        'subtitle_company' => 'Manage your workers\' availability',
        'subtitle_self' => 'Manage your availability',
        'add_availability' => 'Add Time Slot',
        'confirm_delete' => 'Are you sure you want to delete this availability entry?',
        'empty_table' => 'No availability slots added yet. Click “:action” to get started.',

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
            'save' => 'Save Changes',
        ],

        'delete_modal' => [
            'title' => 'Delete Availability',
            'message' => 'Are you sure you want to delete this availability entry? This action cannot be undone.',
            'confirm' => 'Yes, delete it',
            'cancel' => 'Cancel',
            'item_name' => ':worker on :date',
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
        'no_certifications' => 'No certifications added',
        'experience_years_short' => ':count yrs',
        'experience_years' => ':count years',

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
            'company' => 'Your Company Name',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
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
            'duration_day' => ':count day',
            'duration_days' => ':count days',
            'rate' => 'Rate',
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

        'details_modal' => [
            'title' => 'Mission Details',
            'trade' => 'Trade',
            'description' => 'Description',
            'requirements' => 'Requirements',
            'operational_details' => 'Operational Details',
            'site_name' => 'Site Name',
            'address' => 'Site Address',
            'directions' => 'Directions',
            'contact' => 'Site Contact',
        ],
    ],

    //:: My Missions Page
    'missions_page' => [
        'title' => 'Missions',
        'subtitle' => "Create, manage, and track your company’s missions",
        'create_mission' => 'Create Mission',
        'empty_title' => 'No missions yet',
        'empty_desc' => 'Create your first mission to start finding workers and filling your workforce gaps.',
        'empty_tab_title' => 'No :status missions found',
        'empty_search_description' => 'Try adjusting your search or filters.',
        'empty_tab_description' => 'You currently have no :status missions.',
        'copy_title' => ':title (Copy :count)',

        'filters' => [
            'search' => 'Search by title, location, or requirements...',
            'status' => 'All statuses',
        ],

        'tabs' => [
            'all' => 'All',
        ],

        'labels' => [
            'date_range' => ':start to :end',
            'workers_needed_singular' => ':count worker needed',
            'workers_needed_plural' => ':count workers needed',
        ],

        'fallbacks' => [
            'no_description' => 'No description provided.',
        ],

        'actions' => [
            'edit' => 'Edit',
            'view' => 'View',
        ],

        'table' => [
            'title' => 'Title',
            'status' => 'Status',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'city' => 'City',
            'actions' => 'Actions',
        ],

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
            'hourly_rate' => 'Hourly Rate ($)',
            'requirements' => 'Requirements',
            'requirements_placeholder' => "Add requirements (e.g. 'Own tools', '5+ years of experience')",
            'status' => 'Status *',
            'saving' => 'Saving...',
            'save' => 'Create Mission',
            'cancel' => 'Cancel',
        ],

        'edit_modal' => [
            'title' => 'Edit Mission - :title',
            'save' => 'Update Mission',
        ],

        'view_modal' => [
            'title' => 'View Mission - :title',
        ],

        'delete_modal' => [
            'title' => 'Delete Mission - :title',
            'message' => 'Are you sure you want to delete this mission?',
            'subtitle' => 'This action cannot be undone.',
            'confirm' => 'Yes, Delete',
        ],

        'archive_modal' => [
            'title' => 'Archive Mission - :title',
            'message' => 'Are you sure you want to archive this mission?',
            'subtitle' => 'Archived missions are hidden from your active mission list. They can be restored later.',
            'confirm' => 'Yes, Archive',
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
            'requests' => 'Requests',
            'active' => 'Active',
            'completed' => 'Completed',
            'requests_sent' => 'Requests Sent',
            'requests_received' => 'Requests Received',
            'requests_join' => 'Requests to Join Missions',
            'awaiting_response_invitations' => 'Awaiting their response — Invitations',
            'awaiting_response_applications' => 'Awaiting their response — Applications',
            'needs_your_response' => 'Needs your response',
            'your_active_missions' => 'Your active missions',
            'external_assignments' => 'External assignments',
            'your_mission' => 'Your Mission',
            'external_assignment' => 'External Assignment',
            'ongoing_missions' => 'Ongoing Missions',
            'completed_missions' => 'Completed Missions',
            'date' => 'Requested on',
            'waiting_response' => 'Waiting for response...',
            'accept' => 'Accept',
            'reject' => 'Reject',
        ],

        'sections' => [
            'completed_created' => 'Completed Missions You Created',
            'completed_joined' => "Completed Missions You've Joined",
            'pending_activity' => 'Pending Activity',
            'ongoing_activity' => 'Ongoing Activity',
            'completed_activity' => 'Completed Activity',
        ],

        'empty_states' => [
            'no_sent_requests' => 'No sent requests',
            'sent_requests_description' => 'Mission invitations you send to workers will appear here.',
            'no_received_requests' => 'No received requests',
            'received_requests_description' => 'Worker applications and invitations will appear here.',
            'no_join_requests' => 'No join requests',
            'join_requests_description' => 'Mission applications submitted by your company will appear here.',
            'no_active_missions' => 'No active missions',
            'active_created_description' => 'Accepted workers for your missions will appear here.',
            'active_joined_description' => 'External missions your workers joined will appear here.',
            'no_completed_missions' => 'No completed missions',
            'completed_created_description' => 'Completed missions for your organization will appear here.',
            'completed_joined_description' => 'External missions completed by your workers will appear here.',
            'no_activity' => 'No mission activity yet',
            'activity_description' => 'Requests, ongoing missions, and completed work will appear here.',
            'no_pending_activity' => 'No pending activity',
            'pending_activity_description' => 'Pending requests and missions awaiting acceptance will appear here.',
            'no_ongoing_activity' => 'No ongoing activity',
            'ongoing_activity_description' => 'Ongoing requests and missions will appear here.',
            'no_completed_activity' => 'No completed activity',
            'completed_activity_description' => 'Completed requests and missions will appear here.',
        ],

        'labels' => [
            'mission' => 'Mission',
            'worker' => 'Worker',
            'requested_worker' => 'Requested Worker',
            'proposed_worker' => 'Proposed Worker',
            'assigned_worker' => 'Assigned Worker',
            'requested_dates' => 'Requested Dates',
            'mission_dates' => 'Mission Dates',
            'worker_rate' => "Worker's Rate",
            'rate' => 'Rate',
            'final_rate' => 'Final Rate',
            'message' => 'Message',
            'message_sent' => 'Message Sent',
            'message_received' => 'Message Received',
            'application_message' => 'Application Message',
            'invitation_message' => 'Invitation Message',
            'company_name' => 'Company Name',
            'company_owner' => 'Company Owner',
            'requested_on' => 'Requested on',
            'accepted_on' => 'Accepted on',
            'completed_on' => 'Completed on',
            'contact_company_owner' => 'Contact company owner',
            'worker_review' => 'Worker Review',
            'reason_optional' => 'Reason (optional)',
        ],

        'actions' => [
            'complete_and_rate' => 'Complete & Rate',
            'complete_mission' => 'Complete Mission',
            'view_worker_profile' => 'View Worker Profile',
            'view_mission' => 'View Mission',
        ],

        'states' => [
            'mission_accepted' => 'Mission accepted.',
            'mission_completed' => 'Mission completed successfully.',
        ],

        'fallbacks' => [
            'no_feedback' => 'No feedback was provided for this worker.',
            'pending_activity' => 'Pending mission activity.',
            'active_mission' => 'Mission currently active.',
        ],

        'rating' => [
            'score_given' => ':score/5 given',
            'score_received' => ':score/5 received',
        ],

        'response_modal' => [
            'accept_title' => 'Accept Request',
            'reject_title' => 'Reject Request',
            'accept_note' => 'This worker or company owner will be notified immediately.',
            'reject_note' => 'The worker or company owner will be notified of this decision.',
            'acceptance_placeholder' => 'Add an optional message...',
            'rejection_placeholder' => 'Optional rejection reason...',
            'confirm_accept' => 'Confirm Accept',
            'confirm_reject' => 'Reject Request',
            'company_contact_fallback' => 'Team',
            'acceptance_message_self_employed' => 'Hello :worker, your request has been accepted for the mission “:mission”. We look forward to working with you. Thank you!',
            'acceptance_message_company' => 'Hello :contact, the worker “:worker” that you proposed has been accepted for the mission “:mission”. We look forward to working with your team. Thank you!',
        ],

        'completion_modal' => [
            'title' => 'Complete & Rate Worker',
            'rating_label' => 'How was your experience?',
            'comments_label' => 'Comments (optional)',
            'comments_placeholder' => 'Share your experience...',
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
            'success' => 'Personal information updated.',
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
            'success' => 'Notification preferences updated.',
            'email_description' => 'Receive email updates',
            'sms_description' => 'Receive SMS updates',
            'missions_description' => 'Get notified about new missions',
            'timezone_options' => [
                'paris' => 'Europe/Paris (GMT+1)',
                'utc' => 'UTC',
            ],
        ],

        'danger_zone' => [
            'title' => 'Danger Zone',
            'subtitle' => 'Irreversible and destructive actions',
            'delete_account' => 'Delete Account',
            'confirm' => 'Are you sure you want to delete your account? This cannot be undone.',
            'deleted_alert' => 'Account deleted. Redirecting to homepage.',
        ],

        'common' => [
            'languages' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ],
    ],
];
