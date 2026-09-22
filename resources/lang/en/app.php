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
            'ended_early' => 'Ended Early',
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
            'certifications_placeholder' => 'Add multiple certifications by separating with commas',
            'skills' => 'Skills *',
            'skills_placeholder' => 'Add multiple skills by separating with commas',
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
            'message' => 'Permanently delete this unused worker profile? This action cannot be undone.',
            'action' => 'Delete',
            'confirm' => 'Yes, delete it',
            'cancel' => 'Cancel',
        ],

        'archive_modal' => [
            'title' => 'Archive Worker Profile',
            'message' => 'Archive this worker profile? Its mission, request, and rating history will be preserved.',
            'action' => 'Archive',
            'confirm' => 'Yes, archive it',
            'cancel' => 'Cancel',
        ],

        'validation' => [
            'cannot_delete_worker' => 'Only unarchived worker profiles without business history can be permanently deleted.',
            'cannot_archive_worker' => 'Only worker profiles with resolved history and no active work or requests can be archived.',
            'cannot_edit_archived_worker' => 'Archived worker profiles cannot be edited.',
        ],
    ],

    //:: Availability Page
    'availability_page' => [
        'title' => 'Availability Management',
        'subtitle' => 'Manage your workers\' availability',
        'subtitle_company' => 'Manage your workers\' availability',
        'subtitle_self' => 'Manage your availability',
        'add_availability' => 'Add Time Slot',

        'calendar' => [
            'today' => 'Today',
            'previous' => 'Previous',
            'next' => 'Next',
            'day' => 'Day',
            'week' => 'Week',
            'month' => 'Month',
            'loading' => 'Loading calendar…',
            'load_error' => 'Unable to load calendar availability.',
            'worker_filter_label' => 'Worker',
            'all_workers' => 'All workers',
            'status_legend' => 'Availability status legend',
            'no_workers' => 'There are no workers available to schedule.',
            'no_slots' => 'No availability slots in this period.',
        ],

        'status_options' => [
            'available' => 'Available',
            'booked' => 'Booked',
            'unavailable' => 'Unavailable',
        ],

        'validation' => [
            'overlap' => 'This worker already has an availability slot that overlaps this time.',
            'end_after_start' => 'End time must be later than start time.',
            'mission_assignment_conflict' => 'This worker is already assigned to a mission on this date.',
            'calendar_range_too_large' => 'The calendar range cannot exceed :days days.',
            'worker_not_available' => 'The selected worker is not available to you.',
            'archived_worker_cannot_receive_availability' => 'Archived workers cannot receive new or updated availability.',
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
            'archived_worker_cannot_receive_request' => 'Archived workers cannot receive new requests.',
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
            'workers' => 'Workers',
            'capacity' => ':committed / :required',
            'staffing_progress' => 'Workers: :committed / :required',
            'remaining_capacity' => ':count remaining',
            'requirements' => 'Requirements',
            'posted_by' => 'Posted by',
            'request_join' => 'Request to Join',
            'applied' => 'Applied',
            'invited' => 'Invited',
            'active_profile_required' => 'Active worker profile required',
        ],

        'request_modal' => [
            'title' => 'Request to Join Mission',
            'select_worker' => 'Select Worker *',
            'worker' => 'Select a Worker',
            'no_matching_workers' => "No workers match this mission's job type.",
            'message' => 'Optional Message (e.g. specific skills, experience, questions, etc.)',
            'info' => 'Contact information will be unlocked upon acceptance',
            'already_requested' => 'Already Requested',
            'already_requested_for_mission' => 'This worker already has a request for this mission.',
            'sending' => 'Sending Application...',
            'send' => 'Send Application',
            'cancel' => 'Cancel',
        ],

        'validation' => [
            'archived_worker_cannot_request' => 'Archived workers cannot submit new mission applications.',
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
            'workers_needed_remaining' => 'Workers Needed: :workers — :remaining Remaining',
            'staffing_progress' => 'Workers: :committed / :required',
            'remaining_capacity' => ':count remaining',
            'recruiting' => 'Recruiting',
            'staffing_closed' => 'Staffing Closed',
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
            'staffing' => 'Staffing',
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
            'requirements_placeholder' => 'Add multiple requirements by separating with commas',
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
            'title' => 'Delete Draft Mission - :title',
            'message' => 'Delete this draft mission?',
            'subtitle' => 'This action permanently removes the unused draft.',
            'confirm' => 'Yes, Delete',
        ],

        'archive_modal' => [
            'title' => 'Archive Mission - :title',
            'message' => 'Archive this mission?',
            'subtitle' => 'This mission will be removed from your normal mission list, but its staffing, request, rating, and worker history will be preserved.',
            'confirm' => 'Yes, Archive',
        ],

        'validation' => [
            'lifecycle_managed_status' => 'This mission status is managed by the staffing lifecycle and cannot be edited here.',
            'open_mission_must_remain_open' => 'An open mission cannot be moved back to draft through ordinary editing.',
            'capacity_below_committed' => 'Worker capacity cannot be reduced below the :count committed worker(s).',
            'cannot_delete_mission' => 'Only unused, unarchived draft missions can be permanently deleted.',
            'cannot_archive_mission' => 'Only completed, unarchived missions can be archived.',
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
            'staffing' => 'Staffing',
            'in_progress' => 'In Progress',
            'active' => 'Active',
            'completed' => 'Completed',
            'requests_sent' => 'Requests Sent',
            'requests_received' => 'Requests Received',
            'requests_join' => 'Requests to Join Missions',
            'awaiting_response_invitations' => 'Awaiting their response — Invitations',
            'awaiting_response_applications' => 'Awaiting their response — Applications',
            'needs_your_response' => 'Needs your response',
            'invitations' => 'Invitations',
            'applications' => 'Applications',
            'assignments' => 'Assignments',
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
            'completed_assignments' => 'Completed Assignments',
            'pending_activity' => 'Pending Activity',
            'ongoing_activity' => 'Ongoing Activity',
            'completed_activity' => 'Completed Activity',
        ],

        'empty_states' => [
            'no_requests' => 'No requests',
            'requests_description' => 'Requests related to your missions and workers will appear here.',
            'self_employed_requests_description' => 'Invitations and application requests will appear here.',
            'no_staffing_missions' => 'No staffing missions',
            'staffing_description' => 'Pre-start missions with accepted workers or closed recruiting will appear here.',
            'no_assignments' => 'No assignments',
            'assignments_description' => 'Accepted assignments that have not started yet will appear here.',
            'no_in_progress_missions' => 'No missions in progress',
            'in_progress_description' => 'Active mission assignments will appear here.',
            'completed_description' => 'Completed mission outcomes will appear here.',
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
            'requests' => 'Requests',
            'worker' => 'Worker',
            'assigned_workers' => 'Assigned Workers',
            'assignments' => 'Assignments',
            'worker_outcomes' => 'Worker Outcomes',
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
            'company' => 'Company',
            'company_owner' => 'Company Owner',
            'worker_offered' => 'Worker Offered',
            'reviewed_by' => 'Reviewed By',
            'requested_on' => 'Requested on',
            'accepted_on' => 'Accepted on',
            'rejected_on' => 'Rejected on',
            'cancelled_on' => 'Cancelled on',
            'completed_on' => 'Completed on',
            'original_request' => 'Original request',
            'rejection' => 'Rejection',
            'ended_on' => 'Ended on',
            'contact_company_owner' => 'Contact company owner',
            'worker_review' => 'Worker Review',
            'staffing_progress' => 'Staffing',
            'capacity' => ':committed / :required',
            'remaining' => ':count remaining',
            'recruiting' => 'Recruiting',
            'staffing_closed' => 'Staffing Closed',
            'invitation' => 'Invitation',
            'application' => 'Application',
            'incoming' => 'Incoming',
            'outgoing' => 'Outgoing',
        ],

        'actions' => [
            'complete_and_rate' => 'Complete & Rate',
            'complete_mission' => 'Complete Mission',
            'end_assignment' => 'End Assignment',
            'end_assignment_and_rate' => 'End Assignment & Rate',
            'view_worker_profile' => 'View Worker Profile',
            'view_request_history' => 'View request history',
            'view_mission' => 'View Mission',
            'stop_recruiting' => 'Stop Recruiting',
            'start_mission' => 'Start Mission',
        ],

        'roles' => [
            'administrator' => 'Administrator',
            'company_owner' => 'Company Owner',
            'planning_manager' => 'Planning Manager',
            'self_employed' => 'Self-employed',
        ],

        'states' => [
            'mission_accepted' => 'Mission accepted.',
            'mission_completed' => 'Mission completed successfully.',
            'assignment_completed' => 'Assignment completed successfully.',
            'assignment_ended_early' => 'Assignment ended early.',
        ],

        'success' => [
            'recruiting_closed' => 'Recruiting has been closed for this mission.',
            'mission_started' => 'Mission has started successfully.',
            'assignment_completed' => 'Assignment completed successfully.',
            'assignment_ended_early' => 'Assignment ended early successfully.',
        ],

        'validation' => [
            'request_no_longer_pending' => 'This request is no longer pending.',
            'recruiting_closed' => 'This mission is no longer accepting workers.',
            'mission_not_eligible_for_staffing' => 'This mission is not eligible for additional staffing.',
            'worker_already_committed' => 'This worker is already committed to this mission.',
            'recruiting_already_closed' => 'Recruiting is already closed for this mission.',
            'recruiting_requires_committed_worker' => 'At least one worker must be accepted before recruiting can be closed.',
            'mission_not_ready_to_start' => 'This mission is not ready to start.',
            'mission_start_date_not_reached' => 'This mission cannot start before its start date.',
            'mission_start_requires_committed_worker' => 'At least one committed worker is required to start this mission.',
            'mission_not_in_progress' => 'This mission is not currently in progress.',
            'assignment_not_ready_to_complete' => 'This assignment is not ready to be completed.',
            'assignment_not_ready_to_end_early' => 'This assignment is not ready to end early.',
            'mission_end_date_not_reached' => 'This assignment cannot be completed before the mission end date.',
            'rating_already_exists' => 'This worker has already been rated for this mission.',
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
            'accept_note' => ':contact will be notified when you confirm this request.',
            'reject_note' => ':contact will be notified when you confirm this rejection.',
            'acceptance_placeholder' => 'Add an optional message...',
            'rejection_placeholder' => 'Optional rejection reason...',
            'confirm_accept' => 'Confirm Accept',
            'confirm_reject' => 'Reject Request',
            'company_contact_fallback' => 'Team',
            'acceptance_message_self_employed' => "Hello :contact,\n\nYou've been accepted for the mission “:mission”.\n\nWe look forward to working with you. Thank you!",
            'acceptance_message_company' => "Hello :contact,\n\n:worker has been accepted for the mission “:mission”.\n\nWe look forward to working with you and your team. Thank you!",
            'rejection_message_self_employed' => "Hello :contact,\n\nUnfortunately, you have not been selected for the mission “:mission”.\n\nThank you for your interest.",
            'rejection_message_company' => "Hello :contact,\n\nUnfortunately, :worker has not been selected for the mission “:mission”.\n\nThank you for your interest.",
        ],

        'request_history_modal' => [
            'title' => 'Request History',
        ],

        'completion_modal' => [
            'title' => 'Complete & Rate Worker',
            'end_early_title' => 'End Assignment & Rate Worker',
            'rating_label' => 'How was your experience?',
            'comments_label' => 'Comments (optional)',
            'comments_placeholder' => 'Share your experience...',
        ],

        'recruiting_modal' => [
            'title' => 'Stop Recruiting',
            'message' => 'Stop recruiting for this mission?',
            'subtitle' => 'Pending requests will be cancelled. Accepted workers will remain assigned.',
            'confirm' => 'Stop Recruiting',
        ],

        'start_modal' => [
            'title' => 'Start Mission',
            'message' => 'Start this mission now?',
            'subtitle' => 'Accepted worker assignments will move to in progress.',
            'confirm' => 'Start Mission',
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
                'utc' => 'UTC',
                'newfoundland' => 'Newfoundland Time',
                'atlantic' => 'Atlantic Time',
                'eastern' => 'Eastern Time',
                'central' => 'Central Time',
                'saskatchewan' => 'Saskatchewan Time',
                'mountain' => 'Mountain Time',
                'pacific' => 'Pacific Time',
            ],
        ],

        'danger_zone' => [
            'title' => 'Danger Zone',
            'subtitle' => 'Deactivate access to your account',
            'deactivate_account' => 'Deactivate Account',
            'deactivate_modal_title' => 'Deactivate your account?',
            'deactivate_modal_description' => 'Enter your password to deactivate your account. You will be signed out on all devices.',
            'password' => 'Password',
            'owner_cannot_deactivate' => 'Company owners cannot deactivate their account while they own a company.',
        ],

        'common' => [
            'languages' => [
                'en' => 'English',
                'fr' => 'French',
            ],
        ],
    ],
];
